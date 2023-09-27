<?php
namespace WPDRMS\ASP\Hooks\Actions;

use WPDRMS\ASP\Index\Manager;

if (!defined('ABSPATH')) die('-1');

class IndexTable extends AbstractAction {
	public function handle() {}

	/** @noinspection PhpUnusedParameterInspection */
	public function update_post_meta($mid, $object_id, $meta_key, $_meta_value) {
		$it_options = wd_asp()->o['asp_it_options'];
		if ( $it_options['it_index_on_update_post_meta'] == 1 ) {
			self::update($object_id, null, true);
		}
	}

	/** @noinspection PhpMissingReturnTypeInspection */
	public function update($post_id=null, $_post=null, $update=false ) {
		/**
		 * Argument default values are set to NULL, as some developers like to call
		 * this action without arguments, which causes an error.
		 */
		if ( !isset($post_id) || wp_is_post_revision( $post_id ) )
			return false;

		$stop = apply_filters('asp_index_on_save_stop', false, $post_id, $_post, $update);
		if ( $stop )
			return false;

		$it_options = wd_asp()->o['asp_it_options'];

		if ($it_options !== false) {

			/**
			 * In some cases custom fields are not created in time of saving the post.
			 * To solve that, the user has an option to turn off automatic indexing
			 * when the post is created - but not when updated, or when a CRON job is executed.
			 */
			if ( $it_options['it_index_on_save'] == 0 && $update == false )
				return false;

			$args = array();
			foreach ($it_options as $k => $o) {
				$args[str_replace('it_', '', $k)] = $o;
			}
			$it_o = new Manager( $args );

			$post_status = get_post_status( $post_id );
			$allowed_statuses = explode(',', $args['post_statuses']);
			if ( count($allowed_statuses) <= 0 )
				return false;
			foreach ($allowed_statuses as &$v)
				$v = trim($v);

			if ($post_status == 'trash' || !in_array($post_status, $allowed_statuses)) {
				$this->delete( $post_id );
				return true;
			}

			$post_type = get_post_type( $post_id );
			$allowed_types = $args['post_types'];
			// If this is a product, and product variations should be indexed, index them as well
			if ( class_exists('WooCommerce') &&
				in_array($post_type, array('product', 'product_variation'))
			) { // Woo products and variations
				if ( $post_type === 'product'  ) { // Product saving
					// Save the variations, if selected
					if ( in_array('product_variation', $allowed_types) ) {
						$args = array(
							'post_type'   => 'product_variation',
							'post_status' => $allowed_statuses,
							'numberposts' => -1,
							'fields'      => 'ids',
							'post_parent' => $post_id // $post->ID
						);
						$variations = get_posts($args);
						foreach ($variations as $variation) {
							if (!is_array($variation) && !is_object($variation))
								$it_o->indexDocument($variation, true, true);
						}
					}
					// Save the product, if selected
					if ( in_array('product', $allowed_types) )
						$it_o->indexDocument( $post_id, true, true );
				} else if ( in_array('product_variation', $allowed_types) && $post_type === 'product_variation' ) { // variation saving
					// Check if post parent status before indexing
					$parent = wp_get_post_parent_id( $post_id );
					if ( $parent !== false ) {
						$parent_post_status = get_post_status( $parent );
						if ( in_array($parent_post_status, $allowed_statuses) )
							$it_o->indexDocument( $post_id, true, true );
					}
				}
			} else { // Any other post type
				$it_o->indexDocument( $post_id, true, true );
			}
		}
		return true;
	}


	public function delete( $post_id ) {
		$it_o = new Manager();

		$post_type = get_post_type( $post_id );
		if ( class_exists('WooCommerce') &&
			$post_type === 'product'
		) {
			$args = array(
				'post_type'     => 'product_variation',
				'post_status'   => 'any',
				'numberposts'   => -1,
				'fields'        => 'ids',
				'post_parent'   => $post_id // $post->ID
			);
			$variations = get_posts( $args );
			$variations[] = $post_id;
			$it_o->removeDocument( $variations );
		} else {
			$it_o->removeDocument( $post_id );
		}
	}

	public function extend() {
		$asp_it_options = wd_asp()->o['asp_it_options'];
		if ($asp_it_options !== false) {
			$args = array();
			foreach ($asp_it_options as $k => $o) {
				$args[str_replace('it_', '', $k)] = $o;
			}
			$it_obj = new Manager( $args );
			$res = $it_obj->extendIndex( );
			update_option("asp_it_cron", array(
				"last_run"  => time(),
				"result"    => $res
			));
		}

	}

	public function cron_extend() {
		// Index Table CRON
		if ( !wp_next_scheduled( 'asp_cron_it_extend' ) ) {
			$asp_it_options = wd_asp()->o['asp_it_options'];
			if ($asp_it_options !== false) {
				$enabled = $asp_it_options['it_cron_enable'];
				$period = $asp_it_options['it_cron_period'];
				$do_media = is_array($asp_it_options['it_post_types']) && in_array( 'attachment', $asp_it_options['it_post_types'] );

				// If enabled, or attachments are queued for index
				if ( $enabled == 1 || $do_media ) {
					if ( $do_media ) {
						// Index media at least every 5 minutes
						if ( $enabled && in_array($period, array('asp_cr_two_minutes', 'asp_cr_three_minutes')) ) {
							wp_schedule_event(time(), $period, 'asp_cron_it_extend');
						} else {
							wp_schedule_event(time(), "asp_cr_five_minutes", 'asp_cron_it_extend');
						}
					} else {
						wp_schedule_event(time(), $period, 'asp_cron_it_extend');
					}
				}
			}
		}
	}
}
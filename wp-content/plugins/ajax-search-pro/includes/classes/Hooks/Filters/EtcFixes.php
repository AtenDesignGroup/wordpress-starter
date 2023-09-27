<?php
/** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpMissingReturnTypeInspection */

/** @noinspection PhpUnused */

namespace WPDRMS\ASP\Hooks\Filters;

if (!defined('ABSPATH')) die('-1');

class EtcFixes extends AbstractFilter {
	protected static $diviElementsLoaded = false;

	function handle() {}

	/**
	 * Fix for the Download Monitor plugin download urls
	 *
	 * @param $results
	 * @return mixed
	 */
	function plug_DownloadMonitorLink($results) {
		if ( class_exists("\\DLM_Download") ) {
			foreach ($results as $r) {
				if ($r->post_type == "dlm_download") {
					$dl = new \DLM_Download($r->id);
					if ($dl->exists()) {
						$r->link = $dl->get_the_download_link();
					}
				}
			}
		}
		return $results;
	}

	/**
	 * Executes search shortcodes when placed as menu titles
	 *
	 * @param $menu_items
	 * @return mixed
	 */
	function allowShortcodeInMenus($menu_items ) {
		foreach ( $menu_items as $menu_item ) {
			if (
				strpos($menu_item->title, '[wd_asp') !== false ||
				strpos($menu_item->title, '[wpdreams_') !== false
			) {
				$menu_item->title = do_shortcode($menu_item->title);
				$menu_item->url = '';
			}
		}
		return $menu_items;
	}

	/**
	 * Adds the 'Standard' post format virtual term to the filter list
	 */
	function fixPostFormatStandard($terms, $taxonomy, $args, $needed_all) {
		if (
			$taxonomy == 'post_format' && !is_wp_error($terms) &&
			( $needed_all || in_array(-200, $args['include_ids']) )
		) {
			$std_term = new \stdClass();
			$std_term->term_id = -200;
			$std_term->taxonomy = 'post_format';
			$std_term->children = array();
			$std_term->name = asp_icl_t('Post format: Standard', 'Standard');
			$std_term->label = asp_icl_t('Post format: Standard', 'Standard');
			$std_term->parent = 0;
			$std_term = apply_filters('asp_post_format_standard', $std_term);
			array_unshift($terms, $std_term);
		}
		return $terms;
	}

	/**
	 * Fixes the 'Standard' post format filter back-end
	 */
	function fixPostFormatStandardArgs($args) {
		if ( isset($args['post_tax_filter']) && is_array($args['post_tax_filter']) ) {
			foreach ($args['post_tax_filter'] as &$v) {
				if ( $v['taxonomy'] == 'post_format') {
					if ( isset($v['_termset']) && in_array(-200, $v['_termset']) && !in_array(-200, $v['exclude']) ) {
						// Case 1: Checkbox, not unselected, but displayed
						$v['allow_empty'] = 1;
					} else if ( in_array(-200, $v['exclude']) ) {
						// Case 2: 'Standard' unchecked
						$v['allow_empty'] = 0;
					} else if ( in_array(-200, $v['include']) && count($v['include']) == 1 ) {
						// Case 3: Non-checkbox, and 'Standard' selected.
						$v['allow_empty'] = 1;
					} else if ( isset($v['_is_checkbox']) && !$v['_is_checkbox'] && !in_array(-200, $v['include']) ) {
						$v['allow_empty'] = 0;
					}
				}
			}
		}
		return $args;
	}

	/**
	 * Fix for the Oxygen builder plugin editor error console
	 */
	function fixOxygenEditorJS( $exit ) {
		if ( isset($_GET['ct_builder']) ) {
			return true;
		}
		return false;
	}

	/**
	 * Fix images on multisite installation results pages with search override
	 *
	 * @param $image
	 * @param $attachment_id
	 * @param $size
	 * @param $icon
	 * @return array
	 */
	function multisiteImageFix( $image, $attachment_id, $size, $icon ) {
		if (
			defined('ASP_MULTISITE_IMAGE_FIX') &&
			is_multisite() &&
			!empty( get_asp_result_field('image') )
		) {
			return array( get_asp_result_field('image'), 300, 300, true );
		} else {
			return $image;
		}
	}

	function allow_json_mime_type($mimes) {
		$mimes['json'] = 'application/json';
		return $mimes;
	}

	function http_request_host_is_external_filter( $allow, $host, $url ) {
		return ( false !== strpos( $host, 'wp-dreams' ) ) ? true : $allow;
	}

	function http_request_args( $args, $url ) {
		// If it is a https request, and we are performing a package download, disable ssl verification.
		if ( strpos( $url, 'update.wp-dreams.com' ) ) {
			$args['sslverify'] = false;
			$args['reject_unsafe_urls'] = false;
		}

		return $args;
	}

	public function diviBuilderReady() {
		self::$diviElementsLoaded = true;
	}

	/**
	 * Init DIVI builder modules for index table save process on the front-end divi editor screen
	 */
	function diviInitModules( $content ) {
		if ( !self::$diviElementsLoaded && function_exists('et_builder_add_main_elements') ) {
			if (
				( isset($_POST['action']) && $_POST['action'] == 'et_fb_ajax_save' )
			) {
				self::$diviElementsLoaded = true;
				et_builder_add_main_elements();
			}
		}

		return $content;
	}

	/**
	 * Init DIVI builder modules for index table ajax process
	 */
	function diviInitModulesOnAjax( $actions ) {
		$actions[] = 'asp_indextable_admin_ajax';
		return $actions;
	}
}
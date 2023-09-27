<?php /** @noinspection PhpComposerExtensionStubsInspection */

/** @noinspection RegExpRedundantEscape */

namespace WPDRMS\ASP\Index;

use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Shortcode') ) {
	class Shortcode {

		private $temporary_shortcode_tags = array();

		/**
		 * Executes the shortcodes within the given string
		 *
		 * @param $content
		 * @param $post
		 * @param $exclude
		 * @return string
		 * @noinspection PhpUndefinedClassInspection
		 */
		function execute($content, $post, $exclude): string {
			$content = apply_filters( 'asp_index_before_shortcode_execution', $content, $post );

			// WP Table Reloaded support
			if ( defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) {
				/** @noinspection PhpIncludeInspection */
				include_once( WP_TABLE_RELOADED_ABSPATH . 'controllers/controller-frontend.php' );
				if ( class_exists('\\WP_Table_Reloaded_Controller_Frontend') ) {
					/** @noinspection PhpFullyQualifiedNameUsageInspection */
					$wpt_reloaded = new \WP_Table_Reloaded_Controller_Frontend();
				}
			}
			// TablePress support
			if ( defined( 'TABLEPRESS_ABSPATH' ) && class_exists('\\TablePress') ) {
				$content .= ' ' . $this->parseTablePressShortcodes($content);
			}

			// Remove user defined shortcodes
			$shortcodes = explode( ',',$exclude );
			$try_getting_sc_content = apply_filters('asp_it_try_getting_sc_content', true);
			foreach ( $shortcodes as $shortcode ) {
				$shortcode = trim($shortcode);
				if ( $shortcode == '' )
					continue;
				// First let us try to get any contents from the shortcode itself
				if ( $try_getting_sc_content ) {
					$content = preg_replace(
						'/(?:\[' . $shortcode . '[ ]+.*?\]|\[' . $shortcode . '[ ]*\])(.*?)\[\/' . $shortcode . '[ ]*]/su',
						' $1 ',
						$content
					);
				}
				// Then remove the shortcode completely
				$this->temporaryDisableShortcode($shortcode);
			}

			// Try extracting the content of these shortcodes, but do not execute
			$more_shortcodes = array(
				'cws-widget', 'cws-row', 'cws-column', 'col', 'row', 'item'
			);
			foreach ( $more_shortcodes as $shortcode ) {
				// First let us try to get any contents from the shortcode itself
				$content = preg_replace(
					'/(?:\[' . $shortcode . '[ ]+.*?\]|\[' . $shortcode . '[ ]*\])(.*?)\[\/' . $shortcode . '[ ]*]/su',
					' $1 ',
					$content
				);
				/*remove_shortcode( $shortcode );
				add_shortcode( $shortcode, array( $this, 'return_empty_string' ) );*/
				$this->temporaryDisableShortcode($shortcode);
			}

			// These shortcodes are completely ignored, and removed with content
			$ignore_shortcodes = array(
				'vc_asp_search',
				'ts_products_in_category_tabs',
				'wd_asp',
				'wpdreams_ajaxsearchpro',
				'wpdreams_ajaxsearchpro_results',
				'wpdreams_asp_settings',
				'contact-form',
				'starrater',
				'responsive-flipbook',
				'avatar_upload',
				'product_categories',
				'recent_products',
				'templatera',
				'bsf-info-box', 'logo-slider',
				'ourteam', 'embedyt', 'gallery', 'bsf-info-box', 'tweet', 'blog', 'portfolio',
				'peepso_activity', 'peepso_profile', 'peepso_group'
			);
			if ( defined( 'TABLEPRESS_ABSPATH' ) && class_exists('\\TablePress') ) {
				$ignore_shortcodes[] = 'table';
			}

			foreach ( $ignore_shortcodes as $shortcode ) {
				$this->temporaryDisableShortcode($shortcode);
			}

			$content = do_shortcode( $content );

			// WP 4.2 emoji strip
			if ( function_exists( 'wp_encode_emoji' ) ) {
				$content = wp_encode_emoji( $content );
			}

			if ( defined( 'TABLEPRESS_ABSPATH' ) ) {
				unset( $tp_controller );
			}

			if ( defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) {
				unset( $wpt_reloaded );
			}

			$this->enableDisabledShortcodes();

			return apply_filters( 'asp_index_after_shortcode_execution', $content, $post );
		}

		private function temporaryDisableShortcode($tag) {
			global $shortcode_tags;
			if ( array_key_exists( $tag, $shortcode_tags ) ) {
				$this->temporary_shortcode_tags[$tag] = $shortcode_tags[$tag];
				$shortcode_tags[ $tag ] = array( $this, 'return_empty_string' );
			}
		}

		private function enableDisabledShortcodes() {
			global $shortcode_tags;
			foreach ($this->temporary_shortcode_tags as $tag => $callback) {
				$shortcode_tags[$tag] = $callback;
			}
		}

		private function parseTablePressShortcodes( $content ): string {
			$regex = '/\[table[^\]]*id\=[\'"]{0,1}([0-9]+)[\'"]{0,1}?[^\]]*\]/';
			$tables = json_decode(get_option('tablepress_tables'), true);
			if ( !is_null($tables) && isset($tables['table_post']) && preg_match_all($regex, $content, $matches) > 0) {
				$return = array();
				foreach ( $matches[1] as $table_id ) {
					$data = json_decode( get_post_field('post_content', $tables['table_post'][$table_id]), true );
					if ( $data !== null ) {
						$return[] = Str::anyToString($data);
					}
				}
				return implode(' ', $return);
			}

			return '';
		}

		/**
		 * An empty function to override individual shortcodes. This must be a public method.
		 *
		 * @return string
		 * @noinspection PhpUnused
		 */
		function return_empty_string(): string {
			return "";
		}
	}
}
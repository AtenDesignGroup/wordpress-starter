<?php
use WPDRMS\ASP\Utils\Script;

defined('ABSPATH') or die("You can't access this file directly.");

add_action('init', array('asp_Gutenberg', 'init'));

if ( !class_exists('asp_Gutenberg') ) {
    class asp_Gutenberg {

        private static $media_query = '';

		public static function render( $atts ){
			if ( $atts['scType'] == 1 ) {
				return do_shortcode('[wd_asp id="' . $atts['instance'] . '" include_styles=1]');
			} else {
				return '';
			}
		}

        public static function init() {

            if ( function_exists('register_block_type') ) {
                $instances = wd_asp()->instances->getWithoutData();

                if (count($instances) > 0) {
                    $ids = array_keys($instances);
                    if (self::$media_query == '')
                        self::$media_query = ASP_DEBUG == 1 ? asp_gen_rnd_str() : get_site_option("asp_media_query", "defn");
                    wp_register_script(
                        'wd-asp-gutenberg',
                        ASP_URL_NP . 'backend/gutenberg/gutenberg.js',
                        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components'),
                        self::$media_query,
                        true
                    );
                    Script::objectToInlineScript('wd-asp-gutenberg', 'ASP_GUTENBERG', array(
                        'ids' => $ids,
                        'instances' => $instances
                    ));
                    wp_register_style('wd-asp-gutenberg-css',
                        ASP_URL_NP . 'backend/gutenberg/gutenberg.css',
                        array( 'wp-edit-blocks' ),
                        self::$media_query
                    );
                    register_block_type( 'ajax-search-pro/block-asp-main', array(
                        'editor_script' => 'wd-asp-gutenberg',
                        'editor_style' => 'wd-asp-gutenberg-css',
						'render_callback' => array(__NAMESPACE__ . '\\' . __CLASS__, 'render'),
						'attributes' => array(
							'instance' => array(
								'default' => 1,
								'type' => 'integer'
							),
							'scType' => array(
								'default' => 1,
								'type' => 'integer'
							)
						)
                    ) );
                    //wp_enqueue_script('wd-asp-gutenberg');

                }
            }
        }
    }
}
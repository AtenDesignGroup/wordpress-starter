<?php
namespace WPDRMS\ASP\Hooks;
if ( !defined('ABSPATH') ) die('-1');

class FiltersManager {
	const NAMESPACE = "WPDRMS\\ASP\\Hooks\\Filters\\";
	/**
	 * Array of internal known filters
	 *
	 * @var array
	 */
	private static $filters = array(
		array(
			"filter" => "posts_request",
			"handler" => array("SearchOverride", "maybeCancelWPQuery"),
			"priority" => 999999999,
			"args" => 2
		),
		array(
			"filter" => "posts_results",
			"handler" => array("SearchOverride", "override"),
			"priority" => 999999999,
			"args" => 2
		),
		array(
			"filter" => "page_link",
			"handler" => array("SearchOverride", "fixUrls"),
			"priority" => 999999999,
			"args" => 2
		),
		array(
			"filter" => "post_link",
			"handler" => array("SearchOverride", "fixUrls"),
			"priority" => 999999999,
			"args" => 2
		),
		array(
			"filter" => "post_type_link",
			"handler" => array("SearchOverride", "fixUrls"),
			"priority" => 999999999,
			"args" => 2
		),
		array(
			"filter" => "elementor/query/query_args",
			"handler" => array("Elementor", "posts"),
			"priority" => 999,
			"args" => 2
		),
		array(
			"filter" => "elementor/theme/posts_archive/query_posts/query_vars",
			"handler" => array("Elementor", "posts_archive"),
			"priority" => 999,
			"args" => 1
		),
		array(
			"filter" => "woocommerce_shortcode_products_query",
			"handler" => array("Elementor", "products"),
			"priority" => 999,
			"args" => 3
		),
		array(
			"filter" => "wp_enqueue_scripts",
			"handler" => array('Asset', 'onPluginFrontendHead'),
			"priority"    => 10,
			"args"  => 0
		),
		array(
			"filter" => "wp_print_footer_scripts",
			"handler" => array('Asset', 'onPluginFooter'),
			"priority"    => 6,
			"args"  => 0
		),
		array(
			"filter" => "admin_print_footer_scripts",
			"handler" => array('Asset', 'onPluginFooter'),
			"priority"    => 6,
			"args"  => 0
		),
		array(
			"filter" => "admin_print_footer_scripts",
			"handler" => array('Asset', 'onPluginBackendFooter'),
			"priority"    => 7,
			"args"  => 0
		),
		array(
			"filter" => "asp_ob_end",
			"handler" => array('Asset', 'injectToOutputBuffer'),
			"priority"    => -101,
			"args"  => 1
		),
		array(
			"filter" => "shutdown",
			"handler" => array('Asset', 'onShutdown'),
			"priority"    => -100,
			"args"  => 0
		),
		array(
			'filter' => 'asp_load_css_js',
			'handler' => array('Asset', 'applySelectiveAssetLoader'),
			'priority'    => 10,
			'args'  => 1
		),
		/* GENESIS REPLACEMENT FOR MULTISITE */
		array(
			"filter" => "genesis_post_title_output",
			"handler" => array("SearchOverride", "fixUrlsGenesis"),
			"priority" => 999999999,
			"args" => 3
		),
		/* ALLOW SHORTCODE AS MENU TITLE */
		array(
			"filter" => "wp_nav_menu_objects",
			"handler" => array("EtcFixes", "allowShortcodeInMenus"),
			"priority" => 10,
			"args" => 1
		),
		array(
			"filter" => "asp_theme_search_form",
			"handler" => "FormOverride",
			"priority" => 999999999,
			"args" => 1
		),
		array(
			"filter" => "get_search_form",
			"handler" => "FormOverride",
			"priority" => 999999999,
			"args" => 1
		),
		array(
			"filter" => "get_product_search_form",
			"handler" => "WooFormOverride",
			"priority" => 999999999,
			"args" => 1
		),
		array(
			"filter" => "asp_results",
			"handler" => array("EtcFixes", "plug_DownloadMonitorLink"),
			"priority" => 999999999,
			"args" => 1
		),
		array(
			"filter" => "asp_fontend_get_taxonomy_terms",
			"handler" => array("EtcFixes", "fixPostFormatStandard"),
			"priority" => 999,
			"args" => 4
		),
		array(
			"filter" => "asp_query_args",
			"handler" => array("EtcFixes", "fixPostFormatStandardArgs"),
			"priority" => 999,
			"args" => 1
		),
		array(
			"filter" => "asp_query_args",
			"handler" => array("IclTranslations", "aspQueryArgsTranslations"),
			"priority" => 999,
			"args" => 2
		),
		array(
			"filter" => "asp_load_js",
			"handler" => array("EtcFixes", "fixOxygenEditorJS"),
			"priority" => 999,
			"args" => 1
		),
		array(
			"filter" => "wp_get_attachment_image_src",
			"handler" => array("EtcFixes", "multisiteImageFix"),
			"priority" => 999,
			"args" => 4
		),
		array(
			"filter" => "upload_mimes",
			"handler" => array("EtcFixes", "allow_json_mime_type"),
			"priority" => 999,
			"args" => 1
		),
		array(
			"filter" => "http_request_host_is_external",
			"handler" => array("EtcFixes", "http_request_host_is_external_filter"),
			"priority" => 9999,
			"args" => 3
		),
		array(
			"filter" => "http_request_args",
			"handler" => array("EtcFixes", "http_request_args"),
			"priority" => 9999,
			"args" => 2
		),
		array(
			"filter" => "asp_post_content_before_tokenize_clear",
			"handler" => array("EtcFixes", "diviInitModules"),
			"priority" => 9999,
			"args" => 1
		),
		array(
			"filter" => "attachment_fields_to_edit",
			"handler" => "MediaScreen",
			"priority" => 9999,
			"args" => 2
		),
		array(
			"filter" => "et_builder_load_actions",
			"handler" => array("EtcFixes", "diviInitModulesOnAjax"),
			"priority" => 9999,
			"args" => 1
		),
		array(
			"filter" => "et_builder_ready",
			"handler" => array("EtcFixes", "diviBuilderReady"),
			"priority" => 9999,
			"args" => 0
		)
	);

	/**
	 * Array of already registered objects
	 *
	 * @var array
	 */
	private static $registered = array();

	/**
	 * Registers all the handlers from the $actions variable
	 */
	public static function registerAll() {
		foreach (self::$filters as $data)
			self::register($data['filter'], $data['handler'], $data['priority'], $data['args']);
	}

	/**
	 * Get all the queued handlers
	 *
	 * @return array
	 */
	public static function getAll(): array {
		return array_keys(self::$filters);
	}

	/**
	 * Get all the already registered handlers (singleton instance storage)
	 *
	 * @return array
	 */
	public static function getRegistered(): array {
		return self::$registered;
	}

	/**
	 * Registers a filter with the handler class name.
	 *
	 * @param $filter
	 * @param $handler string|array
	 * @param int $priority
	 * @param int $accepted_args
	 * @return bool
	 */
	public static function register($filter, $handler, int $priority = 10, int $accepted_args = 0): bool {

		if ( is_array($handler) ) {
			$class = self::NAMESPACE . $handler[0];
			$handle = $handler[1];
		} else {
			$class = self::NAMESPACE . $handler;
			$handle = "handle";
		}

		if ( !class_exists($class) ) return false;

		if ( !isset(self::$registered[$class]) ) {
			self::$registered[$class] = call_user_func(array($class, 'getInstance'));
		}

		if ( !has_filter($filter, array(self::$registered[$class], $handle)) ) {
			add_filter($filter, array(self::$registered[$class], $handle), $priority, $accepted_args);
		}

		return true;
	}

	/**
	 * Deregisters an action handler.
	 *
	 * @param $filter
	 * @param $handler
	 */
	public static function deregister($filter, $handler) {

		if ( is_array($handler) ) {
			$class = self::NAMESPACE . $handler[0];
			$handle = $handler[1];
		} else {
			$class = self::NAMESPACE . $handler;
			$handle = "handle";
		}

		if ( isset(self::$registered[$class]) ) {
			// Deregister via custom method, as WordPress sometimes does not recognize object->method filters
			self::remove_object_filter($filter, $class, $handle);
		}

	}

	private static function remove_object_filter($filter_name, $class_name, $function_name) {
		global $wp_filter;
		foreach ($wp_filter[$filter_name]->callbacks as $priority => $pri_data) {
			foreach ($pri_data as $cb => $cb_data) {
				if (
					is_array($cb_data['function']) &&
					isset($cb_data['function'][0], $cb_data['function'][1])
					&& get_class($cb_data['function'][0]) == $class_name &&
					$cb_data['function'][1] == $function_name
				) {
					unset($wp_filter[$filter_name]->callbacks[$priority][$cb]);
				}
			}
		}
	}

}
<?php
namespace WPDRMS\ASP\Core;

if (!defined('ABSPATH')) die('-1');

class Menu {

	/**
	 * Holds the main menu item
	 *
	 * @var array the main menu
	 */
	private static $main_menu = array();

	/**
	 * @var array
	 */
	private static $hooks = array();

	/**
	 * Submenu titles and slugs
	 *
	 * @var array
	 */
	private static $submenu_items = array();

	/**
	 * Bypass method to support translations, because static array varialbes cannot have a value defined as a result
	 * of a function, like 'key' => __('text', ..)
	 */
	private static function preInit() {
		if ( count(self::$submenu_items) == 0 ) {
			$main_menu = array(
				"title" => __('Ajax Search Pro', 'ajax-search-pro'),
				"slug" => "asp_main_settings",
				"file" => "/backend/settings.php",
				"position" => "207.9",
				"icon_url" => "icon.png"
			);
			$submenu_items = array(
				array(
					"title" => __('Index Table', 'ajax-search-pro'),
					"file" => "/backend/index_table.php",
					"slug" => "asp_index_table"
				),
				array(
					"title" => __('Priorities', 'ajax-search-pro'),
					"file" => "/backend/priorities.php",
					"slug" => "asp_priorities"
				),
				array(
					"title" => __('Search Statistics', 'ajax-search-pro'),
					"file" => "/backend/statistics.php",
					"slug" => "asp_statistics"
				),
				array(
					"title" => __('Analytics Integration', 'ajax-search-pro'),
					"file" => "/backend/analytics.php",
					"slug" => "asp_analytics"
				),
				array(
					"title" => __('Cache Settings', 'ajax-search-pro'),
					"file" => "/backend/cache_settings.php",
					"slug" => "asp_cache_settings"
				),
				array(
					"title" => __('Performance tracking', 'ajax-search-pro'),
					"file" => "/backend/performance.php",
					"slug" => "asp_performance"
				),
				array(
					"title" => __('Compatibility & Other Settings', 'ajax-search-pro'),
					"file" => "/backend/compatibility_settings.php",
					"slug" => "asp_compatibility_settings"
				),
				array(
					"title" => __('Export/Import', 'ajax-search-pro'),
					"file" => "/backend/export_import.php",
					"slug" => "asp_export_import"
				),
				array(
					"title" => __('Maintenance', 'ajax-search-pro'),
					"file" => "/backend/maintenance.php",
					"slug" => "asp_maintenance"
				),
				array(
					"title" => __('Help & Updates', 'ajax-search-pro'),
					"file" => "/backend/updates_help.php",
					"slug" => "asp_updates_help"
				)
			);

			self::$main_menu = $main_menu;
			self::$submenu_items = $submenu_items;
		}
	}

	/**
	 * Runs the menu registration process
	 */
	public static function register() {

		$capability = ASP_DEMO == 1 ? 'read' : 'manage_options';

		self::preInit();

		$h = add_menu_page(
			self::$main_menu['title'],
			self::$main_menu['title'],
			$capability,
			self::$main_menu['slug'],
			array("\\WPDRMS\\ASP\\Core\\Menu", "route"),
			ASP_URL . self::$main_menu['icon_url'],
			self::$main_menu['position']
		);
		self::$hooks[$h] = self::$main_menu['slug'];

		foreach (self::$submenu_items as $submenu) {
			$h = add_submenu_page(
				self::$main_menu['slug'],
				self::$main_menu['title'],
				$submenu['title'],
				$capability,
				$submenu['slug'],
				array("\\WPDRMS\\ASP\\Core\\Menu", "route")
			);
			self::$hooks[$h] = $submenu['slug'];
		}

	}

	/**
	 *  Includes the correct back-end file based on the page string
	 */
	public static function route() {
		$current_view = self::$hooks[current_filter()];
		include(ASP_PATH.'backend/'.str_replace("asp_", "", $current_view).'.php');
	}

	/**
	 * Method to obtain the menu pages for context checking
	 *
	 * @return array
	 */
	public static function getMenuPages(): array {
		self::preInit();
		$ret = array();

		$ret[] = self::$main_menu['slug'];

		foreach (self::$submenu_items as $menu)
			$ret[] = $menu['slug'];

		return $ret;
	}

}
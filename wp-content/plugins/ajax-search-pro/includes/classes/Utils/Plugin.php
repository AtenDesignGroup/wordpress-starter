<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Plugin') ) {
	class Plugin {
		/**
		 * Version comparator for previous plugin versions, based on PHP version_check
		 *
		 * @param string $ver version to check against
		 * @param string $operator opertator, same as in version_check()
		 * @param false $check_updated if true, returns if the version was just updated from an older one
		 * @return bool|int
		 */
		public static function previousVersion(string $ver = '', string $operator = '<=', bool $check_updated = false ) {
			$updated = false;

			$version = get_site_option('_asp_version', array(
				'current' => ASP_CURR_VER_STRING,
				'previous' => ASP_CURR_VER_STRING, // version, when this feature was implemented
				'new' => true
			));
			// The option does not exist yet
			if ( isset($version['new']) ) {
				$version = array(
					'current' => ASP_CURR_VER_STRING,
					'previous' => get_option('asp_version', 0) == 0 ? ASP_CURR_VER_STRING : '4.20.2'
				);
				update_site_option('_asp_version', $version);
				delete_option('asp_version');
			}
			// It has been an update - this executes only once per each version activation
			if ( $version['current'] != ASP_CURR_VER_STRING ) {
				$version = array(
					'current' => ASP_CURR_VER_STRING,
					'previous' => $version['current']
				);
				update_site_option('_asp_version', $version);
				$updated = true;
			}

			if ( $check_updated ) {
				return $updated;
			} else {
				return version_compare($version['previous'], $ver, $operator);
			}
		}


		/**
		 * Returns the template file path, considering templating feature
		 *
		 * @param string $file File path relative to the /views/ directory, without starting slash.
		 * @return string
		 */
		public static function templateFilePath(string $file): string {
			$theme_path = get_stylesheet_directory() . "/asp/";
			if ( file_exists( $theme_path . $file ) )
				return $theme_path . $file;
			else
				return ASP_INCLUDES_PATH . 'views/' . $file;
		}
	}
}
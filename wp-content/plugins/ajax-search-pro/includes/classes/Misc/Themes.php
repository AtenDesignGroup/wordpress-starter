<?php
namespace WPDRMS\ASP\Misc;

if ( !defined('ABSPATH') ) die('-1');

class Themes {
	/**
	 * List of theme files
	 *
	 * @var array
	 */
	private static $files = array(
		'search' => 'themes.json',
		'search_buttons' => 'sb_themes.json'
	);
	/**
	 * List of themes (key=>theme)
	 *
	 * @var array
	 */
	private static $themes = array();
	/**
	 * Path to the theme files
	 *
	 * @var
	 */
	private static $path;

	/**
	 * Gets the theme, or sub theme
	 *
	 * @param $theme
	 * @param string $sub
	 * @return mixed
	 */
	public static function get($theme, string $sub = 'all' ) {
		if ( count(self::$themes) < 1 ) {
			self::init();
		}
		if ( isset(self::$themes[$theme]) ) {
			if ( $sub != 'all' && !empty($sub) ) {
				if ( isset(self::$themes[$theme][$sub]) ) {
					if ( isset(self::$themes[$theme][$sub]['_ref']) ) {
						$ref = self::$themes[$theme][$sub]['_ref'];
						return array_merge(
							self::$themes[$theme][$ref],
							self::$themes[$theme][$sub]
						);
					} else {
						return self::$themes[$theme][$sub];
					}
				} else {
					/**
					 * Some themes may not have any keys just a boolean (false) value, avoid those
					 * and return the next usable theme.
					 */
					foreach ( self::$themes[$theme] as $keys ) {
						if ( is_array($keys) )
							return $keys;
					}
				}
			} else {
				return self::$themes[$theme];
			}
		}

		return false;
	}

	/**
	 * Init, gets the files to the variables
	 */
	private static function init() {
		$ds = DIRECTORY_SEPARATOR;
		self::$path = ASP_PATH . $ds . 'backend' . $ds . 'settings' . $ds;

		foreach ( self::$files as $k => $file ) {
			if ( !isset(self::$themes[$k]) ) {
				if ( $file == 'themes.json') {
					if ( ASP_DEBUG == 1 || defined('WP_ASP_TEST_ENV') ) {
						self::$themes[$k] = file_get_contents(self::$path . 'themes.json');
					} else {
						self::$themes[$k] = file_get_contents(self::$path . 'themes.min.json');
					}
				} else {
					self::$themes[$k] = file_get_contents(self::$path . $file);
				}
				self::$themes[$k] = json_decode(self::$themes[$k], true);
			}
		}
	}
}
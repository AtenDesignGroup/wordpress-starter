<?php
namespace WPDRMS\ASP\Utils\Polylang;

defined('ABSPATH') or die("You can't access this file directly.");

class StringTranslations {
	/**
	 * All string translations added by asp_icl_t(...)
	 * @var array
	 */
	private static $strings = array();

	/**
	 * To see if the $strings array was changed at any time during execution
	 * @var bool
	 */
	private static $changed = false;

	/**
	 * Runs at init action
	 */
	public static function init() {
		if ( function_exists('pll_register_string') ) {
			self::$strings = get_option('_asp_pll_strings', array());
		}
	}

	/**
	 * Add a string translation to $strings static variable
	 *
	 * @param $name
	 * @param $value
	 */
	public static function add($name, $value) {
		if ( function_exists('pll_register_string') ) {
			$found = false;
			foreach (self::$strings as &$string) {
				if ($string['name'] == $name) {
					if ( $string['value'] != $value ) {
						$string['value'] = $value;
						self::$changed = true;
					}
					$found = true;
					break;
				}
			}
			if ( !$found ) {
				self::$strings[] = array(
					'name' => $name,
					'value' => $value
				);
				self::$changed = true;
			}
		}
	}

	/**
	 * Runs at wp_footer action hook. Saves string translations at page footer
	 */
	public static function save() {
		if ( function_exists('pll_register_string') && self::$changed ) {
			self::$strings = wd_array_super_unique(self::$strings, 'name');
			update_option('_asp_pll_strings', self::$strings);
		}
	}

	/**
	 * Runs at init action hook. Registers the strings with polylang
	 */
	public static function register() {
		/**
		 * PLL specific
		 *   Pll does not actually register the unique string by $name, only the values, but it causes issues
		 *   with saving them on the back-end, as they override each other. Removing the variables, from the names
		 *   resolves the problem.
		 */
		if ( function_exists('pll_register_string') ) {
			$strings = get_option('_asp_pll_strings', array());
			$names = array();
			$values = array();
			foreach ($strings as $string) {
				if ( !in_array($string['value'], $values) ) {
					$name = preg_replace('/[0-9]+/', '', $string['name']);
					$name = preg_replace('/\s+/', ' ', $name);
					$name = str_replace(array('()', '[]'), '', $name);
					$names[] = $name;
					$values[] = $string['value'];
				}
			}
			// Register only distinctive values
			foreach ( $names as $k => $v ) {
				@pll_register_string($v, $values[$k], 'ajax-search-pro');
			}
		}
	}
}
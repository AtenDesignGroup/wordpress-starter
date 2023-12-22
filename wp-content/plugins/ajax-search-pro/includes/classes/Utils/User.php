<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

class User {
	/**
	 * Gets the custom user meta field value, supporting ACF get_field()
	 *
	 * @param  $field   -   Custom field label
	 * @param  $r       -   Result object||Result ID
	 * @param bool  $use_acf -   If true, will use the get_field() function from ACF
	 * @return mixed
	 * @see get_field() ACF post meta parsing.
	 */
	public static function getCFValue($field, $r, bool $use_acf = false) {
		$ret = '';
		$id = is_object($r) && isset($r->id) ? $r->id : intval($r);

		if ( $use_acf && function_exists('get_field') ) {
			$mykey_values = get_field($field, 'user_'.$id, true);
			if (!is_null($mykey_values) && $mykey_values != '' && $mykey_values !== false ) {
				if (is_array($mykey_values)) {
					if (!is_object($mykey_values[0])) {
						$ret = implode(', ', $mykey_values);
					}
				} else {
					$ret = $mykey_values;
				}
			}
		} else {
			$mykey_values = get_user_meta($id, $field);
			if (isset($mykey_values[0])) {
				$ret = $mykey_values[0];
			}
		}

		return $ret;
	}
}
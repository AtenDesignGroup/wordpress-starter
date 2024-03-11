<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Validation;

class ColorValidator {
	public static function validateHex($value, \WP_Error $errors) {
		if ( !is_string($value) ) {
			$errors->add('invalid_color_string', 'Value must be a string');
			return $errors;
		}

		$value = trim($value);
		//Allow either 3 or 6 hex digits, but nothing in between.
		//Alpha is technically allowed, but the WP color picker doesn't support it.
		if ( !preg_match('/^#(?:[\da-f]{6}|[\da-f]{3})$/i', $value) ) {
			$errors->add('invalid_hex_color', 'Value must be a valid CSS hex color');
			return $errors;
		}
		return $value;
	}
}
<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

class ColorSetting extends Setting {
	protected $label = 'Color';
	protected $dataType = 'color';

	public function validate($errors, $value, $stopOnFirstError = false) {
		if ( $value === '' ) {
			//An empty string is explicitly valid.
			return $value;
		} else if ( $this->canTreatAsNull($value) ) {
			return null;
		}

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
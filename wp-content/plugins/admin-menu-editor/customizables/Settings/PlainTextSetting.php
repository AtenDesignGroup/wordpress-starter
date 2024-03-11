<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

/**
 * Like StringSetting, except it always strips HTML tags, even if the current
 * user has the "unfiltered_html" capability.
 */
class PlainTextSetting extends StringSetting {
	public function validate($errors, $value, $stopOnFirstError = false) {
		$convertedValue = parent::validate($errors, $value);
		if ( is_wp_error($convertedValue) || ($convertedValue === null) ) {
			return $convertedValue;
		}
		return wp_kses($convertedValue, 'strip');
	}
}
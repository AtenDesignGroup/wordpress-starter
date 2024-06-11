<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

class UrlSetting extends StringSetting {
	protected $dataType = 'url';

	public function validate($errors, $value, $stopOnFirstError = false) {
		$convertedValue = parent::validate($errors, $value);
		if ( is_wp_error($convertedValue) || ($convertedValue === null) ) {
			return $convertedValue;
		}

		//Optionally, accept an empty string.
		$convertedValue = ltrim($convertedValue);
		if ( ($convertedValue === '') && ($this->minLength === 0) ) {
			return $convertedValue;
		}

		//TODO: Optionally, allow protocol-relative URLs. Also in the JS validator.
		//TODO: Optionally, allow shortcodes.

		$filteredValue = filter_var($convertedValue, FILTER_VALIDATE_URL);
		if ( $filteredValue === false ) {
			$errors->add('invalid_url', 'Value must be a valid URL');
			return $errors;
		}

		$convertedValue = esc_url_raw($filteredValue);
		if ( empty($convertedValue) ) {
			//esc_url() documentation says it returns an empty string if the protocol
			//is not one of the allowed protocols, but I'm not 100% sure if that is
			//the *only* situation where it might return an empty string.
			$errors->add('invalid_protocol', 'Invalid protocol or a malformed URL');
			return $errors;
		}

		return $convertedValue;
	}
}
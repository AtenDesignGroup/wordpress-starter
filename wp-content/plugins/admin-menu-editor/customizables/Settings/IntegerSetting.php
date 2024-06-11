<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

class IntegerSetting extends NumericSetting {
	protected $dataType = 'integer';

	public function validate($errors, $value, $stopOnFirstError = false) {
		$numValue = parent::validate($errors, $value);
		if ( is_wp_error($numValue) || ($numValue === null) ) {
			return $numValue;
		}

		//The value must be an integer (no decimals).
		if ( $numValue !== floor($numValue) ) {
			$errors->add('not_integer', 'Value must be an integer');
			return $errors;
		}

		return intval($value);
	}

	public function serializeValidationRules() {
		$result = parent::serializeValidationRules();
		if ( !isset($result['parsers']) ) {
			$result['parsers'] = [];
		}
		$result['parsers'][] = ['int'];
		return $result;
	}
}
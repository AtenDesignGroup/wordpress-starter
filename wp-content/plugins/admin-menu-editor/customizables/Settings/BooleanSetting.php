<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

class BooleanSetting extends Setting {
	protected $dataType = 'boolean';

	public function validate($errors, $value, $stopOnFirstError = false) {
		if ( $this->canTreatAsNull($value) ) {
			return null;
		}

		$value = $this->tryConvertToBool($value);
		if ( $value === null ) {
			$errors->add('not_boolean', 'Value must be a boolean (true or false)');
			return $errors;
		}
		return $value;
	}

	/**
	 * @param mixed $value
	 * @return bool|null
	 */
	protected function tryConvertToBool($value) {
		if ( is_string($value) ) {
			//Handle values like "on", "off", "false", etc.
			return filter_var(strtolower($value), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		} else if ( $value !== null ) {
			return boolval($value);
		}
		return null;
	}
}
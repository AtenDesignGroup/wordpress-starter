<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;
use YahnisElsts\AdminMenuEditor\Customizable\Validation\StringValidator;

class StringSetting extends Setting {
	protected $dataType = 'string';

	protected $minLength = 0;
	protected $maxLength = null;
	protected $validators = [];

	public function __construct($id, StorageInterface $store = null, $params = array()) {
		parent::__construct($id, $store, $params);

		if ( array_key_exists('minlength', $params) ) {
			$this->minLength = ($params['minlength'] === null) ? null : (int)$params['minlength'];
		}
		if ( array_key_exists('maxlength', $params) ) {
			$this->maxLength = ($params['maxlength'] === null) ? null : (int)$params['maxlength'];
		}

		$this->validators[] = new StringValidator(
			$this->minLength,
			$this->maxLength,
			false,
			isset($params['regex']) ? $params['regex'] : null,
			array_key_exists('trimmed', $params) && $params['trimmed']
		);
	}

	public function validate($errors, $value, $stopOnFirstError = false) {
		if ( $this->canTreatAsNull($value) ) {
			return null;
		}

		$convertedValue = $value;
		foreach ($this->validators as $validator) {
			$result = call_user_func($validator, $convertedValue, $errors);
			if ( is_wp_error($result) ) {
				$errors = $result;
				if ( $stopOnFirstError ) {
					return $errors;
				}
			} else {
				$convertedValue = $result;
			}
		}

		return $convertedValue;
	}
}
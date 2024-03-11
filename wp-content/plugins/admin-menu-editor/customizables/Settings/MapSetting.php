<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

class MapSetting extends Setting {
	protected $dataType = 'map';
	protected $defaultValue = [];

	protected $keyValidators = [];
	protected $valueValidators = [];

	public function __construct($id, StorageInterface $store = null, $params = array()) {
		parent::__construct($id, $store, $params);

		if ( array_key_exists('keyValidators', $params) ) {
			$this->keyValidators = $params['keyValidators'];
		}
		if ( array_key_exists('value_validators', $params) ) {
			$this->valueValidators = $params['valueValidators'];
		}
	}

	public function validate($errors, $value, $stopOnFirstError = false) {
		if ( $this->canTreatAsNull($value) ) {
			return null;
		}

		$validatedItems = [];
		$hasErrors = false;

		foreach($value as $key => $item) {
			$validatedKey = self::applyValidators($this->keyValidators, $key, $errors, $stopOnFirstError);
			if ( is_wp_error($validatedKey) ) {
				$hasErrors = true;
				$errors = $validatedKey;
				if ( $stopOnFirstError ) {
					return $errors;
				}
				continue;
			}

			$validatedItem = self::applyValidators($this->valueValidators, $item, $errors, $stopOnFirstError);
			if ( is_wp_error($validatedItem) ) {
				$hasErrors = true;
				$errors = $validatedItem;
				if ( $stopOnFirstError ) {
					return $errors;
				}
			} else {
				$validatedItems[$key] = $item;
			}
		}

		return $hasErrors ? $errors : $validatedItems;
	}
}
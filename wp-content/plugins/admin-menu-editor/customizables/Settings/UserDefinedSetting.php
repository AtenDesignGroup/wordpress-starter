<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

class UserDefinedSetting extends Setting {
	/**
	 * @var callable
	 */
	protected $validationCallback;

	public function __construct($id, StorageInterface $store = null, $params = array()) {
		parent::__construct($id, $store, $params);

		if ( isset($params['validationCallback']) ) {
			$this->validationCallback = $params['validationCallback'];
		} else {
			throw new \InvalidArgumentException('UserDefinedSetting must have a validationCallback parameter');
		}
	}

	public function validate($errors, $value, $stopOnFirstError = false) {
		return call_user_func($this->validationCallback, $value, $errors);
	}
}
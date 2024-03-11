<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

class StringEnumSetting extends EnumSetting {
	protected $dataType = 'string';

	public function encodeForForm($value) {
		if ( $this->isNullable() ) {
			return wp_json_encode($value);
		} else {
			return (string)$value;
		}
	}

	public function decodeSubmittedValue($value) {
		if ( $this->isNullable() ) {
			return json_decode($value, true);
		} else {
			return $value;
		}
	}
}
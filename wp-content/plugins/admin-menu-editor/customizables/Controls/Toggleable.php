<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\SettingCondition;

trait Toggleable {
	/**
	 * @var callable
	 */
	protected $enabled = '__return_true';

	protected function parseEnabledParam($params) {
		if ( array_key_exists('enabled', $params) ) {
			if (
				is_bool($params['enabled'])
				|| is_numeric($params['enabled'])
				|| ($params['enabled'] === null)
			) {
				$this->enabled = $params['enabled'] ? '__return_true' : '__return_false';
			} else {
				$this->enabled = $params['enabled'];
			}
		} else if ( isset($this->mainSetting) && !empty($this->mainSetting) ) {
			$this->enabled = $this->mainSetting->isEditableByUser() ? '__return_true' : '__return_false';
		}
	}

	/**
	 * @return bool
	 */
	public function isEnabled() {
		return call_user_func($this->enabled);
	}

	protected function getKoEnableBinding() {
		if ( $this->enabled instanceof SettingCondition ) {
			return ['enable' => $this->enabled->getJsKoExpression()];
		}
		return $this->isEnabled() ? [] : ['enable' => false];
	}

	protected function serializeConditionForJs() {
		if ( $this->enabled instanceof SettingCondition ) {
			return $this->enabled->serializeForJs();
		}
		return $this->isEnabled();
	}
}
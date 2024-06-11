<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;

class ToggleCheckBoxBuilder extends ControlBuilder {
	public function __construct($settings = array(), $params = array()) {
		parent::__construct(Controls\ToggleCheckbox::class, $settings, $params);
	}

	/**
	 * @param scalar $value
	 * @return $this
	 */
	public function onValue($value) {
		$this->params['onValue'] = $value;
		return $this;
	}

	/**
	 * @param scalar $value
	 * @return $this
	 */
	public function offValue($value) {
		$this->params['offValue'] = $value;
		return $this;
	}
}
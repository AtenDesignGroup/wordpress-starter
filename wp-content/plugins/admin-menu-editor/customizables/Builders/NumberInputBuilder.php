<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;
use YahnisElsts\AdminMenuEditor\Customizable\Settings;

class NumberInputBuilder extends ControlBuilder {
	public function __construct($settings = array(), $params = array()) {
		parent::__construct(Controls\NumberInput::class, $settings, $params);
	}

	public function min($min) {
		$this->params['min'] = $min;
		return $this;
	}

	public function max($max) {
		$this->params['max'] = $max;
		return $this;
	}

	public function step($step) {
		$this->params['step'] = $step;
		return $this;
	}

	public function unitSetting(Settings\Setting $unitSetting) {
		$this->params['unit'] = $unitSetting;
		return $this;
	}

	/**
	 * @param string $unit
	 * @return $this
	 */
	public function unitText($unit) {
		$this->params['unit'] = (string)$unit;
		return $this;
	}
}
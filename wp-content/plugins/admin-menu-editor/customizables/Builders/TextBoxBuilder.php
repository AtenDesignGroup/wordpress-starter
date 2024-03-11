<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;

class TextBoxBuilder extends ControlBuilder {
	public function __construct($settings = array(), $params = array()) {
		parent::__construct(Controls\TextInputControl::class, $settings, $params);
	}

	public function type($inputTypeAttribute) {
		$this->params['inputType'] = $inputTypeAttribute;
		return $this;
	}

	public function code($isCode = true) {
		$this->params['isCode'] = $isCode;
		return $this;
	}
}
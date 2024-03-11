<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;

class TextareaBuilder extends ControlBuilder {
	public function __construct($settings = array(), $params = array()) {
		parent::__construct(Controls\TextArea::class, $settings, $params);
	}

	public function rows($rows) {
		$this->params['rows'] = $rows;
		return $this;
	}

	public function cols($cols) {
		$this->params['cols'] = $cols;
		return $this;
	}
}
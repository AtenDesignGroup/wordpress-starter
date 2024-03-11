<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;

class CodeEditorBuilder extends ControlBuilder {
	public function __construct($settings = array(), $params = array()) {
		parent::__construct(Controls\CodeEditor::class, $settings, $params);
	}

	public function cssMode() {
		$this->params['mimeType'] = 'text/css';
		return $this;
	}

	public function jsMode() {
		$this->params['mimeType'] = 'application/javascript';
		return $this;
	}

	public function htmlMode() {
		$this->params['mimeType'] = 'text/html';
		return $this;
	}
}
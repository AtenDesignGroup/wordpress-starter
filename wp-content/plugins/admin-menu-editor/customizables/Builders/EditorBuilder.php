<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;

class EditorBuilder extends ControlBuilder {
	public function __construct($settings = array(), $params = array()) {
		parent::__construct(Controls\WpEditor::class, $settings, $params);
	}

	/**
	 * @param int $rows
	 * @return $this
	 */
	public function rows($rows) {
		$this->params['rows'] = $rows;
		return $this;
	}

	/**
	 * @param bool $isTeeny
	 * @return $this
	 */
	public function setTeeny($isTeeny) {
		$this->params['teeny'] = $isTeeny;
		return $this;
	}
}
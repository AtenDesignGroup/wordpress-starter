<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;

class GroupBuilder extends ContainerBuilder {
	public function __construct($title = '', $children = array()) {
		parent::__construct(Controls\ControlGroup::class, $title, $children);
	}

	/**
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Controls\ControlGroup
	 */
	public function build() {
		return new Controls\ControlGroup($this->title, $this->buildChildren(), $this->params);
	}

	public function stacked($isStacked = true) {
		$this->params['stacked'] = $isStacked;
		return $this;
	}

	public function fieldset($wantsFieldset = true) {
		$this->params['fieldset'] = $wantsFieldset;
		return $this;
	}
}
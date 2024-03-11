<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;

class SectionBuilder extends ContainerBuilder {
	public function __construct($title = '', $children = array()) {
		parent::__construct(Controls\Section::class, $title, $children);
	}

	public function group($title = '') {
		$builder = new GroupBuilder($title);
		$this->children[] = $builder;
		return $builder;
	}
}
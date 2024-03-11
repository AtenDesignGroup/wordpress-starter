<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\UiElement;

/**
 * @template ElementClass of UiElement
 */
interface ElementBuilder {
	/**
	 * @return ElementClass
	 */
	public function build();
}
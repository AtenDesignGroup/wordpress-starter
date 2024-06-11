<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;

class StaticHtmlBuilder implements ElementBuilder {
	/**
	 * @var string
	 */
	protected $html;

	/**
	 * @param string $html
	 */
	public function __construct($html) {
		$this->html = $html;
	}

	/**
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Controls\StaticHtml
	 */
	public function build() {
		return new Controls\StaticHtml($this->html);
	}
}
<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\UiElement;

abstract class ContainerBuilder extends BaseElementBuilder {
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var array<ElementBuilder|UiElement|array>
	 */
	protected $children = array();

	/**
	 * @param class-string<\YahnisElsts\AdminMenuEditor\Customizable\Controls\Container> $containerClass
	 * @param string $title
	 */
	protected function __construct($containerClass, $title, $children = array()) {
		parent::__construct($containerClass);
		$this->title = $title;
		$this->children = $children;
	}

	/**
	 * @return UiElement[]
	 */
	protected function buildChildren() {
		return self::buildItems($this->children);
	}

	public function build() {
		$className = $this->elementClass;
		return new $className($this->title, $this->buildChildren(), $this->params);
	}

	/**
	 * @param ElementBuilder|UiElement ...$children
	 * @return $this
	 */
	public function add(...$children) {
		return $this->addAll($children);
	}

	/**
	 * @param array<ElementBuilder|UiElement> $children
	 * @return $this
	 */
	public function addAll($children) {
		foreach ($children as $child) {
			$this->children[] = $child;
		}
		return $this;
	}

	public function tooltip($html, $type = Controls\Tooltip::DEFAULT_TYPE) {
		$this->params['tooltip'] = new Controls\Tooltip($html, $type);
		return $this;
	}
}
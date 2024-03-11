<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls\UiElement;

/**
 * @template ElementClass of UiElement
 */
abstract class BaseElementBuilder implements ElementBuilder {
	/**
	 * @var array
	 */
	protected $params = array();

	/**
	 * @var class-string<ElementClass>
	 */
	protected $elementClass;

	/**
	 * @param class-string<\YahnisElsts\AdminMenuEditor\Customizable\Controls\UiElement> $elementClass
	 * @param array $params
	 */
	protected function __construct($elementClass, $params = array()) {
		$this->elementClass = $elementClass;
		$this->params = $params;
	}

	protected static function buildItems($items, $preserveKeys = false) {
		$results = array();
		foreach ($items as $key => $item) {
			if ( is_array($item) ) {
				//Flatten nested arrays of buildable things.
				$results = array_merge($results, self::buildItems($item, $preserveKeys));
				continue;
			}

			if ( $item instanceof ElementBuilder ) {
				$item = $item->build();
			} elseif ( !($item instanceof UiElement) ) {
				$typeString = is_object($item) ? get_class($item) : gettype($item);
				throw new \InvalidArgumentException(
					'Invalid item type for an element builder: ' . $typeString
				);
			}

			if ( $preserveKeys ) {
				$results[$key] = $item;
			} else {
				$results[] = $item;
			}
		}
		return $results;
	}

	public function id($string) {
		$this->params['id'] = $string;
		return $this;
	}

	public function getCustomId() {
		return isset($this->params['id']) ? $this->params['id'] : null;
	}

	/**
	 * @param string|callable $textOrCallback
	 * @return $this
	 */
	public function description($textOrCallback) {
		$this->params['description'] = $textOrCallback;
		return $this;
	}

	public function classes(...$cssClassNames) {
		return $this->addItemsToArrayParam('classes', $cssClassNames);
	}

	/**
	 * Add CSS class names if their values are truthy.
	 *
	 * @param array<string,mixed> $classEnabled ['class-a' => true, 'class-b' => false, ...]
	 * @return $this
	 */
	public function conditionalClasses($classEnabled) {
		return $this->classes(...array_keys(array_filter($classEnabled)));
	}

	public function styles($propertyPairs) {
		return $this->addItemsToArrayParam('style', $propertyPairs);
	}

	/**
	 * @param string $paramName
	 * @param $items
	 * @return $this
	 */
	protected function addItemsToArrayParam($paramName, $items) {
		if ( !isset($this->params[$paramName]) ) {
			$this->params[$paramName] = array();
		}
		$this->params[$paramName] = array_merge($this->params[$paramName], (array)$items);
		return $this;
	}

	/**
	 * Set one or more parameters for the element.
	 *
	 * Will overwrite any existing parameters with the same name.
	 *
	 * @param array<string,mixed> $additionalParams
	 * @return $this
	 */
	public function params($additionalParams) {
		$this->params = array_merge($this->params, $additionalParams);
		return $this;
	}

	/**
	 * Render the element only if the condition evaluates to true.
	 *
	 * @param bool|callable $condition
	 */
	public function onlyIf($condition) {
		$this->params['renderCondition'] = $condition;
		return $this;
	}

	/**
	 * @return ElementClass
	 */
	abstract public function build();
}
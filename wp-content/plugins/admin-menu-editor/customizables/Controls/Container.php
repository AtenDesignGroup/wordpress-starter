<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

abstract class Container extends UiElement implements \IteratorAggregate, ControlContainer {
	/**
	 * @var UiElement[]
	 */
	protected $children = [];

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var null|\YahnisElsts\AdminMenuEditor\Customizable\Controls\Tooltip
	 */
	protected $tooltip = null;

	public function __construct($title, $children = [], $params = []) {
		parent::__construct($params);
		$this->title = $title;
		foreach ($children as $child) {
			$this->add($child);
		}

		if ( isset($params['tooltip']) ) {
			$this->tooltip = $params['tooltip'];
		}
	}

	/**
	 * @param UiElement $child
	 * @return void
	 */
	public function add($child) {
		$this->children[] = $child;
	}

	/**
	 * @return UiElement[]
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Sort the container's children using the specified comparison function.
	 *
	 * @param callable(UiElement,UiElement):int $compareFunction
	 * @return void
	 */
	public function sortChildren($compareFunction) {
		usort($this->children, $compareFunction);
	}

	/**
	 * Recursively filter the container's children using the specified callback.
	 *
	 * The children list is replaced with the filtered list.
	 *
	 * @param callable(UiElement):bool $callback
	 * @return void
	 */
	public function recursiveFilterChildrenInPlace($callback) {
		foreach ($this->children as $key => $child) {
			//Depth-first traversal.
			if ( $child instanceof Container ) {
				$child->recursiveFilterChildrenInPlace($callback);
			}
			if ( $callback($child) === false ) {
				unset($this->children[$key]);
			}
		}

		//Re-index the array. Note: array_filter() would not help much since it does not re-index the array.
		$this->children = array_values($this->children);
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $newTitle
	 */
	public function setTitle($newTitle) {
		$this->title = $newTitle;
	}

	public function hasTitle() {
		return !empty($this->title);
	}

	/**
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Controls\Tooltip|null
	 */
	public function getTooltip() {
		return $this->tooltip;
	}

	/**
	 * @return bool
	 */
	public function hasTooltip() {
		return ($this->tooltip !== null);
	}

	/**
	 * Recursively search the container for a UI element that has the specified ID.
	 *
	 * @param string $id
	 * @return UiElement|null
	 */
	public function findChildById($id) {
		foreach ($this->children as $child) {
			if ( $child->getId() === $id ) {
				return $child;
			} else if ( $child instanceof Container ) {
				$result = $child->findChildById($id);
				if ( $result !== null ) {
					return $result;
				}
			}
		}
		return null;
	}

	public function isEmpty() {
		return empty($this->children);
	}

	public function hasChildren() {
		return !empty($this->children);
	}

	public function serializeForJs() {
		$result = parent::serializeForJs();
		if ( $this->hasTitle() ) {
			$result['title'] = $this->title;
		}

		if ( !empty($this->children) ) {
			$result['children'] = [];
			foreach ($this->children as $child) {
				$result['children'][] = $child->serializeForJs();
			}
		}
		return $result;
	}

	public function enqueueKoComponentDependencies() {
		parent::enqueueKoComponentDependencies();
		foreach ($this->children as $child) {
			$child->enqueueKoComponentDependencies();
		}
	}

	/** @noinspection PhpLanguageLevelInspection */
	#[\ReturnTypeWillChange]
	public function getIterator() {
		return new \ArrayIterator($this->children);
	}

	/**
	 * Recursively get all descendants of this container.
	 *
	 * @return \Generator
	 */
	public function getAllDescendants() {
		foreach ($this->children as $child) {
			yield $child;
			if ( $child instanceof ControlContainer ) {
				yield from $child->getAllDescendants();
			}
		}
	}
}
<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

/**
 * Interface ControlContainer.
 *
 * Indicates that a UI element can contain children that are {@link Control}
 * instances. The class that implements this interface does not necessarily
 * have to be a subclass of {@link Control} itself.
 */
interface ControlContainer {
	/**
	 * Get the direct children of this container.
	 *
	 * @return iterable<Control>
	 */
	public function getChildren();

	/**
	 * Recursively get all descendants of this container.
	 *
	 * @return iterable<Control>
	 */
	public function getAllDescendants();
}
<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Rendering;

use YahnisElsts\AdminMenuEditor\Customizable\Controls\Control;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\ControlGroup;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\Section;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\Tooltip;

abstract class Renderer {
	/**
	 * @param \YahnisElsts\AdminMenuEditor\Customizable\Controls\InterfaceStructure $structure
	 * @return void
	 */
	public function renderStructure($structure) {
		foreach ($structure->getAsSections() as $section) {
			if ( $section->shouldRender() ) {
				$this->renderSection($section);
			}
		}
	}

	/**
	 * @param Section $section
	 * @return void
	 */
	abstract public function renderSection($section);

	protected function renderSectionChildren(Section $section) {
		foreach ($section->getChildren() as $child) {
			if ( !$child->shouldRender() ) {
				continue;
			}

			if ( $child instanceof Section ) {
				$this->renderChildSection($child);
			} else if ( $child instanceof ControlGroup ) {
				$this->renderControlGroup($child);
			} else if ( $child instanceof Control ) {
				$this->renderUngroupedControl($child);
			} else {
				throw new \RuntimeException(
					'Unexpected child type: ' .
					(is_object($child) ? get_class($child) : gettype($child))
				);
			}
		}
	}

	/**
	 * @param Section $section
	 * @return void
	 */
	protected function renderChildSection($section) {
		$this->renderSection($section);
	}

	/**
	 * @param ControlGroup $group
	 * @return void
	 */
	abstract protected function renderControlGroup($group);

	protected function renderGroupChildren(ControlGroup $group, $parentContext = null) {
		foreach ($group->getChildren() as $child) {
			if ( !$child->shouldRender() ) {
				continue;
			}

			if ( $child instanceof Control ) {
				$this->renderControl($child, $parentContext);
			} else if ( $child instanceof ControlGroup ) {
				$this->renderChildControlGroup($child);
			} else {
				throw new \RuntimeException(
					'Unexpected child type: ' .
					(is_object($child) ? get_class($child) : gettype($child))
				);
			}
		}
	}

	protected function renderChildControlGroup(ControlGroup $group) {
		$this->renderControlGroup($group);
	}

	/**
	 * @param Control $control
	 */
	protected function renderUngroupedControl($control) {
		$params = [];
		$controlId = $control->getId();
		if ( $controlId ) {
			$params['id'] = 'ame_control_group-' . $controlId;
		}

		$tempGroup = new ControlGroup($control->getAutoGroupTitle(), [$control], $params);
		$this->renderControlGroup($tempGroup);
	}

	/**
	 * @param Control $control
	 * @param null|mixed $parentContext Arbitrary context data from the parent control.
	 */
	public function renderControl($control, $parentContext = null) {
		$control->renderContent($this);
	}

	abstract public function renderTooltipTrigger(Tooltip $tooltip);

	/**
	 * @param string $containerSelector The CSS selector for the element that contains
	 *                                  the rendered controls. Typically, this is the form element.
	 * @return void
	 */
	public function enqueueDependencies($containerSelector = '') {
		//No dependencies by default.
	}
}
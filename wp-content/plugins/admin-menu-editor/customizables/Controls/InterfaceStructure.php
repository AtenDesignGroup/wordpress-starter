<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

class InterfaceStructure extends Container {
	/**
	 * Get all top-level elements organized into sections.
	 *
	 * @return Section[]
	 */
	public function getAsSections() {
		$currentAnonymousSection = null;

		$sections = array();
		foreach ($this->children as $child) {
			if ( $child instanceof Section ) {
				$sections[] = $child;
				$currentAnonymousSection = null;
			} else {
				//Put all non-section elements into an anonymous section.
				if ( $currentAnonymousSection === null ) {
					$currentAnonymousSection = new Section('');
					$sections[] = $currentAnonymousSection;
				}
				$currentAnonymousSection->add($child);
			}
		}
		return $sections;
	}

	protected function getJsUiElementType() {
		return 'structure';
	}
}
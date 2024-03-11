<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls\RadioGroup;

class RadioGroupBuilder extends ControlBuilder {
	public function __construct($settings = [], $params = []) {
		parent::__construct(RadioGroup::class, $settings, $params);
	}

	public function choiceChild($value, $childControl) {
		if ( !is_string($value) ) {
			//Because we use the value as an array key, it must be a string
			//to avoid potential collisions (1 vs 1.3 vs '1') and other problems.
			throw new \InvalidArgumentException('At the moment, ' . __FUNCTION__ . '() only supports string values.');
		}

		if ( !isset($this->params['choiceChildren']) ) {
			$this->params['choiceChildren'] = [];
		}
		$this->params['choiceChildren'][$value] = $childControl;
		return $this;
	}

	public function build() {
		if ( isset($this->params['choiceChildren']) ) {
			$this->params['choiceChildren'] = self::buildItems($this->params['choiceChildren'], true);
		}
		return parent::build();
	}
}
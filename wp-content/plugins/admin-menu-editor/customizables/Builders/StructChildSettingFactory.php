<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Settings;

class StructChildSettingFactory extends SettingFactory {
	/**
	 * @var \YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractStructSetting
	 */
	private $struct;

	private $idToChildKey = [];

	public function __construct(Settings\AbstractStructSetting $parent, array $defaults = []) {
		parent::__construct($parent->getStore(), $defaults);
		$this->struct = $parent;
	}

	protected function idFrom($path) {
		$id = $this->struct->makeChildId(str_replace('.', '-', $path));
		$this->idToChildKey[$id] = $path;
		return $id;
	}

	public function getChildKeyFromId($id) {
		return isset($this->idToChildKey[$id]) ? $this->idToChildKey[$id] : null;
	}
}
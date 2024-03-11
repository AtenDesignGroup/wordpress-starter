<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Storage;

//TODO: A module could have a method that sets  up settings.
class ModuleSettings extends AbstractSettingsDictionary {
	protected $settingCreationCallback;

	public function __construct(
		$optionName,
		$scope = ScopedOptionStorage::GLOBAL_SCOPE,
		$defaults = array(),
		$settingCreationCallback = null,
		$jsonSerializationEnabled = false,
		$lastModifiedTimeEnabled = false
	) {
		$store = new ScopedOptionStorage($optionName, $scope);
		$store->setJsonSerialization($jsonSerializationEnabled);

		$this->defaults = $defaults;
		$this->settingCreationCallback = $settingCreationCallback;

		parent::__construct($store, $optionName . '--', $lastModifiedTimeEnabled);
	}

	protected function createDefaults() {
		return $this->defaults;
	}

	protected function createSettings() {
		if ( $this->settingCreationCallback !== null ) {
			$settings = call_user_func($this->settingCreationCallback, $this);
			//Index by ID.
			$results = array();
			foreach ($settings as $setting) {
				$results[$setting->getId()] = $setting;
			}
			return $results;
		}
		return array();
	}

	/**
	 * @return StorageInterface
	 */
	public function getStore() {
		return $this->store;
	}
}

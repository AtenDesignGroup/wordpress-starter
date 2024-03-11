<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Storage;

class ActiveMenuConfiguration implements StorageInterface {
	/**
	 * @var \WPMenuEditor
	 */
	private $menuEditor;

	/**
	 * @var null|MenuConfigurationWrapper
	 */
	private $wrapper = null;

	public function __construct(\WPMenuEditor $menuEditor) {
		$this->menuEditor = $menuEditor;
	}

	/**
	 * @param \WPMenuEditor $menuEditor
	 * @return self
	 */
	public static function getInstance($menuEditor = null) {
		static $instance = null;
		if ( $instance === null ) {
			if ( $menuEditor === null ) {
				$menuEditor = $GLOBALS['wp_menu_editor'];
			}
			$instance = new self($menuEditor);
		}
		return $instance;
	}

	/**
	 * @return MenuConfigurationWrapper
	 */
	private function lazyInit() {
		if ( $this->wrapper !== null ) {
			return $this->wrapper;
		}

		$actualConfigId = $this->menuEditor->get_loaded_menu_config_id();
		if ( empty($actualConfigId) ) {
			$this->menuEditor->load_custom_menu();
			$actualConfigId = $this->menuEditor->get_loaded_menu_config_id();
			if ( empty($actualConfigId) ) {
				$actualConfigId = 'site';
			}
		}

		$this->wrapper = MenuConfigurationWrapper::getStoreByConfigId(
			$actualConfigId,
			$this->menuEditor
		);
		return $this->wrapper;
	}

	public function getValue($defaultValue = null) {
		return $this->lazyInit()->getValue($defaultValue);
	}

	public function setValue($value) {
		return $this->lazyInit()->setValue($value);
	}

	public function getPath($path, $defaultValue = null) {
		return $this->lazyInit()->getPath($path, $defaultValue);
	}

	public function setPath($path, $value) {
		return $this->lazyInit()->setPath($path, $value);
	}

	public function deleteValue() {
		 $this->lazyInit()->deleteValue();
	}

	public function deletePath($path) {
		$this->lazyInit()->deletePath($path);
	}

	public function buildSlot($path) {
		return $this->lazyInit()->buildSlot($path);
	}

	public function save() {
		$this->lazyInit()->save();
	}

	public function getStorageKey() {
		return $this->lazyInit()->getStorageKey();
	}

	public function addReadAliases($aliases) {
		$this->lazyInit()->addReadAliases($aliases);
	}

	public function getSmallestSavable() {
		return $this->lazyInit()->getSmallestSavable();
	}

	public function setPreviewValue($value) {
		$this->lazyInit()->setPreviewValue($value);
	}

	public function setPreviewByPath($path, $value) {
		$this->lazyInit()->setPreviewByPath($path, $value);
	}
}
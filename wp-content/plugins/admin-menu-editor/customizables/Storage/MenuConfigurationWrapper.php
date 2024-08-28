<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Storage;

class MenuConfigurationWrapper extends LazyArrayStorage implements StorageInterface {
	/**
	 * @var \WPMenuEditor
	 */
	private $menuEditor;
	/**
	 * @var string|null
	 */
	private $menuConfigId;

	private static $wrappersById = [];

	public function __construct(\WPMenuEditor $menuEditor, $menuConfigId) {
		$this->menuEditor = $menuEditor;
		$this->menuConfigId = $menuConfigId;
		parent::__construct();
	}

	protected function loadData() {
		$data = $this->menuEditor->load_custom_menu($this->menuConfigId);
		if ( $data === null ) {
			$data = [];
		}
		return $data;
	}

	protected function storeData($newData) {
		$this->menuEditor->set_custom_menu($newData, $this->menuConfigId);
	}

	protected function deleteStoredData() {
		throw new \LogicException('Cannot delete the menu configuration via this interface.');
	}

	public function setValue($value) {
		throw new \LogicException('Cannot replace the menu configuration via this interface.');
	}

	/**
	 * @param string $menuConfigId
	 * @param \WPMenuEditor|null $menuEditor
	 * @return self
	 */
	public static function getStoreByConfigId($menuConfigId, $menuEditor = null) {
		if ( isset(self::$wrappersById[$menuConfigId]) ) {
			return self::$wrappersById[$menuConfigId];
		}
		if ( $menuEditor === null ) {
			$menuEditor = $GLOBALS['wp_menu_editor'];
		}

		$wrapper = new self($menuEditor, $menuConfigId);
		self::$wrappersById[$menuConfigId] = $wrapper;
		return $wrapper;
	}

	/**
	 * @param string|null $optionalConfigId
	 * @param \WPMenuEditor|null $menuEditor
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface
	 */
	public static function getStore($optionalConfigId = null, $menuEditor = null) {
		if ( $optionalConfigId === null ) {
			return ActiveMenuConfiguration::getInstance($menuEditor);
		} else {
			return self::getStoreByConfigId($optionalConfigId, $menuEditor);
		}
	}
}
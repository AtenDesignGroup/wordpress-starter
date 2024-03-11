<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Storage;

use ameMultiDictionary;

class NullStorage implements StorageInterface {
	private $isPreviewing;
	private $previewValue;

	public function getValue($defaultValue = null) {
		return $this->isPreviewing ? $this->previewValue : $defaultValue;
	}

	public function getPath($path, $defaultValue = null) {
		if ( $this->isPreviewing && is_array($this->previewValue) ) {
			return ameMultiDictionary::get($this->previewValue, $path, $defaultValue);
		}
		return $defaultValue;
	}

	public function setValue($value) {
		//Do nothing.
		return true;
	}

	public function setPath($path, $value) {
		//Do nothing.
		return true;
	}

	public function deleteValue() {
		//Also do nothing here.
	}

	public function deletePath($path) {
		//Do nothing.
	}

	public function buildSlot($path) {
		return new Slot($this, $path);
	}

	public function save() {
		//Do nothing.
	}

	public function getStorageKey() {
		return '{NullStorage}';
	}

	public function addReadAliases($aliases) {
		//We store nothing, so we don't need aliases.
	}

	public function getSmallestSavable() {
		return $this;
	}

	public function setPreviewValue($value) {
		$this->isPreviewing = true;
		$this->previewValue = $value;
	}

	public function setPreviewByPath($path, $value) {
		$this->isPreviewing = true;

		if ( !is_array($this->previewValue) ) {
			$this->previewValue = [];
		}

		ameMultiDictionary::set($this->previewValue, $path, $value);
	}
}
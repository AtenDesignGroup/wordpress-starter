<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Storage;

use ameMultiDictionary;

/**
 * A simple storage implementation that stores data in a PHP array.
 *
 * This class has extension points for lazy-loading and saving data,
 * but it doesn't implement them by default. Subclasses can override
 * methods like storeData() to implement these features. On its own,
 * this class acts as an in-memory store.
 */
class LazyArrayStorage extends StorageMethods implements StorageInterface {
	/**
	 * @var array|null
	 */
	protected $data = null;
	protected $doneLoading = false;
	protected $isMarkedForDeletion = false;

	/**
	 * @param array|null $initialData
	 */
	public function __construct($initialData = null) {
		//This just makes the constructor public.
		parent::__construct();

		if ( $initialData !== null ) {
			$this->data = $initialData;
			$this->doneLoading = true;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function setValue($value) {
		$this->data = $value;
		$this->doneLoading = true;
		$this->isMarkedForDeletion = false;
		return true;
	}

	public function deleteValue() {
		$this->data = null;
		$this->doneLoading = true;
		$this->isMarkedForDeletion = true;
	}

	/**
	 * @inheritDoc
	 */
	public function setPath($path, $value) {
		$this->lazyLoad();
		$this->isMarkedForDeletion = false;

		$path = $this->parsePath($path);

		//Data could be NULL if it was previously deleted.
		if ( $this->data === null ) {
			$this->data = [];
		}

		return ameMultiDictionary::set($this->data, $path, $value);
	}

	public function deletePath($path) {
		$this->lazyLoad();

		$path = $this->parsePath($path);
		//An empty path is invalid. This method can't delete the entire storage.
		if ( empty($path) ) {
			throw new \InvalidArgumentException('Path cannot be empty');
		}

		ameMultiDictionary::delete($this->data, $path);
	}

	/**
	 * @inheritDoc
	 */
	public function buildSlot($path) {
		return new Slot($this, $path);
	}

	/**
	 * @inheritDoc
	 */
	protected function rawGetPath($path, $defaultValue = null) {
		$this->lazyLoad();
		return ameMultiDictionary::get($this->data, $path, $defaultValue);
	}

	protected function rawGetValue($defaultValue = null) {
		$this->lazyLoad();
		return ($this->data !== null) ? $this->data : $defaultValue;
	}

	protected function lazyLoad() {
		if ( $this->doneLoading ) {
			return;
		}

		$this->doneLoading = true;
		$this->isMarkedForDeletion = false;
		$this->data = $this->loadData();
	}

	public function save() {
		if ( $this->isMarkedForDeletion && ($this->data === null) ) {
			$this->deleteStoredData();
		} else {
			$this->lazyLoad();
			$this->storeData($this->data);
		}
	}

	/**
	 * Save data to the database or other persistent storage.
	 *
	 * @param array $newData
	 * @return void
	 */
	protected function storeData($newData) {
		//Override in subclasses.
	}

	/**
	 * Load data from persistent storage.
	 *
	 * @return array|null
	 */
	protected function loadData() {
		//Override in subclasses.
		return [];
	}

	/**
	 * Delete data in persistent storage.
	 *
	 * @return void
	 */
	protected function deleteStoredData() {
		//Override in subclasses.
	}
}
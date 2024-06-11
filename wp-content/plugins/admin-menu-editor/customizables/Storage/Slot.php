<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Storage;

class Slot extends StorageMethods implements StorageInterface {
	/**
	 * @var StorageInterface
	 */
	protected $store;
	/**
	 * @var array
	 */
	protected $pathPrefix;

	/**
	 * @param StorageInterface $store
	 * @param string|string[] $path
	 */
	public function __construct(StorageInterface $store, $path) {
		parent::__construct();
		$this->store = $store;
		$this->pathPrefix = $this->parsePath($path);
	}

	protected function rawGetValue($defaultValue = null) {
		return $this->store->getPath($this->pathPrefix, $defaultValue);
	}

	public function setValue($value) {
		return $this->store->setPath($this->pathPrefix, $value);
	}

	protected function rawGetPath($path, $defaultValue = null) {
		return $this->store->getPath(
			$this->addPrefixToPath($this->pathPrefix, $path),
			$defaultValue
		);
	}

	public function setPath($path, $value) {
		return $this->store->setPath(
			$this->addPrefixToPath($this->pathPrefix, $path),
			$value
		);
	}

	public function deleteValue() {
		$this->store->deletePath($this->pathPrefix);
	}

	public function deletePath($path) {
		$this->store->deletePath(
			$this->addPrefixToPath($this->pathPrefix, $path)
		);
	}

	public function buildSlot($path) {
		//We could simply return "new Slot($this, $path)", but that would lead to
		//unnecessary nested getPath() calls when retrieving the value. Using
		//the underlying storage should be more efficient.
		return new Slot(
			$this->store,
			$this->addPrefixToPath($this->pathPrefix, $path)
		);
	}

	public function addReadAliases($aliases) {
		/*
		 * Forward the aliases to the underlying storage, but prefix them with
		 * the path of this slot.
		 *
		 * We could store aliases on the slot, but then buildSlot() would not be able
		 * to use the underlying storage because that storage would not know about
		 * the aliases. Using the $store only when there are no aliases also wouldn't
		 * work sometimes because aliases could be added later.
		*/
		foreach($aliases as $alias => $path) {
			$nestedAlias = $this->addPrefixToPath($this->pathPrefix, $alias);
			$nestedPath = $this->addPrefixToPath($this->pathPrefix, $path);

			$this->store->addReadAliases([
				implode(self::PATH_SEPARATOR, $nestedAlias) => implode(self::PATH_SEPARATOR, $nestedPath),
			]);
		}
	}

	public function getSmallestSavable() {
		return $this->store->getSmallestSavable();
	}

	public function setPreviewValue($value) {
		return $this->store->setPreviewByPath($this->pathPrefix, $value);
	}

	public function setPreviewByPath($path, $value) {
		return $this->store->setPreviewByPath(
			$this->addPrefixToPath($this->pathPrefix, $path),
			$value
		);
	}
}
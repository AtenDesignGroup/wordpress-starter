<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Storage;

interface StorageInterface {
	const PATH_SEPARATOR = '.';

	public function getValue($defaultValue = null);

	/**
	 * @param $value
	 * @return boolean
	 */
	public function setValue($value);

	public function getPath($path, $defaultValue = null);

	/**
	 * @param $path
	 * @param $value
	 * @return boolean
	 */
	public function setPath($path, $value);

	public function deleteValue();

	public function deletePath($path);

	/**
	 * @param string|array $path
	 * @return Slot
	 */
	public function buildSlot($path);

	/**
	 * Save data. This can be a no-op for implementations that
	 * immediately save changes in the database.
	 *
	 * @return void
	 */
	public function save();

	/**
	 * @return string
	 */
	public function getStorageKey();

	/**
	 * @param array<string,string|string[]> $aliases
	 */
	public function addReadAliases($aliases);

	/**
	 * Get the closest StorageInterface in this instance's hierarchy that
	 * can be efficiently saved.
	 *
	 * For example, you can have a Slot that represents a specific key in
	 * an associative array, and the array is stored as a WP option. Technically,
	 * you can "save" the individual Slot, but it will actually cause the whole
	 * array to be written to the database. This is inefficient if you need to
	 * update multiple slots that share the same underlying array because you
	 * will end up rewriting the same option multiple times.
	 *
	 * Instead, this method should return the underlying StorageInterface,
	 * and you should call save() on that.
	 *
	 * If you have a deep hierarchy, this method should not search for the root,
	 * but return the closest storage that can be saved as a unit (which could
	 * be $this), or at least the one that would trigger the lowest number of writes.
	 *
	 * Implementations that don't have a parent should just return $this.
	 *
	 * @return StorageInterface
	 */
	public function getSmallestSavable();

	public function setPreviewValue($value);

	public function setPreviewByPath($path, $value);
}
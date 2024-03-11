<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Storage;

use ameMultiDictionary;

abstract class StorageMethods implements StorageInterface {
	protected $undefinedMarker;

	/**
	 * @var array<string,string|string[]> When one path doesn't exist, try reading another path.
	 */
	private $readAliases = array();

	protected $isPreviewing = false;
	protected $previewData = [];

	protected function __construct() {
		$this->undefinedMarker = new \StdClass();
	}

	protected function parsePath($path) {
		return AmeMultiDictionary::parsePath($path, StorageInterface::PATH_SEPARATOR);
	}

	/**
	 * @param array $prefix
	 * @param string|array $path
	 * @return array
	 */
	protected function addPrefixToPath($prefix, $path) {
		return AmeMultiDictionary::addPrefixToPath($prefix, $path, StorageInterface::PATH_SEPARATOR);
	}

	public function save() {
		//Does nothing. Subclasses can override this to save changes.
	}

	public function getStorageKey() {
		return 'not_applicable';
	}

	public function getPath($path, $defaultValue = null) {
		$path = $this->parsePath($path);
		//Empty path = return all data.
		if ( empty($path) ) {
			return $this->getValue($defaultValue);
		}

		//Try the specified path.
		$result = $this->rawGetPath($path, $this->undefinedMarker);
		if ( ($result === $this->undefinedMarker) && !empty($this->readAliases) ) {
			//Try aliases.
			$stringPath = is_array($path)
				? implode(StorageInterface::PATH_SEPARATOR, $path)
				: $path;

			if ( isset($this->readAliases[$stringPath]) ) {
				$result = $this->rawGetPath(
					$this->readAliases[$stringPath],
					$defaultValue
				);
			}
		}

		if ( $this->isPreviewing ) {
			$previewValue = ameMultiDictionary::get($this->previewData, $path, $this->undefinedMarker);
			if ( $previewValue !== $this->undefinedMarker ) {
				if ( is_array($previewValue) && is_array($result) ) {
					//Merge preview data with existing settings.
					$result = $this->mergeArraysRecursively($result, $previewValue);
				} else {
					//Just override the setting.
					return $previewValue;
				}
			}
		}

		if ( $result !== $this->undefinedMarker ) {
			return $result;
		}
		//Fall back to default value.
		return $defaultValue;
	}

	/**
	 * Get the value at a path without using aliases.
	 *
	 * @param string[] $path Should always be a pre-parsed array, not a string.
	 * @param mixed|null $defaultValue
	 * @return mixed
	 */
	abstract protected function rawGetPath($path, $defaultValue = null);

	public function getValue($defaultValue = null) {
		$result = $this->rawGetValue($defaultValue);
		if ( $this->isPreviewing ) {
			if ( is_array($result) && is_array($this->previewData) ) {
				$result = array_merge($result, $this->previewData);
			} else {
				$result = $this->previewData;
			}
		}
		return $result;
	}

	abstract protected function rawGetValue($defaultValue = null);

	/**
	 * @param array<string,string|string[]> $aliases
	 * @return void
	 */
	public function addReadAliases($aliases) {
		$this->readAliases = array_merge($this->readAliases, $aliases);
	}

	public function getSmallestSavable() {
		return $this;
	}

	public function setPreviewByPath($path, $value) {
		$this->isPreviewing = true;

		$path = $this->parsePath($path);
		if ( empty($path) ) {
			$this->previewData = $value;
		} else {
			ameMultiDictionary::set($this->previewData, $path, $value);
		}
	}

	public function setPreviewValue($value) {
		$this->isPreviewing = true;
		$this->previewData = $value;
	}

	/**
	 * Merge two arrays recursively.
	 *
	 * This method differs from array_merge_recursive() in how it handles elements
	 * that have the same key in both arrays. array_merge_recursive() will combine
	 * the values into a new nested array. This method will do the same if both values
	 * are arrays. However, if either value is not an array, it will just overwrite
	 * the first value with the second.
	 *
	 * Example:
	 *
	 * $a = array('foo' => 1);
	 * $b = array('foo' => 2);
	 * $merged = array_merge_recursive($a, $b);
	 * //Result: array('foo' => array(1, 2))
	 *
	 * $merged = $this->mergeArraysRecursively($a, $b);
	 * //Result: array('foo' => 2)
	 *
	 * @param array $base
	 * @param array $input
	 * @return array
	 */
	protected static function mergeArraysRecursively($base, $input) {
		if ( is_array($base) && is_array($input) ) {
			foreach ($input as $key => $value) {
				if (
					array_key_exists($key, $base)
					&& is_array($base[$key])
					&& is_array($value)
				) {
					$base[$key] = self::mergeArraysRecursively($base[$key], $value);
				} else {
					$base[$key] = $value;
				}
			}
			return $base;
		}
		return $input;
	}
}
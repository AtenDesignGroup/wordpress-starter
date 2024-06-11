<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Storage;

use WPMenuEditor;

class ScopedOptionStorage extends LazyArrayStorage implements StorageInterface, CompressedStorage {
	const GLOBAL_SCOPE = 'global';
	const SITE_SCOPE = 'site';

	const COMPRESSED_VALUE_PREFIX = 'gzcompress:';

	protected $scope = self::SITE_SCOPE;
	protected $optionName;

	protected $data = null;
	protected $doneLoading = false;
	protected $isMarkedForDeletion = false;

	protected $jsonSerializationEnabled = false;
	protected $compressionSupported = true;
	protected $compressionEnabled = false;

	public function __construct(
		$optionName,
		$scope = self::SITE_SCOPE,
		$compressionEnabled = false
	) {
		parent::__construct();
		$this->optionName = $optionName;
		$this->scope = $scope;
		$this->compressionEnabled = $compressionEnabled;
	}

	protected function loadData() {
		$defaultValue = null;

		if ( $this->scope === self::SITE_SCOPE ) {
			$value = get_option($this->optionName, $defaultValue);
		} else {
			$value = get_site_option($this->optionName, $defaultValue);
		}

		//Decompress gzipped data.
		if (
			$this->compressionSupported
			&& is_string($value)
			&& (substr($value, 0, strlen(self::COMPRESSED_VALUE_PREFIX)) === self::COMPRESSED_VALUE_PREFIX)
			&& function_exists('gzuncompress')
		) {
			//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- For back-compat with older plugin versions.
			$value = unserialize(gzuncompress(base64_decode(
				substr($value, strlen(self::COMPRESSED_VALUE_PREFIX)
				))));
		}

		//Parse JSON.
		if ( $this->jsonSerializationEnabled && is_string($value) ) {
			$value = json_decode($value, true);
		}

		return $value;
	}

	protected function storeData($newData) {
		$storedData = $newData;

		//Optionally, serialize to JSON.
		if ( $this->jsonSerializationEnabled ) {
			$storedData = wp_json_encode($storedData);
		}

		//Compress the data.
		if (
			$this->compressionSupported
			&& $this->compressionEnabled
			&& function_exists('gzcompress')
		) {
			//This presents a migration risk: if the database is migrated from a site
			//that has the zlib extension to one that does not, the plugin won't be able
			//to load the compressed data.
			//phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			$storedData = self::COMPRESSED_VALUE_PREFIX
				. base64_encode(gzcompress(serialize($storedData)));
			//phpcs:enable
		}

		if ( ($this->scope === self::GLOBAL_SCOPE) && is_multisite() ) {
			if ( class_exists('\\WPMenuEditor', false) ) {
				return WPMenuEditor::atomic_update_site_option($this->optionName, $storedData);
			}
			return update_site_option($this->optionName, $storedData);
		} else {
			return update_option($this->optionName, $storedData);
		}
	}

	protected function deleteStoredData() {
		if ( ($this->scope === self::GLOBAL_SCOPE) && is_multisite() ) {
			return delete_site_option($this->optionName);
		} else {
			return delete_option($this->optionName);
		}
	}

	/**
	 * Toggle gzip compression.
	 *
	 * This only works if the zlib extension is available. If it's not, the method will
	 * always return false.
	 *
	 * @param boolean $enabled
	 * @return boolean Whether compression was actually enabled or not.
	 */
	public function setCompressionEnabled($enabled) {
		if ( !function_exists('gzcompress') ) {
			$this->compressionEnabled = false;
		} else {
			$this->compressionEnabled = $enabled;
		}
		return $this->compressionEnabled;
	}

	public function setJsonSerialization($enabled) {
		$this->jsonSerializationEnabled = $enabled;
	}

	public function getStorageKey() {
		return $this->optionName;
	}
}
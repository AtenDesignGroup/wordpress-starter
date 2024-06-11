<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Storage;

interface CompressedStorage extends StorageInterface {
	/**
	 * @param boolean $enabled
	 * @return boolean
	 */
	public function setCompressionEnabled($enabled);
}
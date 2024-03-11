<?php

namespace YahnisElsts\AdminMenuEditor\Customizable;

use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

abstract class Customizable {
	protected $id;

	/**
	 * @var string
	 */
	protected $label = '';
	/**
	 * @var string
	 */
	protected $description = '';
	/**
	 * @var null|string
	 */
	protected $groupTitle = null;

	/**
	 * @var StorageInterface
	 */
	protected $store = null;

	public function __construct($id, StorageInterface $store = null, $params = array()) {
		$this->id = $id;
		$this->store = $store;

		$this->label = isset($params['label']) ? $params['label'] : (!empty($this->label) ? $this->label : $id);
		if ( isset($params['description']) ) {
			$this->description = $params['description'];
		}
		if ( isset($params['groupTitle']) ) {
			$this->groupTitle = $params['groupTitle'];
		}
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return string|null
	 */
	public function getCustomGroupTitle() {
		return $this->groupTitle;
	}

	public function getStore() {
		return $this->store;//todo: remove debug code
	}
}
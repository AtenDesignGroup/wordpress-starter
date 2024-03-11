<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

abstract class NumericSetting extends Setting {
	protected $defaultValue = 0;
	/**
	 * @var null|float|int
	 */
	protected $minValue = null;
	/**
	 * @var null|float|int
	 */
	protected $maxValue = null;

	public function __construct($id, StorageInterface $store = null, $params = []) {
		parent::__construct($id, $store, $params);

		$this->minValue = isset($params['minValue']) ? $params['minValue'] : $this->minValue;
		$this->maxValue = isset($params['maxValue']) ? $params['maxValue'] : $this->maxValue;
	}

	public function validate($errors, $value, $stopOnFirstError = false) {
		if ( $this->canTreatAsNull($value) ) {
			return null;
		}

		if ( !is_numeric($value) ) {
			$errors->add('not_numeric', 'Value must be a number');
			return $errors;
		}

		$numValue = floatval($value);
		if ( ($this->minValue !== null) && ($numValue < $this->minValue) ) {
			$errors->add('min_value', 'Value must be ' . $this->minValue . ' or greater');
		}
		if ( ($this->maxValue !== null) && ($numValue > $this->maxValue) ) {
			$errors->add('max_value', 'Value must be ' . $this->maxValue . ' or less');
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}
		return $numValue;
	}

	/**
	 * @return float|int|null
	 */
	public function getMinValue() {
		return $this->minValue;
	}

	/**
	 * @return float|int|null
	 */
	public function getMaxValue() {
		return $this->maxValue;
	}

	public function serializeValidationRules() {
		$result = [];
		if ( $this->isNullable() ) {
			$result['isNullable'] = true;
			$result['convertEsToNull'] = true;
		}

		$parserConfig = [];
		if ( $this->minValue !== null ) {
			$parserConfig['min'] = $this->minValue;
		}
		if ( $this->maxValue !== null ) {
			$parserConfig['max'] = $this->maxValue;
		}
		$result['parsers'] = [['numeric', $parserConfig]];

		return $result;
	}
}
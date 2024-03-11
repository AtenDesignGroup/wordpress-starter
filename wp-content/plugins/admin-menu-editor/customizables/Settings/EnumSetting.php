<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Controls\ChoiceControlOption;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

/**
 * Enum
 *
 * Note that if you want to allow NULL, it must be explicitly included as one
 * of the possible values. Only setting the default value to NULL is not enough.
 */
class EnumSetting extends Setting {
	/**
	 * @var array
	 */
	protected $enumValues = array();
	protected $choiceDetails = array();

	protected $valueEnabled = array();
	protected $valueStateCallback = null;

	public function __construct($id, StorageInterface $store, $enumValues, $params = array()) {
		if ( empty($enumValues) ) {
			throw new \InvalidArgumentException('Enum must have at least one possible value');
		}

		parent::__construct($id, $store, $params);
		$this->enumValues = array_values($enumValues);

		if ( !in_array($this->defaultValue, $this->enumValues) ) {
			$this->defaultValue = reset($this->enumValues);
		}
	}

	public function encodeForForm($value) {
		return wp_json_encode($value);
	}

	public function decodeSubmittedValue($value) {
		if ( is_string($value) ) {
			return @json_decode($value, true);
		}
		return parent::decodeSubmittedValue($value);
	}

	public function validate($errors, $value, $stopOnFirstError = false) {
		if ( $this->canTreatAsNull($value) ) {
			return null;
		}

		if ( !in_array($value, $this->enumValues) ) {
			$errors->add(
				'invalid_value',
				'Value must be one of: ' . implode(', ', $this->enumValues)
				. '. Received: ' . wp_json_encode($value)
			);
			return $errors;
		}

		if ( !$this->isChoiceEnabled($value) ) {
			$errors->add('disabled_value', 'That option is currently not allowed');
			return $errors;
		}

		return $value;
	}

	public function isChoiceEnabled($value) {
		if ( !in_array($value, $this->enumValues) ) {
			return false;
		}

		$safeValue = $this->encodeForForm($value);
		if ( isset($this->valueEnabled[$safeValue]) ) {
			$decider = $this->valueEnabled[$safeValue];
			if ( is_scalar($decider) ) {
				return (bool)$decider;
			} elseif ( is_callable($decider) ) {
				return call_user_func($decider, $value);
			}
		}

		if ( isset($this->valueStateCallback) ) {
			return call_user_func($this->valueStateCallback, $value);
		}
		return true;
	}

	/**
	 * @param mixed $value
	 * @param string|null $label
	 * @param string|null $description
	 * @param bool|callable|null $state
	 * @param string|null $icon
	 * @return EnumSetting
	 */
	public function describeChoice($value, $label, $description = '', $state = null, $icon = null) {
		$safeValue = $this->encodeForForm($value);
		$this->choiceDetails[$safeValue] = array(
			'label'       => $label,
			'description' => $description,
			'icon'        => $icon,
		);
		if ( $state !== null ) {
			$this->valueEnabled[$safeValue] = $state;
		}
		return $this;
	}

	/**
	 * Automatically generate dropdown/radio/etc options from the setting's
	 * possible values.
	 *
	 * Will use custom labels/descriptions if available.
	 *
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Controls\ChoiceControlOption[]
	 */
	public function generateChoiceOptions() {
		$results = array();
		foreach ($this->enumValues as $value) {
			$encodedValue = $this->encodeForForm($value);
			if ( array_key_exists($encodedValue, $this->choiceDetails) ) {
				$results[] = new ChoiceControlOption(
					$value,
					$this->choiceDetails[$encodedValue]['label'],
					array(
						'description' => $this->choiceDetails[$encodedValue]['description'],
						'enabled'     => $this->isChoiceEnabled($value),
						'icon'        => $this->choiceDetails[$encodedValue]['icon'],
					)
				);
			} else {
				if ( $value === null ) {
					$label = 'Default';
				} else {
					$label = is_string($value) ? $value : wp_json_encode($value);
					$label = ucwords(preg_replace('/[_-]+/', ' ', $label));
				}
				$results[] = new ChoiceControlOption($value, $label, array(
					'enabled' => $this->isChoiceEnabled($value),
				));
			}
		}

		return $results;
	}
}
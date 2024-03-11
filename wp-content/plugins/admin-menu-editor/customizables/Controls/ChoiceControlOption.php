<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\HtmlHelper;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\Setting;

class ChoiceControlOption {
	public $value;
	public $label;
	public $description = '';
	public $enabled = true;
	public $icon = null;

	/**
	 * @param mixed|null $value
	 * @param string|null $label
	 * @param array $params
	 */
	public function __construct($value, $label = null, $params = []) {
		$this->value = $value;
		$this->label = ($label !== null) ? $label : $value;
		if ( isset($params['description']) ) {
			$this->description = $params['description'];
		}
		if ( array_key_exists('enabled', $params) ) {
			$this->enabled = (bool)($params['enabled']);
		}
		if ( isset($params['icon']) ) {
			$this->icon = $params['icon'];
		}
	}

	public function serializeForJs() {
		$result = [
			'value' => $this->value,
			'label' => $this->label,
		];
		if ( $this->description !== '' ) {
			$result['description'] = $this->description;
		}
		if ( !$this->enabled ) {
			$result['enabled'] = false;
		}
		if ( $this->icon !== null ) {
			$result['icon'] = $this->icon;
		}
		return $result;
	}

	public static function fromArray($array) {
		return new static(
			array_key_exists('value', $array) ? $array['value'] : null,
			array_key_exists('label', $array) ? $array['label'] : null,
			$array
		);
	}

	/**
	 * @param ChoiceControlOption[] $options
	 * @param mixed $selectedValue
	 * @param Setting $setting
	 * @return array
	 */
	public static function generateSelectOptions($options, $selectedValue, Setting $setting) {
		$htmlLines = [];

		foreach ($options as $option) {
			$htmlLines[] = HtmlHelper::tag(
				'option',
				[
					'value'    => $setting->encodeForForm($option->value),
					'selected' => ($selectedValue === $option->value),
					'disabled' => !$option->enabled,

				],
				$option->label
			);
		}

		$koOptionData = self::generateKoOptions($options);
		$optionBindings = array_map('wp_json_encode', $koOptionData);

		return [implode("\n", $htmlLines), $optionBindings];
	}

	/**
	 * @param ChoiceControlOption[] $choiceOptions
	 * @return array{options: array, optionsText: string, optionsValue: string}
	 */
	public static function generateKoOptions($choiceOptions) {
		$koOptions = [];
		foreach ($choiceOptions as $option) {
			$koOptions[] = [
				'value'    => $option->value,
				'label'    => $option->label,
				'disabled' => !$option->enabled,
			];
		}

		return [
			'options'      => $koOptions,
			'optionsText'  => 'label',
			'optionsValue' => 'value',
		];
	}
}
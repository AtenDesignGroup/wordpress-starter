<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssLengthSetting;
use YahnisElsts\AdminMenuEditor\Customizable\HtmlHelper;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;
use YahnisElsts\AdminMenuEditor\Customizable\Settings;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\Setting;

class NumberInput extends AbstractNumericControl {
	protected $type = 'fancyNumber';
	protected $koComponentName = 'ame-number-input';

	/**
	 * @var string|null
	 */
	protected $fixedUnit = null;
	/**
	 * @var Setting|null
	 */
	protected $unitSetting = null;

	public function __construct($settings = [], $params = []) {
		$this->hasPrimaryInput = true;
		parent::__construct($settings, $params);

		//Units can be specified in a number of ways.
		if ( isset($settings['unit']) ) {
			$this->unitSetting = $settings['unit'];
		}

		if ( !$this->unitSetting ) {
			if ( array_key_exists('unit', $params) ) {
				if ( is_string($params['unit']) ) {
					$this->fixedUnit = $params['unit'];
				} else if ( $params['unit'] instanceof Setting ) {
					$this->unitSetting = $params['unit'];
				}
			} else if ( $this->mainSetting instanceof CssLengthSetting ) {
				$unitSetting = $this->mainSetting->getUnitSetting();
				if ( $unitSetting instanceof Setting ) {
					$this->unitSetting = $unitSetting;
				} else {
					$this->fixedUnit = $this->mainSetting->getUnit();
				}
			}
		}

		//Add the "ame-small-number-input" class to controls where the expected
		//number of digits is 4 or less. This only applies if both the min and
		//max values are known.
		if ( is_numeric($this->min) && is_numeric($this->max) ) {
			$digits = 1;
			//Digits before the decimal point = greatest log10 of abs(min) and abs(max).
			//Add 1 because log10 is one less than the number of digits (e.g. log10(1) = 0).
			//Note the use of loose comparison to avoid "0 !== 0.0" issues.
			if ( ($this->min != 0) ) {
				$digits = max($digits, floor(log10(abs($this->min))) + 1);
			}
			if ( ($this->max != 0) ) {
				$digits = max($digits, floor(log10(abs($this->max))) + 1);
			}

			//Add the digits after the decimal point if the step is a decimal number.
			$defaultStep = $this->getDefaultStep();
			$fraction = abs($defaultStep - floor($defaultStep));
			if ( ($fraction != 0) ) {
				$digits += floor(abs(log10(abs($fraction))));
			}

			if ( ($digits <= 4) && !in_array('ame-small-number-input', $this->inputClasses) ) {
				$this->inputClasses[] = 'ame-small-number-input';
			}
		}
	}

	public function renderContent(Renderer $renderer) {
		$hasUnitDropdown = $this->unitSetting instanceof Settings\EnumSetting;

		$currentUnitValue = $this->getCurrentUnit();
		if ( $hasUnitDropdown || !empty($currentUnitValue) ) {
			$unitElementId = $this->getUnitElementId();
		} else {
			$unitElementId = null;
		}

		$value = $this->getMainSettingValue();

		$sliderRanges = $this->getSliderRanges();

		$wrapperClasses = ['ame-number-input-control'];
		if ( !empty($sliderRanges) ) {
			$wrapperClasses[] = 'ame-container-with-popup-slider';
		}
		$wrapperClasses = array_merge($wrapperClasses, $this->classes);

		echo HtmlHelper::tag(
			'fieldset',
			[
				'class'     => $wrapperClasses,
				'data-bind' => $this->makeKoDataBind($this->getKoEnableBinding()),
			]
		);
		if ( $hasUnitDropdown ) {
			echo '<div class="ame-input-group">';
		}

		$attributes = $this->getBasicInputAttributes();
		$attributes['value'] = $value;

		$inputClasses = [];
		if ( !empty($sliderRanges) ) {
			$inputClasses[] = 'ame-input-with-popup-slider';
		}
		$inputClasses[] = 'ame-number-input';
		//buildInputElement() will add $this->inputClasses, so no need to do it here.
		$attributes['class'] = implode(' ', $inputClasses);

		if ( !empty($unitElementId) ) {
			$attributes['data-unit-element-id'] = $unitElementId;
		}
		if ( !empty($sliderRanges) ) {
			$attributes['data-slider-ranges'] = wp_json_encode($sliderRanges);
		}

		$attributes['data-bind'] = $this->makeKoDataBind(array_merge([
			'value'                     => $this->getKoObservableExpression($value),
			'ameObservableChangeEvents' => 'true',
		], $this->getKoEnableBinding()));

		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- buildInputElement() is safe
		echo $this->buildInputElement($attributes);

		if ( $hasUnitDropdown && ($this->unitSetting instanceof Settings\EnumSetting) ) {
			$this->renderUnitDropdown($this->unitSetting, [
				'name'               => $this->getFieldName(null, $this->unitSetting),
				'id'                 => $unitElementId,
				'class'              => 'ame-input-group-secondary ame-number-input-unit',
				'data-ac-setting-id' => $this->unitSetting->getId(),
			]);
		} else {
			$unit = $this->getCurrentUnit();
			if ( !empty($unit) ) {
				echo HtmlHelper::tag(
					'span',
					[
						'id'               => $unitElementId,
						'class'            => 'ame-number-input-unit',
						'data-number-unit' => $unit,
					],
					' ' . esc_html($unit)
				);
			}
		}

		if ( $hasUnitDropdown ) {
			echo '</div>';
		}

		//Slider
		if ( !empty($sliderRanges) ) {
			PopupSlider::basic()->render();
		}

		echo '</fieldset>';

		static::enqueueDependencies();
	}

	protected function getCurrentUnit() {
		if ( $this->unitSetting instanceof Setting ) {
			return $this->unitSetting->getValue();
		}
		return $this->fixedUnit;
	}

	protected function getUnitElementId() {
		return $this->getPrimaryInputId() . '__unit';
	}

	protected function getKoComponentParams() {
		$params = parent::getKoComponentParams();

		$unitText = $this->getCurrentUnit();
		$hasUnitDropdown = false;

		if ( $this->unitSetting instanceof Settings\EnumSetting ) {
			$hasUnitDropdown = true;
			$params['hasUnitDropdown'] = true;
			$params['unitDropdownOptions'] = ChoiceControlOption::generateKoOptions(
				$this->unitSetting->generateChoiceOptions()
			);
		} else if ( !empty($unitText) ) {
			$params['unitText'] = $unitText;
		}

		if ( $hasUnitDropdown || !empty($unitText) ) {
			$params['unitElementId'] = $this->getUnitElementId();
		}

		return $params;
	}

	public function serializeForJs() {
		$result = parent::serializeForJs();

		if ( $this->unitSetting instanceof Settings\EnumSetting ) {
			if ( empty($result['settings']) ) {
				$result['settings'] = [];
			}
			$result['settings']['unit'] = $this->unitSetting->getId();
		}

		return $result;
	}

	public function enqueueKoComponentDependencies() {
		parent::enqueueKoComponentDependencies();

		//The slider automatically enqueues its dependencies when it's rendered
		//via PopupSlider::render(), but KO components don't use that method.
		//We need to enqueue the dependencies explicitly.
		PopupSlider::enqueueDependencies();
	}

}
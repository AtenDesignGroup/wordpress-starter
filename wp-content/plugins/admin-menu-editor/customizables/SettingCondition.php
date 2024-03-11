<?php

namespace YahnisElsts\AdminMenuEditor\Customizable;

use YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting;

class SettingCondition {
	const VALID_OPERATORS = [
		'=='     => true,
		'!='     => true,
		'>'      => true,
		'<'      => true,
		'>='     => true,
		'<='     => true,
		'truthy' => true,
		'falsy'  => true,
	];

	const EQUALS = '==';
	const IS_TRUTHY = 'truthy';
	const IS_FALSY = 'falsy';

	/**
	 * @var AbstractSetting
	 */
	protected $setting;

	/**
	 * @var mixed
	 */
	protected $comparisonValue;

	/**
	 * @var string
	 */
	protected $comparisonOperator;

	public function __construct(
		AbstractSetting $setting,
		                $comparisonOperator,
		                $comparisonValue
	) {
		if ( !array_key_exists($comparisonOperator, self::VALID_OPERATORS) ) {
			throw new \InvalidArgumentException('Invalid comparison operator: ' . $comparisonOperator);
		}

		$this->setting = $setting;
		$this->comparisonValue = $comparisonValue;
		$this->comparisonOperator = $comparisonOperator;
	}

	/**
	 * Perform the comparison and return true if the current value of the setting
	 * matches the specified comparison value.
	 *
	 * @return bool
	 */
	public function evaluate() {
		$settingValue = $this->setting->getValue();
		switch ($this->comparisonOperator) {
			case '==':
				//Note: Intentionally using loose comparison here.
				return $settingValue == $this->comparisonValue;
			case '!=':
				return $settingValue != $this->comparisonValue;
			case '>':
				return $settingValue > $this->comparisonValue;
			case '<':
				return $settingValue < $this->comparisonValue;
			case '>=':
				return $settingValue >= $this->comparisonValue;
			case '<=':
				return $settingValue <= $this->comparisonValue;
			case 'truthy':
				return !empty($settingValue);
			case 'falsy':
				return empty($settingValue);
			default:
				throw new \InvalidArgumentException('Invalid comparison operator: ' . $this->comparisonOperator);
		}
	}

	public function __invoke() {
		return $this->evaluate();
	}

	/**
	 * Generate a JavaScript expression that performs the comparison.
	 * Intended to be used in KnockoutJS bindings.
	 *
	 * @return string
	 */
	public function getJsKoExpression() {
		$observableExpr = sprintf(
			'($root.getSettingObservable(%s, %s)())',
			wp_json_encode($this->setting->getId()),
			wp_json_encode(null)
		);

		if ( $this->comparisonOperator === self::IS_TRUTHY ) {
			return sprintf('(!!%s)', $observableExpr);
		} else if ( $this->comparisonOperator === self::IS_FALSY ) {
			return sprintf('(!%s)', $observableExpr);
		}

		return sprintf(
			'(%s %s %s)',
			$observableExpr,
			$this->comparisonOperator,
			wp_json_encode($this->comparisonValue)
		);
	}

	public function serializeForJs() {
		return [
			'settingId' => $this->setting->getId(),
			'op'        => $this->comparisonOperator,
			'value'     => $this->comparisonValue,
		];
	}
}
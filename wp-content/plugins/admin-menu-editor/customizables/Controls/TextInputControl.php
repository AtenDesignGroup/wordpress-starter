<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\StringSetting;

class TextInputControl extends ClassicControl {
	protected $type = 'text';
	protected $koComponentName = 'ame-text-input';

	/**
	 * @var StringSetting
	 */
	protected $mainSetting;

	/**
	 * @var bool Whether to style the value as code (e.g. using fixed width fonts).
	 */
	protected $isCode = false;

	protected $inputType = 'text';

	public function __construct($settings = array(), $params = array()) {
		parent::__construct($settings, $params);

		$this->hasPrimaryInput = true;
		$this->isCode = !empty($params['isCode']);
		if ( !empty($params['inputType']) ) {
			$this->inputType = $params['inputType'];
		}
	}

	public function renderContent(Renderer $renderer) {
		$classes = array('regular-text');
		if ( $this->isCode ) {
			$classes[] = 'code';
		}
		$classes[] = 'ame-text-input-control';
		$value = $this->getMainSettingValue();

		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- buildInputElement() is safe
		echo $this->buildInputElement(
			array(
				'type'      => $this->inputType,
				'value'     => ($value === null) ? '' : $value,
				'class'     => $classes,
				'style'     => $this->styles,
				'data-bind' => $this->makeKoDataBind([
					'value' => $this->getKoObservableExpression($value),
				]),
			)
		);
		//phpcs:enable
		$this->outputSiblingDescription();
	}

	protected function getKoComponentParams() {
		$params = parent::getKoComponentParams();
		$params['isCode'] = $this->isCode;
		$params['inputType'] = $this->inputType;
		return $params;
	}

}
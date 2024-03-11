<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\StringSetting;

class TextArea extends ClassicControl {
	protected $type = 'textarea';

	/**
	 * @var StringSetting
	 */
	protected $mainSetting;

	protected $rows = 5;
	protected $cols = 100;

	public function __construct($settings = array(), $params = array()) {
		$this->hasPrimaryInput = true;
		parent::__construct($settings, $params);

		if ( isset($params['rows']) ) {
			$this->rows = max(intval($params['rows']), 1);
		}
		if ( isset($params['cols']) ) {
			$this->cols = max(intval($params['cols']), 1);
		}
	}

	public function renderContent(Renderer $renderer) {
		$value = $this->mainSetting->getValue('');

		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- builtInputElement() is safe
		echo $this->buildInputElement(
			[
				'rows'      => (int)$this->rows,
				'cols'      => (int)$this->cols,
				'class'     => 'large-text',
				'data-bind' => $this->makeKoDataBind([
					'value' => $this->getKoObservableExpression($value),
				]),
			],
			'textarea',
			esc_textarea($value)
		);
		//phpcs:enable
		$this->outputSiblingDescription();
	}
}
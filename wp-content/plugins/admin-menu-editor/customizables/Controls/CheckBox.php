<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\Setting;

class CheckBox extends ClassicControl {
	protected $type = 'checkbox';
	protected $koComponentName = 'ame-toggle-checkbox';

	public function __construct($settings = [], $params = []) {
		$this->hasPrimaryInput = true;
		parent::__construct($settings, $params);
	}

	public function renderContent(Renderer $renderer) {
		//buildInputElement() is safe, and we intentionally allow HTML in the label and description.
		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<label>';
		echo $this->buildInputElement(
			[
				'type'      => 'checkbox',
				'checked'   => $this->isChecked(),
				'data-bind' => $this->makeKoDataBind([
					'checked' => $this->getKoObservableExpression($this->isChecked()),
				]),
			]
		);
		echo ' ', $this->label;

		$this->outputNestedDescription();
		echo '</label>';
		//phpcs:enable
	}

	public function isChecked() {
		if ( $this->mainSetting instanceof Setting ) {
			return boolval($this->mainSetting->getValue());
		}
		return false;
	}

	public function includesOwnLabel() {
		return true;
	}

	protected function getKoComponentParams() {
		return array_merge(
			parent::getKoComponentParams(),
			[
				'onValue'  => true,
				'offValue' => false,
			]
		);
	}
}
<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\Setting;

class ColorPicker extends ClassicControl {
	protected $type = 'colorPicker';
	protected $koComponentName = 'ame-color-picker';

	/**
	 * @var \YahnisElsts\AdminMenuEditor\Customizable\Settings\ColorSetting
	 */
	protected $mainSetting;

	public function __construct($settings = [], $params = []) {
		$this->hasPrimaryInput = true;
		parent::__construct($settings, $params);
	}

	public function renderContent(Renderer $renderer) {
		$value = $this->getMainSettingValue();
		if ( !is_string($value) ) {
			$value = '';
		}
		$settingId = ($this->mainSetting instanceof Setting) ? $this->mainSetting->getId() : null;

		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- buildInputElement() is safe
		echo $this->buildInputElement([
			'type'                => 'text',
			'class'               => array_merge(
				['ame-color-picker', 'ame-customizable-color-picker'],
				$this->classes
			),
			'value'               => $value,
			'style'               => 'visibility: hidden',
			'data-ame-setting-id' => $settingId,
			'data-bind'           => 'ameObservableChangeEvents: ' . $this->getKoObservableExpression($value),
		]);
		//phpcs:enable

		static::enqueueDependencies();
	}

	protected static function enqueueDependencies() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;

		parent::enqueueDependencies();

		wp_enqueue_script('wp-color-picker');
	}

	public function supportsLabelAssociation() {
		//This currently doesn't work with the WordPress color picker.
		return false;
	}
}
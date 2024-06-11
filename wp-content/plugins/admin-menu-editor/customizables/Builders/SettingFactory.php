<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Settings;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\Borders;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\BorderStyle;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\BoxShadow;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssColorSetting;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssEnumSetting;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssLengthSetting;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\Font;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\IndividualBorder;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\Spacing;

class SettingFactory {
	/**
	 * @var StorageInterface
	 */
	protected $store;
	/**
	 * @var array<string,mixed>
	 */
	protected $defaults;

	protected $idPrefix;

	/**
	 * @var bool Whether to enable postMessage for all settings created by this factory.
	 */
	protected $enablePostMessageForAll = false;

	protected $tagsToApply = array();

	public function __construct(StorageInterface $store, array $defaults = array(), $idPrefix = '') {
		$this->store = $store;
		$this->defaults = $defaults;
		$this->idPrefix = $idPrefix;
	}

	/**
	 * @param $path
	 * @param $label
	 * @param $params
	 * @return array
	 */
	protected function prepareParams($path, $label, $params) {
		if ( !array_key_exists('default', $params) && array_key_exists($path, $this->defaults) ) {
			$params['default'] = $this->defaults[$path];
		}
		if ( isset($label) ) {
			$params['label'] = $label;
		}
		if ( $this->enablePostMessageForAll ) {
			$params['supportsPostMessage'] = true;
		}
		if ( !empty($this->tagsToApply) ) {
			$params['tags'] = $this->tagsToApply;
		}
		return $params;
	}

	protected function idFrom($path) {
		return $this->idPrefix . str_replace('.', '-', $path);
	}

	protected function slotFor($path) {
		return $this->store->buildSlot($path);
	}

	public function enum($path, $enumValues, $label = null, $params = array()) {
		return new Settings\EnumSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$enumValues,
			$this->prepareParams($path, $label, $params)
		);
	}

	public function stringEnum($path, $enumValues, $label = null, $params = array()) {
		return new Settings\StringEnumSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$enumValues,
			$this->prepareParams($path, $label, $params)
		);
	}

	public function boolean($path, $label = null, $params = array()) {
		return new Settings\BooleanSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function url($path, $label = null, $params = array()) {
		return new Settings\UrlSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function string($path, $label = null, $params = array()) {
		return new Settings\StringSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function userSanitizedString(
		$path,
		$mode = Settings\UserSanitizedStringSetting::SANITIZE_STRIP_HTML,
		$label = null,
		$params = array()
	) {
		$params['sanitizationMode'] = $mode;
		return new Settings\UserSanitizedStringSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	/**
	 * Plain text (no tags) for regular users, arbitrary content for users with
	 * the "unfiltered_html" capability.
	 *
	 * HTML entities are allowed in either case.
	 *
	 * @param $path
	 * @param $label
	 * @param $params
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Settings\UserSanitizedStringSetting
	 */
	public function userText($path, $label = null, $params = array()) {
		return $this->userSanitizedString(
			$path,
			Settings\UserSanitizedStringSetting::SANITIZE_STRIP_HTML,
			$label,
			$params
		);
	}

	public function userHtml($path, $label = null, $params = array()) {
		return $this->userSanitizedString(
			$path,
			Settings\UserSanitizedStringSetting::SANITIZE_POST_HTML,
			$label,
			$params
		);
	}

	public function plainText($path, $label = null, $params = array()) {
		return new Settings\PlainTextSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function integer($path, $label = null, $params = array()) {
		return new Settings\IntegerSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function cssLength($path, $label = null, $cssProperty = '', $params = array()) {
		return new CssLengthSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$cssProperty,
			$this->prepareParams($path, $label, $params)
		);
	}

	public function cssColor($path, $cssProperty, $label = null, $params = array()) {
		return new CssColorSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$cssProperty,
			$this->prepareParams($path, $label, $params)
		);
	}

	public function image($path, $label = null, $params = array()) {
		return new Settings\ImageSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function cssBoxShadow($path, $label = null, $params = array()) {
		return new BoxShadow(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function cssFont($path, $label = null, $params = array()) {
		return new Font(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function cssSpacing($path, $label = null, $params = array()) {
		return new Spacing(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function cssEnum($path, $cssProperty, $enumValues, $label = null, $params = array()) {
		return new CssEnumSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			$cssProperty,
			$enumValues,
			$this->prepareParams($path, $label, $params)
		);
	}

	public function cssBorders($path, $label = null, $params = array()) {
		return new Borders(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function cssIndividualBorder($path, $label = null, $params = array()) {
		return new IndividualBorder(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, $label, $params)
		);
	}

	public function cssBorderStyle($path, $cssProperty = 'border-style', $label = null, $params = array()) {
		return new BorderStyle(
			$this->idFrom($path),
			$this->slotFor($path),
			$cssProperty,
			$this->prepareParams($path, $label, $params)
		);
	}

	/**
	 * @param string $path
	 * @param string $dataType
	 * @param callable $validationCallback
	 * @param $label
	 * @param array $params
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Settings\UserDefinedSetting
	 */
	public function custom(
		$path,
		$dataType,
		$validationCallback,
		$label = null,
		$params = array()
	) {
		return new Settings\UserDefinedSetting(
			$this->idFrom($path),
			$this->slotFor($path),
			array_merge(
				$this->prepareParams($path, $label, $params),
				array(
					'validationCallback' => $validationCallback,
					'type'               => $dataType,
				)
			)
		);
	}

	/**
	 * @param string|array $path
	 * @param callable|null $childGeneratorCallback
	 * @param array $params
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Settings\UserDefinedStruct
	 */
	public function customStruct(
		$path,
		$childGeneratorCallback = null,
		$params = array()
	) {
		if ( isset($childGeneratorCallback) ) {
			$params['childGenerator'] = $childGeneratorCallback;
		}

		return new Settings\UserDefinedStruct(
			$this->idFrom($path),
			$this->slotFor($path),
			$this->prepareParams($path, '', $params)
		);
	}

	/**
	 * @param class-string<\YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting> $settingClass
	 * @param string|array<string> $path
	 * @param string|null $label
	 * @param $params
	 * @param ...$otherConstructorArgs
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting
	 */
	public function create($settingClass, $path, $label = null, $params = array(), ...$otherConstructorArgs) {
		//$params is always the last constructor argument.
		$otherConstructorArgs[] = $this->prepareParams($path, $label, $params);

		return new $settingClass(
			$this->idFrom($path),
			$this->slotFor($path),
			...$otherConstructorArgs
		);
	}

	/**
	 * @return string
	 */
	public function getIdPrefix() {
		return $this->idPrefix;
	}

	/**
	 * Tell the factory to automatically enable postMessage support for all settings
	 * that it creates.
	 *
	 * @return void
	 */
	public function enablePostMessageSupport() {
		$this->enablePostMessageForAll = true;
	}

	public function disablePostMessage() {
		$this->enablePostMessageForAll = false;
	}

	/**
	 * Tell the factory to add the specified tags to all settings that it creates.
	 *
	 * @param string[] $tags
	 * @return void
	 */
	public function setTags(...$tags) {
		$this->tagsToApply = $tags;
	}
}
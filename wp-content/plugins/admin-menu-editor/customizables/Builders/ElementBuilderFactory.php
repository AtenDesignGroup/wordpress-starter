<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\UiElement;
use YahnisElsts\AdminMenuEditor\Customizable\Settings;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\Setting;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\AbstractSettingsDictionary;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Controls\BackgroundPositionSelector;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Controls\BackgroundRepeat;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Controls\BoxDimensions;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Controls\FontStylePicker;

class ElementBuilderFactory {
	/**
	 * @var AbstractSettingsDictionary|null
	 */
	protected $settingLookup = null;

	public function __construct(AbstractSettingsDictionary $settingLookup = null) {
		$this->settingLookup = $settingLookup;
	}

	/**
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Storage\AbstractSettingsDictionary|null
	 */
	public function getSettingDictionary() {
		return $this->settingLookup;
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\Customizable\Controls\Container|ContainerBuilder ...$containers
	 * @return InterfaceBuilder
	 */
	public function structure(...$containers) {
		$builder = new InterfaceBuilder();
		foreach ($containers as $container) {
			$builder->add($container);
		}
		return $builder;
	}

	/**
	 *
	 * @param string $title
	 * @param ElementBuilder|UiElement|ElementBuilder[]|UiElement[] ...$children
	 * @return SectionBuilder
	 */
	public function section($title = '', ...$children) {
		return new SectionBuilder($title, $children);
	}

	/**
	 * @param string $title
	 * @param ElementBuilder|UiElement ...$children
	 * @return GroupBuilder
	 */
	public function group($title = '', ...$children) {
		return new GroupBuilder($title, $children);
	}

	/**
	 * @param string|Setting|null $idOrSetting
	 * @return TextBoxBuilder
	 */
	public function textBox($idOrSetting = '') {
		return new TextBoxBuilder($this->findSettings($idOrSetting));
	}

	/**
	 * @param string|Setting|null $idOrSetting
	 * @return ControlBuilder<\YahnisElsts\AdminMenuEditor\Customizable\Controls\CheckBox>
	 */
	public function checkBox($idOrSetting = null) {
		return $this->initControlBuilder(Controls\CheckBox::class, $idOrSetting);
	}

	/**
	 * @param class-string<\YahnisElsts\AdminMenuEditor\Customizable\Controls\Control> $controlClass
	 * @param string|Setting|array<string|Setting> $idOrSetting
	 */
	protected function initControlBuilder($controlClass, $idOrSetting) {
		return new ControlBuilder($controlClass, $this->findSettings($idOrSetting));
	}

	/**
	 * @param Setting|null $idOrSetting
	 * @return TextareaBuilder
	 */
	public function textArea($idOrSetting = null) {
		return new TextareaBuilder($this->findSettings($idOrSetting), []);
	}

	/**
	 * @param string $rawHtml
	 * @return StaticHtmlBuilder
	 */
	public function html($rawHtml) {
		return new StaticHtmlBuilder($rawHtml);
	}

	/**
	 * @param Setting|null|string $idOrSetting
	 * @return EditorBuilder
	 */
	public function editor($idOrSetting = null) {
		return new EditorBuilder($this->findSettings($idOrSetting));
	}

	/**
	 * @param Setting|null|string $idOrSetting
	 * @return ControlBuilder<\YahnisElsts\AdminMenuEditor\Customizable\Controls\RadioGroup>
	 */
	public function radioGroup($idOrSetting = null) {
		return new RadioGroupBuilder($this->findSettings($idOrSetting));
	}

	/**
	 * @param Setting|null|string $idOrSetting
	 * @return ControlBuilder<\YahnisElsts\AdminMenuEditor\Customizable\Controls\SelectBox>
	 */
	public function select($idOrSetting = null) {
		return $this->initControlBuilder(Controls\SelectBox::class, $idOrSetting);
	}

	/**
	 * @param Setting|null|string $idOrSetting
	 * @return ControlBuilder<\YahnisElsts\AdminMenuEditor\Customizable\Controls\ColorPicker>
	 */
	public function colorPicker($idOrSetting = null) {
		return $this->initControlBuilder(Controls\ColorPicker::class, $idOrSetting);
	}

	public function imageSelector($idOrSetting = null) {
		return $this->initControlBuilder(Controls\ImageSelector::class, $idOrSetting);
	}

	public function codeEditor($idOrSetting = null) {
		return new CodeEditorBuilder($this->findSettings($idOrSetting));
	}

	public function backgroundPosition($idOrSetting = null) {
		return $this->initControlBuilder(
			BackgroundPositionSelector::class,
			$idOrSetting
		);
	}

	public function backgroundRepeat($idOrSetting = null) {
		return $this->initControlBuilder(BackgroundRepeat::class, $idOrSetting);
	}

	public function backgroundSize($idOrSetting = null) {
		return $this->radioGroup($idOrSetting)
			->label('Image size')
			->params(['descriptionsAsTooltips' => true]);
	}

	public function fontStyle($idOrSetting = null) {
		return $this->initControlBuilder(FontStylePicker::class, $idOrSetting);
	}

	public function toggleCheckBox($idOrSetting = null) {
		return new ToggleCheckBoxBuilder($this->findSettings($idOrSetting));
	}

	/**
	 * Create a "Save Changes" button. It uses the default submit button settings.
	 *
	 * @param boolean $wrapInParagraph
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Builders\StaticHtmlBuilder
	 */
	public function saveButton($wrapInParagraph = false) {
		return $this->html(
			get_submit_button(null, 'primary', 'submit', $wrapInParagraph)
		);
	}

	public function number($idOrSetting = null) {
		return new NumberInputBuilder($this->findSettings($idOrSetting));
	}

	public function boxDimensions($idOrSetting = null) {
		return $this->initControlBuilder(BoxDimensions::class, $idOrSetting);
	}

	/**
	 * Automatically choose a suitable control or container for the given setting.
	 *
	 * @param \YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting|string $idOrSetting
	 * @return BaseElementBuilder
	 */
	public function auto($idOrSetting) {
		list($setting) = $this->findSettings($idOrSetting);

		if ( $setting instanceof Settings\EnumSetting ) {
			return $this->radioGroup($setting);
		} else if ( $setting instanceof Settings\BooleanSetting ) {
			return $this->toggleCheckBox($setting)->onValue(true)->offValue(false);
		} else if ( $setting instanceof Settings\ImageSetting ) {
			return $this->imageSelector($setting);
		} else if ( $setting instanceof Settings\NumericSetting ) {
			return $this->number($setting);
		} else if ( $setting instanceof Settings\PredefinedSet ) {
			return $this->autoSection($setting);
		} else if ( $setting instanceof Settings\AbstractSetting ) {
			switch ($setting->getDataType()) {
				case 'color':
					return $this->colorPicker($setting);
				case 'url':
					return $this->textBox($setting)->type('url')->code();
				default:
					return $this->textBox($setting);
			}
		} else {
			if ( empty($setting) ) {
				throw new \InvalidArgumentException("Setting not found: " . $idOrSetting);
			} else {
				throw new \InvalidArgumentException("Unsupported setting type: " . get_class($setting));
			}
		}
	}

	/**
	 * Create a section from the specified setting(s). Supports predefined sets of settings
	 * as well as regular structs. You could even pass a single setting, but that's not very useful.
	 *
	 * @param $idOrSetting
	 * @param string|null $title
	 * @param string $role
	 * @return SectionBuilder
	 */
	public function autoSection($idOrSetting, $title = null, $role = Controls\Section::CONTENT_ROLE) {
		$settings = $this->findSettings($idOrSetting);
		if ( empty($settings) && is_string($idOrSetting) ) {
			$predefinedSet = $this->settingLookup->getPredefinedSet($idOrSetting);
			if ( $predefinedSet !== null ) {
				$settings = [$predefinedSet];
			}
		}

		$firstSetting = reset($settings);
		if ( $firstSetting instanceof Settings\PredefinedSet ) {
			$controls = $firstSetting->createControls($this);
			return $this->section(
				$title ?: $firstSetting->getLabel(),
				...$controls
			)->params(['preferredRole' => $role]);
		}

		if ( !empty($settings) ) {
			$section = $this->section($title)->params(['preferredRole' => $role]);
			foreach ($settings as $setting) {
				$section->add($this->auto($setting));
			}
			return $section;
		} else {
			throw new \InvalidArgumentException("Setting not found: " . $idOrSetting);
		}
	}

	/**
	 * @param string $title
	 * @param ElementBuilder|UiElement|ElementBuilder[]|UiElement[] ...$children
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Builders\SectionBuilder
	 */
	public function contentSection($title = '', ...$children) {
		return $this->section($title, $children)
			->params(['preferredRole' => Controls\Section::CONTENT_ROLE]);
	}

	/**
	 * @template T of \YahnisElsts\AdminMenuEditor\Customizable\Controls\Control
	 * @param class-string<T> $controlClass
	 * @param Setting|null|string $idOrSetting
	 * @return ControlBuilder<T>
	 */
	public function control($controlClass, $idOrSetting = null) {
		return $this->initControlBuilder($controlClass, $idOrSetting);
	}

	/**
	 * @param Setting|string|null|array<string|Setting> $idOrSetting
	 * @return Setting[]
	 */
	protected function findSettings($idOrSetting) {
		if ( $idOrSetting instanceof Settings\AbstractSetting ) {
			return [$idOrSetting];
		} else if ( is_string($idOrSetting) ) {
			if ( isset($this->settingLookup) ) {
				return [$this->settingLookup->getSetting($idOrSetting)];
			} else {
				throw new \InvalidArgumentException(sprintf(
					'Cannot find a setting "%s" because no setting lookup was provided.',
					$idOrSetting
				));
			}
		} else if ( $idOrSetting === null ) {
			return [];
		} else if ( is_array($idOrSetting) ) {
			$settings = [];
			foreach ($idOrSetting as $key => $setting) {
				$found = $this->findSettings($setting);
				$settings[$key] = !empty($found) ? reset($found) : null;
			}
			return $settings;
		}
		throw new \InvalidArgumentException(sprintf(
			'Invalid setting query (type: "%s"). Input must be a Setting, a string, or NULL.',
			gettype($idOrSetting)
		));
	}
}
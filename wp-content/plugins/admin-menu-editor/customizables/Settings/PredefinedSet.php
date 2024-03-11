<?php
namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Builders;
use YahnisElsts\AdminMenuEditor\Customizable\Controls;

/**
 * A PredefinedSet defines a reusable collection of settings that are typically
 * used together.
 *
 * For example, a "font" collection could include a font family, font size, font
 * weight, and so on. You could create multiple instances of this collection, each
 * for a different part of your user interface or theme.
 *
 * Each PredefinedSet creates settings. It can also create controls for editing
 * those settings.
 */
interface PredefinedSet extends SettingGeneratorInterface {
	/**
	 * @param \YahnisElsts\AdminMenuEditor\Customizable\Builders\ElementBuilderFactory $b
	 * @return array<\YahnisElsts\AdminMenuEditor\Customizable\Builders\ElementBuilder|\YahnisElsts\AdminMenuEditor\Customizable\Controls\UiElement>
	 */
	public function createControls(Builders\ElementBuilderFactory $b);
}
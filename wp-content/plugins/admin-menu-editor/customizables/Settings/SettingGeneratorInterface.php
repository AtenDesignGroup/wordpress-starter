<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

/**
 * This interface indicates that an object generates settings. You should be able
 * to retrieve the generated settings by iterating over the object.
 *
 * The implementing class can choose when to actually generate the settings.
 * For example, it might generate a fixed list of settings upon construction,
 * or it could generate new settings every time the object is iterated over.
 */
interface SettingGeneratorInterface extends \IteratorAggregate {

}
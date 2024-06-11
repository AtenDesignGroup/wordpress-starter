<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

use WP_Error;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

/**
 * Like StringSetting, except it sanitizes the string if the current user
 * doesn't have the "unfiltered_html" capability.
 */
class UserSanitizedStringSetting extends StringSetting {
	/**
	 * Leave only HTML tags that are allowed in post content.
	 */
	const SANITIZE_POST_HTML = 1;
	/**
	 * Strip all HTML tags and normalize entities.
	 */
	const SANITIZE_STRIP_HTML = 2;
	/**
	 * Convert special characters to HTML entities (should not double-encode entities).
	 */
	const SANITIZE_ESCAPE_HTML = 3;

	/**
	 * @var int What to do when the current user doesn't have the "unfiltered_html" capability.
	 */
	protected $sanitizationMode = self::SANITIZE_STRIP_HTML;

	public function __construct($id, StorageInterface $store = null, $params = array()) {
		parent::__construct($id, $store, $params);
		if ( isset($params['sanitizationMode']) ) {
			$this->sanitizationMode = $params['sanitizationMode'];
		}
	}

	public function validate($errors, $value, $stopOnFirstError = false) {
		$convertedValue = parent::validate($errors, $value);
		if ( is_wp_error($convertedValue) || ($convertedValue === null) ) {
			return $convertedValue;
		}

		if ( current_user_can('unfiltered_html') ) {
			return $convertedValue;
		} else {
			switch ($this->sanitizationMode) {
				case self::SANITIZE_POST_HTML:
					return wp_kses_post($convertedValue);
				case self::SANITIZE_STRIP_HTML:
					return wp_kses($convertedValue, 'strip');
				case self::SANITIZE_ESCAPE_HTML:
					return esc_html($convertedValue);
				default:
					return new WP_Error(
						'invalid_filter_mode',
						'Invalid filter mode set for this setting'
					);
			}
		}
	}
}
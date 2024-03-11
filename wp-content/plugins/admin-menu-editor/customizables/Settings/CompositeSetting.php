<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

class CompositeSetting extends AbstractStructSetting {
	public function update($validValue) {
		$validValue = $this->filterNewValues($validValue);
		return parent::update($validValue);
	}

	/**
	 * This should be called after validation and sanitization but before the underlying
	 * settings are updated or saved.
	 *
	 * @param array $values
	 * @return array
	 */
	protected function filterNewValues($values) {
		return $values;
	}

	public function preview($unsafeValue, $errors = null) {
		if ( $errors === null ) {
			$errors = new \WP_Error();
		}

		//Unlike a general struct, composite settings are all-or-nothing: if any
		//children fail validation, all preview values are disregarded.
		$validationResult = $this->validate($errors, $unsafeValue, true);
		if ( is_wp_error($validationResult) ) {
			$previewValues = [];
		} else {
			$previewValues = $validationResult;
		}
		$previewValues = $this->filterNewValues($previewValues);

		foreach ($this->settings as $key => $setting) {
			//Note: In this implementation, child settings might get validated twice.
			//because preview() will typically validate the value again.
			if ( array_key_exists($key, $previewValues) ) {
				$setting->preview($previewValues[$key], $errors);
			} else {
				$setting->preview(null, $errors);
			}
		}
	}

	protected function shouldEnablePostMessageForChildren() {
		//A composite settings should be updated or previewed as a whole,
		//so we don't need to enable postMessage for its children.
		return false;
	}
}
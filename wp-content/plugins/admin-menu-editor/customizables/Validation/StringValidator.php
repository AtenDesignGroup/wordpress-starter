<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Validation;

class StringValidator {
	private $minLength;
	private $maxLength;
	private $validationRegex;
	/**
	 * @var bool
	 */
	private $truncateWithoutError;
	/**
	 * @var false
	 */
	private $trimWhitespace;

	public function __construct(
		$minLength = 0,
		$maxLength = null,
		$truncateWithoutError = false,
		$regex = null,
		$trimWhitespace = false
	) {
		$this->minLength = $minLength;
		$this->maxLength = $maxLength;
		$this->truncateWithoutError = $truncateWithoutError;
		$this->validationRegex = $regex;
		$this->trimWhitespace = $trimWhitespace;
	}

	public function __invoke($value, \WP_Error $errors) {
		$convertedValue = strval($value);
		if ( $this->trimWhitespace ) {
			$convertedValue = trim($convertedValue);
		}

		$length = strlen($convertedValue);

		if ( ($this->minLength !== null) && ($length < $this->minLength) ) {
			$errors->add('min_length', 'Value is too short, minimum length is ' . $this->minLength);
			return $errors;
		}

		if ( ($this->maxLength !== null) && ($length > $this->maxLength) ) {
			if ( $this->truncateWithoutError ) {
				$convertedValue = substr($convertedValue, 0, $this->maxLength);
			} else {
				$errors->add('max_length', 'Value is too long, maximum length is ' . $this->maxLength);
				return $errors;
			}
		}

		if ( $this->validationRegex !== null ) {
			if ( !preg_match($this->validationRegex, $convertedValue) ) {
				$errors->add(
					'regex_match_failed',
					'Value must match the following regex: ' . $this->validationRegex
				);
				return $errors;
			}
		}

		return $convertedValue;
	}

	public static function sanitizeStripTags($value) {
		return wp_strip_all_tags((string) $value);
	}
}
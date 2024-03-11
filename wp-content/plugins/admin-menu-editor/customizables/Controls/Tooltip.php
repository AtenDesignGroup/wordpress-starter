<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

class Tooltip {
	const INFO = 'info';
	const EXPERIMENTAL = 'experimental';
	const DEFAULT_TYPE = self::INFO;

	protected $type;
	protected $htmlContent = '';
	protected $extraClasses;

	/**
	 * @param string $htmlContent
	 * @param string $type
	 * @param string[] $extraClasses
	 */
	public function __construct($htmlContent, $type = self::DEFAULT_TYPE, $extraClasses = array()) {
		$this->htmlContent = $htmlContent;
		$this->type = $type;
		$this->extraClasses = $extraClasses;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getHtmlContent() {
		return $this->htmlContent;
	}

	/**
	 * @return string[]
	 */
	public function getExtraClasses() {
		return $this->extraClasses;
	}
}
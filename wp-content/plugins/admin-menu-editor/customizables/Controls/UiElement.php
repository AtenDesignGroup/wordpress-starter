<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\HtmlHelper;

abstract class UiElement {
	/**
	 * @var string
	 */
	protected $id = '';

	/**
	 * @var string|callable
	 */
	protected $description = '';

	/**
	 * @var array List of CSS classes to apply to the outermost DOM node of the element.
	 * This property might not be meaningful for elements that output multiple nodes without
	 * a common parent or that don't have a visible representation.
	 */
	protected $classes = array();

	/**
	 * @var array List of CSS styles to apply to the outermost DOM node of the element.
	 */
	protected $styles = array();

	protected $renderCondition = true;

	/**
	 * Lets the renderer know that the element doesn't want new line breaks added
	 * before and after its content.
	 *
	 * - Block elements (e.g. &lt;fieldset&gt;) and elements that surround their
	 * content with &lt;p&gt; or &lt;br&gt; tags should set this to true.
	 * - Elements that output partial or unclosed tags should also set this to
	 * true to avoid producing invalid HTML.
	 *
	 * @var bool
	 */
	protected $declinesExternalLineBreaks = false;

	public function __construct($params = array()) {
		if ( !empty($params['id']) ) {
			$this->id = $params['id'];
		}
		if ( !empty($params['description']) ) {
			$this->description = $params['description'];
		}
		if ( !empty($params['classes']) ) {
			$this->classes = (array)$params['classes'];
		}
		if ( !empty($params['styles']) ) {
			$this->styles = (array)$params['styles'];
		}
		if ( isset($params['renderCondition']) ) {
			$this->renderCondition = $params['renderCondition'];
		}
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		if ( is_string($this->description) ) {
			return $this->description;
		} elseif ( is_callable($this->description) ) {
			return call_user_func($this->description);
		} else {
			return strval($this->description);
		}
	}

	/**
	 * @return array
	 */
	public function getClasses() {
		return $this->classes;
	}

	protected function buildTag($tagName, $attributes = array(), $content = null) {
		return HtmlHelper::tag($tagName, $attributes, $content);
	}

	/**
	 * @return bool
	 */
	public function declinesExternalLineBreaks() {
		return $this->declinesExternalLineBreaks;
	}

	public function shouldRender() {
		if ( is_callable($this->renderCondition) ) {
			return call_user_func($this->renderCondition);
		}
		return (bool)$this->renderCondition;
	}

	public function serializeForJs() {
		$description = $this->getDescription();
		$result = ['t' => $this->getJsUiElementType()];
		if ( !empty($this->classes) ) {
			$result['classes'] = $this->classes;
		}
		if ( !empty($this->styles) ) {
			$result['styles'] = $this->styles;
		}
		if ( !empty($description) ) {
			$result['description'] = $description;
		}
		if ( !empty($this->id) ) {
			$result['id'] = $this->id;
		}

		$params = $this->getKoComponentParams();
		if ( !empty($params) ) {
			$result['params'] = $params;
		}

		return $result;
	}

	abstract protected function getJsUiElementType();

	/**
	 * Get additional parameters for the Knockout component that renders this element.
	 *
	 * @return array
	 */
	protected function getKoComponentParams() {
		return [];
	}

	public function enqueueKoComponentDependencies() {
		//Do nothing by default.
	}
}
<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Builders;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;
use YahnisElsts\AdminMenuEditor\Customizable\SettingsForm;
use YahnisElsts\AdminMenuEditor\Customizable\UpdateRequestHandler;

class FormBuilder {
	protected $params = array();

	public function structure(Controls\InterfaceStructure $structure) {
		$this->params['structure'] = $structure;
		return $this;
	}

	/**
	 * @param array<string,\YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting> $settings
	 * @return $this
	 */
	public function settings($settings) {
		$this->params['settings'] = $settings;
		return $this;
	}

	public function renderer(Renderer $renderer) {
		$this->params['renderer'] = $renderer;
		return $this;
	}

	/**
	 * Set the action name that will be used for nonce generation, hooks,
	 * and the hidden "action" field.
	 *
	 * Not to be confused with the "action" attribute of a form element.
	 * Use the submitUrl() method to set that.
	 *
	 * @param string $actionName
	 * @return $this
	 */
	public function actionName($actionName) {
		$this->params['action'] = $actionName;
		return $this;
	}

	/**
	 * @param string $httpMethod Either 'get' or 'post'.
	 * @return $this
	 */
	public function method($httpMethod) {
		$httpMethod = trim(strtolower($httpMethod));
		if ( ($httpMethod !== 'get') && ($httpMethod !== 'post') ) {
			throw new \InvalidArgumentException(sprintf(
				'Invalid HTTP method "%s" for a settings form. Must be "get" or "post".',
				$httpMethod
			));
		}

		$this->params['method'] = $httpMethod;
		return $this;
	}

	public function submitUrl($url) {
		$this->params['submitUrl'] = $url;
		return $this;
	}

	public function requiredCapability($capability) {
		$this->params['requiredCapability'] = $capability;
		return $this;
	}

	/**
	 * @param callable $callback
	 * @return $this
	 */
	public function permissionCallback($callback) {
		$this->params['permissionCallback'] = $callback;
		return $this;
	}

	public function id($id) {
		$this->params['id'] = $id;
		return $this;
	}

	/**
	 * @param bool $shouldAddButton
	 * @return $this
	 */
	public function addDefaultSubmitButton($shouldAddButton = true) {
		$this->params['defaultSubmitButtonEnabled'] = $shouldAddButton;
		return $this;
	}

	public function redirectAfterSaving($url, $successParams = array('updated' => 1)) {
		$this->params['redirectUrl'] = $url;
		$this->params['successParams'] = $successParams;
		return $this;
	}

	public function passThroughParams($params) {
		$this->params['passThroughParams'] = $params;
		return $this;
	}

	public function dieOnError() {
		$this->params['errorReporting'] = UpdateRequestHandler::DIE_ON_ERRORS;
		return $this;
	}

	public function storeErrors($transientName = null) {
		$this->params['errorReporting'] = UpdateRequestHandler::STORE_ERRORS;
		$this->params['errorTransientName'] = $transientName;
		return $this;
	}

	public function postProcessSettings($callback) {
		$this->params['postProcessingCallback'] = $callback;
		return $this;
	}

	public function skipMissingFields() {
		$this->params['missingFieldHandling'] = UpdateRequestHandler::SKIP_MISSING_FIELDS;
		return $this;
	}

	public function treatMissingFieldsAsEmpty() {
		$this->params['missingFieldHandling'] = UpdateRequestHandler::TREAT_MISSING_FIELDS_AS_EMPTY;
		return $this;
	}

	public function allowPartialUpdates() {
		$this->params['partialUpdatesAllowed'] = true;
		return $this;
	}

	public function forbidPartialUpdates() {
		$this->params['partialUpdatesAllowed'] = false;
		return $this;
	}

	public function stopOnFirstValidationError() {
		$this->params['$stopOnFirstError'] = true;
		return $this;
	}

	public function build() {
		return new SettingsForm($this->params);
	}
}
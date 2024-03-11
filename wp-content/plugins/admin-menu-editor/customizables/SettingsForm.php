<?php

namespace YahnisElsts\AdminMenuEditor\Customizable;

use YahnisElsts\AdminMenuEditor\Customizable\Builders\FormBuilder;
use YahnisElsts\AdminMenuEditor\Customizable\HtmlHelper;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\FormTableRenderer;

class SettingsForm {
	/**
	 * @var string
	 */
	protected $action = '';

	/**
	 * @var string
	 */
	protected $submitUrl = '';

	/**
	 * @var string
	 */
	protected $method = 'post';

	/**
	 * @var \YahnisElsts\AdminMenuEditor\Customizable\Controls\InterfaceStructure
	 */
	protected $structure = null;
	/**
	 * @var \YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer
	 */
	protected $renderer;
	/**
	 * @var null|array<string,\YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting>
	 */
	protected $settings = null;

	/**
	 * @var string|null ID attribute of the form element.
	 */
	protected $id = null;

	protected $defaultSubmitButtonEnabled = true;

	protected $errorReporting = UpdateRequestHandler::DIE_ON_ERRORS;
	protected $errorTransientName = null;

	protected $redirectUrl = '';
	protected $successParams = array('updated' => 1);
	protected $passThroughParams = array();

	/**
	 * @var null|string
	 */
	protected $requiredCapability = null;
	/**
	 * @var null|callable
	 */
	protected $permissionCallback = null;

	/**
	 * @var null|callable
	 */
	protected $postProcessingCallback = null;

	/**
	 * @var array
	 */
	protected $configurationParams;

	public function __construct($params = array()) {
		$this->configurationParams = $params;

		$copyProperties = array(
			'action',
			'submitUrl',
			'method',
			'structure',
			'settings',
			'id',
			'defaultSubmitButtonEnabled',
			'errorReporting',
			'errorTransientName',
			'redirectUrl',
			'successParams',
			'passThroughParams',
			'requiredCapability',
			'permissionCallback',
			'postProcessingCallback',
		);
		foreach ($copyProperties as $property) {
			if ( isset($params[$property]) ) {
				$this->$property = $params[$property];
			}
		}

		if ( isset($params['renderer']) ) {
			$this->renderer = $params['renderer'];
		} else {
			$this->renderer = new FormTableRenderer();
		}
	}

	public function output() {
		if ( $this->id !== null ) {
			$formId = $this->id;
		} else {
			$formId = 'ame-struct-form-' . time();
		}

		echo HtmlHelper::tag('form', array(
			'action' => $this->submitUrl,
			'method' => $this->method,
			'id'     => $formId,
		));

		$this->renderer->renderStructure($this->structure);

		if ( !empty($this->action) ) {
			echo HtmlHelper::tag('input', array(
				'type'  => 'hidden',
				'name'  => 'action',
				'value' => $this->action,
			));
			wp_nonce_field($this->action);
		}

		if ( $this->defaultSubmitButtonEnabled ) {
			submit_button('Save Changes');
		}

		echo '</form>';

		$this->renderer->enqueueDependencies('#' . $formId);
	}

	public function handleUpdateRequest($requestParams, $queryParams = []) {
		$handler = new UpdateRequestHandler(
			$this->settings,
			array_merge(
			//Pass through most parameters.
				$this->configurationParams,
				[
					'errorReporting'         => $this->errorReporting,
					'errorTransientName'     => $this->errorTransientName,
					'redirectUrl'            => $this->redirectUrl,
					'successParams'          => $this->successParams,
					'passThroughParams'      => $this->passThroughParams,
					'requiredCapability'     => $this->requiredCapability,
					'permissionCallback'     => $this->permissionCallback,
					'postProcessingCallback' => $this->postProcessingCallback,
				]
			)
		);

		$handler->handleRequest($requestParams, $queryParams);
	}

	public static function builder($action = null) {
		return (new FormBuilder())->actionName($action);
	}
}
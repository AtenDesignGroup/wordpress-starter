<?php

namespace YahnisElsts\AdminMenuEditor\Customizable;

use WP_Error;

class UpdateRequestHandler {
	protected $reservedFields = ['action', '_wpnonce', '_ajax_nonce', '_wp_http_referer'];

	protected $expectedAction = null;
	protected $nonceCheckEnabled = true;

	/**
	 * @var bool Whether to stop validation after the first error,
	 * or continue validating the rest of the settings.
	 */
	protected $stopOnFirstError = false;

	const DIE_ON_ERRORS = 1;
	const STORE_ERRORS = 2;
	protected $errorReporting = self::DIE_ON_ERRORS;
	protected $errorTransientName = null;

	/**
	 * @var bool When some of the submitted settings are invalid, should we still
	 * save the settings that are valid?
	 */
	protected $partialUpdatesAllowed = false;

	protected $redirectUrl = '';
	protected $successParams = array('updated' => 1);
	protected $passThroughParams = array();

	/**
	 * @var null|callable
	 */
	protected $permissionCallback = null;

	/**
	 * @var null|callable
	 */
	protected $postProcessingCallback = null;

	/**
	 * @var array<string,\YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting>
	 */
	protected $settingsById = array();

	/**
	 * Skip fields that are not present in the update request. The corresponding
	 * settings won't be changed.
	 */
	const SKIP_MISSING_FIELDS = 10;
	/**
	 * When a setting doesn't have a corresponding field in the update request,
	 * use an empty string in place of the missing field.
	 */
	const TREAT_MISSING_FIELDS_AS_EMPTY = 20;

	/**
	 * @var mixed How to handle missing fields - that is, settings that don't have
	 *  a matching request parameter.
	 */
	protected $missingFieldHandling = self::SKIP_MISSING_FIELDS;

	public function __construct($settingsById, $params = array()) {
		$this->settingsById = $settingsById;

		$copyProperties = array(
			'errorReporting',
			'errorTransientName',
			'redirectUrl',
			'successParams',
			'passThroughParams',
			'permissionCallback',
			'postProcessingCallback',
			'expectedAction',
			'nonceCheckEnabled',
			'missingFieldHandling',
			'stopOnFirstError',
			'partialUpdatesAllowed',
		);
		foreach ($copyProperties as $property) {
			if ( isset($params[$property]) ) {
				$this->$property = $params[$property];
			}
		}
	}

	/**
	 * Beware: This method will stop execution one way or another.
	 *
	 * @param array<string,mixed> $requestParams
	 * @param array<string,mixed> $queryParams
	 */
	public function handleRequest($requestParams, $queryParams = []) {
		//Check action.
		$action = '';
		if ( isset($requestParams['action']) ) {
			$action = $requestParams['action'];
		}
		if ( isset($this->expectedAction) && ($action !== $this->expectedAction) ) {
			$this->handleError(new WP_Error(
				'ame_invalid_action',
				sprintf(
					'The action parameter has an invalid value. Expected: "%s", actual value: "%s".',
					esc_html($this->expectedAction),
					esc_html($action)
				)
			));
		}

		//Check nonce.
		if ( $this->nonceCheckEnabled ) {
			if ( wp_doing_ajax() ) {
				check_ajax_referer($action, '_ajax_nonce');
			} else {
				check_admin_referer($action);
			}
		}

		//Check request permissions.
		if ( !empty($this->permissionCallback) ) {
			$permissionStatus = call_user_func($this->permissionCallback, $requestParams);
			if ( !$permissionStatus ) {
				$this->handleError(new WP_Error(
					'ame_permission_denied',
					'You do not have sufficient permissions to perform this operation.'
				));
			} else if ( is_wp_error($permissionStatus) ) {
				$this->handleError($permissionStatus);
			}
		}

		//Extract relevant fields from request parameters. For example, "action"
		//and "_wpnonce" are usually reserved and do not contain setting values.
		//We only want parameters that match setting IDs.
		$inputValues = [];
		foreach ($requestParams as $key => $value) {
			if ( in_array($key, $this->reservedFields) ) {
				continue;
			}
			if ( isset($this->settingsById[$key]) ) {
				$inputValues[$key] = $value;
			}
		}

		//Optionally, substitute missing fields with empty values.
		//Settings that are not editable are excluded.
		if ( $this->missingFieldHandling === self::TREAT_MISSING_FIELDS_AS_EMPTY ) {
			$inputValues = $this->substituteEmptyValues($this->settingsById, $inputValues);
		}

		list($errors, $sanitizedValues) = $this->checkAllInputs($inputValues, $this->stopOnFirstError);

		//Can we update any settings?
		$settingsUpdated = false;
		if ( !empty($sanitizedValues) && (empty($errors) || $this->partialUpdatesAllowed) ) {
			//Update settings.
			$updatedSettings = [];
			foreach ($sanitizedValues as $settingId => $value) {
				$this->settingsById[$settingId]->update($value);
				$updatedSettings[] = $this->settingsById[$settingId];
			}

			//Send any queued update notifications.
			Settings\AbstractSetting::sendPendingNotifications();

			//Run the post-processing callback.
			if ( !empty($this->postProcessingCallback) ) {
				call_user_func($this->postProcessingCallback, $sanitizedValues, $this->settingsById);
			}

			//Save settings.
			Settings\AbstractSetting::saveAll($updatedSettings);
			$settingsUpdated = true;
		}

		if ( !empty($errors) ) {
			//Error! But could also be a partial success.
			$this->handleError($errors, $settingsUpdated, $requestParams, $queryParams);
		} else if ( $settingsUpdated ) {
			//Success!
			$this->handleSuccess($requestParams, $queryParams);
		} else {
			//No errors and no changes. This is probably an error in itself because the user
			//wouldn't have submitted the form if they didn't intend to save something.
			$this->handleError(new WP_Error(
				'ame_no_changes',
				'There were no validation errors, but no changes were made to the settings.'
				. ' This is unexpected and may be a bug.'
			));
		}
	}

	/**
	 * @param array<string,\YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting>|\Traversable $settingsById
	 * @param array<string,mixed> $inputValues
	 * @return array<string,mixed>
	 */
	protected function substituteEmptyValues($settingsById, $inputValues) {
		foreach ($settingsById as $settingId => $setting) {
			if ( !array_key_exists($settingId, $inputValues) && $setting->isEditableByUser() ) {
				if ( $setting instanceof Settings\AbstractStructSetting ) {
					$inputValues[$settingId] = array();
				} else {
					$inputValues[$settingId] = '';
				}
			}

			if ( $setting instanceof Settings\AbstractStructSetting ) {
				$inputValues[$settingId] = $this->substituteEmptyValues(
					$setting,
					$inputValues[$settingId]
				);
			}
		}
		return $inputValues;
	}

	/**
	 * @param \WP_Error|\WP_Error[] $error
	 * @return void
	 */
	protected function handleError($error, $isPartialSuccess = false, $requestParams = [], $queryParams = []) {
		if ( $this->errorReporting === self::DIE_ON_ERRORS ) {

			if ( is_array($error) ) {
				$messageLines = [];
				foreach ($error as $settingId => $singleError) {
					foreach ($singleError->get_error_messages() as $singleMessage) {
						$messageLines[] = esc_html(sprintf(
							'%s: %s',
							//Add setting names to error messages.
							isset($this->settingsById[$settingId])
								? $this->settingsById[$settingId]->getLabel()
								: (!empty($settingId) ? $settingId : 'Error'),
							$singleMessage
						));
					}
				}

				$message = implode("<br>\n", $messageLines);
				//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Individual lines are escaped above.
				wp_die($message);
			} else {
				$displayError = $error;
				wp_die(wsAmeEscapeWpError($displayError));
			}

		} else if ( $this->errorReporting === self::STORE_ERRORS ) {

			$errors = is_array($error) ? $error : array($error);
			$serializedErrors = wp_json_encode(array_map([self::class, 'errorToArray'], $errors));
			set_transient($this->errorTransientName, $serializedErrors, 120);

			$this->redirectToNextPage(
				$requestParams,
				$queryParams,
				$isPartialSuccess ? $this->successParams : array()
			);
			exit;

		} else {
			throw new \LogicException("Invalid error mode: $this->errorReporting");
		}
	}

	protected function checkAllInputs($inputValues, $stopOnError = false) {
		$errors = [];
		$sanitizedValues = [];

		foreach ($inputValues as $settingId => $value) {
			if ( !isset($this->settingsById[$settingId]) ) {
				continue;
			}

			$setting = $this->settingsById[$settingId];

			//Validate and sanitize.
			$validationResult = $setting->validateFormValue(new WP_Error(), $value, $stopOnError);
			if ( is_wp_error($validationResult) && ($validationResult->has_errors()) ) {
				$errors[$settingId] = $validationResult;
				if ( $stopOnError ) {
					break;
				}
			} else {
				$sanitizedValues[$settingId] = $validationResult;
			}

			//Check setting permissions.
			if ( !$setting->isEditableByUser() ) {
				$errors[$settingId] = new WP_Error(
					'ame_permission_denied',
					'You do not have permission to change this setting.'
				);
				if ( $stopOnError ) {
					break;
				}
			}
		}

		return [$errors, $sanitizedValues];
	}

	protected function redirectToNextPage($requestParams, $queryParams, $addQueryParams = []) {
		if ( empty($this->redirectUrl) ) {
			throw new \LogicException('No redirect URL was specified.');
		}
		//Redirect to the next page.

		//Typically, there will be a parameter like "message" or "updated" that
		//indicates settings were saved successfully.
		$redirectParams = $addQueryParams;

		//Optionally, you can pass through other parameters, e.g. to reselect
		//the previously selected item after saving changes.
		foreach ($this->passThroughParams as $name) {
			//Do not overwrite success parameters.
			if ( array_key_exists($name, $redirectParams) ) {
				continue;
			}

			//Prefer request parameters, then query parameters.
			//These could be the same if it's a GET request.
			if ( array_key_exists($name, $requestParams) ) {
				$redirectParams[$name] = $requestParams[$name];
			} else if ( array_key_exists($name, $queryParams) ) {
				$redirectParams[$name] = $queryParams[$name];
			}
		}

		$url = add_query_arg($redirectParams, $this->redirectUrl);
		if ( wp_redirect($url) ) {
			exit;
		} else {
			wp_die(wsAmeEscapeWpError(new WP_Error(
				'ame_redirect_failed',
				'Failed to redirect to the next page.'
			)));
		}
	}

	protected function handleSuccess($requestParams, $queryParams) {
		if ( !empty($this->redirectUrl) ) {
			$this->redirectToNextPage($requestParams, $queryParams, $this->successParams);
		} else {
			wp_die('Settings updated.');
		}
	}

	/**
	 * Convert a WP_Error instance to an associative array.
	 *
	 * @param WP_Error $error
	 * @return array
	 */
	public static function errorToArray($error) {
		$canGetAllData = method_exists($error, 'get_all_error_data'); //WP 5.6+

		$errorArray = [];
		foreach ($error->get_error_codes() as $code) {
			$errorArray[$code] = ['messages' => $error->get_error_messages($code)];

			if ( $canGetAllData ) {
				$dataItems = $error->get_all_error_data($code);
			} else {
				$data = $error->get_error_data($code);
				if ( $data !== null ) {
					$dataItems = array($data);
				} else {
					$dataItems = array();
				}
			}
			$errorArray[$code]['data'] = $dataItems;
		}
		return $errorArray;
	}

	/**
	 * Create a WP_Error instance from an array that was produced by errorToArray().
	 *
	 * @param array $errorArray
	 * @return WP_Error
	 */
	public static function arrayToError($errorArray) {
		$error = new WP_Error();
		foreach ($errorArray as $code => $details) {
			foreach ($details['messages'] as $message) {
				$error->add($code, $message);
			}
			if ( isset($details['data']) ) {
				foreach ($details['data'] as $data) {
					$error->add_data($data, $code);
				}
			}
		}
		return $error;
	}
}
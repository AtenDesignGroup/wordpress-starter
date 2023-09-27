<?php
/**
 * Droopbox API library.
 *
 * @since 4.0
 *
 * @package ForGravity\Fillable_PDFs
 */

namespace ForGravity\Fillable_PDFs\Integrations\Dropbox;

defined( 'ABSPATH' ) || die();

use ForGravity\Fillable_PDFs\Integrations\Dropbox;
use GFCommon;
use WP_Error;

/**
 * Dropbox API library.
 *
 * @since 4.0
 * @package   ForGravity\Fillable_PDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2022, ForGravity
 */
class API {

	/**
	 * Base path for Dropbox API requests.
	 *
	 * @since 4.0
	 * @var   string
	 */
	const API_URL = 'https://api.dropboxapi.com/2/';

	/**
	 * The key for the Gravity Forms dismissible error message.
	 *
	 * @since 4.0
	 * @var   string
	 */
	const ERROR_MESSAGE_KEY = 'forgravity_fillablepdfs_dropbox_token_not_refreshed';

	/**
	 * The size, in bytes, to trigger a Dropbox upload session.
	 *
	 * @since 4.0
	 * @var   int
	 */
	const UPLOAD_SESSION_THRESHOLD = 8000000;

	/**
	 * The size, in bytes, to split a file into for a Dropbox upload session.
	 *
	 * @since 4.0
	 * @var   int
	 */
	const UPLOAD_SESSION_CHUNK_SIZE = 4000000;

	/**
	 * Dropbox access token.
	 *
	 * @since  4.0
	 * @var    string
	 */
	private $access_token;

	/**
	 * Dropbox refresh token.
	 *
	 * @since 4.0
	 * @var   string
	 */
	private $refresh_token;

	/**
	 * Expiration timestamp of access token.
	 *
	 * @since 4.0
	 * @var   int
	 */
	private $token_expiration_time;

	/**
	 * Initialize the Dropbox API library.
	 *
	 * @since 4.0
	 *
	 * @param array $auth_data  Authentication data.
	 */
	public function __construct( $auth_data ) {

		$this->access_token          = rgar( $auth_data, 'access_token' );
		$this->refresh_token         = rgar( $auth_data, 'refresh_token' );
		$this->token_expiration_time = rgar( $auth_data, 'expiration_time' );

		if ( $this->is_access_token_expired() ) {
			$this->refresh_access_token();
		}

	}





	// # ACCOUNT -------------------------------------------------------------------------------------------------------

	/**
	 * Returns the account information for the currently authenticated user.
	 *
	 * @since 4.0
	 *
	 * @return array|WP_Error
	 */
	public function get_current_account() {

		return $this->make_request( 'users/get_current_account' );

	}





	// # UPLOAD --------------------------------------------------------------------------------------------------------

	/**
	 * Upload file to Dropbox.
	 *
	 * @since 4.0
	 *
	 * @param string $local_file_path       File to be uploaded.
	 * @param string $destination_file_path Path to upload in Dropbox.
	 * @param bool   $overwrite_file        Overwrite file if already exists.
	 *
	 * @return array|WP_Error
	 */
	public function upload( $local_file_path, $destination_file_path, $overwrite_file = false ) {

		// Get size of file.
		if ( ! ( $file_size = filesize( $local_file_path ) ) ) {
			return new WP_Error( 'no_file_size', esc_html__( 'Unable to get file size of file to upload.', 'forgravity_fillablepdfs' ) );
		}

		// If file is above the threshold for a simple upload, start an upload session.
		if ( $file_size > self::UPLOAD_SESSION_THRESHOLD ) {
			return $this->upload_chunked( $local_file_path, $destination_file_path, $overwrite_file );
		}

		return $this->upload_simple( $local_file_path, $destination_file_path, $overwrite_file );

	}

	/**
	 * Upload file directly to Dropbox.
	 *
	 * @since 4.0
	 *
	 * @param string $local_file_path       File to be uploaded.
	 * @param string $destination_file_path Path to upload in Dropbox.
	 * @param bool   $overwrite_file        Overwrite file if already exists.
	 *
	 * @return array|WP_Error
	 */
	private function upload_simple( $local_file_path, $destination_file_path, $overwrite_file ) {

		// Prepare request parameters.
		$api_args = [
			'autorename' => true,
			'mode'       => $overwrite_file ? 'overwrite' : 'add',
			'path'       => $destination_file_path,
		];

		// Get file contents.
		$fstream = fopen( $local_file_path, 'r' );
		$fsize   = filesize( $local_file_path );
		$fdata   = fread( $fstream, $fsize );

		return $this->make_upload_request( 'upload', $fdata, $api_args );

	}

	/**
	 * Upload file to Dropbox via an upload session.
	 *
	 * @since 4.0
	 *
	 * @param string $local_file_path       File to be uploaded.
	 * @param string $destination_file_path Path to upload in Dropbox.
	 * @param bool   $overwrite_file        Overwrite file if already exists.
	 *
	 * @return array|WP_Error
	 */
	private function upload_chunked( $local_file_path, $destination_file_path, $overwrite_file ) {

		// Get size of file.
		$file_size = filesize( $local_file_path );

		// Get session ID.
		$session_id = $this->upload_session_start( $local_file_path );

		// If upload session could not be started, return.
		if ( ! $session_id ) {
			return new WP_Error( 'session_not_started', 'Unable to start upload session for ' . basename( $local_file_path ) );
		}

		// Calculate uploaded and remaining sizes.
		$uploaded  = self::UPLOAD_SESSION_CHUNK_SIZE;
		$remaining = $file_size - self::UPLOAD_SESSION_CHUNK_SIZE;

		// Continue to upload chunks of the file until whole file is uploaded.
		while ( $remaining > self::UPLOAD_SESSION_CHUNK_SIZE ) {

			// Append the next chunk to the upload session.
			$session_id = $this->upload_session_append( $session_id, $local_file_path, $uploaded );

			// If chunk could not be uploaded, return.
			if ( is_wp_error( $session_id ) ) {
				return $session_id;
			}

			// Calculate uploaded and remaining sizes.
			$uploaded  += self::UPLOAD_SESSION_CHUNK_SIZE;
			$remaining -= self::UPLOAD_SESSION_CHUNK_SIZE;

		}

		// Finish upload session.
		return $this->upload_session_finish( $session_id, $local_file_path, $destination_file_path, $overwrite_file, $uploaded, $remaining );

	}

	/**
	 * Start the Dropbox upload session.
	 *
	 * @since 4.0
	 *
	 * @param string $local_file_path File to be uploaded.
	 *
	 * @return false|string
	 */
	private function upload_session_start( $local_file_path ) {

		// Get file contents.
		$fstream = fopen( $local_file_path, 'r' );
		$fdata   = fread( $fstream, self::UPLOAD_SESSION_CHUNK_SIZE );

		$session = $this->make_upload_request( 'upload_session/start', $fdata, [ 'close' => false ] );

		return is_wp_error( $session ) ? false : rgar( $session, 'session_id', false );

	}

	/**
	 * Append file data to Dropbox upload session.
	 *
	 * @since 4.0
	 *
	 * @param string $session_id      Upload session ID.
	 * @param string $local_file_path File to be uploaded.
	 * @param int    $uploaded        The amount of data, in bytes, uploaded so far.
	 *
	 * @return string|WP_Error
	 */
	private function upload_session_append( $session_id, $local_file_path, $uploaded ) {

		// Get file contents.
		$fstream = fopen( $local_file_path, 'r' );
		fseek( $fstream, $uploaded );
		$fdata = fread( $fstream, self::UPLOAD_SESSION_CHUNK_SIZE );

		// Prepare parameters.
		$params = [
			'close'  => false,
			'cursor' => [
				'session_id' => $session_id,
				'offset'     => $uploaded,
			],
		];

		// Upload chunk.
		$response = $this->make_upload_request( 'upload_session/append_v2', $fdata, $params );

		return is_wp_error( $response ) ? $response : $session_id;

	}

	/**
	 * Finish Dropbox upload session.
	 *
	 * @param string $session_id      Upload session ID.
	 * @param string $local_file_path File to be uploaded.
	 * @param string $destination_file_path Path to upload in Dropbox.
	 * @param bool   $overwrite_file        Overwrite file if already exists.
	 * @param int    $uploaded   The amount of data, in bytes, uploaded so far.
	 * @param int    $remaining  THe amount of data, in bytes, to upload.
	 *
	 * @return array|WP_Error
	 */
	private function upload_session_finish( $session_id, $local_file_path, $destination_file_path, $overwrite_file, $uploaded, $remaining ) {

		// Get file contents.
		$fstream = fopen( $local_file_path, 'r' );
		fseek( $fstream, $uploaded );
		$fdata = fread( $fstream, $remaining );

		// Prepare parameters.
		$params = [
			'cursor' => [
				'session_id' => $session_id,
				'offset'     => $uploaded,
			],
			'commit' => [
				'autorename' => true,
				'mode'       => $overwrite_file ? 'overwrite' : 'add',
				'path'       => $destination_file_path,
			],
		];

		return $this->make_upload_request( 'upload_session/finish', $fdata, $params );

	}





	// # REQUEST METHODS -----------------------------------------------------------------------------------------------

	/**
	 * Make API request.
	 *
	 * @since 4.0
	 *
	 * @param string $action Request action.
	 * @param array  $params Request params.
	 * @param string $method Request method.
	 *
	 * @return array|string|WP_Error
	 */
	private function make_request( $action, $params = null, $method = 'POST' ) {

		// Build request URL.
		$request_url = self::API_URL . $action;

		// Build request arguments.
		$request_args = [
			'body'    => wp_json_encode( $params ),
			'method'  => $method,
			'timeout' => 120,
			'headers' => [
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->access_token,
			],
		];

		// Execute request.
		$result = wp_remote_request( $request_url, $request_args );

		// If response is an error, return error.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// If an error status code was returned, return error.
		if ( wp_remote_retrieve_response_code( $result ) >= 400 ) {
			return new WP_Error( wp_remote_retrieve_response_code( $result ), wp_remote_retrieve_body( $result ) );
		}

		// Decode response.
		$response = wp_remote_retrieve_body( $result );
		$response = fg_fillablepdfs()->is_json( $response ) ? json_decode( $response, true ) : $response;

		return $response;

	}

	/**
	 * Make API request for a chunked upload session.
	 *
	 * @since 4.0
	 *
	 * @param string $action Request action.
	 * @param string $body   Request body.
	 * @param array  $params Request params.
	 *
	 * @return array|string|WP_Error
	 */
	private function make_upload_request( $action, $body, $params ) {

		// Build request URL.
		$request_url = 'https://content.dropboxapi.com/2/files/' . $action;

		// Build request arguments.
		$request_args = [
			'body'    => $body,
			'method'  => 'POST',
			'timeout' => 120,
			'headers' => [
				'Authorization'   => 'Bearer ' . $this->access_token,
				'Content-Type'    => 'application/octet-stream',
				'Dropbox-API-Arg' => wp_json_encode( $params ),
			],
		];

		// Execute request.
		$result = wp_remote_request( $request_url, $request_args );

		// If response is an error, return error.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// If an error status code was returned, return error.
		if ( wp_remote_retrieve_response_code( $result ) >= 400 ) {
			return new WP_Error( wp_remote_retrieve_response_code( $result ), wp_remote_retrieve_body( $result ) );
		}

		// Decode response.
		$response = wp_remote_retrieve_body( $result );
		$response = fg_fillablepdfs()->is_json( $response ) ? json_decode( $response, true ) : $response;

		// If error was found, return error.
		if ( rgar( $response, 'error' ) ) {
			return new WP_Error( rgars( $response, 'error/.tag' ), $response['error_summary'] );
		}

		return $response;

	}





	// # AUTHENTICATION METHODS ----------------------------------------------------------------------------------------

	/**
	 * Determines if the Access Token has expired.
	 *
	 * @since 4.0
	 *
	 * @return bool
	 */
	private function is_access_token_expired() {

		if ( empty( $this->refresh_token ) ) {
			fg_fillablepdfs()->log_debug( __METHOD__ . '(): No refresh token available; not refreshing access token.' );
			return false;
		}

		if ( $this->token_expiration_time > time() ) {
			fg_fillablepdfs()->log_debug( __METHOD__ . '(): Access token has not expired yet.' );
			return false;
		}

		return true;

	}

	/**
	 * Refreshes the Access Token.
	 *
	 * @since 4.0
	 */
	private function refresh_access_token() {

		$plugin_settings = fg_fillablepdfs()->get_plugin_settings();

		$result = wp_remote_request(
			FG_EDD_STORE_URL . '/wp-json/fg/v2/auth/dropbox/refresh',
			[
				'method' => 'POST',
				'body'   => [
					'license'       => rgar( $plugin_settings, 'license_key' ),
					'product'       => str_replace( 'forgravity-', '', fg_fillablepdfs()->get_slug() ),
					'refresh_token' => $this->refresh_token,
					'site_url'      => home_url(),
				],
			]
		);

		$response_code = wp_remote_retrieve_response_code( $result );
		$message       = '';

		if ( $response_code > 200 ) {

			// Set the access_token to an empty string.
			$this->access_token = '';

			// Whenever API couldn't refresh the token, wipe out the tokens.
			unset( $plugin_settings['integrations']['dropbox'] );
			fg_fillablepdfs()->update_plugin_settings( $plugin_settings );

			// Set default warning.
			// translators: 1. Open a tag 2. Close a tag.
			$message = sprintf(
				esc_html__( 'Fillable PDFs cannot upload your generated PDFs to Dropbox. You need to %1$sauthenticate with Dropbox%2$s again to fix this issue.', 'forgravity_fillablepdfs' ),
				'<a href="' . esc_url( fg_fillablepdfs()->get_plugin_settings_url() ) . '">',
				'</a>'
			);
		}

		// Return if the API has errors.
		if ( is_wp_error( $result ) ) {
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Cannot refresh token; ' . $result->get_error_message() );

			// Set a dismissible message.
			GFCommon::add_dismissible_message( $message, self::ERROR_MESSAGE_KEY, 'error', fg_fillablepdfs()->get_capabilities( 'settings_page' ), true );

			return;
		}

		// Decode response.
		$response = json_decode( wp_remote_retrieve_body( $result ), true );

		// If the response contains API error.
		if ( rgar( $response, 'code' ) === 'fg_auth_api_error' ) {
			// Log the error.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Cannot refresh token on Dropbox.' );

			// Set a dismissible message.
			GFCommon::add_dismissible_message( $message, self::ERROR_MESSAGE_KEY, 'error', fg_fillablepdfs()->get_capabilities( 'settings_page' ), true );

			return;
		}

		// If the response contains the forbidden error.
		if ( rgar( $response, 'code' ) === 'fg_auth_forbidden' ) {
			// Log the error.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Cannot refresh token; ' . rgar( $response, 'message' ) );

			// Get the error data.
			$data = rgar( $response, 'data' );

			// translators: 1. Open a tag 2. The action 3. Close a tag.
			$message = sprintf(
				esc_html__( 'Fillable PDFs cannot connect to Dropbox because your license is not valid. Please %1$s%2$s%3$s.', 'forgravity_fillablepdfs' ),
				'<a href="' . esc_url( $data['url'] ) . '" target="_blank">',
				$this->get_license_action_i18n( $data['label'] ),
				'</a>'
			);

			// Set a dismissible message.
			GFCommon::add_dismissible_message( $message, self::ERROR_MESSAGE_KEY, 'error', fg_fillablepdfs()->get_capabilities( 'settings_page' ), true );

			return;
		}

		// Update the access token data.
		$this->access_token          = rgar( $response, 'access_token' );
		$this->token_expiration_time = time() + intval( rgar( $response, 'expires_in' ) );

		// Prepare the settings to be updated.
		$plugin_settings['integrations']['dropbox'] = [
			'access_token'    => $this->access_token,
			'refresh_token'   => $this->refresh_token,
			'expiration_time' => $this->token_expiration_time,
		];
		fg_fillablepdfs()->update_plugin_settings( $plugin_settings );

	}

	/**
	 * Turn the license action label into a translated string.
	 *
	 * @since 4.0
	 *
	 * @param string $label The label.
	 *
	 * @return string
	 */
	private function get_license_action_i18n( $label ) {

		switch ( $label ) {

			case 'Renew License':
				return esc_html__( 'renew your license', 'forgravity_fillablepdfs' );

			case 'Upgrade License':
				return esc_html__( 'upgrade your license', 'forgravity_fillablepdfs' );

			case 'Activate Site':
				return esc_html__( 'activate your site', 'forgravity_fillablepdfs' );

			default:
				return esc_html__( 'purchase a license', 'forgravity_fillablepdfs' );

		}

	}

	/**
	 * Revokes the provided access token.
	 *
	 * @since 4.0
	 *
	 * @return array|WP_Error
	 */
	public function revoke_token() {

		return $this->make_request( 'auth/token/revoke' );

	}

}

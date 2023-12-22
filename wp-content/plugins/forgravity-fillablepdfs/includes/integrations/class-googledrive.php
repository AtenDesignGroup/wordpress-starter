<?php
/**
 * Google Drive Integration class.
 *
 * @since 4.5
 *
 * @package ForGravity\Fillable_PDFs
 */

namespace ForGravity\Fillable_PDFs\Integrations;

defined( 'ABSPATH' ) || die();

use Exception;
use GFCommon;
use WP_Error;

use League\Flysystem\Filesystem;
use Google_Client;
use Google_Service_Drive;
use Masbug\Flysystem\GoogleDriveAdapter;

/**
 * Google Drive Integration class.
 *
 * @since     4.5
 * @package   ForGravity\Fillable_PDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2023, CosmicGiant
 */
class GoogleDrive extends Base implements Connectable {

	/**
	 * Google Drive brand color.
	 *
	 * @since 4.5
	 *
	 * @var string
	 */
	protected $color = '#2684FC';

	/**
	 * Name of third party Integration.
	 *
	 * @since 4.5
	 *
	 * @var string
	 */
	protected $name = 'Google Drive';

	/**
	 * Slug of third party Integration.
	 *
	 * @since 4.5
	 *
	 * @var string
	 */
	protected $type = 'googledrive';

	/**
	 * Google Drive API instance.
	 *
	 * @since 4.5
	 *
	 * @var false|Google_Client
	 */
	private $api;





	// # FORM SUBMISSION -----------------------------------------------------------------------------------------------

	/**
	 * Uploaded generated PDF to Google Drive.
	 *
	 * @since 4.5
	 *
	 * @param array $pdf_meta PDF meta properties.
	 * @param array $entry    The current Entry object.
	 * @param array $form     The current Form object.
	 * @param array $feed     The current Feed object.
	 */
	public function action_fg_fillablepdfs_after_generate( $pdf_meta, $entry, $form, $feed ) {

		$settings = rgars( $feed, 'meta/integrations/' . $this->type );

		if ( ! rgar( $settings, 'enable' ) ) {
			return;
		}

		if ( ! $this->api() ) {
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Cannot upload PDF to Google Drive because API is not initialized.' );
			return;
		}

		// Initialize Flysystem.
		$drive      = new Google_Service_Drive( $this->api() );
		$adapter    = new GoogleDriveAdapter( $drive );
		$filesystem = new Filesystem( $adapter );

		// If API could not be initialized, return.
		if ( ! $filesystem ) {
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Unable to upload PDF to Google Drive because API could not be initialized.' );
			return;
		}

		// Prepare destination file path.
		$destination_path = GFCommon::replace_variables( $settings['folder'], $form, $entry, false, false, false, 'text' );
		$destination_path = trailingslashit( $destination_path ) . $this->get_file_name( $pdf_meta, $feed, $entry, $form );

		try {

			// Upload file.
			if ( $filesystem->has( $destination_path ) ) {
				$filesystem->updateStream( $destination_path, fopen( $pdf_meta['file_path'], 'r' ) );
			} else {
				$filesystem->writeStream( $destination_path, fopen( $pdf_meta['file_path'], 'r' ) );
			}

			fg_fillablepdfs()->log_debug( __METHOD__ . '(): Generated PDF successfully uploaded to Google Drive.' );

		} catch ( Exception $e ) {

			// Log that file could not be uploaded.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Unable to upload generated PDF to Google Drive; ' . $e->getMessage() );

		}

	}





	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Returns current user authenticated with Google Drive for plugin settings page.
	 *
	 * @since 4.5
	 *
	 * @return string
	 */
	public function get_connected_message() {

		if ( ! $this->is_connected() ) {
			return '';
		}

		try {

			// Get account information.
			$drive   = new Google_Service_Drive( $this->api );
			$account = $drive->about->get( [ 'fields' => 'user' ] );

			return sprintf(
				'%1$s<br /><strong>%2$s</strong>',
				esc_html__( 'Authenticated as:', 'forgravity_fillablepdfs' ),
				esc_html( $account->user->displayName )
			);

		} catch ( Exception $e ) {

			return '';

		}

	}





	// # AUTHENTICATION ------------------------------------------------------------------------------------------------

	/**
	 * Revoke Google Drive access token.
	 *
	 * @since 4.5
	 *
	 * @return true|WP_Error
	 */
	public function disconnect() {

		// If API cannot be initialized, exit.
		if ( ! $this->api() ) {
			return new WP_Error( 'api_not_initialized', __( 'Could not initialize API.', 'forgravity_fillablepdfs' ) );
		}

		try {

			// Revoke access token.
			$this->api()->revokeToken();

			// Remove authentication data from plugin settings.
			$settings = fg_fillablepdfs()->get_plugin_settings();
			unset( $settings['integrations'][ $this->type ] );
			fg_fillablepdfs()->update_plugin_settings( $settings );

		} catch ( Exception $e ) {

			// Log that we could not revoke the access token.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Unable to revoke access token; ' . $e->getMessage() );

			// Return error response.
			return new WP_Error( $e->getCode(), $e->getMessage() );

		}
		return true;

	}

	/**
	 * Returns the URL to start the authentication process.
	 *
	 * @since 4.5
	 *
	 * @return string
	 */
	public function get_auth_url() {

		$license = fg_fillablepdfs()->get_plugin_setting( 'license_key' );
		$product = str_replace( 'forgravity-', '', fg_fillablepdfs()->get_slug() );

		$auth_url = add_query_arg(
			[
				'license'     => $license,
				'redirect_to' => rawurlencode( fg_fillablepdfs()->get_plugin_settings_url() ),
				'product'     => $product,
				'state'       => fg_fillablepdfs()->get_authentication_state(),
			],
			FG_EDD_STORE_URL . '/wp-json/fg/v2/auth/googledrive'
		);

		return $auth_url;

	}





	// # INTEGRATION DETAILS -------------------------------------------------------------------------------------------

	/**
	 * Returns the Google Drive description.
	 *
	 * @since 4.5
	 *
	 * @return string
	 */
	public function get_description() {

		return esc_html__( 'Connect to have generated PDFs delivered directly to Google Drive.', 'forgravity_fillablepdfs' );

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Returns an instance of the Google Client, if available.
	 *
	 * @since 4.5
	 *
	 * @return null|false|Google_Client
	 */
	public function api() {

		if ( ! is_null( $this->api ) ) {
			return $this->api;
		}

		// Get plugin settings.
		$settings  = rgars( fg_fillablepdfs()->get_plugin_settings(), 'integrations/' . $this->type );
		$auth_data = fg_fillablepdfs()->maybe_decode_json( $settings );

		// If no access token exists, return.
		if ( rgblank( rgar( $auth_data, 'access_token' ) ) ) {
			return null;
		}

		fg_fillablepdfs()->log_debug( __METHOD__ . '(): Validating API credentials.' );

		try {

			// Get the access token.
			$access_token = $this->get_access_token( $settings );

			// Initialize new Google client.
			$client = new Google_Client();
			$this->set_http_client( $client );
			$client->setAccessToken( $access_token );

			// Log that credentials are valid.
			fg_fillablepdfs()->log_debug( __METHOD__ . '(): API credentials are valid.' );

			// Assign Google Client to the class.
			$this->api = $client;

		} catch ( Exception $e ) {

			// Log that credentials are invalid.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): API credentials are invalid; ' . $e->getMessage() );

			return false;

		}

		return $this->api;

	}

	/**
	 * Returns if the site is successfully connected to Google Drive.
	 *
	 * @since 4.5
	 *
	 * @return bool
	 */
	public function is_connected() {

		return is_a( $this->api(), Google_Client::class );

	}

	/**
	 * Return the access token.
	 *
	 * @since 4.5
	 *
	 * @param array $auth_data Authentication data.
	 *
	 * @return string
	 */
	private function get_access_token( $auth_data = [] ) {

		if ( ! $auth_data ) {
			$auth_data = rgars( fg_fillablepdfs()->get_plugin_settings(), 'integrations/' . $this->type );
		}

		if ( ! is_array( $auth_data ) ) {
			return $auth_data;
		}

		// Refresh the access token if it is about to expire.
		if ( rgar( $auth_data, 'expiration_time' ) - 300 < time() ) {
			$this->refresh_access_token( $auth_data );
		}

		return rgar( $auth_data, 'access_token' );

	}

	/**
	 * Refresh the access token.
	 *
	 * @since 4.5
	 *
	 * @param array $auth_data The auth data.
	 */
	private function refresh_access_token( &$auth_data ) {

		$settings = fg_fillablepdfs()->get_plugin_settings();

		$result = wp_remote_post(
			FG_EDD_STORE_URL . '/wp-json/fg/v2/auth/googledrive/refresh',
			[
				'body' => [
					'refresh_token' => rgar( $auth_data, 'refresh_token' ),
					'product'       => fg_fillablepdfs()->get_slug(),
					'license'       => rgar( $settings, 'license_key' ), // @todo Use get_license_key() method.
					'site_url'      => home_url(),
				],
			]
		);

		$response_code = wp_remote_retrieve_response_code( $result );
		$message       = '';

		if ( $response_code > 200 ) {
			// Set the access_token to an empty string.
			$auth_data['access_token'] = '';

			// Whenever API couldn't refresh the token, wipe out the tokens.
			unset( $settings['integrations'][ $this->type ] );
			fg_fillablepdfs()->update_plugin_settings( $settings );

			// Set default warning.
			// translators: 1. Open a tag 2. Close a tag.
			$message = sprintf(
				esc_html__( 'Fillable PDFs cannot upload your exported files to Google Drive. You need to %1$sauthenticate with Google Drive%2$s again to fix this issue.', 'forgravity_fillablepdfs' ),
				"<a href='" . esc_url( fg_fillablepdfs()->get_plugin_settings_url() ) . "'>",
				'</a>'
			);
		}

		$dismissible_message_key = fg_fillablepdfs()->get_slug() . '_googledrive_token_not_refreshed';

		// Return if the API has errors.
		if ( is_wp_error( $result ) ) {
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Cannot refresh token; ' . $result->get_error_message() );

			// Set a dismissible message.
			GFCommon::add_dismissible_message( $message, $dismissible_message_key, 'error', fg_fillablepdfs()->get_capabilities( 'settings_page' ), true );

			return;
		}

		// Decode response.
		$response = json_decode( wp_remote_retrieve_body( $result ), true );

		// If the response contains API error.
		if ( rgar( $response, 'code' ) === 'fg_auth_api_error' ) {
			// Log the error.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Cannot refresh token on Dropbox.' );

			// Set a dismissible message.
			GFCommon::add_dismissible_message( $message, $dismissible_message_key, 'error', fg_fillablepdfs()->get_capabilities( 'settings_page' ), true );

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
				esc_html__( 'Fillable PDFs cannot connect to Google Drive because your license is not valid. Please %1$s%2$s%3$s.', 'forgravity_fillablepdfs' ),
				'<a href="' . esc_url( $data['url'] ) . '" target="_blank">',
				$this->get_license_action_i18n( $data['label'] ),
				'</a>'
			);

			// Set a dismissible message.
			GFCommon::add_dismissible_message( $message, $dismissible_message_key, 'error', fg_fillablepdfs()->get_capabilities( 'settings_page' ), true );

			return;
		}

		// Update the access token data.
		$auth_data['access_token']    = rgar( $response, 'access_token' );
		$auth_data['expiration_time'] = time() + intval( rgar( $response, 'expires_in' ) );

		// Prepare the settings to be updated.
		$settings['integrations'][ $this->type ] = $auth_data;
		fg_fillablepdfs()->update_plugin_settings( $settings );

	}

	/**
	 * Turn the license action label into a translated string.
	 *
	 * @since 4.5
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
	 * Set HTTP client options. Can be used to bypass SSL verification.
	 *
	 * @since 4.5
	 *
	 * @param Google_Client $client The Google client.
	 */
	private function set_http_client( &$client ) {

		// Allow bypassing SSL verify with the filter.
		if ( ! apply_filters( 'https_local_ssl_verify', true ) ) {
			$guzzleClient = new \GuzzleHttp\Client( [ 'verify' => false ] );
			$client->setHttpClient( $guzzleClient );
		}

	}

}

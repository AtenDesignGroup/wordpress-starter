<?php
/**
 * Droopbox Integration class.
 *
 * @since 4.0
 *
 * @package ForGravity\Fillable_PDFs
 */

namespace ForGravity\Fillable_PDFs\Integrations;

defined( 'ABSPATH' ) || die();

use ForGravity\Fillable_PDFs\Integrations\Dropbox\API;
use GFCommon;
use WP_Error;

/**
 * Dropbox Integration class.
 *
 * @since 4.0
 * @package   ForGravity\Fillable_PDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2022, ForGravity
 */
class Dropbox extends Base implements Connectable {

	/**
	 * Dropbox brand color.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	protected $color = '#0061FF';

	/**
	 * Name of third party Integration.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	protected $name = 'Dropbox';

	/**
	 * Slug of third party Integration.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	protected $type = 'dropbox';

	/**
	 * Dropbox API instance.
	 *
	 * @since 4.0
	 *
	 * @var false|Dropbox\API
	 */
	private $api;





	// # FORM SUBMISSION -----------------------------------------------------------------------------------------------

	/**
	 * Uploaded generated PDF to Dropbox.
	 *
	 * @since 4.0
	 *
	 * @param array $pdf_meta PDF meta properties.
	 * @param array $entry    The current Entry object.
	 * @param array $form     The current Form object.
	 * @param array $feed     The current Feed object.
	 */
	public function action_fg_fillablepdfs_after_generate( $pdf_meta, $entry, $form, $feed ) {

		$settings = rgars( $feed, 'meta/integrations/dropbox' );

		if ( ! rgar( $settings, 'enable' ) ) {
			return;
		}

		if ( ! $this->api() ) {
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Cannot upload PDF to Dropbox because API is not initialized.' );
			return;
		}

		// Prepare destination file path.
		$destination_path = GFCommon::replace_variables( $settings['folder'], $form, $entry, false, false, false, 'text' );
		$destination_path = trailingslashit( $destination_path ) . $this->get_file_name( $pdf_meta, $feed, $entry, $form );

		$uploaded = $this->api()->upload( $pdf_meta['file_path'], $destination_path, (bool) rgar( $settings, 'overwrite' ) );

		if ( is_wp_error( $uploaded ) ) {
			fg_fillablepdfs()->add_feed_error( 'Unable to upload generated PDF to Dropbox: ' . $uploaded->get_error_message(), $feed, $entry, $form );
		} else {
			fg_fillablepdfs()->log_debug( __METHOD__ . '(): Generated PDF successfully uploaded to Dropbox.' );
		}

	}





	// # FEED SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Returns the feed settings for Dropbox.
	 *
	 * @since 4.0
	 *
	 * @return array
	 */
	public function feed_settings_fields() {

		$fields             = parent::feed_settings_fields();
		$fields['fields'][] = [
			'name'       => 'integrations[dropbox][overwrite]',
			'label'      => esc_html__( 'Overwrite File If Already Exists?', 'forgravity_fillablepdfs' ),
			'type'       => 'toggle',
			'dependency' => $this->get_integration_enabled_dependency(),
		];

		return $fields;

	}





	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Returns current user authenticated with Dropbox for plugin settings page.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_connected_message() {

		if ( ! $this->is_connected() ) {
			return '';
		}

		$account = $this->api()->get_current_account();

		if ( is_wp_error( $account ) ) {
			return '';
		}

		return sprintf(
			'%1$s<br /><strong>%2$s</strong>',
			esc_html__( 'Authenticated as:', 'forgravity_fillablepdfs' ),
			esc_html( rgars( $account, 'name/display_name' ) )
		);

	}





	// # AUTHENTICATION ------------------------------------------------------------------------------------------------

	/**
	 * Revoke Dropbox access token.
	 *
	 * @since 4.0
	 *
	 * @return true|WP_Error
	 */
	public function disconnect() {

		// If API cannot be initialized, exit.
		if ( ! $this->api() ) {
			return new WP_Error( 'api_not_initialized', __( 'Could not initialize API.', 'forgravity_fillablepdfs' ) );
		}

		// Revoke access token.
		$revoked = $this->api()->revoke_token();

		if ( is_wp_error( $revoked ) ) {
			return $revoked;
		}

		// Remove authentication data from plugin settings.
		$settings = fg_fillablepdfs()->get_plugin_settings();
		unset( $settings['integrations']['dropbox'] );
		fg_fillablepdfs()->update_plugin_settings( $settings );

		return true;

	}

	/**
	 * Returns the URL to start the authentication process.
	 *
	 * @since 4.0
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
			FG_EDD_STORE_URL . '/wp-json/fg/v2/auth/dropbox'
		);

		return $auth_url;

	}





	// # INTEGRATION DETAILS -------------------------------------------------------------------------------------------

	/**
	 * Returns the Dropbox description.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_description() {

		return esc_html__( 'Connect to have generated PDFs delivered directly to Dropbox.', 'forgravity_fillablepdfs' );

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Returns an instance of the Dropbox API library, if available.
	 *
	 * @since 4.0
	 *
	 * @return null|false|Dropbox\API
	 */
	public function api() {

		if ( ! is_null( $this->api ) ) {
			return $this->api;
		}

		// Get plugin settings.
		$settings  = rgars( fg_fillablepdfs()->get_plugin_settings(), 'integrations/dropbox' );
		$auth_data = fg_fillablepdfs()->maybe_decode_json( $settings );

		// If no access token exists, return.
		if ( rgblank( rgar( $auth_data, 'access_token' ) ) ) {
			return null;
		}

		fg_fillablepdfs()->log_debug( __METHOD__ . '(): Validating API credentials.' );

		// Initialize the API library and make a test request.
		$api     = new Dropbox\API( $auth_data );
		$account = $api->get_current_account();

		// If test request failed, return.
		if ( is_wp_error( $account ) ) {
			fg_fillablepdfs()->log_error( __METHOD__ . '(): API credentials are invalid; ' . $account->get_error_message() );
			return false;
		}

		fg_fillablepdfs()->log_debug( __METHOD__ . '(): API credentials are valid.' );

		// Assign API library to instance.
		$this->api = $api;

		return $this->api;

	}

	/**
	 * Returns if the site is successfully connected to Dropbox.
	 *
	 * @since 4.0
	 *
	 * @return bool
	 */
	public function is_connected() {

		return is_a( $this->api(), API::class );

	}

}

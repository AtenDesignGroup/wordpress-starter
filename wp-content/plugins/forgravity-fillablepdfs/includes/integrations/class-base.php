<?php
/**
 * Base Integration class.
 * Handles all functionality shared across Integrations
 *
 * @since 4.0
 *
 * @package ForGravity\Fillable_PDFs
 */

namespace ForGravity\Fillable_PDFs\Integrations;

use ForGravity\Fillable_PDFs\Utils\File_Path;
use GFCommon;
use Gravity_Forms\Gravity_Forms\Settings\Fields as Settings_API_Fields;
use WP_Error;

defined( 'ABSPATH' ) || die();

/**
 * Base Integration class.
 * Handles all functionality shared across Integrations.
 *
 * @since 4.0
 * @package   ForGravity\Fillable_PDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2022, ForGravity
 */
abstract class Base {

	/**
	 * Integration brand color.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	protected $color;

	/**
	 * Description of the third party Integration.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Name of third party Integration.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Slug of third party Integration.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Stores the License information.
	 *
	 * @since 4.0
	 *
	 * @var object
	 */
	private $license;

	/**
	 * Registers needed hooks.
	 *
	 * @since 4.0
	 */
	public function add_hooks() {

		add_action( 'parse_request', [ $this, 'handle_auth_response' ] );

		add_filter( 'fg_fillablepdfs_plugin_settings_fields', [ $this, 'filter_plugin_settings_fields' ] );
		add_action( 'fg_fillablepdfs_after_generate', [ $this, 'action_fg_fillablepdfs_after_generate' ], 11, 4 );

		add_filter( 'gform_' . fg_fillablepdfs()->get_slug() . '_feed_settings_fields', [ $this, 'filter_feed_settings_fields' ], 10, 1 );

		add_action( 'wp_ajax_fg_fillablepdfs_integration_disconnect', [ $this, 'handle_ajax_disconnect' ] );

	}





	// # FEED SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Adds the Integrations settings to the feed settings.
	 * Inserts Integrations tab if it does not already exist.
	 *
	 * @since 4.0
	 *
	 * @param array $settings Feed settings tabs.
	 *
	 * @return array
	 */
	public function filter_feed_settings_fields( $settings ) {

		// Register Enable field type.
		Settings_API_Fields::register( 'fg_fillablepdfs_integration_enable', '\ForGravity\Fillable_PDFs\Integrations\Settings\Enable' );

		// Add Integrations tab.
		$settings = $this->add_integrations_tab( $settings );

		// Get array key for Integrations tab.
		$integrations_tab_array_key = $this->get_integrations_tab_array_key( $settings );

		$settings[ $integrations_tab_array_key ]['sections'] = array_merge(
			$settings[ $integrations_tab_array_key ]['sections'],
			[ $this->feed_settings_fields() ]
		);

		return $settings;

	}

	/**
	 * Returns the feed settings for the Integration.
	 *
	 * @since 4.0
	 *
	 * @return array
	 */
	protected function feed_settings_fields() {

		$enabled_dependency = $this->get_integration_enabled_dependency();

		return [
			'title'  => esc_html( $this->name ),
			'fields' => [
				[
					'name'        => sprintf( 'integrations[%1$s][enable]', $this->type ),
					'type'        => 'fg_fillablepdfs_integration_enable',
					'integration' => $this,
					'action'      => sprintf(
						esc_html__( 'Upload PDF to %1$s', 'forgravity_fillablepdfs' ),
						esc_html( $this->name )
					),
				],
				[
					'name'                => sprintf( 'integrations[%1$s][folder]', $this->type ),
					'label'               => esc_html__( 'Destination Folder', 'forgravity_fillablepdfs' ),
					'type'                => 'text',
					'required'            => true,
					'class'               => 'merge-tag-support mt-position-right mt-hide_all_fields',
					'dependency'          => $enabled_dependency,
					'validation_callback' => function( $field, $value ) {
						if ( strpos( $value, '/' ) !== 0 ) {
							$field->set_error( esc_html__( 'Destination Folder must start with a slash.', 'forgravity_fillablepdfs' ) );
						}
					},
				],
				[
					'name'        => sprintf( 'integrations[%1$s][alternateFileName]', $this->type ),
					'label'       => esc_html__( 'Alternate File Name', 'forgravity_fillablepdfs' ),
					'type'        => 'text',
					'description' => esc_html__( 'Leave blank to use the default PDF file name.', 'forgravity_fillablepdfs' ),
					'class'       => 'merge-tag-support mt-position-right mt-hide_all_fields',
					'dependency'  => $enabled_dependency,
				],
			],
		];

	}

	/**
	 * Returns the dependency array to display feed settings when the Integration is enabled.
	 *
	 * @since 4.5
	 *
	 * @return array
	 */
	protected function get_integration_enabled_dependency() {

		return [
			'live'     => true,
			'fields'   => [
				[ 'field' => 'integrations[' . $this->type . '][enable]' ],
			],
			'callback' => [
				'js'  => 'fg_fillablepdfs_integration_enable',
				'php' => function( $settings ) {
					return $this->is_connected() && (bool) $settings->get_value( 'integrations[' . $this->type . '][enable]' );
				},
			],
		];

	}




	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Adds the Integrations settings to the plugin settings.
	 * Inserts Integrations tab if it does not already exist.
	 *
	 * @since 4.0
	 *
	 * @param array $settings Plugin settings tabs.
	 *
	 * @return array
	 */
	public function filter_plugin_settings_fields( $settings ) {

		// Register Authentication field type.
		Settings_API_Fields::register( 'fg_fillablepdfs_integration_auth', '\ForGravity\Fillable_PDFs\Integrations\Settings\Auth' );

		// Add Integrations tab.
		$settings = $this->add_integrations_tab( $settings );

		// Get array key for Integrations tab.
		$integrations_tab_array_key = $this->get_integrations_tab_array_key( $settings );

		if ( empty( $settings[ $integrations_tab_array_key ]['sections'] ) ) {
			$settings[ $integrations_tab_array_key ]['sections'][0] = [
				'title'  => esc_html__( 'Available Integrations', 'forgravity_fillablepdfs' ),
				'fields' => [],
			];
		}

		$settings[ $integrations_tab_array_key ]['sections'][0]['fields'][] = [
			'name'        => 'integrations[' . $this->type . ']',
			'type'        => 'fg_fillablepdfs_integration_auth',
			'integration' => $this,
		];

		return $settings;

	}





	// # AUTHENTICATION ------------------------------------------------------------------------------------------------

	/**
	 * Save authentication data.
	 *
	 * @since 4.5
	 *
	 * @param array $auth_data Authentication data.
	 *
	 * @return bool
	 */
	public function save_auth_data( $auth_data ) {

		// Get current plugin settings.
		$settings = fg_fillablepdfs()->get_plugin_settings();

		// Add access token to plugin settings.
		$settings['integrations'][ $this->type ] = [
			'access_token'    => sanitize_text_field( rgar( $auth_data, 'access_token' ) ),
			'refresh_token'   => sanitize_text_field( rgar( $auth_data, 'refresh_token' ) ),
			'expiration_time' => time() + intval( rgar( $auth_data, 'expires_in' ) ),
		];

		// Save plugin settings.
		fg_fillablepdfs()->update_plugin_settings( $settings );

		return true;

	}

	/**
	 * Handle Integration authentication response.
	 *
	 * @since 4.0
	 */
	public function handle_auth_response() {

		$host = isset( $_SERVER['HTTP_REFERER'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), PHP_URL_HOST ) : [];

		// If it's not coming back from FG website, exit.
		if ( $host !== wp_parse_url( FG_EDD_STORE_URL, PHP_URL_HOST ) ) {
			return;
		}

		// If the response if not for this service, exit.
		$slug = sprintf( 'fg_%1$s_%2$s', $this->type, str_replace( 'forgravity-', '', fg_fillablepdfs()->get_slug() ) );
		if ( ! $response = rgpost( $slug ) ) {
			return;
		}

		// Decode the response.
		$auth_data = json_decode( $response, true );

		// If the access token was not presented, redirect with errors.
		if ( ! $access_token = rgar( $auth_data, 'access_token' ) ) {
			$this->auth_error_redirect();
		}

		// If state does not match, redirect with errors.
		$state = get_transient( fg_fillablepdfs()->get_authentication_state_action() );
		if ( ! rgar( $auth_data, 'state' ) || rgar( $auth_data, 'state' ) !== $state ) {
			$this->auth_error_redirect();
		}

		// Delete the authentication state.
		fg_fillablepdfs()->delete_authentication_state();

		// Connect to service.
		$connected = ! is_wp_error( $this->save_auth_data( $auth_data ) );

		// If we were able to connect to the service, redirect back to the settings page.
		if ( $connected ) {
			wp_safe_redirect( fg_fillablepdfs()->get_plugin_settings_url(), 303 );
			exit();
		}

		// Otherwise, throw an error.
		$this->auth_error_redirect();

	}

	/**
	 * Processes the AJAX Integration disconnect request.
	 *
	 * @since 4.0
	 */
	public function handle_ajax_disconnect() {

		// If we are not processing the disconnect request for this Integration, exit.
		if ( $this->type !== rgpost( 'integration' ) ) {
			return;
		}

		// Verify nonce.
		if ( wp_verify_nonce( rgpost( 'nonce' ), fg_fillablepdfs()->get_slug() ) === false ) {
			wp_send_json_error( esc_html__( 'Access denied.', 'forgravity_fillablepdfs' ) );
		}

		// If user is not authorized, exit.
		if ( ! GFCommon::current_user_can_any( fg_fillablepdfs()->get_capabilities( 'settings_page' ) ) ) {
			wp_send_json_error( esc_html__( 'Access denied.', 'forgravity_fillablepdfs' ) );
		}

		$disconnected = $this->disconnect();

		if ( is_wp_error( $disconnected ) ) {
			wp_send_json_error( esc_html( $disconnected->get_error_message() ) );
		} else {
			wp_send_json_success();
		}

	}

	/**
	 * Redirect to the Fillable PDFs plugin settings page with an authentication error query parameter.
	 *
	 * @since 4.0
	 */
	protected function auth_error_redirect() {

		$settings_url = add_query_arg( 'auth_error', 'true', fg_fillablepdfs()->get_plugin_settings_url() );
		wp_safe_redirect( $settings_url, 303 );
		exit();

	}





	// # INTEGRATION DETAILS -------------------------------------------------------------------------------------------

	/**
	 * Returns the brand color of the Integration.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_color() {

		return $this->color;

	}

	/**
	 * Returns the description of the Integration.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_description() {

		return $this->description;

	}

	/**
	 * Returns the URL of the Integration logo.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_logo() {

		return fg_fillablepdfs()->get_asset_url( sprintf( 'dist/images/integrations/%1$s/logo.svg', $this->type ) );

	}

	/**
	 * Returns the name of the Integration.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_name() {

		return $this->name;

	}

	/**
	 * Returns the type of Integration.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_type() {

		return $this->type;

	}

	/**
	 * Returns the Upgrade License button for the Integration.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_upgrade_button() {

		$upgrade_url = $this->get_upgrade_url();

		if ( ! $upgrade_url ) {
			return '';
		}

		return sprintf(
			'<a href="%s" class="fillablepdfs-license-feature-upgrade">%s</a>',
			esc_url( $upgrade_url ),
			esc_html__( 'Upgrade License', 'forgravity_fillablepdfs' )
		);

	}

	/**
	 * Returns the upgrade URL for this Integration.
	 *
	 * @since 4.0
	 *
	 * @return string|false
	 */
	public function get_upgrade_url() {

		return $this->get_license_property( 'upgrade_url' );

	}

	/**
	 * Determines if the site has access to this Integration.
	 *
	 * @since 4.0
	 *
	 * @return bool
	 */
	public function has_access() {

		return $this->get_license_property( 'has_access' );

	}




	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Adds the Integrations tab to plugin or feed settings fields.
	 *
	 * @since 4.0
	 *
	 * @param array $tabs Settings fields.
	 *
	 * @return array
	 */
	private function add_integrations_tab( $tabs ) {

		if ( $this->get_integrations_tab_array_key( $tabs ) ) {
			return $tabs;
		}

		$tabs[] = [
			'id'       => $this->get_integrations_tab_id(),
			'title'    => esc_html__( 'Integrations', 'forgravity_fillablepdfs' ),
			'sections' => [],
		];

		return $tabs;

	}

	/**
	 * Returns the ID for the Integrations settings tab.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	private function get_integrations_tab_id() {

		return sprintf( '%1$s-integrations', fg_fillablepdfs()->get_slug() );

	}

	/**
	 * Returns the tab index for the Integrations settings tab.
	 *
	 * @since 4.0
	 *
	 * @param array $tabs Settings fields.
	 *
	 * @return false|int|string
	 */
	private function get_integrations_tab_array_key( $tabs ) {

		$tab_ids = wp_list_pluck( $tabs, 'id' );

		return array_search( $this->get_integrations_tab_id(), $tab_ids );

	}

	/**
	 * Returns the file name of the file to be uploaded.
	 *
	 * @since 4.0
	 *
	 * @param array $pdf_meta PDF meta properties.
	 * @param array $feed     The current Feed object.
	 * @param array $entry    The current Entry object.
	 * @param array $form     The current Form object.
	 *
	 * @return string
	 */
	protected function get_file_name( $pdf_meta, $feed, $entry, $form ) {

		$settings = rgars( $feed, 'meta/integrations/' . $this->type );

		// Get file name.
		$file_name = rgar( $settings, 'alternateFileName' ) ? $settings['alternateFileName'] : $pdf_meta['file_name'];

		// Replace merge tags.
		$file_name = GFCommon::replace_variables( $file_name, $form, $entry, false, false, false, 'text' );
		$file_name = str_replace( '/', '-', $file_name );
		$file_name = sanitize_file_name( $file_name );

		// Force file extension.
		$file_name = File_Path::add_file_extension( $file_name );

		return $file_name;

	}

	/**
	 * Returns the current Fillable PDFs License.
	 *
	 * @since 4.0
	 *
	 * @return object
	 */
	private function get_license() {

		if ( $this->license ) {
			return $this->license;
		}

		$this->license = fg_fillablepdfs()->check_license();

		return $this->license;

	}

	/**
	 * Returns a specific property for the Integration from the License object.
	 *
	 * @since 4.0
	 *
	 * @param string $prop Property name. If not provided, returns the full Integration object from the License.
	 *
	 * @return false|string|object|bool
	 */
	protected function get_license_property( $prop = false ) {

		$license = $this->get_license();

		if ( ! $license || ! isset( $license->integrations->{$this->type} ) ) {
			return false;
		}

		$integration_props = $license->integrations->{$this->type};

		if ( ! $prop ) {
			return $integration_props;
		}

		return isset( $integration_props->{$prop} ) ? $integration_props->{$prop} : false;

	}

}

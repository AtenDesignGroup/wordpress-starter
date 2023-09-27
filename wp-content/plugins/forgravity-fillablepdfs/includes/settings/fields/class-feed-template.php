<?php

namespace ForGravity\Fillable_PDFs\Settings\Fields;

use GFAPI;
use Gravity_Forms\Gravity_Forms\Settings\Fields\Hidden;

defined( 'ABSPATH' ) || die();

class Feed_Template extends Hidden {

	/**
	 * Field type.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $type = 'fg_fillablepdfs_feed_template';

	/**
	 * Register scripts to enqueue when displaying field.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function scripts() {

		// Get parent scripts, minification string.
		$scripts = parent::scripts();
		$min     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		// Add script.
		$scripts[] = [
			'handle'    => 'fg_fillablepdfs_feed_template',
			'src'       => fg_fillablepdfs()->get_asset_url( "dist/js/feed-template{$min}.js" ),
			'version'   => $min ? fg_fillablepdfs()->get_version() : fg_fillablepdfs()->get_asset_filemtime( "js/feed-template{$min}.js" ),
			'deps'      => [ 'wp-element', 'wp-components', 'wp-i18n' ],
			'in_footer' => true,
			'callback'  => [ $this, 'localize_script' ],
		];

		return $scripts;

	}

	/**
	 * Localize field script.
	 *
	 * @since 3.0
	 */
	public function localize_script() {

		if ( ! fg_pdfs_api() ) {
			return;
		}

		wp_localize_script(
			'fg_fillablepdfs_feed_template',
			'fg_fillablepdfs',
			[
				'api_base'     => FG_FILLABLEPDFS_API_URL,
				'entry_meta'   => fg_fillablepdfs()->get_entry_meta_options(),
				'integrations' => fg_fillablepdfs()->get_available_integrations(),
				'nestedForms'  => [
					'nonce' => wp_create_nonce( 'fg_fillablepdfs_get_nested_form' ),
				],
				'templates'    => [
					'available'   => fg_fillablepdfs()->get_templates_as_choices(),
					'nonce'       => wp_create_nonce( 'fg_fillablepdfs_get_feed_template' ),
					'placeholder' => fg_fillablepdfs()->get_asset_url( 'dist/images/templates/placeholder.svg' ),
					'selected'    => $this->get_template(),
				],
			]
		);

	}

	/**
	 * Enqueue needed stylesheets.
	 *
	 * @since  3.0
	 *
	 * @return array
	 */
	public function styles() {

		// Get minification string.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		return [
			[
				'handle'  => fg_fillablepdfs()->get_slug() . '_feed_template',
				'src'     => fg_fillablepdfs()->get_asset_url( "dist/css/feed-template{$min}.css" ),
				'version' => $min ? fg_fillablepdfs()->get_version() : fg_fillablepdfs()->get_asset_filemtime( 'dist/css/feed-template.css' ),
				'enqueue' => [
					[
						'admin_page' => [ 'form_settings' ],
						'tab'        => fg_fillablepdfs()->get_slug(),
					],
				],
			],
		];

	}





	// # RENDER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Render field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function markup() {

		// Insert hidden container to store template ID, React container.
		$html = parent::markup();
		$html .= '<div id="fillablepdfs-feed-template"></div>';
		$html .= $this->get_error_icon();

		return $html;

	}





	// # VALIDATION ----------------------------------------------------------------------------------------------------

	/**
	 * Validate the selected template.
	 *
	 * @since 3.0
	 *
	 * @param string $value
	 */
	public function do_validation( $value ) {

		if ( rgblank( $value ) || $value == '0' ) {
			$this->set_error( esc_html__( 'You must select a template.', 'forgravity_fillablepdfs' ) );
		}

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Return Nested Forms child form object.
	 *
	 * @since 3.0
	 */
	public static function ajax_get_nested_form() {

		if ( ! wp_verify_nonce( rgpost( 'nonce' ), 'fg_fillablepdfs_get_nested_form' ) ) {
			wp_send_json_error( esc_html__( 'Access denied.', 'forgravity_fillablepdfs' ) );
		}

		if ( ! fg_fillablepdfs()->current_user_can_any( fg_fillablepdfs()->get_capabilities( 'form_settings' ) ) ) {
			wp_send_json_error( esc_html__( 'Access denied.', 'forgravity_fillablepdfs' ) );
		}

		$form_id = rgpost( 'form_id' );

		if ( ! $form_id ) {
			wp_send_json_error( esc_html__( 'Selected Nested Form field does not have a Nested Form selected.', 'forgravity_fillablepdfs' ) );
		}

		$form = GFAPI::get_form( $form_id );

		if ( ! $form ) {
			wp_send_json_error( esc_html__( 'Nested Form could not be found.', 'forgravity_fillablepdfs' ) );
		}

		return wp_send_json_success( $form );

	}

	/**
	 * Return template object for drop down.
	 *
	 * @since 3.0
	 */
	public static function ajax_get_template() {

		if ( ! wp_verify_nonce( rgpost( 'nonce' ), 'fg_fillablepdfs_get_feed_template' ) ) {
			wp_send_json_error( esc_html__( 'Access denied.', 'forgravity_fillablepdfs' ) );
		}

		if ( ! fg_fillablepdfs()->current_user_can_any( fg_fillablepdfs()->get_capabilities( 'form_settings' ) ) ) {
			wp_send_json_error( esc_html__( 'Access denied.', 'forgravity_fillablepdfs' ) );
		}

		// Get the template ID.
		$template_id = rgpost( 'template_id' );

		// If no template is selected, return.
		if ( ! $template_id ) {
			wp_send_json_error( esc_html__( 'You must select a template.', 'forgravity_fillablepdfs' ) );
		}

		if ( ! fg_pdfs_api() ) {
			wp_send_json_error( esc_html__( 'Unable to connect to Fillable PDFs API.', 'forgravity_fillablepdfs' ) );
		}

		try {

			$template = fg_pdfs_api()->get_template( $template_id );

			wp_send_json_success( $template );

		} catch ( \Exception $e ) {

			wp_send_json_error( $e->getMessage() );

		}

	}

	/**
	 * Get the currently selected template.
	 *
	 * @since 3.0
	 *
	 * @return array|false
	 */
	private function get_template() {

		// Initialize template object.
		$template = false;

		// Get the selected template ID if ID was not provided.
		$template_id = $this->get_value();

		// If no template is selected, return.
		if ( ! $template_id ) {
			return $template;
		}

		try {

			// Get template meta.
			$template = fg_pdfs_api()->get_template( $template_id );

		} catch ( \Exception $e ) {

			// Log that template could not be retrieved.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Unable to retrieve template; ' . $e->getMessage() );

		}

		return $template;

	}

}

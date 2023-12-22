<?php
/**
 * Integration Authentication field.
 *
 * @since     4.0
 * @package   ForGravity\Fillable_PDFs
 */

namespace ForGravity\Fillable_PDFs\Integrations\Settings;

defined( 'ABSPATH' ) || die();

use ForGravity\Fillable_PDFs\Integrations\Base;
use Gravity_Forms\Gravity_Forms\Settings\Fields\Hidden;
use Gravity_Forms\Gravity_Forms\Settings\Settings;

/**
 * Integration Authentication field.
 *
 * @since     4.0
 * @package   ForGravity\Fillable_PDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2022, ForGravity
 */
class Auth extends Hidden {

	/**
	 * Instance of the Integration to be authenticated.
	 *
	 * @since 4.0
	 *
	 * @var Base
	 */
	protected $integration;

	/**
	 * Field type.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	public $type = 'fg_fillablepdfs_integration_auth';

	/**
	 * Initialize field.
	 *
	 * @since 4.0
	 *
	 * @param array    $props    Field properties.
	 * @param Settings $settings Settings instance.
	 */
	public function __construct( $props, $settings ) {

		parent::__construct( $props, $settings );

		add_filter( 'gaddon_no_output_field_properties', [ $this, 'filter_gaddon_no_output_field_properties' ], 10, 2 );

	}




	// # RENDER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Render field.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function markup() {

		// Render hidden input containing auth data.
		$html = parent::markup();

		// Display Integration logo, name.
		$html .= sprintf(
			'<img src="%1$s" height="36" alt="%2$s" class="fillablepdfs-integration-auth__logo" />
			<span class="fillablepdfs-integration-auth__name">%2$s</span>',
			esc_url( $this->integration->get_logo() ),
			esc_html( $this->integration->get_name() )
		);

		// Display description.
		$html .= sprintf(
			'<p class="fillablepdfs-integration-auth__description">%1$s</p>',
			$this->integration->has_access() && $this->integration->is_connected() ? $this->integration->get_connected_message() : esc_html( $this->integration->get_description() )
		);

		// Display action button.
		$html .= $this->render_action_button();

		return $html;

	}

	/**
	 * Renders the Connect or Disconnect action button.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	private function render_action_button() {

		$is_connected = $this->integration->is_connected();
		$button_text  = $is_connected ? esc_html__( 'Disconnect from %1$s', 'forgravity_fillablepdfs' ) : esc_html__( 'Connect to %1$s', 'forgravity_fillablepdfs' );

		if ( ! $this->integration->has_access() ) {

			return $this->integration->get_upgrade_button();

		} elseif ( $is_connected ) {

			return sprintf(
				'<button type="button" data-integration="%1$s" class="fillablepdfs-integration-auth__action fillablepdfs-integration-auth__action--disconnect">%2$s</button>',
				esc_attr( $this->integration->get_type() ),
				sprintf( $button_text, esc_html( $this->integration->get_name() ) )
			);

		} else {

			return sprintf(
				'<a href="%1$s" class="fillablepdfs-integration-auth__action fillablepdfs-integration-auth__action--connect" style="color: %3$s;">%2$s</a>',
				$this->integration->get_auth_url(),
				sprintf( $button_text, esc_html( $this->integration->get_name() ) ),
				esc_attr( $this->integration->get_color() )
			);

		}

	}

	/**
	 * Prevent Integration class from being included as a markup attribute.
	 *
	 * @since 4.0
	 *
	 * @param array                                             $properties Properties to exclude.
	 * @param \Gravity_Forms\Gravity_Forms\Settings\Fields\Base $field      Field object.
	 *
	 * @return array
	 */
	public function filter_gaddon_no_output_field_properties( $properties, $field ) {

		if ( ! $field || $field->type !== $this->type ) {
			return $properties;
		}

		$properties[] = 'integration';

		return $properties;

	}

}

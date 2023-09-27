<?php
/**
 * Integration Enable field.
 *
 * @since     4.0
 * @package   ForGravity\Fillable_PDFs
 */

namespace ForGravity\Fillable_PDFs\Integrations\Settings;

defined( 'ABSPATH' ) || die();

use ForGravity\Fillable_PDFs\Integrations\Base;
use GFCommon;
use Gravity_Forms\Gravity_Forms\Settings\Fields\Toggle;
use Gravity_Forms\Gravity_Forms\Settings\Settings;

/**
 * Integration Enable field.
 *
 * @since     4.0
 * @package   ForGravity\Fillable_PDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2022, ForGravity
 */
class Enable extends Toggle {

	/**
	 * Action being taken when toggling setting.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	protected $action;

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
	public $type = 'fg_fillablepdfs_integration_enable';

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
			'handle'    => 'fg_fillablepdfs_integrations',
			'src'       => fg_fillablepdfs()->get_asset_url( "dist/js/integrations{$min}.js" ),
			'version'   => $min ? fg_fillablepdfs()->get_version() : fg_fillablepdfs()->get_asset_filemtime( "js/integrations{$min}.js" ),
			'in_footer' => true,
		];

		return $scripts;

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

		// Display logo and label.
		$html = sprintf(
			'<img src="%1$s" alt="%2$s" height="24" /><span class="fillablepdfs-integration-enable__label gform-settings-label">%2$s</span>',
			esc_url( $this->integration->get_logo() ),
			esc_html( $this->action )
		);

		// Display connection status or upgrade button.
		if ( ! $this->integration->has_access() ) {

			$html .= $this->integration->get_upgrade_button();

		} elseif ( $this->integration->is_connected() ) {
			$html .= sprintf(
				'<span class="fillablepdfs-integration-enable__status fillablepdfs-integration-enable__status--%2$s">%1$s</span>',
				esc_html__( 'Connected', 'forgravity_fillablepdfs' ),
				'connected'
			);

			// Display toggle.
			$html .= sprintf( '<div class="gform-settings-field__toggle">%1$s</div>', parent::markup() );

		} else {
			$html .= sprintf(
				'<span class="fillablepdfs-integration-enable__status fillablepdfs-integration-enable__status--%2$s">%1$s</span>',
				esc_html__( 'Disconnected', 'forgravity_fillablepdfs' ),
				'disconnected'
			);

			// Display connect button.
			if ( GFCommon::current_user_can_any( fg_fillablepdfs()->get_capabilities( 'settings_page' ) ) ) {
				$html .= sprintf(
					'<a href="%2$s" class="fillablepdfs-integration-enable__authenticate" style="color: %3$s;">%1$s</a>',
					sprintf( esc_html__( 'Connect to %1$s', 'forgravity_fillablepdfs' ), $this->integration->get_name() ),
					esc_url( $this->integration->get_auth_url() ),
					esc_attr( $this->integration->get_color() )
				);
			}

		}

		return $html;

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

		return array_merge(
			$properties,
			[ 'integration', 'action' ]
		);

	}

}

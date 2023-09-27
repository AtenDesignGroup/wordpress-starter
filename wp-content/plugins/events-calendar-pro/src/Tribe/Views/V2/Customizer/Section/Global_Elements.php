<?php
/**
 * The Events Calendar Customizer Section Class
 * Global Elements
 *
 * @since 5.9.0
 */

namespace Tribe\Events\Pro\Views\V2\Customizer\Section;

/**
 * Global Elements
 *
 * @since 5.9.0
 */
class Global_Elements {

	/**
	 * Filters the Global Elements settings to add view selector settings.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed> $settings The existing array of settings.
	 *
	 * @return array $defaults The modified array of settings.
	 */
	public function filter_global_elements_content_settings ( $settings ) {
		$pro_settings = [
			'map_pin' => [
				'sanitize_callback'	   => 'esc_url_raw',
				'sanitize_js_callback' => 'esc_url_raw',
			],
		];

		return array_merge( $settings, $pro_settings );
	}

	/**
	 * Filters the Global Elements controls to add view selector controls.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed> $controls The existing array of controls.
	 *
	 * @return array $defaults The modified array of controls.
	 */
	public function filter_global_elements_content_controls ( $controls ) {
		$pro_controls = [
			'map_pin' => [
				'priority'    => 35, // Immediately after view_selector_background_color_choice.
				'type'        => 'image',
				'label'       => esc_html_x(
					'Map Pin',
					'Label for the map pin control',
					'tribe-events-calendar-pro'
				),
				'description' => esc_html_x(
					'Google recommends a marker no more than 80px tall.',
					'Notes on recommended image size for map pins.',
					'tribe-events-calendar-pro'
				),
				'active_callback' => function( $control ) {
					if ( tribe_is_using_basic_gmaps_api() ) {
						return false;
					}

					$enabled_views = tribe_get_option( 'tribeEnableViews', [] );

					return in_array( 'map', $enabled_views );
				},
			],
		];

		return array_merge( $controls, $pro_controls );
	}


	/**
	 * Filters the Global Elements CSS template to add ECP-specific styles.
	 *
	 * @since 5.9.0
	 *
	 * @param string                     $css_template The current css output.
	 * @param Tribe__Customizer__Section $section      The section instance we are dealing with.
	 *
	 * @return string $css_template The modified css output.
	 */
	public function filter_global_elements_css_template ( $css_template, $section ) {
		$new_styles    = [];


		if ( empty( $new_styles ) ) {
			return $css_template;
		}

		// This should, for now, all be inherited.
		$new_css = sprintf(
			':root {
				/* Customizer-added ECP Global Elements styles */
				%1$s
			}',
			implode( "\n", $new_styles )
		);

		return $css_template . $new_css;
	}
}

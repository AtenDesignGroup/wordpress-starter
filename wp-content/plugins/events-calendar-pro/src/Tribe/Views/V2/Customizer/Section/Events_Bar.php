<?php
/**
 * The Events Calendar Customizer Section Class
 * Events Bar
 *
 * @since 5.8.0
 */

namespace Tribe\Events\Pro\Views\V2\Customizer\Section;

/**
 * Events Bar
 *
 * @since 5.8.0
 */
class Events_Bar {

	/**
	 * Filters the Events Bar defaults to add default view selector settings.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed>        $defaults       The existing array of default values.
	 * @param Tribe__Customizer__Section $unused_section The section instance we are dealing with.
	 *
	 * @return array $defaults The modified array of default values.
	 */
	public function filter_events_bar_default_settings ( $defaults, $unused_section = null ) {
		$pro_defaults = [
			'view_selector_background_color_choice' => 'default',
			'view_selector_background_color'        => '#FFFFFF',
		];

		return array_merge( $defaults, $pro_defaults );
	}

	/**
	 * Filters the Events Bar settings to add view selector settings.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed>        $settings       The existing array of settings.
	 *
	 * @return array $defaults The modified array of settings.
	 */
	public function filter_events_bar_content_settings ( $settings ) {
		$pro_settings = [
			'view_selector_background_color_choice' => [
				'sanitize_callback'    => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
				'transport'            => 'postMessage',
			],
			'view_selector_background_color'        => [
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'transport'            => 'postMessage',
			],
		];

		return array_merge( $settings, $pro_settings );
	}

	/**
	 * Filters the Events Bar controls to add view selector controls.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed>        $controls       The existing array of controls.
	 *
	 * @return array $defaults The modified array of controls.
	 */
	public function filter_events_bar_content_controls ( $controls ) {
		$customizer = tribe( 'customizer' );
		$enabled_views = tribe_get_option( 'tribeEnableViews', [] );

		$pro_controls = [
			'view_selector_background_color_choice' => [
				'priority'    => 27, // These are chosen based on the priority in Tribe\Events\Views\V2\Customizer\Section\Events_Bar.
				'type'        => 'radio',
				'label'       => esc_html_x(
					'View Dropdown Background Color',
					'The View Selector background color setting label.',
					'tribe-events-calendar-pro'
				),
				'choices'     => [
					'default' => esc_html_x(
						'Use Event Bar Color',
						'Label for the default option.',
						'tribe-events-calendar-pro'
					),
					'custom'  => esc_html_x(
						'Custom',
						'Label for option to set a custom color.',
						'tribe-events-calendar-pro'
					),
				],
				'active_callback' => function( $control ) use ( $enabled_views ) {
					return 3 < count( $enabled_views );
				},
			],
			'view_selector_background_color'        => [
				'priority'    => 28, // Immediately after view_selector_background_color_choice.
				'type'        => 'color',
				'active_callback' => function( $control ) use ( $customizer, $enabled_views ) {
					$setting_name = $customizer->get_setting_name( 'view_selector_background_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return 'custom' === $value && 3 < count( $enabled_views );
				},
			],
		];

		return array_merge( $controls, $pro_controls );
	}

	/**
	 * Filters the Events Bar CSS template to add ECP-specific styles.
	 *
	 * @since 5.8.0
	 *
	 * @param string                     $css_template The current css output.
	 * @param Tribe__Customizer__Section $section      The section instance we are dealing with.
	 *
	 * @return string $css_template The modified css output.
	 */
	public function filter_events_bar_css_template ( $css_template, $section ) {
		$new_styles    = [];
		$enabled_views = tribe_get_option( 'tribeEnableViews', [] );

		// View Selector Drop-down background color.
		if ( 3 < count( $enabled_views ) ) {
			if ( $section->should_include_setting_css( 'view_selector_background_color_choice' ) ) {
				$bg_color = $section->get_option( 'view_selector_background_color' );

			} elseif ( $section->should_include_setting_css( 'events_bar_background_color_choice' ) ) {
				$bg_color = 'custom' === $section->get_option( 'events_bar_background_color_choice' )
					? $section->get_option( 'events_bar_background_color' )
					: tribe( 'customizer' )->get_option( [ 'global_elements', 'events_bar_background_color' ] );
			}

			if ( ! empty( $bg_color ) ) {
				$new_styles[] = "--tec-color-background-view-selector: {$bg_color};";
			}
		}

		if ( empty( $new_styles ) ) {
			return $css_template;
		}

		$new_css = sprintf(
			':root {
				/* Customizer-added ECP Events Bar styles */
				%1$s
			}',
			implode( "\n", $new_styles )
		);

		return $css_template . $new_css;
	}
}

<?php

namespace TEC\Events_Pro\Modifiers\Contracts;

use WP_Post;

/**
 * Class Visibility_Modifier_Abstract.
 *
 * @since   6.2.0
 *
 * @package TEC\Events_Pro\Modifiers\Contracts
 */
abstract class Visibility_Modifier_Abstract {
	/**
	 * Get the setting key.
	 *
	 * @since   6.2.0
	 * @return string The key for the setting in the options table.
	 */
	abstract public function get_setting_key(): string;

	/**
	 * Get the setting label.
	 *
	 * @since   6.2.0
	 *
	 * @return string The label for the setting in the settings page.
	 */
	abstract public function get_setting_label(): string;

	/**
	 * Get the setting slug, mostly used for the filter names.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	abstract public function get_slug(): string;

	/**
	 * Get the list of valid options for visibility.
	 *
	 * @since   6.2.0
	 * @return array The keys of the settings options where 'default' is true.
	 */
	public function get_valid_options(): array {
		return array_keys( $this->get_setting_options() );
	}

	/**
	 * Get the default settings.
	 *
	 * @since   6.2.0
	 * @return array The keys of the settings options where 'default' is true.
	 */
	public function get_defaults(): array {
		return array_keys( wp_list_filter( $this->get_setting_options(), [ 'default' => true ] ) );
	}

	/**
	 * Allow extending classes to define the setting options to be merged with the one used in the settings page.
	 *
	 * @since 6.2.0
	 *
	 *
	 * @return array
	 */
	abstract public function define_setting_options(): array;

	/**
	 * Get the setting options.
	 *
	 * @since   6.2.0
	 *
	 * @return array The setting options for visibility modifier.
	 */
	public function get_setting_options(): array {
		$default = [
			'label'   => null,
			'default' => false,
		];

		$options = array_merge( [], $this->define_setting_options() );
		$slug    = $this->get_slug();

		/**
		 * Filter the options for the visibility modifier setting.
		 *
		 * @since 6.2.0
		 *
		 * @param array $options The options for the visibility modifier setting.
		 */
		$options = apply_filters( "tec_events_pro_{$slug}_visibility_get_setting_options", $options );

		// Enforce the default structure.
		foreach ( $options as $key => $option ) {
			$options[ $key ] = array_merge( $default, $option );
		}

		return $options;
	}

	/**
	 * Get the setting definition.
	 *
	 * @since   6.2.0
	 *
	 * @return array The setting definition for visibility modifier.
	 */
	public function get_setting_definition(): array {
		$setting = [
			'type'            => 'checkbox_list',
			'label'           => $this->get_setting_label(),
			'default'         => $this->get_defaults(),
			'options'         => array_map( static function ( $option ) {
				return $option['label'];
			}, $this->get_setting_options() ),
			'validation_type' => 'options_multi',
			'can_be_empty'    => true,
		];

		$slug = $this->get_slug();

		/**
		 * Filter the definition for this modifier visibility setting.
		 *
		 * @since 6.2.0
		 *
		 * @param array $setting The definition for the visibility modifier setting.
		 */
		return apply_filters( "tec_events_pro_{$slug}_visibility_get_setting_definition", $setting );
	}

	/**
	 * Get the setting value, filtered by the valid options and if will use the default if the value is not present.
	 *
	 * @since   6.2.0
	 *
	 * @return array The values of the settings from the options table.
	 */
	public function get_setting_value(): array {
		$value = tribe_get_option( $this->get_setting_key(), $this->get_defaults() );
		if ( empty( $value ) ) {
			return [];
		}

		if ( ! is_array( $value ) ) {
			return [];
		}

		$value = array_values( $value );

		return array_intersect( $value, $this->get_valid_options() );
	}

	/**
	 * Check if the visibility modifier's linked data (i.e. phone number) is visible on a certain page.
	 *
	 * @since   6.2.0
	 *
	 * @param string                  $area The area for this visibility modifier.
	 * @param null|int|string|WP_Post $post The post to check for this visibility modifier.
	 *
	 * @return bool True if the linked data is visible on the given page, false otherwise.
	 */
	public function is_visible( string $area, $post = null ): bool {
		$is_visible = in_array( $area, $this->get_setting_value(), true );
		$slug       = $this->get_slug();

		/**
		 * Filter the value of the visibility modifier for all areas, it will also pass the post for context and area for granularity.
		 *
		 * @since 6.2.0
		 *
		 * @param bool                    $is_visible True if  this visibility modifier is visible in the given area, false otherwise.
		 * @param string                  $area       The area to check for this visibility modifier.
		 * @param null|int|string|WP_Post $post       The post to check for this visibility modifier.
		 */
		$is_visible = (bool) apply_filters( "tec_events_pro_{$slug}_visibility_is_visible", $is_visible, $area, $post );

		/**
		 * Filter the visibility of the visibility modifier specifically for a certain area.
		 *
		 * @since 6.2.0
		 *
		 * @param bool                    $is_visible True if this visibility modifier is visible in the given area, false otherwise.
		 * @param string                  $area       The area to check for this visibility modifier.
		 * @param null|int|string|WP_Post $post       The post to check for this visibility modifier.
		 */
		$is_visible = (bool) apply_filters( "tec_events_pro_{$slug}_visibility_is_visible:{$area}", $is_visible, $area, $post );

		/**
		 * Filter the visibility of the visibility modifier specifically for a post in a certain area.
		 *
		 * @since 6.2.0
		 *
		 * @param bool                    $is_visible True if this visibility modifier is visible in the given area, false otherwise.
		 * @param string                  $area       The area to check for this visibility modifier.
		 * @param null|int|string|WP_Post $post       The post to check for this visibility modifier.
		 */
		return (bool) apply_filters( "tec_events_pro_{$slug}_visibility_is_visible:{$area}:{$post}", $is_visible, $area, $post );
	}
}
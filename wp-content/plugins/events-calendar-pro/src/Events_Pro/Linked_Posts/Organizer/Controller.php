<?php

namespace TEC\Events_Pro\Linked_Posts\Organizer;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller.
 *
 * This class extends the Controller_Contract to provide specific functionalities
 * for the TEC\Events_Pro\Linked_Posts\Organizer package.
 *
 * @since   6.2.0
 * @package TEC\Events_Pro\Linked_Posts\Organizer
 */
class Controller extends Controller_Contract {
	/**
	 * Determines if this controller will register.
	 * This is present due to how UOPZ works, it will fail if method belongs to the parent/abstract class.
	 *
	 * @since 6.2.0
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return true;
	}

	/**
	 * Boot the Controller.
	 *
	 * This function is used to instantiate the singleton classes Phone_Visibility_Modifier,
	 * Email_Visibility_Modifier, Settings, etc.
	 *
	 * @since   6.2.0
	 */
	public function boot() {
		$this->container->register( Taxonomy\Category::class );
		$this->container->singleton( Phone_Visibility_Modifier::class );
		$this->container->singleton( Email_Visibility_Modifier::class );
		$this->container->singleton( Settings::class );
	}

	/**
	 * Register the filters and actions.
	 *
	 * @since   6.2.0
	 */
	public function do_register(): void {
		$this->boot();

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Unregister the filters and actions.
	 *
	 * @since   6.2.0
	 */
	public function unregister(): void {
		$this->remove_actions();
		$this->remove_filters();
	}

	/**
	 * Add actions related to this controller.
	 *
	 * @since   6.2.0
	 */
	public function add_actions(): void {
		add_action( 'tribe_events_single_event_meta_primary_section_start', [ $this, 'include_organizer_visibility_filters_classic' ] );
		add_action( 'tribe_events_single_event_meta_primary_section_end', [ $this, 'remove_organizer_visibility_filters_classic' ] );

		add_action( 'tribe_template_before_include:events/blocks/event-organizer', [ $this, 'include_organizer_visibility_filters_block' ] );
		add_action( 'tribe_template_after_include:events/blocks/event-organizer', [ $this, 'remove_organizer_visibility_filters_block' ] );
	}

	/**
	 * Remove actions related to this controller.
	 *
	 * @since   6.2.0
	 */
	public function remove_actions(): void {
		remove_action( 'tribe_events_single_event_meta_primary_section_start', [ $this, 'include_organizer_visibility_filters_classic' ] );
		remove_action( 'tribe_events_single_event_meta_primary_section_end', [ $this, 'remove_organizer_visibility_filters_classic' ] );

		remove_action( 'tribe_template_before_include:events/blocks/event-organizer', [ $this, 'include_organizer_visibility_filters_block' ] );
		remove_action( 'tribe_template_after_include:events/blocks/event-organizer', [ $this, 'remove_organizer_visibility_filters_block' ] );
	}

	/**
	 * Add filters related to this controller.
	 *
	 * @since   6.2.0
	 */
	public function add_filters(): void {
		add_filter( 'tec_events_display_settings_tab_fields', [ $this, 'filter_inject_settings' ], 14 );
	}

	/**
	 * Remove filters related to this controller.
	 *
	 * @since   6.2.0
	 */
	public function remove_filters(): void {
		remove_filter( 'tec_events_display_settings_tab_fields', [ $this, 'filter_inject_settings' ], 14 );
	}

	/**
	 * Include the organizer visibility filters for classic editor
	 *
	 * @since 6.2.0
	 */
	public function include_organizer_visibility_filters_classic(): void {
		add_filter( 'tribe_get_organizer_phone', [ $this, 'filter_modify_organizer_phone' ], 15 );
		add_filter( 'tribe_get_organizer_email', [ $this, 'filter_modify_organizer_email' ], 15 );
	}

	/**
	 * Remove the organizer visibility filters for classic editor
	 *
	 * @since 6.2.0
	 */
	public function remove_organizer_visibility_filters_classic(): void {
		remove_filter( 'tribe_get_organizer_phone', [ $this, 'filter_modify_organizer_phone' ], 15 );
		remove_filter( 'tribe_get_organizer_email', [ $this, 'filter_modify_organizer_email' ], 15 );
	}

	/**
	 * Include the organizer visibility filters for block editor
	 *
	 * @since 6.2.0
	 */
	public function include_organizer_visibility_filters_block(): void {
		add_filter( 'tribe_get_organizer_phone', [ $this, 'filter_modify_organizer_phone' ], 15 );
		add_filter( 'tribe_get_organizer_email', [ $this, 'filter_modify_organizer_email' ], 15 );
	}

	/**
	 * Remove the organizer visibility filters for block editor
	 *
	 * @since 6.2.0
	 */
	public function remove_organizer_visibility_filters_block(): void {
		remove_filter( 'tribe_get_organizer_phone', [ $this, 'filter_modify_organizer_phone' ], 15 );
		remove_filter( 'tribe_get_organizer_email', [ $this, 'filter_modify_organizer_email' ], 15 );
	}

	/**
	 * Modify the return of the organizer phone number value to hide it if the setting is enabled.
	 *
	 * @since 6.2.0
	 *
	 * @param \WP_Post|int|string|null $organizer
	 *
	 * @return \WP_Post|int|string|null
	 */
	public function filter_modify_organizer_phone( $organizer ) {
		return $this->container->make( Phone_Visibility_Modifier::class )->hide_for_event_single_classic_meta( $organizer );
	}

	/**
	 * Modify the return of the organizer email value to hide it if the setting is enabled.
	 *
	 * @since 6.2.0
	 *
	 * @param \WP_Post|int|string|null $organizer
	 *
	 * @return \WP_Post|int|string|null
	 */
	public function filter_modify_organizer_email( $organizer ) {
		return $this->container->make( Email_Visibility_Modifier::class )->hide_for_event_single_classic_meta( $organizer );
	}

	/**
	 * Inject settings into the provided fields.
	 *
	 * @since   6.2.0
	 *
	 * @param array $fields The fields into which the settings should be injected.
	 *
	 * @return array The fields with the injected settings.
	 *
	 */
	public function filter_inject_settings( $fields ): array {
		return $this->container->make( Settings::class )->inject_display_settings( $fields );
	}
}

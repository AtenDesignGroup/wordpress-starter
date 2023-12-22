<?php
/**
 * The Events Calendar Pro Integration with Zapier for Recurrence.
 *
 * @since   6.0.11
 * @package TEC\Events_Pro\Compatibility\Event_Automator\Zapier\Recurrence
 */

namespace TEC\Events_Pro\Compatibility\Event_Automator\Zapier\Recurrence;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events_Pro\Compatibility\Event_Automator\Zapier\Maps\Event;
use WP_Post;

/**
 * Class Provider
 *
 * @since   6.0.11
 *
 * @package TEC\Events_Pro\Compatibility\Event_Automator\Zapier\Recurrence
 */
class Provider extends Service_Provider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.0.11
	 */
	public function register() {
		// Register the SP on the container
		$this->container->singleton( Provider::class, $this );

		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->add_filters();
	}

	/**
	 * Returns whether the provider is enabled or not via filters.
	 *
	 * @since 6.0.11
	 *
	 * @return bool Whether the provider is enabled or not.
	 */
	public function is_enabled() {
		/**
		 * Filters whether the Event Automator Zapier integration provider for recurring fields is enabled or not.
		 *
		 * @since 6.0.11
		 *
		 * @param bool $enabled Whether the EVA Zapier integration provider for recurring fields is enabled or not.
		 */
		return (bool) apply_filters( "tec_pro_eva_zapier_integration_recurring_fields_enabled", true );
	}

	/**
	 * Adds the filters for Event Automator integration.
	 *
	 * @since 6.0.11
	 */
	protected function add_filters() {
		add_filter( 'tec_automator_map_event_details', [ $this, 'add_recurrence_fields' ], 10, 2 );
	}

	/**
	 * Filters the event details with recurrence details.
	 *
	 * @since 6.0.11
	 *
	 * @param array<string|mixed> An array of event details.
	 * @param WP_Post An instance of the event WP_Post object.
	 *
	 * @return array<string|mixed> An array of event details.
	 */
	public function add_recurrence_fields( array $next_event, WP_Post $event ) {
		return $this->container->make( Event::class )->add_recurrence_fields( $next_event, $event );
	}
}

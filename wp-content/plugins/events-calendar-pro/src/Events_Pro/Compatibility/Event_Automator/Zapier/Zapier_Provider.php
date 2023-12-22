<?php
/**
 * The Events Calendar Pro Integration with Zapier service provider.
 *
 * @since   6.0.11
 * @package TEC\Events_Pro\Compatibility\Event_Automator\Zapier
 */

namespace TEC\Events_Pro\Compatibility\Event_Automator\Zapier;

use TEC\Events_Pro\Compatibility\Event_Automator\Zapier\Maps\Event;
use TEC\Common\Contracts\Service_Provider as Provider_Contract;
use WP_Post;

/**
 * Class Zapier_Provider
 *
 * @since   6.0.11
 *
 * @package TEC\Events_Pro\Compatibility\Event_Automator\Zapier
 */
class Zapier_Provider extends Provider_Contract {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.0.11
	 */
	public function register() {
		// Register the SP on the container
		$this->container->singleton( Zapier_provider::class, $this );

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
		 * Filters whether the Event Automator Zapier integration provider for additional fields is enabled or not.
		 *
		 * @since 6.0.11
		 *
		 * @param bool $enabled Whether the EVA Zapier integration provider for additional fields is enabled or not.
		 */
		return (bool) apply_filters( "tec_pro_eva_zapier_integration_additional_fields_enabled", true );
	}

	/**
	 * Adds the filters for Event Automator integration.
	 *
	 * @since 6.0.11
	 */
	protected function add_filters() {
		add_filter( 'tec_automator_map_event_details', [ $this, 'add_additional_fields' ], 10, 2 );
	}

	/**
	 * Filters the event details with Pro additional fields.
	 *
	 * @since 6.0.11
	 *
	 * @param array<string|mixed> An array of event details.
	 * @param WP_Post An instance of the event WP_Post object.
	 *
	 * @return array<string|mixed> An array of event details.
	 */
	public function add_additional_fields( array $next_event, WP_Post $event ) {
		return $this->container->make( Event::class )->add_additional_fields( $next_event, $event );
	}
}

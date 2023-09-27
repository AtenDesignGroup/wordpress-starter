<?php
/**
 * Handles the Custom Tables integration, and compatibility, with
 * the Repositories.
 *
 * Here what implementations and filters are not relevant, are disconnected.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Repository
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Repository;

use TEC\Events\Custom_Tables\V1\Provider_Contract;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Repository
 */
class Provider extends Service_Provider implements Provider_Contract {
	/**
	 * Connects the methods to the Filters API.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->container->singleton( self::class, $this );
		$this->container->singleton( Events::class, Events::class );

		/*
		 * When the Pro repository needs the callback to create or update an Event recurrences,
		 * let's return one that will avoid the default logic.
		 */
		if ( ! has_filter( 'tribe_repository_event_recurrence_create_callback', [
			$this,
			'create_recurrence_callback'
		] ) ) {
			add_filter( 'tribe_repository_event_recurrence_create_callback', [
				$this,
				'create_recurrence_callback'
			], 20, 4 );
		}
		if ( ! has_filter( 'tribe_repository_event_recurrence_update_callback', [
			$this,
			'create_recurrence_callback'
		] ) ) {
			add_filter( 'tribe_repository_event_recurrence_update_callback', [
				$this,
				'create_recurrence_callback'
			], 20, 4 );
		}
	}

	/**
	 * Disconnects the methods handled by the Provider from the Filters API.
	 *
	 * @since 6.0.0
	 */
	public function unregister() {
		remove_filter(
			'tribe_repository_event_recurrence_create_callback',
			[ $this, 'create_recurrence_callback' ],
			20
		);
		remove_filter(
			'tribe_repository_event_recurrence_update_callback',
			[ $this, 'create_recurrence_callback' ],
			20
		);
	}

	/**
	 * Filters the callback that should be used to create or update the Occurrences
	 * of an Event.
	 *
	 * @since 6.0.0
	 *
	 * @param callable                  $callback           The callback that should be used to update the Event
	 *                                                      recurrence information.
	 * @param int                       $post_id            The post ID to update the recurrence information for.
	 * @param array<string,mixed>       $recurrence_payload The recurrence payload.
	 * @param ?array<string,mixed>|null $postarr            The rest of the Event creation data.
	 *
	 * @return callable The filtered Occurrence creation callback.
	 */
	public function create_recurrence_callback( callable $callback, int $post_id, array $recurrence_payload = [], ?array $postarr = null ): callable {
		return $this->container->make( Events::class )
			->create_recurrence_callback( $post_id, $recurrence_payload, $postarr );
	}
}

<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Event_Cleaner;

use TEC\Common\Contracts\Service_Provider;
use Tribe__Events__Pro__Recurrence__Meta;
use Tribe__Events__Pro__Recurrence__Old_Events_Cleaner;
use Tribe__Events__Pro__Recurrence__Scheduler;
use Tribe__Main;

/**
 * Class Provider
 *
 * This is the provider for our "Old" Event Cleaner system.
 *
 * @since   6.0.12
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Event_Cleaner
 */
class Provider extends Service_Provider {
	/**
	 * A flag property indicating whether the Service Provide did register or not.
	 *
	 * @since 6.0.12
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since 6.0.12
	 *
	 * @return bool Whether the provider registered.
	 */
	public function register(): bool {

		if ( $this->did_register ) {
			// Let's avoid double filtering by making sure we're registering at most once.
			return false;
		}

		$this->did_register = true;

		$this->remove_old_recurrence_cleaners();
		add_filter( 'tribe_events_delete_old_events_sql', [ $this, 'filter_tribe_events_delete_old_events_sql' ] );
		add_action( 'trashed_post', [ $this, 'handle_trashed_provisional_posts' ] );

		return true;
	}

	/**
	 * Deprecating/removing recurrenceMaxMonthsBefore and the scheduler. This is being handled by the CT1 Event Cleaner.
	 * system in CT1.
	 *
	 * @since 6.0.12
	 */
	public function remove_old_recurrence_cleaners() {
		// Hide from settings page.
		add_filter( 'tribe_settings_tab_fields', function ( $args, $id ) {
			if ( $id == 'general' ) {
				unset( $args['recurrenceMaxMonthsBefore'] );
			}

			return $args;
		}, 99, 2 );

		// Remove scheduled cleaner tasks.
		add_action( 'init', function () {
			$scheduler = Tribe__Events__Pro__Recurrence__Meta::$scheduler;
			remove_action( Tribe__Events__Pro__Recurrence__Scheduler::CRON_HOOK, [
				$scheduler,
				'clean_up_old_recurring_events'
			], 10 );
			remove_action( 'update_option_' . Tribe__Main::OPTIONNAME, [
				Tribe__Events__Pro__Recurrence__Old_Events_Cleaner::instance(),
				'clean_up_old_recurring_events',
			], 10 );
		}, 999 );
	}

	/**
	 * Handles all provisional posts that are trashed.
	 *
	 * @since 6.0.12
	 *
	 * @param numeric $post_id
	 */
	public function handle_trashed_provisional_posts( $post_id ) {
		if ( ! is_numeric( $post_id ) ) {
			return;
		}
		// Don't recurse
		remove_action( 'trashed_post', [ $this, 'handle_trashed_provisional_posts' ] );
		tribe( Event_Cleaner::class )->handle_trashed_provisional_post( (int) $post_id );
		add_action( 'trashed_post', [ $this, 'handle_trashed_provisional_posts' ] );
	}

	/**
	 * Hooks into our automated event cleaner service, and modifies the expired events query to handle occurrences for
	 * recurring events.
	 *
	 * @since 6.0.12
	 *
	 * @param string $sql The original query to retrieve expired events.
	 *
	 * @return string The modified CT1 query to retrieve expired events.
	 */
	public function filter_tribe_events_delete_old_events_sql( string $sql ): string {
		return tribe( Event_Cleaner::class )->filter_tribe_events_delete_old_events_sql( $sql );
	}
}

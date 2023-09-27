<?php
/**
 * Handles the creation of recurring events with Series.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Repository;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Repository\Events as TEC_Events;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;
use TEC\Events_Pro\Custom_Tables\V1\Events\Recurrence;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series;
use TEC\Events_Pro\Custom_Tables\V1\Series\Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Tables\Series_Relationships as Series_RelationshipsSchema;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Ical_Strings;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Type;
use TEC\Events_Pro\Custom_Tables\V1\Updates\WP_Function_Edit;
use RuntimeException;
use Tribe__Date_Utils as Dates;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series_Post_Type;
use WP_Post;

/**
 * The recurrence creator callback.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */
class Events extends TEC_Events {
	use With_Ical_Strings;

	/**
	 * A map of the update data per post ID.
	 *
	 * @since 6.0.0
	 *
	 * @var array<int,array<string,mixed>>
	 */
	protected $update_data = [];

	private $postarrs = [];

	/**
	 * Since the creation of Occurrences is handled in the upsert method,
	 * this one will no-op the Occurrences creation callback.
	 *
	 * @since 6.0.0
	 *
	 * @param int                      $post_id            The post ID to create the Occurrences for.
	 * @param array<string,mixed>      $recurrence_payload The recurrence payload.
	 * @param array<string,mixed>|null $postarr            The rest of the Event creation data.
	 *
	 * @return callable The `__return_true` function, as this has already been handled.
	 */
	public function create_recurrence_callback( int $post_id, array $recurrence_payload = [], ?array $postarr = [] ) {
		$this->postarrs[ $post_id ] = $postarr;

		return [ $this, 'upsert_occurrences' ];
	}

	/**
	 * Upserts the Event <> Series relationship information in the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * @param Event             $event         A reference to the Event model.
	 * @param array<string,int> $series        The payload of data to use to upsert the relationships.
	 * @param bool              $match_by_name Whether to match existing Series by name or not.
	 *
	 * @return Event A reference to the Event model.
	 *
	 * @throws RuntimeException On failure.
	 */
	private function update_series_relationships( Event $event, array $series, bool $match_by_name = false ): Event {
		$event_post = get_post( $event->post_id );

		try {
			if ( ! empty( $series ) ) {
				// If the Series will be created now, then assign them the Event post status.
				$series = $this->process_series_data( (array) $series, $match_by_name, [
					'post_status' => get_post_status( $event->post_id )
				] );

				// Associate new Event to the existing Series.
				tribe( Relationship::class )->with_event( $event, $series );
			} elseif ( ! empty( $event->rset ) ) {
				// If we're here, then the Event is recurring, it MUST have a Series relationship.
				$series_post_id = Series::vinsert(
					[ 'title' => $event_post->post_title ],
					[ 'post_status' => $event_post->post_status ]
				);
				tribe( Relationship::class )->with_event( $event, [ $series_post_id ] );
			}
		} catch ( \Exception $e ) {
			throw new RuntimeException( 'Failed to update Series relationship in repository.' );
		}

		return $event;
	}

	/**
	 * This method will run after the normal creation one did run and wil repeat
	 * the Event and Occurrence upsert operations if Recurrence rules came in.
	 *
	 * While a double-write, this method will not run if the Event is not recurring
	 * and we need to run the "normal" upsertion in the `update` method and this new
	 * upsertion once we have new information about recurrence.
	 *
	 * @param int                 $event_id The Event post ID.
	 * @param array<string,mixed> $payload  The recurrence payload.
	 *
	 * @return bool Whether the update was successful or not.
	 * @throws \ReflectionException
	 */
	public function upsert_occurrences( int $event_id, array $payload ): bool {
		/** @var Event $event */
		$event = Event::find( $event_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			return false;
		}

		$postarr = $this->postarrs[ $event_id ] ?? [];
		unset( $this->postarrs[ $event_id ] );
		$series = $postarr['series'] ?? [];
		$series_match_by_name = ! empty( $postarr['series_match_by_name'] );
		$is_recurring = ! empty( $postarr['recurrence'] );

		if ( $is_recurring && empty( $series ) ) {
			// A recurring event must have a Series associated to it.
			$series = [ get_post_field( 'post_title', $event_id ) ];
		}

		$this->update_series_relationships( $event, (array) $series, $series_match_by_name );

		if ( ! isset( $payload['recurrence'] ) ) {
			// Nothing else to do here.
			return true;
		}

		$recurrence = $payload['recurrence'];

		if ( $this->is_icalendar_string( $recurrence ) ) {
			$timezone = get_post_meta( $event_id, '_EventTimezone', true );
			$dtstart = Dates::immutable( get_post_meta( $event_id, '_EventStartDate', true ), $timezone );
			$dtend = Dates::immutable( get_post_meta( $event_id, '_EventEndDate', true ), $timezone );
			$recurrence = Recurrence::from_icalendar_string( $recurrence, $dtstart, $dtend )
				->to_event_recurrence();
		}

		// The Recurrence data might not have been saved yet: let's do  it now.
		update_post_meta( $event_id, '_EventRecurrence', $recurrence );

		if ( Event::upsert( [ 'post_id' ], Event::data_from_post( $event_id ) ) === false ) {
			return false;
		}

		// Refetch the Event to use the updated data.
		$event = Event::find( $event_id, 'post_id' );

		try {
			$event->occurrences()->save_occurrences();
		} catch ( \Exception $e ) {
			do_action( 'tribe_log', 'error', 'Failed to upsert recurring Event Occurrences.', [
				'source'  => __CLASS__,
				'post_id' => $event_id,
			] );

			return false;
		}

		return true;
	}

	/**
	 * Counts the occurrences for this series.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Series post ID.
	 *
	 * @return int
	 */
	public function get_occurrence_count_for_series( int $post_id ): int {
		global $wpdb;

		$events_table = EventsSchema::table_name( true );
		$series_events_table = Series_RelationshipsSchema::table_name( true );
		$occurrence_table = OccurrencesSchema::table_name( true );
		$query = "
			SELECT COUNT(*)
			FROM `{$series_events_table}`
			INNER JOIN `{$events_table}`
				ON `{$series_events_table}`.event_id = `{$events_table}`.event_id
			INNER JOIN `{$wpdb->posts}`
				ON `{$wpdb->posts}`.ID = `{$events_table}`.post_id
			INNER JOIN `{$occurrence_table}`
				ON `{$series_events_table}`.event_id = `{$occurrence_table}`.event_id
			WHERE `{$wpdb->posts}`.post_status != 'trash'
			 	AND `{$series_events_table}`.`series_post_id` = %s";

		return (int) $wpdb->get_var( $wpdb->prepare( $query, $post_id ) );
	}

	/**
	 * Process the set of specified Series to insert those that will need insertion.
	 *
	 * @since 6.0.1
	 * @since 6.0.11 Added $create_overrides param.
	 * @param array<int|string>   $series_data      The set of Series to process, either Series post IDs
	 *                                              or Series post titles, or a mix of both.
	 * @param bool                $match_by_name    Whether to try and match Series posts by name or not.
	 * @param array<string,mixed> $create_overrides An associative array of arguments to override the default ones.
	 *
	 * @return array<int> The post IDs of the inserted or updated Series.
	 */
	protected function process_series_data( array $series_data, bool $match_by_name = true, array $create_overrides = [] ): array {
		$vinsert = array_map( static function ( $series ) use ( $match_by_name ) {
			if ( is_numeric( $series ) ) {
				return [ 'id' => $series ];
			}

			$match = $match_by_name ?
				get_page_by_title( $series, OBJECT, Series_Post_Type::POSTTYPE )
				: null;

			if ( $match instanceof WP_Post ) {
				return [ 'id' => $match->ID ];
			}

			return [ 'title' => $series ];
		}, $series_data );

		return Series::vinsert_many( $vinsert, $create_overrides );
	}
}

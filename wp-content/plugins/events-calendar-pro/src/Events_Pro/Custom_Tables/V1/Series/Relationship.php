<?php
/**
 * Class in charge of storing the relationships of a series.
 *
 *
 * @since 6.0.0
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Series;


use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use WP_Post;

/**
 * Class Relationship
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Series
 */
class Relationship {

	const SERIES_TO_EVENTS_REQUEST_KEY = '_tec_relationship_series_to_events';
	const EVENTS_TO_SERIES_REQUEST_KEY = '_tec_relationship_event_to_series';

	/**
	 * Associate and event with a set of series ID.
	 *
	 * This is an over-writing operation! If one or more relationships already
	 * exist between Events and Series, then those will be overridden with these
	 * new ones.
	 *
	 * @since 6.0.0
	 *
	 * @param Event      $event        A reference to the Event object to save the relationship for.
	 * @param array<int> $series_id    A list of Series post IDs that should be related with
	 *                                 specified event.
	 */
	public function with_event( Event $event, array $series_id = [] ) {
		$series = array_filter( array_map( 'absint', $series_id ) );

		if ( empty( $series ) ) {
			/**
			 * Delete all the items that are part of the same event and post id, as there are no series to attach with,
			 * so make sure that the previous one (if any) reflects this change, empty state of series.
			 */
			Series_Relationship::where( 'event_id', $event->event_id )
			                   ->where( 'event_post_id', $event->post_id )
			                   ->delete();
		} else {
			// Insert all the items of the series.
			foreach ( $series as $series_post_id ) {
				$has_relationship = Series_Relationship::where( 'event_id', $event->event_id )
				                                       ->where( 'event_post_id', $event->post_id )
				                                       ->where( 'series_post_id', $series_post_id )
				                                       ->exists();

				if ( $has_relationship ) {
					// This relationship already exists nothing to do, move on with the next one.
					continue;
				}

				Series_Relationship::insert( [
					'event_id'       => $event->event_id,
					'event_post_id'  => $event->post_id,
					'series_post_id' => $series_post_id,
				] );

				do_action( 'tribe_log', 'debug', 'Series Relationship inserted.', [
					'method'         => 'with_event',
					'event_id'       => $event->event_id,
					'event_post_id'  => $event->post_id,
					'series_post_id' => $series_post_id,
				] );
			}

			// Delete all the items that are no longer associated with this event.
			Series_Relationship::where( 'event_id', $event->event_id )
			                   ->where( 'event_post_id', $event->post_id )
			                   ->where_not_in( 'series_post_id', $series )
			                   ->delete();
		}

		$event_id = $event->post_id;

		/**
		 * Fires after the relationship between an Event and a Series has been updated.
		 *
		 * @since 6.1.1
		 *
		 * @param int        $event_id The ID of the event that was updated.
		 * @param array<int> $series   The list of series that are now associated with the event.
		 */
		do_action( 'tec_events_pro_custom_tables_v1_event_relationship_updated', $event_id, $series_id );
	}

	/**
	 * Associate a list of events with a single series.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $series          The post object that represents the series.
	 * @param array   $event_posts_ids The list of posts IDs representing the events.
	 * @param bool    $replace         Whether to replace the existing Series <> Event relationships, or
	 *                                 to leave the existing ones in place and add the new ones.
	 */
	public function with_series( WP_Post $series, array $event_posts_ids = [], $replace = true ) {
		$event_posts_ids = array_filter( array_map( 'absint', $event_posts_ids ) );

		if ( empty( $event_posts_ids ) ) {
			if ( $replace ) {
				// No events should be associated with the series, remove any event that was added before (if any).
				Series_Relationship::where( 'series_post_id', $series->ID )->delete();
			}

			// Nothing to do here.
			return;
		}

		/**
		 * Delete any previous association if exists for those event_posts_ids, this is possible due the assumption
		 * that can be 1 to 1 relationship as one event_post_id can be associated with a single series.
		 */
		Series_Relationship::where_in( 'event_post_id', $event_posts_ids )->delete();

		if ( $replace ) {
			// Remove current values with the current $series->ID, in case a value is no longer associated with the Series.
			Series_Relationship::where( 'series_post_id', $series->ID )->delete();
		}

		$relationships = [];
		foreach ( $event_posts_ids as $event_post_id ) {
			$event = Event::where( 'post_id', $event_post_id )->first();

			if ( ! $event instanceof Event ) {
				continue;
			}

			$relationships[] = [
				'series_post_id' => $series->ID,
				'event_id'       => $event->event_id,
				'event_post_id'  => $event_post_id,
			];
		}

		Series_Relationship::insert( $relationships );

		/**
		 * Fires after the relationship between a Series and a list of Events has been updated.
		 *
		 * @since 6.1.1
		 *
		 * @param int        $series_id       The ID of the series that was updated.
		 * @param array<int> $event_posts_ids The list of event post IDs that are now associated with the series.
		 */
		do_action( 'tec_events_pro_custom_tables_v1_series_relationships_updated', $series->ID, $event_posts_ids );
	}

	/**
	 * Remove the relationship between a series post and the events associated with that series.
	 *
	 * @since 6.0.0
	 *
	 * @param int $series_id
	 *
	 * @return int The number of affected rows.
	 */
	public function delete( $series_id ) {
		return Series_Relationship::where( 'series_post_id', $series_id )->delete();
	}


	/**
	 * Detaches an Event from either all Series it's related to, or from a specific Series.
	 *
	 * @since 6.0.0
	 *
	 * @param Event        $event  A reference to the Event model instance to detach.
	 * @param WP_Post|null $series A reference to the Series post object instance, or `null` if
	 *                             the Event should be disconnected from all Series, and not just
	 *                             a specific one.
	 *
	 * @return int The number of disconnected Series to Event relationships.
	 */
	public function detach_event( Event $event, WP_Post $series = null ) {
		if ( null === $series ) {
			$detached = Series_Relationship::where( 'event_post_id', '=', $event->post_id )
			                               ->delete();
		} else {
			$detached = Series_Relationship::where( 'event_post_id', '=', $event->post_id )
			                               ->where( 'series_post_id', $series->ID )
			                               ->delete();
		}

		return $detached;
	}
}

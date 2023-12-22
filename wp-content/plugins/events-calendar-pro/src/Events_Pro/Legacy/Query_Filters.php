<?php
/**
 * Filters the Event queries to support legacy The Events Calendar PRO functionalities.
 *
 * @since   6.0.2.1
 *
 * @package TEC\Events_Pro\Legacy;
 */

namespace TEC\Events_Pro\Legacy;

use Tribe__Cache;
use WP_Query;
use Tribe__Events__Main as TEC;

/**
 * Class Query_Filters.
 *
 * @since   6.0.2.1
 *
 * @package TEC\Events_Pro\Legacy;
 */
class Query_Filters {
	/**
	 * A recurring event will have the base post's slug in the
	 * 'name' query var. We need to remove that and replace it
	 * with the correct post's ID
	 *
	 * @since 4.1
	 * @since 6.0.2.1 Move the method here from the `Tribe__Events__Pro__Main` class.
	 *
	 * @param WP_Query $query
	 *
	 * @return void
	 */
	public function set_post_id_for_recurring_event_query( $query ): void {
		$date = $query->get( 'eventDate' );
		$slug = $query->query['name'] ?? '';

		if ( empty( $date ) || empty( $slug ) ) {
			return; // we shouldn't be here
		}

		/**
		 * Filters the recurring event parent post ID.
		 *
		 * @param bool|int $parent_id The parent event post ID. Defaults to `false`.
		 *                            If anything but `false` is returned from this filter
		 *                            that value will be used as the recurring event parent
		 *                            post ID.
		 * @param WP_Query $query     The current query.
		 */
		$parent_id = apply_filters( 'tribe_events_pro_recurring_event_parent_id', false, $query );

		$cache = tribe_cache();
		if ( false === $parent_id ) {
			$post_id = $cache->get( 'single_event_' . $slug . '_' . $date, 'save_post' );
		} else {
			$post_id = $cache->get( 'single_event_' . $slug . '_' . $date . '_' . $parent_id, 'save_post' );
		}

		if ( ! empty( $post_id ) ) {
			unset( $query->query_vars['name'], $query->query_vars[ TEC::POSTTYPE ] );
			$query->set( 'p', $post_id );

			return;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;

		if ( false === $parent_id ) {
			$parent_sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type=%s";
			$parent_sql = $wpdb->prepare( $parent_sql, $slug, TEC::POSTTYPE );
			$parent_id = $wpdb->get_var( $parent_sql );
		}

		$parent_start = get_post_meta( $parent_id, '_EventStartDate', true );

		if ( empty( $parent_start ) ) {
			return; // how does this series not have a start date?
		}

		$parent_start_date = date( 'Y-m-d', strtotime( $parent_start ) );

		$sequence_number = $query->get( 'eventSequence' );
		if ( $parent_start_date === $date && empty( $sequence_number ) ) {
			$post_id = $parent_id;
		} else {
			/* Look for child posts taking place on the requested date (but not
			 * necessarily at the same time as the parent event); take sequence into
			 * account to distinguish between recurring event instances happening on the same
			 * day.
			 */
			$sequence_number = $query->get( 'eventSequence' );
			$should_use_sequence = ! empty( $sequence_number ) && is_numeric( $sequence_number ) && intval( $sequence_number ) > 1;
			$sequence_number = intval( $sequence_number );
			if ( ! $should_use_sequence ) {
				$child_sql = "
					SELECT     ID
					FROM       {$wpdb->posts} p
					INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_EventStartDate'
					WHERE      p.post_parent=%d
					  AND      p.post_type=%s
					  AND      LEFT( m.meta_value, 10 ) = %s
				";
				$child_sql = $wpdb->prepare( $child_sql, $parent_id, TEC::POSTTYPE, $date );
			} else {
				$child_sql = "
					SELECT     ID
					FROM       {$wpdb->posts} p
					INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_EventStartDate'
					INNER JOIN {$wpdb->postmeta} seqm ON seqm.post_id=p.ID AND seqm.meta_key='_EventSequence'
					WHERE      p.post_parent=%d
					  AND      p.post_type=%s
					  AND      LEFT( m.meta_value, 10 ) = %s
					  AND      LEFT( seqm.meta_value, 10 ) = %s
				";
				$child_sql = $wpdb->prepare( $child_sql, $parent_id, TEC::POSTTYPE, $date, $sequence_number );
			}
			$post_id = $wpdb->get_var( $child_sql );
		}

		if ( $post_id ) {
			unset( $query->query_vars['name'], $query->query_vars['tribe_events'] );
			$query->set( 'p', $post_id );
			$cache->set( 'single_event_' . $slug . '_' . $date, $post_id, Tribe__Cache::NO_EXPIRATION, 'save_post' );
		}
	}
}

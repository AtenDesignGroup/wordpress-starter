<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Event_Cleaner;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post_Cache;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Events;
use WP_Post;

/**
 * Class Provider
 *
 * This is the service for our "Old" Event Cleaner system.
 *
 * @since   6.0.12
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Event_Cleaner
 */
class Event_Cleaner {
	/**
	 * Handles all provisional posts that are trashed.
	 *
	 * @since 6.0.12
	 *
	 * @param int $post_id The post or provisional ID.
	 * @return WP_Post|false|null Post data on success, false or null on failure.
	 */
	public function handle_trashed_provisional_post( int $post_id ) {
		$provisional = tribe( Provisional_Post::class );
		if ( ! $provisional->is_provisional_post_id( $post_id ) ) {
			return;
		}
		$occurrence_id = $provisional->normalize_provisional_post_id( $post_id );
		$occurrence    = Occurrence::find( $occurrence_id );
		if ( ! $occurrence instanceof Occurrence ) {
			return;
		}

		// If single event, don't dissect.
		$is_single = Occurrence::where( 'post_id', $occurrence->post_id )
		                       ->count() === 1;
		if ( $is_single ) {
			$post_id_to_trash = $occurrence->post_id;
		} else {
			// We need to split and create a single post when this is trashed, to leverage WP's built in `post` management.
			$post_id_to_trash = tribe( Events::class )->detach_occurrence_from_event( $occurrence );
		}

		return wp_trash_post( $post_id_to_trash );
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
		global $wpdb;
		$occurrence_table = Occurrences::table_name();

		$base = (int) tribe( Provisional_Post_Cache::class )->get_base();

		// Order events by start date, so we can delete the "first" event chronologically.
		return "SELECT {$occurrence_table}.occurrence_id + $base AS provisional_id
				FROM {$wpdb->posts}
			    	INNER JOIN {$occurrence_table} ON {$wpdb->posts}.ID = {$occurrence_table}.post_id
				WHERE {$wpdb->posts}.post_type = %s
					AND {$occurrence_table}.end_date_utc <= DATE_SUB( CURDATE(), INTERVAL %d MONTH )
					AND {$wpdb->posts}.post_status != 'trash'
				ORDER BY {$occurrence_table}.start_date_utc ASC, {$occurrence_table}.end_date_utc ASC
				LIMIT %d";
	}
}

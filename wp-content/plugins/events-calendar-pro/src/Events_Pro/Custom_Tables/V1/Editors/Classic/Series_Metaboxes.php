<?php
/**
 * Class to register metaboxes for the classic editor.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Classic
 */


namespace TEC\Events_Pro\Custom_Tables\V1\Editors\Classic;

use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\Traits\With_Unbound_Queries;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series_Post_Type;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Series\Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Tables\Series_Relationships;
use WP_Post;

/**
 * Class Series_Metaboxes
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Classic
 */
class Series_Metaboxes {
	use With_Unbound_Queries;

	/**
	 * Remove our series from the list of linked post types that will automatically render a metabox in the default
	 * location. We want to control this metabox rendering separately.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $linked_posttypes The list of linked post types to filter.
	 *
	 * @return array<string,mixed> The filtered list of linked post types.
	 */
	public function remove_series_from_linked_metaboxes( array $linked_posttypes ): array {
		unset( $linked_posttypes[ Series_Post_Type::POSTTYPE ] );

		return $linked_posttypes;
	}

	/**
	 * Render the events list on the meta box.
	 *
	 * @since 6.0.0
	 */
	public function events_list(): void {
		$series_post_id = get_the_ID();
		$series_post    = $series_post_id ? get_post( $series_post_id ) : null;
		$events_list    = new Occurrences_List( $series_post );
		$events_list->prepare_items();
		$events_list->views();
		$events_list->display();
	}

	/**
	 * Render the series metabox into the admin of the events.
	 *
	 * @since 6.0.0
	 */
	public function relationship(): void {
		$field_name = Relationship::SERIES_TO_EVENTS_REQUEST_KEY;
		$events     = $this->get_events();

		$relationships  = [];
		$series_post_id = get_the_ID();
		if ( $series_post_id ) {
			$relationships = wp_list_pluck(
				Series_Relationship::where( 'series_post_id', $series_post_id )->get(),
				'event_post_id'
			);
		}

		$events = array_map(
			static function ( $event ) use ( $relationships ) {
				return [
					tribe_get_event( $event ),
					in_array( $event->ID, $relationships, true ),
				];
			},
			$events
		);

		include __DIR__ . '/partials/series-event-relationship.php';
	}

	/**
	 * Get the list of available events to render as part of the options for the series meta box.
	 *
	 * @since 6.0.0
	 *
	 * @return WP_Post[] A list of posts representing the ordered Events that match the criteria.
	 */
	private function get_events(): array {
		$events        = Events::table_name( true );
		$occurrence    = Occurrences::table_name( true );
		$relationships = Series_Relationships::table_name( true );

		$not_related_to_this_series = '';
		$series_post_id             = get_the_ID();
		global $wpdb;
		if ( $series_post_id ) {
			$not_related_to_this_series = $wpdb->prepare( 'WHERE r.series_post_id != %d', $series_post_id );
		}

		/*
		 * Why use sub-queries? This query has an unbound requisite. This means each
		 * "step", in PHP, in which we break the query should take care of NOT running
		 * an unbound query. This version reads a bit better and will let the database
		 * handle the unbound nature of the sub-queries internally, without allocating
		 * ,and destroying, memory for potentially very large sets of data in PHP and
		 * move them over the connection to the database.
		 */
		$query =
			"SELECT e.post_id as ID FROM $events e
			JOIN $wpdb->posts p ON e.post_id = p.ID

    		-- Only Events with upcoming Occurrences.
			JOIN (
			    SELECT {$occurrence}.post_id, MIN({$occurrence}.start_date)
			    FROM {$occurrence}
			    WHERE CAST({$occurrence}.start_date AS DATE) > CURDATE()
			    -- Groupped by post_id in order to prevent to have duplicates of the same recurring event.
			    GROUP BY {$occurrence}.post_id
			) occurrence ON occurrence.post_id = e.post_id

			WHERE (
			    	-- Events not already related to a Series.
				    e.post_id NOT IN (
				        SELECT DISTINCT(r.event_post_id)
				        FROM $relationships r
					)

				    -- Events related to a trashed Series.
					OR e.post_id IN (
					  SELECT DISTINCT(r.event_post_id)
					  FROM $relationships r
					  JOIN $wpdb->posts p ON p.ID = r.series_post_id AND p.post_status = 'trash'
					  $not_related_to_this_series
					)
			)
				-- Exclude trashed Events.
 				AND p.post_status != 'trash'
			ORDER BY e.start_date ASC, p.post_title ASC, p.ID ASC";

		$available = $this->get_all_results( $query, 'ID' );

		$events = array_map( 'get_post', array_filter( (array) $available ) );

		return $events;
	}

	/**
	 * Renders the content of the metabox that will control whether a Series title should
	 * show on the front-end or not.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $post A reference to the Series post object.
	 */
	public function show_series_title( WP_Post $post ): void {
		include __DIR__ . '/partials/show-series-title.php';
	}
}

<?php
/**
 * Events Calendar Pro Venue Template Tags
 *
 * Display functions for use in WordPress templates.
 */

use \TEC\Events_Pro\Linked_Posts\Venue\Taxonomy\Category as Venue_Category;

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}


if ( ! function_exists( 'tec_events_pro_get_venue_categories' ) ) {
	/**
	 * Get the categories for a venue.
	 *
	 * @param int|string|WP_Post $venue The venue ID.
	 *
	 * @return array
	 */
	function tec_events_pro_get_venue_categories( $venue ) {
		$venue = Tribe__Main::post_id_helper( $venue );

		$organizer_category_controller = tribe( Venue_Category::class );

		return wp_get_object_terms( $venue, $organizer_category_controller->get_wp_slug(), [ 'fields' => 'id=>name' ] );
	}
}

if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
	/**
	 * Checks whether a venue has more events in respect to the current page.
	 *
	 * @param int       $page     The current page number.
	 * @param int|array $venue_id The current venue post ID; will be read from the global `post` object
	 *                            if missing. If the value is an array only the first venue ID will be used.
	 *
	 * @return bool `false` if there are no next events, the post is not a venue or the page number is
	 *              not an int value, `true` if there are next events.
	 */
	function tribe_venue_has_next_events( $page, $venue_id = null ) {
		if ( ! is_numeric( $page ) && is_int( $page ) ) {
			return false;
		}

		$venue_id = is_array( $venue_id ) ? reset( $venue_id ) : $venue_id;
		$post_id = Tribe__Main::post_id_helper( $venue_id );

		if ( ! tribe_is_venue( $post_id ) ) {
			return false;
		}

		// Grab Post IDs of events currently on the page to ensure they don't erroneously show up on the "Next" page.
		$wp_query = tribe_get_global_query_object();
		$events_on_this_page = null === $wp_query ? array() : wp_list_pluck( $wp_query->posts, 'ID' );

		/**
		 * Allow for cusotmizing the number of events that show on each single-venue page.
		 *
		 * @since 4.4.16
		 *
		 * @param int $posts_per_page The number of events to show.
		 */
		$events_per_page = apply_filters( 'tribe_events_single_venue_posts_per_page', 100 );

		$display = tribe('context')->get('event_display');

		if ( 'past' === $display ) {
			if ( 1 === (int) $page ) {
				// "Next", on the first page of past events, means first page of upcoming events.
				$date_pivot_key = 'starts_after';
				$page = 1;
			} else {
				$date_pivot_key = 'starts_before';
				++ $page;
			}
		} else {
			$date_pivot_key = 'starts_after';
			++ $page;
		}

		$args = array(
			'venue'          => $venue_id,
			'paged'          => $page,
			'posts_per_page' => $events_per_page,
			'post__not_in'   => $events_on_this_page,
			$date_pivot_key  => 'now',
			'hidden'         => false,
		);

		$found = tribe_events()->by_args( $args )->found();

		return $found > 0;
	}
}

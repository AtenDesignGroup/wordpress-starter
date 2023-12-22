<?php
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Repository\Series_Repository;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use Tribe__Repository__Interface as Repository;

if ( ! function_exists( 'tribe_is_event_series' ) ) {
	/**
	 * Whether a post is a valid Event Series or not.
	 *
	 * @since 6.0.0
	 *
	 * @param int|WP_Post $post_id The post ID or object to check.
	 *
	 * @return bool Whether the post is an Event Series or not.
	 */
	function tribe_is_event_series( $post_id ): bool {
		return Series::POSTTYPE === get_post_type( $post_id );
	}
}

if ( ! function_exists( 'tec_event_series' ) ) {
	/**
	 * Return the first series associated with an event, if the event is private make sure to return `null` if the user
	 * is not logged in.
	 *
	 * @since 6.0.0
	 *
	 * @param null|int $event_post_id The ID of the post ID event we are looking for.
	 *
	 * @return WP_Post|null The post representing the series otherwise `null`
	 */
	function tec_event_series( $event_post_id ): ?WP_Post {
		// Simple prevention of mis-usage.
		if ( null === $event_post_id ) {
			return null;
		}

		$cache = tribe_cache();
		$cache_key = Series_Relationship::get_cache_key( $event_post_id );

		if ( isset( $cache[ $cache_key ] ) ) {
			$relationship = $cache[ Series_Relationship::get_cache_key( $event_post_id ) ];
		} else {
			$relationship = Series_Relationship::where( 'event_post_id', $event_post_id )->first();
			$cache[ $cache_key ] = $relationship;
		}

		if ( $relationship === null ) {
			return null;
		}

		if ( empty( $relationship->series_post_id ) ) {
			return null;
		}

		$series = get_post( $relationship->series_post_id );

		if ( ! $series instanceof WP_Post ) {
			return null;
		}

		// Show private series only if the user is logged in.
		if ( 'private' === $series->post_status && is_user_logged_in() ) {
			return $series;
		}

		// Status considered invalid, meaning those post_status indicate a non relationship for public visibility.
		$invalid_status = [
			'draft'   => true,
			'pending' => true,
			'future'  => true,
			'trash'   => true,
		];

		if ( isset( $invalid_status[ $series->post_status ] ) ) {
			return null;
		}

		return $series;
	}
}

if ( ! function_exists( 'tec_should_show_series_title' ) ) {
	/**
	 * Determines if we should show the series title in the series marker.
	 *
	 * @since 6.0.0
	 *
	 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
	 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
	 *
	 * @return boolean
	 */
	function tec_should_show_series_title( $series = null, $event = null ): bool {
		$show_title = false;
		if ( is_numeric( $series ) ) {
			$series = get_post( $series );
		}

		// If we have the series, check and see if the editor checkbox has been toggled.
		if ( ! empty( $series->ID ) ) {
			$show_title = (bool) get_post_meta( $series->ID, '_tec-series-show-title', true );
		}

		/**
		 * Allows filtering whether to show the series event title in the series marker.
		 *
		 * @6.0.0
		 *
		 * @param boolean          $show_title Should we (visually) hide the title.
		 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
		 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_show_series_title', $show_title, $series, $event );
	}
}

if ( ! function_exists( 'tec_get_series_marker_label_classes' ) ) {
	/**
	 * Generates a list of classes for the marker label.
	 *
	 * @since 6.0.0
	 *
	 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
	 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
	 *
	 * @return array<string> $classes A list of classes for the marker label.
	 */
	function tec_get_series_marker_label_classes( $series = null, $event = null  ): array {
		$classes = [ 'tec_series_marker__title' ];

		/**
		 * If this returns false, we  hide the series marker event title.
		 * (via the `tribe-common-a11y-visual-hide` class which leaves the title for screen readers for additional context.)
		 */
		if ( ! tec_should_show_series_title( $series, $event ) ) {
			$classes[] = 'tribe-common-a11y-visual-hide';
		}

		/**
		 * Allows filtering the series title classes.
		 *
		 * @6.0.0
		 *
		 * @param array<string>    $classes A list of classes to apply to the series title.
		 * @param Series|int|null  $series  The post object or ID of the series the event belongs to.
		 * @param WP_Post|int|null $event   The post object or ID of the event we're displaying.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_series_marker_label_classes', $classes, $series, $event );
	}
}

if ( ! function_exists( 'tec_series' ) ) {
	/**
	 * Builds and returns an instance of the Series repository.
	 *
	 * @since 6.0.1
	 *
	 * @return Tribe__Repository__Interface The series repository instance reference.
	 */
	function tec_series(): Repository {
		$repository_class = Series_Repository::class;

		/**
		 * Filters the class that should be used to build a new instance of the Series repository.
		 *
		 * @since 6.0.1
		 *
		 * @param string $repository_class The class that should be used to build a new instance of the Series repository.
		 */
		$repository_class = apply_filters( 'tec_events_pro_custom_series_repository_class', $repository_class );

		return tribe( $repository_class );
	}
}

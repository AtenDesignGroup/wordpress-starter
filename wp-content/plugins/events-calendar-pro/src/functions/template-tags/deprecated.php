<?php

/**
 * Output the upcoming events associated with a venue
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 */
function tribe_organizer_upcoming_events( $post_id = false ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}


/**
 * Return html attributes required for proper week view js functionality
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 *
 * @param int|object $event The event post, defaults to the global post.
 * @param string $format The format of the returned value. Can be either 'array' or 'string'
 */
function tribe_events_week_event_attributes( $event = null, $format = 'string' ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Check if there are any all day events this week.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 */
function tribe_events_week_has_all_day_events() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Return the range of days to display on week view.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 */
function tribe_events_week_get_days() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Echo the classes used on each week day header.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 */
function tribe_events_week_day_header_classes() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Return the text used in week day headers wrapped in a <span> tag and data attribute needed for mobile js.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 */
function tribe_events_week_day_header() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Setup css classes for daily columns in week view.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 */
function tribe_events_week_column_classes() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Retrieve the current date in Y-m-d format.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 *
 * @param boolean $echo Set to false to return the value rather than echo.
 */
function tribe_events_week_get_the_date( $echo = true ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Build the previous week link.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 *
 * @param string $text The text to be linked.
 *
 * @return string
 */
function tribe_events_week_previous_link( $text = '' ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Build the next week link
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 *
 * @param string $text the text to be linked
 *
 * @return string
 */
function tribe_events_week_next_link( $text = '' ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}


/**
 * Whether there are more calendar days available in the week loop.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 */
function tribe_events_week_have_days() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Increment the current day loop.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 */
function tribe_events_week_the_day() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Return current day in the week loop. The array will contain the following elements:
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 */
function tribe_events_week_get_current_day() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Return the hours to display on week view. Optionally return formatted, first, or last hour.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 *
 * @param string $return Can be 'raw', 'formatted', 'first-hour', or 'last-hour'.
 */
function tribe_events_week_get_hours( $return = null ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * For use on the Map View when the default Google Maps API key is provided. Attempts
 * to find a Venue from the events in the current loop; if found, will return a Google Map
 * basic embed URL with that Venue's address. Otherwise, returns false.
 *
 * @deprecated 6.0.0 No simple 1 to 1 replacement on the Updated Views.
 *
 * @since 4.4.33
 *
 * @return string|boolean The Google Map embed URL if found, or false.
 */
function tribe_events_get_map_view_basic_embed_url() {
	_deprecated_function( __METHOD__, '6.0.0' );
}


/**
 * Output the upcoming events associated with a venue.
 *
 * @param bool|int $post_id  The Venue post ID.
 * @param array    $wp_query An array of arguments to override the default ones.
 *
 * @deprecated 6.0.0
 */
function tribe_venue_upcoming_events( $post_id = false, array $args = array() ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Gets the URL to the previous events for a venue.
 *
 * @param int $page        The current page number
 * @param int $venue_id    The current venue post ID; will be read from the global `post` object
 *                         if missing.
 *
 * @deprecated 6.0.0
 */
function tribe_venue_previous_events_link( $page, $venue_id = null ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Gets the URL to the next events for a venue.
 *
 * @param int $page        The current page number
 * @param int $venue_id    The current venue post ID; will be read from the global `post` object
 *                         if missing.
 *
 * @deprecated 6.0.0
 */
function tribe_venue_next_events_link( $page, $venue_id = null ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Gets the URL to the next or previous events for a venue.
 *
 * @param int       $page      The current page number
 * @param int|array $venue_id  The current venue post ID; will be read from the global `post` object
 *                             if missing. If passed an array then the first venue ID from the array will be used.
 * @param string    $direction Either 'next' or 'prev'.
 *
 * @deprecated 6.0.0
 */
function tribe_venue_direction_link( $page, $venue_id, $direction = 'next' ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * Returns an events distance from location search term
 *
 * @deprecated 6.0.0
 */
function tribe_event_distance() {
	_deprecated_function( __METHOD__, '6.0.0' );
	global $post;
	if ( ! empty( $post->distance ) ) {
		return '<span class="tribe-events-distance">'. tribe_get_distance_with_unit( $post->distance ) .'</span>';
	}
}

/**
 * @deprecated 6.0.0
 */
function tribe_recurring_instances_toggle( $post_id = null ) {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * @deprecated 6.0.0
 */
function tribe_events_the_mini_calendar_header_attributes() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * @deprecated 6.0.0
 */
function tribe_events_the_mini_calendar_prev_link() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * @deprecated 6.0.0
 */
function tribe_events_the_mini_calendar_title() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * @deprecated 6.0.0
 */
function tribe_events_the_mini_calendar_next_link() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * @deprecated 6.0.0
 */
function tribe_events_the_mini_calendar_day_link() {
	_deprecated_function( __METHOD__, '6.0.0' );
}

/**
 * @deprecated 6.0.0
 */
function tribe_events_get_mini_calendar_args() {
	_deprecated_function( __METHOD__, '6.0.0' );
}
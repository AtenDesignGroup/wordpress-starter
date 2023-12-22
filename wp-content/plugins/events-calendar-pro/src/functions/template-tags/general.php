<?php

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events_Pro\Custom_Tables\V1\Series\Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use Tribe\Events\Pro\Views\V2\Views\Map_View;
use Tribe\Events\Pro\Views\V2\Views\Organizer_View;
use Tribe\Events\Pro\Views\V2\Views\Photo_View;
use Tribe\Events\Pro\Views\V2\Views\Venue_View;
use Tribe\Events\Pro\Views\V2\Views\Week_View;
use \Tribe\Events\Views\V2\Manager;
use Tribe__Events__Main as TEC;

if ( ! function_exists( 'tribe_get_mapview_link' ) ) {
	/**
	 * Events Calendar Pro template Tags
	 *
	 * Display functions for use in WordPress templates.
	 *
	 * @todo  move view specific functions to their own file
	 * @since 6.0.0 Refactor to use Views V2 URL building.
	 *
	 */
	function tribe_get_mapview_link( $term = null ) {
		$wp_query = tribe_get_global_query_object();

		if ( ! is_null( $wp_query ) && isset( $wp_query->query_vars[ TEC::TAXONOMY ] ) ) {
			$term = $wp_query->query_vars[ TEC::TAXONOMY ];
		}

		apply_filters_deprecated( 'tribe_get_map_view_permalink', [ null ], '6.0.0' );

		$args = [ 'view' => \Tribe\Events\Pro\Views\V2\Views\Map_View::get_view_slug() ];
		if ( $term ) {
			$args[ TEC::TAXONOMY ] = $term;
		}

		return tribe_events_get_url( $args );
	}
}

if ( ! function_exists( 'tribe_is_recurring_event' ) ) {
	/**
	 * Event Recurrence
	 *
	 * Test to see if event is recurring.
	 *
	 * @param int $post_id (optional)
	 *
	 * @return bool true if event is a recurring event.
	 */
	function tribe_is_recurring_event( $post_id = null ) {

		$post_id = TEC::postIdHelper( $post_id );

		if ( empty( $post_id ) ) {
			return false;
		}

		$post = get_post( $post_id );
		if ( empty( $post ) || $post->post_type != TEC::POSTTYPE ) {
			return false;
		}

		$recurring = false;

		if ( $post->post_parent > 0 ) {
			$recurring = true;
		} else {
			$recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

			if ( ! empty( $recurrence_meta['rules'] ) ) {
				// check if this is event has old-style meta (pre 3.12)
				if ( ! isset( $recurrence_meta['rules'] ) && isset( $recurrence_meta['type'] ) ) {
					$recurrence_meta['rules'] = array( $recurrence_meta );
				}
				foreach ( $recurrence_meta['rules'] as &$recurrence ) {
					if ( 'None' !== $recurrence['type'] ) {
						$recurring = true;
						break;
					}
				}

				// Support legacy Recurrence
			} elseif ( ! empty( $recurrence_meta['type'] ) ) {
				if ( 'None' !== $recurrence_meta['type'] ) {
					$recurring = true;
				}
			}
		}

		/**
		 * Allows for filtering whether the specified event is recurring or not.
		 *
		 * @param boolean $recurring Whether the specified event is recurring or not.
		 * @param int     $post_id   The post ID of the specified event.
		 */
		return apply_filters( 'tribe_is_recurring_event', $recurring, $post_id );
	}
}

if ( ! function_exists( 'tribe_get_recurrence_start_dates' ) ) {
	/**
	 * Get the start dates of all instances of the event,
	 * in ascending order
	 *
	 * @param int $post_id
	 *
	 * @return array Start times, as Y-m-d H:i:s
	 */
	function tribe_get_recurrence_start_dates( $post_id = null ) {
		$post_id = TEC::postIdHelper( $post_id );

		return Tribe__Events__Pro__Recurrence__Meta::get_start_dates( $post_id );
	}
}

/**
 * Recurrence Text
 *
 * Get the textual version of event recurrence
 * e.g Repeats daily for three days
 *
 * @param int $post_id (optional)
 *
 * @return string Summary of recurrence.
 */
if ( ! function_exists( 'tribe_get_recurrence_text' ) ) {
	function tribe_get_recurrence_text( $post_id = null ) {

		$post_id = TEC::postIdHelper( $post_id );

		/**
		 * Allow for filtering the textual version of event recurrence.
		 *
		 * @param string $recurrence_text The textual version of the specified event's recurrence details.
		 * @param int    $post_id         The post ID of the specified event.
		 */
		return apply_filters( 'tribe_get_recurrence_text', Tribe__Events__Pro__Recurrence__Meta::recurrenceToTextByPost( $post_id ), $post_id );
	}
}

if ( ! function_exists( 'tribe_all_occurences_link' ) ) {
	/**
	 * Recurring Event List Link
	 *
	 * Display link for all occurrences of an event (based on the currently queried event).
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Introduced caching based on Post ID or Parent Post ID.
	 * @deprecated 6.0.7 To correct misspelling.
	 *
	 * @param int     $post_id (optional) Which post we are looking for the All link.
	 * @param boolean $echo    (optional) Should be echoed along side returning the value.
	 *
	 * @return string  Link reference to all events in a recurrent event.
	 */
	function tribe_all_occurences_link( $post_id = null, $echo = true ) {
		_deprecated_function( __METHOD__, '6.0.7', 'tribe_all_occurrences_link' );

		return tribe_all_occurrences_link( $post_id, $echo );
	}
}


if ( ! function_exists( 'tribe_all_occurrences_link' ) ) {
	/**
	 * Recurring Event List Link
	 *
	 * Display link for all occurrences of an event (based on the currently queried event).
	 *
	 * @since 3.0.0
	 * @since 5.0.0 Introduced caching based on Post ID or Parent Post ID.
	 *
	 * @param int     $post_id (optional) Which post we are looking for the All link.
	 * @param boolean $echo    (optional) Should be echoed along side returning the value.
	 *
	 * @return string  Link reference to all events in a recurrent event.
	 */
	function tribe_all_occurrences_link( $post_id = null, $echo = true ) {
		$cache_key_links      = __FUNCTION__ . ':links';
		$cache_key_parent_ids = __FUNCTION__ . ':parent_ids';
		$cache_links          = tribe_get_var( $cache_key_links, [] );
		$cache_parent_ids     = tribe_get_var( $cache_key_parent_ids, [] );

		$post_id = TEC::postIdHelper( $post_id );

		if ( ! isset( $cache_parent_ids[ $post_id ] ) ) {
			$cache_parent_ids[ $post_id ] = wp_get_post_parent_id( $post_id );
			tribe_set_var( $cache_key_parent_ids, $cache_parent_ids );
		}

		// The ID to cache will be diff depending on Parent or child post of recurrent event.
		$cache_id = $cache_parent_ids[ $post_id ] ? $cache_parent_ids[ $post_id ] : $post_id;

		if ( ! isset( $cache_links[ $cache_id ] ) ) {
			$tribe_ecp                = TEC::instance();
			/**
			 * Filters the "all occurrences" link.
			 * @since 6.0.7
			 * @deprecated 6.0.7 To correct misspelling.
			 *
			 * @param string $link The link HTML string.
			 */
			$cache_links[ $cache_id ] = apply_filters_deprecated(
				'tribe_all_occurences_link',
				[ $tribe_ecp->getLink( 'all', $post_id ) ],
				'6.0.7',
				'tribe_all_occurrences_link',
				'Filter deprecated to correct misspelling.'
			);

			/**
			 * Filters the "all occurrences" link.
			 * @since 6.0.7
			 *
			 * @param string $link The link HTML string.
			 */
			$cache_links[ $cache_id ] = apply_filters( 'tribe_all_occurrences_link', $tribe_ecp->getLink( 'all', $post_id ) );
			tribe_set_var( $cache_key_links, $cache_links );
		}

		if ( $echo ) {
			echo $cache_links[ $cache_id ];
		}

		return $cache_links[ $cache_id ];
	}
}

if ( ! function_exists( 'tribe_get_custom_fields' ) ) {
	/**
	 * Event Custom Fields
	 *
	 * Get an array of custom fields
	 *
	 * @todo move logic to Tribe__Events__Pro__Custom_Meta class
	 *
	 * @param int $post_id (optional)
	 *
	 * @return array $data of custom fields
	 */
	function tribe_get_custom_fields( $post_id = null ) {
		$post_id      = TEC::postIdHelper( $post_id );
		$data         = array();
		$customFields = tribe_get_option( 'custom-fields', false );

		if ( is_array( $customFields ) ) {
			foreach ( $customFields as $field ) {
				$meta = str_replace( '|', ', ', get_post_meta( $post_id, $field['name'], true ) );
				if ( $field['type'] == 'url' && ! empty( $meta ) ) {
					$url_label = $meta;
					$parseUrl  = parse_url( $meta );

					if ( empty( $parseUrl['scheme'] ) ) {
						$meta = "http://$meta";
					}

					/**
					 * Filter the target attribute for the event website link
					 *
					 * @since 5.1.0
					 *
					 * @param string the target attribute string. Defaults to "_self".
					 */
					$target = apply_filters( 'tribe_get_event_website_link_target', '_self' );


					/**
					 * Filter any link label
					 *
					 * @since 3.0
					 *
					 * @param string the link label/text.
					 */
					$label = apply_filters( 'tribe_get_event_website_link_label', $url_label, $post_id );


					$meta = sprintf(
						'<a href="%s" target="%s">%s</a>',
						esc_url( $meta ),
						esc_attr( $target ),
						esc_html( $label )
					);
				}

				// Display $meta if not empty - making a special exception for (string) '0'
				// which in this context should be considered a valid, non-empty value
				if ( $meta || '0' === $meta ) {
					$data[ esc_html( $field['label'] ) ] = $meta; // $meta has been through wp_kses - links are allowed
				}
			}
		}

		return apply_filters( 'tribe_get_custom_fields', $data );
	}
}

if ( ! function_exists( 'tribe_get_distance_with_unit' ) ) {
	/**
	 * Returns the formatted and converted distance from the db (always in kms.) to the unit selected
	 * by the user in the 'defaults' tab of our settings.
	 *
	 * @param $distance_in_kms
	 *
	 * @return mixed
	 */
	function tribe_get_distance_with_unit( $distance_in_kms ) {

		$unit     = Tribe__Settings_Manager::get_option( 'geoloc_default_unit', 'miles' );
		$distance = round( tribe_convert_units( $distance_in_kms, 'kms', $unit ), 2 );

		return apply_filters( 'tribe_get_distance_with_unit', $distance . ' ' . $unit, $distance, $distance_in_kms, $unit );
	}
}

if ( ! function_exists( 'tribe_convert_units' ) ) {
	/**
	 *
	 * Converts units. Uses tribe_convert_$unit_to_$unit_ratio filter to get the ratio.
	 *
	 * @param $value
	 * @param $unit_from
	 * @param $unit_to
	 */
	function tribe_convert_units( $value, $unit_from, $unit_to ) {

		if ( $unit_from === $unit_to ) {
			return $value;
		}

		$filter = sprintf( 'tribe_convert_%s_to_%s_ratio', $unit_from, $unit_to );
		$ratio  = apply_filters( $filter, 0 );

		// if there's not filter for this conversion, let's return the original value
		if ( empty( $ratio ) ) {
			return $value;
		}

		return ( $value * $ratio );
	}
}

if ( ! function_exists( 'tribe_get_first_week_day' ) ) {
	/**
	 * Get the first day of the week from a provided date
	 *
	 * @param null|mixed $date given date or week # (week # assumes current year)
	 *
	 * @return string
	 * @todo move logic to Tribe__Date_Utils
	 */
	function tribe_get_first_week_day( $date = null ) {

		$wp_query = tribe_get_global_query_object();
		$offset   = 7 - (int) get_option( 'start_of_week', '0' );

		$date = is_null( $date ) && ! is_null( $wp_query ) ? $wp_query->get( 'start_date' ) : $date;

		$timezone = Tribe__Timezones::wp_timezone_string();
		$timezone = Tribe__Timezones::generate_timezone_string_from_utc_offset( $timezone );

		try {
			$date = new DateTime( $date, new DateTimeZone( $timezone ) );
		} catch ( exception $e ) {
			$date = new DateTime( current_time( 'Y-m-d' ), new DateTimeZone( $timezone ) );
		}

		// Clone to avoid altering the original date
		$r = clone $date;
		$r->modify( - ( ( $date->format( 'w' ) + $offset ) % 7 ) . 'days' );

		return apply_filters( 'tribe_get_first_week_day', $r->format( 'Y-m-d' ) );
	}
}

if ( ! function_exists( 'tribe_get_last_week_day' ) ) {
	/**
	 * Get the last day of the week from a provided date
	 *
	 * @param string|int $date_or_int A given date or week # (week # assumes current year)
	 * @param bool       $by_date     determines how to parse the date vs week provided
	 * @param int        $first_day   sets start of the week (offset) respectively, accepts 0-6
	 *
	 * @return DateTime
	 */
	function tribe_get_last_week_day( $date_or_int, $by_date = true ) {
		return apply_filters( 'tribe_get_last_week_day', date( 'Y-m-d', strtotime( tribe_get_first_week_day( $date_or_int, $by_date ) . ' +7 days' ) ) );
	}
}

if ( ! function_exists( 'tribe_is_week' ) ) {
	/**
	 * Conditional function for if we are in the week view.
	 *
	 * @since 6.0.0 Uses `tribe( 'context' )` for determining it's value.
	 * @since 6.0.7 Uses tec_is_view() for better view filtering.
	 *
	 * @return bool
	 */
	function tribe_is_week(): bool {
		$context  = tribe_context();
		$is_week = tec_is_view( Week_View::get_view_slug() );

		/**
		 * Allows filtering of the tribe_is_week boolean value.
		 *
		 * @since 4.4.26 Added inline documentation for this filter.
		 * @since 6.0.0  Remove the `$instance` param.
		 * @since 6.0.7 add `$context` param.
		 *
		 * @param boolean $is_week Whether you're on the main Week View or not
		 */
		return (bool) apply_filters( 'tribe_is_week', $is_week, $context );
	}
}

if ( ! function_exists( 'tribe_is_photo' ) ) {
	/**
	 * Conditional function for if we are in the photo view.
	 *
	 * @since 6.0.0 Uses `tribe( 'context' )` for determining it's value.
	 * @since 6.0.7 Uses tec_is_view() for better view filtering.
	 *
	 * @return bool
	 */
	function tribe_is_photo(): bool {
		$context  = tribe_context();
		$is_photo = tec_is_view( Photo_View::get_view_slug() );

		/**
		 * Allows filtering of the tribe_is_photo boolean value.
		 *
		 * @since 4.4.26 Added inline documentation for this filter.
		 * @since 6.0.0  Remove the `$instance` param.
		 * @since 6.0.7 add `$context` param.
		 *
		 * @param boolean $is_photo Whether you're on the main Photo View or not.
		 */
		return (bool) apply_filters( 'tribe_is_photo', $is_photo, $context );
	}
}

if ( ! function_exists( 'tribe_is_map' ) ) {
	/**
	 * Conditional function for if we are in the map view.
	 *
	 * @since 6.0.0 Uses `tribe( 'context' )` for determining it's value.
	 * @since 6.0.7 Uses tec_is_view() for better view filtering.
	 *
	 * @return bool
	 */
	function tribe_is_map(): bool {
		$context  = tribe_context();
		$is_map = tec_is_view( Map_View::get_view_slug() );

		/**
		 * Allows filtering of the tribe_is_map boolean value.
		 *
		 * @since 4.4.26 Added inline documentation for this filter.
		 * @since 6.0.0 Remove the `$instance` param.
		 * @since 6.0.7 add `$context` param.
		 *
		 * @param boolean $is_map Whether you're on the main Map View or not
		 */
		return (bool) apply_filters( 'tribe_is_map', $is_map, $context );
	}
}

if ( ! function_exists( 'tec_is_venue_view' ) ) {
	/**
	 * Conditional function for if we are on a venue view.
	 *
	 * @since 6.0.7
	 *
	 * @return bool
	 */
	function tec_is_venue_view(): bool {
		$context  = tribe_context();
		$is_venue_view = tec_is_view( Venue_View::get_view_slug() );

		/**
		 * Allows filtering of the tec_is_venue_view boolean value.
		 *
		 * @since 6.0.7
		 *
		 * @param boolean $is_venue_view Whether you're on a venue view or not
		 */
		return (bool) apply_filters( 'tec_is_venue_view', $is_venue_view, $context );
	}
}

if ( ! function_exists( 'tec_is_organizer_view' ) ) {
	/**
	 * Conditional function for if we are on an organizer view.
	 *
	 * @since 6.0.7
	 *
	 * @return bool
	 */
	function tec_is_organizer_view(): bool {
		$context  = tribe_context();
		$is_organizer_view = tec_is_view( Organizer_View::get_view_slug() );

		/**
		 * Allows filtering of the tec_is_organizer_view boolean value.
		 *
		 * @since 6.0.7
		 *
		 * @param boolean $is_organizer_view Whether you're on an organizer view or not
		 */
		return (bool) apply_filters( 'tec_is_organizer_view', $is_organizer_view, $context );
	}
}

if ( ! function_exists( 'tribe_get_last_week_permalink' ) ) {
	/**
	 * Get last week permalink by provided date (7 days offset)
	 *
	 * @todo move logic to week template class
	 * @uses tribe_get_week_permalink
	 *
	 * @param bool   $is_current
	 *
	 * @param string $week
	 *
	 * @return string $permalink
	 */
	function tribe_get_last_week_permalink( $week = null ) {
		$week = ! empty( $week ) ? $week : tribe_get_first_week_day();
		if ( PHP_INT_SIZE <= 4 ) {
			if ( date( 'Y-m-d', strtotime( $week ) ) < '1902-01-08' ) {
				throw new OverflowException( __( 'Date out of range.', 'tribe-events-calendar-pro' ) );
			}
		}

		$week = date( 'Y-m-d', strtotime( $week . ' -1 week' ) );

		return apply_filters( 'tribe_get_last_week_permalink', tribe_get_week_permalink( $week ) );
	}
}

if ( ! function_exists( 'tribe_get_next_week_permalink' ) ) {
	/**
	 * Get next week permalink by provided date (7 days offset)
	 *
	 * @todo move logic to week template class
	 * @uses tribe_get_week_permalink
	 *
	 * @param string $week
	 *
	 * @return string $permalink
	 */
	function tribe_get_next_week_permalink( $week = null ) {
		$week = ! empty( $week ) ? $week : tribe_get_first_week_day();
		if ( PHP_INT_SIZE <= 4 ) {
			if ( date( 'Y-m-d', strtotime( $week ) ) > '2037-12-24' ) {
				throw new OverflowException( __( 'Date out of range.', 'tribe-events-calendar-pro' ) );
			}
		}
		$week = date( 'Y-m-d', strtotime( $week . ' +1 week' ) );

		return apply_filters( 'tribe_get_next_week_permalink', tribe_get_week_permalink( $week ) );
	}
}

if ( ! function_exists( 'tribe_get_week_permalink' ) ) {
	/**
	 * Get the week view permalink.
	 *
	 * @since 6.0.0 Refactored to use Views V2 URL system.
	 *
	 * @param string        $week
	 * @param bool|int|null $term
	 *
	 * @return string $permalink
	 */
	function tribe_get_week_permalink( $week = null, $term = null ) {
		$week = is_null( $week ) ? false : date( 'Y-m-d', strtotime( $week ) );

		$args = [ 'view' => \Tribe\Events\Pro\Views\V2\Views\Week_View::get_view_slug() ];
		if ( $week ) {
			$args['date'] = $week;
		}

		if ( $term ) {
			$args[ TEC::TAXONOMY ] = $term;
		}

		apply_filters_deprecated( 'tribe_get_week_permalink', [ null, $week, $term ], '6.0.0' );

		return tribe_events_get_url( $args );
	}
}

if ( ! function_exists( 'tribe_get_photo_permalink' ) ) {
	/**
	 * Get the photo permalink.
	 *
	 * @param bool|int|null $term
	 *
	 * @return string $permalink
	 */
	function tribe_get_photo_permalink( $term = null ) {
		$args = [ 'view' => \Tribe\Events\Pro\Views\V2\Views\Photo_View::get_view_slug() ];

		if ( $term ) {
			$args[ TEC::TAXONOMY ] = $term;
		}

		apply_filters_deprecated( 'tribe_get_photo_view_permalink', [ null, $term ], '6.0.0' );

		return tribe_events_get_url( $args );
	}
}

if ( ! function_exists( 'tribe_single_related_events' ) ) {
	/**
	 * Echos the single events page related events boxes.
	 *
	 * @todo Deprecate any template include that is not using the Template Engine.
	 *
	 * @return void.
	 */
	function tribe_single_related_events() {
		tribe_get_template_part( 'pro/related-events' );
	}
}

if ( ! function_exists( 'tribe_get_related_posts' ) ) {
	/**
	 * Template tag to get related posts for the current post.
	 *
	 * @param int     $count number of related posts to return.
	 * @param int|obj $post  the post to get related posts to, defaults to current global $post
	 *
	 * @return array the related posts.
	 */
	function tribe_get_related_posts( $count = 3, $post = false ) {
		$post_id = TEC::postIdHelper( $post );

		$args  = [
			'posts_per_page' => $count,
			'start_date'     => 'now',
		];
		$posts = [];

		$orm_args = tribe_events()->filter_by_related_to( $post_id );

		if ( $orm_args ) {
			$args = array_merge( $args, $orm_args );

			if ( $args ) {
				$posts = tribe_get_events( $args );
			}
		}

		/**
		 * Filter the related posts for the current post.
		 *
		 * @since 3.2
		 *
		 * @param int   $post_id Current Post ID.
		 * @param array $args    Query arguments.
		 *
		 * @param array $posts   The related posts.
		 */
		return apply_filters( 'tribe_get_related_posts', $posts, $post_id, $args );
	}
}

if ( ! function_exists( 'tribe_events_recurrence_tooltip' ) ) {
	/**
	 * Shows the recurring event info in a tooltip, including details of the start/end date/time.
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	function tribe_events_recurrence_tooltip( $post_id = null ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$tooltip = '';

		if ( tribe_is_recurring_event( $post_id ) ) {
			$tooltip .= '<div class="recurringinfo">';
			$tooltip .= '<div class="event-is-recurring">';
			$tooltip .= '<span class="tribe-events-divider">|</span>';
			$tooltip .= sprintf( esc_html__( 'Recurring %s', 'tribe-events-calendar-pro' ), tribe_get_event_label_singular() );
			$tooltip .= sprintf( ' <a href="%s">%s</a>',
				esc_url( tribe_all_occurrences_link( $post_id, false ) ),
				esc_html__( '(See all)', 'tribe-events-calendar-pro' )
			);
			$tooltip .= '<div id="tribe-events-tooltip-' . $post_id . '" class="tribe-events-tooltip recurring-info-tooltip">';
			$tooltip .= '<div class="tribe-events-event-body">';
			$tooltip .= tribe_get_recurrence_text( $post_id );
			$tooltip .= '</div>';
			$tooltip .= '<span class="tribe-events-arrow"></span>';
			$tooltip .= '</div>';
			$tooltip .= '</div>';
			$tooltip .= '</div>';
		}

		/**
		 * Allows filtering the recurrence tooltip HTML for the specified event.
		 *
		 * @param string $tooltip The HTML of the recurrence tooltip for the specified event.
		 * @param int    $post_id The post ID of the event.
		 */
		return apply_filters( 'tribe_events_recurrence_tooltip', $tooltip, $post_id );
	}
}

if ( ! function_exists( 'tribe_events_pro_resource_url' ) ) {
	/**
	 * Returns or echoes a url to a file in the Events Calendar PRO plugin resources directory
	 *
	 * @param string $resource the filename of the resource
	 * @param bool $echo whether or not to echo the url
	 * @return string
	 **/
	function tribe_events_pro_resource_url( $resource, $echo = false ) {
		$extension      = pathinfo( $resource, PATHINFO_EXTENSION );
		$resources_path = 'src/resources/';
		switch ( $extension ) {
			case 'css':
				$resource_path = $resources_path . 'css/';
				break;
			case 'js':
				$resource_path = $resources_path . 'js/';
				break;
			case 'scss':
				$resource_path = $resources_path . 'scss/';
				break;
			default:
				$resource_path = $resources_path;
				break;
		}

		$path = $resource_path . $resource;
		$url  = apply_filters( 'tribe_events_pro_resource_url', trailingslashit( Tribe__Events__Pro__Main::instance()->pluginUrl ) . $path, $resource );
		if ( $echo ) {
			echo $url;
		}

		return $url;
	}
}

if ( ! function_exists( 'tribe_get_upcoming_recurring_event_id_from_url' ) ) {
	/**
	 * Returns the next upcoming event in a recurring series from the /all/ URL
	 * if one can be found, else returns null.
	 *
	 * @since 4.2
	 *
	 * @param string $url URL of the recurring series
	 *
	 * @return int|null
	 */
	function tribe_get_upcoming_recurring_event_id_from_url( $url ) {
		$path = @parse_url( $url );

		// Ensure we were able to parse the URL and have an actual path to look at (could be just a scheme, host and query etc)
		if ( empty( $path ) || ! isset( $path['path'] ) ) {
			return null;
		}

		$path = trim( $path['path'], '/' );
		$path = explode( '/', $path );

		// We expect $path to contain at least 3 elements (could be more, for subdirectory installations etc)
		if ( count( $path ) < 3 ) {
			return null;
		}

		// Grab the post name from the /all/ URL
		$post_name = $path[ count( $path ) - 2 ];

		// Fetch the parent (even if it is in the past, hence 'custom')
		$sequence_parent = tribe_get_events( array(
			'name'           => $post_name,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'eventDisplay'   => 'custom',
		) );

		if ( empty( $sequence_parent ) ) {
			return null;
		}

		$parent = current( $sequence_parent );

		// Ensure we are indeed looking at an actual recurring event
		if ( ! tribe_is_recurring_event( $parent->ID ) ) {
			return null;
		}

		// Is the parent itself the next upcoming instance? If so, we can return its ID
		if ( $parent->_EventEndDateUTC >= current_time( 'mysql' ) ) {
			return $parent->ID;
		}

		// Otherwise look for upcoming children of this event
		$upcoming_child = tribe_get_events( array(
			'post_parent'    => $parent->ID,
			'posts_per_page' => 1,
		) );

		if ( empty( $upcoming_child ) ) {
			return null;
		}

		return current( $upcoming_child )->ID;
	}
}

if ( ! function_exists( 'tribe_display_saved_organizer' ) ) {
	/**
	 * Displays the saved organizer
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_organizer() {
		$current_organizer_id = TEC::instance()->defaults()->organizer_id();
		$current_organizer = ( $current_organizer_id != 'none' && $current_organizer_id != 0 && $current_organizer_id ) ? tribe_get_organizer( $current_organizer_id ) : __( 'No default set', 'tribe-events-calendar-pro' );
		$current_organizer = esc_html( $current_organizer );
		echo '<p class="tribe-field-indent description">' . sprintf( __( 'The current default organizer is: %s', 'tribe-events-calendar-pro' ), '<strong>' . $current_organizer . '</strong>' ) . '</p>';
	}
}

if ( ! function_exists( 'tribe_display_saved_venue' ) ) {
	/**
	 * Displays the saved venue
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_venue() {
		$current_venue_id = TEC::instance()->defaults()->venue_id();
		$current_venue = ( $current_venue_id != 'none' && $current_venue_id != 0 && $current_venue_id ) ? tribe_get_venue( $current_venue_id ) : __( 'No default set', 'tribe-events-calendar-pro' );
		$current_venue = esc_html( $current_venue );
		echo '<p class="tribe-field-indent tribe-field-description description">' . sprintf( __( 'The current default venue is: %s', 'tribe-events-calendar-pro' ), '<strong>' . $current_venue . '</strong>' ) . '</p>';
	}
}

if ( ! function_exists( 'tribe_display_saved_address' ) ) {
	/**
	 * Displays the saved address
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_address() {
		$option = TEC::instance()->defaults()->address();
		$option = empty( $option ) ? __( 'No default set', 'tribe-events-calendar-pro' ) : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">' . sprintf( __( 'The current default address is: %s', 'tribe-events-calendar-pro' ), '<strong>' . $option . '</strong>' ) . '</p>';
	}
}

if ( ! function_exists( 'tribe_display_saved_city' ) ) {
	/**
	 * Displays the saved city
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_city() {
		$option = TEC::instance()->defaults()->city();
		$option = empty( $option ) ? __( 'No default set', 'tribe-events-calendar-pro' ) : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">' . sprintf( __( 'The current default city is: %s', 'tribe-events-calendar-pro' ), '<strong>' . $option . '</strong>' ) . '</p>';
	}
}

if ( ! function_exists( 'tribe_display_saved_state' ) ) {
	/**
	 * Displays the saved state
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_state() {
		$option = TEC::instance()->defaults()->state();
		$option = empty( $option ) ? __( 'No default set', 'tribe-events-calendar-pro' ) : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description tribe-saved-state">' . sprintf( __( 'The current default state/province is: %s', 'tribe-events-calendar-pro' ), '<strong>' . $option . '</strong>' ) . '</p>';
	}
}

if ( ! function_exists( 'tribe_display_saved_province' ) ) {
	/**
	 * Displays the saved province
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_province() {
		$option = TEC::instance()->defaults()->province();
		$option = empty( $option ) ? __( 'No default set', 'tribe-events-calendar-pro' ) : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description tribe-saved-province">' . sprintf( __( 'The current default state/province is: %s', 'tribe-events-calendar-pro' ), '<strong>' . $option . '</strong>' ) . '</p>';
	}
}

if ( ! function_exists( 'tribe_display_saved_zip' ) ) {
	/**
	 * Displays the saved zip
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_zip() {
		$option = TEC::instance()->defaults()->zip();
		$option = empty( $option ) ? __( 'No default set', 'tribe-events-calendar-pro' ) : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">' . sprintf( __( 'The current default postal code/zip code is: %s', 'tribe-events-calendar-pro' ), '<strong>' . $option . '</strong>' ) . '</p>';
	}
}

if ( ! function_exists( 'tribe_display_saved_country' ) ) {
	/**
	 * Displays the saved country
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_country() {
		$option = TEC::instance()->defaults()->country();
		$option = empty( $option[1] ) ? __( 'No default set', 'tribe-events-calendar-pro' ) : $option[1];
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">' . sprintf( __( 'The current default country is: %s', 'tribe-events-calendar-pro' ), '<strong>' . $option . '</strong>' ) . '</p>';
	}
}

if ( ! function_exists( 'tribe_display_saved_phone' ) ) {
	/**
	}
	 * Displays the saved phone
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_phone() {
		$option = TEC::instance()->defaults()->phone();
		$option = empty( $option ) ? __( 'No default set', 'tribe-events-calendar-pro' ) : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">' . sprintf( __( 'The current default phone is: %s', 'tribe-events-calendar-pro' ), '<strong>' . $option . '</strong>' ) . '</p>';
	}
}

if ( ! function_exists( 'tribe_get_mobile_default_view' ) ) {
	/**
	 * Allow users to fetch default view For Mobile
	 *
	 * @return string The default view slug.
	 * @category Events
	 *
	 */
	function tribe_get_mobile_default_view() {
		// If there isn't a default mobile set, it will get the default from the normal settings
		$default_view = tribe_get_option( 'mobile_default_view', 'default' );

		if ( 'default' === $default_view ) {
			$default_view = tribe( Manager::class )->get_default_view_slug();
		}

		/**
		 * Allow users to filter which is the default Mobile view globally
		 *
		 * @param string $default_view The default view set
		 */
		return apply_filters( 'tribe_events_mobile_default_view', $default_view );
	}
}

if ( ! function_exists( 'tribe_update_event_with_series' ) ) {
	/**
	 * Updates an Event to be attached to a given Series post.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $event  A reference to the Event post.
	 * @param WP_Post $series A reference to the Series post.
	 *
	 * @return bool Whether the Event was attached to the Series or not.
	 */
	function tribe_update_event_with_series( WP_Post $event, WP_Post $series ): bool {
		if ( TEC::POSTTYPE !== $event->post_type || Series::POSTTYPE !== $series->post_type ) {
			return false;
		}

		if ( Event::upsert( [ 'post_id' ], Event::data_from_post( $event->ID ) ) === false ) {
			return false;
		}

		$event_model = Event::find( $event->ID, 'post_id' );

		if ( ! $event_model instanceof Event ) {
			return false;
		}

		tribe( Relationship::class )->with_event( $event_model, [ $series->ID ] );

		return true;
	}
}

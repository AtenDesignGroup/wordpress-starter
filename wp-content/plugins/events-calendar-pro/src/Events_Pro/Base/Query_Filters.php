<?php
/**
 * Filters Event queries to support The Events Calendar PRO functionalities.
 *
 * @since   6.0.2.1
 *
 * @package TEC\Events_Pro\Base;
 */

namespace TEC\Events_Pro\Base;

use WP_Query;

/**
 * Class Query_Filters.
 *
 * @since   6.0.2.1
 *
 * @package TEC\Events_Pro\Base;
 */
class Query_Filters {
	/**
	 * Set PRO query flags.
	 *
	 * @since 6.0.0 Uses the values from Views V2 to determine old V1 variables that should still be around.
	 * @since 6.0.2.1  Moved here from the `Tribe__Events__Pro__Main` class.
	 *
	 * @param WP_Query $query The current query object.
	 *
	 * @return WP_Query The modified query object.
	 **/
	public function parse_query( $query ) {
		if ( is_admin() ) {
			return $query;
		}

		// If this is set then the class will bail out of any filtering.
		if ( $query->get( 'tribe_suppress_query_filters', false ) ) {
			return $query;
		}

		$context = tribe_context();

		// These are only required for Main Query stuff.
		if ( ! $context->is( 'is_main_query' ) ) {
			return $query;
		}

		if ( ! $context->is( 'tec_post_type' ) )  {
			return $query;
		}

		$query->tribe_is_event_pro_query = true;

		$query->tribe_is_week = 'week' === $context->get( 'event_display' );
		$query->tribe_is_photo = 'photo' === $context->get( 'event_display' );
		$query->tribe_is_map = 'map' === $context->get( 'event_display' );
		$query->tribe_is_recurrence_list = (bool) $query->get( 'tribe_recurrence_list' );

		return $query;
	}
}

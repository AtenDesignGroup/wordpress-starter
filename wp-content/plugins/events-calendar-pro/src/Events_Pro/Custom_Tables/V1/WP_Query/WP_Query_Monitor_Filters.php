<?php
/**
 * Filters for the WP_Query_Monitor.
 *
 * @since 6.0.4
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\WP_Query
 */

namespace TEC\Events_Pro\Custom_Tables\V1\WP_Query;


use TEC\Events\Custom_Tables\V1\WP_Query\Modifiers\Events_Only_Modifier;
use TEC\Events\Custom_Tables\V1\WP_Query\Modifiers\WP_Query_Modifier;
use TEC\Events\Custom_Tables\V1\WP_Query\Monitors\Custom_Tables_Query_Monitor;
use TEC\Events\Custom_Tables\V1\WP_Query\Monitors\Query_Monitor;
use TEC\Events\Custom_Tables\V1\WP_Query\Monitors\WP_Query_Monitor;
use TEC\Events_Pro\Custom_Tables\V1\WP_Query\Modifiers\Events_Not_In_Series_Modifier;
use TEC\Events_Pro\Custom_Tables\V1\WP_Query\Modifiers\Events_Series_Relationship_Modifier;
use TEC\Events_Pro\Custom_Tables\V1\WP_Query\Modifiers\Occurrences_Series_Relationship_Modifier;
use WP_Query;

/**
 * Class WP_Query_Monitor_Filters
 *
 * @since 6.0.4
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\WP_Query
 */
class WP_Query_Monitor_Filters {
	/**
	 * @param array<WP_Query_Modifier> $implementations The query modifier implementations to be filtered.
	 * @param Query_Monitor            $query_monitor   An instance of a Query Monitor class.
	 *
	 * @return array<WP_Query_Modifier> The filtered query modifier implementations.
	 */
	public function filter_query_modifier_implementations( array $implementations, $query_monitor ): array {
		if ( $query_monitor instanceof WP_Query_Monitor ) {
			if ( ! in_array( Events_Series_Relationship_Modifier::class, $implementations ) ) {
				$implementations[] = Events_Series_Relationship_Modifier::class;
			}
			if ( ! in_array( Events_Not_In_Series_Modifier::class, $implementations ) ) {
				$implementations[] = Events_Not_In_Series_Modifier::class;
			}
		}

		if ( $query_monitor instanceof Custom_Tables_Query_Monitor ) {
			if ( ! in_array( Occurrences_Series_Relationship_Modifier::class, $implementations ) ) {
				$implementations[] = Occurrences_Series_Relationship_Modifier::class;
			}
		}

		return $implementations;
	}

	/**
	 * Flag whether a particular query modifier should modify the query or not.
	 *
	 * @since 6.0.4
	 *
	 * @param bool              $should_filter Whether this modifier will apply changes to this query.
	 * @param WP_Query          $wp_query      The query being modified.
	 * @param WP_Query_Modifier $modifier      The modifier that will apply.
	 *
	 * @return bool
	 */
	public function filter_should_modify_query( bool $should_filter, $wp_query, WP_Query_Modifier $modifier ) {
		if ( ! $modifier instanceof Events_Only_Modifier ) {
			return $should_filter;
		}

		return ! tribe( Events_Not_In_Series_Modifier::class )->applies_to( $wp_query );
	}
}

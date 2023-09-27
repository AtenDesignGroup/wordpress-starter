<?php
/**
 * An extension of the Query Filters class used by the Repository to
 * redirect some of its custom fields based queries to the plugin custom tables.
 *
 * @since   6.0.5
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\WP_Query\Repository
 */

namespace TEC\Events_Pro\Custom_Tables\V1\WP_Query\Repository;

use TEC\Events_Pro\Custom_Tables\V1\Tables\Series_Relationships;
use Tribe__Repository__Query_Filters;
use WP_Post;


/**
 * Class Custom_Tables_Query_Filters
 *
 * @since   6.0.5
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\WP_Query\Repository
 */
class Custom_Tables_Query_Filters {

	/**
	 * Applies where and join clauses to the series filter.
	 *
	 * @since 6.0.5
	 *
	 * @param bool                             $filter_override Flag whether to continue using filter parsing.
	 * @param Tribe__Repository__Query_Filters $filter          This instance of the filter object.
	 * @param bool|numeric|WP_Post             $in_series       The series param.
	 *
	 * @return bool True, we always take over the filter.
	 */
	public function apply_series_filters( $filter_override, $filter, $in_series ) {
		$filter->join( $this->join( $in_series ) );
		$filter->where( $this->where( $in_series ) );

		return true;
	}

	/**
	 * Applies appropriate JOIN clause for the series filter.
	 *
	 * @since 6.0.5
	 *
	 * @param mixed $in_series The series param to filter by.
	 *
	 * @return string The JOIN clause.
	 */
	public function join( $in_series ) {
		global $wpdb;
		$series_relationships = Series_Relationships::table_name( true );

		// LEFT JOIN because the where clause will potentially filter for NULL on the join.
		return "LEFT JOIN {$series_relationships} ON {$series_relationships}.event_post_id = {$wpdb->posts}.ID";
	}

	/**
	 * Applies appropriate WHERE clause for the series filter.
	 *
	 * @since 6.0.5
	 *
	 * @param mixed $in_series The series param to filter by.
	 *
	 * @return string The WHERE clause.
	 */
	public function where( $in_series ) {
		global $wpdb;
		$is_id_search         = ! is_bool( $in_series );
		$series_relationships = Series_Relationships::table_name( true );

		if ( $is_id_search ) {
			// Convert to arrays to support an array of IDs as well as single IDs.
			$ids = $in_series instanceof WP_Post ? [ $in_series->ID ] : (array) $in_series;
			// Cast the elements.
			$ids = array_map( 'absint', $ids );

			// Let's filter by the id. We should have joined on the Series Relationship table.
			return "{$series_relationships}.series_post_id IN(" . implode( ',', $ids ) . ")";
		}

		if ( $in_series ) {
			// Grab any in series.
			return (string) $wpdb->prepare(
				"{$series_relationships}.series_post_id IS NOT NULL"
			);
		}

		// Grab none in series.
		return (string) $wpdb->prepare(
			"{$series_relationships}.series_post_id IS NULL"
		);
	}

}

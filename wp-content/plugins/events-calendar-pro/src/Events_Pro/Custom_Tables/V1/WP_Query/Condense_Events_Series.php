<?php
/**
 * An extension of the base WordPress WP_Query to redirect queries to the plugin custom tables.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */

namespace TEC\Events_Pro\Custom_Tables\V1\WP_Query;

use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use TEC\Events_Pro\Custom_Tables\V1\Tables\Series_Relationships;
use WP_Meta_Query;
use WP_Query;

/**
 * Class Condense_Events_Series
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\WP_Query
 */
class Condense_Events_Series {
	/**
	 * Remove query parameters when the ORM is processing the request for `hide_subsequent_recurrences` in order
	 * to remove non required meta queries and prevent having unexpected results on the end query. The original meta
	 * query is stored in a key for future reference.
	 *
	 * The filter is triggered by `tribe_repository_events_query_args`.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string, mixed> $query_args The query args for the current WP_Query request, usually originated by the ORM.
	 *
	 * @return array<string, mixed> The set of args for the ORM.
	 */
	public function query_args( array $query_args = [] ) {
		if ( empty( $query_args['hide_subsequent_recurrences'] ) ) {
			return $query_args;
		}

		if ( isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) ) {
			// Store the meta query key in a variable to save the original values.
			$query_args['__meta_query'] = $query_args['meta_query'];

			// Always remove key `_eventhidefromupcoming_not_exists` so the ORM does not tries to write a query for it.
			unset( $query_args['meta_query']['_eventhidefromupcoming_not_exists'] );

			// Remove the pieces that are attached to the `hide_subsequent_recurrences` behavior if is not on a series.
			if ( empty( $query_args['related_series'] ) ) {
				unset(
					$query_args['meta_query']['ends-after'],
					$query_args['meta_query']['ends-before']
				);
			}

			// The filter at this point is no longer required remove it.
			remove_filter(
				'tribe_repository_events_query_args',
				[ tribe( __CLASS__ ), 'query_args' ]
			);
		}

		return $query_args;
	}

	/**
	 * Action to hook into the request after the ORM has fired.
	 *
	 * @since 6.0.0
	 *
	 * @param Custom_Tables_Query $query The query object requesting the posts.
	 */
	public function pre_get_posts( Custom_Tables_Query $query ) {
		if ( $query->get( 'hide_subsequent_recurrences' ) !== true ) {
			return;
		}

		// If is inside the single series view ignore it and don't write any custom SQL.
		if ( $query->get( 'related_series' ) ) {
			return;
		}

		add_filter( 'posts_join', [ $this, 'hide_subsequent_recurrences' ], 200, 2 );
	}

	/**
	 * Filters the Query JOIN clause to JOIN on the Occurrences table if the Custom
	 * Tables Meta Query did not do that already.
	 *
	 * @since 6.0.0
	 * @since 6.1.2 Moved to a new hook, changed from `WHERE` to `JOIN`, and optimized query.
	 *
	 * @param string   $join The input JOIN clause, as parsed and built by the WordPress query.
	 * @param WP_Query $query A reference to the WP Query object that is currently filtering its JOIN query.
	 *
	 * @return string The filtered `JOIN` query, if required.
	 */
	public function hide_subsequent_recurrences( $join, $query ) {
		global $wpdb;
		if ( ! $query instanceof WP_Query ) {
			return $join;
		}

		// Avoid recursing or running again until needed.
		remove_filter( 'posts_join', [ $this, 'hide_subsequent_recurrences' ], 200 );

		if ( empty( $join ) ) {
			return $join; // Something went wrong - should have CT1 join by now.
		}

		$occurrences_table         = Occurrences::table_name( true );
		$series_relationship_table = Series_Relationships::table_name( true );
		$posts_table               = $wpdb->posts;

		// Build our various query clauses / params.
		$filter             = $this->get_filter( $query );
		$posts_statuses_in  = $this->get_statuses_in( $query );
		$posts_not_in       = $this->get_posts_not_in( $query );
		$posts_in           = $this->get_posts_in( $query );
		$column             = $this->get_column( $filter );
		$operator           = $this->get_operator( $filter );
		$value              = $this->get_value( $filter );
		$date_cast_type     = $this->get_date_cast_type( $value );
		$aggregate_function = $this->get_aggregate_function( $operator );

		$posts_where = "1=1";
		if ( $posts_statuses_in ) {
			$posts_where .= " AND $posts_table.post_status IN($posts_statuses_in)";
		}
		if ( $posts_not_in ) {
			$posts_where .= " AND $posts_table.ID NOT IN($posts_not_in)";
		}
		if ( $posts_in ) {
			$posts_where .= " AND $posts_table.ID IN($posts_in)";
		}

		// Fetch the first event that is part of a series that matches the date criteria.
		$related_group = "
				SELECT {$occurrences_table}.occurrence_id
				FROM {$posts_table}
    			INNER JOIN    {$occurrences_table} ON {$posts_table}.ID = {$occurrences_table}.post_id
				INNER JOIN {$series_relationship_table} ON {$series_relationship_table}.event_post_id = {$occurrences_table}.post_id
				INNER JOIN (
				    SELECT relationship.series_post_id, {$aggregate_function}({$occurrences_table}.{$column}) occurrence_date
				    FROM {$occurrences_table}
				    INNER JOIN {$series_relationship_table} as relationship ON relationship.event_post_id = {$occurrences_table}.post_id
				    WHERE CAST({$occurrences_table}.{$column} AS {$date_cast_type}) {$operator} {$value}
				    GROUP BY relationship.series_post_id
				) results_by_series ON results_by_series.series_post_id = {$series_relationship_table}.series_post_id AND results_by_series.occurrence_date = {$occurrences_table}.{$column}
 		WHERE {$posts_where}
		";

		// Fetches all the events that are not part of a series matching the date criteria.
		$unrelated = "
				SELECT {$occurrences_table}.occurrence_id
				FROM {$posts_table}
    			INNER JOIN {$occurrences_table} ON {$posts_table}.ID = {$occurrences_table}.post_id
				LEFT JOIN {$series_relationship_table} as relationship ON relationship.event_post_id = {$occurrences_table}.post_id
				WHERE CAST({$occurrences_table}.{$column} AS {$date_cast_type}) {$operator} {$value}
					AND relationship.event_post_id IS NULL
					AND {$posts_where}
		";

		$join .= "
		INNER JOIN ( {$related_group} UNION DISTINCT {$unrelated} ) AS `condense_occurrences`
			ON `condense_occurrences`.occurrence_id = `$occurrences_table`.occurrence_id";

		$this->cleanup_meta_query( $query );

		return $join;
	}

	/**
	 * Grabs any status query params and builds the IN(*) list.
	 *
	 * @since 6.1.2
	 *
	 * @param WP_Query $query The query object.
	 *
	 * @return string|null If there is a post_status param found, the compiled string.
	 */
	private function get_statuses_in( WP_Query $query ): ?string {
		if ( empty( $query->get( 'post_status' ) ) || ! is_array( $query->get( 'post_status' ) ) ) {
			return null;
		}

		// Escape strings for query.
		$statuses = array_map( function ( $status ) {
			global $wpdb;

			return (string) $wpdb->prepare( '%s', sanitize_text_field( $status ) );
		}, $query->get( 'post_status' ) );

		return implode( ',', $statuses );
	}

	/**
	 * Grabs any tec_ct1_post__not_in query params and builds the IN(*) list.
	 *
	 * @since 6.1.2
	 *
	 * @param WP_Query $query The query object.
	 *
	 * @return string|null If there is a tec_ct1_post__not_in param found, the compiled string.
	 */
	private function get_posts_not_in( WP_Query $query ): ?string {
		if ( empty( $query->get( 'tec_ct1_post__not_in' ) ) || ! is_array( $query->get( 'tec_ct1_post__not_in' ) ) ) {
			return null;
		}

		// Escape strings for query.
		$ids = array_map( function ( $id ) {
			global $wpdb;

			return (string) $wpdb->prepare( '%d', $id );
		}, $query->get( 'tec_ct1_post__not_in' ) );

		return implode( ',', $ids );
	}

	/**
	 * Grabs any tec_ct1_post__in query params and builds the IN(*) list.
	 *
	 * @since 6.1.2
	 *
	 * @param WP_Query $query The query object.
	 *
	 * @return string|null If there is a tec_ct1_post__in param found, the compiled string.
	 */
	private function get_posts_in( WP_Query $query ): ?string {
		if ( empty( $query->get( 'tec_ct1_post__in' ) ) || ! is_array( $query->get( 'tec_ct1_post__in' ) ) ) {
			return null;
		}

		// Escape strings for query.
		$ids = array_map( function ( $id ) {
			global $wpdb;

			return (string) $wpdb->prepare( '%d', $id );
		}, $query->get( 'tec_ct1_post__in' ) );

		return implode( ',', $ids );
	}

	/**
	 * Retrieve the value from `__meta_query` in order to find if the `meta_query` is populated to return the values
	 * from the ORM used on the legacy system with meta values.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query|null $query The current Query from the request.
	 *
	 * @return array<string, mixed> The filter found or an empty array if not found.
	 */
	private function get_filter( WP_Query $query = null ) {
		if ( $query === null ) {
			return [];
		}

		$meta_query = $query->get( '__meta_query' );

		if ( ! is_array( $meta_query ) ) {
			return [];
		}

		foreach ( [ 'ends-after', 'ends-before', 'starts-after', 'starts-before' ] as $key ) {
			if ( isset( $meta_query[ $key ] ) ) {
				return $meta_query[ $key ];
			}
		}

		return [];
	}

	/**
	 * Get the column to be used in the query based on the `$filter` array if not present fallback no `start_date.
	 *
	 * @param array<string, mixed> $filter An array with the current filter being processed from the Query.
	 *
	 * @return string The column to be used in the SQL query.
	 */
	private function get_column( array $filter = [] ) {
		$field_map = [
			'_EventStartDate'    => 'start_date',
			'_EventEndDate'      => 'end_date',
			'_EventStartDateUTC' => 'start_date_utc',
			'_EventEndDateUTC'   => 'end_date_utc',
		];

		if ( isset( $filter['key'], $field_map[ $filter['key'] ] ) ) {
			return $field_map[ $filter['key'] ];
		}

		// Default field if the field was not found.
		return 'start_date';
	}

	/**
	 * Find the operator inside the `$filter` if not present fallback to `>=`, the search of the operator
	 * is done in order to ensure only valid operators are passed into the SQL Query.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string, mixed> $filter An array with the current filter being processed from the Query.
	 *
	 * @return string A valid operator for the SQL query.
	 */
	private function get_operator( array $filter = [] ) {
		// Create a hash like map for quickly lookup of values.
		$valid_operators = [
			'>'  => true,
			'<'  => true,
			'='  => true,
			'<=' => true,
			'>=' => true,
		];

		// Make sure the operator provided is a valid one/
		if ( array_key_exists( 'compare', $filter ) && array_key_exists( $filter['compare'], $valid_operators ) ) {
			return $filter['compare'];
		}

		// Default operator if no operator is found.
		return '>=';
	}

	/**
	 * Get the value if present on the filter if not fallback to `CURDATE()` from MySQL. If the value is found on the
	 * filter the value is sanitized and prepared for the final SQL query.
	 *
	 * @since 6.0.0
	 *
	 * @see   https://dev.mysql.com/doc/refman/5.6/en/date-and-time-functions.html#function_curdate
	 *
	 * @param array<string, mixed> $filter An array with the current filter being processed from the Query.
	 *
	 * @return string The date used to compare inside the SQL.
	 */
	private function get_value( array $filter = [] ) {
		if ( array_key_exists( 'value', $filter ) ) {
			global $wpdb;

			return (string) $wpdb->prepare( '%s', sanitize_text_field( $filter['value'] ) );
		}

		// Default to the current date if no date is found.
		return 'CURDATE()';
	}

	/**
	 * Get the aggregate function to be used depending on the operator used for the request in order
	 * to find the order of top or bottom from the results.
	 *
	 * @since 6.0.0
	 *
	 * @param string $operator The operator used on the SQL Query.
	 *
	 * @return string The aggregate function to be used in to the SQL query.
	 */
	private function get_aggregate_function( $operator ) {
		$aggregate_functions = [
			'>'  => 'MIN',
			'>=' => 'MIN',
			'<'  => 'MAX',
			'<=' => 'MAX',
		];

		return array_key_exists( $operator, $aggregate_functions ) ? $aggregate_functions[ $operator ] : 'MIN';
	}

	/**
	 * Clean up the meta query to prevent execution of additional non required meta query joins into the post
	 * meta tables.
	 *
	 * The object is passed by reference in order to modify the properties of the object.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query|null $query The query object from the request.
	 */
	private function cleanup_meta_query( WP_Query &$query = null ) {
		if ( $query === null ) {
			return;
		}

		if ( ! $query->meta_query instanceof WP_Meta_Query ) {
			return;
		}

		// Remove any extra meta queries not required at this point.
		unset(
			$query->meta_query->queries['_eventhidefromupcoming_not_exists'],
			$query->meta_query->queries['ends-after'],
			$query->meta_query->queries['ends-before']
		);

		// If the only remaining field on the meta query is a relation value just remove it not required.
		if ( count( $query->meta_query->queries ) === 1 && isset( $query->meta_query->queries['relation'] ) ) {
			unset( $query->meta_query->queries['relation'] );
		}
	}

	/**
	 * Builds the correct cast type for the date limit depending on the input
	 * format of the limit date.
	 *
	 * @since 6.0.0
	 *
	 * @param string $value The limit date, as read from the filter.
	 *
	 * @return string The cast type for the date limit, either `DATE` or `DATETIME`.
	 */
	private function get_date_cast_type( string $value ): string {
		$parsed = date_parse( $value );

		// Use DATE if the limit does not contain time information.
		return $parsed['hour'] === false ? 'DATE' : 'DATETIME';
	}
}

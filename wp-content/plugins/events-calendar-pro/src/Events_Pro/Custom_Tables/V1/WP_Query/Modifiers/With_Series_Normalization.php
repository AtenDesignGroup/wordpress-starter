<?php
/**
 * Provides methods to normalize Series names or IDs to a uniform set in the context of Queries.
 *
 * @since 	6.0.4 Moved into Events Calendar Pro from The Events Calendar.
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\WP_Query\Modifiers
 */

namespace TEC\Events_Pro\Custom_Tables\V1\WP_Query\Modifiers;

use TEC\Events\Custom_Tables\V1\Traits\With_Unbound_Queries;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use WP_Query;

/**
 * Trait With_Series_Normalization
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\WP_Query\Modifiers
 */
trait With_Series_Normalization {
	use With_Unbound_Queries;

	/**
	 * A map from query hashes to the normalized set of Series IDs.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,array<int>>
	 */
	private $normalized_series_ids = [];

	/**
	 * Normalizes an input set of Series post IDs.
	 *
	 * @since 6.0.4 Only supports numeric input now. No longer reinforcing supporting both name and IDs.
	 *            Will no longer search the database for post names.
	 * @since 6.0.0
	 *
	 * @param WP_Query $query A reference to the Query object that is being filtered
	 *                        and for whose the normalization is being done.
	 *
	 * @return array<int> A normalized set of Series post IDs.
	 */
	private function normalize_query_series_ids( WP_Query $query ) {
		$series_input = (array) $query->get( 'related_series', [] );

		// Deduplicate.
		$series_input = array_unique( $series_input );

		// Filter out non-numeric values.
		return array_filter( $series_input, 'is_numeric' );
	}
}

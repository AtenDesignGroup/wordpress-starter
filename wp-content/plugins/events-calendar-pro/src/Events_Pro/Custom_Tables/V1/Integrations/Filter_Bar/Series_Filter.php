<?php
/**
 * Models the logic that will allow administrators to show a "by series" filters to visitors on the Event views.
 *
 * @since   6.0.0
 *
 * @package TEC\Filter_Bar\Custom_Tables\V1
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Integrations\Filter_Bar;

use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\Traits\With_Unbound_Queries;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use TEC\Events_Pro\Custom_Tables\V1\Tables\Series_Relationships;
use TEC\Filter_Bar\Custom_Tables\V1\Builder_Where_Contract;
use Tribe\Events\Filterbar\Views\V2\Filters\Context_Filter;
use Tribe\Events\Filterbar\Views\V2\Filters_Stack;
use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Events__Filterbar__Filter as Filter;
use Tribe__Events__Main as TEC;
use Tribe__Utils__Array as Arr;
use WP_Query;

/**
 * Class Series_Filter
 *
 * @since   6.0.0
 *
 * @package TEC\Filter_Bar\Custom_Tables\V1
 */
class Series_Filter extends Filter implements Builder_Where_Contract {
	use With_Unbound_Queries;
	use Context_Filter;

	/**
	 * The type of filter class, and controls, of this Filter.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public $type = 'select';

	/**
	 * Returns the administration form values for this Filter.
	 *
	 * @since 6.0.0
	 *
	 * @return string The administration form values for this Filter.
	 */
	public function get_admin_form() {
		$title = $this->get_title_field();
		$type  = $this->get_multichoice_type_field();

		return $title . $type;
	}

	/**
	 * Returns the value for this filter instance.
	 *
	 * @since 6.0.0
	 *
	 * @return array<array<string,string|int> A list of name and value maps for each value the filter will allow
	 *                                        filtering Events by.
	 */
	public function get_values() {
		$cache         = tribe_cache();
		$cache_id      = __CLASS__ . '_public_series_with_events';
		$cache_trigger = Cache_Listener::TRIGGER_SAVE_POST;

		$values = $cache->get( $cache_id, $cache_trigger, false );

		if ( false !== $values ) {
			return $values;
		}

		$values = $this->fetch_values();

		$cache->set( $cache_id, $values, WEEK_IN_SECONDS, $cache_trigger );

		return $values;
	}

	/**
	 * Fetches, with a direct and non-cached query to the database, the Filter values.
	 *
	 * @since 6.0.0
	 *
	 * @return array<array<string,string|int> A list of name and value maps for each value the filter will allow
	 *                                        filtering Events by.
	 */
	private function fetch_values() {
		$series_relationships = Series_Relationships::table_name( true );
		$post_type            = Series::POSTTYPE;
		$count                = 0;
		$found                = null;

		// Init with an empty array to avoid possible issues with empty results.
		$carry = [ [] ];

		global $wpdb;
		do {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_title from {$wpdb->posts} p
							RIGHT JOIN {$series_relationships} sr
								ON sr.series_post_id = p.ID
							WHERE p.post_type = %s
								AND p.post_status = 'publish'
							GROUP BY sr.series_post_id
							ORDER BY p.post_title ASC
							LIMIT 1000 ",
					$post_type
				),
				ARRAY_A
			);
			$carry[] = $results;
			$found   = $found !== null ? $found : $wpdb->num_rows;
			$count   += count( $results );
		} while ( $count < $found );

		$values = array_map( static function ( array $result ) {
			return [
				'name'  => (string) $result['post_title'],
				'value' => (int) $result['ID'],
			];
		},
			array_merge( ...$carry )
		);

		return $values;
	}

	/**
	 * Sets up the Filter JOIN clause.
	 *
	 * The clause will be added by the Filter to the current Query JOIN SQL to join the Occurrences
	 * table to the Series Relationships one.
	 *
	 * @since 6.0.0
	 *
	 * @return void This method does not return any value and has the side-effect of setting up the Filter
	 *              JOIN clause.
	 */
	protected function setup_join_clause() {
		if ( empty( $this->currentValue ) ) {
			return;
		}

		$series_relationships = Series_Relationships::table_name( true );
		$occurrences          = Occurrences::table_name( true );

		$this->joinClause = "\n INNER JOIN ${series_relationships} sr ON sr.event_post_id = ${occurrences}.post_id";
	}

	/**
	 * Sets up the Filter WHERE clause.
	 *
	 * The clause will be added by the Filter to the current Query WHERE SQL to filter Events by their relationship
	 * with one or more Series.
	 *
	 * @since 6.0.0
	 *
	 * @return void This method does not return any value and has the side-effect of setting up the Filter
	 *              WHERE clause.
	 */
	protected function setup_where_clause() {
		if ( empty( $this->currentValue ) ) {
			return;
		}

		$series_ids        = implode( ',', array_map( 'absint', Arr::list_to_array( $this->currentValue ) ) );
		$this->whereClause = "\n AND sr.series_post_id IN ({$series_ids})";
	}

	/**
	 * Returns the WHERE clause filtered by the Filter.
	 *
	 * @since 6.0.0
	 *
	 * @return string The filtered WHERE clause.
	 */
	public function get_where_clause(){
		$this->setup_where_clause();
		return $this->whereClause;
	}

	/**
	 * Returns the JOIN clause filtered by the Filter.
	 *
	 * @since 6.0.0
	 *
	 * @return string The filtered JOIN clause.
	 */
	public function get_join_clause(){
		if ( empty( $this->joinClause ) ) {
			$this->setup_join_clause();
		}
		return $this->joinClause;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addQueryJoin( $posts_join, $query ) {
		if ( ! $this->is_event_query( $query ) ) {
			return $posts_join;
		}

		if ( empty( $this->joinClause ) ) {
			$this->setup_join_clause();
		}

		if ( stripos( $posts_join, $this->joinClause ) !== false ) {
			return $posts_join;
		}

		return $posts_join . " \n " . $this->joinClause;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addQueryWhere( $posts_where, $query ) {
		if ( ! $this->is_event_query( $query ) ) {
			return $posts_where;
		}

		if ( empty( $this->whereClause ) ) {
			$this->setup_where_clause();
		}

		if ( stripos( $posts_where, $this->whereClause ) !== false ) {
			return $posts_where;
		}

		return $posts_where . " \n " .  $this->whereClause;
	}

	/**
	 * {@inheritDoc}
	 */
	public function build_where(Filters_Stack $stack) {
		if ( empty( $stack->get_post_ids_pool() ) ) {
			return $stack::$query_id_comment;
		}

		$provisional_post = tribe(Provisional_Post::class );
		global $wpdb;
		$ids = [];
		// Normalization of the IDs - the base in order to make sure the `occurrence_id` matches the values from the occurrence table.
		foreach ( $stack->get_post_ids_pool() as $id ) {
			$ids[] = $wpdb->prepare( '%d', $provisional_post->normalize_provisional_post_id( $id ) );
		}

		$ids = implode( ',', $ids );
		$occurrence_table = Occurrences::table_name( true );
		$comment =  $stack::$query_id_comment;
		return " {$comment} AND {$occurrence_table}.occurrence_id IN ( {$ids} )";
	}

	/**
	 * Returns whether the WordPress query is to only fetch Events or not.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query|mixed $query The input WordPress query reference.
	 *
	 * @return bool Whether the WordPress query is to only fetch Events or not.
	 */
	private function is_event_query( $query ) {
		if ( ! $query instanceof WP_Query ) {
			return false;
		}

		return array_filter( (array) $query->get( 'post_type', [] ) ) === [ TEC::POSTTYPE ];
	}
}

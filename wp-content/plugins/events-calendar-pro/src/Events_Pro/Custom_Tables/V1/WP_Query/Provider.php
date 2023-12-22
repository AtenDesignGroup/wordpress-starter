<?php
/**
 * Handles the plugin integration with the Custom Tables queries
 * present in TEC.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\WP_Query
 */

namespace TEC\Events_Pro\Custom_Tables\V1\WP_Query;

use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use TEC\Events\Custom_Tables\V1\WP_Query\Modifiers\WP_Query_Modifier;
use TEC\Events\Custom_Tables\V1\WP_Query\Monitors\Query_Monitor;
use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator as Provisional_ID_Generator;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post_Cache;
use TEC\Events_Pro\Custom_Tables\V1\WP_Query\Repository\Custom_Tables_Query_Filters;
use TEC\Common\Contracts\Service_Provider;
use Tribe__Repository__Query_Filters;
use WP_Post;
use WP_Query;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\WP_Query
 */
class Provider extends Service_Provider implements \TEC\Events\Custom_Tables\V1\Provider_Contract {
	/**
	 * Whether the provider is currently in no-op mode or not.
	 *
	 * @since 6.0.4
	 *
	 * @var bool
	 */
	private $noop;

	/**
	 * Registers the implementations and filters required by the plugin
	 * to integrate with Custom Tables Queries.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->container->singleton( self::class, self::class );
		$this->container->singleton( Condense_Events_Series::class, Condense_Events_Series::class );
		$this->container->singleton( Replace_Results::class, Replace_Results::class );
		$this->container->singleton( Provisional_Post::class, function () {
			remove_filter( 'query', [ $this, 'hydrate_provisional_post' ], 200 );
			$provisional_post = new Provisional_Post(
				$this->container->make( Provisional_Post_Cache::class ),
				$this,
				$this->container->make( 'cache' )
			);
			add_filter( 'query', [ $this, 'hydrate_provisional_post' ], 200 );
			add_filter( 'get_post_metadata', [ $this, 'hydrate_tec_occurrence_meta' ], 10, 3 );

			return $provisional_post;
		} );
		$this->container->singleton( Custom_Query_Filters::class, function () {
			$base = tribe( Provisional_ID_Generator::class )->current();

			return new Custom_Query_Filters( $base, $this->container->make( Provisional_Post::class ) );
		} );

		if ( ! has_filter( 'query', [ $this, 'hydrate_provisional_post' ] ) ) {
			add_filter( 'query', [ $this, 'hydrate_provisional_post' ], 200 );
		}

		if ( ! has_action(
			'tec_events_custom_tables_v1_custom_tables_query_results',
			[ $this, 'hydrate_provisional_post_caches' ] )
		) {
			add_action(
				'tec_events_custom_tables_v1_custom_tables_query_results',
				[ $this, 'hydrate_provisional_post_caches' ]
			);
		}

		if ( ! has_filter( 'update_post_metadata_cache', [ $this, 'hydrate_provisional_meta_cache' ] ) ) {
			add_filter( 'update_post_metadata_cache', [ $this, 'hydrate_provisional_meta_cache' ], 10, 2 );
		}

		if ( ! has_filter( 'get_post_metadata', [ $this, 'hydrate_cache_on_occurrence' ] ) ) {
			add_filter( 'get_post_metadata', [ $this, 'hydrate_cache_on_occurrence' ], 10, 4 );
		}

		if ( ! has_filter( 'posts_results', [ $this, 'replace_posts_results' ] ) ) {
			add_filter( 'posts_results', [ $this, 'replace_posts_results' ], 10, 2 );
		}

		if ( ! has_filter( 'tec_events_custom_tables_v1_occurrence_select_fields', [ $this, 'filter_occurrence_fields' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_occurrence_select_fields', [ $this, 'filter_occurrence_fields' ], 10, 1 );
		}

		if ( ! has_action( 'tec_events_custom_tables_v1_custom_tables_query_pre_get_posts', [ $this, 'register_custom_tables_filters' ] ) ) {
			add_action(
				'tec_events_custom_tables_v1_custom_tables_query_pre_get_posts',
				[ $this, 'register_custom_tables_filters' ]
			);
		}

		if ( ! has_filter( 'tec_events_custom_tables_v1_custom_tables_query_vars', [ $this, 'filter_query_vars' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_custom_tables_query_vars', [ $this, 'filter_query_vars' ] );
		}

		if ( ! has_filter( 'tec_events_custom_tables_v1_custom_tables_query_where', [ $this, 'filter_where' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_custom_tables_query_where', [ $this, 'filter_where' ], 10, 2 );
		}

		if ( ! has_filter( 'tec_events_custom_tables_v1_custom_tables_query_hydrate_posts', [
			$this,
			'hydrate_query_posts'
		] ) ) {
			add_filter( 'tec_events_custom_tables_v1_custom_tables_query_hydrate_posts', [
				$this,
				'hydrate_query_posts'
			], 10, 2 );
		}

		if ( ! has_filter( 'tec_events_custom_tables_v1_query_modifier_implementations', [
			$this,
			'filter_query_modifier_implementations'
		] ) ) {
			add_filter( 'tec_events_custom_tables_v1_query_modifier_implementations', [
				$this,
				'filter_query_modifier_implementations'
			], 10, 2 );
		}

		if ( ! has_filter( 'tec_events_custom_tables_v1_query_modifier_applies_to_query', [
			$this,
			'filter_should_modify_query'
		] ) ) {
			add_filter( 'tec_events_custom_tables_v1_query_modifier_applies_to_query', [
				$this,
				'filter_should_modify_query'
			], 10, 3 );
		}

		if ( ! has_filter( 'tec_events_pro_tribe_repository_event_series_filter_override', [
			$this,
			'tribe_event_series_filter_override'
		] ) ) {
			add_filter( 'tec_events_pro_tribe_repository_event_series_filter_override', [
				$this,
				'tribe_event_series_filter_override'
			], 10, 3 );
		}

		if ( ! has_filter( 'parse_query', [
			$this,
			'parse_for_sequence_id_lookup'
		] ) ) {
			add_filter( 'parse_query', [
				$this,
				'parse_for_sequence_id_lookup'
			], 10 );
		}

		$this->handle_collapse_recurring_event_instances();
	}

	/**
	 * Inspects and potentially modifies a WP_Query object for `eventSequence` queries.
	 *
	 * @since 6.0.11
	 *
	 * @param WP_Query $wp_query The WP_Query instance to potentially modify for `eventSequence` lookups.
	 */
	public function parse_for_sequence_id_lookup( $wp_query ): void {
		$this->container->make( Custom_Query_Filters::class )->parse_for_sequence_id_lookup( $wp_query );
	}

	/**
	 * Will override the series repository query.
	 *
	 * @since 6.0.5
	 *
	 * @param bool                             $filter_override Flag whether to continue using filter parsing.
	 * @param Tribe__Repository__Query_Filters $filter          This instance of the filter object.
	 * @param bool|numeric|WP_Post             $in_series       The series param.
	 *
	 * @return bool
	 */
	public function tribe_event_series_filter_override( $filter_override, $filter, $in_series ) {
		return tribe( Custom_Tables_Query_Filters::class )->apply_series_filters( $filter_override, $filter, $in_series );
	}

	/**
	 * Filter query modifier implementations.
	 *
	 * @since 6.0.5
	 *
	 * @param array<WP_Query_Modifier> $implementations The query modifier implementations to be filtered.
	 * @param Query_Monitor            $query_monitor   An instance of a Query Monitor class.
	 *
	 * @return array<WP_Query_Modifier> The filtered query modifier implementations.
	 */
	public function filter_query_modifier_implementations( array $implementations, $query_monitor ): array {
		return $this->container->make( WP_Query_Monitor_Filters::class )
		                       ->filter_query_modifier_implementations( $implementations, $query_monitor );
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
	public function filter_should_modify_query( bool $should_filter, $wp_query, WP_Query_Modifier $modifier ): bool {
		return $this->container->make( WP_Query_Monitor_Filters::class )
		                       ->filter_should_modify_query( $should_filter, $wp_query, $modifier );
	}

	/**
	 * Removes the actions and filters set by this provider.
	 *
	 * @since 6.0.0
	 */
	public function unregister() {
		remove_action( 'query', [ $this, 'hydrate_provisional_post' ], 200 );
		remove_action(
			'tec_events_custom_tables_v1_custom_tables_query_results',
			[ $this, 'hydrate_provisional_post_caches' ]
		);
		remove_filter( 'tec_events_custom_tables_v1_custom_tables_query_vars', [ $this, 'filter_query_vars' ] );
		remove_filter( 'tec_events_custom_tables_v1_custom_tables_query_where', [ $this, 'filter_where' ] );
		remove_filter( 'update_post_metadata_cache', [ $this, 'hydrate_provisional_meta_cache' ] );
		remove_filter( 'get_post_metadata', [ $this, 'hydrate_cache_on_occurrence' ] );
		remove_filter( 'posts_results', [ $this, 'replace_posts_results' ] );
		remove_filter( 'tec_events_custom_tables_v1_occurrence_select_fields', [ $this, 'filter_occurrence_fields' ] );
		remove_action(
			'tec_events_custom_tables_v1_custom_tables_query_pre_get_posts',
			[
				$this,
				'register_custom_tables_filters',
			]
		);
		remove_filter( 'tribe_repository_events_collapse_recurring_event_instances', '__return_false' );
		$condense_series_query_args = $this->container->callback( Condense_Events_Series::class, 'query_args' );
		remove_filter( 'tribe_repository_events_query_args', $condense_series_query_args );
		$condense_series_pre_get_posts = $this->container->callback( Condense_Events_Series::class, 'pre_get_posts' );
		remove_action( 'tec_events_custom_tables_v1_custom_tables_query_pre_get_posts', $condense_series_pre_get_posts );
		remove_filter( 'tec_events_custom_tables_v1_custom_tables_query_hydrate_posts', [
			$this,
			'hydrate_query_posts'
		] );
		remove_filter( 'tec_events_custom_tables_v1_query_modifier_implementations', [
			$this,
			'filter_query_modifier_implementations'
		] );
		remove_filter( 'tec_events_custom_tables_v1_query_modifier_applies_to_query', [
			$this,
			'filter_should_modify_query'
		] );
		remove_filter( 'tec_events_pro_tribe_repository_event_series_filter_override', [
			$this,
			'tribe_event_series_filter_override'
		] );
		remove_filter( 'parse_query', [
			$this,
			'parse_for_sequence_id_lookup'
		] );
	}

	/**
	 * Adds appropriate changes to the query_vars before constructing
	 * our custom query object.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $query_vars The Custom Tables Query variables.
	 *
	 * @return array<string,mixed> The filtered Custom Tables Query variables.
	 */
	public function filter_query_vars( array $query_vars ) {
		return $this->noop ?
			$query_vars
			: $this->container->make( Custom_Query_Filters::class )->filter_query_vars( $query_vars );
	}

	/**
	 * Adds appropriate custom table mutations to the WHERE clause with our
	 * custom $wp_query object.
	 *
	 * @since 6.0.0
	 *
	 * @param string   $where    The `WHERE` statement as produced by the Custom Tables Query.
	 * @param WP_Query $wp_query A reference to the query object being filtered.
	 *
	 * @return string The filtered `WHERE` statement.
	 */
	public function filter_where( $where, WP_Query $wp_query ) {
		return $this->noop ?
			$where
			: $this->container->make( Custom_Query_Filters::class )->filter_where( $where, $wp_query );
	}

	/**
	 * Hooks on the `query` filter to hydrate a provisional post instance and accessory data
	 * if required.
	 *
	 * @since 6.0.0
	 *
	 * @param string $query_sql The query SQL to parse.
	 *
	 * @return string The filtered query.
	 */
	public function hydrate_provisional_post( $query_sql ) {
		return $this->noop ?
			$query_sql
			: $this->container->make( Provisional_Post::class )->hydrate_provisional_post_query( $query_sql );
	}

	/**
	 * Hydrates the Provisional Post caches (post fields, custom fields) to
	 * allow calls to `get_post( $provisional_post_id )` to return properly
	 * formed post objects.
	 *
	 * @since 6.0.0
	 *
	 * @param array|object|null $results The query results.
	 */
	public function hydrate_provisional_post_caches( $results ) {
		if (
			$this->noop
			|| ! (
				$results
				&& is_array( $results )
				&& array_filter( (array) $results, 'is_numeric' )
			)
		) {
			// Not a set of post IDs or an empty array: let's avoid building the Provisional Post instance.
			return $results;
		}

		return $this->container->make( Provisional_Post::class )->hydrate_caches( $results );
	}

	/**
	 * Hydrates the meta cache of an occurrence in case this cache has not been set already.
	 *
	 * @since 6.0.0
	 *
	 * @param null|bool $meta null if we can override it.
	 * @param array     $ids  An array with the IDs that requested the meta values.
	 *
	 * @return bool|null Whether caches were correctly updated or not.
	 */
	public function hydrate_provisional_meta_cache( $meta, $ids ) {
		if ( $this->noop || $meta !== null ) {
			return $meta;
		}

		return $this->container->make( Provisional_Post::class )->hydrate_caches( $ids );
	}

	/**
	 * Hydrate the cache when a meta key is requested individually, if data is already on the cache avoid processing.
	 *
	 * @since 6.0.0
	 *
	 * @param mixed  $value        The value to return, either a single metadata value or an array
	 *                             of values depending on the value of `$single`. Default null.
	 * @param int    $object_id    ID of the object metadata is for.
	 * @param string $meta_key     Metadata key.
	 * @param bool   $single       Whether to return only the first value of the specified `$meta_key`.
	 */
	public function hydrate_cache_on_occurrence( $value, $object_id, $meta_key, $single ) {
		if ( $this->noop ) {
			return $value;
		}

		$provisional_post = $this->container->make( Provisional_Post::class );
		// The requested element is not an occurrence move on.
		if ( ! $provisional_post->is_provisional_post_id( $object_id ) ) {
			return $value;
		}

		$cache = wp_cache_get( $object_id, 'post_meta' );
		// This is already on the post_meta cache move on.
		if ( false !== $cache ) {
			return $value;
		}

		$provisional_post->hydrate_caches( [ $object_id ] );

		return $value;
	}

	/**
	 * If the query was not able to find results for specific occurrence IDs we hydrate the cache
	 * before the results are returned to the next WP_Query call.
	 *
	 * @since 6.0.0
	 *
	 * @param array|object|null $posts    The query results.
	 * @param WP_Query|null     $wp_query A reference to the WP Query object whose post
	 *                                    results are being filtered.
	 *
	 * @return mixed The original results, replaced if required.
	 */
	public function replace_posts_results( $posts, $wp_query = null ) {
		if ( $this->noop || ! $wp_query instanceof WP_Query ) {
			return $posts;
		}

		return $this->container->make( Replace_Results::class )->replace( $posts, $wp_query );
	}

	/**
	 * Filters the SQL required to select distinct Occurrences in the context
	 * of a Custom Tables Query.
	 *
	 * @since 6.0.0
	 *
	 * @param string $select_fields       The input SQL required to select distinct Occurrences in the context
	 *                                    of a Custom Tables Query.
	 *
	 * @return string TheSQL required to select distinct Occurrences in the context of a Custom Tables Query,
	 *                pointing to the Occurrences custom table.
	 */
	public function filter_occurrence_fields( $select_fields ) {
		return $this->noop ?
			$select_fields
			: $this->container->make( Custom_Query_Filters::class )->get_occurrence_field();
	}

	/**
	 * Function fired on `tec_events_custom_tables_v1_custom_tables_query_pre_get_posts` via the custom meta query in order
	 * to avoid running on every WP query.
	 *
	 * @since 6.0.0
	 *
	 * @see `tec_events_custom_tables_v1_custom_tables_query_pre_get_posts`
	 */
	public function register_custom_tables_filters() {
		if ( $this->noop ) {
			return;
		}

		add_filter( 'posts_where', [ $this, 'normalize_occurrence_id' ], 10, 2 );
	}

	/**
	 * In case a request is made using the post ID like preview changes from a particular occurrence ID like:
	 * `?post_type=tribe_events&p=10000621&preview=true` we need to make sure instead of `wp_posts.ID` are doing
	 * `wp_tec_occurrences.occurrence_id`.
	 *
	 * Additionally, we need to normalize the provisional post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param string        $where The current where query.
	 * @param WP_Query|null $query The current Query instance of the request.
	 *
	 * @return string The updated where clause.
	 */
	public function normalize_occurrence_id( $where, $query = null ) {
		if ( $this->noop || ! $query instanceof WP_Query ) {
			return $where;
		}

		if ( $query === null ) {
			return $where;
		}

		if ( ! $query->get( 'p' ) ) {
			return $where;
		}

		$provisional = tribe( Provisional_Post::class );
		if ( ! $provisional->is_provisional_post_id( $query->get( 'p' ) ) ) {
			return $where;
		}

		global $wpdb;
		$normalized_id = $provisional->normalize_provisional_post_id( $query->get( 'p' ) );

		return str_replace(
			"{$wpdb->posts}.ID = {$query->get('p')}",
			Occurrences::table_name( true ) . ".occurrence_id = $normalized_id",
			$where
		);
	}

	/**
	 * Handles the request to collapse a Recurring Event instances to the first upcoming one.
	 *
	 * Note: in the Custom Tables implementation this means collapsing Series to the first
	 * upcoming Occurrence.
	 *
	 * @since 6.0.0
	 */
	private function handle_collapse_recurring_event_instances() {
		add_filter( 'tribe_repository_events_collapse_recurring_event_instances', '__return_false' );
		$condense_series_query_args = $this->container->callback( Condense_Events_Series::class, 'query_args' );
		add_filter( 'tribe_repository_events_query_args', $condense_series_query_args );
		$condense_series_pre_get_posts = $this->container->callback( Condense_Events_Series::class, 'pre_get_posts' );
		add_action( 'tec_events_custom_tables_v1_custom_tables_query_pre_get_posts', $condense_series_pre_get_posts );
	}

	/**
	 * Hydrates an Occurrence provision post object caches when the `_tec_occurrence` property is accessed
	 * on the post object.
	 *
	 * @since 6.0.0
	 *
	 * @param mixed  $meta_value The original meta value as worked out by WordPress.
	 * @param int    $object_id  The ID of the object the meta is for.
	 * @param string $meta_key   The meta key.
	 *
	 * @return mixed The original meta value as worked out by WordPress, unmodified by the call.
	 */
	public function hydrate_tec_occurrence_meta( $meta_value, $object_id, $meta_key ) {
		if ( $this->noop ) {
			return $meta_value;
		}

		$object_id = (int) $object_id;

		if ( $meta_key !== '_tec_occurrence' ) {
			// Smaller optimization to avoid the service locator resolution only to bail out.
			return $meta_value;
		}

		return $this->container->make( Provisional_Post::class )
			->hydrate_tec_occurrence_meta( $meta_value, $object_id, $meta_key );
	}

	/**
	 * Hydrates the Custom Tables Query post results early.
	 *
	 * @since 6.0.3
	 *
	 * @param array               $query_posts The posts returned by the query.
	 * @param Custom_Tables_Query $query       The Custom Tables Query instance.
	 *
	 * @return array The posts returned by the query, hydrated.
	 */
	public function hydrate_query_posts( array $query_posts, Custom_Tables_Query $query ): array {
		return $this->noop ?
			$query_posts
			: $this->container->make( Replace_Results::class )->hydrate_query_posts( $query_posts, $query );
	}

	/**
	 * Puts the Service Provider in a no-op mode, any method hooked
	 * to actions will not run, any method hooked to filters will return
	 * the filter input value.
	 *
	 * Calling `unregister` and `register` while an action or filter are being applied
	 * will not remove them and will, instead, cause them to be added twice.
	 *
	 * @since 6.0.4
	 *
	 * @param bool $noop Whether the Service Provider should be in no-op mode or not.
	 *
	 * @return void The Service Provider is put in no-op mode.
	 */
	public function noop( bool $noop ): void {
		$this->noop = $noop;
	}
}

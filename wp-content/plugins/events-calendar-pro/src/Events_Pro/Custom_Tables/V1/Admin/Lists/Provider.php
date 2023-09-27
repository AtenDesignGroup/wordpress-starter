<?php
/**
 * Handle the registration and hooking of the plugin integration and support of the Admin UI lists.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Admin\Lists
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Admin\Lists;

use TEC\Events_Pro\Custom_Tables\V1\Links\Links;
use TEC\Events_Pro\Custom_Tables\V1\Series\Admin_List;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use TEC\Common\Contracts\Service_Provider;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_Query;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Admin\Lists
 */
class Provider extends Service_Provider {


	/**
	 * Hooks on the Admin UI post lists to filter the options and values available.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->container->singleton( __CLASS__, $this );
		$this->container->singleton( Admin_List::class, Admin_List::class );
		$this->container->singleton( Links::class, Links::class );

		$post_types = [ TEC::POSTTYPE, Series::POSTTYPE ];
		$context    = tribe_context();
		if ( ! ( is_admin()
		         && (
			         // To render PHP initial state.
			         $context->is_editing_post( $post_types )
			         // To render during AJAX updates to the posts' list (quick-edit).
			         || $context->is_inline_editing_post( $post_types )
		         )
		) ) {
			return;
		}

		$series_post_type = Series::POSTTYPE;
		add_filter( "manage_{$series_post_type}_posts_columns", [
			tribe( Admin_List::class ),
			'include_custom_columns'
		] );
		add_filter( "manage_edit-{$series_post_type}_sortable_columns", [
			tribe( Admin_List::class ),
			'include_sortable_columns'
		] );
		add_filter( "posts_clauses", [ tribe( Admin_List::class ), 'filter_series_rows_clauses' ], 10, 2 );
		add_action( "manage_{$series_post_type}_posts_custom_column", [
			tribe( Admin_List::class ),
			'custom_column'
		], 10, 2 );
		add_filter( 'the_posts', [ $this, 'populate_series_admin_caches' ], 10, 2 );
		add_filter( 'manage_' . TEC::POSTTYPE . '_posts_columns', [ $this, 'filter_events_post_columns' ] );
		add_action( 'manage_' . TEC::POSTTYPE . '_posts_custom_column', [
			$this,
			'filter_events_post_custom_columns'
		], 10, 2 );
		add_action( 'all_admin_notices', [ $this, 'render_recurrence_svg' ] );
	}

	/**
	 * Adds the Series columns to the Event admin list.
	 *
	 * @since 6.0.0
	 * @since 6.0.11 Moved the method logic to the Columns class.
	 *
	 * @param array<string,string> $columns A list of the columns that will be shown to the user for
	 *                                      the Event post type as produced by WordPress or previous
	 *                                      filters.
	 *
	 * @return array<string,string> The filtered map of columns for the Event post type.
	 */
	public function filter_events_post_columns( $columns ) {
		if ( ! is_array( $columns ) ) {
			return $columns;
		}

		return $this->container->make( Columns::class )->filter_events_post_columns( $columns );
	}

	/**
	 * Populate the admin caches for Series.
	 *
	 * @since 6.0.0
	 *
	 * @param array    $posts
	 * @param WP_Query $query
	 *
	 * @return array<int|WP_Post> The input list of posts, left intact.
	 */
	public function populate_series_admin_caches( $posts, $query ) {
		if ( ! ( is_array( $posts ) && $query instanceof WP_Query ) ) {
			return $posts;
		}

		return $this->container->make( Caches::class )->populate_series_admin_caches( $posts, $query );
	}

	/**
	 * Create a sprite of symbols to resize the SVG using a viewBox property. The sprite is rendered before the full
	 * events table.
	 *
	 * @since 6.0.0
	 * @since 6.0.11 Moved the method logic to the Columns class.
	 *
	 * @retur void The sprite is echoed.
	 */
	public function render_recurrence_svg() {
		$this->container->make( Columns::class )->render_recurrence_svg();
	}

	/**
	 * Filters the columns used in the Admin UI posts list table to populate and output the data using CT1 information
	 * for Events.
	 *
	 * @since 6.0.0
	 * @since 6.0.11 Moved the method logic to the Columns class.
	 *
	 * @param string $column_name The name of the column to filter.
	 * @param int    $post_id     The ID of the post to filter.
	 *
	 * @return void The filtered column value is echoed, if required.
	 */
	public function filter_events_post_custom_columns( $column_name, $post_id ) {
		if ( ! is_string( $column_name ) && is_numeric( $post_id ) ) {
			return;
		}

		$this->container->make( Columns::class )->filter_events_post_custom_columns( $column_name, (int) $post_id );
	}
}

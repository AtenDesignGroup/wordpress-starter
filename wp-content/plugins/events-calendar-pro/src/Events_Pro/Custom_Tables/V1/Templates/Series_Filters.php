<?php
/**
 * Manages the filters that should be applied to Views v2 to correctly display and link
 * in the context of a Series page request.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 *
 * @todo    test
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Templates;

use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use Tribe\Events\Pro\Views\V2\Assets as ECP_Assets;
use Tribe\Events\Views\V2\Assets as TEC_Assets;
use Tribe\Events\Views\V2\Hooks;
use Tribe\Events\Views\V2\Manager;
use Tribe\Events\Views\V2\Url;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\View_Interface;
use Tribe\Events\Pro\Views\V2\Views\Summary_View;
use Tribe\Events\Pro\Views\V2\Views\Photo_View;
use Tribe\Events\Pro\Views\V2\Views\Week_View;
use Tribe\Events\Pro\Views\V2\Views\Map_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe__Context as Context;
use Tribe__Events__Main as TEC;
use Tribe__Utils__Array as Arr;
use WP_Post;

/**
 * Class Series_Filters
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */
class Series_Filters {

	/**
	 * Filters the available and public query vars to add the related Series one.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string> $query_vars The public query vars.
	 *
	 * @return array<string> The update public query vars.
	 */
	public function filter_query_vars( $query_vars ) {
		$query_vars[] = 'related_series';

		return $query_vars;
	}

	/**
	 * Update the Tribe context to ready the query param `related_series` if present.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string, array<string, array>> $locations The different type of locations.
	 * @param Context $context The current context.
	 *
	 * @return array An array with the updated locations.
	 */
	public function update_tribe_context( array $locations, Context $context ) {
		$locations['related_series'] = [
			'read' => [
				$context::FUNC => static function () {
					return (int) get_query_var( 'related_series', 0 );
				}
			]
		];

		return $locations;
	}

	/**
	 * Filters the Views V2 repository arguments to add the one that will
	 * keep the filter based on the Series relationship.
	 *
	 * @since 6.0.4	Changing to utilize the global repository filter instead,
	 *              which passes the View object as the second param.
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $repository_args A map of the current Views
	 *                                             repository arguments.
	 * @param View_Interface      $view            A reference to the View.
	 *
	 * @return array<string,mixed> The updated View repository arguments.
	 */
	public function filter_repository_args( $repository_args, $view ) {
		if ( ! ( is_array( $repository_args ) && $view instanceof View_Interface ) ) {
			return $repository_args;
		}

		$related_series = $view->get_context()->get( 'related_series', false );

		if ( empty( $related_series ) ) {
			return $repository_args;
		}

		// The repository args will be translated into WP_Query args when not mapped.
		$repository_args['related_series'] = $related_series;

		return $repository_args;
	}

	/**
	 * Replaces the View URL object reference with one that will correctly parse
	 * the View parameters when presented in the context of the Series.
	 *
	 * @since 6.0.0
	 *
	 * @param View $view A reference to the View instance that has just set up the loop.
	 */
	public function replace_view_url_object( $view ) {
		$related_series = $view->get_context()->get( 'related_series', false );

		if ( empty( $related_series ) ) {
			return;
		}

		$url       = $view->get_url( false );
		$url_query = wp_parse_url( $url, PHP_URL_QUERY );
		wp_parse_str( $url_query, $url_query_args );
		$url_query_args['related_series'] = $related_series;
		$new_view_url                     = new Url( add_query_arg( $url_query_args, home_url() ) );

		$view->set_url_object( $new_view_url );
	}

	/**
	 * Filters the View URL query arguments to add the one that will store the value of
	 * the Series Relationship, if any.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $query_args A map of the current query arguments.
	 * @param View $view A reference to the instance of the View that is currently
	 *                                        being filtered.
	 *
	 * @return array<string,mixed> The filtered View URL query arguments.
	 */
	public function filter_query_args( $query_args, $view ) {
		$series_id = $view->get_context()->get( 'related_series', false );

		if ( empty( $series_id ) ) {
			return $query_args;
		}

		$series_post_type = Series::POSTTYPE;
		unset(
			$query_args[ $series_post_type ],
			$query_args['name']
		);
		$query_args['post_type']      = TEC::POSTTYPE;
		$query_args['related_series'] = $series_id;

		return $query_args;
	}

	/**
	 * Injects, appending it to the post content, the current View filtered for the Series relationship
	 *
	 *
	 * @since 6.0.0
	 *
	 * @param string $content The Series post content.
	 *
	 * @return string The filtered Series post content.
	 */
	public function inject_content( $content ) {
		$queried_object = get_queried_object();

		if ( ! (
			$queried_object instanceof WP_Post
			&& tribe( Series::class )->is_same_type( $queried_object ) )
		) {
			return $content;
		}

		/** @var WP_Post $queried_object */
		$series_id = $queried_object->ID;

		/** @var Manager $views_v2_template_manager */
		$views_v2_template_manager = tribe( Manager::class );

		/**
		 * Allows filtering the default View that should be used to display the
		 * list of Events related to a Series.
		 *
		 * @since 6.0.0
		 *
		 * @param string $default The default view slug, e.g. `summary` or `list`.
		 */
		$default = apply_filters(
			'tec_events_pro_custom_tables_v1_series_default_view',
			Summary_View::get_view_slug()
		);

		$event_display = get_query_var( 'eventDisplay', $default );

		$view_slug = $this->get_filtered_view_slug( $event_display );

		$context = tribe_context()->alter(
			[
				'event_display'      => $event_display,
				'event_display_mode' => $event_display,
			]
		);

		View::set_container( tribe() );

		$view = View::make( $views_v2_template_manager->get_view_class_by_slug( $view_slug ), $context );
		$view->disable_url_management();
		$view->set_context( $view->get_context()->alter( [
			'related_series' => $series_id,
		] ) );

		tribe_asset_enqueue_group( TEC_Assets::$group_key );
		tribe_asset_enqueue_group( ECP_Assets::$group_key );

		if ( tribe_events_views_v2_is_enabled() ) {
			$content .= $view->get_html();
		}

		return $content;
	}


	/**
	 * Redirects a request to locate the Series post type template to the correct template path.
	 *
	 * @since 6.0.0
	 *
	 * @param string $template The original, suggested, template path as resolved by WordPress.
	 *
	 * @return string The absolute path to the Series singular template, if applicable, or the
	 *                original template path if not applicable.
	 */
	public function redirect_series_template( $template ) {
		$queried_object = get_queried_object();

		if ( ! (
			$queried_object instanceof WP_Post
			&& tribe( Series::class )->is_same_type( $queried_object )
		) ) {
			return $template;
		}

		add_filter( 'the_content', [ $this, 'inject_content' ] );

		// Just returning the template located by the theme is fine: we just need to inject the content.
		return $template;
	}

	/**
	 * Alter the container class, as the container is injected in the middle of the content we take advantage of the
	 * blocks editor class "alignwide", where a width limit is not applied to the container.
	 *
	 * @since 6.0.0
	 *
	 * @param $class_list
	 *
	 * @return mixed
	 */
	public function alter_container_classes( $class_list, $view_slug, View_Interface $view ) {
		$is_series_post_type = $view->get_context()->get( 'post_type' ) === Series::POSTTYPE;
		$related_with_series = is_numeric( $view->get_context()->get( 'related_series' ) );

		if ( ! ( $is_series_post_type || $related_with_series ) ) {
			return $class_list;
		}

		// Create a map for faster lookups of the valid view slugs.
		$valid_views = [
			Summary_View::get_view_slug() => true,
			Photo_View::get_view_slug()   => true,
			Week_View::get_view_slug()    => true,
			Map_View::get_view_slug()     => true,
			Month_View::get_view_slug()   => true,
			List_View::get_view_slug()    => true,
			Day_View::get_view_slug()     => true,
		];

		if ( empty( $valid_views[ $view_slug ] ) ) {
			return $class_list;
		}

		$class_list[] = 'alignwide';

		return $class_list;
	}

	/**
	 * Handles the redirects related to Series.
	 *
	 * @since 6.0.0
	 *
	 * @param string $redirect_url The redirect URL, as worked out by WordPress.
	 * @param string $requested_url The original request URL.
	 *
	 * @return string The modified URL, if required.
	 */
	public function redirect_series_requests( $redirect_url, $requested_url ) {
		if ( ! ( is_string( $redirect_url ) && is_string( $requested_url ) ) ) {
			return $redirect_url;
		}

		global $wp;
		$q      = $wp->query_vars;
		$series = Series::POSTTYPE;

		if ( isset( $q['paged'], $q['name'], $q['post_type'] ) && $q['post_type'] === $series ) {
			// /series/<name>/page/<n> request -- do not redirect.
			return $requested_url;
		}

		if (
			isset( $q['related_series'], $q['post_type'] )
			&& $q['post_type'] === $series
			&& count( (array) $q['related_series'] ) === 1
		) {
			$related_series = (int)$q['related_series'];

			// /events/<view>/?related_series=<series_id>
			$name = get_post_field( 'post_name', $q['related_series'] );
			if ( ! empty( $name ) ) {
				$page = (int) Arr::get_first_set( $q, [ 'page', 'paged' ], 1 );
				unset(
					$q['page'],
					$q['paged'],
					$q['post_type'],
					$q['tribe_event_series'],
					$q['name'],
					$q['related_series']
				);

				if ( isset( $q['eventDisplay'] ) && 'past' !== $q['eventDisplay'] ) {
					unset( $q['eventDisplay'] );
				}

				if ( $page < 2 ) {
					return add_query_arg( $q, get_permalink( $related_series ) );
				}

				return add_query_arg(
					$q,
					trailingslashit( get_permalink( $related_series ) ) . "page/{$page}/"
				);
			}
		}

		return $redirect_url;
	}

	/**
	 * Redirects requests for Event archives related to Series to the Series single page.
	 *
	 * @since 6.0.0
	 */
	public function redirect_to_single_series() {
		if ( ! is_post_type_archive( TEC::POSTTYPE ) ) {
			return;
		}

		$context        = tribe_context();
		$related_series = (int) $context->get( 'related_series', 0 );

		if ( $related_series <= 0 ) {
			return;
		}

		$series = get_post( $related_series );

		if ( ! tribe( Series::class )->is_same_type( $series ) ) {
			return;
		}

		$args                  = [];
		$event_display         = $context->get( 'view', Summary_View::get_view_slug() );
		$default_event_display = $this->get_filtered_view_slug( $event_display );

		if ( $event_display !== $default_event_display ) {
			$args['eventDisplay'] = $event_display;
		}

		$page = $context->get( 'page', 0 );
		if ( $page > 1 ) {
			$args['page'] = $page;
			// Attach the related series again so the `$this->redirect_series_requests()` handles the next part as expected.
			$args['related_series'] = $series->ID;
		}

		// Remove Tribe filter preventing to redirect past events.
		remove_filter( 'wp_redirect', [ tribe( Hooks::class ), 'filter_redirect_canonical' ] );

		wp_redirect( add_query_arg( $args, get_permalink( $series ) ) );

		die();
	}

	/**
	 * Filters and returns the slug of the  View that should be used, by default, to display the
	 * Events related to Series.
	 *
	 * @since 6.0.0
	 *
	 * @param string $view The slug of the View that should be used to display events related to
	 *                     a Series by default.
	 *
	 * @return string The slug of the View that should be used to display the Events related to a
	 *                Series.
	 */
	private function get_filtered_view_slug( $view ) {
		/**
		 * Filters the slug of the View, e.g. "list" or "week", that should be used to display
		 * the Events related to Series.
		 *
		 * @since 6.0.0
		 *
		 * @param string $view The default slug.
		 */
		return apply_filters( 'tec_events_pro_custom_tables_v1_series_event_view_slug', $view );
	}
}

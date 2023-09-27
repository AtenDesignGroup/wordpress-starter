<?php
/**
 * Handles the filters applied by this plugin to the Views.
 *
 * @since   4.7.5
 * @package Tribe\Events\Pro\Views\V2
 */

namespace Tribe\Events\Pro\Views\V2;

use Tribe\Events\Pro\Views\V2\Geo_Loc\Handler_Interface as Geo_Loc_Handler;
use Tribe\Events\Pro\Views\V2\Views\All_View;
use Tribe\Events\Pro\Views\V2\Views\Organizer_View;
use Tribe\Events\Pro\Views\V2\Views\Venue_View;
use Tribe\Events\Pro\Views\V2\Views\Week_View;
use Tribe\Events\Views\V2\Manager as Views_Manager;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\View_Interface;
use Tribe\Events\Views\V2\Manager;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe__Context as Context;
use Tribe__Events__Main as TEC;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Rewrite as TEC_Rewrite;
use Tribe__Events__Venue as Venue;
use WP_REST_Request as Request;

/**
 * Class View_Filters
 *
 * @since   4.7.5
 * @package Tribe\Events\Pro\Views\V2
 */
class View_Filters {

	/**
	 * The geo location handler.
	 *
	 * @since 4.7.9
	 *
	 * @var Geo_Loc_Handler
	 */
	protected $geo_loc_handler;

	public static $option_mobile_default = 'mobile_default_view';

	/**
	 * View_Filters constructor.
	 *
	 * @param Geo_Loc_Handler $geo_loc_handler A geo location handler.
	 */
	public function __construct( Geo_Loc_Handler $geo_loc_handler ) {
		$this->geo_loc_handler = $geo_loc_handler;
	}

	/**
	 * Filters the View repository args to apply the applicable filters provided by the plugin.
	 *
	 * @since 4.7.5
	 *
	 * @param array        $repository_args         The current repository args.
	 * @param Context|null $context                 An instance of the context the View is using or `null` to use the
	 *                                              global Context.
	 *
	 * @return array The filtered repository args.
	 */
	public function filter_repository_args( array $repository_args, Context $context = null ) {
		$context = null !== $context ? $context : tribe_context();
		/**
		 * @var Views_Manager $manager
		 */
		$manager = tribe( Views_Manager::class );

		$hide_subsequent_recurrences_default = tribe_is_truthy( tribe_get_option( 'hideSubsequentRecurrencesDefault', false ) );
		$hide_subsequent_recurrences         = (bool) $context->get( 'hide_subsequent_recurrences', false );

		// If in Recurring "All" Page or the Day View then always show all the recurring events.
		$view = $context->get( 'view' );

		if ( 'default' === $view ) {
			$default_view_class = $manager->get_default_view();
			$view               = $manager->get_view_slug_by_class( $default_view_class );
		}

		if (
			in_array(
				$view,
				[
					Month_View::get_view_slug(),
					Week_View::get_view_slug(),
					All_View::get_view_slug(),
				]
			)
		) {
			$repository_args['hide_subsequent_recurrences'] = false;
		} elseif ( $hide_subsequent_recurrences_default || $hide_subsequent_recurrences ) {
			$repository_args['hide_subsequent_recurrences'] = true;
		}

		$is_location_search = $context->is( 'geoloc_search' );
		if ( $is_location_search ) {
			$repository_args = (array) $this->geo_loc_handler->filter_repository_args( $repository_args, $context );
		}

		return $repository_args;
	}

	/**
	 * Adds the recurrence param to the ignored params on the page reset.
	 *
	 * @since 5.3.0
	 *
	 * @param array     $arguments Which arguments we are ignoring.
	 * @param View|null $view      Current view that we are filtering.
	 *
	 * @return array Array of params with the hide_subsequent_recurrences added.
	 */
	public function add_recurrence_hide_to_page_reset_ignored_params( array $arguments = [], View $view = null ) {
		$arguments[] = 'hide_subsequent_recurrences';

		return $arguments;
	}

	/**
	 * Filter the Rest Requests to point to the correct view when dealing with Venue and Organizer.
	 *
	 * @since  5.0.0
	 *
	 * @param array   $params  Params received on the Request.
	 * @param Request $request Full WP Rest Request instance.
	 *
	 * @return array            Params after view slug is setup.
	 */
	public function filter_rest_request_view_slug( array $params, Request $request ) {
		$post_types_map = [
			Organizer::POSTTYPE => Organizer_View::get_view_slug(),
			Venue::POSTTYPE     => Venue_View::get_view_slug(),
		];

		$intersect_params = array_intersect( array_keys( $params ), array_keys( $post_types_map ) );
		if ( ! count( $intersect_params ) ) {
			return $params;
		}

		$post_type = reset( $intersect_params );

		if ( empty( $post_types_map[ $post_type ] ) ) {
			return $params;
		}

		$params['eventDisplay'] = $post_types_map[ $post_type ];

		return $params;
	}

	/**
	 * Filters the View template variables before the HTML is generated to add the ones related to this plugin filters.
	 *
	 * @since 4.7.5
	 *
	 * @param array   $template_vars The View template variables.
	 * @param Context $context       The View current context.
	 *
	 * @return array The filtered template variables.
	 */
	public function filter_template_vars( array $template_vars, Context $context = null ) {
		$context = null !== $context ? $context : tribe_context();
		if ( empty( $template_vars['bar'] ) ) {
			$template_vars['bar'] = [];
		}

		$hide_subsequent_recurrences = tribe_is_truthy( $context->get( 'hide_subsequent_recurrences', false ) );
		if ( $hide_subsequent_recurrences ) {
			$template_vars['bar']['hide_recurring'] = true;
		}

		$location = $context->get( 'geoloc_search', false );
		if ( ! empty( $location ) ) {
			$template_vars['bar']['location'] = $location;
		}

		$template_vars['display_recurring_toggle'] = tribe_is_truthy( tribe_get_option( 'userToggleSubsequentRecurrences', false ) );

		// When inside of shortcode we need to make sure the correct settings apply.
		if ( $context->get( 'shortcode', false ) ) {
			if ( ! tribe_is_truthy( tribe_get_option( 'tribeEventsShortcodeBeforeHTML', false ) ) ) {
				$template_vars['before_events'] = '';
			}
			if ( ! tribe_is_truthy( tribe_get_option( 'tribeEventsShortcodeAfterHTML', false ) ) ) {
				$template_vars['after_events'] = '';
			}
		}

		return $template_vars;
	}

	/**
	 * Filters the View URL to add, or remove, URL query arguments managed by PRO.
	 *
	 * @since 4.7.9
	 *
	 * @param string         $url       The current View URL.
	 * @param bool           $canonical Whether to return the canonical (pretty) URL or not.
	 * @param View_Interface $view      The View instance that is currently rendering.
	 *
	 * @return string The filtered View URL.
	 */
	public function filter_view_url( $url, $canonical, View_Interface $view ) {
		$context = $view->get_context() ?: tribe_context();

		$search = $context->get( 'geoloc_search' );

		if ( empty( $search ) ) {
			$url = remove_query_arg( 'tribe-bar-location', $url );
		} else {
			$url = add_query_arg( [ 'tribe-bar-location' => $search ], $url );
		}

		$hide_subsequent_recurrences = tribe_is_truthy( $context->get( 'hide_subsequent_recurrences', false ) );
		if ( $hide_subsequent_recurrences ) {
			$url = add_query_arg( [ 'hide_subsequent_recurrences' => true ], $url );
		}

		return $url;
	}

	/**
	 * Get the class name for the default registered view.
	 *
	 * The use of the `wp_is_mobile` function is not about screen width, but about payloads and how "heavy" a page is.
	 * All the Views are responsive, what we want to achieve here is serving users a version of the View that is
	 * less "heavy" on mobile devices (limited CPU and connection capabilities).
	 * This allows users to, as an example, serve the Month View to desktop users and the day view to mobile users.
	 *
	 * @since  4.9.4
	 * @since 5.12.3 - Moved to ECP, where it belongs.
	 *
	 * @param string      $default_view The view slug for the default view.
	 * @param string|null $type         The type of default View to return, either 'desktop' or 'mobile'.
	 *
	 * @return string The default View slug.
	 *
	 * @see wp_is_mobile()
	 * @link https://developer.wordpress.org/reference/functions/wp_is_mobile/
	 */
	public function filter_tec_events_default_view( $default_view, $type ) {
		if ( null === $type ) {
			$type = wp_is_mobile() ? 'mobile' : 'desktop';
		}

		if ( 'desktop' === $type ) {
			return $default_view;
		}

		return (string) tribe_get_option( self::$option_mobile_default, 'default' );
	}

	/**
	 * Redirects the user to the default mobile view if required.
	 *
	 * When on mobile (in terms of device capacity) we redirect to the default mobile View.
	 * To avoid caching issues, where the cache provider would need to keep a mobile and non-mobile version of the
	 * cached pages, we redirect with explicit View slug.
	 *
	 * @link  https://developer.wordpress.org/reference/functions/wp_is_mobile/
	 * @see   wp_is_mobile()
	 * @since 4.7.10
	 *
	 */
	public function on_template_redirect() {
		// This method will not set the `tribe_redirect` query arg in the URL, Event Tickets will use it.
		if (
			! wp_is_mobile()
			|| tribe_is_truthy( tribe_get_request_var( 'tribe_redirected' ) )
			|| is_singular()
			|| ! is_tax( [ TEC::TAXONOMY, 'post_tag' ] )
			|| 'all' === tribe_context()->get( 'view' )
			|| 'embed' === tribe_context()->get( 'view' )
			|| is_front_page()
		) {
			// The view does not require mobile redirection.
			return;
		}

		$context = tribe_context();

		if ( ! $context->get( 'tec_post_type' ) ) {
			return;
		}

		// Make sure users can actually go to the specific views if intentional.
		if ( 'default' !== $context->get( 'view' ) ) {
			return;
		}

		/**
		 * @var Manager $manager
		 */
		$manager = tribe( Manager::class );

		$default_view        = $manager->get_default_view_option( 'desktop' );
		$default_mobile_view = tribe_get_option( 'mobile_default_view', 'default' );

		// Only redirect if the mobile view doesn't resolve to the same place or default view (also the same place) already.
		if ( $default_view === $default_mobile_view || $default_mobile_view === 'default' ) {
			return;
		}

		global $wp;

		$url = home_url( '/' );

		// Add the base WordPress Url Query arguments.
		$url = add_query_arg( $wp->query_vars, $url );

		/*
		 * Logic following this code in the request lifecycle will know we're redirecting.
		 */
		add_filter( 'tec_events_views_v2_redirected', '__return_true' );

		// Add our mobile default to the arguments.
		$url = add_query_arg( [ 'eventDisplay' => $default_mobile_view, ], $url );

		$location = TEC_Rewrite::instance()->get_canonical_url( $url );

		wp_redirect( $location, 302 );

		tribe_exit();
	}

	/**
	 * Filters the slug of the view that will be built according to the request context to add support for Venue and
	 * Organizer Views.
	 *
	 * @since    4.7.9
	 *
	 * @param string  $slug    The View slug that would be loaded.
	 * @param Context $context The current request context.
	 *
	 * @return string The filtered View slug, set to the Venue or Organizer ones, if required.
	 *
	 * @internal This method is not meant be used outside of the plugin.
	 */
	public function filter_bootstrap_view_slug( string $slug, Context $context ): string {
		$post_types         = [
			Organizer::POSTTYPE => 'organizer',
			Venue::POSTTYPE     => 'venue',
		];
		$context_post_types = (array) $context->get( 'post_type', $slug );

		if ( empty( $context_post_types ) || count( $context_post_types ) > 1 )  {
			// Either a multiple post type request or not a request for the Venue or Organizer post types.
			return $slug;
		}

		$post_type = reset( $context_post_types );

		return $post_types[ $post_type ] ?? $slug;
	}
}

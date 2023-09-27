<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Events\Pro\Views\V2\Shortcodes\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'pro.views.v2.shortcodes.hooks' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Events\Pro\Views\V2\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'pro.views.v2.shortcodes.hooks' ), 'some_method' ] );
 *
 * @since 4.7.5
 *
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */

namespace Tribe\Events\Pro\Views\V2\Shortcodes;

use Tribe\Events\Pro\Views\V2\Assets as Pro_Assets;
use Tribe\Shortcode\Manager;
use WP_REST_Request as Request;
use Tribe__Events__Main as TEC;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Hooks.
 *
 * @since 4.7.5
 *
 * @package Tribe\Events\Pro\Views\V2
 */
class Hooks extends Service_Provider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.7.5
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Add actions for all the shortcode related stuff.
	 *
	 * @since 5.5.0
	 */
	public function add_actions() {
		add_action( 'init', [ $this, 'action_add_shortcodes' ], 20 );
		add_action( 'tribe_events_pro_shortcode_tribe_events_after_assets', [ $this, 'action_disable_shortcode_assets_v1' ] );
		add_action( 'tribe_events_views_v2_before_make_view_for_rest', [ $this, 'action_shortcode_toggle_hooks' ], 10, 3 );
	}

	/**
	 * Add filters for all the shortcode related stuff.
	 *
	 * @since 5.5.0
	 */
	public function add_filters() {
		add_filter( 'tribe_shortcodes', [ $this, 'filter_tribe_shortcodes' ] );
		add_filter( 'tribe_context_locations', [ $this, 'filter_context_locations' ] );
		add_filter( 'tec_views_v2_subscribe_links_url_args', [ $this, 'filter_tec_views_v2_subscribe_links_url_args' ], 10, 2 );
	}

	/**
	 * Adds the new shortcodes, this normally will trigger on `init@P20` due to how we the
	 * v1 is added on `init@P10` and we remove them on `init@P15`.
	 *
	 * It's important to leave gaps on priority for better injection.
	 *
	 * @since 4.7.5
	 */
	public function action_add_shortcodes() {
		$this->container->make( Manager::class )->add_shortcodes();
	}

	/**
	 * Add shortcodes for Pro.
	 *
	 * @since 5.5.0
	 *
	 * @param array $shortcodes List of previous shortcodes.
	 *
	 * @return array The modified shortcodes array.
	 */
	public function filter_tribe_shortcodes( $shortcodes ) {
		$shortcodes['tribe_events']          = Tribe_Events::class;
		$shortcodes['tribe_events_list']     = Shortcode_Tribe_Events_List::class;
		$shortcodes['tribe_this_week']       = Shortcode_Tribe_Week::class;
		$shortcodes['tribe_mini_calendar']   = Shortcode_Tribe_Mini_Calendar::class;
		$shortcodes['tribe_event_countdown'] = Shortcode_Tribe_Event_Countdown::class;
		$shortcodes['tribe_featured_venue']  = Shortcode_Tribe_Featured_Venue::class;

		return $shortcodes;
	}

	/**
	 * Filters the context locations to add the ones used by The Events Calendar PRO for Shortcodes.
	 *
	 * @since 4.7.9
	 * @since 5.5.0 Moved this from Tribe\Events\Pro\Views\V2\Hooks.
	 *
	 * @param array $locations The array of context locations.
	 *
	 * @return array The modified context locations.
	 */
	public function filter_context_locations( array $locations = [] ) {
		return Tribe_Events::filter_context_locations( $locations );
	}

	/**
	 * Fires to deregister v1 assets correctly for shortcodes.
	 *
	 * @since 4.7.9
	 * @since 5.5.0 Moved this from Tribe\Events\Pro\Views\V2\Hooks.
	 *
	 * @return  void
	 */
	public function action_disable_shortcode_assets_v1() {
		$this->container->make( Pro_Assets::class )->disable_v1();
	}


	/**
	 * Possibly loads all the shortcode hooks.
	 *
	 * @since 5.5.0
	 *
	 * @param  string    $slug    The current view Slug.
	 * @param  array     $params  Params so far that will be used to build this view.
	 * @param  Request   $request The rest request that generated this call.
	 */
	public function action_shortcode_toggle_hooks( $slug, $params, Request $request ) {
		Tribe_Events::maybe_toggle_hooks_for_rest( $slug, $params, $request );
	}

	/**
	 * Filter the iCal link args to allow shortcodes
	 * to pass through params that would be "hidden" in their params otherwise.
	 *
	 * @since 5.11.1
	 *
	 * @param array<string|mixed> $args The array of args (params) that will be added to the URL.
	 * @param View_Interface      $view The view instance.
	 * @return void
	 */
	public function filter_tec_views_v2_subscribe_links_url_args( $args, $view ) {
		$view_url_args = $view->get_url_args();

		// Shortcode is stripped out of the passthrough args already - it doesn't belong in the subscribe URL.
		if ( empty( $view_url_args['shortcode'] ) ) {
			return $args;
		}

		$database_args = tribe( Tribe_Events::class )->get_database_arguments( $view_url_args['shortcode'] );

		if (
			empty( $database_args['category'] )
			&& empty( $database_args['tag'] )
		) {
			return $args;
		}

		// `tribe_events` allows multiple tags/categories, let's pass them all and let the repository handle it.

		// If we have category args, add them.
		if ( ! empty( $database_args['category'] ) ) {
			$cats = [];

			foreach( $database_args['category'] as $cat_id ) {
				$cats[] = get_term( $cat_id )->slug;
			}

			// Note: WP allows us to use `,` (OR) or `+` (AND) for a separator -
			// but our current rewrite rules break using `+` so we'll stick to `,` for now.
			$args[TEC::TAXONOMY] = implode( ',', $cats );
		}

		// If we have tag args, add them.
		if ( ! empty( $database_args['tag'] ) ) {
			$tags = [];

			foreach( $database_args['tag'] as $tag_id ) {
				$tags[] = get_term( $tag_id )->slug;
			}

			$args['post_tag'] = implode( ',', $tags );
		}

		return $args;
	}
}

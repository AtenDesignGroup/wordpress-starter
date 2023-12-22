<?php
/**
 * Handles the registration and hooking of template redirects.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Templates
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Templates;


use TEC\Events_Pro\Custom_Tables\V1\Templates\Templates as Templates_Loader;
use Tribe\Events\Views\V2\Template_Bootstrap;
use Tribe__Events__Main as TEC;
use Tribe__Events__Pro__Main as Plugin;
use TEC\Common\Contracts\Service_Provider;


/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Templates
 */
class Provider extends Service_Provider {


	/**
	 * Key for the event single group of assets.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public static $event_single_group_key = 'tec-custom-tables-v1-templates-event-single';

	/**
	 * Caches the result of the `is_event_single` check.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $is_event_single;

	public $template_series_link_hooks = [
		// TEC
		'events/v2/day/event/title',
		'events/v2/day/event/featured-image',
		'events/v2/latest-past/event/title',
		'events/v2/latest-past/event/featured-image',
		'events/v2/list/event/title',
		'events/v2/list/event/featured-image',
		'events/v2/month/calendar-body/day/calendar-events/calendar-event/title',
		'events/v2/month/calendar-body/day/calendar-events/calendar-event/featured-image',
		'events/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/title',
		'events/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/featured-image',
		'events/v2/month/calendar-body/day/multiday-events/multiday-event/hidden/title',
		'events/v2/month/mobile-events/mobile-day/mobile-event/title',
		// TEC widgets/shortcodes
		'events/v2/widgets/widget-events-list/event/title',
		// ECP
		'events-pro/v2/map/event-cards/event-card/actions/details',
		'events-pro/v2/map/event-cards/event-card/event/featured-image',
		'events-pro/v2/map/event-cards/event-card/tooltip/title',
		'events-pro/v2/map/map/no-venue-modal',
		'events-pro/v2/photo/event/title',
		'events-pro/v2/photo/event/featured-image',
		'events-pro/v2/summary/date-group/event/title',
		'events-pro/v2/week/grid-body/events-day/event',
		'events-pro/v2/week/grid-body/events-day/event/tooltip/title',
		'events-pro/v2/week/grid-body/events-day/event/tooltip/featured-image',
		'events-pro/v2/week/grid-body/multiday-events-day/multiday-event/hidden/link',
		'events-pro/v2/week/mobile-events/day/event/title',
		'events-pro/v2/week/mobile-events/day/event/featured-image',
		// ECP widgets/shortcodes
		'events-pro/v2/widgets/widget-countdown/event-title',
		'events-pro/v2/widgets/widget-featured-venue/events-list/event/title',
	];

	/**
	 * Registers the bindings and hooks required by the plugin template redirection.
	 *
	 * @since 6.0.0
	 *
	 */
	public function register() {
		$this->container->singleton( Series_Filters::class, Series_Filters::class );
		$this->container->singleton( Templates_Loader::class, Templates_Loader::class );
		$this->container->singleton( Single_Event_Modifications::class, Single_Event_Modifications::class );

		$this->register_assets();

		$filters = function ( $method ) {
			return $this->container->callback( Series_Filters::class, $method );
		};

		add_filter(
			'tribe_events_single_meta_details_section_after_datetime',
			$this->container->callback( Single_Event_Modifications::class, 'include_series_meta_details' )
		);

		add_filter( 'query_vars', $filters( 'filter_query_vars' ) );
		add_filter( 'tec_events_views_v2_view_global_repository_args', $filters( 'filter_repository_args' ), 10, 2 );
		add_action( 'tribe_views_v2_after_setup_loop', $filters( 'replace_view_url_object' ) );
		add_filter( 'tribe_events_views_v2_url_query_args', $filters( 'filter_query_args' ), 10, 2 );

		add_filter( 'tribe_context_locations', $filters( 'update_tribe_context' ), 10, 2 );

		add_filter(
			'get_the_terms',
			$this->container->callback( Single_Event_Modifications::class, 'redirect_get_the_terms' ),
			10,
			3
		);

		add_filter(
			'get_terms',
			$this->container->callback( Single_Event_Modifications::class, 'redirect_get_terms' ),
			10,
			3
		);

		$this->hook_series_markers();

		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			// Only register this on front-end of PHP initial state.
			return;
		}

		// We're in PHP initial state land here.

		add_filter( 'template_include', $filters( 'redirect_series_template' ) );
		add_filter( 'redirect_canonical', $filters( 'redirect_series_requests' ), 10, 2 );
		add_action( 'template_redirect', $filters( 'redirect_to_single_series' ) );
		add_filter( 'tribe_events_views_v2_view_html_classes', $filters( 'alter_container_classes' ), 10, 3 );

		/**
		 * Allows redirecting the event links for events in a series so that they point to the series,
		 * rather than the event.
		 *
		 * @since 6.0.0
		 *
		 * @param boolean $filter_event_url Default false. Whether we redirect event links to the associated series.
		 */
		$filter_event_url = apply_filters( 'tec_events_pro_custom_tables_v1_redirect_event_link_to_series', false );
		if ( $filter_event_url ) {
			foreach( $this->template_series_link_hooks as $hook_name ) {
				add_filter( "tribe_template_include_html:{$hook_name}", [ $this, 'redirect_event_link_to_series' ], 10, 4 );
			}
		}
	}

	/**
	 * Registers the assets required by the service provider.
	 *
	 * @since 6.0.0
	 */
	public function register_assets() {
		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			'tec-events-pro-single-style',
			'custom-tables-v1/single.css',
			[],
			'wp_enqueue_scripts',
			[
				'priority'     => 200,
				'conditionals' => [ $this, 'is_event_single' ],
				'groups'       => [ static::$event_single_group_key ],
			]
		);
	}

	/**
	 * Determine whether we are on the event single page or not.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean
	 */
	public function is_event_single() {
		if ( null !== $this->is_event_single ) {
			return $this->is_event_single;
		}

		if ( ! class_exists( Template_Bootstrap::class ) ) {
			return false;
		}

		$is_event_single = tribe( Template_Bootstrap::class )->is_single_event();

		$is_event_single = apply_filters( 'tec_events_pro_custom_tables_v1_template_assets_is_event_single', $is_event_single );

		$this->is_event_single = $is_event_single;

		return $is_event_single;
	}

	/**
	 * Adds the Series relationship markers where required.
	 *
	 * @since 6.0.0
	 */
	private function hook_series_markers() {
		// Event single - Classic & Block
		add_filter( 'tribe_the_notices', [ $this, 'add_single_series_text_marker' ], 15, 2 );

		// Event single - Classic
		add_filter( 'tribe_events_event_schedule_details', [ $this, 'remove_recurring_info_tooltip' ], 5, 2 );
		add_filter( 'tribe_events_event_schedule_details', [ $this, 'add_single_series_pill_marker' ], 15, 2 );
		add_filter( 'tribe_events_event_schedule_details', [ $this, 'add_related_events_series_icon' ], 15, 2 );

		// Event single - Block
		add_action(
			'tribe_template_before_include:events/single-event-blocks',
			[ $this, 'remove_recurring_marker' ]
		);
		add_action(
			'tribe_template_before_include:events/blocks/event-datetime',
			[ $this, 'add_block_single_series_pill_marker' ],
			5
		);
	}

	/**
	 * Prints the Series relationship marker to the page, if required.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function add_block_single_series_pill_marker() {
		if ( ! is_singular( TEC::POSTTYPE ) ) {
			return;
		}

		echo $this->container->make( Single_Event_Modifications::class )
		                     ->get_series_relationship_pill_marker( get_the_ID() );
	}

	/**
	 * Appends the Series relationship marker to the input HTML code, if required.
	 *
	 * @since 6.0.0
	 *
	 * @param string $html    The HTML code to append the marker to.
	 *
	 * @return string The HTML with the marker HTML appended to it, if required.
	 */
	public function add_single_series_text_marker( $html ) {
		if ( ! is_singular( TEC::POSTTYPE ) ) {
			return $html;
		}

		$series_text_marker = $this->container->make( Single_Event_Modifications::class )
		                                      ->get_series_relationship_text_marker( get_the_ID() );

		return $html . $series_text_marker;
	}

	/**
	 * Appends the Series relationship marker, in the pill form, to the input HTML code, if required.
	 *
	 * @since 6.0.0
	 *
	 * @param string $html    The HTML code to append the marker to.
	 * @param int    $post_id The post ID of the Event to print the marker for, if required.
	 *
	 * @return string The HTML with the marker HTML appended to it, if required.
	 */
	public function add_single_series_pill_marker( $html, $post_id ) {
		if ( ! is_singular( TEC::POSTTYPE ) ) {
			return $html;
		}

		// Only show marker on current event. This prevents showing the marker on related events.
		if ( get_the_ID() !== $post_id ) {
			return $html;
		}

		$series_pill_marker = $this->container->make( Single_Event_Modifications::class )
		                                      ->get_series_relationship_pill_marker( $post_id );

		return $html . $series_pill_marker;
	}

	/**
	 * Appends the Series icon to the input HTML code, if required.
	 *
	 * @since 6.0.0
	 *
	 * @param string $html    The HTML code to append the marker to.
	 * @param int    $post_id The post ID of the Event to print the marker for, if required.
	 *
	 * @return string The HTML with the marker HTML appended to it, if required.
	 */
	public function add_related_events_series_icon( $html, $post_id ) {
		if ( ! is_singular( TEC::POSTTYPE ) ) {
			return $html;
		}

		// Only show marker on related events.
		if ( get_the_ID() === $post_id ) {
			return $html;
		}

		$series_icon = $this->container->make( Single_Event_Modifications::class )
		                               ->get_series_relationship_icon( $post_id );

		return $html . $series_icon;
	}

	/**
	 * Removes the recurring info tooltip from the classic event single.
	 *
	 * @since 6.0.0
	 *
	 * @param string $html    The HTML code for the notice.
	 * @param int    $post_id Post ID of the event.
	 *
	 * @return string HTML string for tooltip.
	 */
	public function remove_recurring_info_tooltip( $html, $post_id ) {
		if ( ! is_singular( TEC::POSTTYPE ) ) {
			return $html;
		}

		$this->container->make( Single_Event_Modifications::class )
		                ->do_not_render_recurring_info_tooltip( $post_id );

		return $html;
	}

	/**
	 * Removes the recurring marker from the block event single.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function remove_recurring_marker() {
		if ( ! is_singular( TEC::POSTTYPE ) ) {
			return;
		}

		$this->container->make( Single_Event_Modifications::class )->do_not_render_recurring_marker();
	}

	/**
	 * Filters the permalink to events, changing the URL to point to the series if the event is in one.
	 *
	 *
	 * @param string $html        The HTML string.
	 * @param string $unused_file Complete path to include the PHP File.
	 * @param array  $unused_name Template name.
	 * @param self   $template    Current instance of the Tribe__Template.
	 *
	 * @return string $html      The final HTML.
	 */
	public function redirect_event_link_to_series( $html, $unused_file, $unused_name, $template ) {
		// Be smart.
		if ( empty( $html ) ) {
			return $html;
		}

		$event = $template->get( 'event' );

		if ( empty( $event ) ) {
			return $html;
		}

		// There's no point in linking to the page we're on (Series view)?
		$param = get_query_var( 'related_series' );

		if ( ! empty( $param ) ) {
			return $html;
		}

		$event_id = tribe( Single_Event_Modifications::class )->normalize_post_id( $event->ID );

		$series = tec_event_series( $event_id );

		// Bail if the event is not in a series.
		if ( empty( $series ) ) {
			return $html;
		}

		$series_url = get_the_permalink( $series );

		$pattern = "/(?<=href=\")(\S*)(?=\")/";
		$html = preg_replace( $pattern, $series_url, $html );

		return $html;
	}
}

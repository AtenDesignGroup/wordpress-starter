<?php

namespace Tribe\Events\Pro\Admin\Manager;

use Tribe\Events\Views\V2\Template;
use Tribe\Events\Views\V2\View_Interface;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe__Events__Main as TEC;
use Tribe__Template;
use Tribe__Context as Context;
use Tribe__Utils__Array as Arr;
use WP_Post;

/**
 * Class Page
 *
 * @since   5.9.0
 *
 * @package Tribe\Events\Pro\Admin\Manager
 */
class Shortcode {

	/**
	 * Base shortcode for the admin manager.
	 *
	 * @since 5.9.0
	 *
	 * @var string
	 */
	protected $shortcode_id = 'admin-manager';

	/**
	 * Gets the shortcode tag used for rendering.
	 *
	 * @since 5.9.0
	 *
	 * @return string The Shortcode registered tag.
	 */
	public function get_shortcode_id() {
		return $this->shortcode_id;
	}


	/**
	 * Gets the shortcode tag used for rendering.
	 *
	 * @since 5.9.0
	 *
	 * @return string The Shortcode registered tag.
	 */
	public function get_shortcode_string() {
		$args              = [
			'id'                   => $this->get_shortcode_id(),
			'view'                 => Month_View::get_view_slug(),
			'month_events_per_day' => '20',
			'hide-export'          => 'yes',
			'tribe-bar'            => 'yes',
			'filter-bar'           => 'yes',
		];
		$attributes_string = \Tribe\Shortcode\Utils::get_attributes_string( $args );

		return "[tribe_events {$attributes_string}]";
	}

	/**
	 * Toggles the filtering the things related to modifying the views for administration mode.
	 *
	 * @since  5.9.0
	 *
	 * @param bool $toggle Whether to turn the hooks on or off.
	 *
	 * @return void
	 */
	public function toggle_shortcode_hooks( $toggle ) {
		if ( $toggle ) {
			$this->add_shortcode_hooks();
		} else {
			$this->remove_shortcode_hooks();
		}
	}

	/**
	 * Add the shortcode hooks to the calendar manager.
	 *
	 * @since 5.9.0
	 */
	public function add_shortcode_hooks() {
		add_filter( 'tribe_events_pro_shortcode_compatibility_required', '__return_false' );
		add_filter( 'tribe_events_views_v2_should_cache_html', '__return_false' );
		add_action( 'tribe_template_after_include:events/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/cost', [
			$this,
			'include_row_actions'
		], 15, 3 );
		add_action( 'tribe_template_after_include:tickets/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/cost', [
			$this,
			'include_row_actions'
		], 15, 3 );
		add_action( 'tribe_template_after_include:events/v2/list/event/cost', [ $this, 'include_row_actions' ], 15, 3 );
		add_action( 'tribe_template_after_include:events/v2/day/event/cost', [ $this, 'include_row_actions' ], 15, 3 );
		add_action( 'tribe_template_after_include:tickets/v2/list/event/cost', [ $this, 'include_row_actions' ], 15, 3 );
		add_action( 'tribe_template_after_include:tickets/v2/day/event/cost', [ $this, 'include_row_actions' ], 15, 3 );

		add_action( 'tribe_template_entry_point:events/v2/month/calendar-body/day/cell-title:before_container_close', [
			$this,
			'include_new_event_link'
		], 15, 3 );

		add_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_include_event_status' ], 15, 3 );
		add_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_include_hidden_from_upcoming' ], 15, 3 );
		add_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_maybe_restrict_to_tickets' ], 15, 3 );
		add_filter( 'tribe_events_views_v2_view_public_views', [ $this, 'filter_modify_public_views' ], 15 );
		add_filter( 'tribe_get_event', [ $this, 'filter_event_object' ] );
		add_filter( 'the_title', [ $this, 'filter_post_status_into_event_title' ], 10, 2 );
		add_filter( 'tribe_events_views_v2_ff_next_event_pre', [ $this, 'filter_fast_forward_event' ], 10, 2 );
		add_filter( 'tribe_events_views_v2_view_url', [ $this, 'filter_url_to_include_post_status' ] );
		add_filter( 'tribe_events_views_v2_view_next_url', [ $this, 'filter_url_to_include_post_status' ] );
		add_filter( 'tribe_events_views_v2_view_prev_url', [ $this, 'filter_url_to_include_post_status' ] );
		add_filter( 'tribe_events_views_v2_view_url', [ $this, 'filter_url_to_include_tribe_has_tickets' ] );
		add_filter( 'tribe_events_views_v2_view_next_url', [ $this, 'filter_url_to_include_tribe_has_tickets' ] );
		add_filter( 'tribe_events_views_v2_view_prev_url', [ $this, 'filter_url_to_include_tribe_has_tickets' ] );
		add_filter( 'tribe_events_views_v2_url_query_args', [ $this, 'filter_query_args_to_include_post_status' ] );
		add_filter( 'tribe_events_views_v2_url_query_args', [ $this, 'filter_query_args_to_include_tribe_has_tickets' ] );
		add_filter( 'tribe_get_option', [ $this, 'filter_earliest_date_option' ], 10, 2 );
		add_filter( 'tribe_get_option', [ $this, 'filter_latest_date_option' ], 10, 2 );
	}

	/**
	 * Remove the shortcode hooks for the calendar manager.
	 *
	 * @since 5.9.0
	 */
	public function remove_shortcode_hooks() {
		remove_filter( 'tribe_events_pro_shortcode_compatibility_required', '__return_false' );
		remove_filter( 'tribe_events_views_v2_should_cache_html', '__return_false' );
		remove_filter( 'tribe_template_after_include:events/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/cost', [
			$this,
			'include_row_actions'
		], 15 );
		remove_filter( 'tribe_template_after_include:tickets/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/cost', [
			$this,
			'include_row_actions'
		], 15 );
		remove_filter( 'tribe_template_after_include:events/v2/list/event/title', [ $this, 'include_row_actions' ], 15 );
		remove_filter( 'tribe_template_after_include:events/v2/day/event/title', [ $this, 'include_row_actions' ], 15 );
		remove_action( 'tribe_template_after_include:tickets/v2/list/event/cost', [ $this, 'include_row_actions' ], 15, 3 );
		remove_action( 'tribe_template_after_include:tickets/v2/day/event/cost', [ $this, 'include_row_actions' ], 15, 3 );

		remove_filter( 'tribe_template_entry_point:events/v2/month/calendar-body/day/cell-title:before_container_close', [
			$this,
			'include_new_event_link'
		], 15 );

		remove_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_include_event_status' ], 15 );
		remove_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_include_hidden_from_upcoming' ], 15 );
		remove_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_maybe_restrict_to_tickets' ], 15, 3 );
		remove_filter( 'tribe_events_views_v2_view_public_views', [ $this, 'filter_modify_public_views' ], 15 );
		remove_filter( 'tribe_get_event', [ $this, 'filter_event_object' ] );
		remove_filter( 'the_title', [ $this, 'filter_post_status_into_event_title' ], 10, 2 );
		remove_filter( 'tribe_events_views_v2_ff_next_event_pre', [ $this, 'filter_fast_forward_event' ], 10, 2 );
		remove_filter( 'tribe_events_views_v2_view_url', [ $this, 'filter_url_to_include_post_status' ] );
		remove_filter( 'tribe_events_views_v2_view_next_url', [ $this, 'filter_url_to_include_post_status' ] );
		remove_filter( 'tribe_events_views_v2_view_prev_url', [ $this, 'filter_url_to_include_post_status' ] );
		remove_filter( 'tribe_events_views_v2_view_url', [ $this, 'filter_url_to_include_tribe_has_tickets' ] );
		remove_filter( 'tribe_events_views_v2_view_next_url', [ $this, 'filter_url_to_include_tribe_has_tickets' ] );
		remove_filter( 'tribe_events_views_v2_view_prev_url', [ $this, 'filter_url_to_include_tribe_has_tickets' ] );
		remove_filter( 'tribe_events_views_v2_url_query_args', [ $this, 'filter_query_args_to_include_post_status' ] );
		remove_filter( 'tribe_events_views_v2_url_query_args', [ $this, 'filter_query_args_to_include_tribe_has_tickets' ] );
		remove_filter( 'tribe_get_option', [ $this, 'filter_earliest_date_option' ], 10, 2 );
		remove_filter( 'tribe_get_option', [ $this, 'filter_latest_date_option' ], 10, 2 );
	}

	/**
	 * Filters the repository args to include all post status.
	 *
	 * @since 5.9.0
	 *
	 * @param array          $repository_args An array of repository arguments that will be set for all Views.
	 * @param Context        $context         The current render context object.
	 * @param View_Interface $repository      The View that will use the repository arguments.
	 *
	 * @return array Repository arguments after modifying the Post Status.
	 */
	public function filter_include_event_status( $repository_args, Context $context, View_Interface $repository ) {
		$post_stati = tribe( Page::class )->get_implicitly_requested_post_stati();

		$repository_args['post_status'] = $post_stati;

		return $repository_args;
	}

	/**
	 * Filters the repository args to include all events that were hidden using the Hide From Upcoming meta setting.
	 *
	 * @since 4.15.1
	 *
	 * @param array          $repository_args An array of repository arguments that will be set for all Views.
	 * @param Context        $context         The current render context object.
	 * @param View_Interface $repository      The View that will use the repository arguments.
	 *
	 * @return array Repository arguments after modifying the hidden from upcoming.
	 */
	public function filter_include_hidden_from_upcoming( $repository_args, Context $context, View_Interface $repository ) {
		if ( ! isset( $repository_args['hidden_from_upcoming'] ) ) {
			return $repository_args;
		}

		// Removing means all events will show regardless of status.
		unset( $repository_args['hidden_from_upcoming'] );

		return $repository_args;
	}

	/**
	 * Filters the repository args to include only events with/without tickets.
	 *
	 * @since 5.9.0
	 *
	 * @param array          $repository_args An array of repository arguments that will be set for all Views.
	 *
	 * @return array Repository arguments after modifying the Post Status.
	 */
	public function filter_maybe_restrict_to_tickets( $repository_args ) {
		$has_tickets = tribe( Page::class )->get_requested_tribe_has_tickets();

		if ( null === $has_tickets ) {
			return $repository_args;
		}

		$repository_args['has_rsvp_or_tickets'] = (bool) $has_tickets;

		return $repository_args;
	}

	/**
	 * Include the create the new event link.
	 *
	 * @since 5.9.0
	 *
	 * @param string          $hook_name
	 * @param string          $entry_point_name
	 * @param Tribe__Template $template
	 *
	 */
	public function include_new_event_link( $hook_name, $entry_point_name, Tribe__Template $template ) {
		/* @var Page $page */
		$page = tribe( Page::class );

		$local_vars    = [];
		$template_vars = array_merge( $local_vars, $template->get_values() );
		$page->get_template()->template( 'manager/create-new-event', $template_vars );
	}

	/**
	 * Modify the event object to change the permalinks.
	 *
	 * @since 5.9.0
	 * @since 5.12.1 Temporarily remove this from the 'tribe_get_event' filter to prevent infinite loops.
	 *
	 * @param \WP_Post $event Current event being filtered.
	 *
	 * @return mixed
	 */
	public function filter_event_object( $event ) {
		// Prevent infinite loops when post gets built.
		remove_filter( 'tribe_get_event', [ $this, 'filter_event_object' ] );
		$event->permalink = get_edit_post_link( $event->ID );

		if ( 0 !== (int) $event->post_parent ) {
			$event->permalink_all = get_edit_post_link( $event->post_parent );
		}

		// Make sure we add it back now!
		add_filter( 'tribe_get_event', [ $this, 'filter_event_object' ] );

		return $event;
	}

	/**
	 * Maybe toggles the hooks for this shortcode class on a rest request.
	 *
	 * @since 5.9.0
	 *
	 * @param string           $slug    The current view Slug.
	 * @param array            $params  Params so far that will be used to build this view.
	 * @param \WP_REST_Request $request The rest request that generated this call.
	 *
	 */
	public function maybe_toggle_hooks_for_rest( $slug, $params, \WP_REST_Request $request ) {
		$shortcode_id = Arr::get( $params, 'shortcode', false );

		// Bail when not a shortcode request.
		if ( ! $shortcode_id ) {
			return;
		}

		if ( $shortcode_id !== $this->get_shortcode_id() ) {
			return;
		}

		add_action( 'tribe_events_pro_shortcode_toggle_view_hooks', [ $this, 'toggle_shortcode_hooks' ] );
	}

	/**
	 * Modifies the public views to only allow
	 *
	 * @since 5.9.0
	 *
	 * @param array $views Views publicly visible.
	 *
	 * @return array Modified list of public views.
	 */
	public function filter_modify_public_views( array $views = [] ) {
		$allowed_views = [
			Day_View::get_view_slug(),
			List_View::get_view_slug(),
			Month_View::get_view_slug(),
		];
		$new_views     = [];

		foreach ( $views as $view_slug => $view_obj ) {
			if ( ! in_array( $view_slug, $allowed_views ) ) {
				continue;
			}

			$new_views[ $view_slug ] = $view_obj;
		}

		return $new_views;
	}

	/**
	 * Echoes the row actions for Calendar Manager events.
	 *
	 * @since 5.9.0
	 *
	 * @param string   $file     Which file is being included.
	 * @param string   $name     Name of the file.
	 * @param Template $template Template including the file.
	 *
	 */
	public function include_row_actions( $file, $name, $template ) {
		$event = $template->get( 'event' );

		if ( ! $event ) {
			return;
		}

		echo tribe( Events_Table::class )->get_row_actions( $event->ID );
	}

	/**
	 * Filters a URL to include a post_status query argument.
	 *
	 * @since 5.9.0
	 *
	 * @param string $url The URL to manipulate.
	 *
	 * @return string
	 */
	public function filter_url_to_include_post_status( $url ) {
		if ( $post_status = tribe( Page::class )->get_requested_post_status() ) {
			$url = add_query_arg( 'post_status', $post_status, $url );
		}

		return $url;
	}

	/**
	 * Filters a URL to include a tribe-has-tickets query argument.
	 *
	 * @since 5.9.0
	 *
	 * @param string $url The URL to manipulate.
	 *
	 * @return string
	 */
	public function filter_url_to_include_tribe_has_tickets( $url ) {
		if ( $has_tickets = tribe( Page::class )->get_requested_tribe_has_tickets() ) {
			$url = add_query_arg( 'tribe-has-tickets', $has_tickets, $url );
		}

		return $url;
	}

	/**
	 * Filters query args to include a post_status query argument.
	 *
	 * @since 5.9.0
	 *
	 * @param array $query_args The query args to manipulate.
	 *
	 * @return string
	 */
	public function filter_query_args_to_include_post_status( $query_args ) {
		if ( $post_status = tribe( Page::class )->get_requested_post_status() ) {
			$query_args['post_status'] = $post_status;
		}

		return $query_args;
	}

	/**
	 * Filters query args to include a tribe-has-tickets query argument.
	 *
	 * @since 5.9.0
	 *
	 * @param array $query_args The query args to manipulate.
	 *
	 * @return string
	 */
	public function filter_query_args_to_include_tribe_has_tickets( $query_args ) {
		if ( $has_tickets = tribe( Page::class )->get_requested_tribe_has_tickets() ) {
			$query_args['tribe-has-tickets'] = $has_tickets;
		}

		return $query_args;
	}

	/**
	 * Filters the Fast Forward event.
	 *
	 * @since 5.9.0
	 *
	 * @param \WP_Post|null $next_event The next event.
	 * @param string        $date       The date from which to start searching for events.
	 *
	 * @return \WP_Post|null
	 */
	public function filter_fast_forward_event( $next_event, $date ) {
		if ( null !== $next_event ) {
			return $next_event;
		}

		$post_stati = tribe( Page::class )->get_implicitly_requested_post_stati();

		return tribe_events()
			->where( 'starts_after', $date )
			->where( 'post_status', $post_stati )
			->per_page( 1 )
			->first();
	}

	/**
	 * Filters the Event title such that relevant post statuses are prepended as text.
	 *
	 * @since 5.9.0
	 *
	 * @param string $title   The Event title.
	 * @param int    $post_id The Event ID.
	 *
	 * @return string
	 */
	public function filter_post_status_into_event_title( $title, $post_id ) {
		$event = get_post( $post_id );

		if ( ! ( $event instanceof WP_Post && $event->ID !== (int) $post_id ) ) {
			return $title;
		}
		$post_status = $event->post_status;

		if ( 'draft' === $post_status ) {
			$title .= ' — ' . esc_html__( 'Draft', 'tribe-events-calendar-pro' );
		} elseif ( 'pending' === $post_status ) {
			$title .= ' — ' . esc_html__( 'Pending', 'tribe-events-calendar-pro' );
		} elseif ( 'trash' === $post_status ) {
			$title .= ' — ' . esc_html__( 'Trashed', 'tribe-events-calendar-pro' );
		} elseif ( 'tribe-ignored' === $post_status ) {
			$title .= ' — ' . esc_html__( 'Ignored', 'tribe-events-calendar-pro' );
		}

		return $title;
	}

	/**
	 * Gets the shortcode stati used in the dashboard calendar manager.
	 *
	 * @since 5.9.0
	 *
	 * @return array
	 */
	protected function get_dashboard_shortcode_stati() {
		$stati = [
			'publish',
			'private',
			'protected',
			'draft',
			'trash',
			\Tribe__Events__Ignored_Events::$ignored_status,
		];

		/**
		 * Filter the dashboard stati for the known range values.
		 *
		 * @since 5.9.0
		 *
		 * @param array $stati Statuses for events on admin manager.
		 */
		return apply_filters( 'tribe_events_pro_known_range_stati_dashboard', $stati );
	}

	/**
	 * Get max or min datetime of events by status.
	 *
	 * @param bool $fetch_start Fetch start or end boundary? True = start, False = end.
	 * @param array $stati Status to search by.
	 *
	 * @return int
	 */
	protected function get_boundary_datetime_by_status( $fetch_start = true, $stati = [] ) {
		/**
		 * Filters the earliest or latest date value the Events Manager will use to render.
		 *
		 * @since 6.0.0
		 *
		 * @param int|null      $date        The date value, initially `null`.
		 * @param bool          $fetch_start Whether to fetch the earliest date (`true`) or the latest (`false`).
		 * @param array<string> $stati       A list of post stati to return the results for.
		 */
		$date = apply_filters( 'tec_events_pro_manager_boundary_datetime_by_status', null, $fetch_start, $stati );

		if ( null !== $date ) {
			return $date;
		}

		global $wpdb;

		$stati = "('" . implode( "','", $stati ) . "')";

		$max_or_min = $fetch_start ? 'MIN' : 'MAX';

		$date = strtotime(
			$wpdb->get_var(
				$wpdb->prepare(
					"
							SELECT {$max_or_min}(meta_value) FROM $wpdb->postmeta
							JOIN $wpdb->posts ON post_id = ID
							WHERE meta_key = '_EventStartDate'
							AND post_type = '%s'
							AND post_status IN $stati
						",
					TEC::POSTTYPE
				)
			)
		);

		return $date;
	}

	/**
	 * Adjust the earliest and latest dates within the dashboard to include all event statuses.
	 *
	 * @since 5.9.0
	 *
	 * @param mixed $value Tribe option value.
	 * @param string $option_name Option name.
	 *
	 * @return false|int|mixed
	 */
	public function filter_earliest_date_option( $value, $option_name ) {
		if ( 'earliest_date' !== $option_name ) {
			return $value;
		}

		if ( ! $date = tribe_get_var( __METHOD__ ) ) {
			$date  = $this->get_boundary_datetime_by_status( true, $this->get_dashboard_shortcode_stati() );

			tribe_set_var( __METHOD__, $date );
		}

		return $date;
	}

	/**
	 * Adjust the latest date option within the dashboard to include all event statuses.
	 *
	 * @since 5.9.0
	 *
	 * @param mixed $value Tribe option value.
	 * @param string $option_name Option name.
	 *
	 * @return false|int|mixed
	 */
	public function filter_latest_date_option( $value, $option_name ) {
		if ( 'latest_date' !== $option_name ) {
			return $value;
		}

		if ( ! $date = tribe_get_var( __METHOD__ ) ) {
			$date  = $this->get_boundary_datetime_by_status( false, $this->get_dashboard_shortcode_stati() );

			tribe_set_var( __METHOD__, $date );
		}

		return $date;
	}
}

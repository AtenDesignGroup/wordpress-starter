<?php
/**
 * Renders the events part of a series in a list-like layout.
 *
 * @since   4.7.9
 * @package Tribe\Events\Pro\Views\V2\Views
 */

namespace Tribe\Events\Pro\Views\V2\Views;

use TEC\Events_Pro\Linked_Posts\Organizer\Taxonomy\Category;
use Tribe\Events\Pro\Rewrite\Rewrite as Pro_Rewrite;
use Tribe\Events\Views\V2\Messages;
use Tribe\Events\Views\V2\Utils;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe__Context as Context;
use Tribe__Events__Organizer as Organizer;
use Tribe__Utils__Array as Arr;

/**
 * Class Organizer_View
 *
 * @since   4.7.9
 *
 * @package Tribe\Events\Pro\Views\V2\Views
 */
class Organizer_View extends List_View {
	/**
	 * Slug for this view.
	 *
	 * @since 4.7.9
	 * @deprecated 6.0.7
	 *
	 * @var string
	 */
	protected $slug = 'organizer';

	/**
	 * Statically accessible slug for this view.
	 *
	 * @since 6.0.7
	 *
	 * @var string
	 */
	protected static $view_slug = 'organizer';

	/**
	 * The organizer parent post name.
	 *
	 * @since  4.7.9
	 * @deprecated 6.2.0 Removed in favor of using $post_id.
	 *
	 * @var string|string[]
	 */
	protected $post_name;

	/**
	 * The organizer parent post IDs.
	 *
	 * @since 5.0.0
	 * @since 6.2.0 Modified to be an array of IDs.
	 *
	 * @var array<int>
	 */
	protected $post_id;

	/**
	 * Visibility for this view.
	 *
	 * @since 4.7.9
	 *
	 * @var bool
	 */
	protected static $publicly_visible = false;

	/**
	 * Whether the View should display the events bar or not.
	 *
	 * @since 4.7.9
	 *
	 * @var bool
	 */
	protected $display_events_bar = false;

	/**
	 * Organizer_View constructor.
	 *
	 * Overrides the base View constructor to use PRO Rewrite handler.
	 *
	 * @since 5.0.1
	 *
	 * {@inheritDoc}
	 */
	public function __construct( Messages $messages = null ) {
		parent::__construct( $messages );
		$this->rewrite = new Pro_Rewrite();
	}

	/**
	 * Default untranslated value for the label of this view.
	 *
	 * @since 6.0.3
	 *
	 * @var string
	 */
	protected static $label = 'Organizer';

	/**
	 * @inheritDoc
	 */
	public static function get_view_label(): string {
		static::$label = _x( 'Organizer', 'The text label for the Organizer View.', 'tribe-events-calendar-pro' );

		return static::filter_view_label( static::$label );
	}

	/**
	 * Gets the Organizer IDs for this view.
	 *
	 * @since 5.0.0
	 * @since 6.2.0 Now returns an array of IDs.
	 *
	 * @return array<int>  Post ID for the venue generating this view.
	 */
	public function get_post_id() {
		return $this->post_id;
	}

	/**
	 * Sets the Post ID for the Organizer view.
	 *
	 * @since 6.2.0
	 *
	 * @param int|array<int> $ids Enables setting the post ids properly.
	 */
	public function set_post_id( $ids ): void {
		if ( is_numeric( $ids ) ) {
			$ids = [ $ids ];
		}

		// Don't set if not an array at this point.
		if ( ! is_array( $ids ) ) {
			return;
		}

		$this->post_id = array_filter( array_map( 'absint', $ids ) );;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html() {
		/*
		 * Since this view has historically being rendered with the `list` one let's allow developers to define
		 * templates for the `all` view, but fallback on the `list` one if not found.
		 */
		if ( $this->template->get_base_template_file() === $this->template->get_template_file() ) {
			$this->template_slug = List_View::get_view_slug();
		}

		return parent::get_html();
	}

	/**
	 * {@inheritDoc}
	 */
	public function prev_url( $canonical = false, array $passthru_vars = [] ) {
		$cache_key = __METHOD__ . '_' . md5( wp_json_encode( func_get_args() ) );

		if ( isset( $this->cached_urls[ $cache_key ] ) ) {
			return $this->cached_urls[ $cache_key ];
		}

		$current_page = (int) $this->context->get( 'page', 1 );
		$display      = $this->context->get( 'event_display_mode', 'organizer' );

		if ( 'past' === $display ) {
			$url = View::next_url( $canonical, [ Utils\View::get_past_event_display_key() => 'past' ] );
		} elseif ( $current_page > 1 ) {
			$url = View::prev_url( $canonical );
		} else {
			$url = $this->get_past_url( $canonical );
		}

		$url = $this->filter_prev_url( $canonical, $url );

		$this->cached_urls[ $cache_key ] = $url;

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function next_url( $canonical = false, array $passthru_vars = [] ) {
		$cache_key = __METHOD__ . '_' . md5( wp_json_encode( func_get_args() ) );

		if ( isset( $this->cached_urls[ $cache_key ] ) ) {
			return $this->cached_urls[ $cache_key ];
		}

		$current_page = (int) $this->context->get( 'page', 1 );
		$display      = $this->context->get( 'event_display_mode', 'organizer' );

		if ( static::$view_slug === $display || 'default' === $display ) {
			$url = View::next_url( $canonical );
		} elseif ( $current_page > 1 ) {
			$url = View::prev_url( $canonical, [ Utils\View::get_past_event_display_key() => 'past' ] );
		} else {
			$url = $this->get_upcoming_url( $canonical );
		}

		$url = $this->filter_next_url( $canonical, $url );

		$this->cached_urls[ $cache_key ] = $url;

		return $url;
	}

	/**
	 * Return the URL to a page of past events.
	 *
	 * @since 5.0.0
	 *
	 * @param bool $canonical Whether to return the canonical version of the URL or the normal one.
	 * @param int  $page The page to return the URL for.
	 *
	 * @return string The URL to the past URL page, if available, or an empty string.
	 */
	protected function get_past_url( $canonical = false, $page = 1 ) {
		$default_date   = 'now';
		$date           = $this->context->get( 'event_date', $default_date );
		$event_date_var = $default_date === $date ? '' : $date;

		$past = tribe_events()->by_args( $this->setup_repository_args( $this->context->alter( [
			'event_display_mode' => 'past',
			'paged'              => $page,
		] ) ) );

		if ( $past->count() > 0 ) {
			$event_display_key = Utils\View::get_past_event_display_key();
			$past_url          = add_query_arg( array_filter( [
				$this->page_key => $page > 1 ? $page : false,
			] ), $this->get_url( false ) );

			if ( ! $canonical ) {
				return $past_url;
			}

			// We've got rewrite rules handling `eventDate` and `eventDisplay`, but not List. Let's remove it.
			$canonical_url = tribe( 'events-pro.rewrite' )
				->get_clean_url( remove_query_arg( [ 'eventDate' ], $past_url ) );

			// We use the `eventDisplay` query var as a display mode indicator: we have to make sure it's there.
			$url = add_query_arg( [ $event_display_key => 'past' ], $canonical_url );

			// Let's re-add the `eventDate` if we had one and we're not already passing it with one of its aliases.
			if ( ! (
				empty( $event_date_var )
				|| $this->url->get_query_arg_alias_of( 'event_date', $this->context )
			) ) {
				$url = add_query_arg( [ 'eventDate' => $event_date_var ], $url );
			}

			return $url;
		}

		return '';
	}

	/**
	 * Return the URL to a page of upcoming events.
	 *
	 * @since 5.0.0
	 *
	 * @param bool $canonical Whether to return the canonical version of the URL or the normal one.
	 * @param int  $page The page to return the URL for.
	 *
	 * @return string The URL to the upcoming URL page, if available, or an empty string.
	 */
	protected function get_upcoming_url( $canonical = false, $page = 1 ) {
		$default_date   = 'now';
		$date           = $this->context->get( 'event_date', $default_date );
		$event_date_var = $default_date === $date ? '' : $date;
		$url = '';

		$upcoming = tribe_events()->by_args( $this->setup_repository_args( $this->context->alter( [
			'paged'        => $page,
		] ) ) );

		if ( $upcoming->count() > 0 ) {
			$upcoming_url_object = clone $this->url->add_query_args( array_filter( [
				$this->page_key    => $page,
				'eventDate'        => $event_date_var,
				'tribe-bar-search' => $this->context->get( 'keyword' ),
				'eventDisplay'     => static::$view_slug,
			] ) );

			$upcoming_url = (string) $upcoming_url_object;

			if ( ! $canonical ) {
				return $upcoming_url;
			}

			// We've got rewrite rules handling `eventDate`, but not List. Let's remove it to build the URL.
			$url = tribe( 'events.rewrite' )->get_clean_url(
				remove_query_arg( [ 'eventDate', 'page', 'paged', 'tribe_event_display' ], $upcoming_url )
			);

			// Let's re-add the `eventDate` if we had one and we're not already passing it with one of its aliases.
			if ( ! (
				empty( $event_date_var )
				|| $upcoming_url_object->get_query_arg_alias_of( 'event_date', $this->context )
			) ) {
				$url = add_query_arg( [ 'eventDate' => $event_date_var ], $url );
			}
		}

		return $url ?: $this->get_today_url( $canonical );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( Context $context = null ) {
		$args = parent::setup_repository_args( $context );

		$context = null !== $context ? $context : $this->context;
		$post_id = $context->get( 'post_id', false );
		$post_name = $context->get( 'tribe_organizer', false );

		$organizer_category_controller = tribe( Category::class );
		if ( $context->is( $organizer_category_controller->get_wp_slug() ) ) {
			$args = $organizer_category_controller->setup_repository_args( $args, $this, $context );
		} else {
			if ( empty( $post_name ) ) {
				$post_name = $context->get( 'name', false );
			}

			if ( empty( $post_id ) && empty( $post_name ) ) {
				// This is weirder but let's show the user events anyway.
				return $args;
			}

			// When Post ID not set we fall back into name.
			if ( empty( $post_id ) ) {
				$organizers = tribe_organizers()->by( 'name', $post_name )->fields( 'ids' );
				$post_id = $organizers->first();
			}

			$args['organizer'] = $post_id;
			$this->set_post_id( $post_id );
		}

		$date = $context->get( 'event_date', 'now' );
		$event_display = $context->get( 'event_display_mode', $context->get( 'event_display' ), 'current' );

		if ( 'past' !== $event_display ) {
			$args['ends_after'] = $date;
		} else {
			$args['order']       = 'DESC';
			$args['ends_before'] = $date;
		}

		return $args;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_show_datepicker_submit() {
		$live_refresh = tribe_get_option( 'liveFiltersUpdate', 'automatic' );
		return 'manual' === $live_refresh;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_url( $canonical = false, $force = false ) {
		$page = $this->url->get_current_page();
		$post_ids = $this->get_post_id();
		$organizer_category_controller = tribe( Category::class );
		$is_taxonomy_page = $this->context->is( $organizer_category_controller->get_wp_slug() );

		$query_args = [
			'eventDisplay'      => static::$view_slug,
			'paged'             => $page > 1 ? $page : false,
		];


		if ( ! $is_taxonomy_page ) {
			$organizer_id = reset( $post_ids );

			if ( ! empty( $organizer_id ) ) {
				$organizer = tribe_get_organizer_object( $organizer_id );
			}
			$query_args[ Organizer::POSTTYPE ] = $organizer->post_name;
		} else {
			$query_args['post_type'] = Organizer::POSTTYPE;
			$query_args[ $organizer_category_controller->get_wp_slug() ] = $this->context->get( $organizer_category_controller->get_wp_slug() );
		}

		$url = add_query_arg( array_filter( $query_args ), home_url() );

		if ( $canonical ) {
			$url = tribe( 'events-pro.rewrite' )->get_clean_url( $url, $force );
		}

		$event_display_key = Utils\View::get_past_event_display_key();
		$event_display_mode = $this->context->get( 'event_display_mode', false );
		if ( 'past' === $event_display_mode ) {
			$url = add_query_arg( [ $event_display_key => $event_display_mode ], $url );
		}

		if ( $is_taxonomy_page ) {
			$url = remove_query_arg( 'post_type', $url );
		}

		$event_date = $this->context->get( 'event_date', false );
		if ( ! empty( $event_date ) ) {
			// If there's a date set, then add it as a query argument.
			$url = add_query_arg( [ 'tribe-bar-date' => $event_date ], $url );
		}

		$url = $this->filter_view_url( $canonical, $url );

		return $url;
	}
	/**
	 * Setup the breadcrumbs for the "Organizer" view.
	 *
	 * @since 4.7.9
	 *
	 * @param array $breadcrumbs The breadcrumbs array.
	 * @param self $view        The instance of the view being rendered.
	 *
	 * @return array The filtered breadcrumbs.
	 *
	 * @see \Tribe\Events\Views\V2\View::get_breadcrumbs() for where this code is applying.
	 */
	public function setup_breadcrumbs( $breadcrumbs, $view ) {
		$post_id = $view->get_post_id();

		if ( ! is_array( $post_id ) ) {
			return $breadcrumbs;
		}

		$breadcrumbs[] = [
			'link'  => tribe_get_events_link(),
			'label' => tribe_get_event_label_plural(),
		];

		$breadcrumbs[] = [
			'link'  => '',
			'label' => tribe_get_organizer_label_plural(),
		];

		$breadcrumbs[] = [
			'link'  => '',
			'label' => get_the_title( reset( $post_id ) ),
		];

		return $breadcrumbs;
	}

	/**
	 * Setups up the Header Title for this view.
	 *
	 * @since 6.2.0
	 *
	 * @param string $header_title
	 * @param View   $view
	 *
	 * @return string
	 */
	public function setup_header_title( string $header_title, View $view ): string {
		$post_id = $view->get_post_id();
		if ( ! is_array( $post_id ) ) {
			return '';
		}

		return (string) get_the_title( reset( $post_id ) );
	}

	/**
	 * Setups up the Header Title for this view.
	 *
	 * @since 6.2.0
	 *
	 * @param string $header_title
	 * @param View   $view
	 *
	 * @return string
	 */
	public function setup_content_title( string $header_title, View $view ): string {
		return sprintf(
			_x( '%1$s from this %2$s', 'Content title for the View, displays right above the date selector on the Organizer View.', 'tribe-events-calendar-pro' ),
			tribe_get_event_label_plural(),
			strtolower( tribe_get_organizer_label_singular() )
		);
	}

	/**
	 * Render the organizer meta.
	 *
	 * @since 5.0.0
	 *
	 * @return string The organizer meta HTML
	 *
	 */
	public function render_meta() {
		$post_id = $this->get_post_id();

		if ( ! is_array( $post_id ) ) {
			return '';
		}

		$organizer = tribe_get_organizer_object( reset( $post_id ) );

		// Bail if we don't have a venue.
		if ( ! $organizer ) {
			return '';
		}

		// Bail if we don't have a venue of the right type.
		if ( Organizer::POSTTYPE !== $organizer->post_type ) {
			return '';
		}

		$template = $this->get_template();

		return $template->template( 'organizer/meta', array_merge( $template->get_values(), [ 'organizer' => $organizer ] ) );
	}

	/**
	 * Updates the URL query arguments for the Organizer View to correctly build its URls.
	 *
	 * @since 5.0.1
	 *
	 * {@inheritDoc}
	 */
	public function set_url( array $args = null, $merge = false ) {
		parent::set_url( $args, $merge );
		$url_query_args = $this->url->get_query_args();

		$url_query_args['post_type'] = null;

		$this->url->add_query_args( $url_query_args );
	}
}

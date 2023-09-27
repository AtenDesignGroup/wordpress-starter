<?php

namespace Tribe\Events\Pro\Admin\Manager;

use WP_Screen;
use Tribe__Settings;
use Tribe__Template;
use Tribe__Utils__Array as Arr;
use Tribe__Events__Pro__Main as Pro;
use Tribe__Events__Main as TEC;
use Tribe__Events__Admin__Bar__Admin_Bar;
use \Tribe\Events\Admin\Settings as TEC_Settings;


/**
 * Class Page
 *
 * @since   5.9.0
 *
 * @package Tribe\Events\Pro\Admin\Manager
 */
class Page {

	/**
	 * What is the page slug that holds the Admin manager.
	 *
	 * @since 5.9.0
	 *
	 * @var string
	 */
	protected $page_slug = 'tribe-admin-manager';

	/**
	 * Holds the Hook associated with the page, only a string when `admin_menu` is rendered.
	 *
	 * @since 5.9.0
	 *
	 * @var string|null
	 */
	protected $page_hook;

	/**
	 * Holds the template instance rendering the admin page.
	 *
	 * @since 5.9.0
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Holds the number of posts for this user.
	 *
	 * @since 5.9.0
	 *
	 * @var int
	 */
	private $user_posts_count;

	/**
	 * Fetches the page slug used.
	 *
	 * @since 5.9.0
	 *
	 * @return string
	 */
	public function get_page_slug() {
		return $this->page_slug;
	}

	/**
	 * Fetches the page hook returned when submenu is added.
	 *
	 * @since 5.9.0
	 *
	 * @return string|null
	 */
	public function get_page_hook() {
		return $this->page_hook;
	}

	/**
	 * Fetches the page title.
	 *
	 * @since 5.9.0
	 *
	 * @return string
	 */
	public function get_page_title() {
		return esc_html__( 'Events Manager', 'tribe-events-calendar-pro' );
	}

	/**
	 * Sets the page hook.
	 *
	 * @since 5.9.0
	 *
	 * @param string $hook Hook used to identify the page added.
	 */
	public function set_page_hook( $hook ) {
		$this->page_hook = $hook;
	}

	/**
	 * Adds the submenu to the admin, and saves the hook for later use.
	 *
	 * @since 5.9.0
	 */
	public function add_submenu_page() {
		$admin_pages = tribe( 'admin.pages' );
		$parent      = tribe( TEC_Settings::class )->get_tec_events_menu_slug();
		$title       = $this->get_page_title();

		$page_hook = $admin_pages->register_page(
			[
				'id'         => $this->get_page_slug(),
				'parent'     => $parent,
				'title'      => $title,
				'path'       => $this->get_page_slug(),
				'capability' => 'edit_tribe_events',
				'callback'   => [
					$this,
					'render',
				],
			]
		);

		$this->set_page_hook( $page_hook );
	}

	/**
	 * Gets the link to the Calendar Manger page.
	 *
	 * @since 5.9.0
	 *
	 * @param array $args Other args to be merged into the link.
	 *
	 * @return string  Link not escaped for the calendar manager.
	 */
	public function get_link( array $args = [] ) {
		$url  = admin_url( 'edit.php' );
		$args = array_merge( $args, [
			'post_type' => TEC::POSTTYPE,
			'page'      => $this->get_page_slug(),
		] );
		$url  = add_query_arg( $args, $url );
		return $url;
	}

	/**
	 * Gets the formatted edit link.
	 *
	 * @since 5.9.0
	 *
	 * @param array  $args  Link arguments.
	 * @param string $label Link label.
	 * @param string $class Link CSS classes.
	 *
	 * @return string
	 */
	public function get_edit_link( array $args, $label, $class ) {
		$url = $this->get_link( $args );

		$class_html   = '';
		$aria_current = '';
		if ( ! empty( $class ) ) {
			$class_html = sprintf(
				' class="%s"',
				esc_attr( $class )
			);

			if ( 'current' === $class ) {
				$aria_current = ' aria-current="page"';
			}
		}

		return sprintf(
			'<a href="%s"%s%s>%s</a>',
			esc_url( $url ),
			$class_html,
			$aria_current,
			$label // This value is not being escaped as it could contain HTML.
		);
	}

	/**
	 * Determine if the current view is the "All" view.
	 *
	 * @since 5.9.0
	 *
	 * @return bool Whether the current view is the "All" view.
	 */
	protected function is_base_request() {
		$vars = $_GET;
		unset( $vars['paged'] );
		unset( $vars['page'] );

		if ( empty( $vars ) ) {
			return true;
		} elseif (
			1 === count( $vars )
			&& ! empty( $vars['post_type'] )
		) {
			return true;
		}

		return 1 === count( $vars ) && ! empty( $vars['mode'] );
	}

	/**
	 * Fetches the link HTML for the page holding the Calendar manager.
	 *
	 * @since 5.9.0
	 *
	 * @return string
	 */
	public function get_link_html() {
		$screen = get_current_screen();

		if ( TEC::POSTTYPE === $screen->id ) {
			$button_title = esc_html_x( 'Back to Manager', 'Link ot the Events Manager page', 'tribe-events-calendar-pro' );
		} else {
			$button_title = esc_html_x( 'Manager', 'Link ot the Events Manager page', 'tribe-events-calendar-pro' );
		}

		return '<a href="' . esc_url( $this->get_link() ) . '" class="page-title-action tec-admin-manager__link">' . $button_title . '</a>';
	}


	/**
	 * Removes the submenu so users cannot navigate to this particular submenu directly.
	 *
	 * @since 5.10.0
	 *
	 */
	public function hide_events_manager_submenu_item() {
		global $submenu;

		$parent_page = 'edit.php?post_type=' . TEC::POSTTYPE;
		if ( ! isset( $submenu[ $parent_page ] ) ) {
			return;
		}

		foreach ( $submenu[ $parent_page ] as $submenu_index => $item ) {
			if ( $this->get_page_slug() === $item[2] ) {
				// Remove this link from menu
				unset( $submenu[ $parent_page ][ $submenu_index ] );

				return;
			}
		}
	}

	/**
	 * Removes the submenu so users cannot navigate to this particular submenu directly.
	 *
	 * @since 5.10.0
	 *
	 * @param string|null $submenu_file
	 *
	 * @return string|null
	 */
	public function change_default_events_menu_url( $submenu_file ) {
		global $submenu;
		$parent_page = 'edit.php?post_type=' . TEC::POSTTYPE;

		if ( ! isset( $submenu[ $parent_page ] ) ) {
			return $submenu_file;
		}

		foreach ( $submenu[ $parent_page ] as $submenu_index => $item ) {
			if (
				$parent_page === $item[2]
				&& tribe( Settings::class )->use_calendar_manager()
			) {
				$item[2] = $this->get_link();

				// Replace the menu item for Editing events with Calendar Manager link.
				$submenu[ $parent_page ][ $submenu_index ] = $item;
			}
		}

		return $submenu_file;
	}

	/**
	 * Configure and returns the template instance controlling the admin page.
	 *
	 * @since 5.9.0
	 *
	 * @return Tribe__Template
	 */
	public function get_template() {
		if ( empty( $this->template ) ) {
			$this->setup_template();
		}

		return $this->template;
	}

	/**
	 * Configures the template instance responsible for rendering the administration page.
	 *
	 * @since 5.9.0
	 */
	protected function setup_template() {
		$template = new Tribe__Template();

		$template->set_template_origin( Pro::instance() );
		$template->set_template_folder( 'src/admin-views' );

		// We specifically don't want to look up template files here.
		$template->set_template_folder_lookup( false );

		// Configures this templating class extract variables.
		$template->set_template_context_extract( true );

		$this->template = $template;
	}

	/**
	 * Gets the arguments passed to the template rendering of this page.
	 *
	 * @since 5.9.0
	 *
	 * @return array
	 */
	public function get_page_arguments() {
		$this->set_user_post_counts();

		$messages = $this->get_bulk_action_messages( $_REQUEST );

		return [
			'page' => $this,
			'shortcode' => tribe( Shortcode::class ),
			'views' => $this->get_views(),
			'bulk_messages' => $messages['bulk_messages'],
			'bulk_counts' => $messages['bulk_counts'],
		];
	}

	/**
	 * Sets the post count for the current user.
	 *
	 * Note: This was largely lifted from WP_Posts_List_Table.
	 *
	 * @since 5.9.0
	 */
	protected function set_user_post_counts() {
		global $wpdb;

		$exclude_states         = get_post_stati( [ 'show_in_admin_all_list' => false ] );
		$this->user_posts_count = (int) $wpdb->get_var(
			$wpdb->prepare( "
					SELECT
						COUNT( 1 )
					FROM
						$wpdb->posts
					WHERE
						post_type = %s
						AND post_status NOT IN ( '" . implode( "','", $exclude_states ) . "' )
						AND post_author = %d
				",
				TEC::POSTTYPE,
				get_current_user_id()
			)
		);
	}

	/**
	 * Check if the current screen is the calendar manager class.
	 *
	 * @since 5.9.0
	 *
	 * @param WP_Screen $screen Current screen being tested.
	 *
	 * @return bool If a current screen is the calendar manager.
	 */
	public function is_current_screen( WP_Screen $screen = null ) {
		if ( null === $screen ) {
			if ( ! function_exists( 'get_current_screen' ) ) {
				return false;
			}
			$screen = get_current_screen();
		}

		if ( empty( $screen->id ) ) {
			return false;
		}

		if ( $screen->id !== $this->get_page_hook() ) {
			return false;
		}

		return true;
	}

	/**
	 * Modify the Admin Title for the calendar manager page.
	 *
	 * @since 5.9.0
	 *
	 * @param string $admin_title Administration title.
	 * @param string $title       Original title.
	 *
	 * @return string Modified page of the Calendar Manager.
	 */
	public function filter_admin_title( $admin_title, $title ) {
		if ( ! $this->is_current_screen() ) {
			return $admin_title;
		}

		return $this->get_page_title() . ' ' . $admin_title;
	}

	/**
	 * Modify link on the Administration Bar for Editing Events.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function modify_edit_events_link() {
		if ( ! tribe( Settings::class )->use_calendar_manager() ) {
			return;
		}

		$admin_bar = Tribe__Events__Admin__Bar__Admin_Bar::instance();

		if ( ! $admin_bar->is_enabled() ) {
			return;
		}

		global $wp_admin_bar;
		$main = TEC::instance();

		if ( ! current_user_can( 'edit_' . TEC::POSTTYPE ) ) {
			return;
		}

		$wp_admin_bar->add_menu( [
			'id'     => 'tribe-events-edit-events',
			'title'  => esc_html( sprintf( __( 'Edit %s', 'tribe-events-calendar-pro' ), $main->plural_event_label ) ),
			'href'   => $this->get_link(),
			'parent' => 'tribe-events-group',
		] );
	}

	/**
	 * Renders the page with calendar manager.
	 *
	 * @since 5.9.0
	 *
	 * @return string
	 */
	public function render() {
		add_action( 'tribe_events_pro_shortcode_toggle_view_hooks', [ tribe( Shortcode::class ), 'toggle_shortcode_hooks' ] );

		return $this->get_template()->template( 'manager/page', $this->get_page_arguments() );
	}

	/**
	 * Gets the view filter list.
	 *
	 * Note: This was largely lifted from WP_Post_List_Table.
	 *
	 * @since 5.9.0
	 *
	 * @return array
	 */
	protected function get_views() {
		global $locked_post_status, $avail_post_stati;

		// Is going to call wp().
		$avail_post_stati = wp_edit_posts_query();
		$post_type = TEC::POSTTYPE;

		// NOTE: The following is completely copied from  WP_Posts_List_Table
		if ( ! empty( $locked_post_status ) ) {
			return array();
		}

		$status_links = array();
		$num_posts    = wp_count_posts( $post_type, 'readable' );
		$total_posts  = array_sum( (array) $num_posts );
		$class        = '';

		$current_user_id = get_current_user_id();
		$all_args        = array( 'post_type' => $post_type );
		$mine            = '';

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
			$total_posts -= $num_posts->$state;
		}

		if ( $this->user_posts_count && $this->user_posts_count !== $total_posts ) {
			if ( isset( $_GET['author'] ) && ( $_GET['author'] == $current_user_id ) ) {
				$class = 'current';
			}

			$mine_args = array(
				'post_type' => $post_type,
				'author'    => $current_user_id,
			);

			$mine_inner_html = sprintf(
			/* translators: %s: Number of posts. */
				_nx(
					'Mine <span class="count">(%s)</span>',
					'Mine <span class="count">(%s)</span>',
					$this->user_posts_count,
					'posts'
				),
				number_format_i18n( $this->user_posts_count )
			);

			$mine = $this->get_edit_link( $mine_args, $mine_inner_html, $class );

			$all_args['all_posts'] = 1;
			$class                 = '';
		}

		if ( empty( $class ) && ( $this->is_base_request() || isset( $_REQUEST['all_posts'] ) ) ) {
			$class = 'current';
		}

		$all_inner_html = sprintf(
		/* translators: %s: Number of posts. */
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$total_posts,
				'posts'
			),
			number_format_i18n( $total_posts )
		);

		$status_links['all'] = $this->get_edit_link( $all_args, $all_inner_html, $class );
		if ( $mine ) {
			$status_links['mine'] = $mine;
		}

		foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( ! in_array( $status_name, $avail_post_stati, true ) || empty( $num_posts->$status_name ) ) {
				continue;
			}

			if ( isset( $_REQUEST['post_status'] ) && $status_name === $_REQUEST['post_status'] ) {
				$class = 'current';
			}

			$status_args = array(
				'post_status' => $status_name,
				'post_type'   => $post_type,
			);

			$status_label = sprintf(
				translate_nooped_plural( $status->label_count, $num_posts->$status_name ),
				number_format_i18n( $num_posts->$status_name )
			);

			$status_links[ $status_name ] = $this->get_edit_link( $status_args, $status_label, $class );
		}

		if ( ! empty( $this->sticky_posts_count ) ) {
			$class = ! empty( $_REQUEST['show_sticky'] ) ? 'current' : '';

			$sticky_args = array(
				'post_type'   => $post_type,
				'show_sticky' => 1,
			);

			$sticky_inner_html = sprintf(
			/* translators: %s: Number of posts. */
				_nx(
					'Sticky <span class="count">(%s)</span>',
					'Sticky <span class="count">(%s)</span>',
					$this->sticky_posts_count,
					'posts'
				),
				number_format_i18n( $this->sticky_posts_count )
			);

			$sticky_link = array(
				'sticky' => $this->get_edit_link( $sticky_args, $sticky_inner_html, $class ),
			);

			// Sticky comes after Publish, or if not listed, after All.
			$split        = 1 + array_search( ( isset( $status_links['publish'] ) ? 'publish' : 'all' ), array_keys( $status_links ), true );
			$status_links = array_merge( array_slice( $status_links, 0, $split ), $sticky_link, array_slice( $status_links, $split ) );
		}

		/**
		 * Filters the list of available list table views.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen.
		 *
		 * Note: this is a duplicate of the filter provided in views_{$screen->id} filter from wp-admin/class-wp-list-table.php.
		 *
		 * @since WP 3.1.0
		 *
		 * @param string[] $views An array of available list table views.
		 */
		$status_links = apply_filters( 'views_edit-' . TEC::POSTTYPE, $status_links );

		return $status_links;
	}

	/**
	 * Gets the requested post stati based on $_REQUEST['post_status'] or $_REQUEST['url'] parameters.
	 *
	 * @since 5.9.0
	 *
	 * @return array
	 */
	public function get_requested_post_status() {
		$requested_status = tribe_get_request_var( 'post_status' );

		if ( ! $requested_status && isset( $_REQUEST['url'] ) ) {
			if ( $query_string = wp_parse_url( $_REQUEST['url'], PHP_URL_QUERY ) ) {
				$query_args = wp_parse_args( $query_string );
				$requested_status = Arr::get( $query_args, 'post_status', null );
			}
		}

		return $requested_status;
	}

	/**
	 * Gets the requested tribe-has-tickets based on $_REQUEST['tribe-has-tickets'] or $_REQUEST['url'] parameters.
	 *
	 * @since 5.9.0
	 *
	 * @return null|string
	 */
	public function get_requested_tribe_has_tickets() {
		$has_tickets = tribe_get_request_var( 'tribe-has-tickets' );

		if ( ! $has_tickets && isset( $_REQUEST['url'] ) ) {
			if ( $query_string = wp_parse_url( $_REQUEST['url'], PHP_URL_QUERY ) ) {
				$query_args = wp_parse_args( $query_string );
				$has_tickets = Arr::get( $query_args, 'tribe-has-tickets', null );
			}
		}

		return $has_tickets;
	}

	/**
	 * Gets all of the relevant post stati for the current request.
	 *
	 * @since 5.9.0
	 *
	 * @return array
	 */
	public function get_implicitly_requested_post_stati() {
		$requested_status = $this->get_requested_post_status();

		$post_stati = get_post_stati( [], 'objects' );
		unset( $post_stati['auto-draft'] );
		unset( $post_stati['inherit'] );

		if (
			! $requested_status
			|| (
				$requested_status
				&& 'trash' !== $requested_status
				&& 'tribe-ignored' !== $requested_status
			)
		) {
			unset( $post_stati['trash'] );
			unset( $post_stati['tribe-ignored'] );
		}

		if ( $requested_status && 'trash' === $requested_status ) {
			$post_stati = [
				'trash'         => $post_stati['trash'],
				'tribe-ignored' => $post_stati['tribe-ignored'],
			];
		} elseif ( $requested_status && 'tribe-ignored' === $requested_status ) {
			$post_stati = [
				'tribe-ignored' => $post_stati[ 'tribe-ignored' ],
			];
		} elseif ( $requested_status && isset( $post_stati[ $requested_status ] ) ) {
			$post_stati = [ $requested_status => $post_stati[ $requested_status ] ];
		}

		return array_keys( $post_stati );
	}

	/**
	 * Get the messaging for single or bulk actions.
	 *
	 * This was largely lifted from wp-admin/edit.php (hence the lack of textdomains.
	 *
	 * @since 5.9.0
	 *
	 * @param array $request_data REQUEST data.
	 * @return array
	 */
	public function get_bulk_action_messages( $request_data = [] ) {
		$bulk_counts = [
			'updated'   => isset( $request_data['updated'] ) ? absint( $request_data['updated'] ) : 0,
			'locked'    => isset( $request_data['locked'] ) ? absint( $request_data['locked'] ) : 0,
			'deleted'   => isset( $request_data['deleted'] ) ? absint( $request_data['deleted'] ) : 0,
			'trashed'   => isset( $request_data['trashed'] ) ? absint( $request_data['trashed'] ) : 0,
			'untrashed' => isset( $request_data['untrashed'] ) ? absint( $request_data['untrashed'] ) : 0,
		];

		$bulk_messages             = [];
		$bulk_messages['post']     = [
			/* translators: %s: Number of posts. */
			'updated'   => _n( '%s post updated.', '%s posts updated.', $bulk_counts['updated'] ),
			'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 post not updated, somebody is editing it.' ) :
				/* translators: %s: Number of posts. */
				_n( '%s post not updated, somebody is editing it.', '%s posts not updated, somebody is editing them.', $bulk_counts['locked'] ),
			/* translators: %s: Number of posts. */
			'deleted'   => _n( '%s post permanently deleted.', '%s posts permanently deleted.', $bulk_counts['deleted'] ),
			/* translators: %s: Number of posts. */
			'trashed'   => _n( '%s post moved to the Trash.', '%s posts moved to the Trash.', $bulk_counts['trashed'] ),
			/* translators: %s: Number of posts. */
			'untrashed' => _n( '%s post restored from the Trash.', '%s posts restored from the Trash.', $bulk_counts['untrashed'] ),
		];

		/**
		 * Filters the bulk action updated messages.
		 *
		 * By default, custom post types use the messages for the 'post' post type.
		 *
		 * Note: This filter is not prefixed by tribe_ or tec_ because it is the filter from wp-admin/edit.php.
		 *
		 * @since WP 3.7.0
		 *
		 * @param array[] $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are
		 *                               keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
		 * @param int[]   $bulk_counts   Array of item counts for each message, used to build internationalized strings.
		 */
		$bulk_messages = apply_filters( 'bulk_post_updated_messages', $bulk_messages, $bulk_counts );
		$bulk_counts   = array_filter( $bulk_counts );

		return [
			'bulk_messages' => $bulk_messages,
			'bulk_counts'   => $bulk_counts
		];
	}

	/**
	 * Outputs the template that renders the manager link and relocates it to the correct location.
	 *
	 * @since 5.9.0
	 */
	public function inject_manager_link() {
		$helper = \Tribe__Admin__Helpers::instance();

		// Are we on a post type edit screen?
		$is_post_type = $helper->is_post_type_screen( TEC::POSTTYPE );

		if ( ! $is_post_type ) {
			return;
		}

		$screen = get_current_screen();

		// Are we on the event list screen?
		if ( 'edit-' . TEC::POSTTYPE !== $screen->id ) {
			return;
		}

		// But not on the manager already?
		if ( $this->get_page_slug() === tribe_get_request_var( 'page', false )) {
			return;
		}

		$this->get_template()->template( 'manager/manager-link', [ 'manager_link' => $this->get_link_html() ] );
	}
}

<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Events\Pro\Admin\Manager\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'pro.admin.manager.hooks' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Events\Pro\Admin\Manager\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'pro.admin.manager.hooks' ), 'some_method' ] );
 *
 * @since 5.9.0
 *
 * @package Tribe\Events\Pro\Admin\Manager
 */

namespace Tribe\Events\Pro\Admin\Manager;

use WP_REST_Request as Request;
use Tribe__Admin__Helpers as Admin_Helpers;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Hooks.
 *
 * @since 5.9.0
 *
 * @package Tribe\Events\Pro\Admin\Manager
 */
class Hooks extends Service_Provider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.9.0
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions required by each Admin Manager component.
	 *
	 * @since 5.9.0
	 */
	protected function add_actions() {
		add_action( 'wp', [ $this, 'set_shortcode_to_display' ] );
		add_action( 'admin_menu', [ $this, 'add_admin_menu_items' ], 15 );
		add_action( 'admin_menu', [ $this, 'hide_events_manager_submenu_item' ], 25 );
		add_action( 'tribe_events_views_v2_before_make_view_for_rest', [ $this, 'action_shortcode_toggle_hooks' ], 5, 3 );
		add_action( 'wp_before_admin_bar_render', [ $this, 'modify_edit_events_link' ], 15 );
		add_action( 'in_admin_footer', tribe_callback( Page::class, 'inject_manager_link' ) );
		add_action( 'admin_notices', tribe_callback( Modal\Split_Upcoming::class, 'render_modal' ) );
		add_action( 'admin_notices', tribe_callback( Modal\Split_Single::class, 'render_modal' ) );
	}

	/**
	 * Adds the filters required by each Admin Manager component.
	 *
	 * @since 5.9.0
	 */
	protected function add_filters() {
		add_filter( 'admin_title', [ $this, 'filter_admin_title' ], 15, 2 );
		add_filter( 'submenu_file', [ $this, 'change_default_events_menu_url' ] );
		add_filter( 'tribe_general_settings_tab_fields', [ $this, 'filter_settings_general_tab' ], 25 );
		add_filter( 'wp_redirect', [ $this, 'filter_edit_page_redirect_to_render_admin_manager' ] );
		add_filter( 'tec_events_views_v2_disable_tribe_bar', [ $this, 'filter_views_v2_disable_tribe_bar_on_event_manager_page' ] );
		add_filter( 'tec_events_views_v2_hide_location_search', [ $this, 'filter_views_v2_hide_location_search_on_event_manager_page' ] );
	}

	/**
	 * Set the tribe_events shortcode to display if in the dashboard and on the manager page.
	 *
	 * @since 5.9.0
	 */
	public function set_shortcode_to_display() {
		if ( ! is_admin() ) {
			return;
		}

		$is_screen = $this->container->make( Page::class )->is_current_screen( get_current_screen() );

		if ( ! $is_screen ) {
			return;
		}

		add_filter( 'tribe_events_shortcode_tribe_events_should_display', '__return_true' );
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
		return $this->container->make( Page::class )->filter_admin_title( $admin_title, $title );
	}

	/**
	 * Modify the General Settings tabs fields to include Calendar Manager checkbox.
	 *
	 * @since 5.9.0
	 *
	 * @param array $fields Previous fields on the General Settings.
	 *
	 * @return array Modified fields.
	 */
	public function filter_settings_general_tab( array $fields = [] ) {
		return $this->container->make( Settings::class )->filter_include_settings( $fields );
	}

	/**
	 * Modify link on the Administration Bar for Editing Events.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function modify_edit_events_link() {
		$this->container->make( Page::class )->modify_edit_events_link();
	}

	/**
	 * Possibly loads all the shortcode hooks.
	 *
	 * @since 5.9.0
	 *
	 * @param  string    $slug    The current view Slug.
	 * @param  array     $params  Params so far that will be used to build this view.
	 * @param  Request   $request The rest request that generated this call.
	 */
	public function action_shortcode_toggle_hooks( $slug, $params, Request $request ) {
		$this->container->make( Shortcode::class )->maybe_toggle_hooks_for_rest( $slug, $params, $request );
	}

	/**
	 * Removes the visible submenu from the admin to prevent users from navigating directly.
	 *
	 * This leverages the submenu_file filter as if it were an action, as it is the last action before
	 * the rendering of the menu where we can alter the URL of the Events menu item.
	 *
	 * @since 5.10.0
	 *
	 * @param string|null $submenu_file
	 *
	 */
	public function change_default_events_menu_url( $submenu_file ) {
		$this->container->make( Page::class )->change_default_events_menu_url( $submenu_file );
	}

	/**
	 * Removes the visible submenu from the admin to prevent users from navigating directly.
	 *
	 * @since 5.9.0
	 */
	public function hide_events_manager_submenu_item() {
		$this->container->make( Page::class )->hide_events_manager_submenu_item();
	}

	/**
	 * Adds the submenu to the Events section, which allows the page to be visited.
	 *
	 * @since 5.9.0
	 */
	public function add_admin_menu_items() {
		$this->container->make( Page::class )->add_submenu_page();
	}

	/**
	 * Filter the redirect URL to send the user to the admin manager page if tec_render argument is set.
	 *
	 * @since 5.9.0
	 *
	 * @param string $location Redirect location.
	 * @return string
	 */
	public function filter_edit_page_redirect_to_render_admin_manager( $location ) {
		if ( ! isset( $_REQUEST['tec_render'] ) ) {
			return $location;
		}

		if ( ! isset( $_REQUEST['doaction'] ) ) {
			return $location;
		}

		if ( ! isset( $_REQUEST['action'] ) ) {
			return $location;
		}

		return add_query_arg( 'page', $this->container->make( Page::class )->get_page_slug(), $location );
	}

	/**
	 * Determine whether to apply the `tribeDisableTribeBar` setting on the Events Manager page.
	 *
	 * @since 5.11.1
	 *
	 * @param boolean $apply_settings Whether to apply the setting or not.
	 *
	 * @return boolean Whether to apply the setting or not.
	 */
	public function filter_views_v2_disable_tribe_bar_on_event_manager_page( $apply_setting ) {
		if ( Admin_Helpers::instance()->is_screen( 'tribe-admin-manager' ) ) {
			return false;
		}

		return $apply_setting;
	}

	/**
	 * Determine whether to apply the `hideLocationSearch` setting on the Events Manager page.
	 *
	 * @since 5.11.1
	 *
	 * @param boolean $apply_settings Whether to apply the setting or not.
	 *
	 * @return boolean Whether to apply the setting or not.
	 */
	public function filter_views_v2_hide_location_search_on_event_manager_page( $apply_setting ) {
		if ( Admin_Helpers::instance()->is_screen( 'tribe-admin-manager' ) ) {
			return false;
		}

		return $apply_setting;
	}
}

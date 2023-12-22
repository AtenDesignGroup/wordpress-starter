<?php
/**
 * Handles the Custom Tables integration, and compatibility, with the non-custom-tables-based
 * implementation of the plugin.
 *
 * Here what implementations and filters are not relevant, are disconnected.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Legacy_Compat
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Legacy_Compat;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Provider_Contract;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Repository\Events;
use Tribe__Admin__Notices as Admin_Notices;
use Tribe__Events__Main as TEC;
use Tribe__Events__Pro__Main as Pro_Main;
use Tribe__Events__Pro__Recurrence__Meta as Pro_Recurrence_Meta;
use TEC\Common\Contracts\Service_Provider;
use WP_Admin_Bar;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Legacy_Compat
 */
class Provider extends Service_Provider implements Provider_Contract {
	/**
	 * Selectively manipulates the previous version hooks in the Filters API
	 * to integrate correctly with the previous version implementation.
	 *
	 * @since 6.0.0
	 *
	 */
	public function register() {
		if ( ! has_action( 'init', [ $this, 'remove_legacy_init_hooks' ] ) ) {
			add_action( 'init', [ $this, 'remove_legacy_init_hooks' ], 15 );
		}

		if ( ! has_action( 'wp_before_admin_bar_render', [ $this, 'update_admin_bar' ] ) ) {
			add_action( 'wp_before_admin_bar_render', [ $this, 'update_admin_bar' ] );
		}

		if ( ! has_action( 'wp_ajax_gutenberg_events_pro_recurrence_queue', [ $this, 'no_queue' ] ) ) {
			add_action( 'wp_ajax_gutenberg_events_pro_recurrence_queue', [ $this, 'no_queue' ], 1 );
		}

		$this->disconnect_pro_realtime_queue();
		$this->unhook_recurrence_meta_updates();

		// Remove redirection on single occurrence view.
		$pro = Pro_Main::instance();
		remove_filter( 'wp', [ $pro, 'detect_recurrence_redirect' ] );

		// Remove the filter with the row actions on recurring events.
		remove_filter( 'post_row_actions', [ Pro_Recurrence_Meta::class, 'edit_post_row_actions' ] );

		// Prevents PRO from updating the Event Post Recurrence meta and Occurrences.
		if ( ! has_filter( 'tribe_events_pro_editor_save_recurrence_meta', '__return_true' ) ) {
			add_filter( 'tribe_events_pro_editor_save_recurrence_meta', '__return_true' );
		}

		// Remove the notice for all occurrences on a series.
		Admin_Notices::instance()->remove( 'editing-all-recurrences' );

		// Remove the PRO filters that are setting up queries on Recurring Events.
		remove_action( 'tribe_events_pre_get_posts', [ $pro, 'setup_hide_recurrence_in_query' ] );

		if ( ! has_filter( 'tribe_settings_tab_fields', [ $this, 'rename_recurring_settings_on_admin' ] ) ) {
			add_filter( 'tribe_settings_tab_fields', [ $this, 'rename_recurring_settings_on_admin' ], 10, 2 );
		}

		// Do not redirect from post names to child posts.
		remove_action( 'parse_query', [ $pro, 'set_post_id_for_recurring_event_query' ], 101 );
	}

	/**
	 * Handles the part of the previous Recurring Events implementation that would
	 * handle the realtime, Javascript-driven, generation of Recurring Event instances
	 * from the Administration UI.
	 *
	 * @since 6.0.0
	 */
	private function disconnect_pro_realtime_queue() {
		$queue_realtime = Pro_Main::instance()->queue_realtime;
		remove_action( 'admin_head-post.php', [ $queue_realtime, 'post_editor' ] );
		remove_action( 'wp_ajax_tribe_events_pro_recurrence_realtime_update', [ $queue_realtime, 'ajax' ] );
	}

	/**
	 * Unhooks the Pro plugin Recurrence Meta Handler.
	 *
	 * @since 6.0.0
	 */
	private function unhook_recurrence_meta_updates() {
		remove_action( 'tribe_events_update_meta', [ Pro_Recurrence_Meta::class, 'updateRecurrenceMeta' ], 20 );

		remove_action(
			'manage_' . TEC::POSTTYPE . '_posts_custom_column',
			[ Pro_Recurrence_Meta::class, 'populate_custom_list_table_columns' ]
		);

		remove_action(
			'manage_' . TEC::POSTTYPE . '_posts_columns',
			[ Pro_Recurrence_Meta::class, 'list_table_column_headers' ]
		);
	}

	/**
	 * Registers the implementations and filters required by the custom tables implementation
	 * to play nice with the existing code.
	 *
	 * @since 6.0.0
	 */
	public function unregister() {
		remove_action( 'init', [ $this, 'remove_legacy_init_hooks' ], 15 );
		remove_action( 'wp_before_admin_bar_render', [ $this, 'update_admin_bar' ] );
		remove_action( 'wp_ajax_gutenberg_events_pro_recurrence_queue', [ $this, 'no_queue' ], 1 );

		$pro            = Pro_Main::instance();
		$queue_realtime = $pro->queue_realtime;
		add_action( 'admin_head-post.php', [ $queue_realtime, 'post_editor' ] );
		add_action( 'wp_ajax_tribe_events_pro_recurrence_realtime_update', [ $queue_realtime, 'ajax' ] );

		if ( ! has_action( 'tribe_events_update_meta', [ Pro_Recurrence_Meta::class, 'updateRecurrenceMeta' ] ) ) {
			add_action( 'tribe_events_update_meta', [ Pro_Recurrence_Meta::class, 'updateRecurrenceMeta' ], 20, 2 );
		}

		add_action(
			'manage_' . TEC::POSTTYPE . '_posts_custom_column',
			[ Pro_Recurrence_Meta::class, 'populate_custom_list_table_columns' ],
			2
		);

		add_action(
			'manage_' . TEC::POSTTYPE . '_posts_columns',
			[ Pro_Recurrence_Meta::class, 'list_table_column_headers' ]
		);

		add_filter( 'wp', [ $pro, 'detect_recurrence_redirect' ] );
		add_filter( 'post_row_actions', [ Pro_Recurrence_Meta::class, 'edit_post_row_actions' ], 10, 2 );
		remove_filter( 'tribe_events_pro_editor_save_recurrence_meta', '__return_true' );
		add_action( 'tribe_events_pre_get_posts', [ $pro, 'setup_hide_recurrence_in_query' ] );
		remove_filter( 'tribe_settings_tab_fields', [ $this, 'rename_recurring_settings_on_admin' ] );
	}

	/**
	 * Handles the part of the previous Recurring Events implementation that would
	 * handle the generation of Recurring Event instances in the context of a Blocks
	 * Editor request.
	 *
	 * @since 6.0.0
	 */
	public function no_queue() {
		wp_send_json( false );
		die();
	}

	/**
	 * Removal of the hooks that are attached after the action "init" has been fired.
	 *
	 * @since 6.0.0
	 */
	public function remove_legacy_init_hooks() {
		if ( ! $this->container->isBound( 'events-pro.editor.meta' ) ) {
			return;
		}
		$pro_editor_meta = $this->container->make( 'events-pro.editor.meta' );
		// @todo Why are we doing this? And if we are removing it here, perhaps we should only do so after we implement $this->handle_legacy_compatibility()
		/*remove_filter( 'get_post_metadata', [ $pro_editor_meta, 'fake_blocks_response' ], 15 );
		remove_filter( 'get_post_metadata', [ $pro_editor_meta, 'fake_recurrence_description' ], 15 );*/
	}

	/**
	 * Remove nodes from the WP_Admin_Bar injected by PRO.
	 *
	 * @since 6.0.0
	 */
	public function update_admin_bar() {
		if ( ! is_single() ) {
			return;
		}

		if ( ! is_singular( TEC::POSTTYPE ) ) {
			return;
		}

		global $wp_admin_bar;

		if ( ! $wp_admin_bar instanceof WP_Admin_Bar ) {
			return;
		}

		$removal_list = [
			'edit-series',
			'split-single',
			'split-series',
		];

		foreach ( $removal_list as $id ) {
			$wp_admin_bar->remove_node( $id );
		}
	}

	/**
	 * Update the wording used for the recurring labels on the general tab of the settings.
	 *
	 * @param array<string, array<string, mixed>> $settings An array with all the settings for that particular tab.
	 * @param string                              $id       The name of the tab we are targeting.
	 *
	 * @return  array<string, array<string, mixed>> An array with the updated settings.
	 */
	public function rename_recurring_settings_on_admin( $settings, $id ) {
		// We are targeting the general tab from the admin.
		if ( $id !== 'general' ) {
			return $settings;
		}

		// Make sure $settings is always an array.
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		// Keys we are accessing inside the $settings array.
		$keys = [
			'hideSubsequentRecurrencesDefault',
			'userToggleSubsequentRecurrences',
		];

		// Make sure the keys exists as arrays before accessing any of the keys.
		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $settings ) || ! is_array( $settings[ $key ] ) ) {
				$settings[ $key ] = [];
			}
		}

		// hideSubsequentRecurrencesDefault
		$settings['hideSubsequentRecurrencesDefault']['label']   = __( 'Condense events in Series', 'tribe-events-calendar-pro' );
		$settings['hideSubsequentRecurrencesDefault']['tooltip'] = __( 'Show only the next event in each Series (only affects list-style views).',
			'tribe-events-calendar-pro' );
		// userToggleSubsequentRecurrences
		$settings['userToggleSubsequentRecurrences']['label']   = __( 'Front-end Condense Events Series toggle',
			'tribe-events-calendar-pro' );
		$settings['userToggleSubsequentRecurrences']['tooltip'] = __( 'Allow users to limit list-style views to only show the next event in each Series.',
			'tribe-events-calendar-pro' );

		return $settings;
	}
}

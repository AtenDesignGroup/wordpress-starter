<?php
/**
 * Handles the registration of modifications done to the Events Manager UI.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Classic
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors\Manager;


use Tribe__Events__Main as TEC;
use Tribe__Events__Pro__Main as Plugin;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Manager
 */
class Provider extends Service_Provider {


	/**
	 * Registers the implementations, hooks and filters required to alter the Events Manager UI flow.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->enqueue_admin_script();
	}

	/**
	 * Enqueues the assets required to alter the Events Manager UI to adapt it to the plugin supported behaviors.
	 *
	 * @since 6.0.0
	 */
	public function enqueue_admin_script() {
		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			'tec-events-pro-event-manager-events.js',
			'custom-tables-v1/event-manager-events.js',
			[],
			'admin_enqueue_scripts',
			[
				'in_footer'    => true,
				'localize'     => [],
				'priority'     => 200,
				'conditionals' => [ $this, 'is_event_manager_screen' ],
			]
		);
	}

	/**
	 * Determine whether the current screen is event manager screen.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean
	 */
	public function is_event_manager_screen() {
		$helper = \Tribe__Admin__Helpers::instance();

		// Are we on a post type edit screen?
		$is_post_type = $helper->is_post_type_screen( TEC::POSTTYPE );

		if ( ! $is_post_type ) {
			return false;
		}

		$screen = get_current_screen();

		// Are we on the event manager screen?
		if ( 'tribe_events_page_tribe-admin-manager' !== $screen->id ) {
			return false;
		}

		return true;
	}
}

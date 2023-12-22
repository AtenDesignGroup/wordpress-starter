<?php
/**
 * Handles registering all Assets for the Events Pro Admin Manager.
 *
 * To remove a Assets:
 * tribe( 'assets' )->remove( 'asset-name' );
 *
 * @since   5.9.0
 *
 * @package Tribe\Events\Pro\Admin\Manager
 */

namespace Tribe\Events\Pro\Admin\Manager;

use Tribe__Events__Pro__Main as Plugin;
use TEC\Common\Contracts\Service_Provider;
use Tribe__Admin__Helpers;


/**
 * Register the Assets for Events Pro Admin Manager.
 *
 * @since   5.9.0
 *
 * @package Tribe\Events\Pro\Admin\Manager
 */
class Assets extends Service_Provider {


	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.9.0
	 */
	public function register() {
		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			'tribe-events-pro-admin-manager',
			'admin/manager-page.js',
			[ 'tribe-common' ],
			'admin_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [
					[ $this, 'should_enqueue' ],
				],
				'localize'     => [
					'name' => 'tribeEventsAdminManagerData',
					'data' => static function () {
						$data = [
							'link_html' => tribe( Page::class )->get_link_html(),
						];

						return $data;
					},
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-pro-admin-manager-styles',
			'admin/manager-page.css',
			[],
			'admin_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [
					[ $this, 'should_enqueue' ],
				],
			]
		);
	}

	/**
	 * Determines if we need to include a set of assets.
	 *
	 * @since 5.9.0
	 *
	 * @return bool
	 */
	public function should_enqueue() {
		/** @var Tribe__Admin__Helpers $helper */
		$helper = Tribe__Admin__Helpers::instance();

		// Are we on a post type screen?
		$is_post_type = $helper->is_post_type_screen();

		return $is_post_type;
	}
}

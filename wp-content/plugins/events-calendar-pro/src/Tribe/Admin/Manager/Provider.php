<?php
/**
 * The main service provider associated with the Admin Manager for Events.
 *
 * @since   5.9.0
 * @package Tribe\Events\Pro\Admin\Manager
 */
namespace Tribe\Events\Pro\Admin\Manager;

use TEC\Common\Contracts\Service_Provider as Provider_Contract;


/**
 * Class Provider.
 *
 * @since   5.9.0
 *
 * @package Tribe\Events\Pro\Admin\Manager
 */
class Provider extends Provider_Contract {


	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.9.0
	 */
	public function register() {
		// Only available on V2.
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		$this->container->singleton( Events_Table::class, Events_Table::class );
		$this->container->singleton( Page::class, Page::class );
		$this->container->singleton( Shortcode::class, Shortcode::class );
		$this->container->singleton( Settings::class, Settings::class );
		$this->container->singleton( Modal\Split_Single::class, Modal\Split_Single::class );
		$this->container->singleton( Modal\Split_Upcoming::class, Modal\Split_Upcoming::class );

		$this->register_hooks();
		$this->register_assets();

		// Register the SP on the container
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'pro.admin.manager.provider', $this );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for Views v2.
	 *
	 * @since 5.9.0
	 */
	protected function register_assets() {
		$assets = new Assets( $this->container );
		$assets->register();

		$this->container->singleton( Assets::class, $assets );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for Views v2.
	 *
	 * @since 5.9.0
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'pro.admin.manager.hooks', $hooks );
	}
}

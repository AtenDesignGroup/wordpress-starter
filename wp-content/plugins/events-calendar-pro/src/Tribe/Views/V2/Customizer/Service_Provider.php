<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\Pro\V2\Customizer
 * @since   5.7.0
 */

namespace Tribe\Events\Pro\Views\V2\Customizer;

use Tribe\Events\Pro\Views\V2\Customizer\Section\Events_Bar;
use Tribe\Events\Pro\Views\V2\Customizer\Section\Global_Elements;
use TEC\Common\Contracts\Service_Provider as Provider_Contract;


/**
 * Class Service_Provider
 *
 * @since   5.8.0
 *
 * @package Tribe\Events\Views\Pro\V2\Customizer
 */
class Service_Provider extends Provider_Contract {

	/**
	 * Registers the bindings and hooks the filters required for the ECP->TEC Customizer integration to work.
	 *
	 * @since 5.8.0
	 */
	public function register() {
		$this->container->singleton( 'pro.views.v2.customizer.provider', $this );

		$this->register_hooks();

		// Events Bar overrides and additions.
		tribe_register( Events_Bar::class, Events_Bar::class );
		// Global Elements overrides and additions.
		tribe_register( Global_Elements::class, Global_Elements::class );
	}

	/**
	 * Register the hooks for Tribe_Customizer integration.
	 *
	 * @since 5.8.0
	 */
	public function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'pro.views.v2.customizer.hooks', $hooks );
	}

}

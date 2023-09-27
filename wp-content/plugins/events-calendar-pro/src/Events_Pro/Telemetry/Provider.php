<?php
/**
 * Service Provider for interfacing with TEC\Common\Telemetry.
 *
 * @since 6.1.0
 *
 * @package TEC\Events_Pro\Telemetry
 */

namespace TEC\Events_Pro\Telemetry;

use TEC\Common\Contracts\Service_Provider;
use TEC\Common\Telemetry\Telemetry as Common_Telemetry;

 /**
  * Class Provider
  *
  * @since 6.1.0

  * @package TEC\Events_Pro\Telemetry
  */
class Provider extends Service_Provider {
	/**
	 * Register the service provider.
	 *
	 * @since 6.1.0
	 */
	public function register() {
		$this->add_filters();
		$this->add_actions();
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 6.1.0
	 */
	public function add_actions() {
		add_action( 'tec_common_telemetry_loaded', [ $this, 'tec_telemetry_register_plugin' ] );
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 6.1.0
	 */
	public function add_filters() {
		add_filter( 'tec_telemetry_slugs', [ $this, 'filter_tec_telemetry_slugs' ] );
	}

	/**
	 * Registers our plugin with Telemetry in Common.
	 *
	 * @since 6.1.0
	 */
	public function tec_telemetry_register_plugin() {
		return $this->container->get( Common_Telemetry::class )->register_tec_telemetry_plugins();
	}

	/**
	 * Let Events Calendar Pro add itself to the list of registered plugins for Telemetry.
	 *
	 * @since 6.1.0
	 *
	 * @param array<string,string> $slugs The existing array of slugs.
	 *
	 * @return array<string,string> $slugs The modified array of slugs.
	 */
	public function filter_tec_telemetry_slugs( $slugs ) {
		return $this->container->get( Telemetry::class )->filter_tec_telemetry_slugs( $slugs );
	}
}

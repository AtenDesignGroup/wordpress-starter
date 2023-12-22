<?php
/**
 * Handles ECP integration with other premium plugins.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Integrations
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Integrations;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events_Pro\Custom_Tables\V1\Integrations\APM\APM_Integration;
use TEC\Events_Pro\Custom_Tables\V1\Integrations\WPML\WPML_Integration;
use TEC\Events_Pro\Compatibility\Event_Automator\Zapier\Recurrence\Provider as Zapier_Recurrence_Provider;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Integrations
 */
class Provider extends Service_Provider {
	/**
	 * Registers, if required, the plugin integration with other premium plugins.
	 *
	 * @since 6.0.0
	 * @since 6.0.11 - Add Event Automation Zapier.
	 */
	public function register() {
		// Class defined by The Events Calendar: Filter Bar plugin.
		if ( class_exists( '\\TEC\\Filter_Bar\\Custom_Tables\\V1\\Provider' ) ) {
			$this->container->register( \TEC\Filter_Bar\Custom_Tables\V1\Provider::class );
		}

		// Class defined by The Events Calendar: Community Events plugin.
		if ( class_exists( '\\TEC\\Community_Events\\Custom_Tables\\V1\\Provider' ) ) {
			$this->container->register( \TEC\Events_Community\Custom_Tables\V1\Provider::class );
		}

		// Load Zapier Recurrence Provider for Event Automator integration.
		$this->container->register( Zapier_Recurrence_Provider::class );

		// Class defined by the Advanced Posts Manager plugin.
		if ( class_exists( '\\Tribe_APM' ) ) {
			$this->container->register( APM_Integration::class );
		}

		// Class and constant defined by the WPML plugin.
		if ( class_exists( 'SitePress' ) && defined( 'ICL_PLUGIN_PATH' ) ) {
			$this->container->register( WPML_Integration::class );
		}
	}
}

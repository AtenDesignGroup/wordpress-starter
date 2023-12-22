<?php

namespace Tribe\Events\Pro\Integrations\Brizy_Builder;
use TEC\Common\Contracts\Service_Provider as Provider_Contract;



/**
 * Class Service_Provider
 *
 * @since 5.14.5
 *
 * @package Tribe\Events\Pro\Integrations\Brizy_Builder
 */
class Service_Provider extends Provider_Contract {


	/**
	 * Registers the bindings and hooks the filters required for the Brizy Builder integrations to work.
	 *
	 * @since 5.14.5
	 */
	public function register() {
		add_filter( 'tribe_events_views_v2_assets_should_enqueue_frontend', [ $this, 'should_enqueue_frontend' ], 10, 2 );
	}

	/**
	 * Checks if we should enqueue frontend assets on Brizy builder.
	 *
	 * @since 5.14.5
	 *
	 * @return bool Whether or not to enqueue assets.
	 */
	public function should_enqueue_frontend() {
		// Bail if views v2 isn't active
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return false;
		}

		return true;
	}
}

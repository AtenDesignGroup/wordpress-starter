<?php
/**
 * Registers the Custom Tables based version of the PRO plugin (v1), if possible.
 *
 * The provider will completely register, or not, the Custom Tables based
 * implementation. The registration will happen on `plugins_loaded::1`.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */

namespace TEC\Events_Pro\Custom_Tables\V1;

use Exception;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Provider as TEC_Provider;
use TEC\Common\Contracts\Service_Provider;
use Throwable;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */
class Provider extends Service_Provider {

	/**
	 * A flag property indicating whether the Service Provider did register or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the Provider did register or not.
	 */
	public function register() {
		if ( ! ( class_exists( TEC_Provider::class ) && TEC_Provider::is_active() ) ) {
			return false;
		}

		if ( $this->did_register ) {
			// Let's avoid double filtering by making sure we're registering at most once.
			return true;
		}

		$this->did_register = true;

		try {
			// Register this provider to allow getting hold of it from third-party code.
			$this->container->singleton( self::class, self::class );
			$this->container->singleton( 'tec.pro.custom-tables.v1.provider', self::class );
			$this->container->register( Tables\Provider::class );
			$this->container->register( Migration\Provider::class );

			$state = $this->container->make( State::class );

			if ( $state->is_running() ) {
				// Some providers are required during the migration process itself.
				$this->container->register( Models\Provider::class );
			}

			// Should we fully activate?
			if ( $state->is_migrated() ) {
				// These providers should be the ones that extend the bulk of features for CT1,
				// with only the bare minimum of providers registered above, to determine important state information.
				$this->container->register( Full_Activation_Provider::class );
			}

			return true;
		} catch ( Throwable $t ) {
			// This code will never fire on PHP 5.6, but will do in PHP 7.0+.

			/**
			 * Fires an action when an error or exception happens in the
			 * context of Custom Tables v1 implementation AND the server
			 * runs PHP 7.0+.
			 *
			 * @since 6.0.0
			 *
			 * @param Throwable $t The thrown error.
			 */
			do_action( 'tec_events_custom_tables_v1_error', $t );
		} catch ( Exception $e ) {
			// PHP 5.6 compatible code.

			/**
			 * Fires an action when an error or exception happens in the
			 * context of Custom Tables v1 implementation AND the server
			 * runs PHP 5.6.
			 *
			 * @since 6.0.0
			 *
			 * @param Exception $e The thrown error.
			 */
			do_action( 'tec_events_custom_tables_v1_error', $e );
		}
	}
}

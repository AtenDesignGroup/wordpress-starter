<?php
/**
 * Handles the code that should be executed when the plugin is activated or deactivated.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */

namespace TEC\Events_Pro\Custom_Tables\V1;

use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\Provider as Provisional_Post_Provider;
use TEC\Events\Custom_Tables\V1\Tables\Provider as TEC_Tables_Provider;
use TEC\Events_Pro\Custom_Tables\V1\Tables\Provider as Tables_Provider;
use TEC\Events\Custom_Tables\V1\Activation as TEC_Activation;
use Tribe__Events__Rewrite as Rewrite;

/**
 * Class Activation
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */
class Activation {

	/**
	 * The name of the transient that will be used to flag whether the plugin did activate
	 * or not.
	 *
	 * @since 6.0.0
	 */
	public const ACTIVATION_TRANSIENT = 'tec_custom_tables_v1_ecp_initialized';

	/**
	 * Returns the name of the transient used by TEC to store the last initialization time of the custom tables.
	 *
	 * @since 5.0.7
	 *
	 * @return string The name of the transient used by TEC to store the last initialization time of the custom tables.
	 */
	public static function get_tec_activation_transient(): string {
		$transient_key = 'tec_custom_tables_v1_initialized';

		if ( class_exists( TEC_Activation::class ) && defined( TEC_Activation::ACTIVATION_TRANSIENT ) ) {
			$transient_key = TEC_Activation::ACTIVATION_TRANSIENT;
		}

		return $transient_key;
	}

	/**
	 * Handles the activation of the feature functions.
	 *
	 * The method does not contain table creation logic as some plugin activation methods will not call this method
	 * and the tables will be created on the next first request. If the plugin is activated using wp-cli or as a
	 * must-use plugin, this method will never run. Table creation logic must live in the `init` method of this class
	 * to ensure tables will be created on the first request that might be using them.
	 *
	 * @since 6.0.0
	 */
	public static function activate(): void {
		set_transient( Rewrite::KEY_DELAYED_FLUSH_REWRITE_RULES, 1 );
		flush_rewrite_rules();

		// Bail when Common is not loaded.
		if ( ! function_exists( 'tribe_register_provider' ) ) {
			return;
		}

		static::init();
	}

	/**
	 * Initializes the custom tables required by the feature to work.
	 *
	 * This method will run once a day (using transients) and is idem-potent
	 * in the context of the same day.
	 *
	 * @since 6.0.0
	 * @since 6.1.0 Reworked transient logic to use tec_timed_option instead. More concise.
	 */
	public static function init(): void {
		// If the activation last ran less than 24 hours ago, bail.
		if ( tec_timed_option()->get( static::ACTIVATION_TRANSIENT ) ) {
			return;
		}

		tec_timed_option()->set( static::ACTIVATION_TRANSIENT, 1, DAY_IN_SECONDS );

		// Register the providers to add the required schemas, TEC will use it to create the ECP tables.
		if ( ! tribe()->isBound( TEC_Tables_Provider::class ) ) {
			tribe_register_provider( TEC_Tables_Provider::class );
		}
		if ( ! tribe()->isBound( Tables_Provider::class ) ) {
			tribe_register_provider( Tables_Provider::class );
		}

		// Clear TEC transient flag so init() will run.
		$tec_transient = self::get_tec_activation_transient();
		tec_timed_option()->delete( $tec_transient );

		// Finally trigger the TEC activation code that will include ECP custom tables schema.
		TEC_Activation::init();

		if ( ! tribe()->getVar( 'ct1_fully_activated' ) ) {
			/**
			 * On new installations the full activation code will find an empty state and
			 * will have not activated at this point, do it now if required.
			 */
			tribe()->register( Full_Activation_Provider::class );
		}

		// Set up the provisional post ID base.
		$services = tribe();
		$services->register( Provisional_Post_Provider::class );
		$services->make( Provisional_Post_Provider::class )->on_activation();
	}

	/**
	 * Handles the feature deactivation.
	 *
	 * @since 6.0.0
	 */
	public static function deactivate() {
		// Delete the transient to make sure the activation code will run again.
		$transient = self::get_tec_activation_transient();
		tec_timed_option()->delete( $transient );
		tec_timed_option()->delete( self::ACTIVATION_TRANSIENT );

		tribe()->make( Provisional_Post_Provider::class )->on_deactivation();
	}
}

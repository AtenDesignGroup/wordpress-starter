<?php
/**
 * Responsible for registering providers that are only relevant after an appropriate number of steps have been taken to
 * fully activate the features of Custom Tables V1.
 *
 * Should not be registered if the Custom Tables have not been generated yet.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */

namespace TEC\Events_Pro\Custom_Tables\V1;

use Exception;
use TEC\Events_Pro\Custom_Tables\V1\Admin\Notices\Occurrence_Notices;
use Throwable;
use Tribe__Admin__Notices;
use Tribe__Events__Admin_List as TEC_Admin_List;
use Tribe__Events__Main as TEC;
use \TEC\Common\Contracts\Service_Provider;

/**
 * Class Full_Activation_Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */
class Full_Activation_Provider extends Service_Provider {

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
		if ( $this->did_register ) {
			// Let's avoid double filtering by making sure we're registering at most once.
			return true;
		}

		$this->did_register = true;

		try {
			// Register this provider to allow getting hold of it from third-party code.
			$this->container->singleton( self::class, self::class );
			$this->container->singleton( 'tec.pro.custom-tables.v1.provider', self::class );
			$this->container->singleton( Occurrence_Notices::class );
			$this->container->register( Links\Provider::class );
			$this->container->register( Models\Provider::class );
			$this->container->register( Admin\Lists\Provider::class );
			$this->container->register( Admin\Settings_Controller::class );
			$this->container->register( Series\Provider::class );
			$this->container->register( Templates\Provider::class );
			$this->container->register( Updates\Provider::class );
			$this->container->register( Editors\Provider::class );
			$this->container->register( WP_Query\Provider::class );
			$this->container->register( Events\Provisional\Provider::class );
			$this->container->register( Legacy_Compat\Provider::class );
			$this->container->register( Repository\Provider::class );
			$this->container->register( Views\V2\Provider::class );
			$this->container->register( REST\V1\Provider::class );
			$this->container->register( Admin\Notices\Provider::class );
			$this->container->register( Events_Manager\Provider::class );
			$this->container->register( Links\Provider::class );
			$this->container->register( Events\Event_Cleaner\Provider::class );
			$this->container->singleton( Gettext::class, Gettext::class );

			/*
			 * Integrations with 3rd party code are registered last to
			 * allow for their registration to happen on the "ready"
			 * state of the container.
			 */
			$this->container->register( Integrations\Provider::class );

			$this->add_filters();

			if ( tribe( 'context' )->doing_ajax() ) {
				add_action( 'admin_init', [ $this, 'remove_admin_filters' ] );
			} else {
				add_action( 'current_screen', [ $this, 'remove_admin_filters' ] );
			}
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

		/**
		 * Fires an action when ECP Custom Tables v1 implementation is fully activated.
		 *
		 * @since 6.1.1
		 */
		do_action( 'tec_events_pro_custom_tables_v1_fully_activated' );

		return true;
	}

	/**
	 * Adds the general filters required by the feature to work correctly.
	 *
	 * @since 6.0.0
	 */
	private function add_filters() {
		// Overwrite the wp_ajax_tribe_notice_dismiss Ajax action
		remove_action( 'wp_ajax_tribe_notice_dismiss', [ Tribe__Admin__Notices::instance(), 'maybe_dismiss' ]);
		add_action( 'wp_ajax_tribe_notice_dismiss', $this->container->callback( Occurrence_Notices::class, 'on_dismiss' ) );
		// Update the count of the original event when the occurrence is broken out of the series.
		add_action( 'tec_events_occurrences_after_broken', $this->container->callback( Occurrence_Notices::class, 'on_occurrences_broken' ));

		// Build immediately and delegate hooking as it will required runtime init and will always happen.
		$this->container->make( Gettext::class )->hook();

		// Activate after the TEC part of the feature (10).
		add_action( 'init', [ Activation::class, 'init' ], 11 );
	}

	/**
	 * Remove the base admin filters added by TEC to override the behavior by Occurrence.
	 *
	 * @since 6.0.0
	 */
	public function remove_admin_filters() {
		remove_filter( 'views_edit-' . TEC::POSTTYPE, [ TEC_Admin_List::class, 'update_event_counts' ] );
		remove_action( 'manage_posts_custom_column', [ TEC_Admin_List::class, 'custom_columns' ] );
	}
}

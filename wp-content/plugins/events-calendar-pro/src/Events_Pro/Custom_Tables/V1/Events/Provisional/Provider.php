<?php
/**
 * Manages the provisional events.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Provisional
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Provisional;


use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator as Provisional_ID_Generator;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post_Cache;
use TEC\Common\Contracts\Service_Provider;
use Tribe__Events__Main as TEC;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Provisional
 */
class Provider extends Service_Provider {

	/**
	 * Registers the service provider functions.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->container->singleton( Provisional_ID_Generator::class, Provisional_ID_Generator::class );

		if ( is_multisite() ) {
			$this->register_multisite_actions();
		}

		add_action( 'wp_insert_post', [ $this, 'flush_cache' ] );
		add_action( 'save_post_' . TEC::POSTTYPE, [ $this, 'flush_event_cache' ] );
	}

	/**
	 * In case a new post ID is created make sure the base is updated correctly.
	 *
	 * @since 6.0.0
	 *
	 * @param $post_id
	 */
	public function flush_cache( $post_id ) {
		$ID_generator     = $this->container->make( Provisional_ID_Generator::class );
		$provisional_post = $this->container->make( Provisional_Post_Cache::class );

		while ( $ID_generator->needs_change() ) {
			$provisional_post->flush_all();
			$ID_generator->update();
		}
	}

	/**
	 * Flush the cache of an event ID.
	 *
	 * @since 6.0.0
	 *
	 * @param $post_id
	 */
	public function flush_event_cache( $post_id ) {
		$this->container->make( Provisional_Post_Cache::class )->flush_occurrences( $post_id );
	}

	/**
	 * Hooks specifically for WP MU Installations.
	 *
	 * @since 6.0.0
	 */
	protected function register_multisite_actions() {
		add_action( 'activate_blog', [ $this, 'on_activation' ] );
	}

	/**
	 * Action fired when the plugin was installed.
	 *
	 * @since 6.0.0
	 */
	public function on_activation() {
		$this->container->make( Provisional_ID_Generator::class )->install();
	}

	/**
	 * Action fired once the plugin has been deactivated.
	 *
	 * @since 6.0.0
	 */
	public function on_deactivation() {
		$this->container->make( Provisional_ID_Generator::class )->uninstall();
		$this->container->make( Provisional_Post_Cache::class )->flush_all();
	}
}

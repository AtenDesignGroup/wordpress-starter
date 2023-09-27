<?php
/**
 * Manages the update, created and removed Occurrences notices in the Admin UI.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Admin\Notices;

use TEC\Events\Custom_Tables\V1\Provider_Contract;
use TEC\Common\Contracts\Service_Provider;
use Tribe__Events__Main;

/**
 * Recurring Notices Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */
class Provider extends Service_Provider implements Provider_Contract {

	/**
	 * Registers the filters and implementations required by the new Notices implementation.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		// Make the provider available in the container.
		$this->container->singleton( self::class, $this );

		add_action( 'tec_events_custom_tables_v1_request_after_insert_event', [
			$this,
			'on_inserted_event'
		], 10, 2 );
		add_action( 'tec_events_custom_tables_v1_request_after_update_event', [
			$this,
			'on_updated_event'
		], 10, 2 );
		add_filter( 'post_updated_messages', [ $this, 'remove_default_message' ], 99, 1 );
	}

	/**
	 * We are handling this notice with overrides defined in Occurrence_Notices.
	 * All handling should be delegated there.
	 *
	 * @since 6.0.0
	 * @since 6.0.2 Removing several other messages (draft and published) to allow Occurrence_Notices to take over.
	 *
	 * @param array $messages
	 *
	 * @return array The filtered messages.
	 */
	public function remove_default_message( $messages = [] ) {
		if ( ! is_array( $messages ) ) {
			return $messages;
		}

		if ( isset( $messages[ Tribe__Events__Main::POSTTYPE ][1] ) ) {
			// Remove the main "updated" and "published" message for Events.
			$messages[ Tribe__Events__Main::POSTTYPE ][1]  = false;
			$messages[ Tribe__Events__Main::POSTTYPE ][6]  = false;
			$messages[ Tribe__Events__Main::POSTTYPE ][10] = false;
		}

		return $messages;
	}

	/**
	 * {@inheritDoc}
	 */
	public function unregister() {
		$this->unregister_ct1_notices();
		remove_filter( 'post_updated_messages', [
			$this,
			'remove_default_message'
		] );
	}

	/**
	 * Separated CT1 notices to target in some situations.
	 */
	public function unregister_ct1_notices() {
		remove_action( 'tec_events_custom_tables_v1_request_after_insert_event', [
			$this,
			'on_inserted_event'
		] );
		remove_action( 'tec_events_custom_tables_v1_request_after_update_event', [
			$this,
			'on_updated_event'
		] );
	}
	/**
	 * Fired when a new event is created.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post to attach this notice to.
	 */
	public function on_inserted_event( $post_id ) {
		return tribe( Occurrence_Notices::class )->on_inserted_event( $post_id );
	}

	/**
	 * Fired when an existing event was updated.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post to attach this notice to.
	 */
	public function on_updated_event( $post_id ) {
		return tribe( Occurrence_Notices::class )->on_updated_event( $post_id );
	}
}

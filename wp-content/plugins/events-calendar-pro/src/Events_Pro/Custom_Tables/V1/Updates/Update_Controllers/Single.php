<?php
/**
 * Hooks on the WordPress IDENTIFY, WRITE and READ phases to break an Occurrence
 * out of the original Recurring Event and update the original and new Events.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Admin\Notices\Provider as Notices_Provider;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Events;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Redirector;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Requests;
use Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta as Blocks_Meta;
use WP_Post;
use Tribe__Timezones as Timezones;

/**
 * Class Single
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers
 */
class Single implements Update_Controller_Interface {
	use Update_Controller_Methods;

	/**
	 * A reference to the current Events repository handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Events
	 */
	private $events;

	/**
	 * The ID of the Event post created by the Update Controller.
	 *
	 * @since 6.0.0
	 *
	 * @var int|null
	 */
	private $single_post_id;

	/**
	 * A reference to the current Requests handler.
	 *
	 * @since 6.0.1
	 *
	 * @var Requests
	 */
	private $requests;
	/**
	 * A reference to the current broewser and request redirection handler.
	 *
	 * @since 6.0.1
	 *
	 * @var Redirector
	 */
	private $redirector;

	/**
	 * Single constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Events   $events   A reference to the current Events repository handler.
	 * @param Requests $requests A reference to the current Requests handler.
	 */
	public function __construct( Events $events, Requests $requests, Redirector $redirector ) {
		$this->events     = $events;
		$this->requests   = $requests;
		$this->redirector = $redirector;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.0.12 Moved dissect single occurrence logic into Events repository.
	 * @since 6.0.0
	 */
	public function apply_before_identify_step( $post_id ) {
		if ( false === ( $post = $this->check_step_requirements( $post_id ) ) ) {
			return false;
		}

		$this->save_request_id( $post_id );

		$provisional_post = tribe( Provisional_Post::class );
		// Fetch this occurrence.
		if ( $provisional_post->is_provisional_post_id( $post_id ) ) {
			$occurrence = Occurrence::find( $provisional_post->normalize_provisional_post_id( $post_id ) );
		} else {
			// If it is a post ID, this should be the first occurrence.
			$occurrence = Occurrence::where( 'post_id', $post_id )
			                        ->where( 'is_rdate', 0 )
			                        ->order_by( 'start_date', 'ASC' )
			                        ->first();
		}
		if ( ! $occurrence ) {
			// Something happened, bail.
			do_action( 'tribe_log', 'error', 'Failed to locate requested occurrence on Single update.', [
				'source'  => __METHOD__,
				'slug'    => 'occurrence-dissect-fail-on-single',
				'post_id' => $post_id,
			] );

			return false;
		}

		// Dissect and move the occurrence out from the originating recurrence.
		$this->single_post_id = $this->events->detach_occurrence_from_event( $occurrence );
		$this->save_rest_request_recurrence_meta( $this->single_post_id, $this->request );

		// Make sure we remove the split events recurrence meta before committing CT1 updates (recurrence still in the globals).
		add_action( 'tec_events_custom_tables_v1_update_post_before', [ $this, 'ensure_no_recurrence_meta' ] );

		if ( $this->requests->is_link_update_request( $this->request ) ) {
			$this->redirector->redirect_to_edit_link( $this->single_post_id );
		}

		return $this->single_post_id;
	}

	/**
	 * Removes the Recurrence meta that might have been set for the created Single Event.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post ID of the event for which the pre-commit process
	 *                     is running.
	 */
	public function ensure_no_recurrence_meta( int $post_id ): void {
		if ( $post_id !== $this->single_post_id ) {
			return;
		}

		remove_action( current_action(), [ $this, 'ensure_no_recurrence_meta' ] );

		$this->events->delete_recurrence_meta( $post_id );
	}
}

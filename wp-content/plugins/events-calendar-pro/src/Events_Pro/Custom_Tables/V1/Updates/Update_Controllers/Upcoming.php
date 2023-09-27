<?php
/**
 * Hooks on the WordPress IDENTIFY, WRITE and READ phases to split a Recurring
 * Event at the specified Occurrence and update the original and new Events.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Updates\Requests;
use TEC\Events_Pro\Custom_Tables\V1\Admin\Notices\Provider as Notices_Provider;
use TEC\Events_Pro\Custom_Tables\V1\Events\Recurrence;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Controller;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Events;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Transient_Occurrence_Redirector as Occurrence_Redirector;
use WP_Post;
use WP_REST_Request;

/**
 * Class Upcoming
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers
 */
class Upcoming implements Update_Controller_Interface {
	use Update_Controller_Methods;

	/**
	 * A reference to the current Events repository implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Events
	 */
	private $events;

	/**
	 * The ID of the Event post created to model the split recurring Event.
	 *
	 * @since 6.0.0
	 *
	 * @var int|null
	 */
	private $first_post_id;

	/**
	 * The date, in string format, of the original Recurring Event last Occurrence.
	 *
	 * @since 6.0.0
	 *
	 * @var string|null
	 */
	private $original_last_date;

	/**
	 * The Recurrence rules and exclusions used by the original Recurring Event, in
	 * the format used in the `_EventRecurrenc` meta value.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed>|null
	 */
	protected $original_recurrence_meta;

	/**
	 * The number of Occurrences part of the right side of the split,
	 * including the split Occurrence.
	 *
	 * @var int
	 */
	private $original_right_side_count = 0;

	/**
	 * The page redirect object for block editor.
	 *
	 * @since 6.0.0
	 *
	 * @var Occurrence_Redirector
	 */
	private $occurrence_redirector;

	/**
	 * A reference to the current Requests factory/repository implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Requests
	 */
	private $requests;

	/**
	 * Upcoming constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Events                $events                A reference to the current Event repository implementation.
	 * @param Occurrence_Redirector $occurrence_redirector The page redirect object for block editor.
	 * @param Requests              $requests              A reference to the current Requests factory/repository
	 *                                                     implementation.
	 */
	public function __construct( Events $events, Occurrence_Redirector $occurrence_redirector, Requests $requests ) {
		$this->events = $events;
		$this->occurrence_redirector = $occurrence_redirector;
		$this->requests = $requests;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.0.0
	 */
	public function apply_before_identify_step( $post_id ) {
		if ( false === ( $post = $this->check_step_requirements( $post_id ) ) ) {
			return false;
		}

		$this->save_request_id( $post_id );

		$post_id = $post->ID;

		// Duplicate the original Event.
		$new_first_post = $this->events->duplicate(
			$post,
			// Keep the same post status as the original Event.
			[ 'post_status' => get_post_field( 'post_status', $post ) ]
		);

		if ( ! $new_first_post instanceof WP_Post ) {
			do_action( 'tribe_log', 'error', 'Failed to create Event on Upcoming update.', [
				'source'  => __CLASS__,
				'slug'    => 'duplicate-fail-on-upcoming-update',
				'post_id' => $post_id,
			] );

			return false;
		}

		$notice_provider = tribe( Notices_Provider::class );
		// Remove notices from watching the other events being updated, we will manually apply here.
		$notice_provider->unregister_ct1_notices();
		// Manually apply notices to the new event, since that is where we are landing.
		$notice_provider->on_updated_event( $new_first_post->ID );

		$this->first_post_id = $new_first_post->ID;

		$this->original_recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

		$this->set_until_limit_on_left_side( $post_id );

		$occurrence = $this->occurrence;

		if ( $occurrence->is_rdate ) {
			$next = Occurrence::where( 'post_id', '=', $occurrence->post_id )
				->order_by( 'start_date', 'ASC' )
				->where( 'start_date', '>=', $occurrence->start_date )
				->where( 'is_rdate', '=', 0 )
				->first();

			if ( $next instanceof Occurrence ) {
				// Move our start date to our recurring event's start date.
				$this->redirect_rdate_update_to_occurrence( $occurrence, $next, $this->first_post_id );
			}
		}

		$this->original_right_side_count = Occurrence::where( 'post_id', '=', $post_id )
		                                             ->order_by( 'start_date', 'ASC' )
		                                             ->where( 'start_date', '>=', $occurrence->start_date )
		                                             ->count();

		$current_last = Occurrence::where( 'post_id', '=', $post->ID )
		                          ->order_by( 'start_date', 'DESC' )
		                          ->first();

		if ( ! $current_last instanceof Occurrence ) {
			return false;
		}

		$this->original_last_date = $current_last->end_date;

		// Normalizes the request object meta data.
		$this->ensure_request_meta( $this->request );
		$this->ensure_delete_request_meta( $this->request );

		/*
		 * Assign upcoming Occurrences to the right side of the split to give it a chance to
		 * recycle them.
		 */
		$this->events->transfer_occurrences_from_to(
			$post_id,
			$this->first_post_id,
			'start_date >= %s',
			$occurrence->start_date
		);

		// Do not generate more Occurrences than required when the limit is a COUNT one.
		add_action( 'tec_events_custom_tables_v1_update_post_before', [ $this, 'limit_split_event' ] );

		// RDATEs need to split appropriately, since the events are split now.
		if ( $this->events->split_rdates(
			$post_id,
			$this->first_post_id,
			$occurrence->start_date,
			$this->request
		) ) {
			// Set flag to evaluate an occurrence being moved to a new ID.
			tribe( Controller::class )->set_should_redirect_occurrence( true );
		}

		$this->save_rest_request_recurrence_meta( $this->first_post_id, $this->request, true );

		if ( Occurrence::where( 'post_id', '=', $post_id )->count() === 1 ) {
			// If the original Event is left with one Occurrence we can make it a Single Event.
			$this->events->make_event_single( $post_id );
		}

		// The redirection will make it so the Recurrence rule is applied to the new Event.
		return $this->first_post_id;
	}

	/**
	 * Sets an UNTIL limit on the split Recurring Event if the Recurrence Rules have not been changed
	 * in respect to the original Event, to make sure a split where teh RRULE has a COUNT limit will
	 * not generate more Occurrences if that is not the user intention.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The
	 *
	 * @return false|array<string,mixed> Either the Split Recurring Event updated recurrence meta (in
	 *                                   the format used in the `_EventRecurrence` meta value), or `false`
	 *                                   if the update does not apply to the post.
	 */
	public function limit_split_event( $post_id ) {
		if ( (int) $post_id !== $this->first_post_id ) {
			// Not the post we're handling in this Update Controller.
			return false;
		}

		if ( ! isset( $this->first_post_id, $this->original_last_date, $this->original_recurrence_meta ) ) {
			// Pre-requirements are not met.
			return false;
		}

		// Need to use _EventRecurrence here (state is dirty, not propagated everywhere else).
		$current_recurrence  = Recurrence::from_recurrence( (array) get_post_meta( $this->first_post_id, '_EventRecurrence', true ) );
		$original_recurrence = Recurrence::from_recurrence( (array) $this->original_recurrence_meta );

		$limits_changed = ! $this->events->compare_interval_and_limit( $current_recurrence->to_event_recurrence(), $original_recurrence->to_event_recurrence() );

		if ( $limits_changed ) {
			// The split Recurring Event meta limit/interval was changed: respect that.
			return $current_recurrence->to_event_recurrence();
		}

		// If a count recurrence, make sure the right side count adjusts accordingly.
		if ( $original_recurrence->has_count_limit() ) {
			if ( ! $this->events->set_count_limit_on_event( $this->first_post_id, $this->original_right_side_count ) ) {
				do_action( 'tribe_log', 'error', 'Failed to set COUNT limit on split Event.', [
					'source'  => __CLASS__,
					'slug'    => 'set-count-limit-fail-on-upcoming-update',
					'post_id' => $post_id,
				] );
			}
		}

		/*
		 * Update the first occurrence of the right side in place, to make sure it will match
		 * the first Occurrence dates that would be created on the right side.
		 *
		 * In the context of the Block Editor, this will avoid having to redirect the browser
		 * to another Occurrence.
		 */
		if ( ! $this->events->update_occurrence_from_post( $this->occurrence->occurrence_id, $post_id ) ) {
			// While this is not ideal, it's something we can recover from, keep moving.
			do_action( 'tribe_log', 'error', 'Failed to update Occurrence from post.', [
				'source'  => __CLASS__,
				'slug'    => 'update-occurrence-fail-on-upcoming-update',
				'post_id' => $post_id,
			] );
		}

		return get_post_meta( $this->first_post_id, '_EventRecurrence', true );
	}

	/**
	 * Ensure the request object meta is set correctly in the context of a delete request.
	 *
	 * When a DELETE request hits the backend, an Occurrence meta values, the ones required
	 * to correctly update it in the database would be missing. When an Event is trashed, the
	 * data of the trashed Occurrences must be ensured to allow for a correct restore operation.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the request object to update.
	 *
	 * @return WP_REST_Request A reference to the request object, updated to include the Occurrence
	 *                         meta if required.
	 */
	private function ensure_delete_request_meta( WP_REST_Request $request ): WP_REST_Request {
		if ( ! $this->requests->is_delete_request( $request ) ) {
			return $request;
		}

		$request_meta = $this->events->get_request_meta( $request );

		if ( ! empty( $request_meta ) ) {
			// The request already has the meta, no need to add it again.
			$request->set_param( 'meta', $request_meta );
		} else {
			// The request does not have the meta, add it from the Occurrence being edited.
			$occurence_meta = [
				'_EventStartDate'    => $this->occurrence->start_date,
				'_EventEndDate'      => $this->occurrence->end_date,
				'_EventDuration'     => $this->occurrence->duration,
				'_EventStartDateUTC' => $this->occurrence->start_date_utc,
				'_EventEndDateUTC'   => $this->occurrence->end_date_utc,
			];
			$request->set_param( 'meta', $occurence_meta );
			$recurrence_meta = get_post_meta( $this->first_post_id, '_EventRecurrence', true );
			foreach ( [ 'rules', 'exclusions' ] as $type ) {
				if ( ! isset( $recurrence_meta[ $type ] ) ) {
					continue;
				}
				array_walk( $recurrence_meta[ $type ], function ( array &$rule ): void {
					$rule['EventStartDate'] = $this->occurrence->start_date;
					$rule['EventEndDate'] = $this->occurrence->end_date;
				} );
			}
			$request->set_param( 'recurrence', $recurrence_meta );
		}

		return $request;
	}

	/**
	 * Update the original Event Recurrence meta to add an UNTIL limit for the previous Occurrence.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The previous Event post ID.
	 *
	 * @return void The method does not return any value and will have the side-effect of adding an
	 *              UNTIL limit to the original Event (left side) of the split.
	 */
	private function set_until_limit_on_left_side( int $post_id ): void {
		$previous_occurrence = Occurrence::where( 'post_id', '=', $post_id )
			->order_by( 'start_date', 'DESC' )
			->where( 'start_date', '<', $this->occurrence->start_date )
			->where( 'is_rdate', '=', 0 )
			->first();

		if ( $previous_occurrence === null ) {
			// Try again to find an Occcurence, no matter if an RDATE or not.
			$previous_occurrence = Occurrence::where( 'post_id', '=', $post_id )
				->order_by( 'start_date', 'DESC' )
				->where( 'start_date', '<', $this->occurrence->start_date )
				->first();
		}
		if (
			$previous_occurrence instanceof Occurrence
			&& ! $this->events->set_until_limit_on_event( $post_id, $previous_occurrence->start_date )
		) {
			do_action( 'tribe_log', 'error', 'Failed to set UNTIL limit on original Event.', [
				'source'  => __CLASS__,
				'slug'    => 'set-until-limit-fail-on-upcoming-update',
				'post_id' => $post_id,
			] );
		}
	}
}

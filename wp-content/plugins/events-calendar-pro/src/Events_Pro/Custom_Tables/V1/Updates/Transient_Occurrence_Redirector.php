<?php
/**
 * Handles the redirection of a removed Occurrence to the Event post
 * ID that originally created it.
 *
 * @since   6.0.0
 *
 * @pacakge TEC\Events_Pro\Custom_Tables\V1\Updates
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use Tribe__Date_Utils as Dates;

/**
 * Class Transient_Occurrence_Redirector
 *
 * @since   6.0.0
 *
 * @pacakge TEC\Events_Pro\Custom_Tables\V1\Updates
 */
class Transient_Occurrence_Redirector {

	/**
	 * A reference to the current provisional post handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_Post
	 */
	private $provisional_post;
	/**
	 * A reference to the current provisional post ID generator.
	 *
	 * @since 6.0.0
	 *
	 * @var ID_Generator
	 */
	private $id_generator;

	/**
	 * Transient_Occurrence_Redirector constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Provisional_Post $provisional_post A reference to the current provisional post handler
	 */
	public function __construct(Provisional_Post  $provisional_post, ID_Generator $id_generator) {
		$this->provisional_post = $provisional_post;
		$this->id_generator = $id_generator;
	}

	/**
	 * Fetch the correct occurrence ID for situations where occurrences were replaced.
	 *
	 * @since 6.0.0
	 *
	 * @param int|string $provisional_id The Occurrence provisional ID to get the redirection
	 *                                  ID for.
	 *
	 * @return array{string,int|string}|null Either an array defining the redirect ID and start
	 *                                       date of the redirected Occurrence, or `null`.
	 */
	public function get_redirect_data( $provisional_id ) {
		$value = get_transient( "_tec_events_occurrence_{$provisional_id}_redirect" );

		if ( empty( $value ) ) {
			return null;
		}

		return array_replace( [ 'redirect_id' => null, 'start_date' => null, 'force_redirect' => false ], $value );
	}

	/**
	 * This should be used to set a transient which is solving the issue of updating occurrences
	 * that will be replaced with a new occurrence and resolves the ID to appropriate one.
	 *
	 * @since 6.0.0
	 *
	 * @param int|string  $to_id          The ID that should be resolved / redirected to.
	 * @param int|string  $from_id        The original ID that needs to be redirected to a new ID.
	 * @param string|null $start_date     The start date of the Occurrence to redirect.
	 * @param bool        $force_redirect If we force the redirect on page, skipping the UI prompt.
	 *
	 * @return bool True if the value was set, false otherwise.
	 *
	 * @todo  add daily cleanup of transients
	 */
	public function set_redirected_id( $to_id, $from_id, $start_date = null, $force_redirect = false ) {
		return set_transient(
			"_tec_events_occurrence_{$from_id}_redirect",
			[ 'redirect_id' => $to_id, 'start_date' => $start_date, 'force_redirect' => $force_redirect ],
			6 * HOUR_IN_SECONDS
		);
	}

	/**
	 * Removes the redirect data from the WP transient.
	 *
	 * @since 6.0.0
	 *
	 * @param numeric $from_id The occurrence ID this redirect data was bound to.
	 *
	 * @return bool
	 */
	public function remove_redirect_transient( $from_id ) {
		return delete_transient( "_tec_events_occurrence_{$from_id}_redirect" );
	}

	/**
	 * Provides the Occurrence redirect information following an update that might
	 * have removed the originally edited Occurrence.
	 *
	 * @since 6.0.0
	 *
	 * @param int      $object_id     The ID of the post to provide the Occurrence redirect data for.
	 * @param int|null $event_post_id The ID of the Event post the Occurrence belonged to when the state
	 *                                was hydrated in the Blocks Editor.
	 *
	 * @return object<string,string>|null The filtered Occurrence redirect data in object format, or `null`
	 *                                    if no redirection is required.
	 */
	public function get_occurrence_redirect_response( $object_id, $event_post_id = null ) {
		if ( ! $this->provisional_post->is_provisional_post_id( $object_id ) ) {
			// Not a provisional ID: redirect should not happen.
			return null;
		}

		$redirect_data = $this->get_redirect_data( $object_id );
		$occurrence_id = $this->id_generator->unprovide_id( $object_id );

		if ( empty( $redirect_data ) || empty( $redirect_data['redirect_id'] ) ) {
			return $this->redirect_broken_out_occurrence( $occurrence_id, $object_id, $event_post_id );
		}

		$this->remove_redirect_transient( $object_id );

		if ( ! $redirect_data['force_redirect'] && Occurrence::where( 'occurrence_id', '=', $occurrence_id )->count() === 1 ) {
			//  The Occurrence has been restored before the transient expired, do not redirect.
			return null;
		}

		// By default, redirect to the real Event ID, the first Occurrence.
		$redirect_id = $redirect_data['redirect_id'];
		$post_id     = Occurrence::normalize_id( $redirect_data['redirect_id'] );
		$location    = get_edit_post_link( $redirect_id, 'internal' );
		$occurrence  = Occurrence::find( $this->id_generator->unprovide_id( $redirect_id ) );

		// Setup data for the redirect prompt.
		$first_start_date = get_post_meta( $post_id, '_EventStartDate', true );
		$redirect_date    = $occurrence instanceof Occurrence ?
			Dates::build_date_object( $occurrence->start_date )
			: Dates::build_date_object( $first_start_date );
		$same_date        = false;

		// If the destination date is the same, we will force redirect.
		if ( $redirect_data['start_date'] ) {
			$from_date = Dates::build_date_object( $redirect_data['start_date'] );
			// Ignore time, we only care about same day match
			$same_date = $from_date->format( 'Y-m-d' ) === $redirect_date->format( 'Y-m-d' );
		}

		// If we are forcing the redirect, we are skipping the dialog so no more data necessary.
		if ( $redirect_data['force_redirect'] || $same_date ) {
			return (object) [
				'location'      => $location,
				'forceRedirect' => true,
			];
		}

		$title = _x(
			'Recurring event updated',
			'The title of the dialog that will inform the user the browser will be redirected to a new Occurrence edit screen.',
			'tribe-events-calendar-pro'
		);
		$count = Occurrence::where( 'post_id', '=', $post_id )->count();

		/* translators: %1$d is the number of occurrences updated, %2$s is the date of the target Occurrence. */
		$message_template = _x(
			'%1$d occurrences of this event have been updated. You will be redirected to the occurrence on %2$s.',
			'The template of the message displayed to inform the user of the redirection to a new Occurrence.',
			'tribe-events-calendar-pro'
		);

		$format               = tribe_get_date_option( 'dateWithYearFormat', Dates::DBDATEFORMAT );
		$date                 = $redirect_date->format( $format );
		$message              = sprintf( $message_template, $count, $date );
		$confirm_button_label = _x( 'Okay', 'The label of the confirm button in the Occurrence redirect dialog.', 'tribe-events-calendar-pro' );

		return (object) [
			'location'           => $location,
			'title'              => $title,
			'message'            => $message,
			'confirmButtonLabel' => $confirm_button_label,
		];
	}

	/**
	 * Redirect to a broken out Occurrence if the Event post ID changed.
	 *
	 * @since 6.0.11
	 *
	 * @param int      $occurrence_id The ID of the Occurrence to redirect to.
	 * @param int      $object_id     The ID of the provisional Occurrence post to redirect to.
	 * @param int|null $event_post_id The ID of the Event post the Occurrence belonged do, from the Blocks Editor state.
	 *
	 * @return object|null The redirect data in object format, or `null` if no redirection is required.
	 */
	private function redirect_broken_out_occurrence( int $occurrence_id, int $object_id, ?int $event_post_id ): ?object {
		if ( ! $event_post_id ) {
			// No previous Event post, do not redirect.
			return null;
		}

		$occurrence = Occurrence::find( $occurrence_id );

		if (
			$occurrence instanceof Occurrence
			&& ! $occurrence->has_recurrence
			&& $occurrence->post_id !== $event_post_id
		) {
			/*
			 * The Event post ID changed and the Occurrence is no longer part of a recurrence:
			 * redirect to the broken out Occurrence to force a page refresh and avoid Block Editor
			 * running on bad state.
			 */
			return (object) [
				'location'      => get_edit_post_link( $object_id, 'raw' ),
				'forceRedirect' => true,
			];
		}

		return null;
	}
}

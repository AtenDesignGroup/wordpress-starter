<?php


class Tribe__Events__Pro__Recurrence__Events_Saver {

	/**
	 * @var int
	 */
	protected $event_id;
	/**
	 * @var bool|int
	 */
	protected $updated;

	/**
	 * @var Tribe__Events__Pro__Recurrence__Exclusions
	 */
	protected $exclusions;

	/**
	 * Tribe__Events__Pro__Recurrence__Events_Saver constructor.
	 *
	 * @param int                                             $event_id The post ID of the event being saved
	 * @param bool|int                                        $updated  The meta_id of the post meta containing the
	 *                                                                  event recurrence meta information.
	 * @param Tribe__Events__Pro__Recurrence__Exclusions|null $exclusions
	 */
	public function __construct( $event_id, $updated, Tribe__Events__Pro__Recurrence__Exclusions $exclusions = null ) {
		$this->event_id        = $event_id;
		$this->updated         = $updated;
		$event_timezone_string = Tribe__Events__Timezones::get_event_timezone_string( $event_id );
		$this->exclusions = $exclusions ?
			$exclusions :
			Tribe__Events__Pro__Recurrence__Exclusions::instance( $event_timezone_string );
	}

	/**
	 * Do the actual work of saving a recurring series of events
	 *
	 * @since ?.?.?
	 * @since 5.5.0 Included a conditional for the processing right away.
	 *
	 * @param bool $process If we will process a small batch of events right away or way for the async to kick in.
	 *
	 * @return bool
	 */
	public function save_events( $process = true ) {
		$existing_instances = Tribe__Events__Pro__Recurrence__Children_Events::instance()->get_ids( $this->event_id );

		$recurrences = Tribe__Events__Pro__Recurrence__Meta::get_recurrence_for_event( $this->event_id );

		$to_create             = array();
		$exclusions            = array();
		$to_update             = array();
		$to_delete             = array();
		$possible_next_pending = array();
		$earliest_date         = strtotime( Tribe__Events__Pro__Recurrence__Meta::$scheduler->get_earliest_date() );
		$latest_date           = strtotime( Tribe__Events__Pro__Recurrence__Meta::$scheduler->get_latest_date() );
		$rule_count            = 0;

		foreach ( $recurrences['rules'] as &$recurrence ) {
			if ( ! $recurrence ) {
				continue;
			}
			$recurrence->setMinDate( $earliest_date );
			$recurrence->setMaxDate( $latest_date );
			$to_create = array_merge( $to_create, $recurrence->getDates( $rule_count ) );

			if ( $recurrence->constrainedByMaxDate() !== false ) {
				$possible_next_pending[] = $recurrence->constrainedByMaxDate();
			}
			$rule_count++;
		}

		$to_create = tribe_array_unique( $to_create );

		// find days we should exclude
		foreach ( $recurrences['exclusions'] as &$recurrence ) {
			if ( ! $recurrence ) {
				continue;
			}

			$recurrence->setMinDate( $earliest_date );
			$recurrence->setMaxDate( $latest_date );

			$exclusions = array_merge( $exclusions, $recurrence->getDates() );
		}

		// make sure we don't create excluded dates
		$exclusions = tribe_array_unique( $exclusions );
		$to_create  = $this->exclusions->remove_exclusions( $to_create, $exclusions );

		if ( $possible_next_pending ) {
			update_post_meta(
				$this->event_id,
				'_EventNextPendingRecurrence',
				date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, min( $possible_next_pending ) )
			);
		}

		foreach ( $existing_instances as $instance ) {
			$start_date = strtotime( get_post_meta( $instance, '_EventStartDate', true ) . '+00:00' );
			$end_date   = strtotime( get_post_meta( $instance, '_EventEndDate', true ) . '+00:00' );
			$duration   = $end_date - $start_date;

			$existing_date_duration = array(
				'timestamp' => $start_date,
				'duration' => $duration,
			);

			$found              = array_search( $existing_date_duration, $to_create );
			$should_be_excluded = in_array( $existing_date_duration, $exclusions );

			if ( false === $found || false !== $should_be_excluded ) {
				$to_delete[ $instance ] = $existing_date_duration;
			} else {
				$to_update[ $instance ] = $to_create[ $found ];
				unset( $to_create[ $found ] ); // so we don't re-add it
			}
		}

		$commit_payload = [
			'to_create'  => $to_create,
			'to_update'  => $to_update,
			'to_delete'  => $to_delete,
			'exclusions' => $exclusions,
		];

		/**
		 * Whether the Event occurrence should be updated or not by the default behavior.
		 *
		 * Returning a non `null` value will override the default behavior.
		 *
		 * @since 6.0.0
		 *
		 * @param bool                $commit         Whether the Event Occurrences should be updated using the default behavior
		 *                                            or not.
		 * @param int                 $post_id        The ID of the Event.
		 * @param array<string,array> $commit_payload The payload that will be used to update the Event Occurrences.
		 */
		$commit = apply_filters( 'tec_events_pro_recurrence_update_commit', null, $this->event_id, $commit_payload );

		if ( $commit !== null ) {
			return (bool) $commit;
		}

		// Store the list of instances to create/update/delete etc for future processing
		$queue = new Tribe__Events__Pro__Recurrence__Queue( $this->event_id );
		$queue->update( $to_create, $to_update, $to_delete, $exclusions );

		if ( $process ) {
			// ...but don't wait around, process a small initial batch right away
			Tribe__Events__Pro__Main::instance()->queue_processor->process_batch( $this->event_id );
		}

		return true;
	}
}

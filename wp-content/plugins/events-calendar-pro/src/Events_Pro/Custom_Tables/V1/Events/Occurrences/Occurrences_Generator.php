<?php
/**
 * Handles the generation of Occurrences in the context of the ECP plugin.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Occurrences
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Occurrences;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Generator;
use RRule\RSet;
use TEC\Events\Custom_Tables\V1\Events\Occurrences\Occurrences_Generator as TEC_Occurrences_Generator;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Models\Occurrence as ECP_Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\RRule\RSet_Wrapper;
use TEC\Events\Custom_Tables\V1\Events\Occurrences\Max_Recurrence;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

/**
 * Class Occurrences_Generator
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Occurrences
 */
class Occurrences_Generator {
	/**
	 * A reference to the TEC Occurrences Generator that will be proxied
	 * for any Single Event requests.
	 *
	 * @since 6.0.0
	 *
	 * @var TEC_Occurrences_Generator
	 */
	private $tec_occurrences_generator;

	/**
	 * Occurrences_Generator constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param TEC_Occurrences_Generator $tec_occurrences_generator A reference to the TEC Occurrences Generator that
	 *                                                             will be proxied for any Single Event requests.
	 */
	public function __construct( TEC_Occurrences_Generator $tec_occurrences_generator ) {
		$this->tec_occurrences_generator = $tec_occurrences_generator;
	}

	/**
	 * Provides a Generator that will yield Occurrences generated based on the
	 * Event and the provided, or pre-existing, RSET specification.
	 *
	 * Note: to allow the direct use of this method to generate Occurrences with ECP
	 * awareness and functionality, the method will proxy to the TEC Generator provider
	 * when dealing with Events that have not RSET input or definition.
	 *
	 * @since 6.0.0
	 *
	 * @param Event $event         A reference to the Event Model instance to generate
	 *                             the Occurrences for.
	 * @param mixed $args,...      A variadic set of arguments to govern the generation
	 *                             of the Occurrences for the Event.
	 *
	 * @return Generator A reference to a Generator that will yield the Occurrences for the RSET combination.
	 */
	public function get_occurrences_generator( Event $event, ...$args ) {
		list( $rset, $duration ) = array_replace( [ null, null ], $args );

		if ( empty( $rset ) ) {
			// Use the Single Event generator if the RSET is empty and if there are no pre-existing Occurrences.
			if ( empty( $event->rset ) && Occurrence::where( 'post_id', '=', $event->post_id )->count() <= 1 ) {
				// There is no RSET information in the input and in the Event: let TEC handle it.
				return $this->tec_occurrences_generator->generate_from_event( $event );
			}

			return $this->generate_from_event( $event );
		}

		$generator = $this->generate_from_rset( $event, $rset, null, $duration );
		$event->update( [ 'rset' => $rset ] );
		$event->refresh();

		return $generator;
	}

	/**
	 * Generate all the occurrences for this event, without using large chunks of memory in the process.
	 *
	 * @since 6.0.0
	 *
	 * @param Event $event The Event model instance.
	 *
	 * @return Generator<Occurrence>|void Either the next row generated for the Event or void to indicate the Event is
	 *                                    not in a state where its Occurrences can be generated.
	 */
	public function generate_from_event( Event $event ) {
		// This one has not been saved yet.
		if ( empty( $event->event_id ) ) {
			return;
		}

		if ( empty( $event->rset ) ) {
			yield $this->get_single_event_row( $event );
		}

		$timezone       = Timezones::build_timezone_object( $event->timezone );
		$starting_point = Dates::immutable( Dates::build_date_object( $event->start_date, $timezone ) );

		$result = $this->generate_from_rset( $event, $event->rset, $starting_point );

		while ( $result instanceof Generator && $result->valid() ) {
			yield $result->current();
			$result->next();
		}
	}

	/**
	 * Generates Occurrences for an Event using a custom RSET string, start date and timezone.
	 *
	 * @since 6.0.0
	 *
	 * @param Event             $event            A reference to the Event model instance.
	 * @param string|RSET       $rset             The RSET definition, in the iCalendar format.
	 * @param DateTimeInterface $generation_start The moment the RSET should start generating Occurrences at.
	 * @param int|null          $duration         The duration, in seconds, of the Occurrences to generate from the
	 *                                            RSET, or `null` to use the Event duration.
	 *
	 * @return Generator<Occurrence>|void Either the next row generated for the Event or void to indicate the Event is
	 *                                    not in a state where its Occurrences can be generated.
	 */
	public function generate_from_rset(
		Event $event,
		$rset,
		DateTimeInterface $generation_start = null,
		$duration = null
	) {
		try {
			$timezone = Timezones::build_timezone_object( $event->timezone );
			if ( $generation_start === null ) {
				$generation_start = new DateTimeImmutable( $event->start_date, $timezone );

				$default_start = null;
				if ( $rset instanceof RSet_Wrapper && false === strpos( (string) $rset, 'DTSTART;' ) ) {
					$default_start = $generation_start;
				} elseif ( is_string( $rset ) && false === strpos( $rset, 'DTSTART;' ) ) {
					$default_start = $generation_start;
				}
			} else {
				$default_start = $generation_start;
			}
			$rset = $rset instanceof RSet_Wrapper ? $rset : new RSet_Wrapper( $rset, $default_start );
		} catch ( Exception $e ) {
			do_action( 'tribe_log', 'error', 'Failed to generate Occurrences from Event RSET.', [
				'source'  => __CLASS__,
				'slug'    => 'occurrence-generation-from-rset-fail',
				'error'   => $e->getMessage(),
				'post_id' => $event->post_id,
				'rset'    => $event->rset,
			] );

			// Return stopping the Generator at step 0: no Occurrences.
			return;
		}

		// Point in time where all the events are generated into the future.
		$utc = new DateTimeZone( 'UTC' );
		$months = $this->get_filtered_months_in_advance_limit( $event );
		$generation_start = Dates::immutable( $generation_start, $utc );
		$this_duration = $rset->get_duration( $duration );
		$duration = $this_duration === null ? $event->duration : $this_duration;
		$limit = null;

		if ( $rset->isInfinite() ) {
			$time = Dates::immutable( $event->start_date, $event->timezone );
			// Cannot work with infinite RSETs: constrain the generation to the months in advance limit from today.
			$limit = Dates::build_date_object( 'today', $generation_start->getTimezone() )
				// Set the time to the event start time.
				->setTime( $time->format( 'H' ), $time->format( 'i' ), $time->format( 's' ) )
				// The actual limit is years in advance to the original start date of the event...
				->add( new DateInterval( "P{$months}M" ) )
				// ...plus the duration of the event so the limit can be set a [start, limit] instead of [start, limit).
				->add( new DateInterval( "PT{$duration}S" ) );
		}

		$sequence     = ECP_Occurrence::get_sequence( $event->post_id );
		$new_sequence = $sequence + 1;
		$format = Dates::DBDATETIMEFORMAT;
		$rdate_tuples = array_map( static function ( $occurrence ) use ( $format ): array {
			return $occurrence instanceof \TEC\Events_Pro\Custom_Tables\V1\RRule\Occurrence ?
				[
					$occurrence->format_start( $format ),
					$occurrence->format_end( $format )
				] :
				[ $occurrence->format( $format ), $occurrence->format( $format ) ];
		}, $rset->getDates() );

		/** @var DateTimeInterface $occurrence */
		foreach ( $rset as $occurrence ) {
			// This reached the limit of generated recurrences
			if ( $limit && $occurrence > $limit ) {
				return;
			}

			// By default, let's assume the Occurence is not an RDATE.
			$is_rdate = false;

			if ( ! $occurrence instanceof \TEC\Events_Pro\Custom_Tables\V1\RRule\Occurrence ) {
				$start = DateTimeImmutable::createFromMutable( $occurrence );
				$end = $start->add( new DateInterval( "PT{$duration}S" ) );
			} else {
				$start = $occurrence->start();
				$end = $occurrence->end();
				$occurrence_tuple = [ $occurrence->format_start( $format ), $occurrence->format_end( $format ) ];
				$is_rdate = in_array( $occurrence_tuple, $rdate_tuples, true );
			}

			$row      = new Occurrence( [
				'event_id'       => $event->event_id,
				'post_id'        => $event->post_id,
				'start_date'     => $occurrence,
				'start_date_utc' => $start->setTimezone( $utc ),
				'end_date'       => $end,
				'end_date_utc'   => $end->setTimezone( $utc ),
				'duration'       => $end->getTimestamp() - $start->getTimestamp(),
				'updated_at'     => new DateTime( 'now', new DateTimeZone( 'utc' ) ),
				'has_recurrence' => true,
				'sequence' => $new_sequence,
				'is_rdate' => $is_rdate,
			] );

			$row->hash = $row->generate_hash();

			yield $row;
		}
	}

	/**
	 * Returns the filtered number of months to generate Occurrences in advance for a Recurring Events.
	 *
	 * The method wraps the filtering and sanitization logic not allowing any value that is not between 1 month and 10
	 * years.
	 *
	 * @since 6.0.0
	 *
	 * @param Event $event
	 *
	 * @return int The number of months to generate Occurrences in advance for.
	 */
	private function get_filtered_months_in_advance_limit( Event $event ) {
		$months = (int) floor( absint( tribe_get_option( 'recurrenceMaxMonthsAfter', Max_Recurrence::get_recurrence_max_months_default() ) ) );

		/**
		 * Filters the number of months in advance Occurrences should be generated for.
		 *
		 * Note: the number has to be a value between 1 month and 10 years; invalid value will result
		 * in the default value of 24 months being used.
		 *
		 * @since 6.0.0
		 *
		 * @param int $months  The number of months in advance to generate Occurrences for.
		 * @param int $post_id The Event post ID.
		 */
		$months = apply_filters( 'tec_events_pro_custom_tables_v1_editor_occurrences_months_in_advance', $months,
			$event->post_id );

		// Prevent the number of months to be more than 10 years.
		return (int) min( 120, max( absint( $months ), 1 ) );
	}

	/**
	 * Produces an Occurrence model instance for the single Event row applying to it
	 * the extra fields used by ECP.
	 *
	 * @since 6.0.0
	 *
	 * @param Event $event A reference to the Event model instance to produce the single
	 *                     Occurrence model instance for.
	 *
	 * @return Occurrence A reference to an Occurrence model instance representing the single
	 *                    Event row, its sequence updated.
	 */
	private function get_single_event_row( Event $event ) {
		$row           = tribe( TEC_Occurrences_Generator::class )->get_single_event_row( $event );
		$sequence      = ECP_Occurrence::get_sequence( $event->post_id );
		$row->sequence = $sequence + 1;

		return $row;
	}
}

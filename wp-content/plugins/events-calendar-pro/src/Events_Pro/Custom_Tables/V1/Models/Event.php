<?php
/**
 * Provides the code required to extend the base Event Model using the extensions API.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Models
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Models;

use Exception;
use TEC\Events_Pro\Custom_Tables\V1\Events\Converter\From_Event_Recurrence_Converter;
use TEC\Events_Pro\Custom_Tables\V1\Models\Formatters\RSet_Formatter;
use TEC\Events_Pro\Custom_Tables\V1\Models\Validators\Valid_RSet;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Event_Recurrence;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

/**
 * Class Event
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Models
 */
class Event {
	use With_Event_Recurrence;

	/**
	 * Retrieves the wp_posts id of the Series a post is connected to.
	 *
	 * @since 6.0.0
	 * @param $event_id
	 *
	 * @return int
	 */
	public static function get_series_id( $event_id ) {
		$related_series = Series_Relationship::where( 'event_post_id', '=', $event_id )->get();
		$series_map     = array_map(
			static function ( Series_Relationship $relationship ) {
				return $relationship->series_post_id;
			},
			$related_series
		);

		return (int) array_shift( $series_map );
	}

	/**
	 * Checks if the an id refers to an object that is part of a Series.
	 *
	 * @since 6.0.0
	 *
	 * @param $object_id
	 *
	 * @return bool
	 */
	public static function is_part_of_series( $object_id ) {

		$event_id = tribe( Occurrence::class )->normalize_occurrence_post_id( $object_id );

		if ( $object_id !== $event_id ) {
			return true;
		}

		return static::get_series_id( $event_id ) > 0;
	}

	/**
	 * Extends the base Event Model using the extensions API.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array<string,mixed>> $extensions A map of the current Model
	 *                                                      extensions.
	 *
	 * @return array<string,array<string,mixed>> The filtered extensions map.
	 */
	public function extend( array $extensions = [] ) {
		return wp_parse_args( [
			'validators'  => [
				'rset' => Valid_RSet::class,
			],
			'formatters'  => [
				'rset' => RSet_Formatter::class,
			],
			'hashed_keys' => [
				'rset',
			],
			'methods'     => [
				'has_recurrence' => function () {
					/** @var Event $this Bound at run time to the Closure. */
					return ! empty( $this->rset );
				}
			],
		], $extensions );
	}

	/**
	 * Filters the Event post data adding the ECP data to it.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $data     The Event post data, as produced by The Events Calendar and
	 *                                      previous filtering functions.
	 * @param int                 $event_id The Event post ID.
	 *
	 * @return array<string,mixed> The filtered Event post data.
	 */
	public function add_event_post_data( array $data, $event_id ) {
		$recurrence = get_post_meta( $event_id, '_EventRecurrence', true );

		$recurrence = $this->add_off_pattern_flag_to_meta_value( $recurrence, $event_id );

		if (
			empty( $recurrence['rules'] )
			|| ! isset( $data['start_date'], $data['end_date'], $data['timezone'], $data['duration'] )
		) {
			$data['rset'] = '';
		} else {
			try {
				$tz                        = Timezones::build_timezone_object( get_post_meta( $event_id, '_EventTimezone', true ) );
				$dtstart                   = Dates::immutable( get_post_meta( $event_id, '_EventStartDate', true ), $tz );
				$dtend                     = Dates::immutable( get_post_meta( $event_id, '_EventEndDate', true ), $tz );
				$from_recurrence_converter = new From_Event_Recurrence_Converter( $dtstart, $dtend );
				$converted_rset            = (array) $from_recurrence_converter->convert_to_rset(
					$data['start_date'],
					$data['end_date'],
					$data['timezone'],
					$recurrence
				);

				if ( count( $converted_rset ) ) {
					$data ['rset'] = $this->join_converted_rset( $converted_rset );
				} else {
					do_action( 'tribe_log', 'error', __CLASS__, [
						'message'    => 'Event RSET conversion empty.',
						'post_id'    => $event_id,
						'recurrence' => $recurrence
					] );
					$data ['rset'] = '';
				}
			} catch ( Exception $e ) {
				/**
				 * Filters whether the conversion of `_EventRecurrence` format meta to RSET string
				 * should fail silently or not.
				 *
				 * @since 6.0.1
				 *
				 * @param bool $throw Whether the conversion should throw an exception or not.
				 */
				$throw = apply_filters( 'tec_events_pro_custom_tables_v1_throw_on_rset_conversion', true );

				if ( $throw ) {
					throw $e;
				} else {
					do_action( 'tribe_log', 'error', __CLASS__, [
						'message'    => 'Event RSET conversion failed.',
						'post_id'    => $event_id,
						'error'      => $e->getMessage(),
						'recurrence' => $recurrence
					] );

					$data['rset'] = '';
				}
			}
		}

		return $data;
	}

	/**
	 * Joins the pieces of the converted RSET into a string format RSET definition.
	 *
	 * @since 6.0.0
	 *
	 * @param string|array $rset           Either a converted RSET in map format (from durations
	 *                                     to RRULEs/RDATEs) or in string format. The second will
	 *                                     not be converted.
	 *
	 * @return string The joined converted RSET definition, or an empty string if no line in the RSET
	 *                is providing a DTSTART definition.
	 */
	private function join_converted_rset( $rset ) {
		if ( is_string( $rset ) ) {
			return $rset;
		}

		$joined  = '';
		$dtstart = null;
		foreach ( $rset as $rset_line ) {
			if ( null === $dtstart && 0 === strpos( $rset_line, 'DTSTART' ) ) {
				list( $dtstart ) = explode( "\n", $rset_line );
				$joined .= $rset_line;
			} elseif ( $dtstart ) {
				$joined .= str_replace( [ $dtstart, $dtstart . "\n" ], [ '', '' ], $rset_line );
			}
		}

		return $dtstart ? $joined : '';
	}
}

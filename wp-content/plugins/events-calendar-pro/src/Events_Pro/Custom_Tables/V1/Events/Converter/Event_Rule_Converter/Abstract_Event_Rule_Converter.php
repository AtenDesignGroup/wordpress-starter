<?php
/**
 * The base class providing common methods for the converters.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;

use DateTime;
use TEC\Events_Pro\Custom_Tables\V1\Events\Converter\From_Event_Recurrence_Converter;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Ical_Strings;

/**
 * Class Abstract_Event_Rule_Converter.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;
 */
abstract class Abstract_Event_Rule_Converter {
	use With_Ical_Strings;

	/**
	 * A reference to the `_EventRecurrence` to RSET converter.
	 *
	 * @since 6.0.0
	 *
	 * @var From_Event_Recurrence_Converter
	 */
	protected $converter;

	/**
	 * The rule to convert, in the format used by the `_EventRecurrence` meta value.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed>
	 */
	protected $rule;

	/**
	 * Abstract_Event_Rule_Converter constructor.
	 *
	 * since 6.0.0
	 *
	 * @param From_Event_Recurrence_Converter $converter A reference to the `_EventRecurrence` to RSET converter.
	 * @param array<string,mixed>             $rule      The rule to convert, in the format used by the
	 *                                                   `_EventRecurrence` meta value.
	 */
	public function __construct( From_Event_Recurrence_Converter $converter, array $rule ) {
		$this->converter = $converter;
		$this->rule      = $rule;
	}

	/**
	 * Converts a daily rule from the format used in the EventRecurrence meta to the one used by iCal.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $is_exrule Flag to handle parser for exrule/exdate.
	 *
	 * @return string The iCal version of the rule.
	 * @throws \Exception If the timezone building fails.
	 */
	abstract public function convert_to_rrule( bool $is_exrule = false ): string;

	/**
	 * Adds the DTSTART and DTEND components to the converted iCalendar format string.
	 *
	 * Note that the method will not replace an existing DTSTART and DTEND if they are already present
	 * in the iCalendar string.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule        The recurrence rule to add the DSTART and DTEND
	 *                                         components to.
	 * @param string              $ical_string The iCalendar format string to add the DTSTART and
	 *                                         DTEND components to.
	 *
	 * @return string The updated iCalendar format string.
	 *
	 * @throws \Exception If there's any issue building the date objects required.
	 */
	protected function prepend_dtstart_dtend( array $rule, string $ical_string ): string {
		if ( ! isset( $rule['EventStartDate'], $rule['EventEndDate'] ) ) {
			// If the start and end dates are not set, then there's nothing to do.
			return $ical_string;
		}

		$is_same_time = isset( $rule['custom']['same-time'] ) && tribe_is_truthy( $rule['custom']['same-time'] );

		$timezone = $this->converter->get_timezone();
		$string_dtstart = $this->parse_string_dt_attribute( $ical_string, 'DTSTART' );
		$string_dtend = $this->parse_string_dt_attribute( $ical_string, 'DTEND' );
		$dtstart_date = ( new DateTime( $rule['EventStartDate'], $timezone ) );
		$dtend_date = ( new DateTime( $rule['EventEndDate'], $timezone ) );

		if ( $string_dtstart !== null ) {
			if ( $string_dtend !== null ) {
				// No need to update anything.
				return $ical_string;
			}

			// There is a DTSTART, but not a DTEND: work out the DTEND from the DSTART and the RRULE diff.
			$rule_diff = $dtstart_date->diff( $dtend_date );
			$dtstart_date = $string_dtstart;
			$dtend_date = $string_dtstart->add( $rule_diff );
		}

		if ( ! $is_same_time ) {
			// Read the custom start time and set it on the DTSTART.
			$diff_start = ( new DateTime( $rule['custom']['start-time'] ) );
			$diff_start_args = [ $diff_start->format( 'H' ), $diff_start->format( 'i' ), $diff_start->format( 's' ) ];
			$dtstart_date->setTime( ...$diff_start_args );

			// Read the custom end time and set it on the DTEND taking the end-day difference into account.
			$dtend_date = clone $dtstart_date;
			$end_day = $rule['custom']['end-day'] ?? 0;

			if ( is_numeric( $end_day ) && (int) $end_day > 0 ) {
				// Add as many days as the end-day difference.
				$dtend_date->add( new \DateInterval( "P{$end_day}D" ) );
			}

			// Set the custom end time on the DTEND.
			$diff_end = ( new DateTime( $rule['custom']['end-time'] ) );
			$diff_end_args = [ $diff_end->format( 'H' ), $diff_end->format( 'i' ), $diff_end->format( 's' ) ];
			$dtend_date->setTime( ...$diff_end_args );
		}
		$ical_string = $this->remove_dtstart_dtend( $ical_string );
		$dtstart_string = $this->build_dstart_string( $dtstart_date );
		$dtend_string = $this->build_dtend_string( $dtend_date );

		return implode( "\n", [ $dtstart_string, $dtend_string, $ical_string ] );
	}
}
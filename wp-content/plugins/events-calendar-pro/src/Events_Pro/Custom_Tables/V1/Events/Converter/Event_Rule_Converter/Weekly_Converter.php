<?php
/**
 * Converts a weekly rule from the format used in the `_EventRecurrence` meta to the RSET one.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;

use DateTimeZone;
use TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Pro__Date_Series_Rules__Week as Legacy_Weekly_Rule;

/**
 * Class Weekly_Converter.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;
 */
class Weekly_Converter extends Abstract_Event_Rule_Converter {
	/**
	 * Whether to append the `BYDAY` entry to weekly rules or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $weekly_add_byday = true;

	/**
	 * @inheritDoc
	 */
	public function convert_to_rrule( bool $is_exrule = false ): string {
		$rule = $this->rule;

		switch ( $rule['end-type'] ) {
			default:
			case 'Never':
				$converted = $this->convert_ends_never( $rule );
				break;
			case 'After':
				$converted = $this->convert_ends_after( $rule );
				break;
			case 'On':
				$converted = $this->convert_ends_on( $rule );
				break;
		}

		$converted = $this->align_rrule_with_legacy( $converted, $rule );

		if ( ! $is_exrule ) {
			$converted = $this->prepend_dtstart_dtend( $rule, $converted );
		}

		return $converted;
	}

	/**
	 * Returns the BYDAY fragment of the RRULE.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The recurrence rule in the format
	 *                                  used in the `_EventRecurrence` meta value.
	 *
	 * @return string|null The BYDAY fragment of the RRULE.
	 */
	private function build_byday_string( array $rule ) {
		if ( empty( $rule['custom']['week']['day'] ) || ! is_array( $rule['custom']['week']['day'] ) ) {
			return null;
		}
		$mapped_days = array_filter(
			array_map(
				[ From_Event_Rule_Converter::class, 'day_number_to_abbr' ],
				(array) $rule['custom']['week']['day']
			)
		);

		return sprintf( 'BYDAY=%s', implode( ',', $mapped_days ) );
	}

	/**
	 * Returns the numeric value of the INTERVAL attribute read from the legacy recurrence
	 * rule.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The recurrence rule in the format
	 *                                  used in the `_EventRecurrence` meta value.
	 *
	 * @return int The rule INTERVAL value.
	 */
	private function get_interval( array $rule ) {
		return isset( $rule['custom']['interval'] ) ? (int) $rule['custom']['interval'] : 1;
	}

	/**
	 * Returns the WKST and BYDAY fragments of the RRULE, if required.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The recurrence rule in the format
	 *                                  used in the `_EventRecurrence` meta value.
	 *
	 * @return string The required WKST and BYDAY components of the RRULE.
	 */
	private function get_days_and_week_start( array $rule ) {
		if ( $this->weekly_add_byday ) {
			$byday = $this->build_byday_string( $rule );
			$days_and_week_start = $byday ? "WKST=SU;{$byday}" : "WKST=SU";
		} else {
			$days_and_week_start = "WKST=SU";
		}

		return $days_and_week_start;
	}

	/**
	 * Converts a Weekly recurrence rule with no limit.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The recurrence rule in the format
	 *                                  used in the `_EventRecurrence` meta value.
	 *
	 * @return string The iCalendar string format RRULE.
	 */
	private function convert_ends_never( array $rule ) {
		$interval = $this->get_interval( $rule );
		$days_and_week_start = $this->get_days_and_week_start( $rule );

		return sprintf( 'RRULE:FREQ=WEEKLY;INTERVAL=%d;%s', $interval, $days_and_week_start );
	}

	/**
	 * Converts a Weekly recurrence rule with a number limit.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The recurrence rule in the format
	 *                                  used in the `_EventRecurrence` meta value.
	 *
	 * @return string The iCalendar string format RRULE.
	 *
	 * @throws Requirement_Error If the rule is missing information.
	 */
	private function convert_ends_after( array $rule ) {
		$interval = $this->get_interval( $rule );
		$days_and_week_start = $this->get_days_and_week_start( $rule );

		if ( ! isset( $rule['end-count'] ) ) {
			throw Requirement_Error::due_to_missing_required_information(
				'rule.end-count',
				'Weekly rule definition',
				wp_json_encode( $rule )
			);
		}

		$ical_string = sprintf(
			'RRULE:FREQ=WEEKLY;INTERVAL=%d;COUNT=%s;%s',
			$interval,
			$rule['end-count'],
			$days_and_week_start
		);

		if ( ! $this->rrule_includes_dtstart( $ical_string, $this->converter->get_start_date() ) ) {
			return $this->update_ical_string_count_by( $ical_string, - 1 );
		}

		return $ical_string;
	}

	/**
	 * Converts a Weekly recurrence rule with a date limit.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The recurrence rule in the format
	 *                                  used in the `_EventRecurrence` meta value.
	 *
	 * @return string The iCalendar string format RRULE.
	 *
	 * @throws Requirement_Error If the rule is missing information.
	 */
	private function convert_ends_on( array $rule ) {
		$interval = $this->get_interval( $rule );
		$days_and_week_start = $this->get_days_and_week_start( $rule );
		if ( ! isset( $rule['end'] ) ) {
			throw Requirement_Error::due_to_missing_required_information(
				'rule.end',
				'Weekly rule definition',
				wp_json_encode( $rule )
			);
		}
		$event_timezone = $this->converter->get_timezone();
		$end = Dates::build_date_object( tribe_end_of_day( $rule['end'] ), $event_timezone );

		return sprintf(
			'RRULE:FREQ=WEEKLY;INTERVAL=%d;UNTIL=%sZ;%s',
			$interval,
			$end->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis' ),
			$days_and_week_start
		);
	}

	/**
	 * Realign the converted RRULE adding a DTSTART and RDATE entry if the Legacy and
	 * CT1 code would not produce the same 2nd Occurrence.
	 *
	 * If the rule interval is greater than 1, then Legacy might not produce Occurrences
	 * in a position in conformance to the one that the iCalendar standard applications
	 * would produce. In this instance, the method will move the DSTART of the RRULE to
	 * the 2nd Occurrence Legacy code would produce, make the RRULE an aligned one and
	 * add a DTSTART to model the first Occurrence (the previous DTSTART).
	 *
	 * @since 6.0.0
	 *
	 * @param string              $converted The RRULE string, as converted.
	 * @param array<string,mixed> $rule      The recurrence rule in the format
	 *                                       used in the `_EventRecurrence` meta value.
	 *
	 * @return string The updated iCalendar format string, if required.
	 *
	 * @throws \Exception If there's any issue building the RSET required for the
	 *                    checks.
	 */
	private function align_rrule_with_legacy( $converted, array $rule ) {
		$interval = $this->get_interval( $rule );
		$dtstart = $this->converter->get_start_date();
		$rrule_includes_dtstart = $this->rrule_includes_dtstart( $converted, $dtstart );

		if ( $interval === 1 || $rrule_includes_dtstart ) {
			return $converted;
		}

		$days = isset( $rule['custom']['week']['day'] ) ?
			$rule['custom']['week']['day']
			: [ $this->get_icalendar_week_byday( $dtstart ) ];

		$legacy_rule = new Legacy_Weekly_Rule( $interval, $days );
		$legacy_2nd_occurrence = $legacy_rule->getNextDate( $dtstart->getTimestamp() );

		$rset = $this->get_rset_for_ical_string_dtstart( $converted, $dtstart );
		$ct1_2nd_occurrence = $rset->offsetGet( 0 );

		if (
			$ct1_2nd_occurrence instanceof \DateTimeInterface
			&& $ct1_2nd_occurrence->getTimestamp() !== $legacy_2nd_occurrence
		) {
			// Move DTSTART to CT1 first occurrence.
			$utc = new DateTimezone( 'UTC' );
			$new_dtstart = Dates::immutable( $legacy_2nd_occurrence, $utc )->setTimezone( $dtstart->getTimezone() );
			$realigned = $this->build_dstart_string( $new_dtstart ) . "\n" . $converted;
			// Add an RDATE to cover for first Legacy Occurrence.
			$realigned .= "\n" . $this->build_rdates_string( [ $dtstart ] );

			/*
			 * Note: the COUNT is not reduced here as it's already been reduced before since such rule
			 * will never have a DTSTART aligned to the WEEKLY RRULE. Reducing the COUNT here again would
			 * reduce it by 2, not the right thing to do.
			 */

			return $realigned;
		}

		return $converted;
	}
}
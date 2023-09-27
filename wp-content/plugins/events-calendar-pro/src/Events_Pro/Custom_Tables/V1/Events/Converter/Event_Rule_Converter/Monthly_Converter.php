<?php
/**
 * Handles the conversion of Monthly recurrence or exclusion rules from the
 * `_EventRecurrence` meta value format to the iCalendar RSET one.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;

use DateTimeZone;
use TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error;
use Tribe__Date_Utils;

/**
 * Class Monthly_Converter.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;
 */
class Monthly_Converter extends Abstract_Event_Rule_Converter {
	/**
	 * @inheritDoc
	 */
	public function convert_to_rrule( bool $is_exrule = false ): string {
		$rule = $this->rule;

		$interval = $rule['custom']['interval'] ?? 1;

		$byday       = '';
		$is_same_day = true;

		if ( isset( $rule['custom']['month']['same-day'] ) ) {
			$is_same_day = tribe_is_truthy( $rule['custom']['month']['same-day'] );
		}

		if ( ! $is_same_day ) {
			if ( ! isset( $rule['custom']['month']['number'] ) ) {
				throw Requirement_Error::due_to_missing_required_information(
					'rule.custom.month.number',
					'Monthly rule definition',
					wp_json_encode( $rule )
				);
			}

			$number = $rule['custom']['month']['number'];

			if ( is_numeric( $number ) ) {
				/*
				 * It's an occurrence like "on the 23rd of each month"; we do not want to be picky here as values
				 * like "-1" are valid in the iCal specification and mean "last day of the month".
				 */
				$byday = ";BYMONTHDAY={$number}";
			} else {
				if ( ! isset( $rule['custom']['month']['day'] ) ) {
					throw Requirement_Error::due_to_missing_required_information(
						'rule.custom.month.day',
						'Monthly rule definition',
						wp_json_encode( $rule )
					);
				}

				/*
				 * Might be one of two patterns:
				 * 1. "On the third Tuesday of each month".
				 * 2. "On the 4th day", which means "on the 4 of each month".
				 * Case 2 is modeled, in the legacy UI, by setting `custom.month.day` to `8`.
				 */
				$position = From_Event_Rule_Converter::monthly_position_to_number( $number );
				if ( 8 === (int) $rule['custom']['month']['day'] ) {
					// Case 2 from example above, it's really a month day.
					$byday = ";BYMONTHDAY={$position}";
				} else {
					$day = From_Event_Rule_Converter::day_number_to_abbr( $rule['custom']['month']['day'] );
					// Something like "BYDAY=3TU" to represent the example string above.
					$byday = ";BYDAY={$position}{$day}";
				}
			}
		}

		$end_type = isset( $rule['end-type'] ) ? $rule['end-type'] : null;
		switch ( $end_type ) {
			default:
			case 'Never':
				$converted = sprintf( 'RRULE:FREQ=MONTHLY;INTERVAL=%d%s', $interval, $byday );
				break;
			case 'After':
				if ( ! isset( $rule['end-count'] ) ) {
					throw Requirement_Error::due_to_missing_required_information(
						'rule.end-count',
						'Monthly rule definition',
						wp_json_encode( $rule )
					);
				}

				$converted = sprintf(
					'RRULE:FREQ=MONTHLY;INTERVAL=%d;COUNT=%s%s',
					$interval,
					$rule['end-count'],
					$byday
				);

				if ( ! $this->rrule_includes_dtstart( $converted, $this->converter->get_start_date() ) ) {
					/*
					 * Legacy code, when a RRULE has a COUNT limit, will always include the
					 * DTSTART in the COUNT. When the DTSTART is not included in the RRULE,
					 * we should account for that and decrease the COUNT of the RRULE by 1.
					 */
					$converted = $this->update_ical_string_count_by( $converted, - 1 );
				}

				break;
			case 'On':
				if ( ! isset( $rule['end'] ) ) {
					throw Requirement_Error::due_to_missing_required_information(
						'rule.end',
						'Monthly rule definition',
						wp_json_encode( $rule )
					);
				}

				/*
				 * The UNTIL date is specified in the event timezone; when converting to UTC timezone
				 * that information should be preserved.
				 */
				$event_timezone = $this->converter->get_timezone();
				$end            = Tribe__Date_Utils::build_date_object( tribe_end_of_day( $rule['end'] ), $event_timezone );
				$converted      = sprintf(
					'RRULE:FREQ=MONTHLY;INTERVAL=%d;UNTIL=%sZ%s',
					$interval,
					$end->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis' ),
					$byday
				);
				break;
		}

		if ( ! $is_exrule ) {
			$converted = $this->prepend_dtstart_dtend( $rule, $converted );
		}

		return $converted;
	}

}
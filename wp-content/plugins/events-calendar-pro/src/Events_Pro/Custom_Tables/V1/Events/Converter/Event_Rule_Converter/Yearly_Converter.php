<?php
/**
 * Handles the conversion of Yearly recurrence or exclusion rules from the
 * `_EventRecurrence` meta value format to the iCalendar RSET one.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;

use DateTime;
use DateTimeZone;
use Exception;
use TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error;
use Tribe__Utils__Array;
use Tribe__Date_Utils;

/**
 * Class Yearly_Converter.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;
 */
class Yearly_Converter extends Abstract_Event_Rule_Converter {
	/**
	 * @inheritDoc
	 */
	public function convert_to_rrule( bool $is_exrule = false ): string {
		$rule     = $this->rule;
		$interval = isset( $rule['custom']['interval'] ) ? $rule['custom']['interval'] : 1;

		$byday       = '';
		$is_same_day = true;

		if ( isset( $rule['custom']['year']['same-day'] ) ) {
			$is_same_day = tribe_is_truthy( $rule['custom']['year']['same-day'] );
		}

		$starting_point = null;
		if ( isset( $rule['custom']['year']['month'] ) ) {
			$bymonth = ';BYMONTH=' . Tribe__Utils__Array::to_list( Tribe__Utils__Array::list_to_array( $rule ['custom']['year']['month'] ), ',' );
		} else {
			$utc   = new DateTimeZone( 'UTC' );
			$what  = 'rule.custom.year.month';
			$where = 'Yearly rule definition';
			$data  = wp_json_encode( $rule );

			if ( isset( $rule['EventStartDate'] ) ) {
				try {
					$starting_point = new DateTime( $rule['EventStartDate'], $utc );
				} catch ( Exception $e ) {
					$where = 'While trying to build the month value using the `EventStartDate`';
					throw Requirement_Error::due_to_missing_required_information( $what, $where, $data );
				}
			} else if ( isset( $rule['EventEndDate'] ) ) {
				try {
					$starting_point = new DateTime( $rule['EventEndDate'], $utc );
				} catch ( Exception $e ) {
					$where = 'While trying to build the month value using the `EventEndDate`';
					throw Requirement_Error::due_to_missing_required_information( $what, $where, $data );
				}
			}

			if ( $starting_point === null ) {
				throw Requirement_Error::due_to_missing_required_information( $what, $where, $data );
			}

			if ( $is_same_day ) {
				$day     = $this->converter->get_start_date()->format( 'j' );
				$byday   = ";BYMONTHDAY={$day}";
				$month   = $this->converter->get_start_date()->format( 'n' );
				$bymonth = ";BYMONTH={$month}";
			} else {
				$month   = (int) $starting_point->format( 'n' );
				$bymonth = ";BYMONTH={$month}";
			}
		}

		if ( ! $is_same_day ) {
			$number = $rule['custom']['year']['number'];

			if ( is_numeric( $number ) ) {
				/*
				 * It's an occurrence like "on the 23rd of this month(s)"; we do not want to be picky here as values
				 * like "-1" are valid in the iCal specification and mean "last day of the month".
				 */
				$byday = ";BYMONTHDAY={$number}";
			} else {
				if ( ! isset( $rule['custom']['year']['day'] ) ) {
					throw Requirement_Error::due_to_missing_required_information(
						'rule.custom.year.day',
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
				if ( 8 === (int) $rule['custom']['year']['day'] ) {
					// Case 2 from example above, it's really a month day.
					$byday = ";BYMONTHDAY={$position}";
				} else {
					$day = From_Event_Rule_Converter::day_number_to_abbr( $rule['custom']['year']['day'] );
					// Something like "BYDAY=3TU" to represent the example string above.
					$byday = ";BYDAY={$position}{$day}";
				}
			}
		}

		switch ( $rule['end-type'] ) {
			default:
			case 'Never':
				$converted = sprintf( 'RRULE:FREQ=YEARLY;INTERVAL=%d%s%s', $interval, $bymonth, $byday );
				break;
			case 'After':
				if ( ! isset( $rule['end-count'] ) ) {
					throw Requirement_Error::due_to_missing_required_information(
						'rule.end-count',
						'Yearly rule definition',
						wp_json_encode( $rule )
					);
				}

				$converted = sprintf(
					'RRULE:FREQ=YEARLY;INTERVAL=%d;COUNT=%s%s%s',
					$interval,
					$rule['end-count'],
					$bymonth,
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
						'Yearly rule definition',
						wp_json_encode( $rule )
					);
				}

				$end       = Tribe__Date_Utils::build_date_object( tribe_end_of_day( $rule['end'] ), $this->converter->get_timezone() );
				$converted = sprintf(
					'RRULE:FREQ=YEARLY;INTERVAL=%d;UNTIL=%sZ%s%s',
					$interval,
					$end->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis' ),
					$bymonth,
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
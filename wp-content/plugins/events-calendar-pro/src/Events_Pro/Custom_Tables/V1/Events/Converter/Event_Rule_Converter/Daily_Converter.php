<?php
/**
 * Handles the conversion of a Daily recurrence rule, or exclusion, from the format
 * used in the `_EventRecurrence` meta value to the iCalendar RSET one.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;

use DateTimeZone;
use Tribe__Date_Utils as Dates;

/**
 * Class Daily_Converter.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;
 */
class Daily_Converter extends Abstract_Event_Rule_Converter {
	/**
	 * @inheritDoc
	 */
	public function convert_to_rrule( bool $is_exrule = false ): string {
		$rule = $this->rule;
		$interval = isset( $rule['custom']['interval'] ) ? $rule['custom']['interval'] : 1;

		switch ( $rule['end-type'] ) {
			default:
			case 'Never':
				$converted = sprintf( 'RRULE:FREQ=DAILY;INTERVAL=%d', $interval );
				break;
			case 'After':
				$converted = sprintf( 'RRULE:FREQ=DAILY;INTERVAL=%d;COUNT=%s', $interval, $rule['end-count'] );
				break;
			case 'On':
				$event_timezone = $this->converter->get_timezone();
				$end = Dates::build_date_object( tribe_end_of_day( $rule['end'] ), $event_timezone );
				$converted = sprintf(
					'RRULE:FREQ=DAILY;INTERVAL=%d;UNTIL=%sZ',
					$interval,
					$end->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis' )
				);
				break;
		}

		if ( ! $is_exrule ) {
			$converted = $this->prepend_dtstart_dtend( $rule, $converted );
		}

		return $converted;
	}
}
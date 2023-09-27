<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;

use DateTime;
use DateInterval;
use DateTimeImmutable;
use Exception;
use TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error;
use Tribe__Date_Utils;

class Date_Converter extends Abstract_Event_Rule_Converter {
	/**
	 * @inheritDoc
	 */
	public function convert_to_rrule( bool $is_exrule = false ): string {

		$rule = $this->rule;
		if ( ! isset( $rule['custom']['date']['date'] ) ) {
			throw Requirement_Error::due_to_missing_required_information(
				'rule.custom.date.date',
				'Date rule definition',
				wp_json_encode( $rule )
			);
		}

		try {
			$timezone        = $this->converter->get_timezone();
			$timezone_string = $timezone->getName();

			$same_time = isset( $rule['custom']['same-time'] ) && tribe_is_truthy( $rule['custom']['same-time'] );

			// Common to both: the date is correct, the time will not be correct.
			$start = DateTime::createFromFormat( 'Y-m-d', $rule['custom']['date']['date'] );
			// In case we have time in format (sometimes the case).
			if ( ! $start ) {
				$start = DateTime::createFromFormat( 'Y-m-d H:i:s', $rule['custom']['date']['date'] );
			}

			if ( ! $start ) {
				throw Requirement_Error::due_to_malformed_information(
					'rule.custom.date.date is Invalid',
					'Invalid start Date rule',
					wp_json_encode( $rule )
				);
			}

			if ( $same_time && isset( $rule['custom']['date']['date'], $rule['EventStartDate'], $rule['EventEndDate'] ) ) {
				$start_time = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $rule['EventStartDate'], $timezone );
				$end_time   = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $rule['EventEndDate'], $timezone );
				$duration   = $end_time->format( 'U' ) - $start_time->format( 'U' );

				// Set the correct time on the start.
				$start->setTime( $start_time->format( 'H' ), $start_time->format( 'i' ), $start_time->format( 's' ) );

				// Build the end adding the duration to the start.
				$end = clone $start;
				$end->add( new DateInterval( 'PT' . $duration . 'S' ) );
			} elseif ( isset( $rule['custom']['start-time'], $rule['custom']['end-day'], $rule['custom']['end-time'] ) ) {
				$start_time = Tribe__Date_Utils::build_date_object( $rule['custom']['start-time'], $timezone );
				$start->setTime( $start_time->format( 'H' ), $start_time->format( 'i' ), $start_time->format( 's' ) );
				$end = clone $start;
				$end->add( new DateInterval( 'P' . (int) $rule['custom']['end-day'] . 'D' ) );
				$end_time = Tribe__Date_Utils::build_date_object( $rule['custom']['end-time'], $timezone );
				$end->setTime( $end_time->format( 'H' ), $end_time->format( 'i' ), $end_time->format( 's' ) );
			}
		} catch ( Exception $e ) {
			throw Requirement_Error::due_to_malformed_information(
				$e->getMessage(),
				'Date rule',
				wp_json_encode( $rule )
			);
		}

		if ( ! isset( $start, $end ) ) {
			throw Requirement_Error::due_to_malformed_information(
				'rule.custom.date.date missing start/end dates.',
				'Date rule',
				wp_json_encode( $rule )
			);
		}

		$formatted_start = $start->format( 'Ymd\THis' );
		$formatted_end   = $end->format( 'Ymd\THis' );

		if ( $is_exrule ) {
			return sprintf( 'RDATE:%s', $start->format( 'Ymd' ) );
		}

		return sprintf( 'RDATE;TZID=%s;VALUE=PERIOD:%s/%s', $timezone_string, $formatted_start, $formatted_end );
	}
}
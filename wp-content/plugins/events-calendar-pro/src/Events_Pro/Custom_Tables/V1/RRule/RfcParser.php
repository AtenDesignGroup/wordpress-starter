<?php
/**
 * An extension of the base rlanvin/php-rrule RFC Parser implementation to support what additional entries we require.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\RRule
 */

namespace TEC\Events_Pro\Custom_Tables\V1\RRule;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use RRule\RfcParser as Original_Rfc_Parser;
use Tribe__Timezones as Timezones;

/**
 * Class RfcParser
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\RRule
 */
class RfcParser extends Original_Rfc_Parser {

	/**
	 * Parses an RDATE entry.
	 *
	 * This method is, for the most, a copy-and-paste of the original one. What modifications we made, we made to
	 * support additional RDATE entries.
	 *
	 * @since 6.0.0
	 *
	 * @param string $line The RDATE entry to parse.
	 *
	 * @return array<string,string|int> The parsed RDATE entry, in map format from property names to property values.
	 *
	 * @throws Exception If the RDATE line to parse does not define all the minimum required parts or those parts are
	 *                   not coherent.
	 */
	static public function parseRDate( $line, DateTimeImmutable $dtstart = null ) {
		$property = self::parseLine( $line );
		if ( $property['name'] !== 'RDATE' ) {
			throw new InvalidArgumentException( "Failed to parse RDATE line, this is a {$property['name']} property" );
		}

		$period = false;
		$tz     = null;
		foreach ( $property['params'] as $name => $value ) {
			switch ( strtoupper( $name ) ) {
				case 'TZID':
					$tz = new DateTimeZone( $value );
					break;
				case 'VALUE':
					switch ( $value ) {
						case 'DATE':
						case 'DATE-TIME':
							break;
						case 'PERIOD':
							$period = true;
							break;
						default:
							throw new InvalidArgumentException( "Unknown VALUE value for RDATE: $value, must be one of DATE-TIME, DATE or PERIOD" );
					}
					break;
				default:
					throw new InvalidArgumentException( "Unknown property parameter: $name" );
			}
		}

		$dates = [];

		foreach ( explode( ',', $property['value'] ) as $value ) {
			if ( $period ) {
				$dates[] = self::parse_rdate_period( $value, $tz );
			} else{
				if ( false !== strpos( $value, 'Z' ) ) {
					if ( $tz !== null ) {
						throw new InvalidArgumentException( 'Invalid RDATE property: TZID must not be applied when time is specified in UTC' );
					}

					$date = new DateTime( $value );
				} else {
					$date = new DateTime( $value, $tz );
				}

				if ( $dtstart !== null && date_parse( $value )['hour'] === false ) {
					// If the value does not define a time, then apply the DTSTART time.
					$date->setTime( $dtstart->format( 'H' ), $date->format( 'i' ), $dtstart->format( 's' ) );
				}

				$dates[] = $date;
			}
		}

		return $dates;
	}

	/**
	 * Parses a VALUE=PERIOD RDATE or EXDATE entry to a valid Occurrence.
	 *
	 * Since the notion of duration is implicit to the definition of a PERIOD RDATE or EXDATE, the values returned
	 * by this method might not be `DateTime` instances, but `TEC\Events_Pro\Custom_Tables\V1\RRule\Occurrence` instances that will allow the
	 * correct representation of the period.
	 *
	 * @since 6.0.0
	 *
	 * @param string $value The input RDATE line value.
	 * @param string|DateTimeZone|null A timezone name, the reference to a timezone object, or `null`
	 *                                 to use the default site timezone.
	 *
	 * @throws Exception If the RDATE or EXDATE PERIOD specification components are not correctly built or coherent.
	 */
	private static function parse_rdate_period( $value, $timezone = null ) {
		$components = explode( '/', $value, 2 );
		// Let's build a timezone for the RDATE with the provided information.
		$tz = Timezones::build_timezone_object( $timezone );
		// The first component will always be the start date.
		$start_date = new DateTimeImmutable( $components[0], $tz );

		if ( 0 === strpos( $components[1], 'P' ) ) {
			// The second component is a duration in the same format used by the `DateInterval::__construct` method.
			$interval = new DateInterval( $components[1] );
			$end_date = $start_date->add( $interval );
		} else {
			// The second component is a date.
			$end_date = new DateTimeImmutable( $components[1], $tz );
		}

		// We allow for start and end dates to be equal, but not end before start.
		if ( $start_date > $end_date ) {
			throw new InvalidArgumentException(
				'Invalid period in RDATE: start date must must be before end date.'
			);
		}

		return new Occurrence( $start_date, $end_date->getTimestamp() - $start_date->getTimestamp() );
	}

	/**
	 * Parses an iCalendar format EXDATE string to return a set of exclusion dates.
	 *
	 * @since 6.0.0
	 *
	 * @param string                 $line    The iCalendar format line to parse.
	 * @param DateTimeImmutable|null $dtstart A reference to the DTSTART object, if any.
	 *
	 * @return array<DateTime> The parsed exclusion dates set.
	 *
	 * @throws Exception If there's any issue building the dates.
	 */
	public static function parseExDate( $line, DateTimeImmutable $dtstart = null ): array {
		$property = self::parseLine( $line );
		if ( $property['name'] !== 'EXDATE' ) {
			throw new InvalidArgumentException( "Failed to parse EXDATE line, this is a {$property['name']} property" );
		}

		$tz = null;
		foreach ( $property['params'] as $name => $value ) {
			switch ( strtoupper( $name ) ) {
				case 'VALUE':
					// Ignore optional words
					break;
				case 'TZID':
					$tz = new DateTimeZone( $value );
					break;
				default:
					throw new InvalidArgumentException( "Unknown property parameter: $name" );
			}
		}

		$dates = array();

		foreach ( explode( ',', $property['value'] ) as $value ) {
			if ( strpos( $value, 'Z' ) ) {
				if ( $tz !== null ) {
					throw new InvalidArgumentException( 'Invalid EXDATE property: TZID must not be applied when time is specified in UTC' );
				}
				$date = new Ex_Date( $value );
			} else {
				$date = new Ex_Date( $value, $tz );
			}

			$dates[] = $date;
		}

		return $dates;
	}
}

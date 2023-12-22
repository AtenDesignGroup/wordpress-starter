<?php
/**
 * Provides methods to classes acting as RSET builders, to parse the RSET using custom logic.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\RRule
 */

namespace TEC\Events_Pro\Custom_Tables\V1\RRule;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use RRule\RRule;

/**
 * Trait RSet_Builder
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\RRule
 */
trait RSet_Builder {

	/**
	 * This method is the code, pretty much copied and pasted, coming from the original class `__construct` method.
	 *
	 * The original class `__construct` method hard-codes the RFC Parser dependency we need to inject, this is why
	 *
	 * @since 6.0.0
	 *
	 * @param string|null                    $string          The RSET string to parse or null to start from an empty
	 *                                                        RSET.
	 * @param DateTimeInterface|string|null $default_dtstart The default DTSTART date in object or string format, or
	 *                                                        `null` to use the current system timezone.
	 *
	 * @return void The method has the side-effect of adding each parsed entry to this RSET.
	 *
	 * @throws Exception If there's any issue parsing the RSET string specification.
	 */
	protected function parse_ruleset( $string = null, $default_dtstart = null ) {
		/*
		 * The base library will rely on the DTSTART date being a mutable object in some rules configurations.
		 * Let's make sure it is.
		 */
		$default_dtstart = $default_dtstart instanceof DateTimeImmutable ?
			new DateTime( $default_dtstart->format( 'Y-m-d H:i:s' ), $default_dtstart->getTimezone() )
			: $default_dtstart;

		if ( $string && is_string( $string ) ) {
			$string  = trim( $string );
			$rrules  = [];
			$exrules = [];
			$rdates  = [];
			$exdates = [];
			$dtstart = null;

			// Parse each RSET line.
			$lines = explode( "\n", $string );

			foreach ( $lines as $line ) {
				$line = trim( $line );

				if ( strpos( $line, ':' ) === false ) {
					throw new InvalidArgumentException( 'Failed to parse RFC string, line is not starting with a property name followed by ":"' );
				}

				list( $property_name, $property_value ) = explode( ':', $line );
				$tmp           = explode( ";", $property_name, 2 );
				$property_name = $tmp[0];
				switch ( strtoupper( $property_name ) ) {
					case 'DTSTART':
						if ( $default_dtstart || $dtstart !== null ) {
							throw new InvalidArgumentException( 'Failed to parse RFC string, multiple DTSTART found' );
						}
						$dtstart = $line;
						break;
					case 'RRULE':
						$rrules[] = $line;
						break;
					case 'EXRULE':
						$exrules[] = $line;
						break;
					case 'RDATE':
						// Accumulate now, merge later.
						$rdates[] = RfcParser::parseRDate( $this->add_timezone_to_xdate( $line ), $this->dtstart_object );
						break;
					case 'EXDATE':
						// Accumulate now, merge later.
						$exdates[] = RfcParser::parseExDate( $this->add_timezone_to_xdate( $line ), $this->dtstart_object );
						break;
					default:
						throw new InvalidArgumentException( "Failed to parse RFC, unknown property: $property_name" );
				}
			}

			// Merge RDATES and EXDATES, we've accumulated them to avoid calling `array_merge` in a loop.
			$rdates  = count( $rdates ) ? array_merge( ...$rdates ) : [];
			$exdates = count( $exdates ) ? array_merge( ...$exdates ) : [];

			foreach ( $rrules as $rrule ) {
				if ( $dtstart ) {
					$rrule = $dtstart . "\n" . $rrule;
				}

				$this->addRRule( new RRule( $rrule, $default_dtstart ) );
			}

			foreach ( $exrules as $rrule ) {
				if ( $dtstart ) {
					$rrule = $dtstart . "\n" . $rrule;
				}
				$this->addExRule( new RRule( $rrule, $default_dtstart ) );
			}

			foreach ( $rdates as $date ) {
				$this->addDate( $date );
			}

			foreach ( $exdates as $date ) {
				$this->addExDate( $date, true );
			}
		}
	}

	/**
	 * Enforces the current DTSTART timezone in the RDATE or EXDATE string, if not
	 * specified.
	 *
	 * Here the assumption is made that, if the input string does not specify a timezone,
	 * either a real or the UTC one by means of the `Z` notation, then the timezone should
	 * be assumed to be the current DTSTART one. This is inline with what big providers and
	 * consumers of iCalendar format files do (e.g. Google Calendar and Outlook).
	 *
	 * @since 6.0.0
	 *
	 * @param string $string The RDATE or EXDATE string to parse.
	 *
	 * @return string The RDATE or XDATE string with added timezone information, if required.
	 */
	private function add_timezone_to_xdate( $string ) {
		$timezone_name = $this->dtstart->getTimezone()->getName();
		$has_timezone  = false !== strpos( $string, 'TZID' )
		                 || false !== strpos( $string, 'Z' );
		$uses_utc_timezone        = in_array( $timezone_name, [ 'UTC', 'Z' ], true );

		if ( $has_timezone || $uses_utc_timezone  ) {
			return $string;
		}

		/*
		 * The RDATE specification does not contain timezone information and the DTSTART timezone
		 * is not UTC: add the timezone details to the string to avoid the `rrule` library from
		 * adding UTC Occurrences.
		 */

		if ( in_array( $timezone_name, [ 'UTC', 'Z' ], true ) ) {
			// The DTSTART timezone is UTC, use `Z`.
			return preg_replace( '/($|,)/', 'Z$1', $string );
		}

		return str_replace( ':', ';TZID=' . $timezone_name . ':', $string );
	}
}

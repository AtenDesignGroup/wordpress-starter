<?php
/**
 * Extends the default RSET builder implementation to add logic.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\RRule
 */

namespace TEC\Events_Pro\Custom_Tables\V1\RRule;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use RRule\RRule;
use RRule\RSet;
use TEC\Events\Custom_Tables\V1\Traits\With_Timezones;
use Tribe__Date_Utils as Dates;

/**
 * Class RSet_Wrapper
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\RRule
 */
class RSet_Wrapper extends RSet {
	use With_Timezones;
	use RSet_Builder;

	/**
	 * A flag indicating whether EXDATEs and EXRULEs should apply to the DTSTART or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $excludable_dtstart;

	/**
	 * A reference to the immutable date time object representing the DSTART.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $dtstart_object;

	/**
	 * Whether the DTSTART date should receive a special treatment and
	 * be "spared" from removal should an EXDATE be defined for it.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $dtstart_spared = false;

	/**
	 * The initial start of the occurrences, a DateTime instance.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTime|null $dtstart
	 */
	private $dtstart;

	/**
	 * A reference to the DTEND component, if any.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable|null
	 */
	private $dtend;
	/**
	 * The RSET duration inferred from the DTSTART and DTEND components.
	 * If the DTEND component is not specified in the RSET string, then
	 * the RSET duration will be undefined.
	 *
	 * @since 6.0.0
	 *
	 * @var int|null
	 */
	private $duration_in_seconds;

	/**
	 * An internal property used to keep track of the previous Occurrence while iterating.
	 *
	 * @var DateTimeInterface|null
	 */
	private $i_previous_occurrence;

	/**
	 * A internal property used to keep track of the total number of Occurrences generated while
	 * iterating.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	private $i_total = 0;

	/**
	 * An internal property used, in bitmask format, to toggle the use of cache while iterating.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	private $i_use_cache = 0;

	/**
	 * RSet_Wrapper constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param string|null                       $string             The string representation of this set.
	 * @param int|string|DateTimeInterface|null $default_dtstart    The default datetime to start, or `null` to read
	 *                                                              it from the input string.
	 * @param bool                              $excludable_dtstart A flag indicating whether EXDATEs and EXRULEs should
	 *                                                              apply to the DTSTART or not.
	 *
	 * @throws Exception If there's any issue building the DTSTART date object from the provided information.
	 */
	public function __construct( $string = null, $default_dtstart = null, bool $excludable_dtstart = false ) {
		$dtstart_normalized_string = $this->parse_dtstart( $string, $default_dtstart );
		$dstart_object             = Dates::immutable( $this->dtstart );
		$this->excludable_dtstart  = $excludable_dtstart;

		if ( false === $dstart_object ) {
			throw new \InvalidArgumentException(
				'Failed to parse DTSTART - it must be a valid date, timestamp or \DateTime object'
			);
		}

		$this->dtstart_object = $dstart_object;
		$dtend_normalized_string = $this->parse_dtend( $dtstart_normalized_string, $this->dtstart_object );
		$this->parse_ruleset( $dtend_normalized_string, $this->dtstart );
	}

	/**
	 * Parses the RSET specification string with option to override the provided DTSTART. Will return the RSET string
	 * with DTSTART removed if it existed, and hydrates the instance DTSTART properties.
	 *
	 * @since 6.0.0
	 *
	 * @param string|null                       $rset    The RSET specification string.
	 * @param string|DateTimeInterface|int|null $dtstart A date parseable value, DateTimeInterface or the RSET DTSTART
	 *                                                   value. The value will be ignored if the RSET string contains
	 *                                                   a DTSTART component.
	 *
	 * @return string The RSET specification string normalized to not include the DTSTART
	 *                specification (unless the $dtstart_override is specified), if any existed.
	 *
	 * @throws Exception If there's any issue building the DTSTART object from the provided
	 *                   information.
	 */
	private function parse_dtstart( ?string $rset = '', $dtstart = null ): string {
		$dtstart_line = $this->extract_dtstart_line( $rset );

		// The normalized string is the RSET spec. string minus the DTSTART entry.
		if ( ! empty( $dtstart_line ) ) {
			// The DTSTART defined in the string will trump the provided one.
			$parsed = RfcParser::parseRRule( $dtstart_line );
			$dtstart = $parsed['DTSTART'];
		}

		// Ensure the DTSTART object will be mutable: the base library requires it.
		$this->dtstart = Dates::mutable( $dtstart );

		return $this->remove_dtstart_line( $rset );
	}

	/**
	 * Returns the RSET RFC-compliant representation.
	 *
	 * @since 6.0.0
	 * @since 6.0.3 Now $dates_include_time affects EXDATEs, not just RDATEs.
	 *
	 * @param bool $include_dtstart    Whether to include the DTSTART
	 *                              in the output string or not.
	 * @param bool $dates_include_time Whether the output dates (EXDATE, RDATE) should be formatted
	 *                                 to include date and time, or just date.
	 *
	 * @return string The RSET RFC-compliant representation.
	 */
	public function get_rfc_string( bool $include_dtstart = true, bool $dates_include_time = true ): string {
		$rules  = $this->getRRules();
		$rdates = $this->getDates();

		if ( empty( $rules ) && empty( $rdates ) ) {
			// The RSET is empty.
			return '';
		}

		if ( $include_dtstart ) {
			$pieces[] = $this->get_dtstart_rfc_string();
			$pieces[] = $this->get_dtend_rfc_string();
		}
		$pieces[] = $this->get_rrule_rfc_string();
		$pieces[] = $this->get_rdate_rfc_string( $dates_include_time );
		$pieces[] = $this->get_exrule_rfc_string();
		$pieces[] = $this->get_exdate_rfc_string( $dates_include_time );

		return implode( "\n", array_filter( $pieces ) );
	}

	/**
	 * Returns the RFC-compliant RRULE line of the RSET, it will not include
	 * the DTSTART portion.
	 *
	 * Mind it might contain line-breaks.
	 *
	 * @since 6.0.0
	 *
	 * @return string The RFC-compliant representation of the RSET RRULE entry,
	 *                or an empty string if there is none.
	 */
	private function get_rrule_rfc_string( ) {
		$rules = $this->getRRules();
		$rule  = reset( $rules );

		if ( empty( $rule ) || ! $rule instanceof RRUle ) {
			return '';
		}

		$rule_rfc_string       = (string) $rule->rfcString( true );
		$rule_rfc_string_lines = explode( "\n", $rule_rfc_string );
		// Drop the DTSTART line, if any.
		$rule_rfc_string_lines = array_filter( $rule_rfc_string_lines, static function ( $line ) {
			return false === strpos( $line, 'DTSTART' );
		} );

		// The next line should be the RRULE one, it _might_ contain the RRULE prefix or not: normalize.
		return 'RRULE:' . str_replace( 'RRULE:', '', implode( "\n", $rule_rfc_string_lines ) );
	}

	/**
	 * Builds and returns the RFC-compliant EXRULE line for the RSET.
	 *
	 * Mind it might contain line-breaks.
	 *
	 * @since 6.0.0
	 *
	 * @return string The RFC-compliant EXRULE line of the RSET or an
	 *                empty string if no EXRULE are defined in the RSET.
	 */
	private function get_exrule_rfc_string() {
		$exrules = $this->getExRules();
		$exrule  = reset( $exrules );

		if ( empty( $exrule ) || ! $exrule instanceof RRule ) {
			return '';
		}
		$exrule_rfc_string       = (string) $exrule->rfcString( true );
		$exrule_rfc_string_lines = explode( "\n", $exrule_rfc_string );
		// Drop the DTSTART line, if any.
		$exrule_rfc_string_lines = array_filter( $exrule_rfc_string_lines, static function ( $line ) {
			return false === strpos( $line, 'DTSTART' );
		} );

		// The next line should be the RRULE one, it _might_ contain the RRULE prefix or not: normalize.
		return 'EXRULE:' . str_replace( 'RRULE:', '', implode( "\n", $exrule_rfc_string_lines ) );
	}

	/**
	 * Builds and returns the RFC-compliant representation of the RSET EXDATEs.
	 *
	 * @since 6.0.0
	 * @since 6.0.3 Added $dates_include_time optional param.
	 *
	 * @param bool $dates_include_time Flag whether to ignore time values in the output string.
	 *
	 * @return string The RFC-compliant representation of the RSET EXDATEs,
	 *                or an empty string if none are defined.
	 */
	public function get_exdate_rfc_string( bool $dates_include_time = true ) {
		$exdates = $this->getExDates();

		if ( empty( $exdates ) ) {
			return '';
		}

		return 'EXDATE:' . implode( ',', array_map(
				static function ( $exdate ) use ( $dates_include_time ) {
					return self::convert_exdate_to_rfc( $exdate, $dates_include_time );
				}, $exdates ) );
	}


	/**
	 * Will take a DateTimeInterface and retrieve the RFC string for an RSET.
	 *
	 * @since 6.0.3
	 *
	 * @param DateTimeInterface $exdate             The EXDATE to retrieve the RFC string for.
	 * @param bool              $dates_include_time Flag whether to ignore time values in the output string.
	 *                                              If the original Ex_Date object was not constructed with a time,
	 *                                              this flag will be ignored.
	 *
	 * @return string The RFC string.
	 */
	public static function convert_exdate_to_rfc( DateTimeInterface $exdate, bool $dates_include_time ): string {
		if ( $dates_include_time && $exdate instanceof Ex_Date ) {
			// Will only include time if the EXDATEs were constructed with a time.
			return $exdate->to_rfc_string();
		}

		return $dates_include_time ? $exdate->format( "Ymd\THis" ) : $exdate->format( 'Ymd' );
	}

	/**
	 * Returns a set of Occurrence dates, up to `$limit`, that start between the start
	 * and the end of a date.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface|string|int $date  The date to check.
	 * @param int                          $limit How many dates to return at most.
	 *
	 * @return array<DateTimeInterface> The matching Occurrences, in ascending start date order.
	 */
	public function get_occurrences_on_date( $date, int $limit = 1 ): array {
		$date = Dates::immutable( $date )->format( Dates::DBDATETIMEFORMAT );
		$start = tribe_beginning_of_day( $date );
		$end = tribe_end_of_day( $date );

		return $this->getOccurrencesBetween( $start, $end, $limit );
	}

	/**
	 * Overrides the method implemented by the trait to ensure the DTSTART Occurrence
	 * will not be removed by any applicable EXDATE or EXRULE.
	 *
	 * {@inheritDoc}
	 */
	private function compare_exdate_occurrence( $exdate, $occurrence ) {
		$occurrence = $occurrence instanceof Occurrence ? $occurrence->start() : $occurrence;

		if ( $exdate->format('Ymd') < $occurrence->format('Ymd') ) {
			// The EXDATE is less than the Occurrence: drop it as it will not apply to any other Occurrence.
			return - 1;
		}

		$f = 'Y-m-d H:i:s';

		$exdate_matches_occurrences = ( $exdate instanceof Ex_Date && $exdate->should_exclude_all_day() )
			? $exdate->format( 'Ymd' ) === $occurrence->format( 'Ymd' )
			: $exdate == $occurrence;
		if ( $exdate_matches_occurrences ) {
			if (
				! $this->excludable_dtstart && ! $this->dtstart_spared
				&& $occurrence->format( $f ) == $this->dtstart->format( $f )
			) {
				$this->dtstart_spared = true;

				/*
				 * The Occurrence should not be removed by the EXDATE, but the EXDATE should be kept
				 * as it might apply to other Occurrences with same start, diff. duration.
				 */

				return 1;
			}

			// The EXDATE matches the Occurrence: exclude the Occurrence.
			return 0;
		}

		// The EXDATE does not apply to the Occurrence, but might apply to future ones.
		return 1;
	}

	/**
	 * Builds and returns the RFC-compliant DTSTART string for the RSET.
	 *
	 * @since 6.0.0
	 *
	 * @return string The complete, RFC-compliant, DTSTART string
	 *                of the RSET.
	 */
	private function get_dtstart_rfc_string() {
		$dtstart_timezone = $this->dtstart->getTimezone()->getName();
		if ( in_array( $dtstart_timezone, [ 'UTC', 'Z' ], true ) ) {
			return sprintf(
				'DTSTART:%sZ',
				$this->dtstart->format( 'Ymd\THis' )
			);
		}

		return sprintf(
			'DTSTART;TZID=%s:%s',
			$dtstart_timezone, $this->dtstart->format( 'Ymd\THis' )
		);
	}

	/**
	 * Returns the DTEND entry, if any, in the iCalendar RFC format.
	 *
	 * @since 6.0.0
	 *
	 * @return string The DTEND entry in the iCalendar RFC format,
	 *                if any, or an empty string.
	 */
	private function get_dtend_rfc_string() {
		if ( ! $this->dtend instanceof DateTimeImmutable ) {
			return '';
		}

		$dtend_timezone = $this->dtend->getTimezone()->getName();
		if ( in_array( $dtend_timezone, [ 'UTC', 'Z' ], true ) ) {
			return sprintf(
				'DTEND:%sZ',
				$this->dtend->format( 'Ymd\THis' )
			);
		}

		return sprintf(
			'DTEND;TZID=%s:%s',
			$dtend_timezone, $this->dtend->format( 'Ymd\THis' )
		);
	}

	/**
	 * Builds and returns the RFC-compliant lines for the RDATES.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $dates_include_time Whether the output dates should be formatted
	 *                                 to include date and time, or just date.
	 *
	 * @return string The RFC-compliant RDATE lines of the RSET.
	 *                The lines might contain line-breaks if multiple
	 *                definitions are required.
	 */
	private function get_rdate_rfc_string( bool $dates_include_time = true ): string {
		$rdates = $this->getDates();
		if ( empty( $rdates ) ) {
			return '';
		}
		$pieces        = [];
		$plain_rdates  = [];
		$period_rdates = [];
		/** @var DateTime|Occurrence $rdate */
		foreach ( $rdates as $rdate ) {
			if ( $rdate instanceof Occurrence ) {
				// Add PERIOD.
				$duration        = $rdate->get_duration();
				$period_rdates[] = $rdate->start()->format( 'Ymd\THis' ) . sprintf( '/PT%dS', $duration );
			} else {
				// Add RDATE
				$format = $dates_include_time ? 'Ymd\THis' : 'Ymd';
				$plain_rdates[] = $rdate->format( $format );
			}
		}
		$pieces[] = count( $plain_rdates ) ? 'RDATE;VALUE=DATE:' . implode( ',', $plain_rdates ) : '';
		$pieces[] = count( $period_rdates ) ? 'RDATE;VALUE=PERIOD:' . implode( ',', $period_rdates ) : '';

		return implode( "\n", array_filter( $pieces ) );
	}

	/**
	 * Implementation of the magic method that will return the
	 * RSET RFC-compliant representation.
	 *
	 * @since 6.0.0
	 *
	 * @return string The RSET RFC-compliant representation.
	 */
	public function __toString() {
		return $this->get_rfc_string();
	}

	/**
	 * Overrides the base method to ensure the DTSTART will always be part of the generated Occurrences
	 * whether it fits the RDATE + RRULE - EXDATE - EXRULE computation.
	 *
	 * This is a take on this ambiguous sentence from the iCalendar standard:
	 * > The "EXDATE" property can be used to exclude the value specified in "DTSTART".
	 * > However, in such cases, the original "DTSTART" date MUST still be maintained
	 * > by the calendaring and scheduling system because the original "DTSTART" value
	 * > has inherent usage dependencies by other properties such as the "RECURRENCE-ID".
	 * Different implementations will treat the this differently, but larger implementations
	 * like Google Calendar or Office will **keep** the DTSTART no matter the EXRULE or EXDATE
	 * specification; we're following that example.
	 *
	 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-5-1-exception-date-times.html
	 * @link https://docs.microsoft.com/en-us/openspecs/exchange_standards/ms-stanoical/dcd6ef46-96c6-4d8a-88a9-3dfcc8b2f874
	 *
	 * @since 6.0.0
	 *
	 * @param bool $reset Whether to restart the iteration, or keep going.
	 *
	 * @return DateTime|DateTimeInterface|int|string|null Either the reference to an Occurrence
	 *                                                    generated by the RSET, or `null` if
	 *                                                    no Occurrence would be generated from the
	 *                                                    RSET.
	 */
	protected function iterate( $reset = false ) {
		$previous_occurrence = & $this->i_previous_occurrence;
		$total = & $this->i_total;
		$use_cache = & $this->i_use_cache;

		if ( $reset ) {
			$this->i_previous_occurrence = null;
			$this->i_total               = 0;
			$this->i_use_cache           = true;
			reset($this->cache);
		}

		// Go through the cache first.
		if ( $use_cache ) {
			while ( ($occurrence = current($this->cache)) !== false ) {
				next($this->cache);
				++ $total;
				return clone $occurrence;
			}
			reset($this->cache);
			/*
			 * Now set use_cache to false to skip the all thing on next iteration
			 * and start filling the cache instead.
			 */
			$use_cache = false;
			// If the cache as been used up completely and we now there is nothing else.
			if ( $total === $this->total ) {
				return null;
			}
		}

		if ( $this->rlist_heap === null ) {
			// RRULEs and RDATEs.
			$this->rlist_heap = new \SplMinHeap();
			$this->rlist_iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);
			$this->rlist_iterator->attachIterator(new \ArrayIterator($this->rdates));
			foreach ( $this->rrules as $rrule ) {
				$this->rlist_iterator->attachIterator($rrule);
			}
			$this->rlist_iterator->rewind();

			// EXRULEs and EXDATE.
			$this->exlist_heap = new \SplMinHeap();
			$this->exlist_iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);

			$this->exlist_iterator->attachIterator(new \ArrayIterator($this->exdates));
			foreach ( $this->exrules as $rrule ) {
				$this->exlist_iterator->attachIterator($rrule);
			}
			$this->exlist_iterator->rewind();
		}

		while ( true ) {
			foreach ( $this->rlist_iterator->current() as $date ) {
				if ( $date !== null ) {
					$this->rlist_heap->insert( $date );
				}
			}

			// Advance the iterator for the next call.
			$this->rlist_iterator->next();

			if ( $this->rlist_heap->isEmpty() ) {
				// Exit the loop to stop the iterator.
				break;
			}

			$occurrence = $this->rlist_heap->top();
			$this->rlist_heap->extract(); // remove the occurrence from the heap

			if (
				( $previous_occurrence !== null && $occurrence <= $previous_occurrence )
				|| in_array( $occurrence, $this->cache, false )
			) {
				continue; // skip, was already considered
			}

			/*
			 * Now we need to check against the list of Exclusions.
			 * We need to iterate exlist as long as it contains dates lower than occurrence
			 * (they will be discarded), and then check if the date is the same
			 * as occurrence (in which case it is discarded).
			 */
			$excluded = false;
			while ( true ) {
				foreach ( $this->exlist_iterator->current() as $date ) {
					if ( $date !== null ) {
						$this->exlist_heap->insert($date);
					}
				}
				// Advance the iterator for the next call.
				$this->exlist_iterator->next();

				if ( $this->exlist_heap->isEmpty() ) {
					// Break this loop only.
					break;
				}

				$exdate = $this->exlist_heap->top();

				$compare = $this->compare_exdate_occurrence( $exdate, $occurrence );

				if ( $compare < 0 ) {
					// EXDATE is less than the occurrence: remove as it will not apply anymore.
					$this->exlist_heap->extract();
					continue;
				}

				if ( $compare === 0 ) {
					// The EXDATE instance is excluding this Occurrence.
					$excluded = true;
					break;
				}

				// EXDATE is later than occurrence: keep it for later.
				break;
			}

			$previous_occurrence = $occurrence;

			if ( $excluded ) {
				continue;
			}

			++ $total;
			$this->cache[] = $occurrence;
			// = yield.
			return $this->build_return_occurrence($occurrence);
		}

		// Save total for count cache.
		$this->total = $total;

		// Stop the iterator.
		return null;
	}

	/**
	 * Override the base method to avoid adding an exclusion date before
	 * the RSET DSTART.
	 *
	 * @since 6.0.0
	 *
	 * @param string|DateTimeInterface|int $date          The exclusion date to add.
	 * @param bool                         $if_applicable Whether to add the exclusion date only if it is applicable
	 *                                                    to the RSET or not.
	 *
	 * @return $this A link to this object, for chaining.
	 */
	public function addExDate( $date, bool $if_applicable = false ) {
		try {
			if ( is_integer( $date ) ) {
				$parsed = \DateTime::createFromFormat( 'U', $date );
				$parsed->setTimezone( new \DateTimeZone( 'UTC' ) ); // default is +00:00 (see issue #15)
			} else if ( is_string( $date ) ) {
				$parsed = new Ex_Date( $date );
			} else if ( $date instanceof DateTimeInterface && ! $date instanceof Ex_Date ) {
				$parsed = new Ex_Date( $date->format( 'Y-m-d H:i:s e' ) );
			} else if ( $date instanceof DateTimeInterface ) {
				$parsed = $date;
			} else {
				throw new \InvalidArgumentException(
					"Failed to parse the date"
				);
			}

			$exlusion_ends = new DateTime( $parsed->exclusion_ends() );
			if ( $exlusion_ends < $this->dtstart || in_array( $parsed, $this->exdates, true ) ) {
				// Do not add an EXDATE before the DTSTART: it will never apply.
				return $this;
			}

			if ( $if_applicable && ! $this->is_exdate_applicable( $parsed ) ) {
				// Add the EXDATE only if it would actually apply.
				return $this;
			}

			$this->exdates[] = $parsed;
			sort( $this->exdates );
		} catch ( \Exception $e ) {
			throw new \InvalidArgumentException(
				'Failed to parse EXDATE - it must be a valid date, timestamp or \DateTime object'
			);
		}

		$this->clearCache();

		return $this;
	}

	/**
	 * Parse the string and return the DTSTART line, if any.
	 *
	 * @since 6.0.0
	 *
	 * @param string $rset The iCalendar format RSET string.
	 *
	 * @return string|false Either the DTSTART line, or `nul` if the RSET string does
	 *                     not contain a DTSTART line.
	 */
	private function extract_dtstart_line( $rset ) {
		$dstart_lines = array_filter( explode( "\n", (string) $rset ), static function ( $line ) {
			return 0 === strpos( $line, 'DTSTART' );
		} );

		return reset( $dstart_lines );
	}

	/**
	 * Returns the RSET string removing the DTSTART line from it.
	 *
	 * @since 6.0.0
	 *
	 * @param string $rset The iCalendar format RSET string.
	 *
	 * @return string The input RSET string, without the DTSTART line.
	 */
	private function remove_dtstart_line( $rset ) {
		return implode( "\n", array_filter( explode( "\n", (string) $rset ), static function ( $line ) {
			return false === stripos( $line, 'DTSTART' );
		} ) );
	}

	private function parse_dtend( $string, DateTimeImmutable $dtstart ) {
		if ( empty( $string ) ) {
			return $string;
		}

		$lines = explode( "\n", $string );

		foreach ( $lines as $k => $line ) {
			if ( strpos( $line, 'DTEND' ) !== false ) {
				// Use the DTSTART facility to parse the DTEND.
				$parsed_dtend = RfcParser::parseRRule( str_replace( 'DTEND', 'DTSTART', $line ) );

				if ( ! ( is_array( $parsed_dtend ) && isset( $parsed_dtend['DTSTART'] ) ) ) {
					return $string;
				}

				$this->dtend = Dates::immutable( $parsed_dtend['DTSTART'] );
				$this->duration_in_seconds = $this->dtend->getTimestamp() - $dtstart->getTimestamp();
				$remove_line = $k;

				break;
			}
		}

		if ( isset( $remove_line ) ) {
			unset( $lines[ $remove_line ] );
			$string = implode( "\n", $lines );
		}

		return $string;
	}

	/**
	 * Returns the RSET duration, if any.
	 *
	 * Normally, RSETs will not have a duration, those specifying a DTSTART and DTEND
	 * component will, though.
	 *
	 * @since 6.0.0
	 *
	 * @return int|null The duration of the RSET Occurrences as inferred from
	 *                  the DTSTART and DTEND components, if present.
	 */
	public function get_duration( $default = null ): ?int {
		return $this->duration_in_seconds ?: $default;
	}

	/**
	 * Builds the instances that should be returned to represent an Occurrence
	 * generated by the RSET.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface|null $occurrence The Occurrence as generated from the
	 *                                           RSET iteration logic.
	 *
	 * @return Occurrence|DateTimeInterface|null An reference to an Occurrence object if the RSET
	 *                                  has a duration set, or a reference to a DateTime
	 *                                  object otherwise.
	 *
	 * @throws Exception If there's an issue building the dates used to build the
	 *                   Occurrence object.
	 */
	private function build_return_occurrence( DateTimeInterface $occurrence = null ): ?DateTime {
		if ( $occurrence === null ) {
			return null;
		}

		if ( $occurrence instanceof Occurrence ) {
			return $occurrence;
		}

		if ( $this->dtend instanceof DateTimeImmutable ) {
			return new Occurrence( $occurrence, $this->duration_in_seconds );
		}

		return $occurrence;
	}

	/**
	 * Sets the RSET duration in seconds.
	 *
	 * @since 6.0.0
	 *
	 * @param int $duration The new RSET duration in seconds.
	 *
	 * @return static $this A reference to the updated RSET instance.
	 *
	 * @throws Exception If there's any issue building the Date objects
	 *                   required for the calculation.
	 */
	public function set_duration_in_seconds( int $duration ): self {
		if ( $duration === $this->duration_in_seconds ) {
			return $this;
		}

		$this->duration_in_seconds = $duration;
		$this->dtend = Dates::immutable( $this->dtstart )->add( new \DateInterval( 'PT' . $duration . 'S' ) );

		return $this->clearCache();
	}

	/**
	 * Returns the RSET Occcurence at the specified offset.
	 *
	 * If possible, the RSET will return an Occurrence.
	 *
	 * @since 6.0.0
	 *
	 * @param int $offset The offset to return the Occurrence for.
	 *
	 * @return DateTime|Occurrence|null The Occurrence at the specified offset.
	 *
	 * @throws Exception If there's any issue building the Date objects required.
	 */
	public function offsetGet( $offset ): ?DateTime {
		return $this->build_return_occurrence( parent::offsetGet( $offset ) );
	}

	/**
	 * Returns the RSET DTSTART date object.
	 *
	 * @since 6.0.0
	 *
	 * @return DateTimeImmutable The RSET DTSTART date object.
	 */
	public function get_dtstart(): DateTimeImmutable {
		return $this->dtstart_object;
	}

	/**
	 * Returns the RSET DTEND date object. Will try and infer from duration if a DTEND is not found.
	 *
	 * @since 6.0.3
	 *
	 * @return DateTimeImmutable The RSET DTEND date object.
	 */
	public function get_dtend(): DateTimeImmutable {
		if ( $this->dtend ) {
			return $this->dtend;
		}

		$dtend    = clone $this->dtstart_object;
		$interval = new DateInterval( 'PT' . $this->get_duration( 0 ) . 'S' );

		return $dtend->add( $interval );
	}

	/**
	 * Returns whether the input EXDATE would exclude an Occurrence generated
	 * from the RSET or not; if not, then the EXDATE is deemed not applicable.
	 *
	 * @since 6.0.0
	 * @since 6.0.3 Changed parameter to require an Ex_Date class.
	 *
	 * @param Ex_Date $exdate The EXDATE object to check.
	 *
	 * @return bool Whether the input EXDATE would exclude an Occurrence generated from the RSET or not.
	 */
	private function is_exdate_applicable( Ex_Date $exdate ): bool {
		return count( $this->getOccurrencesBetween(
				$exdate->exclusion_begins(),
				$exdate->exclusion_ends(),
				1 )
		       ) === 1;
	}

	/**
	 * Overrides the default implementation to reset some flag properties
	 * specific to this particular class.
	 *
	 * @since 6.0.0
	 *
	 * @return RSet_Wrapper A reference to this object, for chaining.
	 */
	public function clearCache() {
		$this->dtstart_spared = false;

		return parent::clearCache();
	}

	/**
	 * Returns whether an RDATE would make sense in the context of the RSET
	 * or not.
	 *
	 * RDATEs are not applicable when they either match an existing Occurrence
	 * generated by the RRULE, or when they match an existing RDATE.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface $rdate The RDATE object to check.
	 *
	 * @return bool Whether the input RDATE would make sense in the context of the RSET or not.
	 */
	private function is_rdate_applicable( DateTimeInterface $rdate ): bool {
		$matches = $this->getOccurrencesBetween( $rdate, $rdate, 1 );

		if ( empty( $matches ) ) {
			return true;
		}

		if ( ! $rdate instanceof Occurrence ) {
			// There is a match, and we cannot further discriminate on duration.
			return false;
		}

		// Check the duration of the match.
		$match = reset( $matches );
		$match_end = $match instanceof Occurrence ? $match->end()->getTimestamp() : null;
		$rdate_end = $rdate->end()->getTimestamp();

		return $rdate_end !== $match_end;
	}

	/**
	 * Override of the parent method to ensure an RDATE will not be added to the
	 * RDATE set if already present or if matching an Occurrence that would be
	 * generated from the RRULE.
	 *
	 * @since 6.0.0
	 *
	 * @param string|DateTimeInterface|Occurrence $date          The date to add to the set.
	 * @param bool                                $if_applicable Whether to add the RDATE only if applicable or not.
	 *
	 * @return RSet_Wrapper A reference to this object, for chaining.
	 */
	public function addDate( $date, bool $if_applicable = true ) {
		try {
			$parsed = RRule::parseDate( $date );

			if ( in_array( $parsed, $this->rdates, true ) ) {
				// Do not add an EXDATE before the DTSTART: it will never apply.
				return $this;
			}

			if ( $if_applicable && ! $this->is_rdate_applicable( $parsed ) ) {
				// Add the EXDATE only if it would actually apply.
				return $this;
			}

			$this->rdates[] = $parsed;
			sort( $this->rdates );
		} catch ( \Exception $e ) {
			throw new \InvalidArgumentException(
				'Failed to parse RDATE - it must be a valid date, timestamp or \DateTime object'
			);
		}

		$this->clearCache();

		return $this;
	}

	/**
	 * Overrides the parent method to cast returned dates to Occurrences when possible.
	 *
	 * @since 6.0.0
	 *
	 * @return array<DateTimeInterface|Occurrence> The list of RDATE Occurrences.
	 */
	public function getDates() {
		return array_map( [ $this, 'build_return_occurrence' ], parent::getDates() );
	}
}

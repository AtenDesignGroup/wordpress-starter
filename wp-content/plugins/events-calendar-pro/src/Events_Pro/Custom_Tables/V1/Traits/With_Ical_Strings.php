<?php
/**
 * Provides methods to read and update iCalendar format strings.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Traits;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Traits;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use RRule\RfcParser;
use RRule\RRule;
use RuntimeException;
use TEC\Events\Custom_Tables\V1\Events\Occurrences\Max_Recurrence;
use TEC\Events_Pro\Custom_Tables\V1\RRule\RSet_Wrapper;
use Tribe__Date_Utils as Dates;

/**
 * Trait With_Ical_Strings.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Traits;
 */
trait With_Ical_Strings {
	/**
	 * Checks and returns whether an iCalendar format string contains will create
	 * an Occurrence for the specified DTSTART or not.
	 *
	 * @since 6.0.0
	 *
	 * @param string            $ical_string The iCalendar format string to check.
	 * @param DateTimeImmutable $dtstart     A reference to the DTSTART to check.
	 *
	 * @return bool Whether the recurrence rule, when coupled with the specified DTSTART,
	 *              would produce an Occurrence for the DTSTART or not.
	 *
	 * @throws Exception If the RSET construction fails.
	 */
	protected function rrule_includes_dtstart( string $ical_string, DateTimeImmutable $dtstart ): bool {
		$rset = $this->get_rset_for_ical_string_dtstart( $ical_string, $dtstart );
		$rset_first_occurrence = $rset->offsetGet( 0 );

		return $dtstart == $rset_first_occurrence;
	}

	/**
	 * Intersects two sets of dates returning the ones in the first that are also present
	 * in the second.
	 *
	 * Keys are preserved.
	 *
	 * @since 6.0.0
	 * @param array<DateTimeInterface> $dates_set_a The first set.
	 * @param array<DateTimeInterface> $dates_set_b The second set.
	 *
	 * @return array<DateTimeInterface> The intersected set of dates.
	 */
	protected function intersect_dates( array $dates_set_a, array $dates_set_b ): array {
		$get_timestamp = static function ( DateTimeInterface $date ) {
			return $date->getTimestamp();
		};
		$string_format_a_set = array_map( $get_timestamp, $dates_set_a );
		$string_format_b_set = array_map( $get_timestamp, $dates_set_b );
		$intersected = array_intersect( $string_format_a_set, $string_format_b_set );

		return count( $intersected ) ? array_intersect_key( $dates_set_a, $intersected ) : [];
	}

	/**
	 * Returns an RSET built for an iCalendar string and DTSTART couple.
	 *
	 * To avoid wasteful computation to parse the iCalendar string and build the RSET,
	 * the RSETs will be built once and cloned after each request.
	 *
	 * @since 6.0.0
	 *
	 * @param string            $ical_string              The iCalendar format string to
	 *                                                    build the RSET for, should not
	 *                                                    include the DTSTART.
	 * @param DateTimeInterface $dtstart                  A reference to the DTSTART in object format.
	 * @param int|null          $limit_infinite_until     Whether to allow infinite recurrences or not. If set to `true`, then
	 *                                                    infinite recurrences will be limited to the specified date.
	 *
	 * @return RSet_Wrapper A reference to an RSET instance, its reference unique to the holding code
	 *                      by means of a `clone` call.
	 *
	 * @throws Exception If there's any issue building the RSET.
	 */
	protected function get_rset_for_ical_string_dtstart( string $ical_string, DateTimeInterface $dtstart, DateTimeImmutable $limit_infinite_until = null ): RSet_Wrapper {
		$dtstart = Dates::immutable( $dtstart, null, false );

		if ( $limit_infinite_until instanceof DateTimeImmutable && $this->ical_string_is_never_ending( $ical_string ) ) {
			$ical_string = $this->set_ical_string_until_limit( $ical_string, $limit_infinite_until );
		}

		if ( false === $dtstart ) {
			throw new RuntimeException( 'Failed to build Immutable DateTime object to build RSET.' );
		}

		$rset = new RSet_Wrapper( $ical_string, $dtstart, true );

		return $rset;
	}

	/**
	 * Updates and returns an iCalendar format string COUNT value.
	 *
	 * @since 6.0.0
	 *
	 * @param string $ical_string The iCalendar string to update.
	 * @param int    $value       The integer value to update the COUNT value of
	 *                            the iCalendar format string by.
	 *
	 * @return string The updated iCalendar format string.
	 *
	 * @throws RuntimeException If the iCalendar format string contains an
	 *                             invalid COUNT value.
	 */
	protected function update_ical_string_count_by( string $ical_string, int $value ): string {
		$current_count = $this->get_ical_string_count( $ical_string );
		if ( $current_count === 0 ) {
			throw new RuntimeException( "RRULE string $ical_string has a COUNT of 0." );
		}

		if ( $current_count === 1 ) {
			// It's not illegal, but it would make the RRULE moot, just drop it.
			return false;
		}

		return preg_replace( '/COUNT=\d+/', 'COUNT=' . ( $current_count + $value ), $ical_string );
	}

	/**
	 * Returns the COUNT value of an iCalendar string, if any.
	 *
	 * Note: while not making any sense, the method will return a
	 * COUNT value of `0` if found in the iCalendar string.
	 *
	 * @since 6.0.0
	 *
	 * @param string $ical_string The iCalendar format string.
	 *
	 * @return int|null Either the iCalendar string COUNT value, or
	 *                  `null` if the string does not have a COUNT
	 *                  value.
	 */
	protected function get_ical_string_count( string $ical_string ): ?int {
		if ( ! preg_match( '/COUNT=(\d+)/', $ical_string, $matches ) ) {
			return null;
		}

		return (int) $matches[1];
	}

	/**
	 * Mutates an EXRULE iCalendar format string to align with the new DTSTART.
	 *
	 * Much like an RRULE, an EXRULE recurrence will depend on the DTSTART. If
	 * the DTSTART changes, depending on the type, interval and limit of the
	 * EXRULE, the EXRULE might need to be mutated to produce the same exclusions
	 * it would have produced with the old DTSTART with the new DSTART.
	 *
	 * @since 6.0.0
	 *
	 * @param string            $exrule        The EXRULE iCalendar format string.
	 * @param string            $rrule_string  The iCalendar format string of the RRULE this EXRULE
	 *                                         should apply to.
	 * @param DateTimeImmutable $old_dtstart   A reference to the old DTSTART Date object.
	 * @param DateTimeImmutable $new_dtstart   A reference to the new DTSTART Date object.
	 * @param bool              $force_exdates Whether to force the mutation of the EXRULE into EXDATEs
	 *                                         or not.
	 *
	 * @return string|false Either the mutated, realigned, EXRULE string, or `false` to indicate
	 *                      the EXRULE would not produce any applicable exclusion when applied
	 *                      to the RRULE.
	 *
	 * @throws Exception If there's any issue building the RSETs required for the method logic.
	 */
	protected function realign_exrule_for_rrule(
		$exrule,
		$rrule_string,
		DateTimeImmutable $old_dtstart,
		DateTimeImmutable $new_dtstart,
		$force_exdates = false,
		$force_exdates_below = 5
	) {
		if ( $new_dtstart == $old_dtstart ) {
			return $exrule;
		}

		$count = $this->get_rrule_count( $exrule );
		$until_date = $this->get_rrule_end_date( $exrule, $new_dtstart );
		$limit_type = null === $count ? 'UNTIL' : 'COUNT';

		if ( null !== $until_date && $until_date <= $new_dtstart ) {
			// An EXRULE ending before the new DTSTART will never exclude any of the RRULE Occurrences.
			return false;
		}

		$exrule_as_rrule = str_replace( 'EXRULE', 'RRULE', $exrule );
		$exrule_rset = $this->get_rset_for_ical_string_dtstart( $exrule_as_rrule, $old_dtstart );

		/**
		 * Prepare a Closure that will return the EXDATEs that would apply to the RRULE when
		 * applied to the new DTSTART.
		 */
		$get_intersecting_exdates = function () use ( $exrule_rset, $new_dtstart, $rrule_string, $exrule ) {
			$exrule_end = $this->get_rrule_end_date( $exrule );
			$rrule_end = $this->get_rrule_end_date( $rrule_string, $new_dtstart );

			// Use the nearest end date, to avoid computation cycles to create wasted Occurrences.
			$end_dates = array_filter( [ $exrule_end, $rrule_end, $this->get_never_date_from( $new_dtstart ) ] );

			// In neither had a limit, use the "never" one.
			$nearest_end = count( $end_dates ) > 1 ? min( ...$end_dates ) : reset( $end_dates );
			$rrule_rset = $this->get_rset_for_ical_string_dtstart( $rrule_string, $new_dtstart );
			$occurrences = $rrule_rset->getOccurrencesBetween( $new_dtstart, $nearest_end );

			// Pull EXDATEs up to the last Occurrence, we're looking for intersections.
			$exdates = $exrule_rset->getOccurrencesBetween( $new_dtstart, end( $occurrences ) );

			// Remove the EXDATEs that would not apply to the RRULE.
			return $this->intersect_dates( $exdates, $occurrences );
		};

		if ( ! $force_exdates ) {
			if ( 'COUNT' === $limit_type ) {
				// The RSET::getOccurrencesBetween method is inclusive.
				$skipped = count( $exrule_rset->getOccurrencesBetween( $old_dtstart, $new_dtstart ) );
				$produced_after_new_dtstart = $count - $skipped;
			} else {
				// Just a number to apply the default step.
				$produced_after_new_dtstart = 2;
			}

			if ( 0 === $produced_after_new_dtstart ) {
				// The EXRULE is completely skipped due to the new DTSTART, drop it.
				return false;
			}

			if ( 1 === $produced_after_new_dtstart ) {
				// When applied to the new DTSTART the EXRULE will produce one EXDATE only.
				$exdates = $exrule_rset->getOccurrencesBetween( $new_dtstart, null, 2 );

				if ( count( $exdates ) !== 2 ) {
					return false;
				}

				// Since the method is inclusive, pick the 2nd one.
				return sprintf( 'EXDATE;VALUE=DATE:%s', $exdates[1]->format( 'Ymd\THis' ) );
			}

			// The EXRULE would produce 2+ exclusions from the new DTSTART.

			$first_after_new_dtstart = $exrule_rset->getOccurrencesBetween( $new_dtstart, null, 1 );
			$is_aligned_with_old_dtstart = $first_after_new_dtstart == [ $new_dtstart ];

			if ( 'COUNT' === $limit_type ) {
				// Just reduce the COUNT limit.
				$exrule = $this->update_ical_string_count_by( $exrule, - $skipped );
			}

			if ( $is_aligned_with_old_dtstart ) {
				// No need to modify anything else.
				return $exrule;
			}

			preg_match( '/FREQ=(?<freq>\w+)/', $exrule, $matches );
			$exrule_freq = isset( $matches['freq'] ) ? strtoupper( $matches['freq'] ) : 'DAILY';
			$mutated_exrule = null;

			if ( $exrule_freq !== 'DAILY' ) {
				switch ( $exrule_freq ) {
					case 'WEEKLY':
						$old_dtstart_week_day = $this->get_icalendar_week_byday( $old_dtstart );

						// If we can set the BYDAY correctly, it can stay an EXRULE.
						$byday = $this->get_ical_string_attribute_values( $exrule, 'BYDAY', [] );

						if ( in_array( $old_dtstart_week_day, $byday, true ) ) {
							// Nothing to do, the EXRULE already contains the old DTSTART week day.
							$mutated_exrule = $exrule;
							break;
						}

						if ( empty( $byday ) ) {
							// Fix EXRULE
							$mutated_exrule = $this->set_ical_string_attribute( $exrule, 'BYDAY', [ $old_dtstart_week_day ] );
							break;
						}
						break;
					case 'MONTHLY':
						$old_dtstart_bymonthday = $this->get_icalendar_bymonthday( $old_dtstart );
						$old_dtstart_byday = $this->get_icalendar_month_byday( $old_dtstart );
						$bymonthday = $this->get_ical_string_attribute_values( $exrule, 'BYMONTHDAY', [] );
						$byday = $this->get_ical_string_attribute_values( $exrule, 'BYDAY', [] );

						if ( empty( $bymonthday ) && empty( $byday ) ) {
							// Just fix the EXRULE on the old DTSTART day in the month.
							$mutated_exrule = $exrule . ';BYMONTHDAY=' . $old_dtstart_bymonthday;
							break;
						}

						if ( ! empty( $bymonthday ) && in_array( $old_dtstart_bymonthday, $bymonthday, false ) ) {
							// Nothing to update, the EXRULE already targets the old day in the month.
							$mutated_exrule = $exrule;
							break;
						}

						if ( ! empty( $byday ) && in_array( $old_dtstart_byday, $byday, true ) ) {
							// Nothing to update, the EXRULE already targets the old day in the month.
							$mutated_exrule = $exrule;
							break;
						}
						break;
					case 'YEARLY':
						$bymonthday = $this->get_ical_string_attribute_values( $exrule, 'BYMONTHDAY', [] );
						$byday = $this->get_ical_string_attribute_values( $exrule, 'BYDAY', [] );
						$bymonth = $this->get_ical_string_attribute_values( $exrule, 'BYMONTH', [] );

						if ( $bymonth && ( $bymonthday || $byday ) ) {
							// The EXRULE is set in BYMONTH and either BYMONTHDAY or BYDAY: let it be.
							$mutated_exrule = $exrule;
							break;
						}

						$old_dtstart_bymonth = $this->get_icalendar_bymonth( $old_dtstart );
						$old_dtstart_bymonthday = $this->get_icalendar_bymonthday( $old_dtstart );
						$old_dtstart_byday = $this->get_icalendar_month_byday( $old_dtstart );

						if ( ! count( array_filter( [ $bymonthday, $byday, $bymonth ] ) ) ) {
							// The EXRULE is not set in BYMONTH, BYMONTHDAY or BYDAY: fix it on the old DTSTART.
							$mutated_exrule = $exrule . ';BYMONTH=' . $old_dtstart_bymonth . ';BYMONTHDAY=' . $old_dtstart_bymonthday;
							break;
						}

						if ( ! empty( $bymonth ) && in_array( $old_dtstart_bymonth, $bymonth, false ) ) {
							// The EXRULE BYMONTH is set and it targets the old DTSTART month.

							if ( ! empty( $bymonthday ) && in_array( $old_dtstart_bymonthday, $bymonthday, false ) ) {
								// The EXRULE BYMONTH and BYMONTHDAY are already aligned w/ the old DSTART.
								$mutated_exrule = $exrule;
								break;
							}

							if ( ! empty( $byday ) && in_array( $old_dtstart_byday, $byday, false ) ) {
								// The EXRULE BYMONTH and BYDAY are already aligned w/ the old DSTART.
								$mutated_exrule = $exrule;
								break;
							}
						}

						break;
				}
			}

			if ( null !== $mutated_exrule ) {
				/*
				 * Static changes are done and produced a mutated EXRULE.
				 * Now let's run some costly computation to know if there's
				 * any value in including it.
				 */
				$intersecting_exdates = $get_intersecting_exdates();

				if ( $intersecting_exdates ) {
					$has_limit = ! $this->ical_string_is_never_ending( $exrule_as_rrule );

					if ( $has_limit && count( $intersecting_exdates ) <= $force_exdates_below ) {
						// Less than a few EXDATEs? Make it an EXDATE set.
						return $this->build_exdates_strings( $intersecting_exdates );
					}

					return $mutated_exrule;
				}

				// The EXRULE would fail to exclude any Occurrence from the EXRULE, drop it.
				return false;
			}
		}

		/*
		* There is little we can do but mutate the EXRULE into a set of EXDATEs.
		* To reduce the size of the set, keep only the ones that would apply to the RRULE.
		*/

		$exdates = $get_intersecting_exdates();

		if ( count( $exdates ) === 0 ) {
			// The EXRULE would not exclude any occurrence of the RRULE, drop it.
			return false;
		}

		// Mutate the EXRULE into a set of EXDATEs.
		return $this->build_exdates_strings( $exdates );
	}

	/**
	 * Returns a RRULE, or EXRULE, count value, if any.
	 *
	 * @since 6.0.0
	 *
	 * @param string $string The RRULE, or EXRULE, count value, if any.
	 *
	 * @return int|null Either the COUNT value, or `null` if the RRULE, or
	 *                  EXRULE, does not have a COUNT limit.
	 */
	protected function get_rrule_count( string $string ): ?int {
		if ( preg_match( '/COUNT=(?<count>\\d+)/', $string, $matches ) ) {
			return (int) $matches['count'];
		}

		return null;
	}

	/**
	 * Returns a RRULE, or EXRULE, iCalendar format string end date, if any.
	 *
	 * @since 6.0.0
	 *
	 * @param string                 $string          The RRULE, or EXRULE, iCalendar
	 *                                                format string to return the UNTIL
	 *                                                limit Date for.
	 *
	 * @param DateTimeImmutable|null $never_starts_at If provided, then this Date object
	 *                                                will be used as base to calculate the
	 *                                                the "never" limit Date.
	 *
	 * @return DateTimeImmutable|null Either a reference to the limit Date object, or `null` if
	 *                                the RRULE, or EXRULE, limit is either not defined or of
	 *                                the COUNT type.
	 */
	protected function get_rrule_end_date( string $string, DateTimeImmutable $never_starts_at = null ): ?DateTimeImmutable {

		if ( preg_match( '/UNTIL=(?<until>\w+)/', $string, $matches ) ) {
			return Dates::immutable( RRule::parseDate( $matches['until'] ) );
		}

		if ( null === $never_starts_at || $this->get_rrule_count( $string ) ) {
			// Either NEVER should be supported, or it's a COUNT limit.
			return null;
		}

		// There is no NEVER: there is only far-enough into the future.
		return $this->get_never_date_from( $never_starts_at );
	}

	/**
	 * Returns the Date object representing the "never" limit in respect to a base date.
	 *
	 * Spoiler: there is no "never"; there is only so many months into the future from
	 * a date.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeImmutable $date A reference to the Date object representing the
	 *                                date to build the "never" limit from.
	 *
	 * @return DateTimeImmutable A reference to the Date object representing the "never"
	 *                           limit date.
	 */
	protected function get_never_date_from( DateTimeImmutable $date ): DateTimeImmutable {
		$max_months_after = tribe_get_option( 'recurrenceMaxMonthsAfter', Max_Recurrence::get_recurrence_max_months_default() );

		return $date->add( new DateInterval( "P{$max_months_after}M" ) );
	}

	/**
	 * Returns a set of values for an iCalendar string attribute, if any.
	 *
	 * @since 6.0.0
	 *
	 * @param string                 $string    The iCalendar format string to read the attribute values from.
	 * @param string                 $attribute The attribute to read the values for.
	 * @param array<int|string>|null $default   The default value to return if the string does
	 *                                          not contain the seeked attribute.
	 *
	 * @return array<string|int>|null The attribute value, if present, else the default value.
	 */
	protected function get_ical_string_attribute_values( string $string, string $attribute, ?array $default = [] ): ?array {
		preg_match( sprintf( "/%s=(?<values>[^;]+)/", preg_quote( $attribute, '/' ) ), $string, $matches );

		if ( isset( $matches['values'] ) ) {
			return explode( ',', $matches['values'] );
		}

		return $default;
	}

	/**
	 * Removes an attribute and its values, if present, from an iCalendar format string.
	 *
	 * @since 6.0.0
	 *
	 * @param string $string    The iCalendar format string to remove the attribute from.
	 * @param string $attribute The attribute to remove from the string.
	 *
	 * @return string The updated iCalendar format string.
	 */
	protected function unset_ical_string_attribute( string $string, string $attribute ): string {
		$pattern = '/' . preg_quote( $attribute, '/' ) . '[=:;][^;]*(;|$)*/';

		return trim( preg_replace( $pattern, '', $string ), ';' );
	}

	/**
	 * Sets, overriding its value if already present, an attribute in the iCalendar string.
	 *
	 * @since 6.0.0
	 *
	 * @param string                 $string     The iCalendar format string to set the value in.
	 * @param string                 $attribute  The uppercase name of the attribute to set in the string.
	 * @param array<string|int>|null $new_values Either a set of values to set for the attribute or
	 *                                           `null` to remove the attribute.
	 *
	 * @return string The updated iCalendar format string.
	 */
	protected function set_ical_string_attribute( string $string, string $attribute, array $new_values = null ): string {
		$string = $this->unset_ical_string_attribute( $string, $attribute );

		if ( empty( $new_values ) ) {
			return $string;
		}

		return rtrim( $string, ';' ) . ';' . $attribute . '=' . implode( ',', $new_values );
	}

	/**
	 * Returns the Week BYDAY value for a date.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface $date A reference to the date object to return the value for.
	 *
	 * @return string The uppercase, two-letters, week day; e.g. `MO` for Monday.
	 */
	protected function get_icalendar_week_byday( DateTimeInterface $date ): string {
		return strtoupper( substr( $date->format( 'D' ), 0, 2 ) );
	}

	/**
	 * Returns a date BYMONTHDAY value.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface $date A reference to the date object to return the value for.
	 *
	 * @return string The date BYMONTHDAY value; e.g. `23`.
	 */
	protected function get_icalendar_bymonthday( DateTimeInterface $date ): string {
		return $date->format( 'j' );
	}

	/**
	 * Returns the date week and day position in the month.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface $date A reference to the date object to return the value for.
	 *
	 * @return string The date week and day position in the month; e.g. `3WE` for the 3rd Wednesday.
	 */
	protected function get_icalendar_month_byday( DateTimeInterface $date ): string {
		$first_month_day = $date->setDate( $date->format( 'Y' ), $date->format( 'm' ), 1 );
		$offset = $first_month_day->format( 'j' ) >= $date->format( 'j' );
		$old_dtstart_week_n = (int) $date->format( 'W' ) - (int) $first_month_day->format( 'W' ) + $offset;

		return $old_dtstart_week_n . $this->get_icalendar_week_byday( $date );
	}

	/**
	 * Returns the date month number in the format used in the iCalendar specification.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface $date A reference to the date object to return the value for.
	 *
	 * @return string The date month number; e.g. `3` for March.
	 */
	protected function get_icalendar_bymonth( DateTimeInterface $date ): string {
		return $date->format( 'n' );
	}

	/**
	 * Build an iCalendar format EXDATE string from a set of EXDATEs.
	 *
	 * @since 6.0.0
	 *
	 * @param array<DateTimeInterface> $exdates The set of Date objects to
	 *                                          build the EXDATE string from.
	 *
	 * @return string The iCalendar format EXDATE string built from the set
	 *                of Dates.
	 */
	protected function build_exdates_strings( array $exdates ): string {
		return str_replace( 'RDATE', 'EXDATE', $this->build_rdates_string( $exdates ) );
	}

	/**
	 * Build an iCalendar format RDATE string from a set of RDATEs.
	 *
	 * @since 6.0.0
	 *
	 * @param array<DateTimeInterface> $rdates  The set of Date objects to
	 *                                          build the RDATE string from.
	 *
	 * @return string The iCalendar format RDATE string built from the set
	 *                of Dates.
	 */
	protected function build_rdates_string( array $rdates ): string {
		if ( empty( $rdates ) ) {
			return '';
		}

		return sprintf( 'RDATE;VALUE=DATE:%s', implode( ',',
				array_map( static function ( $rdate ) {
					return $rdate->format( 'Ymd\THis' );
				}, $rdates )
			)
		);
	}

	/**
	 * Builds the iCalendar DSTART string fragment for a date.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface $date    A reference to the Date object to
	 *                                   build the DTEND string fragment for.
	 * @param bool              $use_utc Whether to build the DTSTART using the UTC time, or
	 *                                   by using the local time and adding the TZID component
	 *                                   too.
	 *
	 * @return string The DSTART iCalendar string fragment.
	 */
	protected function build_dstart_string( DateTimeInterface $date, bool $use_utc = false ): string {
		if ( ! $use_utc ) {
			return sprintf( 'DTSTART;TZID=%s:%s', $date->getTimezone()->getName(), $date->format( 'Ymd\THis' ) );
		}

		$utc = new DateTimeZone( 'UTC' );
		$utc_date = Dates::immutable( $date )->setTimezone( $utc );

		return sprintf( "DTSTART:%s", $utc_date->format( 'Ymd\THis\Z' ) );
	}

	/**
	 * Builds the iCalendar DTEND string fragment for a date.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface $date    A reference to the Date object to
	 *                                   build the DTEND string fragment for.
	 * @param bool              $use_utc Whether to build the DTEND using the UTC time, or
	 *                                   by using the local time and adding the TZID component
	 *                                   too.
	 *
	 * @return string The DTEND iCalendar string fragment.
	 */
	protected function build_dtend_string( DateTimeInterface $date, bool $use_utc = false ): string {
		return str_replace( 'DTSTART', 'DTEND', $this->build_dstart_string( $date, $use_utc ) );
	}

	/**
	 * Sets, overriding existing values if any, the DT(START|END) attribute value in the input
	 * iCalendar format string.
	 *
	 * @since 6.0.0
	 *
	 * @param string $string       The iCalendar format string to update.
	 * @param string $dt_attribute The name of the DT(START|END) attribute to set the value for.
	 * @param string $dt_value     The full string, including the attribute name, to set the value
	 *                             to.
	 *
	 * @return string The updated iCalendar format string.
	 */
	protected function set_ical_string_dt_attribute( string $string, string $dt_attribute, string $dt_value ): string {
		$lines = explode( "\n", $string );
		$dt_line_position = count( $lines );
		foreach ( $lines as $position => $line ) {
			if ( strpos( $line, $dt_attribute ) !== false ) {
				$dt_line_position = $position;
				break;
			}
		}
		$lines[ $dt_line_position ] = $dt_value;

		return implode( "\n", $lines );
	}

	/**
	 * Removes the DT(START|END) attributes from the input iCalendar format string.
	 *
	 * @since 6.0.0
	 *
	 * @param string $ical_string The input iCalendar format string to remove the DT(START|END) from.
	 *
	 * @return string The updated iCalendar format string.
	 */
	protected function remove_dtstart_dtend( string $ical_string ): string {
		$lines = explode( "\n", $ical_string );
		$lines = array_filter( $lines, static function ( $line ) {
			return ! preg_match( '/^DTSTART|^DTEND/', $line );
		} );

		return implode( "\n", $lines );
	}

	/**
	 * Parses the iCalendar format string and returns the value of the DT(START|END) attribute.
	 *
	 * @since 6.0.0
	 *
	 * @param string $string    The iCalendar format string to parse.
	 * @param string $attribute The name of the DT(START|END) attribute to get the value for.
	 *
	 * @return DateTimeImmutable|null The DateTimeImmutable value of the DT(START|END) attribute, or `null`
	 *                                if the attribute is not found in the string.
	 */
	protected function parse_string_dt_attribute( string $string, string $attribute = 'DTSTART' ): ?DateTimeImmutable {
		$dt_line = null;

		foreach ( explode( "\n", $string ) as $line ) {
			if ( strpos( $line, $attribute ) !== false ) {
				$dt_line = $line;
				break;
			}
		}

		if ( $dt_line === null ) {
			return null;
		}

		$parsed = RfcParser::parseRRule( $dt_line, [] );

		return Dates::immutable( $parsed[ $attribute ] ) ?? null;
	}

	/**
	 * Returns whether the input iCalendar format string contains RRULE limited
	 * by the COUNT or UNTIL attributes or not.
	 *
	 * @since 6.0.0
	 *
	 * @param string $string The iCalendar format string to check.
	 *
	 * @return bool Whether the input iCalendar format string contains RRULE limited
	 *              by the COUNT or UNTIL attributes or not.
	 */
	protected function ical_string_is_never_ending( string $string ): bool {
		$rrule_pos = strpos( $string, 'RRULE' );
		if ( $rrule_pos === false ) {
			// The iCalendar string does not contain an RRULE, so it cannot be never ending.
			return false;
		}

		// The iCalendar string contains an RRULE, and neither an UNTIL nor a COUNT limit are set.
		return strpos( substr( $string, $rrule_pos ), 'UNTIL' ) === false
		       && strpos( substr( $string, $rrule_pos ), 'COUNT' ) === false;
	}

	/**
	 * Sets an UNTIL limit in the iCalendar format string RRULE entry.
	 *
	 * If the string already contains an RRULE entry with a COUNT or UNTIL limit,
	 * those will be overridden.
	 *
	 * @since 6.0.0
	 *
	 * @param string                 $string The iCalendar format string to update.
	 * @param DateTimeImmutable|null $until  The UNTIL limit to set in the RRULE entry,
	 *                                       or `null` to remove the UNTIL limit.
	 *
	 * @return string The updated iCalendar format string.
	 */
	protected function set_ical_string_until_limit( string $string, DateTimeImmutable $until = null ): string {
		$rrule_line_pos = null;
		$lines = explode( "\n", $string );

		foreach ( $lines as $k => $line ) {
			if ( strpos( $line, 'RRULE' ) === 0 ) {
				$rrule_line_pos = $k;
				break;
			}
		}

		if ( $rrule_line_pos === null ) {
			// The iCalendar string does not contain an RRULE, so it cannot be limited.
			return $string;
		}

		$rrule_line = $lines[ $rrule_line_pos ];

		// Remove the RRULE COUNT attribute if it exists.
		$rrule_line = $this->set_ical_string_attribute( $rrule_line, 'COUNT', null );

		if ( $until === null ) {
			// Remove the RRULE UNTIL attribute if it exists.
			$rrule_line = $this->set_ical_string_attribute( $rrule_line, 'UNTIL', null );
		} else {
			// Set the RRULE UNTIL attribute.
			$utc = new DateTimezone( 'UTC' );
			$until = $until->setTimezone( $utc );
			$until_string = $until->format( 'Ymd\THis\Z' );
			$rrule_line = $this->set_ical_string_attribute( $rrule_line, 'UNTIL', [ $until_string ] );
		}

		$lines[ $rrule_line_pos ] = $rrule_line;

		return implode( "\n", $lines );
	}

	/**
	 * During migration, we cannot work with infinite RSETs; limit any infinite RSET to the highest
	 * possible limit between the user-set value and the CT1 default.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string> $strings The set of iCalendar strings to limit.
	 * @param DateTimeImmutable $until The UNTIL limit date to set in the RRULE entries.
	 *
	 * @return array<string> The input set of iCalendar strings.
	 *
	 * @throws Exception If there's any issue with the RSET object.
	 */
	protected function limit_never_ending_strings( array $strings, DateTimeImmutable $until ): array {
		return array_map( function ( string $string ) use ( $until ): string {
			if ( ! $this->ical_string_is_never_ending( $string ) ) {
				return $string;
			}

			return $this->set_ical_string_until_limit( $string, $until );
		}, $strings );
	}

	/**
	 * Limits an iCalendar format string to the given UNTIL limit.
	 *
	 * @since 6.0.0
	 *
	 * @param string            $string The iCalendar format string to limit.
	 * @param DateTimeImmutable $until  The UNTIL limit to set in the RRULE entry,
	 *
	 * @return string The limited iCalendar format string.
	 *
	 * @throws Exception If there's any issue building the required Date objects.
	 */
	protected function limit_never_ending_string( string $string, DateTimeImmutable $until ): string {
		return $this->limit_never_ending_strings( [ $string ], $until )[0];
	}

	/**
	 * Returns the first value of an iCalendar format string attribute.
	 *
	 * @since 6.0.0
	 *
	 * @param string $ical_string The iCalendar format string to read the attribute from.
	 * @param string $attribute   The iCalendar format attribute to read the value for.
	 * @param mixed  $default     The default value to return if the attribute is not found.
	 *
	 * @return mixed The attribute value if found, or the default value if not found.
	 */
	protected function get_ical_string_attribute_value( string $ical_string, string $attribute, $default = null ) {
		$values = $this->get_ical_string_attribute_values( $ical_string, $attribute, [] );

		if ( count( $values ) ) {
			return reset( $values );
		}

		return $default;
	}

	/**
	 * Returns whether a value is an iCalendar format string or not.
	 *
	 * @since 6.0.1
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool Whether the value is an iCalendar format string or not.
	 */
	protected function is_icalendar_string( $value ): bool {
		if ( ! is_string( $value ) ) {
			return false;
		}

		foreach ( [ 'DTSTART', 'RRULE', 'RDATE' ] as $canary ) {
			if ( strpos( $value, $canary ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Updates the iCalendar format string to ensure the UNTIL attribute conforms to the requirement
	 * of having a UTC timezone.
	 *
	 * If the UNTIL attribute of the string is already specified in UTC timezone, it is left unchanged.
	 *
	 * @since 6.0.1
	 * @param string       $string   The iCalendar format string to update.
	 * @param DateTimeZone $timezone The timezone to use for the UNTIL attribute.
	 *
	 * @return string The updated iCalendar format string.
	 */
	protected function normalize_until_date( string $string, DateTimeZone $timezone ): string {
		$lines = explode( "\n", $string );

		array_walk( $lines, function ( string &$line ) use ( $timezone ): void {
			$until = $this->get_ical_string_attribute_value( $line, 'UNTIL' );

			if ( $until === null || strpos( $until, 'Z' ) === strlen( $until ) - 1 ) {
				return;
			}

			$utc = new DateTimezone( 'UTC' );
			$normalized_until = Dates::immutable( $until, $timezone )->setTimezone( $utc )->format( 'Ymd\THis\Z' );
			$line = $this->set_ical_string_attribute( $line, 'UNTIL', [ $normalized_until ] );
		} );

		return implode( "\n", $lines );
	}
}
<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Converter;

use DateTime;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Ical_Strings;
use WP_Post;
use RRule\RRule;
use TEC\Events\Custom_Tables\V1\Traits\With_Reflection;
use TEC\Events_Pro\Custom_Tables\V1\RRule\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\RRule\RSet_Wrapper;
use Tribe__Utils__Array;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

class From_Rset_Converter {
	use With_Reflection;
	use With_Ical_Strings;

	/**
	 * A map from recurrence types to the ascending weight they should have in a sort; heavier will
	 * sink, lighter will float.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,int>
	 */
	private static $type_weights = [
		'Daily'   => 0,
		'Weekly'  => 1,
		'Monthly' => 2,
		'Yearly'  => 3,
		'Date'    => 4,
	];

	/**
	 * Converts a recurring event rule set(s) information to the old (legacy) format.
	 *
	 * @since 6.0.0
	 *
	 * @param array|string $rset_data    The recurring event rule set or an array of rule sets.
	 * @param int|WP_Post  $post_id The event post ID or object.
	 *
	 * @return array An array representing the event recurrence information in the legacy format; this
	 *               is the value that should be stored in an event `recurrence` key when sent from the UI
	 *               to the backend or in the `_EventRecurrence` meta when read from the database.
	 *
	 * @throws Exception If there's any issue building the dates for the event.
	 */
	public function convert_to_event_recurrence( $rset_data, $post_id ): array {
		$post_id = $post_id instanceof WP_Post ? $post_id->ID : $post_id;

		$use_default_duration = false;

		if ( ! is_array( $rset_data ) ) {
			$use_default_duration = true;
		}
		// Setup our RSET DTSTART/DTEND definitions.
		$rset_string = implode( "\n", (array) $rset_data );
		$rset        = new RSet_Wrapper( $rset_string );
		$dtstart     = $rset->get_dtstart();
		$dtend       = $rset->get_dtend();

		// Default to the RSET's DTSTART/DTEND before retrieving outside data.
		$tz      = Timezones::build_timezone_object( get_post_meta( $post_id, '_EventTimezone', true ) );
		$dtstart = $dtstart ?? Dates::immutable( get_post_meta( $post_id, '_EventStartDate', true ), $tz );
		$dtend   = $dtend ?? Dates::immutable( get_post_meta( $post_id, '_EventEndDate', true ), $tz );

		return $this->convert_to_event_recurrence_from_dates( $rset_data, $dtstart, $dtend, $use_default_duration );
	}


	/**
	 * A map relating the week days abbreviations in iCal format to
	 * the value assigned to them in the UI.
	 *
	 * @var array
	 */
	protected static $day_map = [
		1 => 'MO',
		2 => 'TU',
		3 => 'WE',
		4 => 'TH',
		5 => 'FR',
		6 => 'SA',
		7 => 'SU',
	];

	/**
	 * A map relating the position of a day in the month to as the UI calls it to the format iCal uses.
	 *
	 * @var array
	 */
	protected static $monthly_positions_map = [
		'first'  => 1,
		'second' => 2,
		'third'  => 3,
		'fourth' => 4,
		'fifth'  => 5,
		'last'   => - 1,
	];

	/**
	 * Whether to allow Occurrences generated on the DTSTART day to be generated or not.
	 * RBE support if `true`, Legacy support is `false`.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $allow_occurrences_on_dtstart = false;

	/**
	 * Whether to reduce the COUNT of RRULEs to include the offset DTSTART or not.
	 * In the legacy code, a RRULE like Weekly, on Wed, 3 times with a DTSTART of
	 * 2022/04/04 (a Monday) would generate 3 Occurrences in total, including the
	 * DTSTART; the correct reading should generate 4 Occurrences, instead.
	 * When this flag is `false` 4 Occurrences in total would be generated, when
	 * `true`, only 3 Occurrences in total would be generated.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $reduce_count_to_include_offset_dtstart = true;

	/**
	 * Checks whether the payload contains legacy Admin UI Recurrence information or not.
	 *
	 * @since 6.0.0
	 *
	 * @param array $data The data coming from the legacy Admin UI.
	 *
	 * @return bool Whether the payload contains legacy Admin UI Recurrence information or not.
	 */
	public function contains_old_format_ruleset( array $data ) {
		return isset( $data['recurrence']['rules'] );
	}

	/**
	 * Maps the string representing a day position in the monthly recurrence to a number that
	 * the iCal format can use.
	 *
	 * @since 6.0.0
	 *
	 * @param string $position The day position in the month, e.g. "First", "Third" or "Last".
	 *
	 * @return int|string The signed integer number iCal format uses to represent the position
	 *                    in the month or the original `$position` value if not found.
	 */
	public static function monthly_position_to_number( $position ) {
		return Tribe__Utils__Array::get( static::$monthly_positions_map, strtolower( $position ), $position );
	}

	/**
	 * Maps a number to the string representing a day position in the monthly recurrence.
	 *
	 * @since 6.0.0
	 *
	 * @param int $number The day number in the month.
	 *
	 * @return int|string The string position, capitalized, or the original `$number` value if not found.
	 */
	public static function monthly_number_to_position( int $number ) {
		$found = array_search( (int) $number, static::$monthly_positions_map, true );

		return false === $found ? $number : ucwords( $found );
	}

	/**
	 * Returns the day abbreviation the iCal standard used corresponding to day number of the week.
	 *
	 * @since 6.0.0
	 *
	 * @param int $day The position of the day in the week.
	 *
	 * @return false|int The day abbreviation iCal uses to represent the day, e.g. "MO" for "Monday", or
	 *                   the input day if the day number was not found and, probably, does not make sense.
	 */
	public static function day_number_to_abbr( $day ) {
		return Tribe__Utils__Array::get( static::$day_map, (int) $day, $day );
	}

	/**
	 * Returns the day number corresponding to the day abbreviation.
	 *
	 * @since 6.0.0
	 *
	 * @param string $day_abbreviation The day abbreviation, e.g. "FR", "Fr" or "fr", "Friday".
	 *
	 * @return false|int The number, position in the week, corresponding to the day abbreviation; Monday is 0; the
	 *                   original day abbreviation value if not found.
	 */
	public static function day_abbr_to_number( $day_abbreviation ) {
		$day_abbreviation = substr( $day_abbreviation, 0, 2 );
		$found            = array_search( strtoupper( $day_abbreviation ), static::$day_map, true );

		return $found ? (int) $found : $day_abbreviation;
	}

	/**
	 * Returns the pretty version of the end day difference.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface $start The start date.
	 * @param DateTimeInterface $end The end date.
	 *
	 * @return string The string, or number as string, representing the end day difference in the
	 *                format used by the legacy recurrence format.
	 */
	protected function old_format_end_day( DateTimeInterface $start, DateTimeInterface $end ) {
		$immutable_start = $start instanceof DateTimeImmutable ? $start : DateTimeImmutable::createFromMutable( $start );
		$immutable_end   = $end instanceof DateTimeImmutable ? $end : DateTimeImmutable::createFromMutable( $end );

		// The end day value will be a string only for the same day, else a number.
		$map = [
			'same-day',
			'1',
			'2',
			'3',
			'4',
			'5',
			'6',
		];

		// Set start and end date at midnight.
		$day_diff = $immutable_end->setTime( 0, 0, 0 )->diff( $immutable_start->setTime( 0, 0, 0 ) )->d;

		/*
		 * Since we're using numbers for any end day that's not same day
		 * let's return a number, in string format, if the day difference
		 * cannot be mapped to a value we know.
		 */

		return Tribe__Utils__Array::get( $map, $day_diff, (string) $day_diff );
	}

	/**
	 * Converts an occurrence date information to the legacy format.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTime          $start          The occurrence date object.
	 * @param int               $duration       This rdates duration.
	 * @param DateTimeImmutable $dtstart        The event start date.
	 * @param int               $event_duration The main event duration.
	 *
	 * @return array An array of information representing the occurrence date in the legacy format.
	 * @throws Exception If there's any issue building the date objects.
	 */
	protected function convert_rdate_to_old( DateTimeInterface $start, int $duration, DateTimeImmutable $dtstart, int $event_duration ): array {
		/*
		 * If the start time is `00:00:00` there's a chance this rule set
		 * is the default one, let's adjust the start time to the
		 * event start time if that's the case.
		 */
		$this_is_midnight  = '00:00:00' === $start->format( 'H:i:s' );
		$event_is_midnight = '00:00:00' === $dtstart->format( 'H:i:s' );


		if ( $this_is_midnight && ! $event_is_midnight ) {
			$start = $start->setTime(
				$dtstart->format( 'H' ),
				$dtstart->format( 'i' ),
				$dtstart->format( 's' )
			);
		}

		$the_start_time = $start->format( 'H:i:s' );
		// Set up a mutable date, so we can increment to the end time.
		$end            = new DateTime( null, $start->getTimezone() );
		$end->setTimestamp( $start->getTimestamp() );
		$end->add( new DateInterval( "PT{$duration}S" ) );

		$event_start_time = $dtstart->format( 'H:i:s' );
		$same_time        = $the_start_time === $event_start_time && $duration === $event_duration;

		$converted = [
			'type'   => 'Custom',
			'custom' =>
				[
					'interval' => 1, // RDATEs have the interval set to int 1.
					'type'     => 'Date',
					'date'     =>
						[
							'date' => $start->format( 'Y-m-d' ),
						],
				],
		];

		if ( $same_time ) {
			$converted['custom']['same-time'] = 'yes';
		} else {
			$converted['custom']['same-time']  = 'no';
			$converted['custom']['start-time'] = $start->format( 'g:ia' );
			$converted['custom']['end-time']   = $end->format( 'g:ia' );
			$converted['custom']['end-day']    = $this->old_format_end_day( $start, $end );
		}
		$event_end = clone $dtstart;
		$event_end = $event_end->add( new DateInterval( 'PT' . $event_duration . 'S' ) );
		$converted['EventStartDate'] = $dtstart->format( 'Y-m-d H:i:s' );
		$converted['EventEndDate']   = $event_end->format( 'Y-m-d H:i:s' );

		return $converted;
	}

	/**
	 * Converts an exclusion date information to the legacy format.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTime          $start          The exclusion date exclusion object.
	 * @param int               $duration       The exclusion duration.
	 * @param DateTimeImmutable $dtstart        The event start date.
	 * @param int               $event_duration The event duration.
	 *
	 * @return array An array of information representing the exclusion date in the legacy format.
	 * @throws Exception If there's any issue building the date objects.
	 */
	protected function convert_exdate_to_old( DateTime $start, int $duration, DateTimeImmutable $dtstart, int $event_duration ): array {
		$converted = $this->convert_rdate_to_old( $start, $duration, $dtstart, $event_duration );
		// Exclusion dates always span the whole day.
		$converted['custom']['same-time'] = 'yes';
		unset( $converted['custom']['start-time'], $converted['custom']['end-time'], $converted['custom']['end-day'] );

		return $converted;
	}

	/**
	 * Converts an occurrence rule from the new format to the legacy one.
	 *
	 * The code in this method is willingly verbose: debugging it, with a verbose format as input is
	 * easier; it's a trade-off between comments and verbose code with the difference that the latter
	 * is inspectable during debug runs.
	 *
	 * @since 6.0.0
	 *
	 * @param RRule             $rrule          The rule object.
	 * @param int               $duration       The rule duration.
	 * @param DateTimeImmutable $dtstart        The event start date.
	 * @param int               $event_duration The event duration.
	 *
	 * @return array The occurrence rule information in the legacy format.
	 *
	 * @throws \ReflectionException If there's an issue accessing one of the RSET private properties.
	 */
	protected function convert_rrule_to_old( RRule $rrule, int $duration, DateTimeImmutable $dtstart, int $event_duration ): array {
		$rrule_data  = $rrule->getRule();
		$occurrences = $rrule->getOccurrences( 1 );

		if ( empty( $occurrences ) ) {
			/*
			 * This is a legit, albeit weird, case that should be handled.
			 * A recurrence rule like this "Yearly, on Jan 12, starting on 2018-01-10, ending on 2018-12-01
			 * would yield no occurrences although the recurrence pattern is correct.
			 * Since there is no way to "build back" the recurrence rule if there is not, at least, one
			 * occurrence, then the rule is left empty.
			 */

			return [];
		}

		$event_end_date = $dtstart->add( new DateInterval( 'PT' . (int) $event_duration . 'S' ) );
		$the_start_time = $dtstart->format( 'H:i:s' );
		$event_start_time = $dtstart->format( 'H:i:s' );
		$same_time = $the_start_time === $event_start_time && $duration === $event_duration;
		$type = ucwords( strtolower( $rrule_data['FREQ'] ) );

		$converted = [
			'type'           => 'Custom',
			'custom'         =>
				[
					'interval'  => (string) (int) $rrule_data['INTERVAL'],
					'type'      => $type,
				],
			'EventStartDate' => $dtstart->format( 'Y-m-d H:i:s' ),
			'EventEndDate'   => $event_end_date->format( 'Y-m-d H:i:s' ),
		];


		if ( 'Weekly' === $type ) {
			/*
			 * Sunday is the first day of the week with index `0`.
			 * The `RRule` object uses the same notation so, in place of adding a method
			 * to compute the weekday numbers from their abbreviations, we just read it from the RRule object.
			 */
			$converted['custom']['week']['day'] = array_map(
				'strval',
				(array) $this->get_private_property( $rrule, 'byweekday' )
			);
		} elseif ( 'Monthly' === $type) {
			$converted['custom']['month'] = [
				'same-day' => 'yes',
			];

			if ( ! empty( $rrule_data['BYDAY'] ) ) {
				if ( ! empty( $rrule_data['BYSETPOS'] ) ) {
					// Something like "FR" for "Friday" or "-1" for "Last day of the month".
					preg_match( '/^(?<day>\\w*)$/u', $rrule_data['BYDAY'], $matches );
					$converted['custom']['month']['same-day'] = 'no';
					$converted['custom']['month']['number']   = (int) $rrule_data['BYSETPOS'];
					$converted['custom']['month']['day']      = (string) static::day_abbr_to_number( ! empty( $matches['day'] ) ? $matches['day'] : '8' );
				} else {
					// Something like "2FR" for "2nd Friday" or "-1" for "Last day of the month".
					preg_match( '/^(?<number>(-){0,1}\\d+)(?<day>\\w*)$/u', $rrule_data['BYDAY'], $matches );
					$converted['custom']['month']['same-day'] = 'no';
					$converted['custom']['month']['number'] = static::monthly_number_to_position( (int) $matches['number'] );
					$converted['custom']['month']['day']      = (string) static::day_abbr_to_number( ! empty( $matches['day'] ) ? $matches['day'] : '8' );
				}
			} elseif ( ! empty( $rrule_data['BYMONTHDAY'] ) ) {
				// Something like "8", "On the 8th day of each month".
				$converted['custom']['month']['same-day'] = 'no';
				$converted['custom']['month']['number']       = (string) $rrule_data['BYMONTHDAY'];
			}
		} elseif ( 'Yearly' === $type ) {
			$converted['custom']['year'] = [
				// A comma-separated list of month numbers.
				'month'    => empty( $rrule_data['BYMONTH'] ) ? [ $dtstart->format( 'n' ) ] : [ $rrule_data['BYMONTH'] ],
				'same-day' => 'yes',
			];

			if ( ! empty( $rrule_data['BYDAY'] ) ) {
				if ( ! empty( $rrule_data['BYSETPOS'] ) ) {
					// Something like "FR" for "Friday" or "-1" for "Last day of the month".
					preg_match( '/^(?<day>\\w*)$/u', $rrule_data['BYDAY'], $matches );
					$converted['custom']['year']['same-day'] = 'no';
					$converted['custom']['year']['number']   = (int) $rrule_data['BYSETPOS'];
				} else {
					// Something like "2FR" for "2nd Friday" or "-1" for "Last day of the month".
					preg_match( '/^(?<number>(-){0,1}\\d+)(?<day>\\w*)$/u', $rrule_data['BYDAY'], $matches );
					$converted['custom']['year']['same-day'] = 'no';
					$converted['custom']['year']['number'] = static::monthly_number_to_position( (int) $matches['number'] );
				}
				$converted['custom']['year']['day'] = (string) static::day_abbr_to_number( ! empty( $matches['day'] ) ? $matches['day'] : '8' );
			} elseif ( ! empty( $rrule_data['BYMONTHDAY'] ) ) {
				$converted['custom']['year']['same-day'] = 'no';

				if ( (int) $rrule_data['BYMONTHDAY'] > 0 ) {
					// It's a day of the month, e.g. "on the 4th".
					$converted['custom']['year']['number'] = (string) $rrule_data['BYMONTHDAY'];
				} else {
					// It's a negative number, e.g. "-1" ("on the last day").
					$converted['custom']['year']['number'] = static::monthly_number_to_position( (int) $rrule_data['BYMONTHDAY'] );
					// The legacy format uses "8" to indicate "Day".
					$converted['custom']['year']['day'] = '8';
				}
			}
		}

		if ( $same_time ) {
			$converted['custom']['same-time'] = 'yes';
		} else {
			$occurrence = Occurrence::create_from_start_duration( $occurrences[0], $duration );
			$rset_dtstart_date = $occurrence->start();
			$rset_dtend_date = $occurrence->end();
			$converted['custom']['same-time'] = 'no';
			$converted['custom']['start-time'] = $rset_dtstart_date->format( 'g:ia' );
			$converted['custom']['end-time'] = $rset_dtend_date->format( 'g:ia' );
			$converted['custom']['end-day'] = $this->old_format_end_day( $rset_dtstart_date, $rset_dtend_date );
		}

		if ( $rrule_data['UNTIL'] instanceof DateTime ) {
			$converted['end-type'] = 'On';
			$until                 = clone $rrule_data['UNTIL'];
			$until->setTimezone( $dtstart->getTimezone() );
			$converted['end'] = $until->format( 'Y-m-d' );
		} elseif ( null !== $rrule_data['COUNT'] ) {
			$converted['end-type']              = 'After';
			$converted['end-count']             = (string) (int) $rrule_data['COUNT'];
		} else {
			$converted['end-type'] = 'Never';
		}

		return $converted;
	}

	/**
	 * Converts an exclusion rule from the new format to the legacy one.
	 *
	 * @since 6.0.0
	 *
	 * @param RRule             $exrule         The exclusion rule object.
	 * @param int               $duration       The rule duration.
	 * @param DateTimeImmutable $event_start    The event start date.
	 * @param int               $event_duration The event duration.
	 *
	 * @return array The exclusion rule information in the legacy format.
	 */
	protected function convert_exrule_to_old( RRule $exrule, int $duration, DateTimeImmutable $event_start, int $event_duration ): array {
		$converted = $this->convert_rrule_to_old( $exrule, $duration, $event_start, $event_duration );
		// Exclusion dates always span the whole day.
		$converted['custom']['same-time'] = 'yes';
		unset( $converted['custom']['start-time'], $converted['custom']['end-time'], $converted['custom']['end-day'] );

		return $converted;
	}

	/**
	 * Converts a recurrence rule from the iCalendar RSET format to the one used in the `_EventRecurrence` meta field.
	 *
	 * @since 6.0.1
	 *
	 * @param array<int,string>|string $rset_data            Either a set of RSETs, or a single RSET definition.
	 * @param DateTimeImmutable        $dtstart              The event start date.
	 * @param DateTimeImmutable        $dtend                The event end date.
	 * @param bool                     $use_default_duration Whether to use the default duration.
	 *
	 * @return array<array>|array Either a set of converted RSETs, or the converted RSET.
	 *
	 * @throws \ReflectionException If there's an issue accessing one of the RSET private properties.
	 */
	public function convert_to_event_recurrence_from_dates(
		$rset_data,
		DateTimeImmutable $dtstart,
		DateTimeImmutable $dtend,
		bool $use_default_duration = false
	): array {
		$event_start_timestamp = $dtstart->format( 'U' );
		$event_end_timestamp = $dtend->format( 'U' );
		$event_duration = $event_end_timestamp - $event_start_timestamp;

		$converted = [
			'rules'      => [],
			'exclusions' => [],
		];

		$rset_data = (array) $rset_data;

		/** @var RSet_Wrapper $rset */
		foreach ( $rset_data as $key_duration => $rset ) {
			$rset = $this->normalize_until_date( $rset, $dtstart->getTimezone() );
			$rset_object = new RSet_Wrapper( $rset, $dtstart );
			$rset_duration = $rset_object->get_duration();
			if ( $rset_duration !== null ) {
				// If the RSET has enough information to determine the duration, use it.
				$duration = $rset_duration;
			} else {
				$duration = $use_default_duration ? $event_duration : $key_duration;
			}

			$rrules = $rset_object->getRRules();

			/*
			 * The RSET should contain at most one RRULE, we drop any other just in case.
			 *
			 * @var RRule $rrule
			 */
			$rrule = count( $rrules ) ? reset( $rrules ) : null;

			if ( $rrule instanceof RRule ) {
				$converted_rrule = $this->convert_rrule_to_old( $rrule, $duration, $dtstart, $event_duration );

				if ( isset( $converted_rrule['end-count'] ) ) {
					// Increase the COUNT if the DSTART is not included in the RRULE: Legacy quirk.
					$first_occurrence = $rrule->offsetGet( 0 );
					if ( ! empty( $first_occurrence ) && $first_occurrence->format( 'U' ) !== $event_start_timestamp ) {
						/*
						 * Legacy would include the DTSTART in the COUNT limit of an RRULE even when not included in
						 * the RRULE: let's account for that.
						 */
						$converted_rrule['end-count'] ++;
					}
				}

				$converted['rules'][] = $converted_rrule;
			}

			foreach ( $rset_object->getDates() as $rdate ) {
				// Occurrence instances will model RDATE with diff. start and end times from the default ones.
				$rdate_duration = $rdate instanceof Occurrence ? $rdate->get_duration() : $event_duration;

				$converted['rules'][] = $this->convert_rdate_to_old( $rdate, $rdate_duration, $dtstart, $event_duration );
			}

			foreach ( $rset_object->getExDates() as $exdate ) {
				$exdate = $exdate instanceof Occurrence ? $exdate->start() : $exdate;
				$converted['exclusions'][] = $this->convert_exdate_to_old( $exdate, $duration, $dtstart, $event_duration );
			}

			// The RSET should contain at most one EXRULE, we drop any other just in case.
			$exrules = $rset_object->getExRules();
			$exrule = count( $exrules ) ? reset( $exrules ) : null;
			if ( $exrule instanceof RRule ) {
				$converted['exclusions'][] = $this->convert_exrule_to_old( $exrule, $duration, $dtstart, $event_duration );
			}
		}

		/*
		 * The `array_filter` call makes sure empty rules and exclusion specifications are dropped.
		 * The `convert_rule_to_old` method will not build back rules that yield no occurrences.
		 */
		$converted['rules'] = array_values( array_unique( array_filter( $converted['rules'] ), SORT_REGULAR ) );
		$converted['exclusions'] = array_values( array_unique( array_filter( $converted['exclusions'] ), SORT_REGULAR ) );

		// We cannot know so let's leave this empty.
		$converted['description'] = null;

		// Sort the converted rules by type to clean up the output.
		usort( $converted['rules'], [ $this, 'sort_rules_by_type' ] );
		usort( $converted['exclusions'], [ $this, 'sort_rules_by_type' ] );

		return $converted;
	}

	/**
	 * Returns a value to sort rules, or exclusions by type, putting the ones that would produce the highest
	 * count (i.e. Daily, Weeekly, ...) first.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule_a The first rule.
	 * @param array<string,mixed> $rule_b The second rule.
	 *
	 * @return int The comparison result.
	 */
	private function sort_rules_by_type( array $rule_a, array $rule_b ): int {
		$rule_a_type = ( isset( $rule_a['type'] ) && $rule_a['type'] !== 'Custom' ) ? $rule_a['type']
			: $rule_a['custom']['type'];
		$rule_b_type = ( isset( $rule_b['type'] ) && $rule_b['type'] !== 'Custom' ) ? $rule_b['type']
			: $rule_b['custom']['type'];

		return $rule_b_type <=> $rule_a_type;
	}
}
<?php
/**
 * Provides methods to check the nature of an Event `_EventRecurrence` meta value.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Traits;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Traits;

use DateInterval;
use DateTimeImmutable;
use TEC\Events_Pro\Custom_Tables\V1\Editors\Event;
use TEC\Events_Pro\Custom_Tables\V1\Events\Rules\Date_Rule;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

/**
 * Trait With_Event_Recurrence.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Traits;
 */
trait With_Event_Recurrence {
	/**
	 * Given an Exclusion rule, guess the amount of exclusion dates it would
	 * generate.
	 *
	 * The method, doing a static evaluation of the rule based on its attributes,
	 * might not be as precise for smaller counts, but will use the COUNT value
	 * if the rule has a COUNT limit. The approximation gets better at higher numbers
	 * where the exact count would be costly. So: it's ok.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The exclusion rule definition in the format
	 *                                  used in the `_EventRecurrence` meta value.
	 *
	 * @return float|int The approximate number of exclusion dates the rule would generate.
	 */
	private function guess_rule_count( array $rule ) {
		$type     = isset( $rule['custom']['type'] ) ? $rule['custom']['type'] : 'n/a';
		$end_type = isset( $rule['end-type'] ) ? $rule['end-type'] : 'Never';

		$count           = null;
		$end_timestamp   = null;
		$start_timestamp = isset( $rule['EventStartDate'] ) ? Dates::immutable( $rule['EventStartDate'] )->getTimestamp() : time();
		$interval        = isset( $rule['custom']['interval'] ) ? $rule['custom']['interval'] : 1;

		switch ( $end_type ) {
			case 'After':
				$count = (int) $rule['end-count'];
				break;
			case 'On':
				$end_timestamp = Dates::immutable( $rule['end'] )->getTimestamp();
				break;
			case 'Never':
			default:
				$end_timestamp = $this->get_never_limit_date()->getTimestamp();
				break;
		}

		switch ( $type ) {
			case 'Date':
				return 1;
			case 'Daily':
				return $count ?: (int) ( ( $end_timestamp - $start_timestamp ) / DAY_IN_SECONDS / $interval );
			case 'Weekly':
				// "Weekly 4 days a week" will generate more dates than "Weekly, 2 days a week".
				$days = isset( $rule['custom']['week']['day'] ) ?
					count( (array) $rule['custom']['week']['day'] )
					: 1;

				return $count ?: (int) ( ( $end_timestamp - $start_timestamp ) / DAY_IN_SECONDS / 7 / $interval * $days );
			case 'Monthly':

				return $count ?: (int) ( ( $end_timestamp - $start_timestamp ) / DAY_IN_SECONDS / 30 / $interval );
			case 'Yearly':
				// "Yearly, 4 months each year" will generate more dates than "Yearly, 2 months a year".
				$months = isset( $rule['custom']['year']['month'] ) ?
					count( (array) $rule['custom']['year']['month'] )
					: 1;

				return $count ?: (int) ( ( $end_timestamp - $start_timestamp ) / DAY_IN_SECONDS / 365 / $interval * $months );
			default:
				return 0;
		}
	}

	/**
	 * Sorts a set of recurrence rules in the format used by the `_EventRecurrence` meta
	 * value by descending guessed count.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array<string,mixed>> $rules A set of recurrence rules to sort.
	 *
	 * @return array<array<string,mixed>> The sorted set of recurrence rules.
	 */
	protected function sort_rules_by_count( array $rules ): array {
		$rule_index_to_count_map = array_map( [ $this, 'guess_rule_count' ], $rules );
		arsort( $rule_index_to_count_map, SORT_NUMERIC );

		// Impose the order of left array on the right one.
		return array_values( array_replace( $rule_index_to_count_map, $rules ) );
	}

	/**
	 * Counts the number of Recurrence Rules in the `rules` component of the `_EventRecurrence` format
	 * meta of an Event that are RRULEs.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,mixed> $rules An array of recurrence rules from the Event `_EventRecurrence` meta value.
	 *
	 * @return int The number of recurrence Rules that would map to an iCalendar standard RRULE.
	 */
	private function count_rrules( array $rules ): int {
		if ( ! is_array( $rules ) ) {
			return 0;
		}

		return count( array_filter( $rules, [ $this, 'is_rrule' ] ) );
	}

	/**
	 * Checks a recurrence rule type to assess whether it's a RRULE or an RDATE.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The rule entry.
	 *
	 * @return bool Whether the rule is an RRULE or not.
	 */
	private function is_rrule( array $rule ): bool {
		if ( ! is_array( $rule ) ) {
			return false;
		}

		if ( isset( $rule['type'] ) && ! in_array( $rule['type'], [ 'Custom', 'Date' ], true ) ) {
			// The type is defined and neither Custom nor Date, so it's a RRULE.
			return true;
		}

		return isset( $rule['custom']['type'] ) && $rule['custom']['type'] !== 'Date';
	}

	/**
	 * Checks a recurrence rule type to assess whether it's an RDATE recurrence rule or not.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The rule entry.
	 *
	 * @return bool Whether the rule is an RDATE or not.
	 */
	private function is_rdate( array $rule ): bool {
		return is_array( $rule )
		       && (
			       ( isset( $rule['custom']['type'] ) && $rule['custom']['type'] === 'Date' )
			       || ( isset( $rule['type'] ) && $rule['type'] === 'Date' )
		       );
	}

	/**
	 * Builds the date-related objects from the input recurrence rule in the format used by the
	 * `_EventRecurrence` meta value.
	 *
	 * @since 6.0.0
	 *
	 * @param int|null            $post_id The Event post ID.
	 * @param array<string,mixed> $rule    The recurrence rule in the format used by the `_EventRecurrence` meta value.
	 *
	 * @return array{DateTimeImmutable, DateTimeImmutable, int} The date-related objects.
	 */
	private function build_date_data_from_rule( int $post_id = null, array $rule = null ): array {
		$timezone     = Timezones::build_timezone_object( get_post_meta( $post_id, '_EventTimezone', true ) );
		$start_string = empty( $rule['EventStartDate'] ) ?
			get_post_meta( $post_id, '_EventStartDate', true )
			: $rule['EventStartDate'];
		$end_string   = empty( $rule['EventEndDate'] ) ?
			get_post_meta( $post_id, '_EventEndDate', true )
			: $rule['EventEndDate'];
		$start        = Dates::immutable( $start_string, $timezone );
		$end          = Dates::immutable( $end_string, $timezone );
		$duration     = $end->getTimestamp() - $start->getTimestamp();

		return array( $start, $end, $duration );
	}

	/**
	 * Returns whether a daily rule DSTART is off-pattern or not.
	 *
	 * The DSTART is off-pattern when it would not be generated by the recurrence
	 * rule.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array> $rule    The recurrence rule in the format used in the
	 *                                     `_EventRecurrence` meta.
	 * @param int|null            $post_id The Event post ID.
	 *
	 * @return bool Whether the rule DTSTART is off-pattern or not.
	 */
	private function is_daily_rule_dtstart_off_pattern( array $rule, ?int $post_id = null ): bool {
		if ( tribe_is_truthy( $rule['custom']['same-time'] ) ) {
			return false;
		}

		[ $start, $end, $duration ] = $this->build_date_data_from_rule( $post_id, $rule );

		return ! $this->diff_time_data_matches( $start, $end, $duration, $rule['custom'] );
	}

	/**
	 * Returns whether a weekly rule DSTART is off-pattern or not.
	 *
	 * The DSTART is off-pattern when it would not be generated by the recurrence
	 * rule.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array> $rule    The recurrence rule in the format used in the
	 *                                     `_EventRecurrence` meta.
	 * @param int|null            $post_id The Event post ID.
	 *
	 * @return bool Whether the rule DTSTART is off-pattern or not.
	 */
	private function is_weekly_rule_dstart_off_pattern( array $rule, ?int $post_id = null ): bool {
		[ $start, $end, $duration ] = $this->build_date_data_from_rule( $post_id, $rule );
		$same_day = in_array( $start->format( 'N' ), $rule['custom']['week']['day'], false );

		if ( ! $same_day ) {
			return true;
		}

		if ( tribe_is_truthy( $rule['custom']['same-time'] ) ) {
			return false;
		}

		return ! $this->diff_time_data_matches( $start, $end, $duration, $rule['custom'] );
	}

	/**
	 * Returns whether a monthly rule DSTART is off-pattern or not.
	 *
	 * The DSTART is off-pattern when it would not be generated by the recurrence
	 * rule.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array> $rule    The recurrence rule in the format used in the
	 *                                     `_EventRecurrence` meta.
	 * @param int|null            $post_id The Event post ID.
	 *
	 * @return bool Whether the rule DTSTART is off-pattern or not.
	 */
	private function is_monthly_rule_dstart_off_pattern( array $rule, ?int $post_id = null ): bool {
		[ $start, $end, $duration ] = $this->build_date_data_from_rule( $post_id, $rule );

		if ( ! ( $this->is_same_month_day( $rule, $start, 'month' ) ) ) {
			return true;
		}

		return ! $this->diff_time_data_matches( $start, $end, $duration, $rule['custom'] );
	}

	/**
	 * Returns whether a yearly rule DSTART is off-pattern or not.
	 *
	 * The DSTART is off-pattern when it would not be generated by the recurrence
	 * rule.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array> $rule    The recurrence rule in the format used in the
	 *                                     `_EventRecurrence` meta.
	 * @param int|null            $post_id The Event post ID.
	 *
	 * @return bool Whether the rule DTSTART is off-pattern or not.
	 */
	private function is_yearly_rule_dstart_off_pattern( array $rule, ?int $post_id = null ): bool {
		[ $start, $end, $duration ] = $this->build_date_data_from_rule( $post_id, $rule );

		if ( ! ( $this->is_same_month_day( $rule, $start, 'year' ) ) ) {
			return true;
		}

		$is_same_year_month = empty( $rule['custom']['year']['month'] )
		                      || in_array(
			                      $start->format( 'n' ),
			                      (array) $rule['custom']['year']['month'],
			                      false
		                      );
		if ( ! $is_same_year_month ) {
			return true;
		}

		return ! $this->diff_time_data_matches( $start, $end, $duration, $rule['custom'] );
	}

	/**
	 * Returns whether the data for a rule same time actually matches or not.
	 *
	 * The method is robust to the fact that the rule could specify the time is not
	 * the same but the data would be, actually, the same.
	 *
	 * @since 6.0.0
	 *
	 * @param \DateTimeImmutable $start    A reference to the start date and time object.
	 * @param array              $custom   The rule `custom` component.
	 * @param \DateTimeImmutable $end      A reference to the end date and time object.
	 * @param int                $duration The event duration in seconds.
	 *
	 * @return bool Whether the rule is actually on a different time than the DTSTART or not.
	 */
	private function diff_time_data_matches( DateTimeImmutable $start, DateTimeImmutable $end, int $duration, array $custom ): bool {
		if ( ! isset( $custom['start-time'], $custom['end-time'], $custom['end-day'] ) ) {
			return true;
		}

		$custom_start = Dates::immutable( $custom['start-time'] );
		$custom_end   = Dates::immutable( $custom['end-time'] );

		return $start->format( 'ga' ) === $custom_start->format( 'ga' )
		       && $end->format( 'ga' ) === $custom_end->format( 'ga' )
		       && (int) floor( $duration / DAY_IN_SECONDS ) === (int) $custom['end-day'];
	}

	/**
	 * Returns whether a monthly or yearly rule is specifying a different
	 * day in the month actually different from the DSTART.
	 *
	 * The method is robust to the fact that the rule could specify the time is not
	 * the same but the data would be, actually, the same.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule     The rule in the array format used by the `_EventRecurrence`
	 *                                      meta.
	 * @param \DateTimeImmutable  $start    A reference to the start date and time object.
	 * @param string              $type_key The rule type, either `month` or `year.
	 *
	 * @return bool Whether the rule month day is actually not the same as the DTSTART or not.
	 */
	private function is_same_month_day( array $rule, DateTimeImmutable $start, string $type_key ): bool {
		$int_to_ordinal_map    = [
			1   => 'First',
			2   => 'Second',
			3   => 'Third',
			4   => 'Fourth',
			5   => 'Fifth',
			- 1 => 'Last',
		];
		$last_day_of_month_day = 8;
		$dtstart_weekday       = (int) $start->format( 'N' ); // 1 is Monday, Sunday is 7.
		$dtstart_month_day     = (int) $start->format( 'j' );
		$dtstart_month_pos     = $int_to_ordinal_map[ ceil( $dtstart_month_day / 7 ) ];
		$possible_month_pos    = [ $dtstart_month_pos ];
		// If moving to next week would exceed the number of days in the month, it's the last day too.
		$is_last = $dtstart_month_day + 7 > $start->format( 't' );
		if ( $dtstart_month_pos !== 'Last' && $is_last ) {
			// If the position of the day in the month happens to be the last as well, allow that.
			$possible_month_pos[] = 'Last';
		}

		if (
			! isset( $rule['custom'][ $type_key ]['same-day'] )
			|| tribe_is_truthy( $rule['custom'][ $type_key ]['same-day'] )
		) {
			return true;
		}
		if (
			// Day number of the month matches.
			isset( $rule['custom'][ $type_key ]['number'] )
			&& is_numeric( ( $rule['custom'][ $type_key ]['number'] ) )
			&& $dtstart_month_day === (int) $rule['custom'][ $type_key ]['number']
		) {
			return true;
		}
		if (
			// Day position in the month matches.
			isset( $rule['custom'][ $type_key ]['number'], $rule['custom'][ $type_key ]['day'] )
			&& ! is_numeric( ( $rule['custom'][ $type_key ]['number'] ) )
			&& $dtstart_weekday === (int) $rule['custom'][ $type_key ]['day']
			&& in_array( $rule['custom'][ $type_key ]['number'], $possible_month_pos, true )
		) {
			return true;
		}

		if (
			// Last day of month
			isset( $rule['custom'][ $type_key ]['number'], $rule['custom'][ $type_key ]['day'] )
			&& ! is_numeric( ( $rule['custom'][ $type_key ]['number'] ) )
			&& $last_day_of_month_day === (int) $rule['custom'][ $type_key ]['day']
			&& in_array( $rule['custom'][ $type_key ]['number'], $possible_month_pos, true )
			&& $start->format('t') === $start->format('j')
		) {
			return true;
		}

		return false;
	}

	/**
	 * Updates a recurrence rule in the array format used by the `_EventRecurrence` meta
	 * to set a flag indicating whether the DTSTART is off-pattern or not.
	 *
	 * The DSTART is off-pattern when it would not be generated by the recurrence
	 * rule.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array>|mixed $rule    The recurrence rule in the format used in the
	 *                                           `_EventRecurrence` meta.
	 * @param int|null                  $post_id The Event post ID.
	 * @param bool                      $reset   Whether to reset the flag or respect the one
	 *                                           already set, if found.
	 *
	 * @return array<string,array> The updated recurrence rule, in the format used by the
	 *                             `_EventRecurrence` meta.
	 */
	private function add_off_pattern_flag_to_rule( $rule, int $post_id = null, bool $reset = false ): array {
		if ( ! (
			is_array( $rule )
			&& ( isset( $rule['custom']['type'] ) || isset( $rule['type'] ) ) )
		) {
			return $rule;
		}

		$flag = Event::OFF_PATTERN_DTSTART_FLAG;

		if ( ! $reset && isset( $rule[ $flag ] ) ) {
			return $rule;
		}

		$rule[ $flag ] = $this->is_rule_dtstart_off_pattern( $rule, $post_id );

		return $rule;
	}

	/**
	 * Updates an exclusion rule in the array format used by the `_EventRecurrence` meta
	 * to set a flag indicating whether the DTSTART is off-pattern or not.
	 *
	 * The DSTART is off-pattern when it would not be generated by the exclusion
	 * rule.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array> $rule    The exclusion rule in the format used in the
	 *                                     `_EventRecurrence` meta.
	 * @param int                 $post_id The Event post ID.
	 * @param bool                $reset   Whether to reset the flag or respect the one
	 *                                     already set, if found.
	 *
	 * @return array<string,array> The updated exclusion rule, in the format used by the
	 *                             `_EventRecurrence` meta.
	 */
	private function add_off_pattern_flag_to_exclusion( array $rule, int $post_id, bool $reset = false ): array {
		return $this->add_off_pattern_flag_to_rule( $rule, $post_id, $reset );
	}

	/**
	 * Updates an `_EventRecurrence` format array to add a flag to each rule indicating
	 * whether the DTSTART would be off-pattern for that rule or not.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array>|string $event_recurrence_meta The Event recurrence meta, in the
	 *                                                          format used by the `_EventRecurrence`
	 *                                                          meta.
	 * @param int                        $post_id               The Event post ID.
	 * @param bool                       $reset                 Whether to reset the flag or respect the one
	 *                                                          already set, if found.
	 *
	 * @return array<string,array>|string The updated Event recurrence meta, or the input value if not valid.
	 */
	public function add_off_pattern_flag_to_meta_value( $event_recurrence_meta, int $post_id, bool $reset = false ) {
		if ( ! ( is_array( $event_recurrence_meta ) && isset( $event_recurrence_meta['rules'] ) ) ) {
			return $event_recurrence_meta;
		}

		if ( count( array_filter( [
				get_post_meta( $post_id, '_EventStartDate', true ),
				get_post_meta( $post_id, '_EventEndDate', true ),
				get_post_meta( $post_id, '_EventTimezone', true ),
			] ) ) !== 3 ) {
			return $event_recurrence_meta;
		}

		$event_recurrence_meta ['rules'] = array_map( function ( $rule ) use ( $post_id, $reset ) {
			return $this->add_off_pattern_flag_to_rule( $rule, $post_id, $reset );
		}, $event_recurrence_meta['rules'] );

		if ( isset( $event_recurrence_meta['exclusions'] ) ) {
			$event_recurrence_meta ['exclusions'] = array_map( function ( $rule ) use ( $post_id, $reset ) {
				return $this->add_off_pattern_flag_to_exclusion( $rule, $post_id, $reset );
			}, $event_recurrence_meta['exclusions'] );
		}

		return $event_recurrence_meta;
	}

	/**
	 * Returns whether the rule DTSTART is off-pattern in respect to the RRULE or not.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule    The rule in the format used in the `_EventRecurrence` meta.
	 * @param int|null            $post_id The Event post ID.
	 *
	 * @return bool Whether the rule DTSTART is off-pattern or not.
	 */
	private function is_rule_dtstart_off_pattern( array $rule, int $post_id = null ): bool {
		$type = isset( $rule['type'] ) && $rule['type'] !== 'Custom' ? $rule['type'] : $rule['custom']['type'];

		switch ( $type ) {
			case 'Daily':
				$is_dtstart_off_pattern = $this->is_daily_rule_dtstart_off_pattern( $rule, $post_id );
				break;
			case 'Weekly':
				$is_dtstart_off_pattern = $this->is_weekly_rule_dstart_off_pattern( $rule, $post_id );
				break;
			case 'Monthly':
				$is_dtstart_off_pattern = $this->is_monthly_rule_dstart_off_pattern( $rule, $post_id );
				break;
			case 'Yearly':
				$is_dtstart_off_pattern = $this->is_yearly_rule_dstart_off_pattern( $rule, $post_id );
				break;
			default:
				$is_dtstart_off_pattern = false;
				break;
		}

		return $is_dtstart_off_pattern;
	}

	/**
	 * Update an array of recurrence rules in the `_EventRecurrence` format to replace an RDATE
	 * entry with another.
	 *
	 * Note: if the RDATE is not found in the array, it is NOT added.
	 *
	 * @since 6.0.0
	 *
	 * @param array     $recurrence The recurrence rules in the format used in the `_EventRecurrence` meta.
	 * @param Date_Rule $from_rdate A reference to the RDATE entry to replace.
	 * @param Date_Rule $to_rdate   A reference to the RDATE entry to replace with.
	 *
	 * @return array The updated recurrence rules.
	 */
	private function update_rdate_in_event_recurrence_with( array $recurrence, Date_Rule $from_rdate, Date_Rule $to_rdate ): array {
		if ( ! isset( $recurrence['rules'] ) ) {
			return $recurrence;
		}

		// Update in place the RDATE rule this request came from to keep the changes applied by the user.
		foreach ( $recurrence['rules'] as &$rule ) {
			if ( ! $this->is_rdate( $rule ) ) {
				continue;
			}

			$this_rdate = Date_Rule::from_event_recurrence_format( $rule, $to_rdate->dtstart(), $to_rdate->dtend() );

			if ( $this_rdate->equals( $from_rdate ) ) {
				$rule = $to_rdate->to_event_recurrence_format();
				break;
			}
		}
		unset( $rule );

		return $recurrence;
	}

	/**
	 * Updates a Blocks Editor format recurrence or exclusion rule to normalize the same-time fields to the
	 * `isOffStart` flag, if required.
	 *
	 * Blocks Editor data is, at times, inconsistent in its handling of the same time fields.
	 *
	 * @since 6.0.1
	 *
	 * @param array<string,mixed> $rule    The rule in the format used in the Blocks Editor.
	 * @param DateTimeImmutable   $dtstart The start date of the Event to use to set the time.
	 * @param DateTimeImmutable   $dtend   The end date of the Event to use to set the time.
	 *
	 * @return array<string,mixed> The updated rule.
	 */
	private function normalize_blocks_format_rule_same_time( array $rule, DateTimeImmutable $dtstart, DateTimeImmutable $dtend ): array {
		$time_format = get_option( 'time_format', Dates::TIMEFORMAT );
		if ( isset( $rule['isOffStart'] ) && $rule['isOffStart'] === false && $rule['type'] !== 'single' ) {
			$rule['_start_time_input'] = $dtstart->format( $time_format );
			$rule['start_time'] = $dtstart->format( Dates::DBTIMEFORMAT );
			$rule['_end_time_input'] = $dtend->format( $time_format );
			$rule['end_time'] = $dtend->format( Dates::DBTIMEFORMAT );
			$rule['same-time'] = 'yes';
		}

		return $rule;
	}
}
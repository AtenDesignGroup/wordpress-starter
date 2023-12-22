<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter;

use DateTimeInterface;
use TEC\Events_Pro\Custom_Tables\V1\Events\Converter\From_Event_Recurrence_Converter;
use TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error;
use Tribe__Utils__Array as Arr;
use Tribe__Date_Utils as Dates;

class From_Event_Rule_Converter {
	/**
	 * A map relating the week days abbreviations in iCal format to
	 * the value assigned to them in the UI.
	 *
	 * @var array
	 */
	private static $day_map = [
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
	private static $monthly_positions_map = [
		'first'  => 1,
		'second' => 2,
		'third'  => 3,
		'fourth' => 4,
		'fifth'  => 5,
		'last'   => - 1,
	];

	/**
	 * A reference to the `_EventRecurrence` to RSET converter.
	 *
	 * @since 6.0.0
	 *
	 * @var From_Event_Recurrence_Converter
	 */
	private $converter;

	/**
	 * The rule in the format used in the `_EventRecurrence` meta value.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed>
	 */
	private $rule;

	/**
	 * @since 6.0.0
	 *
	 * @param From_Event_Recurrence_Converter $converter
	 * @param array<string,mixed>             $rule
	 */
	public function __construct( $converter, $rule ) {
		$this->converter = $converter;
		$this->rule      = $rule;
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
		return Arr::get( static::$monthly_positions_map, strtolower( $position ), $position );
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
		return Arr::get( static::$day_map, (int) $day, $day );
	}

	/**
	 * @since 6.0.0
	 *
	 * @param bool $is_exrule Parse for an exdate/exrule format.
	 *
	 * @return string A RRULE string based on the event recurrence rule.
	 *
	 * @throws Requirement_Error If the rule is not valid or the date data is not valid.
	 */
	public function convert_to_rrule( bool $is_exrule = false ): string {
		$rule = $this->rule;

		$rule = $this->normalize_rule_same_time( $rule );
		$rule = $this->update_rule_dtstart_dtend( $rule );

		if ( 'Custom' === $rule['type'] ) {
			if ( ! isset( $rule['custom']['type'] ) ) {
				throw Requirement_Error::due_to_missing_required_information(
					'rule.custom.type',
					'Custom rule type',
					wp_json_encode( $rule )
				);
			}

			$rule['type'] = $rule['custom']['type'];
		}

		switch ( $rule['type'] ) {
			case 'Date':
				$converter = new Date_Converter( $this->converter, $rule );
				break;
			case 'Daily':
				$converter = new Daily_Converter( $this->converter, $rule );
				break;
			case 'Weekly':
				$converter = new Weekly_Converter( $this->converter, $rule );
				break;
			case 'Monthly':
				$converter = new Monthly_Converter( $this->converter, $rule );
				break;
			case 'Yearly':
				$converter = new Yearly_Converter( $this->converter, $rule );
				break;
			default:
				return '';
		}

		return $converter->convert_to_rrule( $is_exrule );
	}

	/**
	 * A utility method to convert a single rule.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface|string|int $dtstart   The start date of the event.
	 * @param DateTimeInterface|string|int $dtend     The end date of the event.
	 * @param array                        $rule      The rule to convert.
	 * @param bool                         $is_exrule Whethe to convert to RRULE or EXRULE.
	 *
	 * @return string The converted RRULE string.
	 */
	public static function convert( $dtstart, $dtend, array $rule, bool $is_exrule = false ): string {
		try {
			$dtstart = Dates::immutable( $dtstart );
			$dtend = Dates::immutable( $dtend );
			$converter = new From_Event_Recurrence_Converter( $dtstart, $dtend );

			return ( new self( $converter, $rule ) )->convert_to_rrule( $is_exrule );
		} catch ( \Exception $e ) {
			return '';
		}
	}

	/**
	 * Normalizes the rule `same-time` flag and related entries to the rule current DTSTART, DTEND.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The rule to normalize.
	 *
	 * @return array<string,mixed> The normalized rule.
	 */
	protected function normalize_rule_same_time( array $rule ): array {
		if ( empty( $rule['custom']['same-time'] ) ) {
			$rule['custom']['same-time'] = 'yes';
		}
		if ( $rule['custom']['same-time'] === 'no' ) {
			$diff_start_time  = Dates::immutable( $rule['custom']['start-time'] );
			$diff_end_time    = Dates::immutable( $rule['custom']['end-time'] );
			$rule_dtstart     = Dates::immutable( $rule['EventStartDate'] );
			$rule_dtend       = Dates::immutable( $rule['EventEndDate'] );
			$start_matches    = $diff_start_time->format( 'h:i:00' ) === $rule_dtstart->format( 'h:i:00' );
			$end_matches      = $diff_end_time->format( 'h:i:00' ) === $rule_dtend->format( 'h:i:00' );
			$rule_end_day     = isset( $rule['end-day'] ) && is_numeric( $rule['end-day'] ) ? (int) $rule['end-day'] : 0;
			$duration_matches = $rule_dtend->diff( $rule_dtstart )->days === $rule_end_day;
			if ( $start_matches && $end_matches && $duration_matches ) {
				$rule['custom']['same-time'] = 'yes';
				unset( $rule['custom']['start-time'], $rule['custom']['end-time'], $rule['custom']['end-day'] );
			}
		}

		return $rule;
	}

	/**
	 * Updates the rule DTSTART and DTEND to the values set in the parent converter.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The rule to update.
	 *
	 * @return array<string,mixed> The updated rule.
	 */
	protected function update_rule_dtstart_dtend( array $rule ): array {
		$rule['EventStartDate'] = $this->converter->get_start_date()->format( Dates::DBDATETIMEFORMAT );
		$rule['EventEndDate'] = $this->converter->get_end_date()->format( Dates::DBDATETIMEFORMAT );

		return $rule;
	}
}
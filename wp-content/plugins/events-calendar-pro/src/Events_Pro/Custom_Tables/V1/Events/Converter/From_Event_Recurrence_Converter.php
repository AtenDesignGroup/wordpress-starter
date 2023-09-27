<?php
/**
 * A converter of Event recurrence rules from the format used in the `_EventRecurrence` meta
 * to the RSET one.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Converter;

use Couchbase\IndexFailureException;
use DateTime;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use DateTimeInterface;
use Exception;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events_Pro\Custom_Tables\V1\RRule\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Date_Operations;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Event_Recurrence;
use TEC\Events\Custom_Tables\V1\Traits\With_Reflection;
use TEC\Events\Custom_Tables\V1\Traits\With_Timezones;
use TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error;
use \TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter\From_Event_Rule_Converter;
use TEC\Events_Pro\Custom_Tables\V1\RRule\RSet_Wrapper;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Ical_Strings;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

/**
 * Class From_Event_Recurrence_Converter.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter;
 */
class From_Event_Recurrence_Converter {
	use With_Event_Recurrence;
	use With_Timezones;
	use With_Reflection;
	use With_Date_Operations;
	use With_Ical_Strings;

	/**
	 * A reference to the Event start date object.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $start_date;

	/**
	 * A reference to the Event end date object.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $end_date;

	/**
	 * A reference to the Event timezone.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimezone
	 */
	private $timezone;

	/**
	 * The duration, in seconds, of the Event to convert.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	private $duration_in_seconds;

	/**
	 * The recurrence rules to convert, in the format used by the `_EventRecurrence` meta.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed.
	 */
	private $rules = [];

	/**
	 * A map relating the week days abbreviations in iCal format to
	 * the value assigned to them in the UI.
	 *
	 * @since 6.0.0
	 *
	 * @var array<int,string>
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
	 * A backup of the Event original recurrence rules in the `_EventRecurrence` format.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed>
	 */
	private $recurrence_array = [];

	/**
	 * From_Event_Recurrence_Converter constructor.
	 *
	 * since 6.0.0
	 *
	 * @param DateTimeInterface|null $dtstart An optional DTSTART object reference to initialize the
	 *                                        converter on.
	 * @param DateTimeInterface|null $dtend   An optional DTEND object reference to initialize the
	 *                                        converter on; it will be ignored if the `$dtstart` parameter
	 *                                        is not provided.
	 */
	public function __construct( DateTimeInterface $dtstart = null, DateTimeInterface $dtend = null ) {
		if ( null === $dtstart || null === $dtend ) {
			return;
		}

		$this->start_date = $dtstart;
		$this->timezone = $dtstart->getTimezone();
		$this->end_date = $dtend;
		$this->duration_in_seconds = $dtend->getTimestamp() - $dtstart->getTimestamp();
	}

	/**
	 * Returns a reference to the Event immutable start date object.
	 *
	 * @since 6.0.0
	 *
	 * @return DateTimeImmutable A reference to the Event immutable start date object.
	 */
	public function get_start_date() {
		return $this->start_date;
	}

	/**
	 * Returns a reference to the Event immutable end date object.
	 *
	 * @since 6.0.0
	 *
	 * @return DateTimeImmutable A reference to the Event immutable end date object.
	 */
	public function get_end_date() {
		return $this->end_date;
	}

	/**
	 * Returns a reference to the Event timezone object.
	 *
	 * @since 6.0.0
	 *
	 * @return DateTimeZone A reference to the Event timezone object.
	 */
	public function get_timezone() {
		return $this->timezone;
	}

	/**
	 * Returns the duration, in seconds, of the Event to convert the rules for.
	 *
	 * @since 6.0.0
	 *
	 * @return int The Event duration in seconds.
	 */
	public function get_duration_in_seconds() {
		return $this->duration_in_seconds;
	}

	/**
	 * Returns the Event recurrence rules in the format used by the `_EventRecurrence` meta.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string,mixed> The Event recurrence rules in the `_EventRecurrence` meta format.
	 */
	public function get_rules() {
		return $this->rules;
	}

	/**
	 * Converts an Event recurrence rules from the `_EventRecurrence` format to the RSET one.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface|string|int $dtstart  string $start_date The Event start date.
	 * @param DateTimeInterface|string|int $dtend    The Event end date.
	 * @param DateTimezone|string          $timezone The Event timezone object or name.
	 * @param array<string,mixed>          $rules    The Event recurrence rules in the `_EventRecurrence` meta format.
	 *
	 * @return array<int,string> A map from durations to the RSET format recurrence rules converted
	 *                           for the Event.
	 *
	 * @throws Requirement_Error If there's any issue building from the provided data.
	 */
	public function convert_to_rset( $dtstart, $dtend, $timezone, array $rules ): array {
		$this->timezone = Timezones::build_timezone_object( Timezones::get_valid_timezone( $timezone ) );
		$this->start_date = Dates::immutable( $dtstart, $this->timezone );
		$this->end_date = Dates::immutable( $dtend, $this->timezone );
		$this->duration_in_seconds = $this->end_date->format( 'U' ) - $this->start_date->format( 'U' );
		$this->rules = $rules;

		return $this->convert_to_new_format( [
			'recurrence'     => $this->rules,
			'EventStartDate' => $this->start_date->format( 'Y-m-d H:i:s' ),
			'EventEndDate'   => $this->end_date->format( 'Y-m-d H:i:s' ),
			'EventDuration'  => $this->duration_in_seconds,
			'EventTimezone'  => $this->timezone->getName(),
		] );
	}

	/**
	 * Reduces the After value of a RRULE with an "ends after" limit by a value.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rrule The RRULE in array format.
	 * @param int                 $value The value to reduce the RRULE COUNT entry by.
	 *
	 * @return array<string,mixed> The RRULE in array format.
	 */
	private function reduce_rrule_count_by( array $rrule, $value ) {
		if ( ! (
			isset( $rrule['end-count'], $rrule['end-type'] )
			&& 'After' === $rrule['end-type']
			&& $rrule['end-count'] > 1
		) ) {
			return $rrule;
		}

		$rrule['end-count'] = (int) $rrule['end-count'] - (int) $value;

		return $rrule;
	}

	/**
	 * Converts the recurrence data found in the legacy Admin UI payload into a
	 * recurrence set or set group.
	 *
	 * @since 6.0.0
	 *
	 * @param array $data                        The data coming from the legacy Admin UI.
	 * @param bool  $include_dtstart             Whether the `DTSTART` string should be prefixed
	 *                                           to the converted rule sets or not.
	 *
	 * @return array|false An array of converted rule sets in a duration to rule set map or `false`
	 *                     if the payload does not contain the legacy Admin UI Recurrence information.
	 *
	 * @throws Requirement_Error If required data is missing from the payload.
	 */
	protected function convert_to_new_format( array $data, bool $include_dtstart = true ) {
		if ( ! $this->contains_old_format_ruleset( $data ) ) {
			return false;
		}

		$this->recurrence_array = $data['recurrence'];

		$recurrence_rules = $data['recurrence']['rules'] ?? [];
		$exclusion_rules = $data['recurrence']['exclusions'] ?? [];

		$recurrence_rules = $this->prune_rules( $recurrence_rules );
		$exclusion_rules = $this->prune_exclusions( $exclusion_rules );

		if ( empty( $recurrence_rules ) ) {
			// There is really nothing to convert.
			return false;
		}

		$dtstart = $this->build_start_date( $data );
		$default_duration = $this->get_default_duration( $data );

		/*
		 * We support at most 1 RRULE, 0-n RDATEs.
		 * Separate RRULEs from RDATEs: in the Legacy they are both called "rules",
		 * but we should handle them separately.
		 */
		$rrules = array_filter( $recurrence_rules, [ $this, 'is_rrule' ] );
		$rrule = reset( $rrules );
		$rdates = array_filter( $recurrence_rules, [ $this, 'is_rdate' ] );

		// $rrule may not be valid. If it is we need to retain any potential mutations done to it above.
		$rules_to_convert = $rrule ? array_merge( [ $rrule ], $rdates ) : $rdates;
		$rule_sets = $this->convert_recurrence_rules( $rules_to_convert, $default_duration, true );

		if ( $include_dtstart ) {
			$dtstart_string = $this->build_dtstart_string_from_data( $data );
			$rule_sets = $this->prepend_dtstart_to_rsets( $rule_sets, $dtstart_string );
		}

		$rule_sets = $this->adjust_for_first_occurrence( $rule_sets, $dtstart, $default_duration );

		if ( count( $exclusion_rules ) ) {
			// Convert the rules only if we really need them.
			$exclusions = $this->convert_exclusion_rules( $exclusion_rules );
			$rule_sets = $this->add_exclusions_to_rsets( $rule_sets, $exclusions, $dtstart );
		}

		return $rule_sets;
	}

	/**
	 * Updates the converted RSETs to include an RDATE entry if the Event first Occurrence would not be included in the
	 * generated Occurrences.
	 *
	 * Note: the Event first Occurrence is included if one of the generated Occurrences has matching start AND end date.
	 * Furthermore, the method will take care of handling the RRULE COUNT value, if required.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,string> $rsets            A map from durations, in seconds, to the RSET definition for each.
	 * @param DateTimeImmutable $dtstart          A reference to the DTSTART Date object.
	 * @param int               $default_duration The default duration, i.e., the duration of the original Event.
	 *
	 * @return array<int, string> The modified input RSETs, if required.
	 */
	private function adjust_for_first_occurrence( array $rsets, DateTimeImmutable $dtstart, $default_duration ) {
		try {
			/** @var int $dtstart_timestamp */
			$dtstart_timestamp = $dtstart->format( 'U' );
			$dtstart_end = $dtstart_timestamp + $default_duration;

			$dtstart_included = false;

			foreach ( $rsets as $duration => $rset_string ) {
				// Remove the DTSTART from the rule to start from equal Rules.
				$rule_wo_dtstart = $this->remove_dstart_from_rset( $rset_string );
				// What we get here might be an RRULE or an RDATE, use RSET to safely parse it.
				$rset = new RSet_Wrapper( $rule_wo_dtstart, $dtstart );
				// Get the first Occurrence the rule would generate.
				$first = $rset->offsetGet( 0 );
				// Sometimes possible to not have an Occurrence (rule negates itself?).
				if ( ! $first ) {
					continue;
				}
				$first_start = $first->format( 'U' );
				if ( $first_start === $dtstart_timestamp && $first_start + $duration === $dtstart_end ) {
					// The rule does include a perfect match for the DTSTART: we're done
					$dtstart_included = true;
					break;
				}
			}

			if ( ! $dtstart_included ) {
				$rsets = $this->append_dtstart_rdate_entry( $rsets, $dtstart, $default_duration );
			}
		} catch ( Exception $exception ) {
			do_action( 'tribe_log', 'error', 'Failed to adjust converted RSET to include first Occurrence.', [
				'source'           => __CLASS__,
				'slug'             => 'converter-fail-on-first-occurrence-include',
				'rsets'            => $rsets,
				'dtstart'          => $dtstart->format( Dates::DBDATETIMEFORMAT ),
				'dtstart_timezone' => $dtstart->getTimezone()->getName(),
			] );
		}

		return $rsets;
	}

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
	 * Returns the default duration specified in the payload.
	 *
	 * The "default" duration is the duration of the "master" event.
	 *
	 * @since 6.0.0
	 *
	 * @param array $data The data coming from the legacy Admin UI.
	 *
	 * @return int The default duration in seconds.
	 *
	 * @throws Requirement_Error If the duration meta is missing from the payload.
	 */
	public function get_default_duration( $data ) {
		if ( ! isset( $data['EventDuration'] ) ) {
			$id = isset( $data['ID'] ) ? $data['ID'] : '';
			throw Requirement_Error::due_to_missing_meta( '_EventDuration', $id );
		}

		return (int) $data['EventDuration'];
	}

	/**
	 * Builds and returns the `DTSTART` string that should be prepended to a
	 * rule set specification.
	 *
	 * @since 6.0.0
	 *
	 * @param array $data The data coming from the legacy Admin UI.
	 *
	 * @return string The DTSTART string in iCal RFC format.
	 *
	 * @throws Requirement_Error If the required start date and timezone information
	 *                                           is missing from the payload.
	 */
	protected function build_dtstart_string_from_data( array $data ) {
		$dtstart = $this->build_start_date( $data );

		return $this->build_dtstart_string_from_object( $dtstart );
	}

	/**
	 * Builds and returns the start date object as read from the payload.
	 *
	 * @since 6.0.0
	 *
	 * @param array $data The data coming from the legacy Admin UI.
	 *
	 * @return DateTimeImmutable|false The payload start date object.
	 *
	 * @throws Requirement_Error If the start time and timezone data is missing
	 *                                           from the payload.
	 */
	public function build_start_date( array $data ) {
		if ( ! isset( $data['EventTimezone'], $data['EventStartDate'] ) ) {
			$id = isset( $data['ID'] ) ? $data['ID'] : '';
			throw Requirement_Error::due_to_missing_meta( '_EventStartDate or _EventTimezone', $id );
		}

		$timezone = Timezones::build_timezone_object( $data['EventTimezone'] );

		return DateTimeImmutable::createFromFormat(
			'Y-m-d H:i:s',
			$data['EventStartDate'],
			$timezone
		);
	}

	/**
	 * Removes the DTSTART line from a multi-line RSET specification, if present.
	 *
	 * @since 6.0.0
	 *
	 * @param string $rule The newline-separated RSET definition.
	 *
	 * @return string The newline-separated RSET definition, the DTSTART line removed,
	 *                if originally present.
	 */
	private function remove_dstart_from_rset( $rule ) {
		if ( false === strpos( $rule, 'DTSTART' ) ) {
			return $rule;
		}

		$lines = explode( "\n", $rule );
		$clean = [];
		foreach ( $lines as $line ) {
			if ( 0 !== strpos( $line, 'DTSTART' ) ) {
				$clean[] = $line;
			}
		}

		return implode( "\n", $clean );
	}

	/**
	 * Appends an RDATE entry to the first fitting RSET entry to model the DTSTART.
	 *
	 * This method assumes the DTSTART is NOT already part of the RSET definitions.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,string> $rset             A map from durations, in seconds, to the RSET definition for each.
	 * @param DateTimeImmutable $dtstart          A reference to the date time object modeling the Event first
	 *                                            Occurrence, the DTSTART in iCalendar context.
	 * @param int               $default_duration The Event default duration, in seconds.
	 *
	 * @return array<int,string> An updated version of the RSET map, from durations to the RSET definitions for each
	 *                           duration, the RDATE added to the best fitting RSET.
	 */
	private function append_dtstart_rdate_entry( array $rset, DateTimeImmutable $dtstart, $default_duration ) {

		/*
		 * Since no RRULE would model the first Event Occurrence, let's add an RDATE to the first RSET entry (any
		 * will do) to model it.
		 * Note: the timezone for RRULEs or RDATEs cannot change, no need to embed it.
		 */
		$first_duration = count( $rset ) ? array_keys( $rset )[0] : $default_duration;
		$first_rset_entry = isset( $rset[ $first_duration ] ) ? $rset[ $first_duration ] : '';
		$timezone_string = $dtstart->getTimezone()->getName();
		$formatted_start = $dtstart->format( 'Ymd\THis' );
		// Get the end date, so we can compose a complete RDATE.
		$end = new DateTime( null, $dtstart->getTimezone() );
		$end->setTimestamp( $dtstart->getTimestamp() );
		$end->add( new DateInterval( 'PT' . $first_duration . 'S' ) );
		$formatted_end = $end->format( 'Ymd\THis' );

		$first_occurrence_rdate = sprintf( 'RDATE;TZID=%s;VALUE=PERIOD:%s/%s', $timezone_string, $formatted_start, $formatted_end );
		$rset[ $first_duration ] = $first_rset_entry . "\n" . $first_occurrence_rdate;

		return $rset;
	}

	/**
	 * Filters the input recurrence or exclusion rules to remove the invalid and empty ones.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,mixed> $rules The recurrence or exclusion rules entry, from the input Event data.
	 *
	 * @return array<int,mixed> The pruned set of Event Recurrence or Exclusion Rules.
	 */
	private function prune_rules( array $rules ) {
		return array_filter(
			$rules,
			static function ( $rule ) {
				return ! empty( $rule['type'] ) && is_array( $rule );
			}
		);
	}

	/**
	 * Filters the input exclusion rules to remove the invalid and empty ones.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,mixed> $exclusions The exclusion rules entry, form the input Event data.
	 *
	 * @return array<int,mixed> The pruned set of Event Recurrence Rules.
	 */
	private function prune_exclusions( array $exclusions ) {
		return array_filter(
			$exclusions,
			static function ( $exclusion ) {
				return ! empty( $exclusion['type'] );
			}
		);
	}

	/**
	 * Converts the Event Recurrence Rule from the old to the new format.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,mixed> $recurrence_rules The Event recurrence rules, in the old format.
	 * @param int              $default_duration The Rules default duration, i.e., the duration of the first Event in
	 *                                           the old format.
	 * @param bool             $implode          Whether to implode the converted rules into a string for each duration,
	 *                                           or to return an array of converted rules for each duration.
	 *
	 * @return array<int,string> A map from each converted RRULE or RDATE duration to the converted, multi-line RSET
	 *                           definition.
	 *
	 * @throws Requirement_Error If the conversion fails due to incoherent or incomplete old format rules definition.
	 */
	public function convert_recurrence_rules( array $recurrence_rules, $default_duration, $implode = true ) {
		if ( empty( $recurrence_rules ) ) {
			return [];
		}

		$converted = [ $default_duration => [] ];

		foreach ( $recurrence_rules as $rule ) {
			$rule_converter = new From_Event_Rule_Converter( $this, $rule );
			$converted[ $default_duration ][] = $rule_converter->convert_to_rrule();
		}

		if ( ! $implode ) {
			return $converted;
		}

		$rule_sets = [];

		foreach ( $converted as $duration => $entries ) {
			$rule_sets[ $duration ] = implode( "\n", $entries );
		}

		return $rule_sets;
	}

	/**
	 * Prepends a DTSTART entry to each RSET multi-line definition to make each RSET an iCalendar
	 * standard idem-potent definition.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,mixed> $rule_sets      A map from the duration, in seconds, that Occurrence generated from the
	 *                                         RSET should have, to the RSET multi-line specification.
	 * @param string           $dtstart_string The DTSTART string generated from the original Event data.
	 *
	 * @return array<int,mixed> The input duration to RSET map, modified to ensure each RSET will include the DTSTART
	 *                          definition string.
	 */
	private function prepend_dtstart_to_rsets( array $rule_sets, $dtstart_string ) {
		foreach ( $rule_sets as &$rule_set ) {
			if ( false !== strpos( $rule_set, 'DTSTART' ) ) {
				// If the RSET definition already includes a DTSTART definition, continue.
				continue;
			}
			$rule_set = $dtstart_string . "\n" . $rule_set;
		}

		return $rule_sets;
	}

	/**
	 * Adds the set of Exclusion Rules to each RSET definition.
	 *
	 * We have to add all Exclusion Rules to all RSETs as, in the old format, Exclusion would be "shared"
	 * between all Recurrence Rules. The iCalendar translation of this Exclusion sharing is to add the set
	 * of Exclusions to all RSETs.
	 * Note that any EXDATE that would not be applicable to the converted RRULE will be dropped.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,string> $rule_sets       A map from the duration that each Occurrence generated by the RSET
	 *                                           will have to the RSET definition for that duration.
	 * @param array<int,mixed>  $exclusions      The converted set of the original Event exclusion rules.
	 * @param DateTimeImmutable $dtstart         A reference to the Date object modeling the original,
	 *                                           recurring, Event first date.
	 *
	 * @return array<int,string> The updated map from durations to RSET definitions, the converted, required, EXRULEs
	 *                           and EXDATEs entries added to each.
	 *
	 * @throws Exception If there's any issue building the EXRULEs and EXDATEs entries.
	 */
	private function add_exclusions_to_rsets( array $rule_sets, array $exclusions, DateTimeImmutable $dtstart ): array {
		foreach ( $exclusions as $exclusion ) {
			$exclusion_lines = explode( "\n", $exclusion );
			[ $exrule_line, $exdate_lines ] = array_replace( [ null, [] ], array_reduce(
				$exclusion_lines,
				static function ( array $carry, string $line ): array {
					if ( strpos( $line, 'EXRULE:' ) === 0 ) {
						$carry[0] = $line;
					} else {
						$carry[1][] = $line;
					}

					return $carry;
				},
				[]
			) );

			$exdates_set = [];
			if ( count( $exdate_lines ) ) {
				foreach ( $exdate_lines as $exdate_line ) {
					// Added EXDATEs with RDATE counterparts, so we can parse and extract the EXDATEs.
					// Without the counterparts they might be removed for not having any valid dates to exclude.
					$exdate_rset = $this->get_rset_for_ical_string_dtstart(
						$exdate_line . "\n" . str_replace( 'EXDATE', 'RDATE', $exdate_line ),
						$dtstart
					);
					// There will be a finite number of EXDATEs.
					$exdates_set[] = $exdate_rset->getExDates();
				}
				$exdates_set = array_merge( ...$exdates_set );
			}

			foreach ( $rule_sets as &$rule_set ) {
				if ( $exrule_line ) {
					// If there is an EXRULE entry, then just add it to the RSET.
					$rule_set .= "\n" . $exrule_line;
				}

				$check_rset = $this->get_rset_for_ical_string_dtstart( $rule_set, $dtstart );

				$rset_dtstart = $check_rset->get_dtstart();
				$rset_hour = $rset_dtstart->format( 'H' );
				$rset_min = $rset_dtstart->format( 'i' );
				$rset_sec = $rset_dtstart->format( 's' );

				/** @var DateTime $exdate */
				foreach ( $exdates_set as $exdate ) {
					// EXDATEs will apply to the whole day, sync them to the RSET start time.
					$exdate = $exdate->setTime( $rset_hour, $rset_min, $rset_sec );
					$check_rset->addExDate( $exdate, true );
				}

				// Set by reference into the $rule_sets array.
				$rule_set = $check_rset->get_rfc_string( true );
			}
		}

		return $rule_sets;
	}

	/**
	 * Converts the Event Exclusion Rules from the old to the iCalendar compatible format.
	 *
	 * Multiple exclusion rules will be reduced to, at most, 1 EXRULE and 0 or more EXDATEs.
	 * Multiple EXRULEs, as multiple RRULEs, are not supported by the iCalendar standard.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,mixed> $exclusion_rules The Exclusion Rules definitions, in the old format.
	 *
	 * @return array<string> A list of the converted Exclusion rules.
	 *
	 * @throws Requirement_Error If anyone of the Exclusion rules definitions is incoherent or invalid.
	 */
	public function convert_exclusion_rules( array $exclusion_rules ) {
		if ( empty( $exclusion_rules ) ) {
			return [];
		}

		$converted = [];

		// Yes: convert to RRULEs, we'll need that later.
		foreach ( $exclusion_rules as $exclusion_rule ) {
			$converted[] = ( new From_Event_Rule_Converter( $this, $exclusion_rule ) )
				->convert_to_rrule( true );
		}

		// We'll need them over and over.
		$rule_search = [ 'RRULE', 'RDATE' ];
		$exrule_replace = [ 'EXRULE', 'EXDATE' ];

		if ( 1 === count( $exclusion_rules ) ) {
			return str_replace( $rule_search, $exrule_replace, $converted );
		}

		// Create tuples of each rule definition and guessed count.
		$tuples = array_map( function ( array $rule, $converted ) {
			return [
				'rule'        => $rule,
				'count'       => $this->guess_rule_count( $rule ),
				'ical_string' => $converted,
			];
		}, $exclusion_rules, $converted );

		// Sort the tuples in ascending order and pick the winner.
		usort( $tuples, static function ( array $a, array $b ) {
			return $b['count'] - $a['count'];
		} );

		$head = array_shift( $tuples );

		/*
		 * We cannot work with infinite RSETs, limit them for the purpose of the conversion.
		 */
		$head_infinite = $this->ical_string_is_never_ending( $head['ical_string'] );
		$never_limit = $this->get_never_limit_date();

		if ( $head_infinite ) {
			$head['ical_string'] = $this->limit_never_ending_string( $head['ical_string'], $never_limit );
		}

		array_walk( $tuples, function ( array &$tuple ) use ( $never_limit ): void {
			$tuple['ical_string'] = $this->limit_never_ending_string( $tuple['ical_string'], $never_limit );
		} );


		$head_rset = new RSet_Wrapper( $head['ical_string'], $this->start_date );

		/*
		 * To avoid keeping in memory a gazzilion date objects, reduce them
		 * immediately. We treat the tail exclusion rules as recurrence rules:
		 * if a date generated by a tail rule does not already happen in the
		 * head rule, add it as an RDATE to the head rule.
		 */
		foreach ( $tuples as $tuple ) {
			$ical_string = $tuple['ical_string'];
			$rset = new RSet_Wrapper( $ical_string, $this->start_date );
			$rset->rewind();
			$current = $rset->current();

			while ( null !== $current ) {
				// Get the first Occurrence happening on or after the date.
				$head_occurrences = $head_rset->getOccurrencesBetween( $current, null, 1 );
				$head_occurrence = reset( $head_occurrences );
				// If we have a match, it should be the same date the tail rule produced.
				if ( empty( $head_occurrence ) || $head_occurrence != $current ) {
					$head_rset->addExDate( $current );
				}

				$rset->next();
				$current = $rset->current();
			}
		}

		/*
		 * The head RSET will now contain the minimum number of RDATEs required to model the converted
		 * exclusion rules. Get the RFC iCalendar string and run replacements on it.
		 * We also never want these type of EXRULE -> EXDATE conversions to store the time.
		 */
		$head_rfc_string = $head_rset->get_rfc_string( false, false );

		if ( $head_infinite ) {
			// Remove the UNTIL limit set for the purpose of the computation from the head rule.
			$head_rfc_string = $this->set_ical_string_until_limit( $head_rfc_string, null );
		}

		$reduced_ical_string = str_replace( $rule_search, $exrule_replace, $head_rfc_string );

		return [ $reduced_ical_string ];
	}

	/**
	 * Builds the `DTSTART` string from an object.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeImmutable $dtstart A reference to the DTSTART object to build the string from.
	 *
	 * @return string The iCalendar standard `DTSTART` string.
	 */
	protected function build_dtstart_string_from_object( $dtstart ) {
		if ( ! $dtstart instanceof DateTimeImmutable ) {
			return '';
		}

		if ( in_array( $dtstart->getTimezone()->getName(), [ 'UTC', 'Z' ], true ) ) {
			return sprintf( 'DTSTART:%sZ', $dtstart->format( 'Ymd\THis' ) );
		}

		return sprintf(
			'DTSTART;TZID=%s:%s',
			$dtstart->getTimezone()->getName(),
			$dtstart->format( 'Ymd\THis' )
		);
	}
}
<?php
/**
 * A value object representing a Date recurrence rule.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Rules\Event_Recurrence;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Rules;

use DateTimeImmutable;
use InvalidArgumentException;
use TEC\Events_Pro\Custom_Tables\V1\RRule\Occurrence;
use Tribe__Date_Utils as Dates;

/**
 * Class Date_Rule.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Rules;
 */
class Date_Rule {

	/**
	 * A reference to the Date occurrence start.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $start;

	/**
	 * A reference to the Date occurrence end.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $end;
	/**
	 * A reference to the Event start date and time.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $dtstart;
	/**
	 * A reference to the Event end date and time.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $dtend;

	/**
	 * Date_Rule constructor.
	 *
	 * since 6.0.0
	 *
	 * @param DateTimeImmutable $dtstart A reference to the Event start date and time.
	 * @param DateTimeImmutable $dtend   A reference to the Event end date and time.
	 * @param DateTimeImmutable $start   A reference to the Date occurrence start.
	 * @param DateTimeImmutable $end     A reference to the Date occurrence end.
	 */
	public function __construct( DateTimeImmutable $dtstart, DateTimeImmutable $dtend, DateTimeImmutable $start, DateTimeImmutable $end ) {
		$this->dtstart = $dtstart;
		$this->dtend = $dtend;
		$this->start = $start;
		$this->end = $end;
	}

	/**
	 * Builds an instance of the object from a rule in the format used in the `_EventRecurrence` meta field.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed>    $rule    A rule in the format used in the `_EventRecurrence` meta field.
	 * @param DateTimeImmutable|null $dtstart A reference to the Event start date and time; this will be used if the
	 *                                        rule is missing the `EventStartDate` key.
	 * @param DateTimeImmutable|null $dtend   A reference to the Event start date and time; this will be used if the
	 *                                        rule is missing the `EventEndDate` key.
	 *
	 * @return Date_Rule The built object.
	 *
	 * @throws InvalidArgumentException If the rule is not in the expected format.
	 */
	public static function from_event_recurrence_format( array $rule, DateTimeImmutable $dtstart = null, DateTimeImmutable $dtend = null ) {
		$missing = array_keys( array_filter( [
			'EventStartDate'   => ! isset( $rule['EventStartDate'] ) && $dtstart === null,
			'EventEndDate'     => ! isset( $rule['EventEndDate'] ) && $dtend === null,
			'custom.date.date' => ! isset( $rule['custom']['date']['date'] )
		] ) );

		if ( count( $missing ) ) {
			throw new InvalidArgumentException(
				sprintf( 'The rule is missing the %s field(s).', implode( ', ', $missing ) )
			);
		}

		$dtstart = isset( $rule['EventStartDate'])  ? Dates::immutable( $rule['EventStartDate'] ) : $dtstart;
		$dtend   = isset( $rule['EventEndDate'] ) ? Dates::immutable( $rule['EventEndDate'] ) : $dtend;
		$start_time = $rule['custom']['start-time'] ?? $dtstart->format( Dates::DBTIMEFORMAT );
		$end_time = $rule['custom']['end-time'] ?? $dtend->format( Dates::DBTIMEFORMAT );
		$start = Dates::immutable( $rule['custom']['date']['date'] . ' ' . $start_time );
		$end = Dates::immutable( $rule['custom']['date']['date'] . ' ' . $end_time );
		if ( ! empty( $rule['custom']['end-day'] ) && is_numeric( $rule['custom']['end-day'] ) ) {
			$end = $end->modify( "+{$rule['custom']['end-day']} days" );
		}

		return new self( $dtstart, $dtend, $start, $end );
	}

	/**
	 * Builds an instance of the class from a RRULE Occurrence object reference.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeImmutable $dtstart          A reference to the Event start date and time.
	 * @param DateTimeImmutable $dtend            A reference to the Event end date and time.
	 * @param Occurrence        $rdate_occurrence A reference to the RRULE occurrence to build the RDATE object
	 *                                            from.
	 *
	 * @return static A reference to the built object.
	 */
	public static function from_rrule_occurrence( DateTimeImmutable $dtstart, DateTimeImmutable $dtend, Occurrence $rdate_occurrence ): self {
		$start = $rdate_occurrence->start();
		$end = $rdate_occurrence->end();

		return new self( $dtstart, $dtend, $start, $end );
	}

	/**
	 * Returns the Date rule in the format used in the `_EventRecurrence` meta field.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string,mixed> The Date rule in the format used in the `_EventRecurrence` meta field.
	 */
	public function to_event_recurrence_format(): array {
		$end_day = (int) floor( ( $this->end->getTimestamp() - $this->start->getTimestamp() ) / DAY_IN_SECONDS );

		return [
			'type'           => 'Custom',
			'custom'         =>
				[
					'type'       => 'Date',
					'interval'   => '1', // Back-compat.
					'date'       =>
						[
							'date' => $this->start->format( Dates::DBDATEFORMAT ),
						],
					'same-time'  => 'no',
					'start-time' => $this->start->format( Dates::TIMEFORMAT ),
					'end-time'   => $this->end->format( Dates::TIMEFORMAT ),
					'end-day'    => $end_day ?? 'same-day',
				],
			'EventStartDate' => $this->dtstart->format( Dates::DBDATETIMEFORMAT ),
			'EventEndDate'   => $this->dtend->format( Dates::DBDATETIMEFORMAT ),
		];
	}

	/**
	 * Compares this date to another one and returns whether their start and end date
	 * match or not.
	 *
	 * The comparison happens on the formatted date as the RDATE will not carry the
	 * timezone information.
	 *
	 * @since 6.0.0
	 *
	 * @param Date_Rule $compare A reference to the date to compare to.
	 *
	 * @return bool Whether this date matches the other one or not.
	 */
	public function equals( Date_Rule $compare ): bool {
		return $this->start->format( Dates::DBDATETIMEFORMAT ) === $compare->start()->format( Dates::DBDATETIMEFORMAT )
		       && $this->end->format( Dates::DBDATETIMEFORMAT ) === $compare->end()->format( Dates::DBDATETIMEFORMAT );
	}

	/**
	 * Returns a reference to the Event start date and time.
	 *
	 * @since 6.0.0
	 *
	 * @return DateTimeImmutable A reference to the Event start date and time.
	 */
	public function dtstart(): DateTimeImmutable {
		return $this->dtstart;
	}

	/**
	 * Returns a reference to the Event end date and time.
	 *
	 * @since 6.0.0
	 *
	 * @return DateTimeImmutable A reference to the Event end date and time.
	 */
	public function dtend(): DateTimeImmutable {
		return $this->dtend;
	}

	/**
	 * Returns a reference to the RDATE start date and time.
	 *
	 * @since 6.0.0
	 *
	 * @return DateTimeImmutable A reference to the RDATE start date and time.
	 */
	public function start(): DateTimeImmutable {
		return $this->start;
	}

	/**
	 * Returns a reference to the RDATE end date and time.
	 *
	 * @since 6.0.0
	 *
	 * @return DateTimeImmutable A reference to the RDATE end date and time.
	 */
	public function end(): DateTimeImmutable {
		return $this->end;
	}
}
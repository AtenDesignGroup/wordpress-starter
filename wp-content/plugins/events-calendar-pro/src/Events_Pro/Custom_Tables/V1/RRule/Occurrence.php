<?php
/**
 * Models an occurrence as something with a start and a duration.
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\RRule
 * @since   6.0.0
 */

namespace TEC\Events_Pro\Custom_Tables\V1\RRule;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Tribe__Date_Utils as Dates;

/**
 * Class Occurrence
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\RRule
 *
 * @since   6.0.0
 */
class Occurrence extends DateTime {
	/**
	 * The occurrence start.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $start;

	/**
	 * The occurrence end.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $end;

	/**
	 * The occurrence duration, saved as an interval.
	 *
	 * @var DateInterval
	 */
	private $duration;

	/**
	 * The occurrence duration in seconds; this is the total duration in seconds, not the DateInterval
	 * object `s` property.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	private $duration_in_seconds;

	/**
	 * Default UTC object.
	 *
	 * @var DateTimeZone
	 */
	private $utc;

	/**
	 * Occurrence constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTimeInterface $start    The occurrence start DateTime object.
	 * @param int|DateInterval  $duration The occurrence duration in seconds or as a DateInterval object.
	 *
	 * @throws Exception If the DateInterval object cannot be built with the specified duration.
	 */
	public function __construct( DateTimeInterface $start, $duration = 0 ) {
		parent::__construct( $start->format( Dates::DBDATETIMEFORMAT ), $start->getTimezone() );

		$this->utc = new DateTimeZone( 'UTC' );

		/*
		 * The RRule library might build the dates using the Z timezone.
		 * The Z timezone is a type 2 timezone that will not respect DST.
		 * To cope with this we convert the Z timezone to the UTC one.
		 */
		$this->start = 'Z' === $start->getTimezone()->getName()
			? Dates::immutable( $start->format( 'Y-m-d H:i:s' ), $this->utc )
			: Dates::immutable( $start );

		$this->duration = $duration instanceof DateInterval ? $duration : new DateInterval( "PT{$duration}S" );
		// Let's make sure the duration we are applying is a positive one: the end comes after the start.
		$this->duration->invert = 0;
		$this->end = $this->start->add( $this->duration );
		$this->duration_in_seconds = $this->end->getTimestamp() - $this->start->getTimestamp();
	}

	/**
	 * Returns the occurrence duration in seconds.
	 *
	 * @since 6.0.0
	 *
	 * @return int The Occurrence duration in seconds.
	 */
	public function get_duration(): int {
		return $this->duration_in_seconds;
	}

	/**
	 * Relays calls of not implemented methods to the start DateTime object.
	 *
	 * This is done to maintain compatibility with the `rlanvin/rrule` package
	 * that handles and models occurrences and points in time, without duration.
	 *
	 * @since 6.0.0
	 *
	 * @param string $name      The called method name.
	 * @param array  $arguments An array of method call arguments.
	 *
	 * @return mixed The value resulting from the call to the start DateTime object.
	 */
	public function __call( $name, $arguments ) {
		return call_user_func_array( [ $this->start, $name ], $arguments );
	}

	/**
	 * Returns a clone of the occurrence start DateTime object.
	 *
	 * A clone is returned in place of the actual start DateTime object
	 * to insulate the occurrence from external changes.
	 *
	 * @since 6.0.0
	 *
	 * @return DateTimeImmutable A reference to the immutable Date object
	 *                           representing the Occurrence start date and time.
	 */
	public function start(): DateTimeImmutable {
		return $this->start;
	}

	/**
	 * Returns a clone of the occurrence end DateTime object.
	 *
	 * A clone is returned in place of the actual end DateTime object
	 * to insulate the occurrence from external changes.
	 *
	 * @since 6.0.0
	 *
	 * @return DateTimeImmutable A reference to the immutable Date object
	 *                           representing the Occurrence end date and time.
	 */
	public function end():DateTimeImmutable {
		return $this->end;
	}

	/**
	 * Formats and returns the occurrence start date.
	 *
	 * @since 6.0.0
	 *
	 * @param string $format The format to use.
	 *
	 * @return string The formatted start date.
	 */
	public function format_start( $format ) {
		return $this->start->format( $format );
	}

	/**
	 * Formats and returns the occurrence start date.
	 *
	 * @param string $format The format to use.
	 *
	 * @return string The formatted start date.
	 * @since 6.0.0
	 */
	public function format_start_utc( $format ) {
		return Dates::build_date_object( $this->start )
			->setTimezone( $this->utc )
			->format( $format );
	}

	/**
	 * Formats and returns the occurrence end date.
	 *
	 * @since 6.0.0
	 *
	 * @param string $format The format to use.
	 *
	 * @return string The formatted end date.
	 */
	public function format_end( $format ) {
		return $this->end->format( $format );
	}

	/**
	 * Formats and returns the occurrence end date.
	 *
	 * @since 6.0.0
	 *
	 * @param string $format The format to use.
	 *
	 * @return string The formatted end date.
	 */
	public function format_end_utc( $format ) {
		return Dates::build_date_object( $this->end )
			->setTimezone( $this->utc )
			->format( $format );
	}

	/**
	 * Builds an Occurrence from a start date and a duration, if not already an
	 * Occurrence instance.
	 *
	 * @since 6.0.0
	 *
	 * @param DateTime $start    A reference to the object representing the Occurrence
	 *                           start date and time, or an Occurrence instance that will
	 *                           be returned not modified.
	 * @param int      $duration The occurrence duration in seconds, if the `$start`
	 *                           parameter is a date.
	 *
	 * @return static A reference to an Occurrence instance, the input one if it's already
	 *                an Occurrence.
	 *
	 * @throws Exception If there's any issue building the dates object required to
	 *                   build the Occurrence.
	 *
	 */
	public static function create_from_start_duration( DateTime $start, int $duration ): self {
		if ( $start instanceof self ) {
			return $start;
		}

		return new self( $start, $duration );
	}

	/**
	 * Returns a tuple representing the Occurrence start and end date and times in
	 * the specified format.
	 *
	 * @since 6.0.0
	 *
	 * @param string $format The format to use to format the Occurrence date and end
	 *                       dates and times.
	 *
	 * @return array<string> A tuple containing the formatted Occurrence start and end
	 *                       date and time in the specified format.
	 */
	public function to_tuple( string $format = Dates::DBDATETIMEFORMAT ): array {
		return [ $this->format_start( $format ), $this->format_end( $format ) ];
	}
}

<?php
/**
 * Provides methods to operate on dates.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Traits;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Traits;

use DateTimeImmutable;
use DateTimeInterface;
use TEC\Events\Custom_Tables\V1\Events\Occurrences\Max_Recurrence;
use Tribe__Date_Utils as Dates;

/**
 * Trait With_Date_Operations.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Traits;
 */
trait With_Date_Operations {
	/**
	 * A cache property that will store the "never" limit date built
	 * from today to the max months after limit.
	 *
	 * @since 6.0.0
	 *
	 * @var null|\DateTimeImmutable
	 */
	private $never_limit_date;

	/**
	 * Filters an array of Date-like objects to remove duplicates preserving
	 * keys.
	 *
	 * @since 6.0.0
	 *
	 * @param array<DateTimeInterface> $dates The set of dates to filter.
	 *
	 * @return array<DateTimeInterface> The filtered set of dates, duplicates
	 *                                  removed, the array keys preserved.
	 */
	private function unique_dates( array $dates ) {
		$unique_strings = array_map( static function ( DateTimeInterface $date ) {
			return $date->format( 'Y-m-d H:i:s' );
		}, $dates );

		return array_intersect_key( $dates, array_unique( $unique_strings ) );
	}

	/**
	 * Returns the date object representing the "never" limit.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $reset Whether to invalidate and re-calculate the
	 *                    cached value or not.
	 *
	 * @return DateTimeImmutable A reference to the immutable date
	 *                            object representing the "never" limit.
	 */
	private function get_never_limit_date( bool $reset = false ): DateTimeImmutable {
		if ( null === $this->never_limit_date || $reset ) {
			$max_months_after = (int) tribe_get_option( 'recurrenceMaxMonthsAfter', Max_Recurrence::get_recurrence_max_months_default() );
			$this->never_limit_date = Dates::immutable( "today + $max_months_after months" );
		}

		return $this->never_limit_date;
	}
}
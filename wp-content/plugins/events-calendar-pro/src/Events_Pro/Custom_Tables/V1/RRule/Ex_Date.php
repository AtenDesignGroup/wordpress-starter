<?php
/**
 * Overrides DateTime to add missing context for EXDATE parsing.
 *
 * @since   6.0.3
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\RRule
 */

namespace TEC\Events_Pro\Custom_Tables\V1\RRule;

use DateTime;

/**
 * Class Event
 *
 * @since   6.0.3
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\RRule
 */
class Ex_Date extends DateTime {

	/**
	 * @var bool Flag whether this EXDATE applies to a full day or specific time.
	 */
	protected $should_exclude_all_day = false;

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		$args = func_get_args();
		$this->parse_date( $args[0] );
		parent::__construct( ...$args );
	}

	/**
	 * Parse the input date string for EXDATE context.
	 *
	 * @since 6.0.3
	 *
	 * @param mixed $string The input date string.
	 */
	protected function parse_date( $string ) {
		$date_components = is_string( $string ) ? date_parse( $string ) : null;
		$has_hour        = isset( $date_components['hour'] ) && $date_components['hour'] !== false;
		// If hour was not specified, we should exclude all day.
		$this->set_should_exclude_all_day( ! $has_hour );
	}

	/**
	 * Parse this EXDATE to the RFC compliant string.
	 *
	 * @since 6.0.3
	 *
	 * @return string Returns an RFC string.
	 */
	public function to_rfc_string(): string {
		return $this->should_exclude_all_day()
			? $this->format( 'Ymd' )
			: $this->format( "Ymd\THis" );
	}

	/**
	 * Flag this EXDATE as full day exclusion or not.
	 *
	 * @since 6.0.3
	 *
	 * @param bool $should_exclude_all_day Flag whether this is to be treated as an all day exclusion or not.
	 */
	public function set_should_exclude_all_day( bool $should_exclude_all_day = true ) {
		$this->should_exclude_all_day = $should_exclude_all_day;
	}

	/**
	 * Check the flag for exclusion of an entire day or specific time.
	 *
	 * @since 6.0.3
	 *
	 * @return bool Whether this EXDATE will exclude the entire day or specific time.
	 */
	public function should_exclude_all_day(): bool {
		return $this->should_exclude_all_day;
	}

	/**
	 * Depending on if this is an all day exclusion or not, will give the time for when the exclusion begins.
	 *
	 * @since 6.0.3
	 *
	 * @return string A date string in the format `Y-m-d H:i:s e`.
	 */
	public function exclusion_begins(): string {
		return $this->should_exclude_all_day()
			? $this->format( 'Y-m-d 00:00:00 e' )
			: $this->format( 'Y-m-d H:i:s e' );
	}

	/**
	 * Depending on if this is an all day exclusion or not, will give the time for when the exclusion ends.
	 *
	 * @since 6.0.3
	 *
	 * @return string A date string in the format `Y-m-d H:i:s e`.
	 */
	public function exclusion_ends(): string {
		return $this->should_exclude_all_day()
			? $this->format( 'Y-m-d 23:59:59 e' )
			: $this->format( 'Y-m-d H:i:s e' );
	}
}

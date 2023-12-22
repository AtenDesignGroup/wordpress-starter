<?php
/**
 * Formats a RRULE entry for database insertion.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
namespace TEC\Events_Pro\Custom_Tables\V1\Models\Formatters;

use RRule\RRule;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Formatter;

/**
 * Class RRule_Formatter
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
class RRule_Formatter implements Formatter {
	public function format( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		$rule = new RRule( $value );

		return trim( $rule->rfcString() );
	}

	public function accessor( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		return new RRule( $value );
	}

	public function prepare() {
		return '%s';
	}
}

<?php
/**
 * Validates a date value.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Models\Formatters;

use TEC\Events\Custom_Tables\V1\Models\Formatters\Formatter;
use TEC\Events_Pro\Custom_Tables\V1\RRule\RSet_Wrapper;

/**
 * Class RSet_Formatter
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
class RSet_Formatter implements Formatter {
	/**
	 * {@inheritdoc }
	 */
	public function format( $value ) {
		// @todo Correctly format empty RSET.
		if ( empty( $value ) ) {
			return '';
		}

		if ( $value instanceof RSet_Wrapper ) {
			return (string) $value;
		}

		return is_string( $value ) ? $value : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare() {
		return '%s';
	}
}

<?php
/**
 * Validates an End Date UTC input.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Models\Validators;

use TEC\Events\Custom_Tables\V1\Models\Model;
use TEC\Events\Custom_Tables\V1\Models\Validators\Validator;

use function tribe_is_event_series;

/**
 * Class Valid_Series
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Valid_Series extends Validator {
	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {
		$this->error_message = '';

		if ( empty( $value ) ) {
			return true;
		}

		$is_event_series = tribe_is_event_series( $value );

		if ( ! $is_event_series ) {
			$this->error_message = 'The provided value is not a valid Series type.';
		}

		return $is_event_series;
	}
}

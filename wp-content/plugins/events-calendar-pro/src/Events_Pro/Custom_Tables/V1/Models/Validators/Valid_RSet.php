<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Models\Validators;

use RRule\RSet;
use TEC\Events\Custom_Tables\V1\Models\Model;
use TEC\Events\Custom_Tables\V1\Models\Validators\Validator;
use TEC\Events_Pro\Custom_Tables\V1\RRule\RSet_Wrapper;

/**
 * Class Valid_RSet
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Models\Validators
 */
class Valid_RSet extends Validator {

	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {
		try {
			$this->error_message = '';

			if ( empty( $value ) ) {
				return true;
			}

			if ( $value instanceof RSet_Wrapper ) {
				return true;
			}

			// RSet does not have a to string method so we need a way to represent the object as string.
			if ( $value instanceof RSet ) {
				return false;
			}

			new RSet_Wrapper( $value, null );

			return true;
		} catch ( \InvalidArgumentException $exception ) {
			$this->error_message = $exception->getMessage();

			return false;
		}
	}
}

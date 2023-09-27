<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Models\Validators;

use RRule\RRule;
use TEC\Events\Custom_Tables\V1\Models\Model;
use TEC\Events\Custom_Tables\V1\Models\Validators\Validator;

/**
 * Class Valid_RRule
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Valid_RRule extends Validator {

	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {
		try {
			$this->error_message = '';
			if ( empty( $value ) ) {
				return true;
			}

			new RRule( $value );

			return true;
		} catch ( \InvalidArgumentException $exception ) {
			$this->error_message = $exception->getMessage();

			return false;
		}
	}
}

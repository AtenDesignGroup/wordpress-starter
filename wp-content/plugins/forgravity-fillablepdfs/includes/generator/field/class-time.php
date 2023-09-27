<?php

namespace ForGravity\Fillable_PDFs\Generator\Field;

/**
 * Fillable PDFs PDF Generator Time field value class.
 *
 * @since     3.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2021, ForGravity
 */
class Time extends Base {

	/**
	 * Gravity Forms Field object.
	 *
	 * @since 3.0
	 *
	 * @var \GF_Field_Time
	 */
	protected $field;

	/**
	 * Returns the full Time or Time input value.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_value() {

		if ( $this->field_id == (int) $this->field_id ) {
			return parent::get_value();
		}

		$field_value = rgar( $this->generator->entry, (int) $this->field_id );

		// Split the time value into the individual inputs.
		preg_match( '/^(\d*):(\d*) ?(.*)$/', $field_value, $input_values );

		return rgar( $input_values, $this->get_input_id() );

	}

}

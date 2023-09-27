<?php

namespace ForGravity\Fillable_PDFs\Generator\Field;

/**
 * Fillable PDFs PDF Generator Name field value class.
 *
 * @since     3.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2021, ForGravity
 */
class Name extends Base {

	/**
	 * Gravity Forms Field object.
	 *
	 * @since 3.0
	 *
	 * @var \GF_Field_Name
	 */
	protected $field;

	/**
	 * Returns the full Name or Name input value.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_value() {

		if ( $this->field_id !== (int) $this->field_id ) {
			return parent::get_value();
		}

		return $this->field->get_value_export( $this->generator->entry );

	}

}

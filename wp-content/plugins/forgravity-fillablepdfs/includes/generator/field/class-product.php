<?php

namespace ForGravity\Fillable_PDFs\Generator\Field;

/**
 * Fillable PDFs PDF Generator Option field value class.
 *
 * @since     4.2
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2022, ForGravity
 */
class Product extends Base {

	/**
	 * Gravity Forms Field object.
	 *
	 * @since 4.2
	 *
	 * @var \GF_Field_Product
	 */
	protected $field;

	/**
	 * Returns the Option value.
	 *
	 * @since 4.2
	 *
	 * @return string|null
	 */
	public function get_value() {

		$field_value    = rgar( $this->generator->entry, $this->field_id );
		$selected_value = preg_split( '~\|(?=[^\|]*$)~', $field_value );

		return rgar( $selected_value, 0 );

	}

}

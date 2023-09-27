<?php

namespace ForGravity\Fillable_PDFs\Generator\Field;

/**
 * Fillable PDFs PDF Generator Address field value class.
 *
 * @since     3.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2021, ForGravity
 */
class Address extends Base {

	/**
	 * Gravity Forms Field object.
	 *
	 * @since 3.0
	 *
	 * @var \GF_Field_Address
	 */
	protected $field;

	/**
	 * Returns the full Address or Address input value.
	 *
	 * @since 3.0
	 * @since 4.4 Added workaround for Geolocation Add-On storing coordinates in entry meta.
	 *
	 * @return string
	 */
	public function get_value() {

		if ( $this->field_id !== (int) $this->field_id ) {

			// Get Geolocation data from entry meta since it is not available in the entry.
			if ( strpos( $this->field_id, 'geolocation' ) !== false ) {
				return gform_get_meta( $this->generator->entry['id'], $this->field_id );
			}

			return parent::get_value();

		}

		return $this->field->get_value_export( $this->generator->entry );

	}

}

<?php

namespace ForGravity\Fillable_PDFs\Generator\Field;

use GFCommon;

/**
 * Fillable PDFs PDF Generator Date field value class.
 *
 * @since     3.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2021, ForGravity
 */
class Date extends Base {

	/**
	 * Gravity Forms Field object.
	 *
	 * @since 3.0
	 *
	 * @var \GF_Field_Date
	 */
	protected $field;

	/**
	 * Returns the full Date or Date input value.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_value() {

		// Use the full Date.
		if ( $this->field_id == (int) $this->field_id ) {
			$value = rgar( $this->generator->entry, $this->field_id );
			return GFCommon::date_display( $value, $this->field->dateFormat, $this->field->get_output_date_format() );
		}

		$date     = GFCommon::parse_date( rgar( $this->generator->entry, $this->field->id ), $this->field->dateFormat );
		$input_id = $this->get_input_id();

		switch ( $this->field->dateFormat ) {

			case 'dmy':
			case 'dmy_dash':
			case 'dmy_dot':

				switch ( $input_id ) {
					case 1:
						return rgar( $date, 'day' );
					case 2:
						return rgar( $date, 'month' );
					case 3:
						return rgar( $date, 'year' );
				}

				break;

			case 'mdy':
			default:

				switch ( $input_id ) {
					case 1:
						return rgar( $date, 'month' );
					case 2:
						return rgar( $date, 'day' );
					case 3:
						return rgar( $date, 'year' );
				}

				break;

			case 'ymd':
			case 'ymd_dash':
			case 'ymd_dot':
			case 'ymd_slash':

				switch ( $input_id ) {
					case 1:
						return rgar( $date, 'year' );
					case 2:
						return rgar( $date, 'month' );
					case 3:
						return rgar( $date, 'day' );
				}

				break;

		}

		return parent::get_value();

	}

}

<?php

namespace ForGravity\Fillable_PDFs\Generator\Field;

use GFCommon;
use RGCurrency;

/**
 * Fillable PDFs PDF Generator Number field value class.
 *
 * @since     3.2
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2021, ForGravity
 */
class Number extends Base {

	const MODIFIER_FORMAT_NUMBER = 'format_number';

	const MODIFIER_REMOVE_CURRENCY = 'remove_currency';

	/**
	 * Gravity Forms Field object.
	 *
	 * @since 3.2
	 *
	 * @var \GF_Field_Number|\GF_Field_Product
	 */
	protected $field;

	/**
	 * Returns either the raw or formatted number.
	 *
	 * @since 3.2
	 *
	 * @return string|null
	 */
	public function get_value() {

		$value = parent::get_value();

		// If no modifiers apply, return raw value.
		if ( ! in_array( self::MODIFIER_FORMAT_NUMBER, $this->modifiers ) ) {
			return $value;
		}

		// Remove the format number modifier.
		$this->remove_modifier( self::MODIFIER_FORMAT_NUMBER );

		$formatted_number = $this->get_formatted_number();

		// If currency does not need to be removed, return formatted value.
		if ( ! in_array( self::MODIFIER_REMOVE_CURRENCY, $this->modifiers ) ) {
			return $formatted_number;
		}

		// Remove the remove currency modifier.
		$this->remove_modifier( self::MODIFIER_REMOVE_CURRENCY );

		return $this->remove_currency( $formatted_number );

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Returns the formatted number.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	private function get_formatted_number() {

		$include_thousands_sep = apply_filters( 'gform_include_thousands_sep_pre_format_number', true, $this->field );
		$number_format         = in_array( $this->field->type, [ 'product', 'total' ] ) ? 'currency' : $this->field->numberFormat;

		return GFCommon::format_number( rgar( $this->generator->entry, $this->field_id ), $number_format, rgar( $this->generator->entry, 'currency' ), $include_thousands_sep );

	}

	/**
	 * Returns the formatted number without currency symbols.
	 *
	 * @since 3.2
	 *
	 * @param string $number   Formatted number.
	 * @param string $currency Currency.
	 *
	 * @return string
	 */
	private function remove_currency( $number, $currency = '' ) {

		if ( ! $currency ) {
			$currency = rgar( $this->generator->entry, 'currency', GFCommon::get_currency() );
		}

		$currency_details = RGCurrency::get_currency( $currency );

		return str_replace(
			[
				rgar( $currency_details, 'symbol_left' ),
				rgar( $currency_details, 'symbol_right' ),
				rgar( $currency_details, 'symbol_padding' ),
			],
			'',
			$number
		);

	}

}

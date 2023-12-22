<?php

namespace ForGravity\Fillable_PDFs\Generator\Field;

use ForGravity\Fillable_PDFs\Generator;

/**
 * Fillable PDFs PDF Generator List field value class.
 *
 * @since     3.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2021, ForGravity
 */
class List_Field extends Base {

	/**
	 * Gravity Forms Field object.
	 *
	 * @since 3.0
	 *
	 * @var \GF_Field_List
	 */
	protected $field;

	/**
	 * Target List column.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	private $column;

	/**
	 * Target List row.
	 *
	 * @since 3.0
	 *
	 * @var int
	 */
	private $row = -1;

	/**
	 * Parse the List field modifier.
	 *
	 * @since 3.0
	 *
	 * @param Generator $generator Instance of the PDF Generator.
	 * @param array     $mapping   Mapping properties.
	 */
	public function __construct( $generator, $mapping ) {

		parent::__construct( $generator, $mapping );

		foreach ( $this->modifiers as $modifier ) {

			if ( substr( $modifier, 0, 5 ) !== 'list=' ) {
				continue;
			}

			$this->remove_modifier( $modifier );

			$column_row = substr( $modifier, 5 );

			$this->row    = (int) substr( $column_row, strrpos( $column_row, ',' ) + 1 );
			$this->column = substr( $column_row, 0, ( strlen( $this->row ) + 1 ) * - 1 );

		}

	}

	/**
	 * Returns the List row value.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_value() {

		// If no row or column is defined, return default value.
		if ( ! $this->get_column() && $this->get_row() === -1 ) {
			return parent::get_value();
		}

		$field_value = rgar( $this->generator->entry, $this->field_id );
		$field_value = maybe_unserialize( $field_value );

		$target_row = $this->get_row() - 1;

		if ( $this->field->enableColumns ) {
			$row = rgar( $field_value, $target_row, [] );
			return rgar( $row, $this->column );
		}

		return rgar( $field_value, $target_row );

	}

	/**
	 * Returns the parsed target List column
	 *
	 * @since 4.0
	 *
	 * @return false|string
	 */
	public function get_column() {

		return $this->column;

	}

	/**
	 * Returns the parsed target List row.
	 *
	 * @since 4.0
	 *
	 * @return int
	 */
	public function get_row() {

		return $this->row;

	}

}

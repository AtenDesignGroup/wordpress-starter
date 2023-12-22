<?php

namespace ForGravity\Fillable_PDFs\Generator\Field;

use ForGravity\Fillable_PDFs\Generator;
use GFAPI;

/**
 * Fillable PDFs PDF Generator Nested Forms field value class.
 *
 * @since     3.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2021, ForGravity
 */
class Form extends Base {

	/**
	 * Gravity Forms Field object.
	 *
	 * @since 3.0
	 *
	 * @var \GP_Nested_Form_Field
	 */
	protected $field;

	/**
	 * Target child entry.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	private $index;

	/**
	 * Parse the Nested Forms field modifier.
	 *
	 * @since 3.0
	 *
	 * @param Generator $generator Instance of the PDF Generator.
	 * @param array     $mapping   Mapping properties.
	 */
	public function __construct( &$generator, $mapping ) {

		parent::__construct( $generator, $mapping );

		foreach ( $this->modifiers as $modifier ) {

			if ( substr( $modifier, 0, 8 ) !== 'nfIndex=' ) {
				continue;
			}

			$this->remove_modifier( $modifier );

			$index       = str_replace( 'nfIndex=', '', $modifier );
			$this->index = empty( $index ) ? 'auto' : $index;

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

		$child_entry = $this->get_entry_by_index();
		$child_form  = $this->field ? GFAPI::get_form( $this->field->gpnfForm ) : [];

		// Initialize generator based on child entry.
		$child_generator = new Generator(
			$this->generator->feed,
			$child_entry,
			$child_form
		);

		$mapping = [
			'field'     => $this->value,
			'value'     => '',
			'modifiers' => $this->modifiers,
		];

		/**
		 * @var Generator\Field\Base $field
		 */
		$field_class = $child_generator->get_field_class_name( rgar( $mapping, 'field' ) );
		$field       = new $field_class( $child_generator, $mapping );

		$field_value     = $field->get_value();
		$this->modifiers = $field->get_modifiers();

		return $field_value;

	}

	/**
	 * Returns the child entry object using the designated index.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	private function get_entry_by_index() {

		$entries_index = &$this->generator->nested_form_entries_index;
		$entries       = rgar( $this->generator->entry, $this->field->id, [] );
		$indexes       = array_keys( $entries );

		switch ( $this->index ) {

			case 'auto':
				// Initialize Nested Form field.
				if ( ! isset( $entries_index[ $this->field_id ] ) ) {
					$entries_index[ $this->field_id ] = [];
				}

				// Initialize Nested Form field child field.
				if ( ! isset( $entries_index[ $this->field_id ][ $this->value ] ) ) {
					$entries_index[ $this->field_id ][ $this->value ] = -1;
				}

				// Increase Nested Form field child field index.
				$entries_index[ $this->field_id ][ $this->value ]++;

				$entry_index = $entries_index[ $this->field_id ][ $this->value ];

				break;

			case 'first':
				$entry_index = 0;
				break;

			case 'last':
				$entry_index = end( $indexes );
				break;

			default:
				$entry_index = $this->index - 1;
				break;

		}

		return in_array( $entry_index, $indexes ) ? $this->generator->entry[ $this->field->id ][ $entry_index ] : [];

	}

}

<?php

namespace ForGravity\Fillable_PDFs\Generator\Field;

use ForGravity\Fillable_PDFs\Generator;
use GFSignature;

/**
 * Fillable PDFs PDF Generator Signature field value class.
 *
 * @since     3.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2021, ForGravity
 */
class Signature extends Base {

	/**
	 * Gravity Forms Field object.
	 *
	 * @since 3.0
	 *
	 * @var \GF_Field_Signature
	 */
	protected $field;

	/**
	 * Force image_fill modifier.
	 *
	 * @since 3.0
	 *
	 * @param Generator $generator Instance of the PDF Generator.
	 * @param array     $mapping   Mapping properties.
	 */
	public function __construct( $generator, $mapping ) {

		parent::__construct( $generator, $mapping );

		if ( ! in_array( 'image_fill', $this->modifiers ) ) {
			$this->modifiers[] = 'image_fill';
		}

	}

	/**
	 * Returns the Signature image URL, with the transparency query argument.
	 *
	 * @since 3.0
	 * @since 3.4 Add support for image binaries.
	 * @since 4.2 Always returns image binaries.
	 *
	 * @return string|null
	 */
	public function get_value() {

		// Get path to signature image.
		$file_name = pathinfo( rgar( $this->generator->entry, $this->field->id ), PATHINFO_FILENAME );
		$file_path = sprintf( '%s%s.png', trailingslashit( GFSignature::get_signatures_folder() ), $file_name );

		return $this->get_file_binary( $file_path );

	}

}

<?php

namespace ForGravity\Fillable_PDFs\Generator\Field;

use GFFormsModel;

/**
 * Fillable PDFs PDF Generator FileUpload field value class.
 *
 * @since     3.4
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2021, ForGravity
 */
class FileUpload extends Base {

	/**
	 * Gravity Forms Field object.
	 *
	 * @since 3.4
	 *
	 * @var \GF_Field_FileUpload
	 */
	protected $field;

	/**
	 * Returns either the file URL or the image binary as a Base64 encoded string.
	 *
	 * @since 3.4
	 *
	 * @return string|null
	 */
	public function get_value() {

		if ( $this->field->multipleFiles ) {
			$value = rgar( $this->generator->entry, $this->field->id );
			$value = empty( $value ) ? null : json_decode( $value, true )[0];
		} else {
			$value = parent::get_value();
		}

		if ( ! $this->use_image_binary() ) {
			return $value;
		}

		// Get local path to uploaded file.
		$path = GFFormsModel::get_physical_file_path( $value );

		return $this->get_file_binary( $path );

	}

}

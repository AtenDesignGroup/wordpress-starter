<?php

namespace ForGravity\Fillable_PDFs\Templates\Fields;

use Exception;
use Gravity_Forms\Gravity_Forms\Settings\Fields\Hidden;

defined( 'ABSPATH' ) || die();

class Template_File extends Hidden {

	/**
	 * Field type.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $type = 'fg_fillablepdfs_template_file';





	// # RENDER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Render field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function markup() {

		// Insert hidden container to store file name.
		$html = parent::markup();

		// Get current template.
		$template = fg_fillablepdfs_templates()->get_current_template();

		// Get changed field name.
		$changed_name = sprintf( '%s_%s_changed', $this->settings->get_input_name_prefix(), $this->name );

		// Determine if upload markup should be shown.
		$show_upload = empty( $template ) || ( $template && rgpost( $changed_name ) && $this->settings->get_field_errors() );

		// Prepare upload markup.
		$html = sprintf(
			'<div class="fillablepdfs-dropzone" data-file="%2$s_%3$s"%1$s>
				<input type="file" id="%2$s_%3$s" name="%2$s_%3$s" accept="application/pdf" />
				<input type="text" name="%2$s_%3$s_changed" class="fillablepdfs-dropzone__changed" value="%5$s" />
				<span>%4$s</span>
			</div>',
			$show_upload ? '' : ' style="display:none;"',
			$this->settings->get_input_name_prefix(),
			$this->name,
			esc_html__( 'Drop or click to add PDF file here.', 'forgravity_fillablepdfs' ),
			rgpost( $changed_name )
		);

		// Prepare template info markup.
		$html .= sprintf(
			'<div class="fillablepdfs-template-info"%1$s>
				<img src="%2$s/dist/images/templates/placeholder.svg" width="100" class="fillablepdfs-template-info__placeholder" alt="%3$s">
				<span class="fillablepdfs-template-info__meta">
					<span class="fillablepdfs-template-info__name">%3$s</span>
					<span class="fillablepdfs-template-info__file-name">%4$s</span>
				</span>
				<button type="button" class="fillablepdfs-template-info__action fillablepdfs-template-info__action--replace">%5$s</button>
			</div>',
			$show_upload ? ' style="display:none;"' : '',
			fg_fillablepdfs()->get_asset_url(),
			$this->settings->get_field( 'name' )->get_value(),
			rgar( $template, 'pdf_name', '' ),
			esc_html__( 'Replace', 'forgravity_fillablepdfs' )
		);

		// Display any error message.
		$html .= $this->get_error_icon();

		return $html;

	}





	// # VALIDATION ----------------------------------------------------------------------------------------------------

	/**
	 * Validate the submitted PDF file.
	 *
	 * @since 3.0
	 *
	 * @param string $value
	 */
	public function do_validation( $value ) {

		// Get template.
		$template = fg_fillablepdfs_templates()->get_current_template();

		// If template already exists, return.
		if ( ! empty( $template ) && ! rgpost( sprintf( '%s_%s_changed', $this->settings->get_input_name_prefix(), $this->name ) ) ) {
			return;
		}

		// Get uploaded file.
		$uploaded_file = rgar( $_FILES, sprintf( '%s_%s', $this->settings->get_input_name_prefix(), $this->name ) );

		// If no file was uploaded, set error.
		if ( rgempty( 'name', $uploaded_file ) ) {
			$this->set_error( esc_html__( 'You must upload a PDF file.', 'forgravity_fillablepdfs' ) );
			return;
		}

		try {

			// Get file meta.
			$has_fields = fg_pdfs_api()->get_file_meta( $uploaded_file, true );

		} catch ( Exception $e ) {

			// Log that files could not be retrieved.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Could not retrieve template file fields; ' . $e->getMessage() );

			$this->set_error( esc_html__( 'Unable to check if file has any fields.', 'forgravity_fillablepdfs' ) );

			return;

		}

		// If the file has no fields, set error.
		if ( ! $has_fields['has_fields'] ) {
			$this->set_error( esc_html__( 'Template file must contain editable PDF fields.', 'forgravity_fillablepdfs' ) );
		}

	}

}

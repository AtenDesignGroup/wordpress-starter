<?php

namespace ForGravity\Fillable_PDFs\Legacy;

use Exception;
use GFAddOn;
use GFAPI;
use GFCommon;
use GFFormDetail;
use GFFormsModel;
use WP_Error;

/**
 * Fillable PDFs Import class.
 *
 * @since     2.4
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2020, ForGravity
 */
class Import {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Form failed to import.
	 *
	 * @since  2.0
	 * @access private
	 * @var    bool
	 */
	private $failed_import = false;

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @return Import
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Run importer.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @uses   GFAddOn::get_slug()
	 * @uses   GFAddOn::is_postback()
	 * @uses   GFCommon::add_error_message()
	 * @uses   GFCommon::add_message()
	 * @uses   Import::create_form_object()
	 */
	public function __construct() {

		// Handle postback.
		if ( fg_fillablepdfs()->is_postback() && rgpost( 'import_pdf' ) ) {

			// Verify nonce.
			check_admin_referer( fg_fillablepdfs()->get_slug() . '_import_pdf', fg_fillablepdfs()->get_slug() . '_import_pdf_nonce' );

			// Get form title and form fields.
			$form_title  = rgpost( 'form_title' );
			$form_fields = rgpost( 'form_fields' );

			// Create form object.
			$form_id = $this->create_form_object( $form_title, $form_fields );

			// Display admin message.
			if ( is_wp_error( $form_id ) ) {

				// Set failed state.
				$this->failed_import = true;

				// Display error message.
				GFCommon::add_error_message( $form_id->get_error_message() );

			} else {

				// Prepare edit link.
				$edit_link = sprintf(
					' <a href="admin.php?page=gf_edit_forms&id=%d">%s</a>',
					$form_id,
					__( 'Edit Form', 'forgravity_fillablepdfs' )
				);

				// Display success message.
				GFCommon::add_message( esc_html__( 'Form imported successfully.', 'forgravity_fillablepdfs' ) . $edit_link );

			}

		}

	}

	/**
	 * Import PDF page.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses   Import::import_mapping_page()
	 * @uses   Import::import_upload_page()
	 * @uses   GFAddOn::is_postback()
	 */
	public function import_page() {

		// Initialize API.
		if ( ! fg_fillablepdfs()->initialize_api() ) {
			return;
		}

		try {

			// Get license info.
			$license = fg_pdfs_api()->get_license_info();

		} catch ( Exception $e ) {

			// Log error.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Unable to get license info; ' . $e->getMessage() );

			return;

		}

		// If license does not have access to Import feature, exit.
		if ( ! $license['supports']['import'] ) {
			return;
		}

		// Add import description.
		printf(
			'<p class="textleft">%s</p><div class="hr-divider"></div>',
			esc_html__( 'Select the PDF template file you would like to import. When you select the file, you will be presented with a list of fields found within the PDF template file. You can then change the field label, field type and required state. To exclude a field from being imported, leave the field label empty.', 'forgravity_fillablepdfs' )
		);

		// If no postback, show upload page.
		if ( ! fg_fillablepdfs()->is_postback() || ( rgpost( 'import_pdf' ) && ! $this->failed_import ) ) {
			$this->import_upload_page();
		} else {
			$this->import_mapping_page();
		}

	}





	// # IMPORT PAGE ---------------------------------------------------------------------------------------------------

	/**
	 * Import PDF: Select PDF Template File page.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $error_message Error message to display.
	 *
	 * @uses   Fillable_PDFs::get_templates_as_choices()
	 * @uses   GFAddOn::get_select_options()
	 */
	public function import_upload_page( $error_message = '' ) {

		// Get templates as choices.
		$templates = fg_fillablepdfs()->get_templates_as_choices();

		?>
        <form method="post" enctype="multipart/form-data">

			<?php wp_nonce_field( fg_fillablepdfs()->get_slug() . '_import_pdf', fg_fillablepdfs()->get_slug() . '_import_pdf_nonce' ); ?>

			<?php if ( ! rgblank( $error_message ) ) { ?>
                <div class="error move-me"><?php echo $error_message; ?></div>
			<?php } ?>

            <p align="center">

				<span class="fillablepdfs-template-file-upload">
					<label class="button" for="fillablepdfs_template_file">Upload a PDF File</label>
					<input type="file" id="fillablepdfs_template_file" name="template_file"/>
					<span class="error-message"></span>
				</span>

				<?php if ( ! empty( $templates ) ) { ?>
                    or &nbsp;
                    <select name="template_id" data-placeholder="Select a Template" style="width:250px;">
						<?php echo fg_fillablepdfs()->get_select_options( fg_fillablepdfs()->get_templates_as_choices(), null ); ?>
                    </select>
				<?php } ?>

            </p>

        </form>
		<?php

	}





	// # IMPORT MAPPING ------------------------------------------------------------------------------------------------

	/**
	 * Import PDF: Map Fields page.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses   API::get_file_fields()
	 * @uses   API::get_template()
	 * @uses   Fillable_PDFs::initialize_api()
	 * @uses   Fillable_PDFs::get_field_types_as_options()
	 * @uses   GFAddOn::log_error()
	 */
	public function import_mapping_page() {

		// Initialize API.
		fg_fillablepdfs()->initialize_api();

		// Get uploaded file.
		$file = rgar( $_FILES, 'template_file' );

		// Get template or uploaded file fields.
		if ( rgpost( 'template_id' ) ) {

			try {

				// Get template.
				$template = fg_pdfs_api()->get_template( rgpost( 'template_id' ) );

				// Set selected template name and file name.
				$selected_template = esc_html( $template['name'] . ' (' . $template['pdf_name'] . ')' );
				$file_name         = $template['pdf_name'];

				// Set file fields.
				$fields = wp_list_pluck( $template['meta']['pages'], 'fields' );
				$fields = array_reduce( $fields, 'array_merge', [] );

			} catch ( Exception $e ) {

				// Log that template could not be retrieved.
				fg_fillablepdfs()->log_error( __METHOD__ . '(): Template could not be retrieved; ' . $e->getMessage() );

				$this->import_upload_page( esc_html__( 'Template could not be retrieved.', 'forgravity_fillablepdfs' ) );
				return;

			}

		} else {

			// If file is not included in postback, set file name.
			if ( ! $file ) {

				$selected_template = $file_name = rgpost( 'template_file_name' );

			} else {

				try {

					// Set selected template name.
					$selected_template = $file_name = esc_html( $file['name'] );

					// Get file fields.
					$file_meta = fg_pdfs_api()->get_file_meta( $file );

					// Set file fields.
					$fields = wp_list_pluck( $file_meta['meta']['pages'], 'fields' );
					$fields = array_reduce( $fields, 'array_merge', [] );

				} catch ( Exception $e ) {

					// Log that files could not be retrieved.
					fg_fillablepdfs()->log_error( __METHOD__ . '(): Could not retrieve template file fields; ' . $e->getMessage() );

					$this->import_upload_page( esc_html__( 'Could not retrieve template file fields.', 'forgravity_fillablepdfs' ) );

					return;

				}

			}

		}

		// Set fields to post values if set.
		if ( rgpost( 'form_fields' ) ) {
			$fields = rgpost( 'form_fields' );
		}

		// If no fields exist, display upload page.
		if ( empty( $fields ) ) {

			// Prepare error message.
			$error = esc_html__( 'Template file must contain editable PDF fields.', 'forgravity_fillablepdfs' );

			$this->import_upload_page( $error );

			return;

		}

?>
		<form method="post" enctype="multipart/form-data">

			<?php wp_nonce_field( fg_fillablepdfs()->get_slug() . '_import_pdf', fg_fillablepdfs()->get_slug() . '_import_pdf_nonce' ); ?>

			<input type="hidden" name="template_file_name" value="<?php echo esc_attr( $file_name ); ?>" />
			<input type="hidden" name="template_id" value="<?php echo esc_attr( rgpost( 'template_id' ) ); ?>" />

			<table class="form-table fillablepdfs-import-pdf">

				<tr valign="top">
					<th scope="row">
						<strong><?php esc_html_e( 'Selected Template', 'forgravity_fillablepdfs' ); ?></strong>
					</th>
					<td>
						<?php echo esc_html( $selected_template ); ?>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="fillablepdfs_form_name">
							<?php esc_html_e( 'Form Title', 'forgravity_fillablepdfs' ); ?>
							<span class="required">*</span>
						</label>
					</th>
					<td>
						<input type="text" name="form_title" id="fillablepdfs_form_name" value="<?php echo esc_attr( rgpost( 'form_title' ) ); ?>" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="fillablepdfs_form_fields">
							<?php esc_html_e( 'Map Fields', 'forgravity_fillablepdfs' ); ?>
						</label>
					</th>
					<td>

						<table class="fillablepdfs-import-pdf-mapping">

							<tr>
								<th><?php esc_html_e( 'Field Label', 'forgravity_fillablepdfs' ); ?></th>
								<th><?php esc_html_e( 'Field Type', 'forgravity_fillablepdfs' ); ?></th>
								<th>&nbsp;</th>
							</tr>

							<?php
								foreach ( $fields as $i => $field ) {

									// Prepare field choices.
									$field_choices = is_array( $field['choices'] ) ? wp_list_pluck( $field['choices'], 'label' ) : [];

									// Set initial display of choices button.
									$display_choices = ! in_array( $field['type'], array( 'select', 'checkbox', 'radio', 'multiselect' ), true ) ? 'style="display:none;"' : null;
							?>

							<tr>
								<td>
									<input type="text" name="form_fields[<?php echo esc_attr( $i ); ?>][label]" data-index="<?php echo esc_attr( $i ); ?>" value="<?php echo esc_attr( $field['label'] ); ?>" />
								</td>
								<td>
									<input type="hidden" name="form_fields[<?php echo esc_attr( $i ); ?>][choices]" data-index="<?php echo esc_attr( $i ); ?>" value='<?php echo json_encode( $field_choices ); ?>' />
									<select name="form_fields[<?php echo esc_attr( $i ); ?>][type]" data-index="<?php echo esc_attr( $i ); ?>"><?php echo $this->get_field_types_as_options( $field['type'] ); ?></select>
									<button type="button" class="button choices-button" <?php echo $display_choices; ?>><?php esc_html_e( 'Choices', 'forgravity_fillablepdfs' ); ?></button>
								</td>
								<td>
									<label>
										<input type="checkbox" name="form_fields[<?php echo esc_attr( $i ); ?>][required]" value="1" <?php checked( rgar( $field, 'required' ), true, true ); ?> /> <?php esc_html_e( 'Required', 'forgravity_fillablepdfs' ); ?>
									</label>
								</td>
							</tr>

							<?php } ?>

						</table>

					</td>
				</tr>

			</table>

			<br /><br />

			<div id="fillablepdfs-choices-editor" style="display:none;">

				<div class="fillablepdfs-choices-editor">

					<input type="hidden" name="index" value="" />
					<textarea placeholder="<?php esc_html_e( 'Separate options with a line break', 'forgravity_fillablepdfs' ); ?>"></textarea>

					<a href="#" class="button button-primary" data-action="save">Save Choices</a>&nbsp;
					<a href="#" class="button" data-action="cancel">Cancel</a>

				</div>

			</div>

			<input type="submit" value="<?php esc_html_e( 'Import Form', 'forgravity_fillablepdfs' ) ?>" name="import_pdf" class="button button-large button-primary" />

		</form>
<?php
	}





	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * Create form object from imported PDF.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $title
	 * @param array  $fields
	 *
	 * @uses   GFAPI::add_form()
	 * @uses   GFCommon::add_error_message()
	 * @uses   GFCommon::add_message()
	 * @uses   GFFormsModel::convert_field_objects()
	 *
	 * @return int|WP_Error
	 */
	public function create_form_object( $title = '', $fields = [] ) {

		// Get the form title.
		$title = sanitize_text_field( rgpost( 'form_title' ) );

		// If form title is empty, set error and display mapping page.
		if ( rgblank( $title ) ) {
			return new WP_Error( 'missing_title', __( 'You must enter a form title.', 'forgravity_fillablepdfs' ) );
		}

		// Get form fields.
		$fields = rgpost( 'form_fields' );

		// Build new form object.
		$form = array(
			'title'          => $title,
			'fields'         => $fields,
			'labelPlacement' => 'top_label',
			'button'         => array(
				'type' => 'text',
				'text' => esc_html__( 'Submit', 'gravityforms' ),
			),
		);

		// Clean up form fields and add field ID.
		foreach ( $form['fields'] as $i => &$field ) {

			// Set field ID.
			$field['id'] = $i + 1;

			// Sanitize label.
			$field['label'] = sanitize_text_field( rgar( $field, 'label' ) );

			// If field label is empty, remove the field.
			if ( rgblank( $field['label'] ) ) {
				unset( $form['fields'][ $i ] );
				continue;
			}

			// Convert required to bool.
			$field['isRequired'] = boolval( rgar( $field, 'required' ) );

			// Remove original required flag.
			unset( $field['required'] );

			// Loop through choice based input types.
			if ( in_array( $field['type'], array( 'select', 'multiselect', 'radio', 'checkbox' ), true ) ) {

				// Decode choices.
				$field['choices'] = json_decode( $field['choices'], true );

				// If no choices are defined, use default choices.
				if ( ! is_array( $field['choices'] ) || ( is_array( $field['choices'] ) && empty( $field['choices'] ) ) ) {

					$field['choices'] = array(
						array(
							'text'  => esc_html__( 'First Choice', 'gravityforms' ),
							'value' => esc_html__( 'First Choice', 'gravityforms' ),
						),
						array(
							'text'  => esc_html__( 'Second Choice', 'gravityforms' ),
							'value' => esc_html__( 'Second Choice', 'gravityforms' ),
						),
						array(
							'text'  => esc_html__( 'Third Choice', 'gravityforms' ),
							'value' => esc_html__( 'Third Choice', 'gravityforms' ),
						),
					);

				} else {

					// Convert choices to text/values arrays.
					foreach ( $field['choices'] as $j => $choice ) {

						// Add choice.
						$field['choices'][ $j ] = array(
							'text'  => sanitize_text_field( $choice ),
							'value' => sanitize_text_field( $choice ),
						);
						
					}

				}

				// Add inputs for checkbox field.
				if ( 'checkbox' === $field['type'] ) {

					// Loop through field choices.
					foreach ( $field['choices'] as $j => $choice ) {

						// Set initial choice number.
						$choice_number = $j + 1;

						// Skip choice number ending in 0.
						if ( $choice_number % 10 == 0 ) {
							$choice_number++;
						}

						// Get input ID.
						$input_id = $field['id'] . '.' . $choice_number;

						// Add input.
						$field['inputs'][] = array(
							'id'    => $input_id,
							'label' => sanitize_text_field( $choice['text'] ),
							'value' => '',
						);

					}

				}

			} else {

				// Remove choices.
				unset( $field['choices'] );

			}

		}

		// Convert field objects.
		$form = GFFormsModel::convert_field_objects( $form );

		// Add default notification.
		if ( apply_filters( 'gform_default_notification', true ) ) {

			$default_notification = [
				'id'       => uniqid(),
				'isActive' => true,
				'to'       => '{admin_email}',
				'name'     => __( 'Admin Notification', 'gravityforms' ),
				'event'    => 'form_submission',
				'toType'   => 'email',
				'subject'  => __( 'New submission from', 'gravityforms' ) . ' {form_title}',
				'message'  => '{all_fields}',
			];

			$form['notifications'] = [ $default_notification['id'] => $default_notification ];

		}

		// Add form.
		$form_id = GFAPI::add_form( $form );

		return $form_id;

	}

	/**
	 * Prepare form field types as options for PDF importer.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $selected Selected field type.
	 *
	 * @uses   GFCommon::get_base_path()
	 * @uses   GFFormDetail::get_field_groups()
	 *
	 * @return string
	 */
	public function get_field_types_as_options( $selected ) {

		// Include Form Detail class.
		if ( ! class_exists( 'GFFormDetail' ) ) {
			require_once( GFCommon::get_base_path() . '/form_detail.php' );
		}

		// Get form field groups.
		$field_groups = GFFormDetail::get_field_groups();

		// Initialize return string.
		$html = '';

		// Loop through field groups.
		foreach ( $field_groups as $field_group ) {

			// Open optgroup for field group.
			$html .= sprintf(
				'<optgroup label="%s">',
				esc_attr( $field_group['label'] )
			);

			// Add field group's fields as options.
			foreach ( $field_group['fields'] as $field ) {
				$html .= sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $field['data-type'] ),
					selected( $selected, $field['data-type'], false ),
					esc_html( $field['value'] )
				);
			}

			// Close optgroup.
			$html .= '</optgroup>';

		}

		return $html;

	}

}

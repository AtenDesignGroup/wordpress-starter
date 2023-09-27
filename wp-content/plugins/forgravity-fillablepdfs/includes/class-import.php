<?php

namespace ForGravity\Fillable_PDFs;

use Exception;
use GFAPI;
use GFCommon;
use GFFormDetail;
use Gravity_Forms\Gravity_Forms\Settings\Fields as Settings_API_Fields;
use Gravity_Forms\Gravity_Forms\Settings\Settings as Settings_API;

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
	 * Instance of the Settings API.
	 *
	 * @since 2.4
	 * @var   Settings_API
	 */
	private $mapping_renderer = false;

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

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
	 * Import PDF page.
	 *
	 * @since  1.0
	 */
	public function import_page() {

		// Initialize renderer.
		$this->initialize_renderer();

		// If user does not have access to Import PDFs feature, display upgrade message.
		if ( ! self::can_import() ) {
			self::display_upgrade_message();
			return;
		}

		// If there are no POST parameters, display the upload page.
		if ( empty( $_POST ) ) {
			self::display_upload();
			return;
		}

		// If a file was uploaded, display the mapping page.
		if ( ! empty( $_POST ) ) {

			// Handle initial upload.
			if ( ! empty( $_FILES ) && empty( $this->get_detected_fields() ) ) {
				self::display_alert( esc_html__( 'File must contain at least one editable field.', 'forgravity_fillablepdfs' ), 'error' );
				self::display_upload();
				return;
			}

			// Process postback.
			if ( empty( $_FILES ) ) {
				$this->mapping_renderer->process_postback();
			}

			// Display upload page upon successful submission.
			if ( empty( $_FILES ) && ! $this->mapping_renderer->get_field_errors() ) {
				self::display_upload();
				return;
			}

			$this->mapping_renderer->render();
			return;

		}

	}

	/**
	 * Initialize Settings API for mapping form.
	 *
	 * @since 2.4
	 */
	private function initialize_renderer() {

		$this->mapping_renderer = new Settings_API(
			[
				'fields'        => $this->get_mapping_ui_fields(),
				'capability'    => fg_fillablepdfs()->get_capabilities( 'form_settings' ),
				'save_callback' => [ $this, 'create_form' ],
			]
		);

	}

	/**
	 * Display an alert above Import UI.
	 *
	 * @since 2.4
	 *
	 * @param string $message Message to display.
	 * @param string $type    Message type.
	 */
	private static function display_alert( $message, $type = 'success' ) {

		printf( '<div class="alert %s">%s</div>', $type, $message );

	}




	// # UPLOAD -------------------------------------------------------------------------------------------------------

	/**
	 * Display the file upload screen.
	 *
	 * @since 2.4
	 */
	private function display_upload() {

		?>

		<div class="gform-settings-panel">

			<form method="post" enctype="multipart/form-data" class="fillablepdfs-import-dropzone">

				<?php wp_nonce_field( fg_fillablepdfs()->get_slug() . '_import', fg_fillablepdfs()->get_slug() . '_import_nonce' ); ?>

				<div class="fillablepdfs-dropzone" data-file="pdf_file" data-import="true">
					<input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" />
					<input type="hidden" class="fillablepdfs-dropzone__changed" value="1" />
					<span><?php esc_html_e( 'Drop or click to select a PDF to start the import process.', 'forgravity_fillablepdfs' ); ?></span>
				</div>

			</form>

		</div>

		<?php

	}




	// # MAPPING -------------------------------------------------------------------------------------------------------

	/**
	 * Create a form after successful validation.
	 *
	 * @since 2.4
	 *
	 * @param array $values Posted values.
	 */
	public function create_form( $values ) {

		// Initialize form object.
		$form = [
			'title'          => rgar( $values, 'title' ),
			'fields'         => [],
			'labelPlacement' => 'top_label',
			'button'         => [
				'type' => 'text',
				'text' => esc_html__( 'Submit', 'gravityforms' ),
			],
		];

		$field_id = 1;

		// Loop through posted fields, prepare properties.
		foreach ( $values['fields'] as $posted_field ) {

			// Create field.
			$field = self::create_field( $posted_field, $field_id );

			// If field was created, add it to the form.
			if ( ! empty( $field ) ) {
				$form['fields'][] = $field;
				$field_id++;
			}

		}

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

		// Prepare message.
		if ( is_wp_error( $form_id ) ) {

			$message      = sprintf(
				esc_html__( 'Unable to create form. %s', 'forgravity_fillablepdfs' ),
				$form_id->get_error_message()
			);
			$message_type = 'error';

		} else {

			$message      = sprintf(
				'%1$s <a href="%3$s">%2$s</a>',
				esc_html__( 'Your form has been successfully imported.', 'forgravity_fillablepdfs' ),
				esc_html__( 'Click here to edit it.', 'forgravity_fillablepdfs' ),
				esc_url( admin_url( 'admin.php?page=gf_edit_forms&id=' . $form_id ) )
			);
			$message_type = 'success';

		}

		self::display_alert( $message, $message_type );

	}

	/**
	 * Create a field object from a posted field.
	 *
	 * @since 2.4
	 *
	 * @param array      $props    Field properties.
	 * @param string|int $field_id Field ID.
	 *
	 * @return array|null
	 */
	private static function create_field( $props, $field_id ) {

		// If this field is excluded, return.
		if ( rgar( $props, 'exclude', false ) ) {
			return null;
		}

		// Initialize field.
		$field = [
			'id'         => $field_id,
			'label'      => rgar( $props, 'label', __( 'Untitled', 'gracityforms' ) ),
			'type'       => rgar( $props, 'type', 'text' ),
			'isRequired' => boolval( rgar( $props, 'required', false ) ),
			'choices'    => [],
			'cssClass'   => '',
			'size'       => 'large',
		];

		// Handle Date field.
		if ( $field['type'] === 'date' ) {
			$field['dateType']            = 'datepicker';
			$field['dateFormat']          = 'mdy';
			$field['dateFormatPlacement'] = 'below';
			$field['calendarIconType']    = 'none';
		}

		// If this is not a field that supports choices, return.
		if ( ! in_array( $field['type'], self::get_fields_with_choices() ) ) {
			return $field;
		}

		// If no choices were provided, use default choices.
		if ( rgempty( 'choices', $props ) ) {

			$field['choices'] = [
				[
					'text'  => esc_html__( 'First Choice', 'gravityforms' ),
					'value' => esc_html__( 'First Choice', 'gravityforms' ),
				],
				[
					'text'  => esc_html__( 'Second Choice', 'gravityforms' ),
					'value' => esc_html__( 'Second Choice', 'gravityforms' ),
				],
				[
					'text'  => esc_html__( 'Third Choice', 'gravityforms' ),
					'value' => esc_html__( 'Third Choice', 'gravityforms' ),
				],
			];

		} else {

			// Add choices.
			foreach ( $props['choices'] as $i => $choice ) {

				// Add choice.
				$field['choices'][] = [
					'text'  => sanitize_text_field( $choice ),
					'value' => sanitize_text_field( $choice ),
				];

			}

		}

		// If this is not a checkbox field, return.
		if ( $field['type'] !== 'checkbox' ) {
			return $field;
		}

		// Add inputs.
		$field['inputs'] = [];
		$choice_id       = 0;
		foreach ( $field['choices'] as $choice ) {

			// Prepare choice ID.
			$choice_id++;
			$choice_id = ( $choice_id % 10 == 0 ) ? $choice_id + 1 : $choice_id;

			// Prepare input ID.
			$input_id = sprintf( '%s.%s', $field_id, $choice_id );

			// Add input.
			$field['inputs'][] = [
				'id'    => $input_id,
				'label' => $choice['text'],
				'value' => '',
			];

		}

		return $field;

	}

	/**
	 * Get settings fields for mapping form.
	 *
	 * @since 2.4
	 *
	 * @return array
	 */
	private function get_mapping_ui_fields() {

		require_once( GFCommon::get_base_path() . '/includes/settings/class-fields.php' );

		Settings_API_Fields::register( 'fg_fillablepdfs_import_fields', '\ForGravity\Fillable_PDFs\Settings\Fields\Import_Fields' );
		Settings_API_Fields::register( 'fg_fillablepdfs_import_file', '\ForGravity\Fillable_PDFs\Settings\Fields\Import_File' );

		return [
			[
				'fields' => [
					[
						'name'          => 'file_info',
						'type'          => 'fg_fillablepdfs_import_file',
						'default_value' => self::get_uploaded_file(),
					],
					[
						'name'          => 'title',
						'type'          => 'text',
						'label'         => esc_html__( 'Form Title', 'forgravity_fillablepdfs' ),
						'required'      => true,
						'error_message' => esc_html__( 'You must set a form title.', 'forgravity_fillablepdfs' ),
					],
					[
						'name'          => 'fields',
						'type'          => 'fg_fillablepdfs_import_fields',
						'label'         => esc_html__( 'Map Form Fields', 'forgravity_fillablepdfs' ),
						'description'   => esc_html__( 'The list below are fields discovered in the uploaded PDF. Change the label, switch to a different field type, make a field required or exclude a field from being included in the create form.', 'forgravity_fillablepdfs' ),
						'default_value' => wp_json_encode( $this->get_detected_fields() ),
					],
					[
						'type'  => 'save',
						'value' => esc_html__( 'Import Form', 'forgravity_fillablepdfs' ),
					],
				],
			],
		];

	}





	// # UPGRADE -------------------------------------------------------------------------------------------------------

	/**
	 * Display upgrade message if user does not have access to feature.
	 *
	 * @since 2.4
	 */
	private static function display_upgrade_message() {

		// Get upgrade URL.
		$upgrade_url = self::get_upgrade_url();

		// Prepare button.
		$upgrade_button = '';
		if ( $upgrade_url ) {
			$upgrade_button = sprintf(
				'<a href="%s" class="fillablepdfs-license-feature-upgrade">%s</a>',
				esc_url( $upgrade_url ),
				esc_html__( 'Upgrade License', 'forgravity_fillablepdfs' )
			);
		}

		?>

		<div class="fillablepdfs-import__upgrade">

			<img src="<?php echo fg_fillablepdfs()->get_base_url() . '/dist/images/features/import.svg'; ?>" alt="<?php esc_html_e('Upgrade to Import', 'forgravity_fillablepdfs' ); ?>" width="114" />

			<span class="fillablepdfs-import__upgrade-message">

				<h3><?php esc_html_e( 'Upgrade to Professional for access to this feature.', 'forgravity_fillablepdfs' ); ?></h3>
				<p><?php esc_html_e( 'With the Professional License, you can upload up to 100 templates. Plus, you get the extremely powerful Convert PDFs into Gravity Forms extension!', 'forgravity_fillablepdfs' ); ?></p>

				<?php echo $upgrade_button; ?>

			</span>

		</div>

		<?php

	}

	/**
	 * Returns URL to upgrade to a Professional license.
	 *
	 * @since 2.4
	 *
	 * @return string|null
	 */
	private static function get_upgrade_url() {

		// If API is not initialized, return.
		if ( ! fg_pdfs_api() ) {
			return '';
		}

		try {

			// Get license info.
			$license = fg_pdfs_api()->get_license_info();

		} catch ( Exception $e ) {

			// Log that license info could not be retrieved.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Unable to retrieve license info; ' . $e->getMessage() );

			return '';

		}

		return rgars( $license, 'upgrade_urls/professional' );


	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Determines if user has access to the Import PDFs feature.
	 *
	 * @since 2.4
	 *
	 * @return bool
	 */
	public static function can_import() {

		// Initialize API.
		if ( ! fg_pdfs_api() ) {
			return false;
		}

		try {

			// Get license info.
			$license = fg_pdfs_api()->get_license_info();

		} catch ( Exception $e ) {

			// Log error.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Unable to get license info; ' . $e->getMessage() );

			return false;

		}

		return (bool) rgars( $license, 'supports/import' );

	}

	/**
	 * Get files found in uploaded file.
	 *
	 * @since 2.4
	 *
	 * @return array|bool
	 */
	private function get_detected_fields() {

		// Get file meta.
		$file_meta = $this->get_file_meta();

		// If no file was provided, return.
		if ( ! $file_meta ) {
			return false;
		}

		// Pull fields from each page, merge into one array.
		$fields = wp_list_pluck( rgars( $file_meta, 'meta/pages' ), 'fields' );
		$fields = array_reduce( $fields, 'array_merge', [] );

		// Clean up field data.
		foreach ( $fields as &$field ) {

			// Remove unneeded keys.
			$field = array_intersect_key(
				$field,
				array_flip(
					[
						'label',
						'type',
						'choices',
						'required',
					]
				)
			);

			// Set excluded flag.
			$field['exclude'] = false;

			// Use only choice labels.
			if ( ! empty( rgar( $field, 'choices' ) ) ) {
				$field['choices'] = wp_list_pluck( $field['choices'], 'label' );
			}

		}

		return $fields;

	}

	/**
	 * Get meta of uploaded file.
	 *
	 * @since 2.4
	 *
	 * @return array|bool
	 */
	private function get_file_meta() {

		static $file_meta;

		// If file meta has been retrieved, return.
		if ( isset( $file_meta ) ) {
			return $file_meta;
		}

		// Get uploaded file.
		$file = self::get_uploaded_file();

		// If no file was uploaded, return.
		if ( ! $file ) {
			return false;
		}

		// If we cannot connect to API, exit.
		if ( ! fg_pdfs_api() ) {
			return false;
		}

		try {

			// Get file fields.
			$file_meta = fg_pdfs_api()->get_file_meta( $file );

		} catch ( Exception $e ) {

			// Log that files could not be retrieved.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Could not retrieve template file fields; ' . $e->getMessage() );

			return false;

		}

		return $file_meta;

	}

	/**
	 * Get available form field types, grouped by category.
	 *
	 * @since 2.4
	 *
	 * @return array
	 */
	public static function get_field_groups() {

		if ( ! class_exists( 'GFFormDetail' ) ) {
			require_once( GFCommon::get_base_path() . '/form_detail.php' );
		}

		$groups = [];

		// Loop through field groups, add field types.
		foreach ( GFFormDetail::get_field_groups() as $field_group ) {

			// Prepare group.
			$groups[ $field_group['name'] ] = [
				'label' => rgar( $field_group, 'label' ),
				'types' => [],
			];

			foreach ( $field_group['fields'] as $field ) {
				$groups[ $field_group['name'] ]['types'][] = [
					'type'  => rgar( $field, 'data-type' ),
					'label' => rgar( $field, 'value' ),
				];
			}


		}

		return $groups;

	}

	/**
	 * Returns the field types that support choices.
	 *
	 * @since 2.4
	 *
	 * @return array
	 */
	public static function get_fields_with_choices() {

		return [ 'select', 'multiselect', 'list', 'radio', 'checkbox' ];

	}

	/**
	 * Return the uploaded PDF file.
	 *
	 * @since 2.4
	 *
	 * @return array|null
	 */
	private static function get_uploaded_file() {

		return rgar( $_FILES, 'pdf_file' );

	}

}

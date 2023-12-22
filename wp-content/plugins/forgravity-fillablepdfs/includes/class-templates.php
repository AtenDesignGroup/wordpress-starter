<?php

namespace ForGravity\Fillable_PDFs;

use Exception;
use GFCommon;
use Gravity_Forms\Gravity_Forms\Settings\Settings as Settings_API;

/**
 * Fillable PDFs Templates class.
 *
 * @since     1.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2017, ForGravity
 */
class Templates {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since 1.0
	 * @var   object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Instance of the Settings API.
	 *
	 * @since 2.4
	 * @var Settings_API
	 */
	private $renderer = false;

	/**
	 * Returns the option name storing the templates per page value.
	 *
	 * @since 3.0
	 * @var string
	 */
	public static $per_page_option = 'fg_fillablepdfs_templates_per_page';

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 *
	 * @return Templates
	 */
	public static function get_instance() {

		if ( self::$_instance === null ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Initialize Settings API for edit page.
	 *
	 * @since 2.4
	 */
	public function initialize() {

		// If this is not the templates page, exit.
		if ( rgget( 'page' ) !== fg_fillablepdfs()->get_slug() && rgget( 'subview' ) !== 'templates' ) {
			return;
		}

		// If this is not the edit page, exit.
		if ( ! isset( $_GET['id'] ) || self::do_delete() ) {
			return;
		}

		// If the API is not initialize, exit.
		if ( ! fg_pdfs_api() ) {
			return;
		}

		// Get current template.
		$template = $this->get_current_template();

		// Initialize new settings renderer.
		$this->renderer = new Settings_API(
			[
				'capability'     => fg_fillablepdfs()->get_capabilities( 'settings_page' ),
				'fields'         => $this->settings_fields(),
				'initial_values' => $template,
				'save_callback'  => [ $this, 'save_template' ],
			]
		);

		// Process save callback.
		if ( $this->renderer->is_save_postback() ) {
			$this->renderer->process_postback();
		}

		// Set validation message on redirect.
		$this->renderer->set_postback_message_callback( function( $message ) {

			// Get referrer.
			$referrer = rgar( $_SERVER, 'HTTP_REFERER' );

			// If referrer not provided, return.
			if ( ! $referrer ) {
				return $message;
			}

			// Parse URL, get template ID.
			$query_args = [];
			$referrer   = wp_parse_url( $referrer );
			parse_str( rgar( $referrer, 'query' ), $query_args );

			if ( rgar( $query_args, 'id' ) == '0' && empty( $_POST ) ) {
				return $this->renderer->get_save_success_message();
			}

			return $message;

		} );

	}

	/**
	 * Display templates plugin page.
	 *
	 * @since  1.0
	 */
	public function templates_page() {

		// Edit template.
		if ( isset( $_GET['id'] ) && ! self::do_delete() ) {
			$this->edit_page();
			return;
		}

		// Delete template(s).
		if ( self::do_delete() ) {
			$this->maybe_delete_template();
		}

		// Display list page.
		$this->list_page();

	}





	// # LIST TEMPLATES ------------------------------------------------------------------------------------------------

	/**
	 * Display templates list page.
	 *
	 * @since  1.0
	 */
	public function list_page() {

		?>
		<?php GFCommon::display_admin_message(); ?>

		<div class="gform-settings-panel">
			<header class="gform-settings-panel__header">
				<h4 class="gform-settings-panel__title"><?php esc_html_e( 'Templates', 'forgravity_fillablepdfs' ); ?></h4>
			</header>

			<div class="gform-settings-panel__content">
				<form id="fillablepdfs-templates" action="" method="post">
					<?php

					$table = $this->get_table();
					$table->prepare_items();
					$table->display();
					?>

					<!--Needed to save state after bulk operations-->
					<input type="hidden" value="<?php esc_attr_e( fg_fillablepdfs()->get_slug() ); ?>" name="page">
					<input type="hidden" value="<?php esc_attr_e( fg_fillablepdfs()->get_current_subview() ); ?>" name="subview">
					<input id="single_action" type="hidden" value="" name="single_action">
					<input id="single_action_argument" type="hidden" value="" name="single_action_argument">
					<?php wp_nonce_field( 'template_list', 'template_list' ) ?>
				</form>

				<script type="text/javascript">
					<?php GFCommon::gf_vars() ?>

					window.addEventListener( 'load', function( e ) {

						let $deletes = document.querySelectorAll( '.fillablepdfs__templates .row-actions .delete a' );

						if ( $deletes.length > 0 ) {
							$deletes.forEach( function( $delete ) {
								$delete.addEventListener( 'click', function( e ) {

									if ( ! confirm( "<?php echo esc_js( __( "Are you sure you want to delete this template?\n\nAny feeds using this template will stop functioning until they are updated. Previously generated PDFs will still be downloadable.", 'forgravity_fillablepdfs' ) ); ?>" ) ) {
										e.preventDefault();
									}

								} );
							} )
						}

					} );
				</script>
			</div>
		</div>
		<?php

	}

	/**
	 * Add the templates per page screen option.
	 *
	 * @since 3.0
	 */
	public static function action_load_forms_page_forgravity_fillablepdfs() {

		$screen = get_current_screen();

		if ( ! is_object( $screen ) ) {
			return;
		}

		if ( rgget( 'subview' ) !== 'templates' || isset( $_GET['id'] ) || rgget( 'action' ) ) {
			return;
		}

		add_screen_option(
			'per_page',
			[
				'label'   => esc_html__( 'Templates per page', 'forgravity_fillablepdfs' ),
				'default' => 20,
				'option'  => self::$per_page_option,
			]
		);

	}

	/**
	 * Save the templates per page screen option.
	 *
	 * @since 3.0
	 *
	 * @param bool|int $status Screen option value. Not used. Defaults to false.
	 * @param string   $option The option to check.
	 * @param int      $value  The number of templates to display per page.
	 *
	 * @return false|int
	 */
	public static function filter_set_screen_option_fg_fillablepdfs_templates_per_page( $status, $option, $value ) {

		if ( $option === self::$per_page_option ) {
			return (int) $value;
		}

		return false;

	}





	// # EDIT TEMPLATE -------------------------------------------------------------------------------------------------

	/**
	 * Display template edit page.
	 *
	 * @since  2.0
	 */
	public function edit_page() {

		// If API is not initialized, display configure Add-On message.
		if ( ! fg_pdfs_api() ) {

			// Display error message.
			printf(
				'<div>%s</div>',
				fg_fillablepdfs()->configure_addon_message()
			);

			return;
		}

		// Get current template.
		$template = $this->get_current_template();

		// If we are creating a new template, check template creation limit.
		if ( is_array( $template ) && empty( $template ) ) {

			try {

				// Get license info.
				$license = fg_pdfs_api()->get_license_info();

			} catch ( Exception $e ) {

				// Log error.
				fg_fillablepdfs()->log_error( __METHOD__ . '(): Unable to get license info; ' . $e->getMessage() );

				esc_html_e( 'Unable to get license info.', 'forgravity_fillablepdfs' );
				return;

			}

			// Check template creation limit.
			if ( ! $license['supports']['create_templates'] ) {
				esc_html_e( 'You have reached the active templates limit.', 'forgravity_fillablepdfs' );
				return;
			}

		}

		$this->renderer->render();

	}

	/**
	 * Maybe save template.
	 *
	 * @since  1.0
	 *
	 * @param array $values Posted values.
	 */
	public function save_template( $values ) {

		// Prepare new values for updating.
		$template_id = rgget( 'id' );
		$name        = rgar( $values, 'name' );
		$file        = null;

		// Get template file.
		if ( $template_id === '0' || rgar( $values, 'pdf_name_changed' ) ) {
			$file = rgar( $_FILES, sprintf( '%s_%s', $this->renderer->get_input_name_prefix(), 'pdf_name' ) );
		}

		try {

			// Create or save template.
			if ( $template_id === '0' ) {
				$new_template = fg_pdfs_api()->create_template( $name, $file );
			} else {
				$new_template = fg_pdfs_api()->save_template( $template_id, $name, $file );
			}

			// If template IDs do not match, redirect.
			if ( $template_id !== $new_template['template_id'] ) {
				wp_safe_redirect( add_query_arg( [ 'id' => $new_template['template_id'] ] ) );
			}

		} catch ( Exception $e ) {

			GFCommon::add_error_message( $e->getMessage() );

		}

	}

	/**
	 * Get settings fields for creating or editing a template.
	 *
	 * @since 2.3
	 *
	 * @return array
	 */
	protected function settings_fields() {

		require_once( GFCommon::get_base_path() . '/includes/settings/class-fields.php' );

		\Gravity_Forms\Gravity_Forms\Settings\Fields::register( 'fg_fillablepdfs_template_file', '\ForGravity\Fillable_PDFs\Templates\Fields\Template_File' );

		// Get current template.
		$template = $this->get_current_template();

		// Prepare save field.
		if ( $template ) {
			$save_button = [
				'type'     => 'save',
				'value'    => esc_html__( 'Save Template', 'forgravity_fillablepdfs' ),
				'messages' => [
					'success' => esc_html__( 'Template has been saved.', 'forgravity_fillablepdfs' ),
					'error'   => esc_html__( 'Template could not be saved. Please review the errors below and try again.', 'forgravity_fillablepdfs' ),
				],
			];
		} else {
			$save_button = [
				'type'     => 'save',
				'value'    => esc_html__( 'Create Template', 'forgravity_fillablepdfs' ),
				'messages' => [
					'success' => esc_html__( 'Template has been created.', 'forgravity_fillablepdfs' ),
					'error'   => esc_html__( 'Template could not be created. Please review the errors below and try again.', 'forgravity_fillablepdfs' ),
				],
			];
		}

		// Prepare settings fields.
		$fields = [
			[
				'title'  => $template ? esc_html__( 'Update Template', 'forgravity_fillablepdfs' ) : esc_html__( 'Add Template', 'forgravity_fillablepdfs' ),
				'fields' => [
					[
						'name' => 'template_id',
						'type' => 'hidden',
					],
					[
						'name'                => 'name',
						'type'                => 'text',
						'label'               => esc_html__( 'Template Name', 'forgravity_fillablepdfs' ),
						'class'               => 'medium',
						'required'            => true,
						'validation_callback' => [ $this, 'validate_template_name' ],
					],
					[
						'name'     => 'pdf_name',
						'type'     => 'fg_fillablepdfs_template_file',
						'label'    => $template ? esc_html__( 'Template File', 'forgravity_fillablepdfs' ) : esc_html__( 'Select a Template File', 'forgravity_fillablepdfs' ),
						'required' => true,
					],
					$save_button,
				],
			],
		];

		return $fields;

	}

	/**
	 * Validate a template name field.
	 *
	 * @since 2.4
	 *
	 * @param \Gravity_Forms\Gravity_Forms\Settings\Fields\Base $field Field object.
	 * @param string                                            $value Posted value.
	 */
	public function validate_template_name( $field, $value = '' ) {

		// Get template.
		$template = $this->get_current_template();

		// If template name is empty, set error.
		if ( rgblank( $value ) ) {
			$field->set_error( esc_html__( 'Template name must not be blank.', 'forgravity_fillablepdfs' ) );
			return;
		}

		try {

			// Get existing templates names.
			$existing_templates = fg_pdfs_api()->get_templates();

		} catch ( Exception $e ) {

			// Log that files could not be retrieved.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Could not retrieve existing templates; ' . $e->getMessage() );

			$field->set_error( esc_html__( 'Unable to check if template name is unique.', 'forgravity_fillablepdfs' ) );

			return;

		}

		// Get existing template names.
		$template_names = [];
		foreach ( $existing_templates as $existing_template ) {
			$template_names[ $existing_template['template_id'] ] = $existing_template['name'];
		}

		// Found template ID.
		$found_template_id = array_search( $value, $template_names );

		// If the file has no fields, set error.
		if ( $found_template_id !== false && $found_template_id !== rgar( $template, 'template_id' )  ) {
			$field->set_error( esc_html__( 'Template name must be unique.', 'forgravity_fillablepdfs' ) );
			return;
		}

	}





	// # DELETE TEMPLATE -----------------------------------------------------------------------------------------------

	/**
	 * Delete templates.
	 *
	 * @since  2.0
	 */
	public function maybe_delete_template() {

		// Get template IDs to be deleted.
		$template_ids = rgpost( 'template_ids' ) ? rgpost( 'template_ids' ) : [ rgget( 'id' ) ];

		// Sanitize template IDs.
		$template_ids = array_map( 'sanitize_text_field', $template_ids );

		// Initialize API.
		if ( ! fg_pdfs_api() ) {

			// Display error message.
			GFCommon::add_error_message( esc_html__( 'Unable to initialize API.', 'forgravity_fillablepdfs' ) );

			return;

		}

		// Loop through template IDs and delete.
		foreach ( $template_ids as $template_id ) {

			try {

				// Log that template is about to be deleted.
				fg_fillablepdfs()->log_debug( __METHOD__ . '(): Deleting template "' . $template_id . '".' );

				// Delete template.
				$deleted = fg_pdfs_api()->delete_template( $template_id );

			} catch ( Exception $e ) {

				// Log that template could not be deleted.
				fg_fillablepdfs()->log_error( __METHOD__ . '(): Template could not be deleted; ' . $e->getMessage() );

				// Add error message.
				GFCommon::add_error_message( esc_html__( 'Template could not be deleted.', 'forgravity_fillablepdfs' ) );

				return;

			}

			// Display error message if template could not be deleted.
			if ( ! $deleted ) {

				// Display error message.
				GFCommon::add_error_message( esc_html__( 'Template could not be deleted.', 'forgravity_fillablepdfs' ) );

				return;

			}

		}

		// Display success message.
		GFCommon::add_message( esc_html__( 'Template(s) were successfully deleted.', 'forgravity_fillablepdfs' ) );

		return;

	}





	// # DOWNLOAD TEMPLATE ---------------------------------------------------------------------------------------------

	/**
	 * Retrieve and serve original template file.
	 *
	 * @since 2.3
	 */
	public static function maybe_download_template() {

		// If this is not the templates page, exit.
		if ( rgget( 'page' ) !== fg_fillablepdfs()->get_slug() && rgget( 'subview' ) !== 'templates' ) {
			return;
		}

		// If this is not a download request, exit.
		if ( rgget( 'action' ) !== 'download' || rgempty( 'id', $_GET ) ) {
			return;
		}

		// If user does not have Fillable PDFs capabilities, exit.
		if ( ! GFCommon::current_user_can_any( fg_fillablepdfs()->get_capabilities( 'settings_page' ) ) ) {
			wp_die( esc_html__( 'You do not have permission to download this template.', 'forgravity_fillablepdfs' ) );
		}

		// If API cannot be initialized, exit.
		if ( ! fg_pdfs_api() ) {
			wp_die( esc_html__( 'Unable to connect to Fillable PDFs API.', 'forgravity_fillablepdfs' ) );
		}

		// Get template ID.
		$template_id = rgget( 'id' );

		try {

			// Get template.
			$template = fg_pdfs_api()->get_template( $template_id );

		} catch ( Exception $e ) {

			wp_die( sprintf( esc_html__( 'Unable to get template: %s', 'forgravity_fillablepdfs' ), $e->getMessage() ) );

		}

		try {

			// Get template file.
			$file = fg_pdfs_api()->get_template_file( $template['template_id'] );

		} catch ( Exception $e ) {

			wp_die( sprintf( esc_html__( 'Unable to get template file: %s', 'forgravity_fillablepdfs' ), $e->getMessage() ) );

		}

		// If response is JSON, display error.
		if ( is_array( $file ) ) {
			wp_die( sprintf( esc_html__( 'Unable to get template file: %s', 'forgravity_fillablepdfs' ), rgar( $file, 'message' ) ) );
		}

		// Enable output buffering.
		ob_start();

		// Set headers.
		header( 'X-Robots-Tag: noindex, nofollow', true );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Cache-Control: public, must-revalidate, max-age=0' );
		header( 'Pragma: public' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Content-Type: application/force-download' );
		header( 'Content-Type: application/octet-stream', false );
		header( 'Content-Type: application/download', false );
		header( 'Content-Type: application/pdf', false );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $template['pdf_name'] ) . '"' );

		// Flush output buffer.
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		// Server PDF.
		echo $file;
		die();

	}





	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * Get the template object currently being edited.
	 *
	 * @since 2.3
	 *
	 * @return array|bool
	 */
	public function get_current_template() {

		static $current_template;

		// If the current template is defined, return.
		if ( ! empty( $current_template ) ) {
			return $current_template;
		}

		// Get the current template ID.
		$template_id = fg_fillablepdfs()->is_postback() ? rgpost( '_gform_setting_template_id' ) : rgget( 'id' );

		// If no ID is provided, return.
		if ( ! $template_id || '0' === $template_id ) {
			$current_template = [];
			return $current_template;
		}

 		try {

			// Get template.
			$template = fg_pdfs_api()->get_template( $template_id );

		} catch ( Exception $e ) {

			// Log error.
			fg_fillablepdfs()->log_error( __METHOD__ . '(): Unable to get template; ' . $e->getMessage() );

			// Set template to false.
			$template = false;

		}

		// Assign template object to instance.
		$current_template = $template;

		return $current_template;

	}

	/**
	 * Get templates list table object.
	 *
	 * @since  1.0
	 *
	 * @return Templates\Table
	 */
	public function get_table() {

		return new Templates\Table();

	}

	/**
	 * Determines if page is processing a template deletion action.
	 *
	 * @since 2.4
	 *
	 * @return bool
	 */
	private static function do_delete() {

		return rgpost( 'action' ) === 'delete' || rgpost( 'action2' ) === 'delete' || ( isset( $_GET['id'] ) && rgget( 'action' ) === 'delete' );

	}

}

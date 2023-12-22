<?php
/**
 * Integrates Fillable PDFs with GravityView.
 *
 * @since     2.3
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2020, ForGravity
 */

namespace ForGravity\Fillable_PDFs\Integrations\GravityView;

use GravityView_Field;

class_exists( 'GFForms' ) || die();

class Field_Link extends GravityView_Field {

	/**
	 * The name of the GravityView field type.
	 *
	 * @since 2.3
	 *
	 * @var string
	 */
	public $name = 'fillablepdfs_link';

	/**
	 * The contexts in which a field is available.
	 *
	 * @since 2.3
	 *
	 * @var array
	 */
	public $contexts = [ 'single', 'multiple' ];

	/**
	 * Determines if field is sortable.
	 *
	 * @since 2.3
	 *
	 * @var bool
	 */
	public $is_sortable = false;

	/**
	 * Determines if field is searchable.
	 *
	 * @since 2.3
	 *
	 * @var bool
	 */
	public $is_searchable = false;

	/**
	 * The group this field belongs to.
	 *
	 * @since 2.3
	 *
	 * @var string
	 */
	public $group = 'meta';

	/**
	 * Link_Field constructor.
	 *
	 * @since 2.3
	 */
	public function __construct() {

		$this->label = esc_html__( 'Link to Generated PDF', 'forgravity_fillablepdfs' );

		$this->add_hooks();

		parent::__construct();

	}

	/**
	 * Add hooks to register settings, display link.
	 *
	 * @since 2.5
	 */
	private function add_hooks() {

		add_filter( 'gravityview_entry_default_fields', [ $this, 'filter_gravityview_entry_default_fields' ], 10, 3 );
		add_filter( 'gravityview_field_entry_value_' . $this->name, [ $this, 'filter_gravityview_field_entry_value' ], 10, 4 );

	}






	// # SETTINGS METHODS ----------------------------------------------------------------------------------------------

	/**
	 * Add Fillable PDFs field.
	 *
	 * @since 2.3
	 *
	 * @param array        $fields Array of fields shown by default
	 * @param string|array $form   form_ID or form object
	 * @param string       $zone   Either 'single', 'directory', 'header', 'footer'
	 *
	 * @return array
	 */
	public function filter_gravityview_entry_default_fields( $fields, $form, $zone ) {

		if ( ! in_array( $zone, [ 'directory', 'single' ] ) ) {
			return $fields;
		}

		$fields[ $this->name ] = [
			'label' => __( 'Generated PDF Link', 'forgravity_fillablepdfs' ),
			'type'  => $this->name,
			'desc'  => __( 'Display a link to the generated Fillable PDF.', 'forgravity_fillablepdfs' ),
		];

		return $fields;

	}

	/**
	 * Adds the link text field option.
	 *
	 * @since 2.3
	 *
	 * @param array  $field_options The field properties.
	 * @param string $template_id   The template ID.
	 * @param string $field_id      The field ID.
	 * @param string $context       The current context.
	 * @param string $input_type    The field input type.
	 * @param int    $form_id       The form ID.
	 *
	 * @return array
	 */
	public function field_options( $field_options, $template_id, $field_id, $context, $input_type, $form_id ) {

		// Remove unneeded options.
		unset( $field_options['show_as_link'], $field_options['search_filter'] );

		// If in the Edit context, return.
		if ( 'edit' === $context ) {
			return $field_options;
		}

		// Initialize array for field settings.
		$settings = [
			'fillablepdfs_link_feed' => [
				'type'    => 'select',
				'label'   => __( 'Fillable PDFs Feed:', 'forgravity_fillablepdfs' ),
				'desc'    => __( 'Select which feed to get the generated PDF from.', 'forgravity_fillablepdfs' ),
				'choices' => $this->get_feeds_as_options( $form_id ),
			],
			'fillablepdfs_link_text' => [
				'type'  => 'text',
				'label' => __( 'Link Text:', 'forgravity_fillablepdfs' ),
				'desc'  => __( 'Override the PDF link text. Defaults to file name.', 'forgravity_fillablepdfs' ),
			],
			'fillablepdfs_link_signed' => [
				'type'  => 'checkbox',
				'label' => __( 'Sign URL', 'forgravity_fillablepdfs' ),
				'desc'  => __( 'This will add a secure token to the URL, allowing anyone to view it.', 'forgravity_fillablepdfs' ),
			],
		];

		return array_merge( $settings, $field_options );

	}






	// # DISPLAY METHODS -----------------------------------------------------------------------------------------------

	/**
	 * Modify the field output.
	 *
	 * @since 2.3
	 *
	 * @param string $output         HTML value output
	 * @param array  $entry          The GF entry array
	 * @param array  $field_settings Settings for the particular GV field
	 * @param array  $field          Current field being displayed
	 *
	 * @return string
	 */
	public function filter_gravityview_field_entry_value( $output, $entry, $field_settings, $field ) {

		// If no feed was selected, return.
		if ( rgempty( 'fillablepdfs_link_feed', $field_settings ) ) {
			return '';
		}

		// Get PDFs for entry.
		$entry_pdfs = fg_fillablepdfs()->get_entry_pdfs( $entry );
		$pdf_meta   = [];

		// Loop through PDFs, look for PDF for selected feed.
		foreach ( $entry_pdfs as $entry_pdf ) {
			if ( (int) rgar( $entry_pdf, 'feed_id' ) === (int) rgar( $field_settings, 'fillablepdfs_link_feed' ) ) {
				$pdf_meta = $entry_pdf;
			}
		}

		// If no PDF was found for selected feed, return.
		if ( empty( $pdf_meta ) ) {
			return '';
		}

		// Get URL for PDF.
		$pdf_url = fg_fillablepdfs()->build_pdf_url( $pdf_meta, (bool) rgar( $field_settings, 'fillablepdfs_link_signed', false ) );

		// Prepare link.
		return sprintf(
			'<a href="%s">%s</a>',
			esc_url( $pdf_url ),
			! rgempty( 'fillablepdfs_link_text', $field_settings ) ? esc_html( $field_settings['fillablepdfs_link_text'] ) : esc_html( $pdf_meta['file_name'] )
		);

	}






	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Get form feeds as GravityView settings options.
	 *
	 * @since 2.3
	 *
	 * @param int $form_id The Form ID.
	 *
	 * @return array
	 */
	private function get_feeds_as_options( $form_id ) {

		$return = [
			'' => __( 'Select a Feed', 'forgravity_fillablepdfs' ),
		];

		// Get feeds.
		$feeds = fg_fillablepdfs()->get_feeds( $form_id );

		// Loop through feeds, add as options.
		foreach ( $feeds as $feed ) {
			$return[ $feed['id'] ] = rgars( $feed, 'meta/feedName' );
		}

		return $return;

	}

}

new Field_Link();

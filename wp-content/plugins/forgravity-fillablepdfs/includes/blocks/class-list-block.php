<?php

namespace ForGravity\Fillable_PDFs\Blocks;

use GFAPI;
use GF_Block;
use GF_Blocks;
use GFCommon;

// If Gravity Forms Block Manager is not available, do not run.
if ( ! class_exists( '\GF_Blocks' ) || ! function_exists( 'register_block_type' ) || ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fillable PDFs List Block Class.
 *
 * @since     2.3
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2019, ForGravity
 */
class List_Block extends GF_Block {

	/**
	 * Contains an instance of this block, if available.
	 *
	 * @since  2.3
	 * @var    GF_Block $_instance If available, contains an instance of this block.
	 */
	private static $_instance = null;

	/**
	 * Block type.
	 *
	 * @since 2.3
	 * @var   string
	 */
	public $type = 'forgravity/fillablepdfs-list';

	/**
	 * Handle of primary block script.
	 *
	 * @since 2.3
	 * @var   string
	 */
	public $script_handle = 'fg_fillablepdfs_block_list';

	/**
	 * Block attributes.
	 *
	 * @since 2.4.10
	 * @var   array
	 */
	public $attributes = [
		'forms'         => [ 'type' => 'array' ],
		'format'        => [ 'type' => 'string' ],
		'columns'       => [ 'type' => 'object' ],
		'columnNames'   => [ 'type' => 'object' ],
		'order'         => [ 'type' => 'string' ],
		'orderby'       => [ 'type' => 'string' ],
		'dateFormat'    => [ 'type' => 'string' ],
		'downloadLabel' => [ 'type' => 'string' ],
	];

	/**
	 * Get instance of this class.
	 *
	 * @since  2.4.10
	 *
	 * @return List_Block
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;

	}





	// # SCRIPT / STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Register scripts for block.
	 *
	 * @since  2.3
	 *
	 * @return array
	 */
	public function scripts() {

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		return [
			[
				'handle'    => $this->script_handle,
				'in_footer' => true,
				'src'       => fg_fillablepdfs()->get_asset_url( "/dist/js/blocks/list{$min}.js" ),
				'deps'      => [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-editor' ],
				'version'   => $min ? fg_fillablepdfs()->get_version() : fg_fillablepdfs()->get_asset_filemtime( "/dist/js/blocks/list{$min}.js" ),
				'callback'  => [ $this, 'localize_script' ],
			],
		];

	}

	/**
	 * Localize Form block script.
	 *
	 * @since  2.4.10
	 *
	 * @param array $script Script arguments.
	 */
	public function localize_script( $script = [] ) {

		// Get date format.
		$date_format = get_option( 'date_format' );

		wp_localize_script(
			$script['handle'],
			$script['handle'],
			[
				'forms' => $this->get_forms(),
				'sampleData' => [
					[
						'fileName' => 'Sample File.pdf',
						'fileSize' => '30 KB',
						'date'     => date( $date_format, rand( strtotime( '-6 months' ), time() ) ),
					],
					[
						'fileName' => 'Sample File.pdf',
						'fileSize' => '30 KB',
						'date'     => date( $date_format, rand( strtotime( '-6 months' ), time() ) ),
					],
					[
						'fileName' => 'Sample File.pdf',
						'fileSize' => '30 KB',
						'date'     => date( $date_format, rand( strtotime( '-6 months' ), time() ) ),
					],
				]
			]
		);

	}





	// # BLOCK RENDER -------------------------------------------------------------------------------------------------

	/**
	 * Display block contents on frontend.
	 *
	 * @since  2.3
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string
	 */
	public function render_block( $attributes = [] ) {

		// Get entries for block.
		$entries = self::get_entries( $attributes );

		// If no entries were found, exit.
		if ( empty( $entries ) || is_wp_error( $entries ) ) {
			return '';
		}

		// Initialize files.
		$files = [];

		// Get PDF file meta for entries.
		foreach ( $entries as $entry ) {
			$files = array_merge( $files, fg_fillablepdfs()->get_entry_pdfs( $entry ) );
		}

		if ( empty( $files ) ) {
			return '';
		}

		// Sort PDF file meta.
		$files = self::sort_file_meta( $files, rgar( $attributes, 'orderby' ), rgar( $attributes, 'order' ) );

		// Prepare markup.
		$html = sprintf(
			'<table>%s<tbody>%s</tbody></table>',
			self::get_table_header( $attributes ),
			implode( '', array_map( function( $file_meta ) use ( $attributes ) { return self::get_table_row( $file_meta, $attributes ); }, $files ))
		);

		return $html;

	}

	/**
	 * Returns an array of enabled columns for block.
	 *
	 * @since 2.3
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return array
	 */
	private static function get_columns( $attributes = [] ) {

		return [
			'fileName' => isset( $attributes['columns']['fileName'] ) ? (bool) $attributes['columns']['fileName'] : true,
			'fileSize' => isset( $attributes['columns']['fileSize'] ) ? (bool) $attributes['columns']['fileSize'] : true,
			'date'     => isset( $attributes['columns']['date'] ) ? (bool) $attributes['columns']['date'] : true,
			'download' => isset( $attributes['columns']['download'] ) ? (bool) $attributes['columns']['download'] : true,
		];

	}

	/**
	 * Returns a table header based on block attributes.
	 *
	 * @since 2.3
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string
	 */
	private static function get_table_header( $attributes = [] ) {

		// Prepare columns.
		$columns = self::get_columns( $attributes );

		// Prepare column names.
		$column_names = [
			'fileName' => isset( $attributes['columnNames']['fileName'] ) ? esc_html( $attributes['columnNames']['fileName'] ) : esc_html__( 'File Name', 'forgravity_fillablepdfs' ),
			'fileSize' => isset( $attributes['columnNames']['fileSize'] ) ? esc_html( $attributes['columnNames']['fileSize'] ) : esc_html__( 'File Size', 'forgravity_fillablepdfs' ),
			'date'     => isset( $attributes['columnNames']['date'] ) ? esc_html( $attributes['columnNames']['date'] ) : esc_html__( 'Date', 'forgravity_fillablepdfs' ),
			'download' => isset( $attributes['columnNames']['download'] ) ? esc_html( $attributes['columnNames']['download'] ) : esc_html__( 'Download', 'forgravity_fillablepdfs' ),
		];

		return sprintf(
			'<thead><tr>%s%s%s%s</tr></thead>',
			$columns['fileName'] ? sprintf( '<th>%s</th>', $column_names['fileName'] ) : '',
			$columns['fileSize'] ? sprintf( '<th>%s</th>', $column_names['fileSize'] ) : '',
			$columns['date'] ? sprintf( '<th>%s</th>', $column_names['date'] ) : '',
			$columns['download'] ? sprintf( '<th>%s</th>', $column_names['download'] ) : ''
		);

	}

	/**
	 * Returns a table row based on PDF file meta and block attributes
	 *
	 * @since 2.3
	 *
	 * @param array $file_meta  PDF file meta.
	 * @param array $attributes Block attributes.
	 *
	 * @return string
	 */
	private static function get_table_row( $file_meta = [], $attributes = [] ) {

		// Get file path.
		$entry     = GFAPI::get_entry( $file_meta['entry_id'] );
		$file_path = fg_fillablepdfs()->get_physical_file_path( $file_meta );

		// If file does not exist, return.
		if ( ! file_exists( $file_path ) ) {
			return '';
		}

		// Get date format, columns, file URL.
		$date_format = rgar( $attributes, 'dateFormat' ) ? $attributes['dateFormat'] : sprintf( '%s %s', get_site_option( 'date_format' ), get_site_option( 'time_format' ) );
		$columns     = self::get_columns( $attributes );
		$file_url    = fg_fillablepdfs()->build_pdf_url( $file_meta );

		// Prepare file name row.
		$file_name = $columns['download'] ? esc_html( rgar( $file_meta, 'file_name' ) ) : sprintf( '<a href="%s">%s</a>', esc_attr( $file_url ), esc_html( rgar( $file_meta, 'file_name' ) ) );

		// Convert date.
		$file_meta['date_created'] = date( 'Y-m-d H:i:s', $file_meta['date_created'] );
		$file_meta['date_created'] = GFCommon::format_date( $file_meta['date_created'], false, $date_format, false );

		return sprintf(
			'<tr>%s%s%s%s</tr>',
			$columns['fileName'] ? sprintf( '<td>%s</td>', $file_name ) : '',
			$columns['fileSize'] ? sprintf( '<td>%s</td>', size_format( filesize( $file_path ) ) ) : '',
			$columns['date'] ? sprintf( '<td>%s</td>', $file_meta['date_created'] ) : '',
			$columns['download'] ? sprintf( '<td><a href="%s">%s</a></td>', esc_attr( $file_url ), esc_html__( 'Download', 'forgravity_fillablepdfs' ) ) : ''
		);

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Returns entries match block attributes for logged in user.
	 *
	 * @since 2.3
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return array|\WP_Error
	 */
	private static function get_entries( $attributes = [] ) {

		// If user is not logged in, return.
		if ( ! is_user_logged_in() ) {
			return [];
		}

		// Get form IDs.
		$form_ids = rgar( $attributes, 'forms' ) ? (array) $attributes['forms'] : null;

		// Get entries.
		return GFAPI::get_entries(
			$form_ids,
			[
				'field_filters' => [
					[
						'key'   => 'created_by',
						'value' => get_current_user_id(),
					],
					[
						'key'      => 'fillablepdfs',
						'operator' => 'isnot',
						'value'    => null,
					],
				],
			]
		);


	}

	/**
	 * Get list of forms for Block control.
	 *
	 * @since 2.3
	 *
	 * @return array
	 */
	private function get_forms() {

		// Get feeds, form IDs.
		$feeds    = fg_fillablepdfs()->get_feeds();
		$form_ids = $feeds ? array_unique( wp_list_pluck( $feeds, 'form_id' ) ) : null;

		// Get forms.
		if ( is_null( $form_ids ) ) {
			$forms = GFAPI::get_forms();
		} else {
			$forms = [];
			foreach ( $form_ids as $form_id ) {
				$forms[] = GFAPI::get_form( $form_id );
			}
		}

		// Simplify form objects.
		$forms = array_map( function( $form ) { return [ 'id' => $form['id'], 'title' => $form['title'] ]; }, $forms );

		return $forms;

	}

	/**
	 * Sort collection of PDF file meta.
	 *
	 * @since 2.3
	 *
	 * @param array  $pdf_meta Collection of PDF file meta.
	 * @param string $orderby Column to order meta by.
	 * @param string $order Direction to order meta.
	 *
	 * @return array
	 */
	private static function sort_file_meta( $pdf_meta, $orderby = 'fileName', $order = 'DESC' ) {

		// Sanitize order by, order parameters.
		$orderby = GFCommon::whitelist( $orderby, [ 'fileName', 'date' ] );
		$order   = GFCommon::whitelist( $order, [ 'ASC', 'DESC' ] );

		switch ( $orderby ) {

			case 'date':
				usort(
					$pdf_meta,
					function( $a, $b ) {
						return $a['date_created'] === $b['date_created'] ? 0 : ( $a['date_created'] < $b['date_created'] ? -1 : 1 );
					}
				);

				break;

			case 'fileName':
				usort(
					$pdf_meta,
					function( $a, $b ) {
						return strcmp( $a['file_name'], $b['file_name'] );
					}
				);
				break;

		}

		if ( $order === 'DESC' ) {
			$pdf_meta = array_reverse( $pdf_meta );
		}

		return $pdf_meta;

	}

}

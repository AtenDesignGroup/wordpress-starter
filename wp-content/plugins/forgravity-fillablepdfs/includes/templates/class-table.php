<?php

namespace ForGravity\Fillable_PDFs\Templates;

use Exception;
use ForGravity\Fillable_PDFs\Templates;
use WP_List_Table;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

defined( 'ABSPATH' ) || die();

/**
 * Templates table for plugin page.
 *
 * @extends WP_List_Table
 */
class Table extends WP_List_Table {

	/**
	 * Get table columns.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function get_columns() {

		return [
			'cb'       => esc_html__( 'Checkbox', 'forgravity_fillablepdfs' ),
			'name'     => esc_html__( 'Name', 'forgravity_fillablepdfs' ),
			'pdf_name' => esc_html__( 'File Name', 'forgravity_fillablepdfs' ),
			'site_url' => esc_html__( 'Created On', 'forgravity_fillablepdfs' ),
		];

	}

	/**
	 * Get sortable table columns.
	 *
	 * @since  2.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {

		return [
			'name'     => [ 'name', true ],
			'pdf_name' => [ 'pdf_name', false ],
		];

	}

	/**
	 * Get bulk template actions.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		return [
			'delete' => esc_html__( 'Delete', 'forgravity_fillablepdfs' ),
		];

	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 2.0
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {

		return [ 'widefat', 'fixed', 'striped', 'fillablepdfs__templates' ];

	}

	/**
	 * Prepare templates for table.
	 *
	 * @since  1.0
	 */
	public function prepare_items() {

		// If API is not initialize, do not set items.
		if ( ! fg_pdfs_api() ) {
			return;
		}

		// Get columns, hidden columns and sortable columns.
		$columns  = $this->get_columns();
		$hidden   = [];
		$sortable = $this->get_sortable_columns();

		// Set column headers.
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$page        = rgget( 'paged' ) ? (int) $_GET['paged'] : 1;
		$per_page    = $this->get_items_per_page( Templates::$per_page_option, 20 );
		$total_items = 0;

		try {

			// Set table items to site templates.
			$this->items = fg_pdfs_api()->get_templates( $page, $per_page );

			$total_items = fg_pdfs_api()->get_template_count();
			$total_items = rgar( $total_items, 'count', 0 );

		} catch ( Exception $e ) {

			// Store error message.
			$this->errorMessage = $e->getMessage();

		}

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		// Sort templates.
		switch ( rgget( 'orderby' ) ) {

			case 'name':

				// Sort templates by name alphabetically.
				usort( $this->items, function( $a, $b ) { return strcasecmp( $a['name'], $b['name'] ); } );

				// Reverse sort.
				if ( 'desc' === rgget( 'order' ) ) {
					$this->items = array_reverse( $this->items );
				}

				break;

			case 'pdf_name':

				// Sort templates by template name alphabetically.
				usort( $this->items, function( $a, $b ) { return strcasecmp( $a['pdf_name'], $b['pdf_name'] ); } );

				// Reverse sort.
				if ( 'desc' === rgget( 'order' ) ) {
					$this->items = array_reverse( $this->items );
				}

				break;

			default:
				break;

		}


	}

	/**
	 * Display default column content.
	 *
	 * @since  1.0
	 *
	 * @param array  $template Template for current row.
	 * @param string $column   Column name being displayed.
	 *
	 * @return string
	 */
	public function column_default( $template, $column ) {

		// Return column content based on column name.
		switch ( $column ) {

			case 'site_url':
				$url = parse_url( rgar( $template, 'site_url' ) );
				return rgar( $url, 'host', null );

			default:
				return esc_html( rgar( $template, $column ) );

		}

	}

	/**
	 * Display checkbox column.
	 *
	 * @since  1.0
	 *
	 * @param array $template Template for current row.
	 *
	 * @return string
	 */
	public function column_cb( $template ) {

		return sprintf( '<input type="checkbox" name="template_ids[]" value="%s" />', esc_attr( $template['template_id'] ) );

	}

	/**
	 * Display name column with action links.
	 *
	 * @since  1.0
	 *
	 * @param array $template Template for current row.
	 *
	 * @return string
	 */
	public function column_name( $template ) {

		// Initialize actions array.
		$actions = [
			'edit'   => sprintf(
				'<a href="%s">%s</a>',
				esc_attr( add_query_arg( [ 'id' => $template['template_id'], 'action' => null ] ) ),
				esc_html__( 'Edit', 'forgravity_fillablepdfs' )
			),
			'delete' => sprintf(
				'<a href="%s">%s</a>',
				esc_attr( add_query_arg( [ 'id' => $template['template_id'], 'action' => 'delete' ] ) ),
				esc_html__( 'Delete', 'forgravity_fillablepdfs' )
			),
			'download' => sprintf(
				'<a href="%s">%s</a>',
				esc_attr( add_query_arg( [ 'id' => $template['template_id'], 'action' => 'download' ] ) ),
				esc_html__( 'Download', 'forgravity_fillablepdfs' )
			)
		];

		return sprintf( '%1$s %2$s', $template['name'], $this->row_actions( $actions ) );

	}

	/**
	 * Display message when no templates exist.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function no_items() {

		// If an error message is set, return it.
		if ( isset( $this->errorMessage ) ) {
			printf(
				'%s: %s',
				esc_html__( 'Unable to get templates', 'forgravity_fillablepdfs' ),
				esc_html( $this->errorMessage )
			);
		}

		// Prepare URL.
		$url = add_query_arg( [ 'id' => 0 ] );
		$url = remove_query_arg( 'action', $url );

		printf(
			esc_html__( 'To get started, %sadd a new template.%s', 'forgravity_fillablepdfs' ),
			'<a href="' . esc_url( $url ) . '">',
			'</a>'
		);

	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * @since 2.4
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {

		if ( $which !== 'top' || ! fg_fillablepdfs()->is_gravityforms_supported( '2.5-dev-1' ) ) {
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

		// If user cannot create templates, exit.
		if ( ! rgars( $license, 'supports/create_templates' ) ) {
			return;
		}

		printf(
			'<div class="alignright"><a href="%s" class="button">%s</a></div>',
			esc_url( add_query_arg( [ 'id' => 0, 'action' => null ] ) ),
			esc_html__( 'Add New', 'forgravity_fillablepdfs' )
		);

	}

	/**
	 * Remove pagination from top of table.
	 *
	 * @since 3.0
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {

		if ( $which === 'top' && fg_fillablepdfs()->is_gravityforms_supported( '2.5-dev-1' ) ) {
			return;
		}

		parent::pagination( $which );

	}

}

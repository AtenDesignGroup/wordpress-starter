<?php
/**
 * Entry Detail metabox displaying the generated PDFs.
 *
 * @since 1.0
 *
 * @package ForGravity\Legal_Signing
 */

namespace ForGravity\Fillable_PDFs\Metaboxes;

/**
 * Entry Detail metabox displaying the generated PDFs.
 *
 * @since 3.4
 *
 * @package ForGravity\Fillable_PDFs
 */
class Documents {

	/**
	 * Metabox element ID.
	 *
	 * @since 3.4
	 *
	 * @var string
	 */
	public static $id = 'fillablepdfs-metabox__documents';

	/**
	 * Metabox context.
	 *
	 * @since 3.4
	 *
	 * @var string
	 */
	public static $context = 'normal';

	/**
	 * Renders a Generated PDFs metabox.
	 *
	 * @since 3.4
	 *
	 * @param array $args Metabox arguments.
	 */
	public static function render( $args = [] ) {

		/**
		 * Modify the PDFs displayed in the Generated PDFs metabox.
		 *
		 * @since 3.3
		 *
		 * @param array $entry_pdfs PDFs generated for Entry.
		 * @param array $entry      Entry object.
		 * @param array $form       Form object.
		 */
		$entry_pdfs = fg_pdfs_apply_filters( 'metabox_generated_pdfs', fg_fillablepdfs()->get_entry_pdfs( $args['entry'] ), $args['entry'], $args['form'] );

		if ( empty( $entry_pdfs ) ) {
			static::render_empty_metabox( $args );
			return;
		}

		echo '<table class="fillablepdfs-metabox__documents-table">';

		// Loop through PDF IDs.
		foreach ( $entry_pdfs as $pdf ) {

			$pdf_url = fg_fillablepdfs()->build_pdf_url( $pdf );

			printf(
				'<tr>
					<td>
						<a href="%3$s" class="fillablepdfs-metabox__documents-file-name">%1$s</a>
					</td>
					<td>
						<a href="%4$s" class="fillablepdfs-metabox__documents-button">%5$s</a>
						<a href="%3$s" class="fillablepdfs-metabox__documents-button">%6$s</a>
						<button type="button" class="fillablepdfs-metabox__documents-delete" data-pdf-id="%2$s" data-nonce="%8$s">
							<img src="%9$s" width="16" height="16" alt="%7%s" />
							<span class="screen-reader-text">%7$s</span>
						</button>
					</td>
				</tr>',
				esc_html( $pdf['file_name'] ),
				esc_attr( $pdf['pdf_id'] ),
				esc_url( $pdf_url ),
				esc_url( add_query_arg( [ 'dl' => 0 ], $pdf_url ) ),
				esc_html__( 'View', 'forgravity_fillablepdfs' ),
				esc_html__( 'Download', 'forgravity_fillablepdfs' ),
				esc_html__( 'Delete', 'forgravity_fillablepdfs' ),
				esc_attr( wp_create_nonce( fg_fillablepdfs()->get_slug() . '_metabox_delete' ) ),
				esc_url( fg_fillablepdfs()->get_asset_url( '/dist/images/metabox/delete.svg' ) )
			);

		}

		echo '</table>';

		// Prepare regenerate PDFs URL.
		$url = add_query_arg(
			[
				fg_fillablepdfs()->get_slug() => 'regenerate',
				'lid'                         => $args['entry']['id'],
			]
		);

		// Display button.
		if ( static::can_regenerate_documents( $entry_pdfs, $args['entry'], $args['form'] ) ) {
			printf(
				'<p><a href="%s" class="fillablepdfs-metabox__documents-regenerate button">%s</a></p>',
				esc_url( $url ),
				esc_html__( 'Regenerate all PDFs', 'forgravity_fillablepdfs' )
			);
		}

	}

	/**
	 * Renders a No Documents Found metabox.
	 *
	 * @since 3.4
	 *
	 * @param array $args Metabox arguments.
	 */
	private static function render_empty_metabox( $args = [] ) {

		$active_feeds = static::get_feeds( $args['entry'], $args['form'] );

		if ( empty( $active_feeds ) ) {

			// Prepare regenerate PDFs URL.
			$url = admin_url( 'admin.php?page=gf_edit_forms&view=settings&id=' . $args['form']['id'] . '&subview=' . fg_fillablepdfs()->get_slug() . '&fid=0' );

			// Display button.
			printf(
				'<p class="fillablepdfs-metabox__documents--no-documents">
					%1$s<br />
					<a href="%3$s" class="button">%2$s</a>
				</p>',
				esc_html__( 'You need to setup a feed before you can generate a PDF.', 'forgravity_fillablepdfs' ),
				esc_html__( 'Add New Feed', 'forgravity_fillablepdfs' ),
				esc_url( $url )
			);

			return;

		}

		// Prepare regenerate PDFs URL.
		$url = add_query_arg(
			[
				fg_fillablepdfs()->get_slug() => 'regenerate',
				'lid'                         => $args['entry']['id'],
			]
		);

		// Display button.
		printf(
			'<p class="fillablepdfs-metabox__documents--no-documents">
				%1$s<br />
				<a href="%3$s" class="button">%2$s</a>
			</p>',
			esc_html__( "It doesn't look like any PDFs have been generated yet.", 'forgravity_fillablepdfs' ),
			esc_html__( 'Generate PDFs', 'forgravity_fillablepdfs' ),
			esc_url( $url )
		);

	}

	/**
	 * Determines if documents can be regenerated for an entry.
	 *
	 * @since 3.4
	 *
	 * @param array $documents Documents generated for Entry.
	 * @param array $entry The current Entry object.
	 * @param array $form The current Form object.
	 *
	 * @return bool
	 */
	protected static function can_regenerate_documents( $documents, $entry, $form ) {

		return ! empty( static::get_feeds( $entry, $form ) );

	}

	/**
	 * Returns all the applicable active feeds for a form.
	 *
	 * @since 3.4
	 *
	 * @param array $entry The current Entry object.
	 * @param array $form The current Form object.
	 *
	 * @return array
	 */
	protected static function get_feeds( $entry, $form ) {

		$filtered_feeds = array_filter(
			fg_fillablepdfs()->get_feeds( $form['id'] ),
			function( $feed ) use ( $entry, $form ) {

				if ( ! (bool) rgar( $feed, 'is_active', false ) ) {
					return false;
				}

				return fg_fillablepdfs()->is_feed_condition_met( $feed, $form, $entry );

			}
		);

		$feeds = [];
		foreach ( $filtered_feeds as $feed ) {
			$feeds[ $feed['id'] ] = $feed;
		}

		return $feeds;

	}

	/**
	 * Process delete PDF request from Entry Detail metabox.
	 *
	 * @since 3.4
	 */
	public static function ajax_delete() {

		if ( ! wp_verify_nonce( rgpost( 'nonce' ), fg_fillablepdfs()->get_slug() . '_metabox_delete' ) ) {
			wp_send_json_error( esc_html__( 'Access denied.', 'forgravity_fillablepdfs' ) );
		}

		if ( ! fg_fillablepdfs()->current_user_can_any( fg_fillablepdfs()->get_capabilities( 'view_pdf' ) ) ) {
			wp_send_json_error( esc_html__( 'Access denied.', 'forgravity_fillablepdfs' ) );
		}

		if ( ! fg_fillablepdfs()->delete_pdf( rgpost( 'pdfId' ) ) ) {
			wp_send_json_error( esc_html__( 'Unable to delete PDF.', 'forgravity_fillablepdfs' ) );
		}

		wp_send_json_success();

	}

}

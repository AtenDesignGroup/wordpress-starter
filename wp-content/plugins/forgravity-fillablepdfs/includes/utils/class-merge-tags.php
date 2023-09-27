<?php
/**
 * The Merge Tags class.
 *
 * @since 3.4
 *
 * @package ForGravity\Fillable_PDFs
 */

namespace ForGravity\Fillable_PDFs\Utils;

defined( 'ABSPATH' ) || die();

/**
 * The Merge Tags class.
 *
 * @since 3.4
 *
 * @package ForGravity\Fillable_PDFs
 */
class Merge_Tags {

	const BASE = 'fillable_pdfs';

	/**
	 * Registers needed hooks.
	 *
	 * @since 3.4
	 */
	public function add_hooks() {

		add_filter( 'gform_admin_pre_render', [ $this, 'filter_gform_admin_pre_render' ] );
		add_filter( 'gform_pre_replace_merge_tags', [ $this, 'filter_gform_pre_replace_merge_tags' ], 6, 3 );

	}





	// # REGISTER TAGS -------------------------------------------------------------------------------------------------

	/**
	 * Inject merge tags script on page.
	 *
	 * @since 3.4
	 *
	 * @param array $form The current Form object.
	 *
	 * @return array
	 */
	public function filter_gform_admin_pre_render( $form ) {

		// If the header has already been output, add merge tags script in the footer.
		if ( ! did_action( 'admin_head' ) ) {
			add_action( 'admin_footer', [ $this, 'inject_merge_tags_in_footer' ] );
			return $form;
		}

		printf(
			'<script type="text/javascript">
				( function ( $ ) {
					if ( window.gform ) {
						gform.addFilter( "gform_merge_tags", ( mergeTags ) => {
							mergeTags[ "%1$s" ] = {
								label: "%2$s",
								tags:  %3$s
							};
							return mergeTags;
						} );
					}
				} )( jQuery );
			</script>',
			esc_js( fg_fillablepdfs()->get_slug() ),
			esc_js( fg_fillablepdfs()->get_short_title() ),
			wp_json_encode( $this->get_tags( $form ) )
		);

		return $form;

	}

	/**
	 * Inject merge tags script in admin footer.
	 *
	 * @since  3.4
	 */
	public function inject_merge_tags_in_footer() {

		// Get current form.
		$form = fg_fillablepdfs()->get_current_form();

		// If form was found, include merge tags script.
		if ( $form ) {
			$this->filter_gform_admin_pre_render( $form );
		}

	}

	/**
	 * Returns available merge tags for form.
	 *
	 * @since  3.4
	 *
	 * @param array $form The current Form object.
	 *
	 * @return array
	 */
	protected function get_tags( $form ) {

		// Initialize merge tags array.
		$merge_tags = [];

		// Get feeds for form.
		$feeds = fg_fillablepdfs()->get_active_feeds( $form['id'] );

		// Loop through feeds.
		foreach ( $feeds as $feed ) {
			$merge_tags = array_merge( $merge_tags, $this->get_feed_tags( $feed, $form ) );
		}

		return $merge_tags;

	}

	/**
	 * Returns the available merge tags for a feed.
	 *
	 * @since 3.4
	 *
	 * @param array $feed The current Feed object.
	 * @param array $form The current Form object.
	 *
	 * @return array[
	 *    'tag'   => string,
	 *    'label' => string
	 * ]
	 */
	protected function get_feed_tags( $feed, $form ) {

		return [
			[
				'tag'   => sprintf( '{%s:%s}', static::BASE, $feed['id'] ),
				'label' => esc_html( $feed['meta']['feedName'] ),
			],
			[
				'tag'   => sprintf( '{%s:%s:%s}', static::BASE, $feed['id'], 'name' ),
				'label' => sprintf( '%s (%s)', esc_html( $feed['meta']['feedName'] ), esc_html__( 'File Name', 'forgravity_fillablepdfs' ) ),
			],
			[
				'tag'   => sprintf( '{%s:%s:%s}', static::BASE, $feed['id'], 'url' ),
				'label' => sprintf( '%s (%s)', esc_html( $feed['meta']['feedName'] ), esc_html__( 'File URL', 'forgravity_fillablepdfs' ) ),
			],
		];

	}





	// # REPLACE TAGS --------------------------------------------------------------------------------------------------

	/**
	 * Replace merge tags.
	 *
	 * @since  3.4
	 *
	 * @param string $text  The current text in which merge tags are being replaced.
	 * @param array  $form  The current form.
	 * @param array  $entry The current entry.
	 *
	 * @return string
	 */
	public function filter_gform_pre_replace_merge_tags( $text, $form, $entry ) {

		// If no entry is provided, return.
		if ( ! rgar( $entry, 'id' ) ) {
			return $text;
		}

		// Search for merge tags in text.
		preg_match_all( '/{(' . static::get_regex_base() . '):(\d+):?([^:]*?)?}/mi', $text, $matches, PREG_SET_ORDER );

		// Loop through matches.
		foreach ( $matches as $match ) {

			// Get parts.
			$merge_tag = $match[0];
			$feed_id   = rgar( $match, 2 );
			$modifier  = rgar( $match, 3 );

			$text = str_replace( $merge_tag, $this->replace_merge_tag( $merge_tag, $feed_id, $modifier, $form, $entry ), $text );

		}

		return $text;

	}

	/**
	 * Returns the replacement for a merge tag.
	 *
	 * @since 3.4
	 *
	 * @param string $merge_tag The full merge tag to be replaced.
	 * @param int    $feed_id   The merge tag feed ID.
	 * @param string $modifier  The merge tag modifier.
	 * @param array  $form      The current Form object.
	 * @param array  $entry     The current Entry object.
	 *
	 * @return string
	 */
	protected function replace_merge_tag( $merge_tag, $feed_id, $modifier, $form, $entry ) {

		// Determine if this is the legacy merge tags.
		$is_legacy = strpos( strtolower( $merge_tag ), '{fillable pdfs:' ) !== false;

		// Get PDF meta.
		$pdf_meta = $this->get_pdf_meta_by_feed( $feed_id, $entry );

		// If no PDF exists for this feed, replace with empty string.
		if ( empty( $pdf_meta ) ) {
			return '';
		}

		// Build replacement.
		switch ( $modifier ) {

			case 'name':
				return esc_html( $pdf_meta['file_name'] );

			case 'url':
				return esc_url( fg_fillablepdfs()->build_pdf_url( $pdf_meta, $is_legacy ) );

			case 'url_signed':
				return esc_url( fg_fillablepdfs()->build_pdf_url( $pdf_meta, true ) );

			default:
				return sprintf(
					'<a href="%s">%s</a>',
					esc_url( fg_fillablepdfs()->build_pdf_url( $pdf_meta, $is_legacy ) ),
					esc_html( $pdf_meta['file_name'] )
				);

		}

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Returns the PDF meta based on the feed ID.
	 *
	 * @since 3.4
	 *
	 * @param int   $feed_id The targeted Feed ID.
	 * @param array $entry   The current Entry object.
	 *
	 * @return array
	 */
	private function get_pdf_meta_by_feed( $feed_id, $entry ) {

		$entry_pdfs = fg_fillablepdfs()->get_entry_pdfs( $entry );

		foreach ( $entry_pdfs as $entry_pdf ) {
			if ( $entry_pdf['feed_id'] === $feed_id ) {
				return $entry_pdf;
			}
		}

		return [];

	}

	/**
	 * Returns the base merge tag for regex string.
	 *
	 * @since 3.4
	 *
	 * @return string
	 */
	protected static function get_regex_base() {

		return static::BASE . '|fillable pdfs';

	}

}

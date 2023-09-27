<?php
/**
 * Fillable PDFs API class.
 *
 * @since   4.0
 *
 * @package ForGravity\Fillable_PDFs
 */

use ForGravity\Fillable_PDFs\Fillable_PDFs;

if ( ! class_exists( 'Fillable_PDFs_API' ) ) {

	/**
	 * API for accessing Fillable PDFs data.
	 *
	 * @since     4.0
	 * @package   FillablePDFs
	 * @author    ForGravity
	 * @copyright Copyright (c) 2022, ForGravity
	 */
	class Fillable_PDFs_API {

		/**
		 * Deletes a PDF.
		 *
		 * @since 4.0
		 *
		 * @param array|string $pdf_object_or_id PDF meta object or PDF ID.
		 *
		 * @return bool
		 */
		public static function delete_pdf( $pdf_object_or_id ) {

			return fg_fillablepdfs()->delete_pdf( $pdf_object_or_id );

		}

		/**
		 * Returns the PDF meta for the given PDF ID.
		 *
		 * @since 4.0
		 *
		 * @param string $id PDF ID.
		 *
		 * @return array|null
		 */
		public static function get_pdf( $id ) {

			return Fillable_PDFs::get_pdf_meta( $id );

		}

		/**
		 * Returns all found PDFs for entry.
		 *
		 * @since 4.0
		 *
		 * @param array|int $entry_object_or_id Entry object or ID.
		 *
		 * @return array
		 */
		public static function get_pdfs_for_entry( $entry_object_or_id ) {

			return fg_fillablepdfs()->get_entry_pdfs( $entry_object_or_id );

		}

		/**
		 * Returns the physical file path to the provided PDF.
		 *
		 * @since 4.0
		 *
		 * @param array|string $pdf_object_or_id PDF meta object or PDF ID.
		 *
		 * @return false|string
		 */
		public static function get_physical_pdf_file_path( $pdf_object_or_id ) {

			if ( ! is_array( $pdf_object_or_id ) ) {
				$pdf_object_or_id = self::get_pdf( $pdf_object_or_id );
			}

			return fg_fillablepdfs()->get_physical_file_path( $pdf_object_or_id );

		}

	}

}

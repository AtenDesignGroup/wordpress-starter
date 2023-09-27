<?php
/**
 * The init file that holds constants and helper methods.
 *
 * @since 3.3
 *
 * @package ForGravity/FillablePDFs
 */

if ( ! defined( 'FG_FILLABLEPDFS_VERSION' ) ) {

	define( 'FG_FILLABLEPDFS_VERSION', '4.5' );
	define( 'FG_FILLABLEPDFS_EDD_ITEM_ID', 169 );
	define( 'FG_FILLABLEPDFS_DIR', dirname( __FILE__ ) );

	if ( ! defined( 'FG_EDD_STORE_URL' ) ) {
		define( 'FG_EDD_STORE_URL', 'https://cosmicgiant.com' );
	}

	if ( ! defined( 'FG_FILLABLEPDFS_API_URL' ) ) {
		define( 'FG_FILLABLEPDFS_API_URL', 'https://cosmicgiant.com/wp-json/pdf/v2/' );
	}

	if ( ! defined( 'FG_FILLABLEPDFS_PATH_CHECK_ACTION' ) ) {
		define( 'FG_FILLABLEPDFS_PATH_CHECK_ACTION', 'forgravity_fillablepdfs_check_base_pdf_path_public' );
	}

	/**
	 * Returns an instance of the Import class
	 *
	 * @esince 1.0
	 *
	 * @return ForGravity\Fillable_PDFs\Import|ForGravity\Fillable_PDFs\Legacy\Import
	 */
	function fg_fillablepdfs_import() {

		// If running on Gravity Forms 2.4.x, run legacy version.
		if ( ! version_compare( GFCommon::$version, '2.5-dev-1', '>=' ) ) {
			return ForGravity\Fillable_PDFs\Legacy\Import::get_instance();
		}

		return ForGravity\Fillable_PDFs\Import::get_instance();

	}

	/**
	 * Returns an instance of the Server class
	 *
	 * @since      1.0
	 * @deprecated 4.4
	 *
	 * @return ForGravity\Fillable_PDFs\Server
	 */
	function fg_fillablepdfs_server() {
		return fg_fillablepdfs()->get_server();
	}

	/**
	 * Returns an instance of the Templates class
	 *
	 * @since 1.0
	 *
	 * @return ForGravity\Fillable_PDFs\Templates|ForGravity\Fillable_PDFs\Legacy\Templates
	 */
	function fg_fillablepdfs_templates() {

		// If running on Gravity Forms 2.4.x, run legacy version.
		if ( ! version_compare( GFCommon::$version, '2.5-dev-1', '>=' ) ) {
			return ForGravity\Fillable_PDFs\Legacy\Templates::get_instance();
		}

		return ForGravity\Fillable_PDFs\Templates::get_instance();

	}

}

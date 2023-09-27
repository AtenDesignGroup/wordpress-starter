<?php
/**
 * The helper functions that allows overriding.
 *
 * @since 3.3
 *
 * @package ForGravity/FillablePDFs
 */

use ForGravity\Fillable_PDFs\API;

/**
 * Returns an instance of the Fillable_PDFs class
 *
 * @since 1.0
 *
 * @return ForGravity\Fillable_PDFs\Fillable_PDFs|ForGravity\Fillable_PDFs\Legacy\Fillable_PDFs
 */
function fg_fillablepdfs() {

	// If running on Gravity Forms 2.4.x, run legacy version.
	if ( ! version_compare( GFCommon::$version, '2.5-dev-1', '>=' ) ) {
		return ForGravity\Fillable_PDFs\Legacy\Fillable_PDFs::get_instance();
	}

	return ForGravity\Fillable_PDFs\Fillable_PDFs::get_instance();

}

/**
 * Returns an instance of the Fillable PDfs API.
 *
 * @since 3.4
 *
 * @param string $license_key License key.
 *
 * @return API|false|null
 */
function fg_pdfs_api( $license_key = '' ) {

	static $instance;

	if ( ! is_null( $instance ) ) {
		return $instance;
	}

	if ( ! function_exists( 'fg_fillablepdfs' ) || ! fg_fillablepdfs() ) {
		return false;
	}

	// Get the license key.
	if ( ! $license_key ) {
		$license_key = fg_fillablepdfs()->get_plugin_setting( 'license_key' );
	}

	// If the license key is empty, do not run a validation check.
	if ( rgblank( $license_key ) ) {
		return null;
	}

	// Log validation step.
	fg_fillablepdfs()->log_debug( __METHOD__ . '(): Validating API Info.' );

	// Setup a new Fillable PDFs API object with the API credentials.
	$api = new API( $license_key, FG_FILLABLEPDFS_EDD_ITEM_ID );

	try {

		// Get license info.
		$api->get_license_info();

		// Assign API library to instance.
		$instance = $api;

		// Log that authentication test passed.
		fg_fillablepdfs()->log_debug( __METHOD__ . '(): API credentials are valid.' );

		return $instance;

	} catch ( Exception $e ) {

		// Log that authentication test failed.
		fg_fillablepdfs()->log_error( __METHOD__ . '(): API credentials are invalid; ' . $e->getMessage() );

		return false;

	}

}

/**
 * Fillable PDFS pre-processing for apply_filters().
 *
 * Prepends the filter name with the Fillable PDFs prefix.
 * Allows additional filters based on form and field ID to be defined easily.
 *
 * @since 3.4
 *
 * @param string|array $filter The name of the filter and optional modifiers.
 * @param mixed        $value  The value to filter.
 *
 * @return mixed The filtered value.
 */
function fg_pdfs_apply_filters( $filter, $value ) {

	$modifiers = [];
	$args      = array_slice( func_get_args(), 2 );

	if ( is_array( $filter ) ) {
		$modifiers = array_splice( $filter, 1, count( $filter ) );
		$filter    = $filter[0];
	}

	// Prefix filter name.
	$filter = 'fg_fillablepdfs_' . $filter;

	// Add an empty modifier so the base filter will be applied as well.
	array_unshift( $modifiers, '' );

	$args = array_pad( $args, 10, null );

	foreach ( $modifiers as $modifier ) {
		$modifier = rgblank( $modifier ) ? '' : sprintf( '_%s', $modifier );
		$filter  .= $modifier;
		$value    = apply_filters( $filter, $value, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9] );
	}

	return $value;

}

/**
 * Fillable PDFs pre-processing for do_action().
 *
 * Prepends the action name with the Fillable PDFs prefix.
 * Allows additional actions based on form and field ID to be defined easily.
 *
 * @since 3.4
 *
 * @param string|array $action The action and optional modifiers.
 */
function fg_pdfs_do_action( $action ) {

	$modifiers = [];
	$args      = array_slice( func_get_args(), 1 );

	if ( is_array( $action ) ) {
		$modifiers = array_splice( $action, 1, count( $action ) );
		$action    = $action[0];
	}

	// Prefix action name.
	$action = 'fg_fillablepdfs_' . $action;

	// Add an empty modifier so the base filter will be applied as well.
	array_unshift( $modifiers, '' );

	$args = array_pad( $args, 10, null );

	foreach ( $modifiers as $modifier ) {
		$modifier = rgblank( $modifier ) ? '' : sprintf( '_%s', $modifier );
		$action  .= $modifier;
		do_action( $action, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9] );
	}

}

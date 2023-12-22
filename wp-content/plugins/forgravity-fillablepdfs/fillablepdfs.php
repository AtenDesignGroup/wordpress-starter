<?php
/**
 * Plugin Name: Fillable PDFs for Gravity Forms
 * Plugin URI: https://cosmicgiant.com/plugins/fillable-pdfs/
 * Description: Generate PDFs from Gravity Forms quickly and easily. Store locally, and import PDFs to use as the basis of a new Gravity Forms.
 * Version: 4.5
 * Author: CosmicGiant
 * Author URI: https://cosmicgiant.com
 * License: GPL-3.0+
 * Text Domain: forgravity_fillablepdfs
 * Domain Path: /languages
 *
 * ------------------------------------------------------------------------
 * Copyright 2019 ForGravity.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses.
 *
 * @package ForGravity/FillablePdfs
 */

defined( 'ABSPATH' ) || exit;

define( 'FG_FILLABLEPDFS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load constants and help functions.
require_once 'init.php';
require_once 'includes/helper-functions.php';
require_once 'includes/class-fillable-pdfs-api.php';

// Initialize the autoloader.
require_once 'includes/autoload.php';
require_once 'includes/vendor/autoload.php';

// Initialize plugin updater.
add_action( 'init', [ 'FillablePDFs_Bootstrap', 'updater' ], 0 );

// Bootstrap Fillable PDFs and register template downloader.
add_action( 'gform_loaded', [ 'FillablePDFs_Bootstrap', 'load' ], 5 );
add_action( 'gform_loaded', [ 'ForGravity\Fillable_PDFs\Templates', 'maybe_download_template' ], 6 );

// Include Gravity Flow step.
add_action( 'gravityflow_loaded', [ 'FillablePDFs_Bootstrap', 'load_gravityflow' ], 5 );

// Remove public folder checking on deactivation.
register_deactivation_hook( __FILE__, [ 'ForGravity\Fillable_PDFs\Fillable_PDFs', 'clear_scheduled_events' ] );

/**
 * Class FillablePDFs_Bootstrap
 * Handles the loading of the Fillable PDFs Add-On and registers with the Add-On framework.
 *
 * @since 1.0
 */
class FillablePDFs_Bootstrap {

	/**
	 * If the Feed Add-On Framework exists, Fillable PDFs Add-On is loaded.
	 *
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		// Register GravityView field.
		if ( class_exists( 'GravityView_Field' ) ) {
			include( dirname( __FILE__ ) . '/includes/integrations/gravityview/class-field-link.php' );
		}

		if ( ! version_compare( GFCommon::$version, '2.5-dev-1', '>=' ) ) {
			GFAddOn::register( 'ForGravity\Fillable_PDFs\Legacy\Fillable_PDFs' );
		} else {
			GFAddOn::register( 'ForGravity\Fillable_PDFs\Fillable_PDFs' );
		}

	}

	/**
	 * If the Gravity Flow exists, Fillable PDFs Step is loaded.
	 *
	 * @since 1.0
	 */
	public static function load_gravityflow() {

		try {

			Gravity_Flow_Steps::register( new \ForGravity\Fillable_PDFs\Integrations\Gravity_Flow\Step() );

		} catch ( Exception $e ) {

			fg_fillablepdfs()->log_error( __METHOD__ . '(): Unable to load Gravity Flow step.' );

		}

	}

	/**
	 * Initialize plugin updater.
	 *
	 * @access public
	 * @static
	 */
	public static function updater() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		// Get license key.
		$license_key = fg_fillablepdfs()->get_plugin_setting( 'license_key' );
		if ( ! is_null( $license_key ) ) {
			$license_key = trim( $license_key );
		}

		new ForGravity\Fillable_PDFs\EDD_SL_Plugin_Updater(
			FG_EDD_STORE_URL,
			__FILE__,
			[
				'version' => FG_FILLABLEPDFS_VERSION,
				'license' => $license_key,
				'item_id' => FG_FILLABLEPDFS_EDD_ITEM_ID,
				'author'  => 'ForGravity',
			]
		);

	}

}

<?php

/**
 * Plugin Name: PDF Embedder Premium
 * Plugin URI: http://wp-pdf.com/
 * Description: Embed mobile-friendly PDFs straight into your posts and pages. No third-party services required. Compatible With Gutenberg Editor Wordpress
 * Version: 5.1.4
 * Author: PDF Embedder Team
 * Author URI: http://wp-pdf.com/
 * Text Domain: pdf-embedder
 * License: Premium Paid per WordPress site
 *
 * Do not copy, modify, or redistribute without authorization from author Lesterland Ltd (contact@wp-pdf.com)
 *
 * You need to have purchased a license to install this software on each website.
 *
 * You are not authorized to use, modify, or distribute this software beyond the single site license(s) that you
 * have purchased.
 *
 * You must not remove or alter any copyright notices on any and all copies of this software.
 *
 * This software is NOT licensed under one of the public "open source" licenses you may be used to on the web.
 *
 * For full license details, and to understand your rights, please refer to the agreement you made when you purchased it
 * from our website at https://wp-pdf.com/
 *
 * THIS SOFTWARE IS SUPPLIED "AS-IS" AND THE LIABILITY OF THE AUTHOR IS STRICTLY LIMITED TO THE PURCHASE PRICE YOU PAID
 * FOR YOUR LICENSE.
 *
 * Please report violations to contact@wp-pdf.com
 *
 * Copyright LionShare Technologies, registered company in the United States of America
 *
 */

require_once( plugin_dir_path(__FILE__).'/core/commercial_pdf_embedder.php' );

class WP_PDF_Premium extends WP_PDF_Commercial {

	protected $plugin_version = '5.1.4';
	protected $wppdf_store_url = 'http://wp-pdf.com/';
	protected $wppdf_item_name = 'PDF Embedder Premium';
	protected $wppdf_item_id = 287;

	// Singleton
	private static $instance = null;

	protected function get_eddsl_optname() {
		return 'eddsl_pdfemb_mobile_ls';
	}

	protected function get_default_options() {
		return array_merge( parent::get_default_options(),
			Array(
				'pdfemb_download' => 'on'
			) );
	}

		/**
		 * Helper Method to get basename
		 *
		 * @return string
		 */
		protected function pdf_plugin_basename() {
			$basename = plugin_basename( __FILE__ );
			if ( __FILE__ === '/' . $basename ) { // Maybe due to symlink.
				$basename = basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ );
			}
			return $basename;
		}
		/**
		 * Helper Method to get plugin url
		 *
		 * @return string
		 */
		protected function pdf_plugin_url() {
			$basename = plugin_basename( __FILE__ );

			if ( __FILE__ === '/' . $basename ) { // Maybe due to symlink.
				return plugins_url() . '/' . basename( dirname( __FILE__ ) ) . '/';
			}

			// Normal case (non symlink).
			return plugin_dir_url( __FILE__ );
		}

		/**
		 * Helper to define
		 *
		 * @param string $name Define Name.
		 * @param mixed  $value Value.
		 * @return void
		 */
		public function define( $name, $value ) {

			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Setup Globals.
		 *
		 * @return void
		 */
		public function setup_globals() {
			$this->define( 'WP_PDF_VERSION', $this->plugin_version );
			$this->define( 'WP_PDF_SECURE_FILE', $this->file );
			$this->define( 'WP_PDF_SECURE_DIR', plugin_dir_path( __FILE__ ) );
			$this->define( 'WP_PDF_SECURE_URL', plugin_dir_url( __FILE__ ) );
		}
		public static function get_instance() {
			if (null == self::$instance) {
				self::$instance = new self;
				self::$instance->setup_globals();
				//self::$instance->includes();
			}
			return self::$instance;
		}
}

// Global accessor function to singleton
function pdfembPDFEmbedderMobile() {
	return WP_PDF_Premium::get_instance();
}

// Initialise at least once
pdfembPDFEmbedderMobile();
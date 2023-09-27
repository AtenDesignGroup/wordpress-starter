<?php
/**
 * Fillable PDFs Blocks class.
 *
 * @since 1.0
 *
 * @package ForGravity\Fillable_PDFs
 */

namespace ForGravity\Fillable_PDFs;

defined( 'ABSPATH' ) || die();

use GF_Blocks;

/**
 * Fillable PDFs Blocks class.
 *
 * @since     3.4
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2022, ForGravity
 */
class Blocks {

	/**
	 * Register blocks.
	 *
	 * This function MUST be called after the `init` hook of the main class.
	 *
	 * @since 3.4
	 */
	public function register() {

		if ( ! class_exists( 'GF_Blocks' ) ) {
			return;
		}

		$registered = GF_Blocks::register( Blocks\List_Block::get_instance() );

		if ( is_wp_error( $registered ) ) {
			fg_fillablepdfs()->log_error( 'Unable to register block; ' . $registered->get_error_message() );
		}

	}

}

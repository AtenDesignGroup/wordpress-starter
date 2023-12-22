<?php
/**
 * Connectable Integration class.
 * Handles all functionality for OAuth Integrations
 *
 * @since 4.0
 *
 * @package ForGravity\Fillable_PDFs
 */

namespace ForGravity\Fillable_PDFs\Integrations;

use WP_Error;

defined( 'ABSPATH' ) || die();

/**
 * Connectable Integration class.
 * Handles all functionality for OAuth Integrations
 *
 * @since 4.0
 * @package   ForGravity\Fillable_PDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2022, ForGravity
 */
interface Connectable {

	// # FORM SUBMISSION -----------------------------------------------------------------------------------------------

	/**
	 * Run Integration after PDF has been generated.
	 *
	 * @since 4.0
	 *
	 * @param array $pdf_meta PDF meta properties.
	 * @param array $entry    The current Entry object.
	 * @param array $form     The current Form object.
	 * @param array $feed     The current Feed object.
	 */
	public function action_fg_fillablepdfs_after_generate( $pdf_meta, $entry, $form, $feed );





	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Returns the text to display on plugin settings page in place of description when connected to Integration.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_connected_message();





	// # AUTHENTICATION ------------------------------------------------------------------------------------------------

	/**
	 * Disconnects from the Integration.
	 *
	 * @since 4.0
	 *
	 * @return WP_Error|true
	 */
	public function disconnect();

	/**
	 * Returns the URL to start the authentication process.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_auth_url();

	/**
	 * Returns if the site is successfully connected to the Integration.
	 *
	 * @since 4.0
	 *
	 * @return bool
	 */
	public function is_connected();

}

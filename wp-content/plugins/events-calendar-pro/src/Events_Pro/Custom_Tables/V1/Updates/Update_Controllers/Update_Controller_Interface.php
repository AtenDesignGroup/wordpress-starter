<?php
/**
 * The interface provided by any Update Controller.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use WP_REST_Request;

/**
 * Interface Update_Controller_Interface
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers
 */
interface Update_Controller_Interface {

	/**
	 * Sets the reference to the Request object the Update Controller
	 * should act upon.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the Request object
	 *                                 the Update Controller should act
	 *                                 upon.
	 */
	public function set_request( WP_REST_Request $request );

	/**
	 * Sets the reference to the Occurrence model instance the Update controller
	 * should act upon.
	 *
	 * @since 6.0.0
	 *
	 * @param Occurrence $occurrence  A reference to the Occurrence model instance
	 *                                the updates was started from.
	 */
	public function set_occurrence( Occurrence $occurrence );

	/**
	 * Applies the changes required by the Update Controller before the WP_IDENTIFY phase
	 * and returns the post ID the current Request should be redirected to.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post ID to apply the changes for.
	 *
	 * @return int|false The post ID the current Request should be redirected to, or
	 *                   `false` if the request should not, or cannot, be redirected.
	 */
	public function apply_before_identify_step( $post_id );
}
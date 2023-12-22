<?php
/**
 * Extends The Events Calendar version of the class to add methods specific to the Events Pro plugin.
 *
 * @since   6.0.1
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Updates\Requests as TEC_Requests;
use WP_REST_Request;

/**
 * Class Requests.
 *
 * @since   6.0.1
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates;
 */
class Requests extends TEC_Requests {
	/**
	 * Override of the TEC class method to support update links
	 *
	 * @since 6.0.1
	 * @param WP_REST_Request $request A reference to the current REST request.
	 *
	 * @return bool Whether the request is for an update or not.
	 */
	public function is_update_request( WP_REST_Request $request ): bool {
		return parent::is_update_request( $request ) || $this->is_link_update_request( $request );
	}

	/**
	 * Determines if the current request is a request to update a post from a link or not.
	 *
	 * @since 6.0.1
	 *
	 * @param WP_REST_Request $request A reference to the Request object to check.
	 *
	 * @return bool Whether the input Request is a request to run an update from a link or not.
	 */
	public function is_link_update_request( WP_REST_Request $request ): bool {
		$request_action = $request->get_param( 'action' );
		$request_update_type = $request->get_param( Updates::REQUEST_KEY );


		$is_link_update_request = $request_action === 'edit'
		                          && in_array( $request_update_type, [ Updates::SINGLE, Updates::UPCOMING ], true )
		                          && $request->get_method() === 'GET';

		return $is_link_update_request && (bool) check_admin_referer( 'tec_edit_link', 'nonce' );
	}
}
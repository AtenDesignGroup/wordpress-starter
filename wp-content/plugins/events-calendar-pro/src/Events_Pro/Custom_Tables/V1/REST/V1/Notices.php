<?php
/**
 * Class used to register the endpoints for the Gutenberg editor to manage the notices on repeated events to
 * indicate the number of generated occurrences per event.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\REST\V1
 */

namespace TEC\Events_Pro\Custom_Tables\V1\REST\V1;

use TEC\Events_Pro\Custom_Tables\V1\Admin\Notices\Occurrence_Notices;
use TEC\Events_Pro\Custom_Tables\V1\Templates\Single_Event_Modifications;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Transient_Occurrence_Redirector;
use WP_Post;
use WP_REST_Request;
use Tribe__Events__Main as TEC;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Occurrences notices at: GET|DELETE `tec/v1/events/{POST_ID|PROVISIONAL_ID}/notices/occurrences/`
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\REST\V1
 */
class Notices {
	public function register() {
		register_rest_route(
			'tec/v1',
			'/events/(?P<id>\d+)/notices/occurrences',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'show' ],
					'permission_callback' => 'is_user_logged_in',
					'args'                => [
						'id' => [
							'required'          => true,
							'sanitize_callback' => [ $this, 'sanitize' ],
							'validate_callback' => [ $this, 'validate' ],
						],
					],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'destroy' ],
					'permission_callback' => 'is_user_logged_in',
					'args'                => [
						'id' => [
							'required'          => true,
							'sanitize_callback' => [ $this, 'sanitize' ],
							'validate_callback' => [ $this, 'validate' ],
						],
					],
				],
			]
		);
	}

	/**
	 * Callback executed for `GET` method to retrieve the notice for a single ID from an event or provisional ID.
	 *
	 * @param WP_REST_Request $request The HTTP client request.
	 *
	 * @return WP_REST_Response The JSON response delivered to the client.
	 */
	public function show( WP_REST_Request $request ) {
		$post_id     = $request->get_param( 'id' );
		$occurrences = tribe( Occurrence_Notices::class );
		$creation    = $occurrences->get_message( $post_id );
		$occurrences->delete( $post_id );
		$data = [];

		// Only return data if is not empty.
		if ( ! empty( $creation ) ) {
			/**
			 * Encode the data in order to escape strings and HTML back to de client when returning a JSON string,
			 * decode happens at client using JSON.parse
			 */
			$data[] = wp_json_encode( $creation );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Callback executed for the  `DELETE` http method. This method removes the transient used to permanently store
	 * a notice associated with a particular post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request The HTTP request from the client.
	 *
	 * @return WP_REST_Response The response delivered to the client.
	 */
	public function destroy( WP_REST_Request $request ) {
		if ( tribe( Occurrence_Notices::class )->delete( $request->get_param( 'id' ) ) ) {
			return new WP_REST_Response( '', 204 );
		}

		return new WP_REST_Response( [ 'error' => _( 'The notice was not deleted as expected.' ) ], 500 );
	}

	/**
	 * Validate that the provided ID on the request is from an existing WP_Post object and that is a valid post_type
	 * in this case a valid post_type is an Event post_type. If a provisional post ID is provided, the ID is normalized
	 * in order to find the right Occurrence if exists.
	 *
	 * @since 6.0.0
	 * @since 6.0.2 Now the validation will take into account whether the ID was redirected from a recent update, instead of failing them.
	 *
	 * @param integer         $id      The ID we are using to locate and validate a WP_Post object.
	 * @param WP_REST_Request $request The HTTP client request against the endpoint.
	 *
	 * @return bool If the provided ID belongs to a valid occurrence or a valid Event post.
	 */
	public function validate( $id, WP_REST_Request $request ) {
		// If we redirected this ID, let's fetch the correct one.
		$redirect = tribe( Transient_Occurrence_Redirector::class )->get_redirect_data( $id );
		if ( isset( $redirect['redirect_id'] ) ) {
			$id = $redirect['redirect_id'];
		}
		$normalized_id = tribe( Single_Event_Modifications::class )->normalize_post_id( $id );
		$post          = get_post( $normalized_id );
		$request->set_param( '_id', $normalized_id );
		$request->set_param( '_post', $post );

		return $post instanceof WP_Post && TEC::POSTTYPE === $post->post_type;
	}

	/**
	 * Sanitize callback is executed after the `validate` and by doing so we have access to a variable stored in the
	 * request `_id`, this variable holds the actual post ID of the request and not the provisional post ID (if the
	 * provisional ID was provided instead). The sanitize callback always returns the actual post ID of the provided
	 * requested ID for instance `wp-json/tec/v1/events/10000731/notices/` will return `10` if `10` is the post ID of
	 * the real event instead of `10000731` the value `10000731` remains on the request object as `_id` in case is
	 * required for later use.
	 *
	 * @since 6.0.0
	 *
	 * @param integer         $id      The original requested ID to retrieve notices from.
	 * @param WP_REST_Request $request The HTTP client request to the endpoint.
	 *
	 * @return integer The actual post ID from the Event.
	 */
	public function sanitize( $id, WP_REST_Request $request ) {
		$normalized_id = $request->get_param( '_id' );
		$request->set_param( '_id', $id );

		return $normalized_id;
	}
}
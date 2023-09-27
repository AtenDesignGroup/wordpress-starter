<?php
/**
 * Builds the correct update controller depending on the request data.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers\Update_Controller_Interface as Update_Controller;
use Tribe__Events__Main as TEC;
use WP_REST_Request;

/**
 * Class Updates
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */
class Updates {
	public const ALL = 'all';
	public const SINGLE = 'single';
	public const UPCOMING = 'upcoming';
	public const REQUEST_KEY = '_tec_update_type';

	/**
	 * A reference to the current Provisional Post handler implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_Post
	 */
	private $provisional_post;
	/**
	 * A reference to the current Requests factory/repository implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Requests
	 */
	private $requests;

	/**
	 * Updates constructor.
	 *
	 * @param Requests         $requests         A reference to the current Requests factory/repository implementation.
	 * @param Provisional_Post $provisional_post A reference to the current Provisional Post handler implementation.
	 */
	public function __construct( Requests $requests, Provisional_Post $provisional_post ) {
		$this->requests         = $requests;
		$this->provisional_post = $provisional_post;
	}

	/**
	 * Returns the correct Update controller depending on the Request data.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return Update_Controller|false Either a reference to the Udpate Controller
	 *                                 that will handle the request, or `false` if the
	 *                                 Request should not be handled.
	 */
	public function for_request( WP_REST_Request $request ) {
		if ( ! $this->requests->is_update_request( $request ) ) {
			return false;
		}

		$request_id = (int) $request->get_param( 'id' );
		$post_id    = Occurrence::normalize_id( $request_id );

		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			return false;
		}

		if ( $request_id === $post_id ) {
			// A request for the Event.
			$occurrence = Occurrence::where( 'post_id', $post_id )
			                        ->order_by( 'start_date', 'ASC' )
			                        ->first();
		} else {
			$occurrence_id = $this->provisional_post->normalize_provisional_post_id( $request_id );
			$occurrence    = Occurrence::find( $occurrence_id, 'occurrence_id' );
		}

		if ( ! $occurrence instanceof Occurrence ) {
			// A new post, provide an Occurrence will provide the correct post information.
			$occurrence = new Occurrence( [ 'post_id' => $post_id, ] );

			return $this->build_all_update_controller( $request, $occurrence );
		}

		$is_first = Occurrence::is_first( $occurrence->occurrence_id );
		$is_last  = Occurrence::is_last( $occurrence->occurrence_id );

		$update_type = $request->get_param( self::REQUEST_KEY );

		if ( empty( $update_type ) ) {
			// When a request is not specifying an update type, then update All occurrences.
			$update_type = self::ALL;
		}

		if ( self::UPCOMING === $update_type ) {
			if ( $is_first ) {
				$update_type = self::ALL;
			}
			if ( $is_last ) {
				$update_type = self::SINGLE;
			}
		}

		switch ( $update_type ) {
			case self::UPCOMING:
				return $this->build_upcoming_update_controller( $request, $occurrence );
			case self::SINGLE:
				return $this->build_single_update_controller( $request, $occurrence );
			default:
			case self::ALL:
				return $this->build_all_update_controller( $request, $occurrence );
		}
	}

	/**
	 * Builds and returns an instance of the "All" update type controller.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the Request object the update should handle.
	 * @param Occurrence $occurrence A reference to the Occurrence model instance the update originated
	 *                                    from, or `null` if the update did not originate from an Occurrence
	 *                                    as
	 *
	 * @return Update_Controllers\All A reference to the built and ready to use "All" update type controller.
	 */
	private function build_all_update_controller( WP_REST_Request $request, Occurrence $occurrence ): Update_Controllers\All {
		$update_controller = tribe( Update_Controllers\All::class );
		$update_controller->set_request( $request );
		$update_controller->set_occurrence( $occurrence );

		return $update_controller;
	}

	/**
	 * Builds and returns an instance of the "Single" update type controller.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request    A reference to the Request object the update should handle.
	 * @param Occurrence      $occurrence A reference to the Occurrence model instance the update originated
	 *                                    from, or `null` if the update did not originate from an Occurrence
	 *                                    as
	 *
	 * @return Update_Controllers\Single A reference to the built and ready to use "Single" update type controller.
	 */
	private function build_single_update_controller( WP_REST_Request $request, Occurrence $occurrence ): Update_Controllers\Single {
		$update_controller = tribe( Update_Controllers\Single::class );
		$update_controller->set_request( $request );
		$update_controller->set_occurrence( $occurrence );

		return $update_controller;
	}

	/**
	 * Builds and returns an instance of the "Upcoming" update type controller.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request    A reference to the Request object the update should handle.
	 * @param Occurrence      $occurrence A reference to the Occurrence model instance the update originated
	 *                                    from, or `null` if the update did not originate from an Occurrence
	 *                                    as
	 *
	 * @return Update_Controllers\Upcoming A reference to the built and ready to use "Upcoming" update type controller.
	 */
	private function build_upcoming_update_controller( WP_REST_Request $request, Occurrence $occurrence ): Update_Controllers\Upcoming {
		$update_controller = tribe( Update_Controllers\Upcoming::class );
		$update_controller->set_request( $request );
		$update_controller->set_occurrence( $occurrence );

		return $update_controller;
	}
}
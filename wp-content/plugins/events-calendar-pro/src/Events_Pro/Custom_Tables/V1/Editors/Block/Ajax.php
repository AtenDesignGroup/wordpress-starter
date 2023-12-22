<?php
/**
 * Handles the AJAX requests fired from the context of the Blocks Editor in the Custom
 * Tables implementation.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors\Block
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors\Block;


use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Transient_Occurrence_Redirector as Redirector;
use WP_Post;
use WP_Post_Type;
use Tribe__Events__Main as TEC;

/**
 * Class Ajax
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors\Block
 */
class Ajax {
	const REDIRECT_ACTION = 'tec_custom_tables_v1_redirect_data';
	const REDIRECT_NONCE_NAME = 'tec_custom_tables_v1_blocks_editor_redirect_nonce';
	const SERIES_ACTION = 'tec_custom_tables_v1_series_data';
	const SERIES_NONCE_NAME = 'tec_custom_tables_v1_blocks_editor_series_nonce';

	/**
	 * A reference to the currence Occurrence redirection handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Redirector
	 */
	private $occurrence_redirecor;

	/**
	 * Ajax constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Redirector $redirector A reference to the current occurrence redirection implementation.
	 */
	public function __construct( Redirector $redirector ) {
		$this->occurrence_redirecor = $redirector;
	}

	/**
	 * Handles the request to return a Series data based on the related Event, if any.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value, but will echo and die a JSON
	 *              response.
	 */
	public function handle_series_data_ajax() {
		if ( ! (
			isset( $_REQUEST[ self::SERIES_NONCE_NAME ] )
			&& wp_verify_nonce( $_REQUEST[ self::SERIES_NONCE_NAME ], self::SERIES_ACTION ) )
		) {
			wp_send_json_error( [
				'error' => 'Unauthorized.'
			], 403 );
		}

		if ( ! ( $event = filter_var( $_REQUEST['event_id'], FILTER_VALIDATE_INT ) ) ) {
			wp_send_json_error( [
				'error' => 'Event id is missing or malformed.'
			], 400 );
		}

		$event_post = get_post( $event );

		if ( empty( $event_post ) ) {
			$redirect_data = $this->occurrence_redirecor->get_redirect_data( $event );

			if ( ! empty( $redirect_data['redirect_id'] ) ) {
				$event_post = get_post( $redirect_data['redirect_id'] );
			}
		}

		if ( ! $event_post instanceof WP_Post ) {
			wp_send_json_error( [
				'error' => 'No Event found.'
			], 404 );
		}

		$relationship = Series_Relationship::where( 'event_post_id', '=', $event_post->ID )->first();

		if ( ! $relationship instanceof Series_Relationship ) {
			// Not finding a Series related to the Event is fine; a consistency check should not be enforced here.
			wp_send_json_success( [
				'message' => 'No related Series found.'
			], 200 );
		}

		$series_post = get_post( $relationship->series_post_id );

		if ( ! $series_post instanceof WP_Post ) {
			wp_send_json_error( [
				'error' => 'Series post not found.'
			], 404 );
		}

		$series_post_type = get_post_type_object( Series::POSTTYPE );

		if ( ! $series_post_type instanceof WP_Post_Type ) {
			wp_send_json_error( [
				'error' => 'Series post type not found.'
			], 500 );
		}

		if ( ! current_user_can( $series_post_type->cap->edit_post, $series_post->ID ) ) {
			wp_send_json_error( [
				'error' => 'User cannot edit the Series.'
			], 403 );
		}

		wp_send_json_success( [
			'id'        => $series_post->ID,
			'edit_link' => get_edit_post_link( $series_post->ID, 'unencoded' ),
		] );
	}

	/**
	 * Handles the request to return an Occurrence redirect data.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value, but will echo and die a JSON
	 *              response.
	 */
	public function handle_redirect_data_ajax() {
		if ( ! (
			isset( $_REQUEST[ self::REDIRECT_NONCE_NAME ] )
			&& wp_verify_nonce( $_REQUEST[ self::REDIRECT_NONCE_NAME ], self::REDIRECT_ACTION )
		) ) {
			wp_send_json_error( [
				'error' => 'Unauthorized.'
			], 403 );
		}

		if ( ! ( $event_id = filter_var( $_REQUEST['event_id'], FILTER_VALIDATE_INT ) ) ) {
			wp_send_json_error( [
				'error' => 'Event id is missing or malformed.'
			], 400 );
		}

		// Converts the value to int on success.
		$event_post_id = filter_var( $_REQUEST['event_post_id'], FILTER_VALIDATE_INT ) ?: null;

		$occurrence_redirect_data = $this->occurrence_redirecor->get_occurrence_redirect_response( $event_id, $event_post_id );
		wp_send_json_success( $occurrence_redirect_data );
	}

	/**
	 * Localizes the data required by the AJAX support of the block editor to fetch the data for
	 * a Series.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value and will print a localizes AJAX payload
	 *              on the page.
	 */
	public function localize() {
		$data = [
			'ajaxurl'           => admin_url( 'admin-ajax.php' ),
			'seriesAction'      => self::SERIES_ACTION,
			'seriesNonce'       => wp_create_nonce( self::SERIES_ACTION ),
			'seriesNonceName'   => self::SERIES_NONCE_NAME,
			'redirectAction'    => self::REDIRECT_ACTION,
			'redirectNonce'     => wp_create_nonce( self::REDIRECT_ACTION ),
			'redirectNonceName' => self::REDIRECT_NONCE_NAME,
			'eventPostId'      => null,
		];

		// Localize the current Event post ID: changes will require a refresh of the page.
		$post = get_post();
		if (
			$post instanceof WP_Post
			&& $post->post_type === TEC::POSTTYPE
			&& tribe( Provisional_Post::class )->is_provisional_post_id( $post->ID )
		) {
			$occurrence_id = tribe( ID_Generator::class )->unprovide_id( $post->ID );
			$occurrence    = Occurrence::find( $occurrence_id );
			if ( $occurrence instanceof Occurrence ) {
				$data['eventPostId'] = $occurrence->post_id;
			}
		}

		wp_localize_script( 'wp-blocks', 'tecCustomTablesV1BlocksEditor', $data );
	}
}

<?php
/**
 * Subscribes to TEC-provided actions and filters to update
 * Event custom tables following scenarios provided by ECP.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates;

use DateTime;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Series\Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Event_Recurrence;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers\Update_Controller_Interface as Update_Controller;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Transient_Occurrence_Redirector as Occurrence_Redirector;
use Tribe__Date_Utils;
use WP_Post;
use WP_REST_Request;
use Tribe__Events__Main as TEC;
use WP_REST_Response;

/**
 * Class Controller
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */
class Controller {
	use With_Event_Recurrence;

	/**
	 * A reference to the current Requests factory/repository implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Requests
	 */
	private $requests;

	/**
	 * A reference to the current Redirector implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Redirector
	 */
	private $redirector;
	/**
	 * A reference to the current Updates controller factory implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Updates
	 */
	private $updates;

	/**
	 * A reference to the Update Controller that is handling the main Request update.
	 *
	 * @since 6.0.0
	 *
	 * @var Update_Controller|null
	 */
	private $update_controller;

	/**
	 * A reference to the current Event repository implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Events
	 */
	private $events;

	/**
	 * A reference to the current Occurrence redirector implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Occurrence_Redirector
	 */
	private $occurrence_redirector;

	/**
	 * A reference to the current Blocks (Editor) Meta handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Blocks_Meta
	 */
	private $blocks_meta;


	/**
	 * A flag that can be set in context specific scenarios during updates, to flag whether
	 * an occurrence may be pruned or some other operation, needing to be redirected to a specific
	 * instance.
	 *
	 * @since 6.0.2
	 *
	 * @var bool
	 */
	protected static $should_redirect_occurrence = false;

	/**
	 * The unmutated request object from the original request.
	 * Should not make any changes here.
	 *
	 * @since 6.0.2
	 *
	 * @var WP_REST_Request
	 */
	protected $original_request;

	/**
	 * The utility object for handling provisional post IDs.
	 *
	 * @since 6.0.2
	 *
	 * @var Provisional_Post
	 */
	protected $provisional_post;

	/**
	 * Controller constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Requests              $requests              A reference to the current Requests factory/repository implementation.
	 * @param Redirector            $redirector            A reference to the current Redirector implementation.
	 * @param Updates               $updates               A reference to the current Updates controller factory implementation.
	 * @param Events                $events                A reference to the current Event repository implementation.
	 * @param Occurrence_Redirector $occurrence_redirector A reference to the current implementation of the
	 *                                                     Occurrence redirector.
	 */
	public function __construct(
		Requests $requests,
		Redirector $redirector,
		Updates $updates,
		Events $events,
		Occurrence_Redirector $occurrence_redirector,
		Blocks_Meta $blocks_meta,
		Provisional_Post $provisional_post
	) {
		$this->requests              = $requests;
		$this->redirector            = $redirector;
		$this->updates               = $updates;
		$this->events                = $events;
		$this->occurrence_redirector = $occurrence_redirector;
		$this->blocks_meta           = $blocks_meta;
		$this->provisional_post      = $provisional_post;
		$this->set_original_request( $this->requests->from_http_request() );
	}

	/**
	 * Ensures our redirected ID does not show up in the REST response.
	 *
	 * @since 6.0.11
	 *
	 * @param WP_REST_Response $response Result to send to the client.
	 *                                   Usually a WP_REST_Response or WP_Error.
	 * @param array            $handler  Route handler used for the request.
	 * @param WP_REST_Request  $request  Request used to generate the response.
	 *
	 * @return WP_REST_Response The modified WP_REST_Response.
	 */
	public function retain_original_id_in_response( WP_REST_Response $response, $handler, WP_REST_Request $request ): WP_REST_Response {
		$redirected = $request->get_param( '_tec_initial_meta' );
		if ( ! isset( $redirected['id'], $response->data['id'] ) ) {
			return $response;
		}

		/**
		 * Grab our original ID, and set it for the response.
		 * Block Editor will get confused if it gets a response with a different ID.
		 * It will think the save did not finish and remain in a 'dirty' state.
		 */
		$response->data['id'] = (int) $redirected['id'];

		return $response;
	}

	/**
	 * Set the original request, used for various evaluations, such as when and if an occurrence should be redirected.
	 *
	 * @since 6.0.2
	 *
	 * @param WP_REST_Request $request The request objected meant to be used as an evaluation of the original request state.
	 */
	public function set_original_request( WP_REST_Request $request ) {
		$this->original_request = $request;
	}

	/**
	 * Delete the Pro data.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id Post ID to be deleted.
	 *
	 * @return int Rows affected.
	 */
	public function delete( $post_id ) {
		return $this->events->delete( $post_id );
	}

	/**
	 * Deletes the occurrence transients tied to this post_id.
	 *
	 * @since 6.0.0
	 *
	 * @param numeric $post_id The post ID to delete occurrence transients for.
	 *
	 * @return bool
	 */
	public function delete_occurrence_transients( $post_id ) {
		return $this->events->delete_occurrence_transients( $post_id );
	}

	/**
	 * Redirects a Classic Editor request to either the real
	 * post when editing All Occurrences of a Recurring Event, or
	 * to a new post when applying edits to a Single Event or the
	 * Upcoming ones.
	 *
	 * @since 6.0.0
	 *
	 * @return int|false Either the post ID the request has been redirected to,
	 *                   or `false` if the request was not redirected.
	 */
	public function redirect_classic_editor_request() {
		// Use a REST Request object to model the HTTP Request.
		$request = $this->requests->from_http_request();

		return $this->redirect_request( $request );
	}

	/**
	 * Redirects a REST request to the correct post ID, if required
	 * by the Request and Update types.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the object modeling
	 *                                 the Request to redirect.
	 *
	 * @return int|false Either the post ID the request has been redirected to,
	 *                   or `false` if the request was not redirected.
	 */
	public function redirect_request( WP_REST_Request $request ) {
		if ( ( $redirected_id = $this->redirect_removed_occurrence( $request ) ) ) {
			$this->redirector->redirect_request( $request, $redirected_id );
		}

		if ( ! $this->requests->is_update_request( $request ) ) {
			// Not the kind of request we need to redirect.
			return false;
		}

		/*
		 * We assume the `id` param will be set as it would not have passed
		 * the previous check.
		 */
		$request_id = (int) $request->get_param( 'id' );

		$update_controller = $this->updates->for_request( $request );

		if ( ! $update_controller instanceof Update_Controller ) {
			return false;
		}

		$this->update_controller = $update_controller;

		$redirected_id = $update_controller->apply_before_identify_step( $request_id );

		if ( $redirected_id === $request_id || empty( $redirected_id ) ) {
			// Nothing to do here.
			return $request_id;
		}

		// Redirect the Request and update the auth.
		return $this->redirector->redirect_request( $request, $redirected_id );
	}

	/**
	 * Prune the just updated Occurrences by sequence number, dropping any one belonging to
	 * the previous sequence.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The ID of the Event post the Occurrences are being saved for.
	 *
	 * @return int|false The number of occurrences deleted or false if missing sequence.
	 */
	public function prune_occurrences_by_sequence( $post_id ) {
		return $this->events->prune_occurrences( $post_id );
	}

	/**
	 * Saves an Event recurrence meta to the database.
	 *
	 * This method is pretty much the same has the
	 * `Tribe__Events__Pro__Recurrence__Meta::updateRecurrenceMeta` one,
	 * minus the children Event generation.
	 *
	 * @since 6.0.0
	 *
	 * @param int                 $event_id The Event post ID.
	 * @param array<string,mixed> $data     The whole Event data, including the Recurrence
	 *                                      data.
	 *
	 * @return bool Whether the meta was saved or not.
	 */
	public function save_recurrence_meta( $event_id, $data ) {
		return $this->events->save_recurrence_meta( $event_id, $data );
	}

	/**
	 * Filter the TEC Occurrence match to return one matched by dates and post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param Occurrence|null $occurrence Either a reference to an existing, matching, Occurrence
	 *                                    or `null`.
	 * @param Occurrence      $result     A reference to the Occurrence model instance that will be inserted
	 *                                    if a matching Occurrence cannot be found.
	 * @param int             $post_id    The post ID of the Event the Occurrence match is being searched for.
	 *
	 * @return Occurrence|null Either the reference to an existing Occcurrence matching the one
	 *                          that should be inserted, or `null` to indicate none was found.
	 */
	public function get_occurrence_match( $occurrence, $result, $post_id ) {
		return $this->events->get_occurrence_match( $occurrence, $result, $post_id );
	}

	/**
	 * After the Events and Occurrences custom tables have been updated following the
	 * request, update the Event to Series relationships with what data is specified
	 * in the Request.
	 *
	 * @since 6.0.0
	 *
	 * @param int             $post_id The Event post ID.
	 * @param WP_REST_Request $request A reference to the Request object that triggered the
	 *                                 updated.
	 *
	 * @return true The method will always return `true` to indicate the update was
	 *              successful: Event to Series relationship for an Event post and
	 *              Request couple could not be created for good reasons.
	 */
	public function commit_post_updates_after( $post_id, WP_REST_Request $request ) {
		$this->events->update_relationships( $post_id, $request );

		return true;
	}

	/**
	 * Determines if we should update any of the custom table data for this Request.
	 * This is where we hook into related data such as associating Series to an Event.
	 *
	 * @since 6.0.0
	 *
	 * @param bool            $should_update Whether the post custom tables should be updated or not,
	 *                                       according to The Events Calendar default logic and previous
	 *                                       methods filtering the value.
	 * @param int             $post_id       The ID of the post currently being updated.
	 * @param WP_REST_Request $request       A reference to object modeling the current Request.
	 *
	 * @return bool Whether the custom tables should be updated or not, taking the input
	 *              value into account.
	 */
	public function should_update_custom_tables( $should_update, $post_id, WP_REST_Request $request ) {
		return $should_update || $request->has_param( Relationship::EVENTS_TO_SERIES_REQUEST_KEY );
	}

	/**
	 * Returns the post ID the request for an Occurrence should be redirected to
	 * if the request targets a removed Occurrence.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the Request to check.
	 *
	 * @return int Either the post ID the request should be redirected to, or `0`
	 *             if the request is not for a removed Occurrence and should not
	 *             be redirected.
	 */
	private function redirect_removed_occurrence( WP_REST_Request $request ) {
		$request_id = $request->get_param( 'id' );

		$data = $this->occurrence_redirector->get_redirect_data( $request_id );

		if ( empty( $data ) ) {
			return 0;
		}

		return isset( $data['redirect_id'] ) ? (int) $data['redirect_id'] : 0;
	}

	/**
	 * Sync's the block recurrence metadata with our classic _EventRecurrence metadata.
	 *
	 * @since 6.0.0
	 *
	 * @param int    $post_id    The ID of the post whose meta is being updated.
	 * @param string $meta_key   The meta key of the filtered update.
	 * @param mixed  $meta_value The value of the filtered update.
	 *
	 * @return bool Whether the meta update was correctly applied or not.
	 */
	public function sync_from_classic_format( $post_id, $meta_key, $meta_value ) {
		if ( $meta_key !== '_EventRecurrence' ) {
			return false;
		}

		$updated   = true;

		$meta_value = $this->add_off_pattern_flag_to_meta_value( $meta_value, $post_id );

		$converted = $this->blocks_meta->from_classic_format( $meta_value );

		if ( empty( $converted ) || 0 === count( array_filter( $converted ) ) ) {
			$this->blocks_meta->delete_blocks_meta( $post_id );
		}

		foreach ( $converted as $key => $value ) {
			$updated &= update_post_meta( $post_id, $key, $value );
		}

		return $updated;
	}

	/**
	 * For recurring events, if the event date is not specified, we should go to the series page
	 * instead of locating the random occurrence to display here.
	 */
	public function redirect_single_view() {
		// Redirect to the Series page
		global $wp_query, $post;

		// If we are on a TEC single event, without the event date defined,
		// we should redirect to the series page if this is a recurring event.
		if ( ! empty( $wp_query->query_vars['eventDate'] )
		     || ! isset( $wp_query->query_vars['eventDisplay'] )
		     || ! isset( $wp_query->query_vars['post_type'] ) ) {
			return;
		}

		if ( $wp_query->query_vars['post_type'] !== TEC::POSTTYPE ) {
			return;
		}

		if ( $wp_query->query_vars['eventDisplay'] !== 'single-event' ) {
			return;
		}

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		// Do we have a Series and is this a Recurring Event?
		$post_id = Occurrence::normalize_id( $post->ID );
		if ( ! $post_id ) {
			return;
		}

		// Is it recurring?
		$count = Occurrence::where( 'post_id', $post_id )->count();
		if ( $count < 2 ) {
			return;
		}

		// In a series?
		$series = Series_Relationship::find( $post_id, 'event_post_id' );
		if ( $series instanceof Series_Relationship ) {
			wp_redirect( get_post_permalink( $series->series_post_id ) );
			exit;
		}
	}

	/**
	 * Redirect an Occurrence when deleted or trashed.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post          $post     A reference to the post object that is being deleted or trashed.
	 * @param WP_REST_Response $response A reference to the REST response generated for the delete or trash request.
	 * @param WP_REST_Request  $request  A reference to the REST request that triggered the post trash or deletion.
	 *
	 * @return void The method will modify the response data.
	 */
	public function redirect_deleted_occurrence( WP_Post $post, WP_REST_Response $response, WP_REST_Request $request ): void {
		$occurrence_redirect_data = $this->occurrence_redirector->get_occurrence_redirect_response( $request->get_param( 'id' ) );
		// Either redirect to the correct Occurrence, or to the Events list.
		$location                            = $occurrence_redirect_data->location
		                                       ?? admin_url( '/edit.php?post_type=' . TEC::POSTTYPE );
		$response->data['_tec_redirect_url'] = $location;
	}

	/**
	 * Redirects a DELETE request to the correct post ID, if required.
	 *
	 * This method acts as a specialized proxy to the default redirection method
	 * to ensure the Request object will conform to the expected format.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the request that triggered the post deletion.
	 *
	 * @return false|int Either the post ID to redirect to, or `false` if the request should not be redirected.
	 */
	public function redirect_delete_request( WP_REST_Request $request ) {
		$id = $request->has_param( 'id' ) ? $request->get_param( 'id' ) : null;

		if ( empty( $id ) || $request->get_method() !== \WP_REST_Server::DELETABLE ) {
			return false;
		}

		// We can make this check safely using an Occurrence provisional ID or a real post ID.
		if ( get_post_field( 'post_type', $id ) !== TEC::POSTTYPE ) {
			// Not an Event post, bail.
			return false;
		}

		/*
		 * The Blocks editor will not handle a Trash request correctly when posts should be just
		 * deleted; see https://github.com/WordPress/gutenberg/issues/13024
		 * To cope with that, set the `force` parameter of the request to let the deletion
		 * work correctly and not show the error message to the user.
		 */
		$force = defined( 'EMPTY_TRASH_DAYS' ) && (int) EMPTY_TRASH_DAYS === 0;
		$request->set_param( 'force', $force );

		return $this->redirect_request( $request );
	}

	/**
	 * Checks if the conditions are set for a redirect from the originating
	 * occurrence ID. If so, will store a transient with flags on what type of
	 * redirect should occur.
	 *
	 * @since 6.0.2
	 *
	 * @param bool            $updated Whether an occurrence update happened.
	 * @param int             $post_id The post ID for this occurrence.
	 *
	 * @return bool Occurrences update flag.
	 * @throws \Exception
	 */
	public function resolve_potential_redirect( bool $updated, int $post_id ): bool {
		// If we updated our occurrences, should we do a redirect?
		$original_request = $this->original_request;
		// Did anything update and are the appropriate conditions right for a redirected ID to be set?
		if ( $updated && $this->should_redirect_occurrence( $original_request ) ) {
			// Get original non mutated ID from request.
			$from_id = self::get_id( $original_request );

			// Find our request start date, so we can locate the occurrence we want.
			$request_start_date = null;
			$meta               = $original_request->get_param( 'meta' );
			if ( $original_request->get_param( 'EventStartDate' ) ) {
				$request_start_date = $original_request->get_param( 'EventStartDate' );
			} else if ( isset( $meta['_EventStartDate'] ) ) {
				$request_start_date = $meta['_EventStartDate'];
			}

			// Did we move this occurrence? Find it again.
			$occurrence = null;
			if ( $request_start_date ) {
				// Normalize from datepicker to database format.
				$request_start_date = Tribe__Date_Utils::maybe_format_from_datepicker( $request_start_date );
				if ( $original_request->get_param( 'EventStartTime' ) ) {
					$request_start_date = $request_start_date . ' ' . $original_request->get_param( 'EventStartTime' );
				}
				$request_start_date = new DateTime( $request_start_date );

				$occurrence = Occurrence::where(
					'start_date', '=', $request_start_date->format( 'Y-m-d H:i:s' ) )
				                        ->where( 'post_id', $post_id )
				                        ->order_by( 'start_date', 'ASC' )
				                        ->first();
			}

			// If we didn't find the adjusted occurrence let's grab the first one for this recurring event.
			if ( ! $occurrence ) {
				$occurrence = Occurrence::where( 'post_id', $post_id )
				                        ->order_by( 'start_date', 'ASC' )
				                        ->first();
			}

			if ( $occurrence instanceof Occurrence ) {
				// Store the new ID that will be used by either block or classic requests.
				tribe( Transient_Occurrence_Redirector::class )
					->set_redirected_id(
						$occurrence->provisional_id,
						$from_id,
						null,
						false
					);
			}
		} else if ( $updated && static::$should_redirect_occurrence ) {
			/**
			 * We updated something, but if it still exists stay on it.
			 * When we change the global request variables, it will by default redirect (in classic editor) to that routed
			 * occurrence. This avoids that default behavior and stays on the current occurrence.
			 */
			$transient_redirect = tribe( Transient_Occurrence_Redirector::class );
			$from_id            = static::get_id( $original_request );
			$redirect           = $transient_redirect->get_redirect_data( $from_id );
			// If no explicitly defined redirect and this Occurrence still exists.
			if ( ! $redirect && Occurrence::find( tribe( ID_Generator::class )->unprovide_id( $from_id ) ) ) {
				// Store the old ID (our request may have been routed to a different occurrence, this ensures we retain it for this scenario).
				$transient_redirect->set_redirected_id(
					$from_id,
					$from_id
				);
			}
		}

		return $updated;
	}

	/**
	 * Try and locate the occurrence or post ID from this request object.
	 *
	 * @since 6.0.2
	 *
	 * @param WP_REST_Request $request A reference to the current request object.
	 *
	 * @return null|int Either the ID of the post targeted by the request, or `null` if not set.
	 */
	public static function get_id( WP_REST_Request $request ): ?int {
		if ( $request->get_param( 'id' ) ) {
			return (int) $request->get_param( 'id' );
		}
		if ( $request->get_param( 'post_ID' ) ) {
			return (int) $request->get_param( 'post_ID' );
		}
		if ( $request->get_param( 'post' ) ) {
			return (int) $request->get_param( 'post' );
		}

		return null;
	}

	/**
	 * This will filter the WP redirect location if we have an occurrence ID that was adjusted.
	 *
	 * @since 6.0.2
	 *
	 * @param string $location The URL to redirect to.
	 * @param int    $post_id  The post ID.
	 *
	 * @return string The correct URL to redirect to.
	 */
	public function classic_redirect_post_location( string $location, int $post_id ): string {
		$redirect = tribe( Transient_Occurrence_Redirector::class )->get_redirect_data( self::get_id( $this->original_request ) );
		if ( isset( $redirect['redirect_id'] ) ) {
			return get_edit_post_link( $redirect['redirect_id'], 'internal' );
		}

		return $location;
	}

	/**
	 * Checks if the occurrence for this request should be redirected. This looks at several indicators including an
	 * explicitly set flag that will be used in context heavy situations.
	 *
	 * @since 6.0.2
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool Whether we should setup a redirect for this request or not.
	 */
	public function should_redirect_occurrence( WP_REST_Request $request ): bool {
		$request_id = self::get_id( $request );

		return self::$should_redirect_occurrence
		       && $this->requests->is_update_request( $request )
		       && $this->provisional_post->is_provisional_post_id( $request_id )
		       // Only redirect if this occurrence is gone
		       && ! Occurrence::find( tribe(ID_Generator::class)->unprovide_id( $request_id ) );
	}

	/**
	 * Sets a flag where the occurrence may be redirected. Other indicators will be evaluated as well, but this flag is
	 * required to set up the redirect transient.
	 *
	 * @since 6.0.2
	 *
	 * @param bool $should_redirect Sets flag that this request may need to redirect to another occurrence.
	 *
	 * @return $this A reference to this, for chaining purposes.
	 */
	public function set_should_redirect_occurrence( bool $should_redirect ): Controller {
		self::$should_redirect_occurrence = $should_redirect;

		return $this;
	}
}

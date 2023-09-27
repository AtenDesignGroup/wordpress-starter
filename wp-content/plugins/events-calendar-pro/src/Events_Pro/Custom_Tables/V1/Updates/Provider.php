<?php
/**
 * Handles the updates to the custom tables data following updates to
 * the Event posts.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Provider_Contract;
use WP_Error;
use WP_HTTP_Response;
use WP_Post;
use WP_REST_Request;
use Tribe__Events__Main as TEC;
use WP_REST_Response;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */
class Provider extends Service_Provider implements Provider_Contract {

	/**
	 * Registers the implementations and filters required for updates to work
	 * correctly with the custom tables;
	 *
	 * @since 6.0.0
	 */
	public function register() {
		// Make this provider available in the Service Locator by class and slug.
		$this->container->singleton( self::class, $this );
		$this->container->singleton( 'tec.events-pro.custom-tables-v1.updates', $this );

		// Some cached values require this object to be the same at the start and end of the request.
		$this->container->singleton( Controller::class, Controller::class );

		$this->hook_to_watch_for_post_updates();
		$this->hook_to_redirect_post_updates();
		$this->hook_to_commit_post_updates();
		$this->hook_to_delete_event();
		$this->hook_into_post_ops();

		add_filter( 'tec_events_pro_recurrence_meta_get', [ $this, 'add_off_pattern_start_flag' ], 10, 2 );
		add_filter( 'tec_events_pro_recurrence_meta_update', [ $this, 'add_off_pattern_start_flag' ], 10, 2 );
		add_filter( 'post_row_actions', [ $this, 'add_update_links' ], 20, 2 );
	}

	/**
	 * Hook into the filter that, in TEC, will decide what meta keys should be watche for updates
	 * to add the Recurrence related one(s).
	 *
	 * @since 6.0.0
	 */
	public function hook_to_watch_for_post_updates(): void {
		/*
	   * Add the `_EventRecurrence` meta to the meta keys that should be watched for updates.
	   */
		add_filter( 'tec_events_custom_tables_v1_tracked_meta_keys', [ $this, 'track_pro_meta_keys' ] );
	}

	/**
	 * Hooks into our delete Event trigger, and remove any Pro specific related data.
	 *
	 * @since 6.0.0
	 */
	public function hook_to_delete_event(): void {
		add_action( 'tec_events_custom_tables_v1_delete_post', [ $this, 'commit_delete' ], 10, 1 );
		add_action( 'tec_events_custom_tables_v1_delete_post', [ $this, 'delete_occurrence_transients' ], 10, 1 );

		/*
		 * This action is documented in wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php
		 * It fires after the post has been trashed or deleted (WRITE) following the REST request, and
		 * before the response is returned (READ).
		 */
		add_action( 'rest_delete_' . TEC::POSTTYPE, [ $this, 'redirect_deleted_occurrence' ], 100, 3 );

		/*
		 * In the context of REST requests, the `rest_pre_dispatch` hook will not provide the required `id` parameter
		 * and the request will not be flagged as an update one. We cope with that here by trying with a later-time
		 * redirection.
		 */
		add_filter( 'rest_request_before_callbacks', [ $this, 'redirect_trash_delete_request' ], 10, 3 );
	}

	/**
	 * Hook ito the actions dispatched by TEC that allow redirecting the post update request to
	 * another one to include Occurrence resolution from a provisional post ID to the real
	 * post ID.
	 *
	 * @since 6.0.0
	 *
	 */
	public function hook_to_redirect_post_updates(): void {
		/**
		 * When TEC would redirect a Classic Editor request, apply the plugin redirection logic.
		 * On Single and Upcoming type of updates, create new posts and redirect the
		 * request to them. On an All update, redirect the request to the real post.
		 */
		add_action( 'tec_events_custom_tables_v1_redirect_classic_editor_event_post', [
			$this,
			'redirect_classic_editor_request'
		] );

		/**
		 * When TEC would redirect a REST request, apply the plugin redirection logic.
		 * On Single and Upcoming type of updates, create new posts and redirect the
		 * request to them. On an All update, redirect the request to the real post.
		 */
		add_action( 'tec_events_custom_tables_v1_redirect_rest_event_post', [ $this, 'redirect_rest_request' ] );

		// Retain our ID for responses.
		if ( ! has_filter( 'rest_request_after_callbacks', [ $this, 'retain_original_id_in_response' ] ) ) {
			add_filter( 'rest_request_after_callbacks', [ $this, 'retain_original_id_in_response' ], 10, 3 );
		}
	}

	/**
	 * Ensures our redirected ID does not show up in the REST response.
	 *
	 * @since 6.0.11
	 *
	 * @param WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response Result to send to the client.
	 *                                                                   Usually a WP_REST_Response or WP_Error.
	 * @param array                                            $handler  Route handler used for the request.
	 * @param WP_REST_Request                                  $request  Request used to generate the response.
	 *
	 * @return WP_REST_Response|WP_HTTP_Response|WP_Error|mixed The modified WP_REST_Response or what was passed in.
	 */
	public function retain_original_id_in_response( $response, $handler, $request ) {
		if ( ! $response instanceof WP_REST_Response || empty( $response->data ) ) {
			return $response;
		}
		if ( ! $request instanceof WP_REST_Request ) {
			return $response;
		}

		return $this->container->make( Controller::class )->retain_original_id_in_response( $response, $handler, $request );
	}

	/**
	 * Hook into the actions and filters dispatched by TEC to update the custom tables data correctly.
	 *
	 * @since 6.0.0
	 *
	 */
	public function hook_to_commit_post_updates(): void {
		/*
	    * In the AFTER-WRITE, BEFORE-READ portion of the code, we'll need the `_EventRecurrence` meta
	    * to be saved.
	    */
		add_action( 'tribe_events_update_meta', [ $this, 'save_recurrence_meta' ], 20, 2 );

		/*
		 * When TEC will look for matching Occurrences, then override the default logic and match them
		 * by date.
		 */
		add_filter( 'tec_custom_tables_v1_get_occurrence_match', [ $this, 'get_occurrence_match' ], 10, 3 );

		/**
		 * During the udpate of the custom tables information, then Occurrences will be inserted or updated.
		 * The Events Calendar will, by default, not perform any cleaning operation on "old" Occurrences, but
		 * this plugin might.
		 */
		add_action( 'tec_events_custom_tables_v1_after_save_occurrences', [ $this, 'prune_occurrences_by_sequence' ] );

		/**
		 * Hook after TEC update operation to perform those updates that require the custom tables to have been
		 * correctly updated to be performed, e.g. Series to Events relationships updates.
		 */
		add_filter( 'tec_events_custom_tables_v1_updated_post', [ $this, 'commit_post_updates_after' ], 10, 3 );

		/**
		 * Hook to check if we should update custom tables for reasons other that the meta key watcher, such as
		 * the block's series save request.
		 */
		add_filter(
			'tec_events_custom_tables_v1_should_update_custom_tables',
			[ $this, 'should_update_custom_tables' ],
			10,
			3
		);

		/**
		 * Responsible for syncing relevant blocks data to our classic recurrence metadata changes.
		 */
		add_action( 'update_post_meta', [ $this, 'sync_blocks_recurrence_meta' ], 10, 4 );
		add_action( 'delete_post_meta', [ $this, 'sync_blocks_recurrence_meta' ], 10, 4 );

		/**
		 * These evaluate and redirect the page and subsequent requests to the appropriate ID in cases an occurrence was pruned.
		 */
		add_action( 'tec_events_custom_tables_v1_updated_post', [ $this, 'resolve_potential_redirect' ], 10, 3 );
		add_filter( 'tec_events_custom_tables_v1_redirect_post_location', [
			$this,
			'classic_redirect_post_location'
		], 10, 2 );
	}

	/**
	 * This will use the logic that handles scenarios redirecting pruned occurrences to the proper destination
	 * occurrence, for the classic editor.
	 *
	 * @since 6.0.2
	 *
	 * @param string  $location The URL being filtered.
	 * @param numeric $post_id  The event post ID.
	 *
	 * @return string The redirected location
	 */
	public function classic_redirect_post_location( $location, $post_id ) {
		return $this->container->make( Controller::class )->classic_redirect_post_location( (string) $location, (int) $post_id );
	}

	/**
	 * Evaluates context and occurrence requests to determine if this occurrence was pruned and needs to have the ID
	 * redirected to a new occurrence ID. This logic is used for both block and classic editor.
	 *
	 * @since 6.0.2
	 *
	 * @param bool            $updated Whether this event was updated.
	 * @param numeric         $post_id The post ID for this event.
	 * @param WP_REST_Request $request The request object being used for these updates.
	 *
	 * @return bool The update flag.
	 * @throws \Exception
	 */
	public function resolve_potential_redirect( $updated, $post_id, WP_REST_Request $request ) {
		remove_action( 'tec_events_custom_tables_v1_updated_post', [ $this, 'resolve_potential_redirect' ], 10, 3 );

		return $this->container->make( Controller::class )->resolve_potential_redirect( (bool) $updated, (int) $post_id, $request );
	}

	/**
	 * Unregisters, from the Filters API, the actions and filters added by this provider.
	 *
	 * @since 6.0.0
	 */
	public function unregister() {
		remove_action( 'tec_events_custom_tables_v1_updated_post', [ $this, 'resolve_potential_redirect' ], 10 );
		remove_filter( 'tec_events_custom_tables_v1_redirect_post_location', [
			$this,
			'classic_redirect_post_location'
		], 10 );
		remove_action( 'tec_events_custom_tables_v1_redirect_classic_editor_event_post',
			[ $this, 'redirect_classic_editor_request' ] );
		remove_action( 'tec_events_custom_tables_v1_redirect_rest_event_post', [ $this, 'redirect_rest_request' ] );
		remove_filter( 'tec_events_custom_tables_v1_tracked_meta_keys', [ $this, 'track_pro_meta_keys' ] );
		remove_action( 'tribe_events_update_meta', [ $this, 'save_recurrence_meta' ], 20 );
		remove_action( 'tec_events_custom_tables_v1_after_save_occurrences', [
			$this,
			'prune_occurrences_by_sequence'
		] );
		remove_action( 'tec_events_custom_tables_v1_delete_post', [ $this, 'commit_delete' ]  );
		remove_action( 'tec_events_custom_tables_v1_delete_post', [ $this, 'delete_occurrence_transients' ]  );
		remove_filter( 'tec_custom_tables_v1_get_occurrence_match', [ $this, 'get_occurrence_match' ] );
		remove_filter( 'tec_events_custom_tables_v1_updated_post', [ $this, 'commit_post_updates_after' ] );
		remove_filter( 'tec_events_custom_tables_v1_should_update_custom_tables', [ $this, 'should_update_custom_tables' ] );
		remove_action( 'update_post_meta', [ $this, 'sync_blocks_recurrence_meta' ] );
		remove_filter( 'rest_request_after_callbacks', [ $this, 'retain_original_id_in_response' ] );
	}

	/**
	 * Watches for changes to our classic recurrence metadata, and syncs the relevant block metadata so they remain
	 * accurate together.
	 *
	 * @since 6.0.0
	 *
	 * @param array|int    $meta_id    The unique value for the entry in custom fields table, unused.
	 * @param int    $object_id  The ID of the post whose meta is being updated.
	 * @param string $meta_key   The meta key of the filtered update.
	 * @param mixed  $meta_value The value of the filtered update.
	 *
	 * @return void The method will update the Block Editor format recurrence meta converting the
	 *              input value in the `_EventRecurrence` format.
	 */
	public function sync_blocks_recurrence_meta( $meta_id, int $object_id, string $meta_key, $meta_value ): void {
		$this->container->make( Controller::class )
		                ->sync_from_classic_format( $object_id, $meta_key, $meta_value );
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
	public function should_update_custom_tables( bool $should_update, int $post_id, WP_REST_Request $request ): bool {
		return $this->container->make( Controller::class )
		                       ->should_update_custom_tables( $should_update, $post_id, $request );
	}

	/**
	 * Commits deletion of related Pro data.
	 *
	 * @since 6.0.0
	 *
	 * @param int             $post_id The ID of the post that is being deleted.
	 * @param WP_REST_Request $request A reference to the Request object that triggered
	 *                                 the delete operation.
	 */
	public function commit_delete( int $post_id ): void {
		$this->container->make( Controller::class )->delete( $post_id );
	}

	/**
	 * Deletes the occurrence transients tied to this post_id.
	 *
	 * @since 6.0.0
	 *
	 * @param numeric $post_id The post ID to delete occurrence transients for.
	 */
	public function delete_occurrence_transients( $post_id ): void {
		$this->container->make( Controller::class )->delete_occurrence_transients( $post_id );
	}

	/**
	 * Redirects a Classic Editor request to either the real
	 * post when editing All Occurrences of a Recurring Event, or
	 * to a new post when applying edits to a Single Event or the
	 * Upcoming ones.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value: its side-effect
	 *              is the update of some super-global, request, values.
	 */
	public function redirect_classic_editor_request(): void {
		$this->container->make( Controller::class )->redirect_classic_editor_request();
	}

	/**
	 * Redirects the Request to the correct (All update) or new (Single and
	 * Upcoming updates) post.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the Request object that
	 *                                 will be both parsed for input information
	 *                                 and updated to redirect to a different post
	 *                                 if required.
	 *
	 * @return void The method will have the side-effect of updating the request information
	 *              in the Request object and in the reques super-globals.
	 */
	public function redirect_rest_request( WP_REST_Request $request ): void {
		$this->container->make( Controller::class )->redirect_request( $request );
	}


	/**
	 * Filters the list of meta keys that should be watched for changes to,
	 * then, trigger an update to the custom tables data to add the ones
	 * related to Event recurrence rules.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string> $meta_keys A list of the meta keys that will be
	 *                                 watched, as filtered by WordPress up
	 *                                 to this point.
	 *
	 * @return array<string> The filtered list of watched meta keys, including
	 *                       the one that will model and Event recurrence rules.
	 */
	public function track_pro_meta_keys( array $meta_keys ): array {
		$meta_keys[] = '_EventRecurrence';

		return $meta_keys;
	}

	/**
	 * Hook after the TEC controller has performed the custom tables updated to perform
	 * other updates that will require the base custom tables information to be set.
	 *
	 *
	 * @since 6.0.0
	 *
	 * @param bool            $updated    Whether the previous updates were performed correctly
	 *                                    by either TEC or previous filtering methods or not.
	 * @param int             $post_id    The Event post ID to perform the post custom tables
	 *                                    update operations on.
	 * @param WP_REST_Request $request    A reference to the Request object that generated the
	 *                                    update request.
	 *
	 * @return bool Whether the updates were applied correctly or not.
	 */
	public function commit_post_updates_after( bool $updated, int $post_id, WP_REST_Request $request ): bool {
		if ( ! $updated ) {
			// If the Event was not correctly updated, do not proceed.
			return $updated;
		}

		return $this->container->make( Controller::class )->commit_post_updates_after( $post_id, $request );
	}

	/**
	 * Saves the Event Recurrence meta in the WRITE part of the request.
	 *
	 * @since 6.0.0
	 *
	 * @param int                 $event_id The Event post ID.
	 * @param array<string,mixed> $data     The Event update data, as provided by the TEC API.
	 */
	public function save_recurrence_meta( int $event_id, array $data = [] ): void {
		$this->container->make( Controller::class )->save_recurrence_meta( $event_id, $data );
	}

	/**
	 * Prune the just updated Occurrences by sequence number, dropping any one belonging to
	 * the previous sequence.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The ID of the Event post the Occurrences are being saved for.
	 */
	public function prune_occurrences_by_sequence( int $post_id ): void {
		$this->container->make( Controller::class )->prune_occurrences_by_sequence( $post_id );
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
	public function get_occurrence_match( ?Occurrence $occurrence, Occurrence $result, int $post_id ): ?Occurrence {
		return $this->container->make( Events::class )->get_occurrence_match( $occurrence, $result, $post_id );
	}

	/**
	 * Hooks into post operations performed by WordPress during any phase of the creation,
	 * or update, process.
	 *
	 * @since 6.0.0
	 */
	private function hook_into_post_ops(): void {
		// Filters the unique post slug generated for an Occurrence.
		add_filter( 'wp_unique_post_slug', [ $this, 'unique_post_slug_for_occurrence' ], 10, 6 );
	}

	/**
	 * Filters the unique post slug generated, or set, for an Event Occurrence.
	 *
	 * @since 6.0.0
	 * @since 6.0.12 Removed strict typing from this public hook callback.
	 *
	 * @param string $slug          The post slug.
	 * @param int    $post_ID       Post ID.
	 * @param string $post_status   The post status.
	 * @param string $post_type     Post type.
	 * @param int    $post_parent   Post parent ID
	 * @param string $original_slug The original post slug.
	 *
	 * @return string The filtered unique post slug.
	 */
	public function unique_post_slug_for_occurrence( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ): string {
		// Some basic sanity check and type check validation.
		if ( ! is_string( $post_type ) || ! is_string( $original_slug ) || ! is_numeric( $post_ID ) ) {
			return $slug;
		}

		return $this->container->make( Post_Ops::class )
		                       ->get_occurrence_post_slug( $slug, (int) $post_ID, $post_type, $original_slug );
	}

	/**
	 * Redirect an Occurrence when deleted or trashed.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post          $post     A reference to the post object that is being deleted or trashed.
	 * @param WP_REST_Response $response A reference to the REST response generated for the delete or trash request.
	 * @param WP_REST_Request  $request  A reference to the REST request that triggered the post trash or deletion.
	 */
	public function redirect_deleted_occurrence( $post, $response, $request ) {
		if ( ! (
			$post instanceof WP_Post
			&& $response instanceof WP_REST_Response
			&& $response instanceof WP_REST_Request
		) ) {
			return;
		}

		$this->container->make( Controller::class )->redirect_deleted_occurrence( $post, $response, $request );
	}

	/**
	 * Filters the value of the `_EventRecurrence` meta before is written or after is read to
	 * add the off-pattern DTSTART flag.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed>|string $recurrence_meta The Event recurrence meta in the format used by the
	 *                                             `_EventRecurrence` meta value.
	 * @param int                 $post_id         The Event post ID.
	 *
	 * @return array<string,mixed>|string The Event recurrence meta updated to add the flag.
	 */
	public function add_off_pattern_start_flag( $recurrence_meta, int $post_id ) {
		if ( ! is_array( $recurrence_meta ) ) {
			return $recurrence_meta;
		}

		return $this->container->make( Controller::class )
		                       ->add_off_pattern_flag_to_meta_value( $recurrence_meta, $post_id );
	}

	/**
	 * Redirects a REST API delete request to the correct update method modifying the
	 * DELETE Request object.
	 *
	 * REST API delete requests will not specify an `id` parameter until later in the request
	 * handling process; working out the `id` from the request should be left to WordPress and
	 * filtering functions. This method is a workaround to ensure that the correct update method
	 * is called as soon as the `id` parameter of the request is known.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Response|WP_Error|null $response A reference to the REST response generated for the delete request.
	 * @param null                           $handler  The handler picked by WordPress for the delete request.
	 * @param WP_REST_Request|null           $request  A reference to the REST request that triggered the delete request.
	 *
	 * @return WP_REST_Response|WP_Error|null The REST response generated for the delete request, unmodified since
	 *                                        this filter is used as an action to modify the Request object.
	 */
	public function redirect_trash_delete_request( $response = null, $handler = null, $request = null ) {
		if ( ! $request instanceof WP_REST_Request ) {
			return $response;
		}

		$this->container->make( Controller::class )
		                ->redirect_delete_request( $request );

		// Return the response unmodified: we use this filter as an action.
		return $response;
	}

	/**
	 * Filters the available post actions to add the direct update links.
	 *
	 * @since 6.0.1
	 *
	 * @param array   $actions The available post actions.
	 * @param WP_Post $post    The post object.
	 *
	 * @return array The available post actions with the direct update links added.
	 */
	public function add_update_links( array $actions, WP_Post $post ): array {
		$more_actions = $this->container->make( Post_Actions::class )
			->get_post_update_links( $post );

		if ( ! count( $more_actions ) ) {
			return $actions;
		}

		return array_merge( $actions, $more_actions );
	}
}

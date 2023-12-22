<?php
/**
 * Provides methods commmon to all the Update Controllers.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers;

use DateTimeZone;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Editors\Block\Meta;
use TEC\Events_Pro\Custom_Tables\V1\Events\Converter\From_Blocks_Converter;
use TEC\Events_Pro\Custom_Tables\V1\Events\Rules\Date_Rule;
use TEC\Events_Pro\Custom_Tables\V1\RRule\Occurrence as RRule_Ocurrence;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Event_Recurrence;
use Tribe__Events__Main as TEC;
use Tribe__Events__Pro__Editor__Recurrence__Blocks;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta as Blocks_Meta;
use Tribe__Date_Utils as Dates;

/**
 * Trait Update_Controller_Methods
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers
 */
trait Update_Controller_Methods {
	use With_Event_Recurrence;

	/**
	 * The original Request ID.
	 *
	 * @since 6.0.0
	 *
	 * @var int|null
	 */
	protected $request_id;
	/**
	 * A reference to the Request object the Update Controller should act upon.
	 *
	 * @since 6.0.0
	 *
	 * @var WP_REST_Request $request
	 */
	private $request;

	/**
	 * A reference to the Occurrence instance the Update Controller should act upon.
	 *
	 * @since 6.0.0
	 *
	 * @var Occurrence
	 */
	private $occurrence;

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.0.0
	 */
	public function set_request( WP_REST_Request $request ) {
		$this->request = $request;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.0.0
	 */
	public function set_occurrence( Occurrence $occurrence ) {
		$this->occurrence = $occurrence;
	}

	/**
	 * Updates the REST API response object to make sure its `id` will match the request one.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Response $response A reference to the response object as produced from the REST API.
	 *
	 * @return WP_REST_Response A reference to the modified response object.
	 */
	public function reset_rest_request_id( WP_REST_Response $response ) {
		$data = $response->get_data();
		$response->set_data( array_merge( $data, [ 'id' => $this->request_id ] ) );

		return $response;
	}

	/**
	 * Runs a set of common step pre-requirement checks.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post ID to check.
	 *
	 * @return WP_Post|false Either an Event Post object reference, or `false` if
	 *                       the post does not exist or is not an Event post.
	 */
	protected function check_step_requirements( $post_id ) {
		if ( empty( $post_id ) || ! ( isset( $this->request, $this->occurrence ) ) ) {
			return false;
		}

		$post_id = Occurrence::normalize_id( $post_id );

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post || TEC::POSTTYPE !== $post->post_type ) {
			return false;
		}

		return $post;
	}

	/**
	 * Redirect a request to update from an RDATE to another Occurrence updating the request
	 * context as if the request was made from the target occurrence.
	 *
	 * @since 6.0.0
	 *
	 * @param Occurrence $rdate An Occurrence instance representing the RDATE.
	 * @param Occurrence $to    An Occurrence instance to redirect to.
	 *
	 * @return array<string,mixed> A map from modified request parameters to their values.
	 */
	protected function redirect_rdate_update_to_occurrence( Occurrence $rdate, Occurrence $to, int $post_id ): array {
		try {
			$request = $this->request;
			$meta = $request['meta'] ?? [];
			$has_blocks_meta = isset( $meta[ Blocks_Meta::$rules_key ] );
			$request_data = $this->get_request_start_end( $request );

			if ( $request_data === null ) {
				return [];
			}

			[ $request_start, $request_end, $request_timezone ] = $request_data;
			$start_date = Dates::immutable( $to->start_date, $request_timezone );
			$end_date = Dates::immutable( $to->end_date, $request_timezone );
			$event_recurrence = $this->get_event_recurrence_rules_from_request( $request );

			if ( ! is_array( $event_recurrence ) ) {
				// We lack the information required to proceed.
				return [];
			}

			$rdate_start = Dates::immutable( $rdate->start_date, $request_timezone );
			$rdate_occurrence = new RRule_Ocurrence( $rdate_start, $rdate->duration );
			$rdate_occurrence = Date_Rule::from_rrule_occurrence( $request_start, $request_end, $rdate_occurrence );
			$updated_rdate = new Date_Rule( $start_date, $end_date, $request_start, $request_end );
			$event_recurrence = $this->update_rdate_in_event_recurrence_with( $event_recurrence, $rdate_occurrence, $updated_rdate );
			// Set the `Event(Start|End)Date` we're redirecting to the ones used by the Occurrence we're redirecting to.
			$update_rule_start_end_date = static function ( array &$rule ) use ( $to ): void {
				$rule['EventStartDate'] = $to->start_date;
				$rule['EventEndDate'] = $to->end_date;
			};
			if ( isset( $event_recurrence['rules'] ) ) {
				array_walk( $event_recurrence['rules'], $update_rule_start_end_date );
			}
			if ( isset( $event_recurrence['exclusions'] ) ) {
				array_walk( $event_recurrence['exclusions'], $update_rule_start_end_date );
			}

			// Avoid running this operation if not required.
			$convert_recurrence_rules_to_blocks_format = static function () use ( $post_id, $event_recurrence ) {
				$converted = [];
				foreach ( $event_recurrence['rules'] as $rule ) {
					$converter = new Tribe__Events__Pro__Editor__Recurrence__Blocks( $rule );
					$converter->parse();
					$converted[] = $converter->get_parsed();
				}

				// Add off pattern flags here, so they will pass the diff check on meta update later.
				$blocks_meta = tribe( Meta::class );
				$converted   = $blocks_meta->add_off_pattern_dtstart_flag( $converted, $event_recurrence['rules'], $post_id );

				return json_encode( $converted );
			};

			// Finally redirect the Update controller action to the next occurrence.
			$this->occurrence = $to;
			$this->request_id = $to->provisional_id;

			// Set up the vars we should update in both Classic and Blocks editor.
			$utc = new DateTimeZone( 'UTC' );
			$overrides = [
				'id'                    => $to->provisional_id,
				'_EventStartDate'       => $start_date->format( Dates::DBDATETIMEFORMAT ),
				'_EventEndDate'         => $end_date->format( Dates::DBDATETIMEFORMAT ),
				'_EventStartDateUTC'    => $start_date->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ),
				'_EventEndDateUTC'      => $end_date->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ),
				'EventStartDate'        => $start_date->format( Dates::DBDATEFORMAT ),
				'EventEndDate'          => $end_date->format( Dates::DBDATEFORMAT ),
				'EventStartTime'        => $start_date->format( Dates::DBTIMEFORMAT ),
				'EventEndTime'          => $end_date->format( Dates::DBTIMEFORMAT ),
				'recurrence'            => $event_recurrence,
				Blocks_Meta::$rules_key => $has_blocks_meta ? $convert_recurrence_rules_to_blocks_format() : '',
				'post_ID'               => $to->provisional_id,
			];

			return $this->override_request_context( $request, $overrides );
		} catch ( \Exception $e ) {
			do_action( 'tribe_log', 'error', 'Failed to redirect RDATE request.', [
				'source'                         => __CLASS__,
				'slug'                           => 'rdate-redirection-failed',
				'post_id'                        => $rdate->post_id,
				'rdate_provisional_id'           => $rdate->provisional_id,
				'redirect_target_provisional_id' => $to->provisional_id,
				'error'                          => $e->getMessage(),
			] );

			return [];
		}
	}

	/**
	 * Saves an Event Recurrence meta converting it from the information contained
	 * in the Request.
	 *
	 * @since 6.0.0
	 *
	 * @param int             $post_id  The post ID to save the Recurrence meta for.
	 * @param WP_REST_Request $request  A reference to the Request object to read
	 *                                  the Recurrence meta from.
	 * @param bool            $override Whether to override the existing Recurrence meta or not.
	 *
	 * @return bool Whether the Request the update controller is handling had recurrence information
	 *              about the Recurrence meta for the specified event or not, and if that information
	 *              was correctly saved or not.
	 */
	private function save_rest_request_recurrence_meta( $post_id, $request, bool $override = false ) {
		$request_meta = $this->events->get_request_meta($request);

		if ( empty( $request_meta ) ) {
			// The request does not contain information about the post meta, do nothing.
			return false;
		}

		foreach (
			[
				'_EventStartDate',
				'_EventEndDate',
				'_EventTimezone',
				'_EventStartDateUTC',
				'_EventEndDateUTC'
			] as $date_meta_key
		) {
			// Ensure the date parameters are set before moving to the conversion and update.
			if ( isset( $request_meta[ $date_meta_key ] ) && ( $override || empty( get_post_meta( $post_id, $date_meta_key, true ) ) ) ) {
				update_post_meta( $post_id, $date_meta_key, $request_meta[ $date_meta_key ] );
			}
		}

		$recurrence_meta = $this->events->get_event_recurrence_format_meta( $post_id, $request );

		if ( false === $recurrence_meta ) {
			// The converted Recurrence meta is empty: delete the `_EventRecurrence` meta.
			delete_post_meta( $post_id, '_EventRecurrence' );

			return true;
		}

		$recurrence_meta = $this->add_off_pattern_flag_to_meta_value( $recurrence_meta, $post_id );

		// We do not check the result as `false` might just mean the value is the same.
		update_post_meta( $post_id, '_EventRecurrence', $recurrence_meta );

		return true;
	}

	/**
	 * Ensures the Request object will contain a `meta` request parameter if not set
	 * in the context of the Blocks Editor.
	 *
	 * In the context of the Blocks Editor, when a user does not update the Event post
	 * meta (e.g. the dates), then the `meta` parameter will not be set.
	 * Since we allow non-meta related changes (e.g. the post title) to be applied to
	 * Single and Upcoming Events, then we need the Occurrence dates to be set to correctly
	 * break (Single update) or split (Upcoming update) the new Event.
	 *
	 * @since 6.0.0
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Request A reference to the Request object.
	 */
	private function ensure_request_meta( WP_REST_Request $request ): WP_REST_Request {
		$request_meta = $this->events->get_request_meta( $request );

		if ( ! empty( $request_meta ) ) {
			$request->set_param( 'meta', $request_meta );
		}

		return $request;
	}

	/**
	 * Stores the original Request post ID to restore it in the context of a REST API response
	 * and avoid issues where a Request is correctly processed, but will return information for
	 * another.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post ID, either real or provisional, the Request is originally for.
	 */
	private function save_request_id( $post_id ) {
		$this->request_id = (int) $post_id;
		/*
		 * If we're in the context of a REST API request, then reset the `id` in the Response to
		 * the request one to avoid issues where the request comes for an ID and comes back with
		 * data for another.
		 */
		add_filter( 'rest_prepare_' . TEC::POSTTYPE, [ $this, 'reset_rest_request_id' ], 10, 1 );
	}

	/**
	 * Returns the start, end and timezone information read from an Editor request.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the Request object to read the
	 *
	 * @return array|null An array containing the start, end and timezone information or `null` if
	 *                    the required information could not be found in the request.
	 */
	private function get_request_start_end( WP_REST_Request $request ): ?array {
		$meta = $request['meta'] ?? [];
		$request_timezone = $meta['_EventTimezone'] ?? $request->get_param( 'EventTimezone' ) ?? null;
		$request_start_date = $meta['_EventStartDate'] ?? ( $request['EventStartDate'] . ' ' . $request['EventStartTime'] );
		$request_end_date = $meta['_EventEndDate'] ?? ( $request['EventEndDate'] . ' ' . $request['EventEndTime'] );

		if ( count( array_filter( [ $request_timezone, $request_start_date, $request_end_date ] ) ) !== 3 ) {
			// We lack the information required to proceed.
			return null;
		}

		$request_start = Dates::immutable( $request_start_date, $request_timezone );
		$request_end = Dates::immutable( $request_end_date, $request_timezone );

		return [ $request_start, $request_end, $request_timezone ];
	}

	/**
	 * Get the recurrence rules contained in a Classic or Blocks Editor request in the
	 * format used in the `_EventRecurrence` meta.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the Request object to read the recurrence
	 *                                 rules information from..
	 *
	 * @return array|null Either an array of recurrence rules, or `null` if the Request does not
	 *                    contain the information
	 */
	private function get_event_recurrence_rules_from_request( WP_REST_Request $request ): ?array {
		$meta = $request['meta'] ?? [];
		[ $request_start, $request_end ] = $this->get_request_start_end( $this->request );

		/*
		 * Depending on the Editor making the request pluck on convert the recurrence information to the
		 * `_EventRecurrence` format.
		 */
		if ( isset( $meta[ Blocks_Meta::$rules_key ] ) ) {
			// Blocks Editor request.
			$rules = (array) json_decode( $meta[ Blocks_Meta::$rules_key ], true );
			$converted = ( new From_Blocks_Converter( $rules, $request_start, $request_end ) )->to_event_recurrence_format();

			return [ 'rules' => $converted ];
		}

		// Classic Editor request.
		return $this->request->get_param( 'recurrence' );
	}

	/**
	 * Updates the context of an update HTTP request to override replace a map of values.
	 *
	 * The method will override the values that are present in the request context at the time
	 * it's invoked and will not blindly set values that were not present in the request.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request   A reference to the Request object to update.
	 * @param array           $overrides A map of values to override.
	 *
	 * @return array A map of the updated values.
	 */
	private function override_request_context( WP_REST_Request $request, array $overrides ): array {
		$updated = [];
		$meta = $request['meta'] ?? [];

		/*
		 * Overwrite only those vars that were originally set in the request object (REST)
		 * or context (HTTP super-globals).
		 */
		$request_meta_updates = [];
		foreach ( $overrides as $param => $value ) {
			if ( $request->has_param( $param ) ) {
				$request->set_param( $param, $value );
			}

			if ( isset( $meta[ $param ] ) ) {
				// Avoid running a costly array_merge in the loop.
				$request_meta_updates[ $param ] = $value;
			}

			if ( isset( $_REQUEST[ $param ] ) ) {
				$_REQUEST[ $param ] = $value;
				$updated[ $param ] = $value;
			}

			if ( isset( $_POST[ $param ] ) ) {
				$_POST[ $param ] = $value;
				$updated[ $param ] = $value;
			}
		}

		if ( $meta && count( $request_meta_updates ) ) {
			$this->request->set_param( 'meta', array_merge( $meta, $request_meta_updates ) );
		}

		return array_merge( $updated, $request_meta_updates );
	}
}
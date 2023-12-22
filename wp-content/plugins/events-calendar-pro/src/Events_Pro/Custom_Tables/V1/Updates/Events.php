<?php
/**
 * Class responsible for top level database transactions, regarding changes
 * to Events and their related database entries/tables.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events_Pro\Custom_Tables\V1\Admin\Notices\Provider as Notices_Provider;
use TEC\Events_Pro\Custom_Tables\V1\Duplicate\Duplicate as Duplicator;
use TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter\From_Event_Rule_Converter;
use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events_Pro\Custom_Tables\V1\Events\Recurrence;
use TEC\Events_Pro\Custom_Tables\V1\Events\Rules\Date_Rule;
use TEC\Events_Pro\Custom_Tables\V1\Migration\Patchers\Event_Recurrence_Meta_Patcher;
use TEC\Events_Pro\Custom_Tables\V1\Models\Occurrence as ECP_Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\RRule\RSet_Wrapper;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Blocks_Editor_Recurrence;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Date_Operations;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Event_Recurrence;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta as Blocks_Meta;
use Tribe__Events__Pro__Editor__Recurrence__Classic as Classic_Recurrence_Meta_Converter;
use Tribe__Events__Pro__Recurrence__Meta_Builder as Meta_Builder;
use Tribe__Timezones as Timezones;
use Tribe__Utils__Array as Arr;
use WP_Post;
use WP_REST_Request;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Transient_Occurrence_Redirector as Redirector;
use Tribe__View_Helpers as View_Helpers;

/**
 * Class Events
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */
class Events {
	use With_Event_Recurrence;
	use With_Date_Operations;
	use With_Blocks_Editor_Recurrence;

	/**
	 * A reference to the current Event duplication service implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Duplicator
	 */
	private $duplicator;

	/**
	 * A reference to the current Series to Events relationship handler implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Relationships
	 */
	private $relationships;

	/**
	 * A reference to the current Provision Post handler implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_Post
	 */
	private $provisional_post;

	/**
	 * A reference to the current redirection handler implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Redirector
	 */
	private $redirector;

	/**
	 * Events constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Duplicator       $duplicator       A reference to the current Event duplication service implementation.
	 * @param Relationships    $relationships    A reference to the current Series to Events relationships handler
	 *                                           implementation.
	 * @param Provisional_Post $provisional_post A reference to the current Provision Post handler implementation.
	 * @param Redirector       $redirector       A reference to the current redirection handler implementation.
	 */
	public function __construct(
		Duplicator $duplicator,
		Relationships $relationships,
		Provisional_Post $provisional_post,
		Redirector $redirector
	) {
		$this->duplicator       = $duplicator;
		$this->relationships    = $relationships;
		$this->provisional_post = $provisional_post;
		$this->redirector       = $redirector;
	}

	/**
	 * Prunes an Event Occurrences removing the ones that are not from the
	 * latest sequence.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post ID to prune the Occurrences for.
	 *
	 * @return int|false The number of occurrences deleted or false if missing sequence.
	 */
	public function prune_occurrences( int $post_id ) {
		$current_sequence = ECP_Occurrence::get_sequence( $post_id );

		/*
		 * A `NULL` sequence value will result in an empty sequence, preserving Single Events
		 * created by TEC that will set the `sequence` column to `NULL`.
		 */
		if ( empty( $current_sequence ) ) {
			return false;
		}

		/*
		 * TEC might insert, since it ignores the concept of `sequence`, a `NULL` value
		 * in the `sequence` column. That would make an Occurrence added by TEC, say
		 * in the context of a repository insertion, not be picked up by this pruning
		 * and would create, thus, "undelete-able" Occurrences.
		 */

		return Occurrence::where( 'post_id', $post_id )
		                 ->where_raw( '`sequence` IS NULL OR `sequence` < %d', $current_sequence )
		                 ->delete();
	}

	/**
	 * Moves an Event start date to the one of an Occurrence.
	 *
	 * @since 6.0.12 The event dates will now go into the CT1 tables.
	 * @since 6.0.0
	 *
	 * @param int        $post_id    The post ID of the Event to move the dates of.
	 * @param Occurrence $occurrence A reference to the Occurrence to move the Event to.
	 */
	public function move_event_date( int $post_id, Occurrence $occurrence ): void {
		update_post_meta( $post_id, '_EventStartDate', $occurrence->start_date );
		update_post_meta( $post_id, '_EventStartDateUTC', $occurrence->start_date_utc );
		update_post_meta( $post_id, '_EventEndDate', $occurrence->end_date );
		update_post_meta( $post_id, '_EventEndDateUTC', $occurrence->end_date_utc );

		$recurrence = get_post_meta( $post_id, '_EventRecurrence', true );
		if ( isset( $recurrence['rules'] ) ) {
			foreach ( $recurrence['rules'] as &$rule ) {
				$rule['EventStartDate'] = $occurrence->start_date;
				$rule['EventEndDate']   = $occurrence->end_date;
			}
		}
		unset( $rule );

		if ( isset( $recurrence['exclusions'] ) ) {
			foreach ( $recurrence['exclusions'] as &$exclusion ) {
				$exclusion['EventStartDate'] = $occurrence->start_date;
				$exclusion['EventEndDate']   = $occurrence->end_date;
			}
		}
		unset( $exclusion );
		if ( ! empty( $recurrence ) ) {
			update_post_meta( $post_id, '_EventRecurrence', $recurrence );
		}

		// Update the CT1 data, so we don't have an incongruent event state.
		$event_data = Event::data_from_post( $post_id );
		Event::upsert( [ 'post_id' ], $event_data );
	}

	/**
	 * If a count limit exists, modify the recurrence meta to decrement its count limit.
	 * Will not force a count limit or modify other types of event limit criteria.
	 *
	 * @since 6.0.8
	 *
	 * @param int $post_id      The post ID of the Event to update.
	 * @param int $decrement_by The amount to decrement on this event.
	 *
	 * @return array<string,mixed>|false The updated `_EventRecurrence` format contents,
	 *                                   or `false` if the update failed.
	 */
	public function decrement_event_count_limit_by( int $post_id, int $decrement_by ) {
		$post_id = Occurrence::normalize_id( $post_id );

		$recurrence = Recurrence::from_event( $post_id );

		if ( $recurrence === null || ! $recurrence->has_count_limit() ) {
			return false;
		}

		// Decrement the RRULE (should only be one)
		foreach ( $recurrence->get_rrules() as $key => $rule ) {
			$recurrence->set_rule(
				$key,
				$rule->set_count_limit( $rule->get_count_limit() - $decrement_by )
			);
		}

		$mutated_recurrence = $recurrence->to_event_recurrence();

		// We do not watch this update as `false` might also mean the value is the same.
		update_post_meta( $post_id, '_EventRecurrence', $mutated_recurrence );

		return $mutated_recurrence;
	}

	/**
	 * Updates a Recurring Event recurrence meta to update its limit to be an UNTIL one.
	 *
	 * @since 6.0.0
	 *
	 * @param int    $post_id The post ID of the Event to update.
	 * @param string $date    The date to set the UNTIL limit to.
	 *
	 * @return array<string,mixed>|false The updated `_EventRecurrence` format contents,
	 *                                   or `false` if the update failed.
	 */
	public function set_until_limit_on_event( int $post_id, string $date ) {
		$post_id = Occurrence::normalize_id( $post_id );

		$recurrence = (array) get_post_meta( $post_id, '_EventRecurrence', true );

		if ( ! isset( $recurrence['rules'] ) ) {
			return $recurrence;
		}

		try {
			$until_date = ( new DateTime( $date ) )->format( Dates::DBDATEFORMAT );
		} catch ( Exception $e ) {
			return false;
		}

		foreach ( $recurrence['rules'] as &$rule ) {
			if ( isset( $rule['custom']['type'] ) && $rule['custom']['type'] === 'Date' ) {
				// Do not apply the limit to RDATEs.
				continue;
			}

			$rule['end-type'] = 'On';
			$rule['end']      = $until_date;
			unset( $rule['end-count'] );
		}
		unset( $rule );

		// We do not watch this update as `false` might also mean the value is the same.
		update_post_meta( $post_id, '_EventRecurrence', $recurrence );

		return $recurrence;
	}

	/**
	 * Will remove an "RDATE" searched by the specified string from the _EventRecurrence meta.
	 *
	 * @since 6.0.7
	 *
	 * @param numeric $post_id The event post ID.
	 * @param string  $date    The date to remove RDATEs for.
	 *
	 * @return bool True when a match found and the meta is updated, false if no match found.
	 */
	public function remove_rdate_from_event( $post_id, string $date ): bool {
		$recurrence = (array) get_post_meta( $post_id, '_EventRecurrence', true );
		try {
			$remove_date = ( new DateTime( $date ) )->format( Dates::DBDATEFORMAT );
		} catch ( Exception $e ) {
			return false;
		}

		$match_date = static function ( string $date, array $rule ): bool {
			return $date === ( $rule['custom']['date']['date'] ?? null );
		};

		// Is there an RDATE matching the provided date?
		$rdate_match = Arr::usearch(
			$remove_date,
			array_filter( $recurrence['rules'] ?? [], [ $this, 'is_rdate' ] ),
			$match_date
		);

		if ( $rdate_match !== false ) {
			// Remove the RDATE and compact the recurrence rules.
			unset( $recurrence['rules'][ $rdate_match ] );
			$recurrence['rules'] = array_values( $recurrence['rules'] );
			update_post_meta( $post_id, '_EventRecurrence', $recurrence );

			return true;
		}

		return false;
	}

	/**
	 * Adds an EXDATE to an Event recurrence meta with care to do it only once.
	 *
	 * @since 6.0.0
	 *
	 * @param int    $post_id The post ID of the Event to update.
	 * @param string $date    The date and time to add an Exclusion for.
	 *
	 * @return array<string,mixed> The updated `_EventRecurrence` format contents,
	 */
	public function add_date_exclusion_to_event( int $post_id, string $date ): array {
		$recurrence = (array) get_post_meta( $post_id, '_EventRecurrence', true );
		$start_date = get_post_meta( $post_id, '_EventStartDate', true );
		$end_date   = get_post_meta( $post_id, '_EventEndDate', true );

		try {
			$exclusion_date = ( new DateTime( $date ) )->format( Dates::DBDATEFORMAT );
		} catch ( Exception $e ) {
			return $recurrence;
		}

		$match_date = static function ( string $date, array $rule ): bool {
			return $date === ( $rule['custom']['date']['date'] ?? null );
		};

		// Is there an RDATE matching the EXDATE?
		$rdate_match = Arr::usearch(
			$exclusion_date,
			array_filter( $recurrence['rules'] ?? [], [ $this, 'is_rdate' ] ),
			$match_date
		);

		$rules = array_filter( ( $recurrence['rules'] ?? [] ), [ $this, 'is_rrule' ] );
		$rule  = reset( $rules );
		// No need of an EXDATE if there is no RRULE to begin with.
		$needs_exdate = (bool) $rule;

		if ( $rdate_match !== false ) {
			// Remove the RDATE and compact the recurrence rules.
			unset( $recurrence['rules'][ $rdate_match ] );

			// Let's assume removing the RDATE is excluding the Occurrence.
			$needs_exdate = false;
		}

		if ( $rule ) {
			// There is an RRULE: we might need to add the EXDATE if the RRULE is occurs on that date.
			$rset_string  = From_Event_Rule_Converter::convert( $start_date, $end_date, $rule );
			$rset         = new RSet_Wrapper( $rset_string );
			$needs_exdate = (bool) $rset->get_occurrences_on_date( $exclusion_date, 1 );
		}

		if ( $needs_exdate ) {
			$exdate_match = Arr::usearch(
				$exclusion_date,
				array_filter( ( $recurrence['exclusions'] ?? [] ), [ $this, 'is_rdate' ] ),
				$match_date
			);

			$exrules         = array_filter( ( $recurrence['exclusions'] ?? [] ), [ $this, 'is_rrule' ] );
			$exrule          = reset( $exrules );
			$matching_exrule = false;

			if ( $exrule ) {
				// Is there an EXRULE that would exclude overlap the EXDATE?
				$exrule_string   = From_Event_Rule_Converter::convert( $start_date, $end_date, $exrule );
				$exrule_rset     = new RSet_Wrapper( $exrule_string );
				$matching_exrule = (bool) $exrule_rset->get_occurrences_on_date( $exclusion_date, 1 );
			}

			if ( $exdate_match === false && ! $matching_exrule ) {
				// Add the EXDATE to the exclusions.
				$exclusion                      = [
					'type'           => 'Custom',
					'custom'         =>
						[
							'date'      =>
								[
									'date' => $exclusion_date,
								],
							'same-time' => 'yes',
							'type'      => 'Date',
							'interval'  => 1,
						],
					'EventStartDate' => $start_date,
					'EventEndDate'   => $end_date,
				];
				$recurrence['exclusions']    [] = $exclusion;
			}
		}

		if ( isset( $recurrence['rules'] ) ) {
			$recurrence['rules'] = array_values( $recurrence['rules'] );
		}

		update_post_meta( $post_id, '_EventRecurrence', $recurrence );

		return $recurrence;
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
	 * @param int                 $post_id  The Event post ID.
	 * @param array<string,mixed> $data     The whole Event data, including the Recurrence
	 *                                      data.
	 *
	 * @return bool Whether the meta was saved or not.
	 */
	public function save_recurrence_meta( int $post_id, array $data ): bool {
		// Do not update recurrence meta on preview.
		if ( isset( $data['wp-preview'] ) && $data['wp-preview'] === 'dopreview' ) {
			return false;
		}

		if ( empty( $data['recurrence']['rules'] ) ) {
			// We do not check this value as `false` might just mean the meta was not there to begin with.
			delete_post_meta( $post_id, '_EventRecurrence' );

			return true;
		}

		try {
			$recurrence_meta = ( new Event_Recurrence_Meta_Patcher( $data['recurrence'], $post_id ) )->patch();
		} catch ( Exception $e ) {
			return false;
		}

		$recurrence_meta = $this->add_off_pattern_flag_to_meta_value( $recurrence_meta, $post_id );

		// We do not check the value here as `false` might just mean the value is the same.
		update_post_meta( $post_id, '_EventRecurrence', $recurrence_meta );

		return true;
	}

	/**
	 * Saves any of the Series and any related tables associated with this event.
	 *
	 * @since 6.0.0
	 *
	 * @param int             $post_id The post ID of the Event to save the Series relationships
	 *                                 for.
	 * @param WP_REST_Request $request A reference to the Request object that should
	 *                                 contain the Series relationship information.
	 *
	 * @return bool Whether the Series to Event relationship information was available
	 *              in the request and could be saved correctly, `false` otherwise.
	 */
	public function update_relationships( int $post_id, WP_REST_Request $request ): bool {
		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post || TEC::POSTTYPE !== $post->post_type ) {
			return false;
		}

		return $this->relationships->update( $post, $request );
	}

	/**
	 * Uses the Duplicate service to duplicate an Event.
	 *
	 * Note: the code will remove the duplicate flag that would indicate
	 * a user-triggered duplication.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post             $post      A reference to the Event post object to duplicate.
	 * @param array<string,mixed> $overrides A set of override arguments to control the duplication output.
	 *
	 * @return WP_Post|false Either a reference to the resulting Event post object,
	 *                       or `false` if the Event post could not be duplicated.
	 */
	public function duplicate( WP_Post $post, array $overrides = [] ) {
		$new_first_post = $this->duplicator->duplicate_event( $post, $overrides );
		// Remove the duplicate flag: this is not a user-triggered duplication operation.
		if ( $new_first_post instanceof WP_Post ) {
			delete_post_meta( $new_first_post->ID, Duplicator::$duplicate_key );
		}

		return $new_first_post;
	}

	/**
	 * Removes Occurrence transients tied to the post_id.
	 *
	 * Note: this method will run after the post has been deleted from the database,
	 * calls to `get_post` or similar functions will return nothing.
	 *
	 * @since 6.0.0
	 *
	 * @param numeric $post_id The post ID.
	 *
	 * @return bool If valid request to delete transients.
	 */
	public function delete_occurrence_transients( $post_id ): bool {
		if ( $this->provisional_post->is_provisional_post_id( $post_id ) ) {
			return false;
		}

		$occurrences = Occurrence::find_all( $post_id, 'post_id' );
		if ( $occurrences ) {
			$id_generator = tribe( ID_Generator::class );
			foreach ( $occurrences as $occurrence ) {
				$provisional_id = $id_generator->provide_id( $occurrence->occurrence_id );
				$this->redirector->remove_redirect_transient( $provisional_id );
			}
		}

		return true;
	}

	/**
	 * This will inspect a recurring event and pull out the specified occurrence, creating a new post for the occurrence
	 * and adjusting the original recurring event accordingly. This logic was moved from the Single update controller.
	 *
	 * @since 6.0.12
	 *
	 * @param Occurrence $occurrence The occurrence to separate into its own single event.
	 *
	 * @return false|int The new post ID created for the specified occurrence, or false on failure.
	 */
	public function detach_occurrence_from_event( Occurrence $occurrence ) {
		$post = get_post( $occurrence->post_id );

		// Duplicate the original Event as a single event.
		$ditch_unnecessary_values = static function ( $duplicate_args, $event ) {
			unset( $duplicate_args['meta_input']['_EventRecurrence'],
				$duplicate_args['meta_input'][ Blocks_Meta::$rules_key ],
				$duplicate_args['meta_input'][ Blocks_Meta::$exclusions_key ],
				$duplicate_args['meta_input'][ Blocks_Meta::$description_key ]
			);

			return $duplicate_args;
		};
		add_filter( 'tec_events_pro_custom_tables_v1_duplicate_arguments', $ditch_unnecessary_values, 10, 2 );
		$single_post = $this->duplicate(
			$post,
			// Keep the same post status as the original Event.
			[ 'post_status' => get_post_field( 'post_status', $post ) ]
		);
		remove_filter( 'tec_events_pro_custom_tables_v1_duplicate_arguments', $ditch_unnecessary_values );

		if ( ! $single_post instanceof WP_Post ) {
			do_action( 'tribe_log', 'error', 'Failed to create Event on Single update.', [
				'source'        => __CLASS__,
				'slug'          => 'duplicate-fail-on-single-trash',
				'occurrence_id' => $occurrence->occurrence_id,
			] );

			return false;
		}

		// Remove notices from watching the other events being updated
		tribe( Notices_Provider::class )->unregister();
		$post_id = $occurrence->post_id;

		$occurrence_id   = $occurrence->occurrence_id;
		$occurrence_date = $occurrence->start_date;

		$is_first = Occurrence::is_first( $occurrence_id );
		$is_last  = Occurrence::is_last( $occurrence_id );

		if ( $is_first ) {
			// Decrement count limit now that we are subtracting one event.
			$this->decrement_event_count_limit_by( $post_id, 1 );

			// Then Update the original Event to start on the second Occurrence.
			$second = Occurrence::where( 'post_id', $post_id )
			                    ->order_by( 'start_date', 'ASC' )
			                    ->offset( 1 )
			                    ->first();
			if ( $second instanceof Occurrence ) {
				$this->move_event_date( $post_id, $second );
			}
		} elseif ( $is_last ) {
			// Update the original Event Recurrence meta to end before the Occurrence date.
			$previous_occurrence = Occurrence::where( 'post_id', '=', $post_id )
			                                 ->order_by( 'start_date', 'DESC' )
			                                 ->where( 'start_date', '<', $occurrence->start_date )
			                                 ->first();

			if (
				$previous_occurrence instanceof Occurrence
				&& ! $this->set_until_limit_on_event( $post_id, $previous_occurrence->start_date )
			) {
				do_action( 'tribe_log', 'error', 'Failed to set UNTIL limit on original Event.', [
					'source'  => __CLASS__,
					'slug'    => 'set-until-limit-fail-on-single-update',
					'post_id' => $post_id,
				] );
			}
		}

		$is_rdate = $occurrence->is_rdate;
		if ( $is_rdate ) {
			// Let's verify we removed the RDATE from the meta correctly.
			$is_rdate = $this->remove_rdate_from_event( $post_id, $occurrence->start_date );
		}

		/**
		 * Don't need exclusion if an RDATE - we are simply removing it from the rule data.
		 * Don't need exclusion if first occurrence, we adjust the start date of the event.
		 * Don't need exclusion if the last occurrence, we adjust when this event ends.
		 */
		if ( ! $is_rdate && ! $is_first && ! $is_last ) {
			// Update the original Event Recurrence meta to add an exclusion on this event date.
			$this->add_date_exclusion_to_event( $post_id, $occurrence_date );
		}

		/*
		 * Assign the Occurrence to the single Event to give it a chance to
		 * recycle it.
		 */
		$this->transfer_occurrences_from_to(
			$post_id,
			$single_post->ID,
			'start_date = %s',
			$occurrence->start_date
		);

		// Fresh occurrence after database mutations above.
		$occurrence = Occurrence::find_by_post_id( $single_post->ID );
		if ( ! $occurrence instanceof Occurrence ) {
			do_action( 'tribe_log', 'error', 'Failed to locate our occurrence after moving to single post.', [
				'source'  => __METHOD__,
				'slug'    => 'failed-on-detach-occurrence',
				'post_id' => $single_post->ID,
			] );
			return false;
		}

		Occurrence::upsert( [ 'occurrence_id' ], [
			'occurrence_id'  => $occurrence->occurrence_id,
			'has_recurrence' => false
		] );

		// If not a first occurrence or is an RDATE, should align the dates.
		if ( ! $is_first || $occurrence->is_rdate ) {
			$this->move_event_date( $single_post->ID, $occurrence );
		}

		// The cache should be cleared after our above modifications.
		$this->provisional_post->clear_occurrence_cache( $occurrence_id );

		return $single_post->ID;
	}

	/**
	 * Deletes the Recurrence meta for the given Event.
	 *
	 * @since 6.0.1
	 * @since 6.0.12 Moved from Single controller. Will clear RSET from Event as well, now.
	 *
	 * @param int $post_id The post ID of the Event to delete the Recurrence meta for.
	 *
	 * @return void The Recurrence meta is deleted.
	 */
	public function delete_recurrence_meta( int $post_id ): void {
		delete_post_meta( $post_id, '_EventRecurrence' );
		delete_post_meta( $post_id, Blocks_Meta::$rules_key );
		delete_post_meta( $post_id, Blocks_Meta::$exclusions_key );
		delete_post_meta( $post_id, Blocks_Meta::$description_key );
		Event::upsert( [ 'post_id' ], [ 'post_id' => $post_id, 'rset' => '' ] );
	}

	/**
	 * Deletes any Pro associated data to this Event.
	 *
	 * @since TDB
	 *
	 * @param int $post_id The ID of the Event post the data is being deleted for.
	 *
	 * @return int|false    The number of database rows affected by the delete operation in the
	 *                      Series relationships table; the ones that might be affected in the
	 *                      posts and postmeta tables will not be counted. False if invalid operation.
	 */
	public function delete( int $post_id ) {
		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			// Not an Event post.
			return false;
		}

		$affected = 0;

		// If we are the last Event in a Series, let's delete it too.
		/** @var Series_Relationship $relationship */
		$relationship = Series_Relationship::find( $post_id, 'event_post_id' );

		// Bail if other process has deleted us already.
		if ( ! $relationship instanceof Series_Relationship ) {
			return $affected;
		}

		$series_post_id = $relationship->series_post_id;

		// Other Events on this Series?
		$other_related_events = Series_Relationship::where( 'event_post_id', '!=', $post_id )
		                                           ->where( 'series_post_id', $series_post_id )
		                                           ->count();

		// Delete the Relationship from the Series Relationship table.
		$affected += $relationship->delete();

		if ( ! $other_related_events ) {
			// Trash or delete the Series post, following the WordPress settings.
			wp_delete_post( $series_post_id );
		}

		return $affected;
	}

	/**
	 * Compares two set of Recurrence meta, in the format used in the
	 * `_EventRecurrence` meta value, to check if they are the equal or not.
	 *
	 * @since 6.0.0
	 *
	 * @param string|array<string,mixed>|null $a            The first set of Recurrence Rules to check.
	 * @param string|array<string,mixed>|null $b            The second set of Recurrence Rules to check.
	 * @param bool                            $ignore_dates Whether to compare the two sets ignoring start
	 *                                                      and end dates or not.
	 *
	 * @return bool Whether the two sets are equal or not.
	 */
	public function compare_recurrence_meta( $a, $b, bool $ignore_dates = true ): bool {
		if ( ! is_array( $a ) && is_array( $b ) ) {
			return false;
		}

		if ( $ignore_dates ) {
			$unset_dates = static function ( array &$rule ) {
				unset( $rule['EventStartDate'], $rule['EventEndDate'] );
			};
			array_walk( $a['rules'], $unset_dates );
			array_walk( $a['exclusions'], $unset_dates );
			array_walk( $b['rules'], $unset_dates );
			array_walk( $b['exclusions'], $unset_dates );
		}

		return $b === $a;
	}

	/**
	 * Transfers Occurrences from a post to another post allowing the specification
	 * of a WHERE condition.
	 *
	 * @since 6.0.0
	 * @since 6.0.12 Will now move `event_id` to the occurrence.
	 *
	 * @param int    $from_id                The post ID the Occurrences will be transferred from.
	 * @param int    $to_id                  The post ID the matching Occurrences should be transferred to.
	 * @param string $where                  A WHERE SQL clause in the format accepted by the `$wpdb->prepare` method.
	 * @param mixed  ...$where_values        An optional set of values that should be used to prepare the WHERE
	 *                                       part of the query.
	 *
	 * @see   wpdb::prepare() For the format to use to provide the `$where` argument.
	 */
	public function transfer_occurrences_from_to( int $from_id, int $to_id, string $where = '1 = 1', ...$where_values ): void {
		global $wpdb;
		$occurrences = Occurrences::table_name( true );
		$sequence    = ECP_Occurrence::get_sequence( $to_id );
		$to_event    = Event::find( $to_id, 'post_id' );
		if ( ! $to_event instanceof Event ) {
			do_action( 'tribe_log', 'error', 'Failed to locate Event to transfer occurrence to.', [
				'source' => __METHOD__,
				'slug'   => 'fail-on-transfer-occurrences',
				'to_id'  => $to_id,
			] );

			return;
		}
		$to_event_id = $to_event->event_id;
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $occurrences SET post_id = %d, sequence = %d, event_id = %d WHERE post_id = %d AND {$where}",
				$to_id,
				$sequence,
				$to_event_id,
				$from_id,
				...$where_values
			)
		);
	}

	/**
	 * Ensure the RDATEs are adjusted in the case of a split that may divide the RDATEs.
	 *
	 * The unchanged RDATEs will be split using the right start date as a dividing point: any RDATE before
	 * the right start date will stay on the left side of the split, any RDATE on or after the
	 * right side start date will go to the right side of the split.
	 * If an RDATE was changed by the user, then it will always go on the right side of the split, no
	 * matter what.
	 *
	 * @since 6.0.0
	 *
	 * @param int             $left_id    The ID of the post to transfer the RDATEs from.
	 * @param int             $right_id   The ID of the post to transfer the RDATEs to.
	 * @param string          $split_date The date of the first Occurrence of the right-side Event.
	 * @param WP_REST_Request $request    A reference to the Request object to read the Recurrence meta from.
	 *
	 * @return bool Whether any RDATE was transferred or not.
	 */
	public function split_rdates( int $left_id, int $right_id, string $split_date, WP_REST_Request $request ): bool {
		$previous_timezone = get_post_meta( $left_id, '_EventTimezone', true );

		// Read and normalize the RDATEs from the request.
		$request_recurrence_meta = $this->get_event_recurrence_format_meta( $right_id, $request ) ?? [];

		if ( ! isset( $request_recurrence_meta['rules'] ) ) {
			return false;
		}

		$request_rdates   = array_filter( $request_recurrence_meta['rules'], [ $this, 'is_rdate' ] );
		$request_timezone = $this->get_request_timezone( $request, $previous_timezone );
		$request_rdates   = array_map( function ( array $rdate ) use ( $request, $request_timezone ) {
			if ( ! isset( $rdate['EventStartDate'] ) && $request->get_param( 'EventStartDate' ) ) {
				$rdate['EventStartDate'] = Dates::immutable( $request->get_param( 'EventStartDate' ) . ' '
				                                             . $request->get_param( 'EventStartTime' ) )
				                                ->format( Dates::DBDATETIMEFORMAT );
			}
			if ( ! isset( $rdate['EventEndDate'] ) && $request->get_param( 'EventEndDate' ) ) {
				$rdate['EventEndDate'] = Dates::immutable( $request->get_param( 'EventEndDate' ) . ' '
				                                           . $request->get_param( 'EventEndTime' ) )
				                              ->format( Dates::DBDATETIMEFORMAT );
			}

			return $this->normalize_rule( $rdate, $request_timezone );
		}, $request_rdates );

		// Read and normalize the previous state of the RDATEs.
		$previous_recurrence_meta = get_post_meta( $left_id, '_EventRecurrence', true );
		$previous_rdates          = isset( $previous_recurrence_meta['rules'] ) ?
			array_filter( $previous_recurrence_meta['rules'], [ $this, 'is_rdate' ] )
			: [];
		$previous_rdates          = array_map( function ( array $rdate ) use ( $previous_timezone ) {
			return $this->normalize_rule( $rdate, $previous_timezone );
		}, $previous_rdates );

		// Shape the RDATEs to a uniform, comparable format.
		$shape                  = static function ( array $rdate ) {
			return Arr::shape_filter( $rdate, [
				'custom' => [
					'date' => [ 'date' ],
					'same-time',
					'?start-time',
					'?end-time',
					'?end-day'
				]
			] );
		};
		$shaped_previous_rdates = array_map( $shape, $previous_rdates );
		$rdates_to_evaluate     = array_filter( $request_rdates, static function ( array $rdate ) use ( $shaped_previous_rdates, $shape ) {
			return in_array( $shape( $rdate ), $shaped_previous_rdates, true );
		} );

		$split_date_immutable = Dates::immutable( $split_date, $request_timezone );

		// Fetch the left side start and end date: we'll need them to localize the RDATEs later.
		$left_start_date = get_post_meta( $left_id, '_EventStartDate', true );
		$left_end_date   = get_post_meta( $left_id, '_EventEndDate', true );

		// Any request RDATE that is not unchanged, is changed.
		$changed_request_rdates = array_diff_key( $request_rdates, $rdates_to_evaluate );

		// Changed RDATEs will always go on the right side.
		$right_side_rdates = $changed_request_rdates;
		$left_side_rdates  = [];

		// If we have deleted some RDATEs, add to be evaluated.
		if ( count( $request_rdates ) !== count( $previous_rdates ) ) {
			foreach ( $previous_rdates as $key => $rdate ) {
				// Are they deleted from the request? If so they may go to the left side.
				if ( ! isset( $request_rdates[ $key ] ) ) {
					$rdate_date_time = $this->get_rdate_date_time( $rdate );
					$rdate_immutable = Dates::immutable( $rdate_date_time, $request_timezone );

					if ( $rdate_immutable->getTimestamp() < $split_date_immutable->getTimestamp() ) {
						// It's after the split date: move to the right side.
						$rdate['EventStartDate'] = $left_start_date;
						$rdate['EventEndDate']   = $left_end_date;
						$left_side_rdates[]      = $rdate;
					}
				}
			}
		}

		// Split the unchanged RDATES: if before the split Occ. date it will go on the left, else it goes right.
		foreach ( $rdates_to_evaluate as $rdate ) {
			$rdate_date_time = $this->get_rdate_date_time( $rdate );
			$rdate_immutable = Dates::immutable( $rdate_date_time, $request_timezone );

			if ( $rdate_immutable->getTimestamp() >= $split_date_immutable->getTimestamp() ) {
				// It's after the split date: move to the right side.
				$right_side_rdates[] = $rdate;
			} else {
				/*
				 * It's before the split date: move to the left side.
				 * The RDATE comes from the right side request, the start and end will be off: correct this.
				 */
				$rdate['EventStartDate'] = $left_start_date;
				$rdate['EventEndDate']   = $left_end_date;
				$left_side_rdates[]      = $rdate;
			}
		}

		// Replace the left side RDATEs.
		$previous_recurrence_meta['rules'] = $this->replace_rdates_in_rules( $previous_recurrence_meta['rules'], $left_side_rdates );
		update_post_meta( $left_id, '_EventRecurrence', $previous_recurrence_meta );

		// Replace the right side RDATEs.
		$request_recurrence_meta['rules'] = $this->replace_rdates_in_rules( $request_recurrence_meta['rules'], $right_side_rdates );
		update_post_meta( $right_id, '_EventRecurrence', $request_recurrence_meta );

		// Update the request in the super-globals.
		if ( isset( $_POST['recurrence']['rules'] ) ) {
			$_POST['recurrence']['rules'] = $request_recurrence_meta['rules'];
		}

		// Update the Classic Editor request, if any.
		if ( $request->has_param( 'recurrence' ) ) {
			$request->set_param( 'recurrence', $request_recurrence_meta );
		}

		// Update the Blocks Editor request, if any.
		if ( $request->has_param( 'meta' ) ) {
			$meta = $request->get_param( 'meta' );
			if ( isset( $meta[ Blocks_Meta::$rules_key ] ) ) {
				$rules = is_string( $meta[ Blocks_Meta::$rules_key ] )
					? json_decode( $meta[ Blocks_Meta::$rules_key ], true )
					: $meta[ Blocks_Meta::$rules_key ];
				// Remove all the RDATEs from and replace them.
				$updated_rules = array_values( array_filter( $rules, static function ( array $rule ) {
					return isset( $rule['type'] ) && $rule['type'] !== 'single';
				} ) );
				array_push( $updated_rules, ...array_values( array_filter( array_map(
					[ $this, 'convert_recurrence_meta_rule_to_block_format' ], $right_side_rdates ) ) ) );
				$meta[ Blocks_Meta::$rules_key ] = json_encode( $updated_rules, JSON_UNESCAPED_SLASHES );
				$request->set_param( 'meta', $meta );
			}
		}

		return true;
	}

	/**
	 * Filter the TEC Occurrence match to return one matched by dates and post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param Occurrence|null $tec_occurrence Either a reference to an existing, matching, Occurrence
	 *                                        or `null`.
	 * @param Occurrence      $result         A reference to the Occurrence model instance that will be inserted
	 *                                        if a matching Occurrence cannot be found.
	 * @param int             $post_id        The post ID of the Event the Occurrence match is being searched for.
	 *
	 * @return Occurrence|false|null The reference to an existing Occurrence matching the one
	 *                               that should be inserted, `false` if no matching Occurrence was found,
	 *                               or `null` to indicate the match logic should not apply
	 *                               (e.g. it's a single Event).
	 */
	public function get_occurrence_match( ?Occurrence $tec_occurrence, Occurrence $result, int $post_id ) {
		if ( empty( get_post_meta( $post_id, '_EventRecurrence', true ) ) ) {
			// Not recurring, let TEC apply its default logic.
			return $tec_occurrence;
		}

		// Did we already build the set?
		$post_id_occurrences = wp_cache_get( $post_id, 'tec_occurrence_matches' );

		if ( false === $post_id_occurrences ) {
			/** @var \Generator<array<int|string>> $occurrences */
			$occurrences = Occurrence::where( 'post_id', '=', $post_id )
			                         ->output( ARRAY_A )
			                         ->all();

			// Extract the values from the Occurrences generator: the batched query logic will be applied.
			$post_id_occurrences = iterator_to_array( $occurrences );

			// Store the set to re-use it in the next run, will expire at the end of the current Request.
			wp_cache_set( $post_id, $post_id_occurrences, 'tec_occurrence_matches' );
		}

		// Look for a match in the set.
		$matches = wp_list_filter(
			$post_id_occurrences, [
				'start_date'     => $result->get_start_date_attribute(),
				'start_date_utc' => $result->get_start_date_utc_attribute(),
			]
		);

		if ( empty( $matches ) ) {
			if ( $tec_occurrence instanceof Occurrence ) {
				/*
				 * If no Occurrence matches the new one, then TEC should not try to reuse the
				 * first Occurrence.
				 */
				return null;
			}

			return $tec_occurrence;
		}

		// Build the Occurrence model instance from the pre-fetched set row.
		$match = new Occurrence( reset( $matches ) );

		return $match instanceof Occurrence ? $match : false;
	}

	/**
	 * Converts the Recurrence and Exclusion rules that might be contained specified by a Request
	 * from the format used by the Blocks Editor to the one used in the `_EventRecurrence` meta value.
	 *
	 * This method is a copy of the code used in ECP that offers some advantages over simply using that
	 * code: 1. it will not fire the same actions and filters; 2. it will not build and hook all the
	 * objects the original implementation would; 3. it will act on a Request + ID couple, not assuming
	 * the current request is for the post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param int                             $post_id               The Event post ID to convert the rules and
	 *                                                               exclusions for.
	 * @param string|array<string,mixed>|null $rules_meta_value      The rules meta value that will be used for
	 *                                                               the conversion, if not provided, then the
	 *                                                               current one will be read from the database.
	 * @param string|array<string,mixed>|null $exclusions_meta_value The exclusions meta value that will be used for
	 *                                                               the conversion, if not provided, then the
	 *                                                               current one will be read from the database.
	 * @param DateTimeImmutable|null          $dtstart               The Event DTSTART.
	 * @param DateTimeImmutable|null          $dtend                 The Event DTEND.
	 *
	 * @return array<string,mixed>|false Either the converted set of Recurrence rules and exclusions,
	 *                                                               if found and matched for the specified Event in the
	 *                                                               Request, `false` otherwise.
	 */
	public function convert_request_recurrence_meta(
		int $post_id,
		$rules_meta_value = null,
		$exclusions_meta_value = null,
		DateTimeImmutable $dtstart = null,
		DateTimeImmutable $dtend = null
	) {
		try {
			if ( is_null( $dtstart ) || is_null( $dtend ) ) {
				return false;
			}

			if ( null === $rules_meta_value ) {
				$rules_meta_value = get_post_meta( $post_id, Blocks_Meta::$rules_key, true );
			}

			$rules = is_string( $rules_meta_value ) ? json_decode( $rules_meta_value, true ) : $rules_meta_value;

			// Don't do anything if the block does not have any data.
			if ( empty( $rules ) ) {
				return false;
			}

			if ( null === $exclusions_meta_value ) {
				$exclusions_meta_value = get_post_meta( $post_id, Blocks_Meta::$exclusions_key, true );
			}

			$exclusions = is_string( $exclusions_meta_value ) ?
				json_decode( $exclusions_meta_value, true )
				: $exclusions_meta_value;

			// Normalize the same-time related fields to the request DTSTART and DTEND.
			$rules      = array_map( function ( array $rule ) use ( $dtstart, $dtend ) {
				return $this->normalize_blocks_format_rule_same_time( $rule, $dtstart, $dtend );
			}, $rules );
			$exclusions = array_map( function ( array $rule ) use ( $dtstart, $dtend ) {
				return $this->normalize_blocks_format_rule_same_time( $rule, $dtstart, $dtend );
			}, ( $exclusions ?? [] ) );

			$data = [
				'EventStartDate' => $dtstart->format( Dates::DBDATETIMEFORMAT ),
				'EventEndDate'   => $dtend->format( Dates::DBDATETIMEFORMAT ),
				'recurrence'     => [
					'rules'       => $this->parse_rules( $rules ),
					'exclusions'  => $this->parse_rules( $exclusions ),
					'description' => get_post_meta( $post_id, Blocks_Meta::$description_key, true ),
				],
			];

			$meta_builder = new Meta_Builder( $post_id, $data );

			return $meta_builder->build_meta();
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Converts all the rules from the Block into classic rules using the
	 * format converted.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed>|null $rules The Recurrence or Exclusion rules to parse.
	 *
	 * @return array<string,mixed> The parsed and converted Recurrence or Exclusion rules.
	 *
	 * @see   \Tribe__Events__Pro__Editor__Recurrence__Provider::parse_rules() for the original method.
	 */
	private function parse_rules( ?array $rules ): array {
		if ( null === $rules ) {
			return [];
		}
		$parsed = [];
		foreach ( $rules as $rule ) {
			$converter = new Classic_Recurrence_Meta_Converter( $rule );
			$converter->parse();
			$parsed[] = $converter->get_parsed();
		}

		return $parsed;
	}

	/**
	 * Returns a set of dates reflecting the fact the original request might have come from an Occurrence
	 * and would, thus, carry that Occurrence dates. The dates will be adjusted to apply to the Event
	 * first Occurrence dates, not the one actually being edited.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request    A reference to the Request that contains the dates. The
	 *                                    request values will be adjusted by the method.
	 * @param Occurrence      $occurrence A reference to the Occurrence the request originated from.
	 *
	 * @return array<string,mixed> The set of date updates to commit.
	 */
	public function adjust_request_dates( WP_REST_Request $request, Occurrence $occurrence ): array {
		if ( empty( $request->get_param( 'id' ) ) ) {
			return [];
		}

		if ( isset( $request['EventStartDate'], $request['EventStartTime'], $request['EventEndDate'], $request['EventEndTime'], $request['EventTimezone'] ) ) {
			// Classic Editor request.
			$request_start_meta = sprintf( '%s %s', $request['EventStartDate'], $request['EventStartTime'] );
			$request_end_meta   = sprintf( '%s %s', $request['EventEndDate'], $request['EventEndTime'] );
			$request_timezone   = Timezones::build_timezone_object( $request['EventTimezone'] );
		} elseif ( isset( $request['meta']['_EventStartDate'], $request['meta']['_EventEndDate'] ) ) {
			// Blocks Editor request
			$request_start_meta = $request['meta']['_EventStartDate'];
			$request_end_meta   = $request['meta']['_EventEndDate'];
			$request_timezone   = Timezones::build_timezone_object( $request['meta']['_EventTimezone'] );
		} else {
			// No elements to proceed.
			return [];
		}

		$request_id = (int) $request->get_param( 'id' );
		$post_id    = Occurrence::normalize_id( $request_id );

		$start_meta    = get_post_meta( $post_id, '_EventStartDate', true );
		$end_meta      = get_post_meta( $post_id, '_EventEndDate', true );
		$timezone_meta = get_post_meta( $post_id, '_EventTimezone', true );
		$timezone      = Timezones::build_timezone_object( $timezone_meta );

		/*
		 * Occurrences are generated from the combination of the Event start date and the
		 * Recurrence Rules and Exclusions. As such the "truth" is in the event dates.
		 */
		$event_start = Dates::build_date_object( $start_meta, $timezone );
		$event_end   = Dates::build_date_object( $end_meta, $timezone );

		[
			$request_start,
			$request_end
		] = $this->build_request_dates( $request_start_meta, $request_end_meta, $request_timezone );

		$occurrence_start = Dates::build_date_object( $occurrence->start_date, $timezone );
		$occurrence_end   = Dates::build_date_object( $occurrence->end_date, $timezone );

		$start_date_diff = $occurrence_start->diff( $request_start );
		$end_date_diff   = $occurrence_end->diff( $request_end );

		/**
		 * The `DateInterval::$inverted` property will be 1 if the time period is negative,
		 * 0 otherwise.
		 */
		if ( $start_date_diff instanceof DateInterval ) {
			$moved_event_start_date = clone $event_start;
			$moved_event_start_date->add( $start_date_diff );
		}

		if ( $end_date_diff instanceof DateInterval ) {
			$moved_event_end_date = ( clone $event_end );
			$moved_event_end_date->add( $end_date_diff );
		}

		// Only one occurrence, no need to speculate different dates.
		if ( Occurrence::where( 'post_id', $request_id )->count() === 1 ) {
			$adjusted_dates = [
				// Classic Editor format.
				'EventStartDate'  => $request_start->format( Dates::DBDATEFORMAT ),
				'EventStartTime'  => $request_start->format( Dates::DBTIMEFORMAT ),
				'EventEndDate'    => $request_end->format( Dates::DBDATEFORMAT ),
				'EventEndTime'    => $request_end->format( Dates::DBTIMEFORMAT ),
				// Blocks Editor, or REST API, format.
				'_EventStartDate' => $request_start->format( Dates::DBDATETIMEFORMAT ),
				'_EventEndDate'   => $request_end->format( Dates::DBDATETIMEFORMAT ),
			];
		} else {
			$adjusted_dates = [
				// Classic Editor format.
				'EventStartDate'  => $moved_event_start_date->format( Dates::DBDATEFORMAT ),
				'EventStartTime'  => $moved_event_start_date->format( Dates::DBTIMEFORMAT ),
				'EventEndDate'    => $moved_event_end_date->format( Dates::DBDATEFORMAT ),
				'EventEndTime'    => $moved_event_end_date->format( Dates::DBTIMEFORMAT ),
				// Blocks Editor, or REST API, format.
				'_EventStartDate' => $moved_event_start_date->format( Dates::DBDATETIMEFORMAT ),
				'_EventEndDate'   => $moved_event_end_date->format( Dates::DBDATETIMEFORMAT ),
			];
		}

		// Adjust the request object.
		foreach ( $adjusted_dates as $key => $value ) {
			$request[ $key ] = $value;
		}
		if ( isset( $request['meta'] ) ) {
			$request['meta'] = array_merge( $request['meta'], $adjusted_dates );
		}

		return $adjusted_dates;
	}

	/**
	 * Updates an Occurrence "in-place" to the data read from the post that controls it.
	 *
	 * @since 6.0.0
	 *
	 * @param int $occurrence_id The `occurrence_id` of the Occurrence ID to update. NOT a
	 *                           provisional post ID.
	 * @param int $post_id       The ID of the post source of the Occurrence updates.
	 *
	 * @return false|int Either the number of updated rows, or `false` if the update failed.
	 */
	public function update_occurrence_from_post( int $occurrence_id, int $post_id ) {
		$event_data = Event::data_from_post( $post_id );

		if ( empty( $event_data ) ) {
			return false;
		}

		return Occurrence::where( 'occurrence_id', '=', $occurrence_id )
		                 ->update( [
			                 'post_id'        => $post_id,
			                 'start_date'     => $event_data['start_date'],
			                 'end_date'       => $event_data['end_date'],
			                 'start_date_utc' => $event_data['start_date_utc'],
			                 'end_date_utc'   => $event_data['end_date_utc'],
			                 'duration'       => $event_data['duration']
		                 ] );
	}

	/**
	 * Updates a Recurring Event recurrence meta to update its limit to be an UNTIL one.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post ID of the Event to update.
	 * @param int $count   The value of the COUNT limit that should be set on all the
	 *                     RRULEs part of the Evnt RSET definition.
	 *
	 * @return array<string,mixed>|false The updated `_EventRecurrence` format contents,
	 *                                   or `false` if the update failed.
	 */
	public function set_count_limit_on_event( int $post_id, int $count ) {
		$post_id = Occurrence::normalize_id( $post_id );

		$recurrence = (array) get_post_meta( $post_id, '_EventRecurrence', true );

		if ( ! isset( $recurrence['rules'] ) ) {
			return $recurrence;
		}

		foreach ( $recurrence['rules'] as &$rule ) {
			if ( isset( $rule['custom']['type'] ) && $rule['custom']['type'] === 'Date' ) {
				// Do not apply the limit to RDATEs.
				continue;
			}

			$rule['end-type']  = 'After';
			$rule['end-count'] = (int) $count;
			unset( $rule['end'] );
		}
		unset( $rule );

		// We do not watch this update as `false` might also mean the value is the same.
		update_post_meta( $post_id, '_EventRecurrence', $recurrence );

		return $recurrence;
	}

	/**
	 * Given an RDATE in the format used in the `_EventRecurrence` meta value, return its date and time.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule             The RDATE rule, in the array format used in the `_EventRecurrence`
	 *                                              meta value.
	 *
	 * @return string The parsed RDATE date and time string in the `Y-m-d H:i:s` format.
	 */
	public function get_rdate_date_time( array $rule ): string {
		if ( isset( $rule['custom']['same-time'] ) && $rule['custom']['same-time'] === 'yes' ) {
			$event_start_time = DateTimeImmutable::createFromFormat( Dates::DBDATETIMEFORMAT, $rule['EventStartDate'] )
			                                     ->format( 'H:i:s' );

			return $rule['custom']['date']['date'] . ' ' . $event_start_time;
		}

		return $rule['custom']['date']['date'] . ' ' . $rule['custom']['start-time'];
	}

	/**
	 * Normalize a recurrence rule adjusting its values and formats.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The recurrence rule, in the format used in the
	 *                                  `_EventRecurrence` meta value.
	 * @param string|\DateTimezone The timezone string or object ot use.
	 *
	 * @return array<string,mixed> The recurrence rule, its `same-time`, and related entries,
	 *                             normalized.
	 */
	private function normalize_rule( array $rule, $timezone ): ?array {
		try {
			return Date_Rule::from_event_recurrence_format( $rule )->to_event_recurrence_format();
		} catch ( Exception $e ) {
			return $rule;
		}
	}

	/**
	 * Replace the RDATE rules found in the set of recurrence rules (in the `_EventRecurrence` meta value
	 * format) with a different set of RDATEs..
	 *
	 * @since 6.0.0
	 *
	 * @param array $rules_set  The set of rules to replace the RDATEs of.
	 * @param array $rdates_set The set of RDATEs to use for the replacemeet.
	 *
	 * @return array The updated rules set.
	 */
	public function replace_rdates_in_rules( array $rules_set, array $rdates_set ): array {
		$rules_set = array_values( array_filter( $rules_set, [ $this, 'is_rrule' ] ) );
		if ( ! empty( $rdates_set ) ) {
			array_push( $rules_set, ...$rdates_set );
		}

		return $rules_set;
	}

	/**
	 * Returns the Recurrence meta read from the Request object in the format used by the
	 * `_EventRecurrence` meta value.
	 *
	 * @since 6.0.0
	 *
	 * @param int                  $post_id The Event post ID to fetch the recurrence meta for.
	 * @param WP_REST_Request|null $request A reference to the request object to fetch the recurrence
	 *                                      meta from.
	 *
	 * @return bool|array<string,mixed> Either the event recurrence meta read from the request, or
	 *                                  `false` to indicate no recurrence meta could be found in the
	 *                                  request.
	 */
	public function get_event_recurrence_format_meta( int $post_id, WP_REST_Request $request = null ) {
		$request_meta    = $this->get_request_meta( $request );
		$recurrence_meta = $request !== null ? $request->get_param( 'recurrence' ) : null;

		if ( ! empty( $recurrence_meta ) ) {
			return $recurrence_meta;
		}

		if ( empty( $request_meta ) ) {
			// The request does not contain information about the post meta, do nothing.
			return false;
		}

		if ( ! tribe( 'events.editor.compatibility' )->is_blocks_editor_toggled_on() ) {
			return false;
		}

		$rules_key      = Blocks_Meta::$rules_key;
		$exclusions_key = Blocks_Meta::$exclusions_key;

		if ( ! isset( $request_meta[ $rules_key ], $request_meta[ $exclusions_key ] ) ) {
			// The request meta does not contain information about recurrence, do nothing.
			return true;
		}

		$dtstart    = Dates::immutable( $request_meta['_EventStartDate'], $request_meta['_EventTimezone'] );
		$dtend      = Dates::immutable( $request_meta['_EventEndDate'], $request_meta['_EventTimezone'] );
		$rules      = $request_meta[ $rules_key ] ?? [];
		$exclusions = $request_meta[ $exclusions_key ] ?? [];

		$recurrence_meta = $this->convert_request_recurrence_meta( $post_id, $rules, $exclusions, $dtstart, $dtend );

		return $recurrence_meta;
	}

	/**
	 * Fetches the meta from a request object.
	 *
	 * If the meta is not present at the root `meta` level, then
	 * the method will look up the `_tec_initial_meta` key to find
	 * that.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the Request object to fetch
	 *                                 the meta from.
	 *
	 * @return array<string,mixed>|null Either an array of meta read from the
	 *                                  request object, or `null` if no meta was
	 *                                  defined in the object.
	 */
	public function get_request_meta( WP_REST_Request $request ): ?array {
		if ( ! $request instanceof WP_REST_Request ) {
			return null;
		}

		// If the user did not change the post meta in any way, this might be empty.
		$request_meta = $request->get_param( 'meta' );

		if ( null === $request_meta ) {
			// Let's look into TEC initial meta to find the dates.
			$initial_meta = $request->get_param( '_tec_initial_meta' );
			$request_meta = is_array( $initial_meta ) && isset( $initial_meta['meta'] ) ?
				$initial_meta['meta']
				: null;
		}

		return $request_meta;
	}

	/**
	 * Updates an Event recurrence meta and custom tables data to ensure an Event
	 * is a Single one.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Event post ID to update.
	 *
	 * @return bool Whether the Event was updated correctly or not.
	 *
	 * @throws Exception If there's an error updating the Event.
	 */
	public function make_event_single( int $post_id ): bool {
		delete_post_meta( $post_id, '_EventRecurrence' );
		delete_post_meta( $post_id, Blocks_Meta::$rules_key );
		delete_post_meta( $post_id, Blocks_Meta::$exclusions_key );
		Event::upsert( [ 'post_id' ], Event::data_from_post( $post_id ) );
		$event = Event::find( $post_id, 'post_id' );

		clean_post_cache( $post_id );

		if ( ! $event instanceof Event ) {
			return false;
		}

		$event->occurrences()->save_occurrences();

		return true;
	}

	/**
	 * Reads and returns the timezone string from the request or falls back to
	 * the default value.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the Request object to read the timezone string from.
	 * @param string          $default The default value to return if the request does not contain the
	 *                                 timezone string information.
	 *
	 * @return string The timezone string read from the request or set to the default.
	 */
	private function get_request_timezone( WP_REST_Request $request, string $default ): string {
		$meta = $this->get_request_meta( $request );

		return $meta['_EventTimezone'] ?? $default;
	}

	/**
	 * Compares the limits of two `_EventRecurrence` format meta values to establish
	 * whether they are the same or not.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array<string,array>> $current  The current `_EventRecurrence` format meta value.
	 * @param array<string,array<string,array>> $previous The previous `_EventRecurrence` format meta value.
	 *
	 * @return bool Whether the two `_EventRecurrence` format meta values have the same limits or not.
	 */
	public function compare_interval_and_limit( array $current, array $previous ): bool {
		// Produces strings like `Daily-1-10` or `Weekly-2-3`.
		$get_rule_limit = static function ( array $rule ): string {
			$count = isset( $rule['end-type'], $rule['end-count'] ) && $rule['end-type'] === 'After' ?
				(int) $rule['end-count']
				: - 1;

			if ( $count === - 1 ) {
				return $count;
			}

			$type     = $rule['custom']['type'] ?? $rule['type'] ?? 'Custom';
			$interval = (int) ( $rule['custom']['interval'] ?? 1 );

			return sprintf( '%s-%d-%d', $type, $interval, $count );
		};

		$current_limits  = array_map( $get_rule_limit, $current['rules'] ?? [] )
		                   + array_map( $get_rule_limit, $current['exclusions'] ?? [] );
		$previous_limits = array_map( $get_rule_limit, $previous['rules'] ?? [] )
		                   + array_map( $get_rule_limit, $previous['exclusions'] ?? [] );

		return $current_limits === $previous_limits;
	}

	/**
	 * Builds the request dates with awareness of the request source: either the Blocks Editor
	 * or the Classic on.
	 *
	 * @since 6.0.0
	 *
	 * @param string       $start            The start date of the request.
	 * @param string       $end              The end date of the request.
	 * @param DateTimeZone $request_timezone The timezone that should be applied to the request dates.
	 *
	 * @return array<DateTime> An array with the start and end dates.
	 */
	private function build_request_dates( string $start, string $end, DateTimeZone $request_timezone ): array {
		$request_start = null;
		$request_end   = null;

		if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
			// Classic Editor request: the format used in the data will change depending on the user's settings.
			$date_format       = Dates::datepicker_formats( Dates::get_datepicker_format_index() );
			$time_format       = View_Helpers::is_24hr_format() ? 'H:i' : tribe_get_time_format();
			$datepicker_format = $date_format . ' ' . $time_format;
			try {
				$request_start = DateTime::createFromFormat( $datepicker_format, $start, $request_timezone );
				$request_end   = DateTime::createFromFormat( $datepicker_format, $end, $request_timezone );
			} catch ( Exception $e ) {
				// Nothing to do, let the following code try again.
			}
		}

		if ( ! ( $request_start && $request_end ) ) {
			// Blocks Editor request or failed processing: the format used in the data will always be the same.
			$request_start = Dates::build_date_object( $start, $request_timezone );
			$request_end   = Dates::build_date_object( $end, $request_timezone );
		}

		return [ $request_start, $request_end ];
	}
}

<?php


namespace TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy;

use DateInterval;
use DateTimeInterface;
use DateTimeImmutable;
use DateTime;
use Exception;
use TEC\Events\Custom_Tables\V1\Migration\Expected_Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Process;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Traits\With_String_Dictionary;
use TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error;
use TEC\Events_Pro\Custom_Tables\V1\Events\Converter\From_Rset_Converter;
use TEC\Events_Pro\Custom_Tables\V1\Events\Converter\From_Event_Recurrence_Converter;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series;
use TEC\Events_Pro\Custom_Tables\V1\RRule\Occurrence as Date_Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\RRule\RSet_Wrapper;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Blocks_Editor_Recurrence;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Date_Operations;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Event_Recurrence;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Ical_Strings;
use Tribe__Events__Main as TEC;
use TEC\Events_Pro\Custom_Tables\V1\Series\Relationship;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta as Blocks_Meta_Keys;
use Tribe__Timezones as Timezones;
use Tribe__Tracker as Modified_Fields_Tracker;
use WP_Error;
use WP_Post;

/**
 * Class Multi_Rule_Event_Migration_Strategy.
 *
 * @since   6.0.0
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy;
 */
class Multi_Rule_Event_Migration_Strategy implements Strategy_Interface {
	use With_Event_Recurrence;
	use With_Date_Operations;
	use With_String_Dictionary;
	use With_Ical_Strings;
	use With_Blocks_Editor_Recurrence;

	/**
	 * A reference to the Converter instance currently handling the conversion of
	 * an Event recurrence rules from the `_EventRecurrence` meta format to the
	 * iCalendar based one.
	 *
	 * @since 6.0.0
	 *
	 * @var From_Event_Recurrence_Converter
	 */
	private $from_event_recurrence_converter;

	/**
	 * A reference to the Converter instance currently handling conversion of
	 * iCalendar RSET strings to `_EventRecurrence` format.
	 *
	 * @since 6.0.0
	 *
	 * @var From_Rset_Converter
	 */
	private $from_rset_converter;

	/**
	 * A reference to the current Series to Event relationships handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Relationship
	 */
	private $relationships;

	/**
	 * A reference to the object representing the Event original DTSTART.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $dtstart;

	/**
	 * A reference to the object representing the Event original DTEND.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $dtend;

	/**
	 * The original Event duration, in seconds.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	private $duration;

	/**
	 * The content of the `_EventRecurrence` meta value for the Event.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,mixed>
	 */
	private $recurrence_meta;

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug() {
		return 'tec-ecp-multi-rule-strategy';
	}

	/**
	 * Builds the Event DTSTART object using the `_EventStartDate` and `_EventTimezone`
	 * meta information.
	 *
	 * @since 6.0.0
	 *
	 * @throws Migration_Exception If the Event is missing the required start date and timezone
	 *                             information.
	 */
	private function build_event_dtstart() {
		$event_timezone_string = get_post_meta( $this->post_id, '_EventTimezone', true );
		$event_start_date = get_post_meta( $this->post_id, '_EventStartDate', true );
		$event_end_date = get_post_meta( $this->post_id, '_EventEndDate', true );

		if ( 3 !== count( array_filter( [ $event_start_date, $event_end_date, $event_timezone_string ] ) ) ) {
			throw new Migration_Exception( 'The Event is missing the required start date or end date or timezone information.' );
		}

		$event_timezone = Timezones::build_timezone_object( $event_timezone_string );
		$this->dtstart = Dates::immutable( $event_start_date, $event_timezone );
		$this->dtend = Dates::immutable( $event_end_date, $event_timezone );
		$this->duration = $this->dtend->getTimestamp() - $this->dtstart->getTimestamp();
	}


	/**
	 * Multi_Rule_Event_Migration_Strategy constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param int  $post_id The post ID of the Event to migrate.
	 * @param bool $dry_run Whether the migration should actually commit information,
	 *                      or run in dry-run mode.
	 *
	 * @throws Migration_Exception If the post is not an Event or the Event is not Recurring
	 *                             and with at most one RRULE.
	 */
	public function __construct( $post_id, $dry_run ) {
		$this->post_id = $post_id;

		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			throw new Migration_Exception( 'Post is not an Event.' );
		}

		$recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

		if ( ! ( is_array( $recurrence_meta ) && isset( $recurrence_meta['rules'] ) ) ) {
			throw new Migration_Exception( 'Event Post is not recurring.' );
		}

		$rrules_count = $this->count_rrules( $recurrence_meta['rules'] );

		if ( 0 === $rrules_count ) {
			throw new Migration_Exception( 'Recurring Event has no RRULEs.' );
		}

		if ( 1 === $rrules_count ) {
			throw new Migration_Exception( 'Recurring Event has only one RRULE.' );
		}

		$this->recurrence_meta = $recurrence_meta;
		$this->build_event_dtstart();
		$this->from_event_recurrence_converter = new From_Event_Recurrence_Converter( $this->dtstart, $this->dtend );
		$this->from_rset_converter = tribe( From_Rset_Converter::class );
		$this->relationships = tribe( Relationship::class );

		$this->dry_run = $dry_run;
	}

	/**
	 * Clones an Event without triggering the update or insertion actions and filters.
	 *
	 * @since 6.0.0
	 *
	 * @param int                 $source_id The post ID of the Event to clone.
	 * @param array<string,mixed> $postarr   A map of additional post insertion arguments to use;
	 *                                       the format is the same used by the `wp_insert_post`
	 *                                       function.
	 *
	 * @return WP_Post A reference to the clone post object.
	 *
	 * @throws Migration_Exception If there's an issue while cloning the Event fields or meta.
	 */
	private function clone_event( $source_id, array $postarr = [], array $no_clone_mask = [] ) {
		$do_not_clone_fields = [
			'ID'                => false,
			'post_date'         => false,
			'post_date_gmt'     => false,
			'post_modfied'      => false,
			'post_modified_gmt' => false,
			'post_type'         => false,
		];
		$post_fields = array_diff_key( get_object_vars( get_post( $source_id ) ), $do_not_clone_fields );

		/**
		 * Filters the post fields, columns from the `wp_posts` table, that should be cloned.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string,mixed> A map from post fields to their values.
		 * @param int $source_id The source post ID.
		 */
		$post_fields = apply_filters( 'tec_events_custom_tables_v1_clone_post_fields', $post_fields, $source_id );

		$post_meta = get_post_meta( $source_id );

		if ( isset( $no_clone_mask['meta_input'] ) ) {
			$no_clone_mask = array_combine( $no_clone_mask['meta_input'], $no_clone_mask['meta_input'] );
			$post_meta = array_diff_key( get_post_meta( $source_id ), $no_clone_mask );
		}

		/**
		 * Filters the post meta, entries from the `wp_postmeta` table, that should be cloned.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string,array> A map from meta keys to sets of values.
		 * @param int $source_id The source post ID.
		 */
		$post_meta = apply_filters( 'tec_events_custom_tables_v1_clone_post_meta', $post_meta, $source_id );

		/*
		 * To avoid triggering all the hooks that would fire after an Event insertion,
		 * clone it to a different type; WordPress does not care about the type when inserting
		 * posts and will not complain.
		 * Since `meta_input` will not support multiple arrays, we'll insert the meta later.
		 */
		$clone_postarr = array_merge( $post_fields, [
			'post_type' => TEC::POSTTYPE . '_clone',
		], $postarr );

		$clone_id = wp_insert_post( $clone_postarr, true, false );

		if ( $clone_id instanceof WP_Error ) {
			throw new Migration_Exception( sprintf( "Failed to clone event $source_id post: %s", $clone_id->get_error_message() ) );
		}

		if ( isset( $postarr['meta_input'] ) ) {
			$post_meta = array_diff_key( $post_meta, $postarr['meta_input'] );
		}

		foreach ( $post_meta as $meta_key => $meta_values ) {
			foreach ( $meta_values as $meta_value ) {
				$meta_id = add_post_meta( $clone_id, $meta_key, $meta_value );
				if ( false === $meta_id ) {
					throw new Migration_Exception( "Failed to clone Event $source_id meta: " . $meta_key );
				}
			}
		}

		// Finally switch the post type to the correct one with a direct query.
		global $wpdb;
		$post_type_set = $wpdb->update( $wpdb->posts, [ 'post_type' => TEC::POSTTYPE ], [ 'ID' => $clone_id ], [ '%s' ], [ '%d' ] );

		if ( false === $post_type_set ) {
			throw new Migration_Exception( sprintf( "Failed to set cloned event $clone_id post type." ) );
		}

		// Clone categories
		$term_objects = wp_get_object_terms( $source_id, TEC::TAXONOMY );

		if ( $term_objects instanceof WP_Error ) {
			throw new Migration_Exception( sprintf( "Failed to clone event $source_id. Category terms: %s", $term_objects->get_error_message() ) );
		}

		$category_ids = wp_list_pluck( $term_objects, 'term_id' );
		$new_terms    = wp_set_post_terms( $clone_id, $category_ids, TEC::TAXONOMY );

		if ( $new_terms instanceof WP_Error ) {
			throw new Migration_Exception( sprintf( "Failed to clone event $source_id. Setting new category terms: %s", $new_terms->get_error_message() ) );
		}

		// Clone tags
		$term_objects = wp_get_object_terms( $source_id, 'post_tag' );

		if ( $term_objects instanceof WP_Error ) {
			throw new Migration_Exception( sprintf( "Failed to clone event $source_id. Tag terms: %s", $term_objects->get_error_message() ) );
		}

		$tag_ids   = wp_list_pluck( $term_objects, 'term_id' );
		$new_terms = wp_set_post_terms( $clone_id, $tag_ids, 'post_tag' );

		if ( $new_terms instanceof WP_Error ) {
			throw new Migration_Exception( sprintf( "Failed to clone event $source_id. Setting new tag terms: %s", $new_terms->get_error_message() ) );
		}

		// Flush the post cache to avoid the transitional post type from sticking.
		clean_post_cache( $clone_id );

		$post = get_post( $clone_id );

		if ( ! $post instanceof WP_Post && $clone_id === $post->ID ) {
			throw new Migration_Exception( "Failed to get cloned post $clone_id." );
		}

		return $post;
	}

	/**
	 * Converts and RSET object to an array in the `_EventRecurrence` format.
	 *
	 * @since 6.0.0
	 *
	 * @param RSet_Wrapper $rset    A reference to the RSET object to convert.
	 * @param int          $post_id The post ID of the Event to convert the data for.
	 *
	 * @return array<string,mixed> An array representing the Event recurrence information in
	 *                             the `_EventRecurrence` format.
	 *
	 * @throws \Exception If there's an issue in the conversion prcess.
	 */
	private function convert_rset_to_event_recurrence( RSet_Wrapper $rset, int $post_id ): array {
		$duration = $rset->get_duration() ?? $this->duration;
		$rset_arr = [ $duration => $rset->__toString() ];

		return $this->from_rset_converter->convert_to_event_recurrence( $rset_arr, $post_id );
	}

	/**
	 * Returns the indexed occurrence of a recurrence rule object.
	 *
	 * @since 6.0.0
	 *
	 * @param string $ical_string The iCalendar format string to return the Occurrence for.
	 * @param int    $offset      The offset of the Occurrence to return.
	 *
	 * @return Date_Occurrence|null The requested Occurrence object.
	 *
	 * @throws \Exception If there's any issue internally  building the RSET object
	 *                                        for the iCalendar string.
	 */
	private function get_rrule_occurrence( string $ical_string, int $offset ): ?Date_Occurrence {
		$check_rset = $this->get_rset_for_ical_string_dtstart( $ical_string, $this->dtstart, $this->duration );
		if ( $check_rset->get_duration() === null ) {
			// Ensure the RSET will have a duration.
			$check_rset->set_duration_in_seconds( $this->duration );
		}

		return $check_rset->offsetGet( $offset );
	}

	/**
	 * Returns whether an RSET will produce at least 1 Occurrence or not.
	 *
	 * @since 6.0.0
	 *
	 * @param RSet_Wrapper $rset A reference to the RSET to check.
	 *
	 * @return bool Whether the RSET will produce at least 1 Occurrence or not.
	 */
	private function rset_has_occurrences( RSet_Wrapper $rset ): bool {
		return $rset->isInfinite() || $rset->offsetExists( 0 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function apply( Event_Report $event_report ): Event_Report {
		$event_report->add_strategy( self::get_slug() )
		             ->set( 'is_single', false );

		$source_post = get_post( $this->post_id );

		if ( ! $source_post instanceof WP_Post ) {
			throw new Migration_Exception( "Could not get source post $this->post_id." );
		}

		// From the constructor method we know there will be 2+ RRULEs.
		$all_rules = $this->recurrence_meta['rules'];
		$rrules = array_values( array_filter( $all_rules, [ $this, 'is_rrule' ] ) );
		$rdates = array_values( array_filter( $all_rules, [ $this, 'is_rdate' ] ) );

		// Sort the rules to move the ones that would generate more Occurrences on top.
		$rrules = $this->sort_rules_by_count( $rrules );

		$ical_strings = $this->from_event_recurrence_converter
			->convert_recurrence_rules( $rrules, $this->duration, false );
		$ical_strings = reset( $ical_strings );

		$ical_strings = $this->apply_technical_never_ending_limit( $ical_strings );

		// Associate each RSET iCalendar string, DTSTART and RSET object.
		$tuples = $this->create_rset_tuples( $ical_strings, $source_post );

		// Add what RDATEs we have to the first RRULE.
		$tuples[0] = $this->add_rdates_to_tuple( $rdates, $tuples[0] );

		// Remove any tuple whose Occurrence count is 0.
		$tuples = array_values( array_filter( $tuples, function ( $tuple ) {
			return $this->rset_has_occurrences( $tuple['rset'] );
		} ) );

		// Rewind all RSETs.
		$this->rewind_tuples_rset( $tuples );

		// Handle collisions.
		if ( count( $tuples ) > 1 ) {
			$tuples = $this->handle_collisions( $tuples );
		}

		$exclusions = $this->recurrence_meta['exclusions'] ?? null;

		if ( ! empty( $exclusions ) ) {
			$tuples = $this->apply_exclusions_to_tuples( $exclusions, $tuples );
		}

		// Refresh the RSETs to include the collision EXDATEs, update COUNTs.
		$tuples = $this->refresh_tuples_dtstart( $tuples );

		// Remove any RSET that, after collisions and updated DTSTART, has no Occurrences.
		$tuples = array_values( array_filter( $tuples, function ( $tuple ) {
			return $this->rset_has_occurrences( $tuple['rset'] );
		} ) );

		// Further reduce the tuples transforming those that would produce 1 Occurrence into RDATEs of the first RSET.
		$tuples = $this->reduce_tuples( $tuples );

		// Backup the Event original `_EventRecurrence` meta to have a state to go back to.
		update_post_meta( $this->post_id, '_EventRecurrenceBackup', $this->recurrence_meta );

		$series = Series::vinsert( [ 'title' => $source_post->post_title ] );
		$series_post = get_post( $series );

		if ( ! $series_post instanceof WP_Post ) {
			throw new Migration_Exception( 'Failed to get Series post.' );
		}

		$event_report->add_series( $series_post );

		/*
		 * The first created Event is not really created: it's the original Event, its `_EventRecurrence`
		 * updated in place and the Occurrences created for it.
		 */

		$clone_postarr = [
			'meta_input' => [ Process::EVENT_CREATED_BY_MIGRATION_META_KEY => 1 ]
		];
		$no_clone_mask = [
			'meta_input' => [
				Modified_Fields_Tracker::$field_key,
				'_EventRecurrence',
				Blocks_Meta_Keys::$rules_key,
				Blocks_Meta_Keys::$exclusions_key,
				Event_Report::META_KEY_MIGRATION_LOCK_HASH,
				Event_Report::META_KEY_MIGRATION_PHASE,
			]
		];
		$UTC = new \DateTimeZone( 'UTC' );
		// If the Event was created using the Blocks Editor, let's make sure to migrate those rules too.
		$update_blocks_meta = ! empty( get_post_meta( $this->post_id, Blocks_Meta_Keys::$rules_key, true ) );
		// Set up the flags that will be used to discriminate the all-day flag application.
		$original_is_all_day = tribe_is_truthy( get_post_meta( $this->post_id, '_EventAllDay', true ) );
		$all_day_start_time = tribe_beginning_of_day( 'today', 'H:i:s' );
		$all_day_end_time = tribe_end_of_day( 'today', 'H:i:s' );

		foreach ( $tuples as $tuple ) {
			$ical_string = $tuple['ical_string'];

			$ical_string = $this->remove_ical_string_technical_never_ending_limit( $ical_string );

			$post = $tuple['post'];
			$dtstart = $tuple['dtstart'];
			/** @var RSet_Wrapper $rset */
			$rset = $tuple['rset'];
			if ( ! $post ) {
				if ( $rset->get_duration() === null ) {
					$rset->set_duration_in_seconds( $tuple['duration'] );
				}

				$first_occurrence = $rset->offsetGet( 0 );

				if ( $first_occurrence === null ) {
					continue;
				}

				$clone_postarr['meta_input']['_EventStartDate'] = $first_occurrence->start()->format( 'Y-m-d H:i:s' );
				$clone_postarr['meta_input']['_EventEndDate'] = $first_occurrence->end()->format( 'Y-m-d H:i:s' );
				$clone_postarr['meta_input']['_EventStartDateUTC'] = $first_occurrence->start()->setTimezone( $UTC )->format( 'Y-m-d H:i:s' );
				$clone_postarr['meta_input']['_EventEndDateUTC'] = $first_occurrence->end()->setTimezone( $UTC )->format( 'Y-m-d H:i:s' );
				$clone_postarr['meta_input']['_EventDuration'] = $rset->get_duration();
			}

			$instance_no_clone_mask = $no_clone_mask;
			$duration               = $tuple['duration'] ?? $rset->get_duration();
			$dtend                  = $dtstart->add( new DateInterval( "PT{$duration}S" ) );

			/*
			 * If not an all day event, make sure we don't clone the all day flag from the source post.
			 * Apply the logic only if the original Event was all-day to avoid flagging as such an event that just
			 * happens to look like one but it's not in the user intention.
			 */
			if ( ! (
				$original_is_all_day
				&& $dtstart->format( 'H:i:s' ) === $all_day_start_time
				&& $dtend->format( 'H:i:s' ) === $all_day_end_time
			)
			) {
				$instance_no_clone_mask['meta_input'][] = '_EventAllDay';
			}
			$clone                 = $post ?? $this->clone_event( $this->post_id, $clone_postarr, $instance_no_clone_mask );
			$occurrences_generated = $this->upsert_event( $clone->ID, $ical_string, $dtstart, $series_post->ID, $update_blocks_meta );
			$event_report->add_created_event( $clone, $occurrences_generated );
		}

		return $event_report;
	}

	/**
	 * Moves an Event start date and, with it, the Event end date.
	 *
	 * @since 6.0.0
	 *
	 * @param int               $post_id     The post ID of the Event to move the start date for.
	 * @param DateTimeImmutable $new_dtstart A reference to the Event new DSTART.
	 *
	 * @throws Migration_Exception If the event meta cannot be correctly updated.
	 */
	private function move_event_start_date( $post_id, DateTimeImmutable $new_dtstart ): void {
		$utc = Timezones::build_timezone_object( 'UTC' );
		$timezone = $this->dtstart->getTimezone();
		$current_dtstart = Dates::immutable( get_post_meta( $post_id, '_EventStartDate', true ), $timezone );

		if ( $current_dtstart == $new_dtstart ) {
			return;
		}

		$current_dtend = Dates::immutable( get_post_meta( $post_id, '_EventEndDate', true ), $timezone );
		$current_duration = $current_dtend->getTimestamp() - $current_dtstart->getTimestamp();
		$new_dtend = $new_dtstart->add( new DateInterval( "PT{$current_duration}S" ) );
		$format = Dates::DBDATETIMEFORMAT;

		$updates = [
			'_EventStartDate'    => $new_dtstart->format( $format ),
			'_EventEndDate'      => $new_dtend->format( $format ),
			'_EventStartDateUTC' => $new_dtstart->setTimezone( $utc )->format( $format ),
			'_EventEndDateUTC'   => $new_dtend->setTimezone( $utc )->format( $format ),
		];

		foreach ( $updates as $meta_key => $meta_value ) {
			if ( ! update_post_meta( $post_id, $meta_key, $meta_value ) ) {
				throw new Migration_Exception( "Failed to update post $post_id $meta_key value." );
			}
		}
	}

	/**
	 * Upserts an Event data in the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * @param int               $post_id            The ID of the Event the data is being upserted for.
	 * @param string            $ical_string        The iCalendar format string that should be used,
	 *                                              together with the DTSTART, to generate the Event
	 *                                              Occurrences.
	 * @param DateTimeImmutable $dtstart            A reference to the Event DTSTART object.
	 * @param int               $series             The post ID of the Series to relate the Event with.
	 * @param bool              $update_blocks_meta Whether to update the Blocks meta for the Event.
	 *
	 * @return int The post ID of the upserted Event.
	 *
	 * @throws Migration_Exception If the Event custom tables data cannot be upserted correctly.
	 */
	private function upsert_event( int $post_id, string $ical_string, DateTimeImmutable $dtstart, int $series, bool $update_blocks_meta ): int {
		$rset = $this->get_rset_for_ical_string_dtstart( $ical_string, $dtstart );
		$this->move_event_start_date( $post_id, $dtstart );
		$event_recurrence_meta = $this->convert_rset_to_event_recurrence( $rset, $post_id );
		update_post_meta( $post_id, '_EventRecurrence', $event_recurrence_meta );

		// If the Event had blocks format Rules, update those too.
		if ( $update_blocks_meta ) {
			$this->update_blocks_format_recurrence_meta( $post_id, $event_recurrence_meta );
		}

		$upserted = Event::upsert( [ 'post_id' ], Event::data_from_post( $post_id ) );
		if ( $upserted === false ) {
			$errors       = Event::last_errors();
			$error_string = implode( '. ', $errors );
			$text         = tribe( String_Dictionary::class );

			$message = sprintf(
				$text->get( 'migration-error-k-upsert-failed' ),
				$this->get_event_link_markup( $this->post_id ),
				$error_string,
				'<a target="_blank" href="https://evnt.is/migrationhelp">',
				'</a>'
			);

			throw new Expected_Migration_Exception( $message );
		}

		$event = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			throw new Migration_Exception( "Created Event $post_id Event model not found." );
		}

		$this->relationships->with_event( $event, [ $series ] );

		$event->occurrences()->save_occurrences();

		return Occurrence::where( 'post_id', '=', $post_id )->count();
	}

	/**
	 * Handles the collisions between recurrence rules by adding EXDATEs where duplicates are found.
	 *
	 * @since 6.0.0
	 *
	 * @param array $input_tuples A set of tuples to handle the collisions for, each one describing a recurrence
	 *                            rule converted to iCalendar format.
	 *
	 * @return array The tuples, modified to add EXDATEs where required.
	 */
	private function handle_collisions( array $input_tuples ) {
		/**
		 * Tuples that have a different start or end will never collide: handle collisions only among the
		 * ones with the same start time and duration.
		 */
		$groups = array_reduce( $input_tuples, static function ( array $carry, array $tuple ): array {
			$key = $tuple['start_time'] . '_' . $tuple['duration'];
			$carry[ $key ][] = $tuple;

			return $carry;
		}, [] );
		// Check and update only the groups that have more that one RRULE in them; let the others.
		[ $to_update, $to_let ] = array_reduce( $groups, static function ( array $carry, array $group ): array {
			if ( count( $group ) > 1 ) {
				$carry[0][] = $group;
			} else {
				$carry[1][] = reset( $group );
			}

			return $carry;
		}, [ 0 => [], 1 => [] ] );

		$updated = [];
		foreach ( $to_update as $tuples ) {
			// Create a copy as the loop will remove tuples from the array.
			$loop_tuples = $tuples;
			foreach ( $loop_tuples as $k => &$loop_tuple ) {
				$loop_tuple['tuple_index'] = $k;
			}
			unset( $loop_tuple );

			/*
			 * Frog-leap through the RSETs adding an EXDATE to each RSET whose head (its
			 * current Occurrence) matches the current Occurrence of a preceding RSET.
			 */
			while ( count( $loop_tuples ) !== 1 ) {
				$currents = array_map( static function ( RSet_Wrapper $rset ) {
					return $rset->current();
				}, array_column( $loop_tuples, 'rset' ) );
				$head = min( ...$currents );
				$head_index = array_search( $head, $currents, true );
				$head_rset = $loop_tuples[ $head_index ]['rset'];

				// Apply an EXDATE to any RSET that has an Occurrence on the current min; exclude the current min one.
				foreach ( array_diff_key( $loop_tuples, [ $head_index => 0 ] ) as $tuple ) {
					if ( $tuple['rset']->current() != $head ) {
						continue;
					}

					// The `rset` key is a reference to the same object, accessing it from here is fine.
					$tuple['rset']->addExDate( $head );
					// Store the collision EXDATEs separately to later update the COUNT.
					$tuples[ $tuple['tuple_index'] ]['collision_exdates'][] = $head;
				}

				// Move the head element Occurrence forward.
				$head_rset->next();

				if ( empty( $head_rset->current() ) ) {
					// The RSET ran out of Occurrences, remove it from the Loop.
					$loop_tuples = array_values( array_diff_key( $loop_tuples, [ $head_index => true ] ) );

					if ( count( $loop_tuples ) === 1 ) {
						// No need to keep looping if only one RSET remains.
						break;
					}
				}
			}

			// Clean up the collision EXDATEs to make them unique.
			array_walk( $tuples, function ( &$tuple ) {
				$tuple['collision_exdates'] = $this->unique_dates( $tuple['collision_exdates'] );
			} );

			$updated[] = $tuples;
		}

		return array_merge( $to_let, ...$updated );
	}

	/**
	 * Returns a list of tuples, each one created from a recurrence rule resulting iCalendar format string anc
	 * containing details about the rule converted information.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string> $ical_strings A list of recurrence rules, each converted in an iCalendar format string.
	 * @param WP_Post       $source_post  A reference to the migration source post.
	 *
	 * @return array A list of sorted and converted tuples containing all the data about a recurrence rule in CT1
	 *               format.
	 *
	 * @throws Migration_Exception If there's any issue converting the iCalendar strings.
	 */
	private function create_rset_tuples( array $ical_strings, WP_Post $source_post ) {
		$ical_strings_to_contains_dstart_map = $this->sort_ical_strings( $ical_strings );
		$rset_tuples = [];
		$i = 0;
		$source_dtstart_Ymd = $this->dtstart->format('Y-m-d');
		foreach ( $ical_strings_to_contains_dstart_map as $ical_string => $includes_dtstart ) {
			if ( 0 === $i ++ ) {
				// The first RRULE will get the original DTSTART and post.
				$rset = $this->get_rset_for_ical_string_dtstart( $ical_string, $this->dtstart );
				$tuple_dstart = $this->dtstart;
				$tuple_duration = $this->duration;
				$post = $source_post;
			} else {
				$next_occurrrence = null;

				$offset = (int) $includes_dtstart;
				$rset = $this->get_rset_for_ical_string_dtstart( $ical_string, $this->dtstart );

				if ( ! $includes_dtstart ) {
					// Update the rule DTSTART to move it to its first natural Ocurrence.
					$next_occurrrence = $rset->offsetGet( 0 );
				} else {
					/*
					 * The original DTSTART is "taken" by the first RRULE: move the rule DSTART
					 * to its next Occurrence. Since we're skipping an Occurrence, the DSTART one,
					 * we reduce the COUNT by 1.
					 */
					$next_occurrrence = $rset->offsetGet( 1 );
					$ical_string = $this->update_ical_string_count_by( $ical_string, - 1 );
				}

				if ( null === $next_occurrrence ) {
					// If the RRULE would not produce an Occurrence, we can just drop it.
					continue;
				}

				/*
				 * Legacy does not allow any Occurrence on the first day that is not from the first
				 * RRULE, so we need to move the DSTART to the next Occurrence for each RRULE that
				 * would generate an Occurrence on the first day.
				 */
				$first_day_offset = 0;
				while ( $next_occurrrence->format_start( 'Y-m-d' ) === $source_dtstart_Ymd ) {
					$next_occurrrence = $rset->offsetGet( $offset + ++ $first_day_offset );
				}
				if ( $first_day_offset ) {
					$ical_string = $this->update_ical_string_count_by( $ical_string, - $first_day_offset );
				}

				$new_dstart_string = $this->build_dstart_string( $next_occurrrence->start() );
				$new_dtend_string = $this->build_dtend_string( $next_occurrrence->end() );
				$ical_string = $this->set_ical_string_dt_attribute( $ical_string, 'DTSTART', $new_dstart_string );
				$ical_string = $this->set_ical_string_dt_attribute( $ical_string, 'DTEND', $new_dtend_string );
				$rset = $this->get_rset_for_ical_string_dtstart( $ical_string, $next_occurrrence );
				$post = null;
				$tuple_dstart = $next_occurrrence->start();
				$tuple_duration = $rset->get_duration() ?? $next_occurrrence->get_duration();
			}

			$rset->rewind();
			$rset_tuples[] = [
				'ical_string'       => $ical_string,
				'dtstart'           => Dates::immutable( $tuple_dstart ),
				'rset'              => $rset,
				'post'              => $post,
				'start_time'        => $tuple_dstart->format( 'His' ),
				'duration'          => $tuple_duration,
				'collision_exdates' => [],
			];
		}

		return $rset_tuples;
	}

	/**
	 * Calls the Iterator rewind method on each tuple RSET object.
	 *
	 * @since 6.0.0
	 *
	 * @param array $rset_tuples A set of tuples to rewind the iterators for.
	 *
	 * @return void The method does not return any value and will have the side
	 *              effect of rewinding each tuple RSET iterator.
	 */
	private function rewind_tuples_rset( array $rset_tuples ) {
		foreach ( array_column( $rset_tuples, 'rset' ) as $rset ) {
			$rset->rewind();
		}
	}

	/**
	 * Sort the RRULEs to have the one that contain the DTSTART on top.
	 *
	 * @param array<string> $ical_strings A list of converted iCalendar format strings.
	 *
	 * @return array<string,bool> A sorted map from iCalendar format strings to a boolean value
	 *                            indicating whether they contain the DTSTART or not.
	 *
	 * @throws Migration_Exception If there's any issue converting the iCalendar format strings.
	 */
	private function sort_ical_strings( array $ical_strings ) {
		$map = array_combine(
			$ical_strings,
			array_map( function ( $ical_string ) {
				$includes_dtstart = (int) $this->rrule_includes_dtstart( $ical_string, $this->dtstart );

				return 10 ** 5 * $includes_dtstart;
			}, $ical_strings )
		);
		arsort( $map, SORT_NUMERIC );

		//After sorting, we can drop the integer and use a boolean to indicate whether the DTSTART is contained.
		array_walk( $map, static function ( &$includes_dtstart ) {
			$includes_dtstart = $includes_dtstart >= 10 ** 5;
		} );

		return $map;
	}

	/**
	 * Refreshes the tuples by moving the DTSTART if an EXDATE has been added that "covers" it.
	 *
	 *
	 * @since 6.0.0
	 *
	 * @param array $tuples The list of tuples representing the recurrence rules converted to
	 *                      iCalendar format strings.
	 *
	 * @return array The tuples, refreshed to have their DTSTART moved to the next available if an
	 *               EXDATE is "covering" it.
	 *
	 * @throws Migration_Exception If there's an issue building the RSETs from the iCalendar strings.
	 */
	private function refresh_tuples_dtstart( array $tuples ) {
		foreach ( $tuples as $k => &$tuple ) {
			if ( 0 === $k ) {
				// The first RSET will not need the update.
				continue;
			}

			/** @var RSet_Wrapper $rset */
			$rset = $tuple['rset'];
			$ical_string_wo_exdates = $tuple['ical_string'];
			$exdates = $rset->getExDates();
			$collision_exdates = $tuple['collision_exdates'];
			$rule_count = $this->get_ical_string_count( $ical_string_wo_exdates );

			if ( null !== $rule_count && count( $collision_exdates ) >= $rule_count ) {
				// If the rule COUNT matches the number of EXDATEs, it will not generate any Occurrence.
				$tuple = null;
				continue;
			}

			// The RSET will return the first Occurrence available given the current DTSTART and EXDATEs.
			$new_dtstart = $rset->offsetGet( 0 );
			$is_after_new_dtstart = static function ( DateTime $exdate ) use ( $new_dtstart ) {
				return $exdate > $new_dtstart;
			};

			if ( null === $new_dtstart ) {
				// The RRULE is completely covered by EXDATEs, hence the first RRULE contains it: no need to port.
				$tuple = null;
				continue;
			}

			// Collision EXDATEs have been added on Occurrences: any one we drop as not applicable is -1 to the COUNT.
			$applicable_collision_exdates = array_filter( $collision_exdates, $is_after_new_dtstart );
			$count_adjust = count( $collision_exdates ) - count( $applicable_collision_exdates );
			$new_ical_string = $this->update_ical_string_count_by( $ical_string_wo_exdates, - $count_adjust );

			// Prune any EXDATE before the new new DTSTART.
			$applicable_exdates = array_filter( $exdates, $is_after_new_dtstart );
			// Build a new RSET and add the EXDATEs to it.
			$transition_rset = $this->get_rset_for_ical_string_dtstart( $new_ical_string, $new_dtstart );
			foreach ( $applicable_exdates as $exdate ) {
				$transition_rset->addExDate( $exdate );
			}
			// Build a new RSET using the RFC string that will include the EXDATEs.
			$new_rset = $this->get_rset_for_ical_string_dtstart(
				$transition_rset->get_rfc_string( true ),
				$new_dtstart
			);

			// Update the tuple values.
			$tuple = array_merge( $tuple, [
				'ical_string' => $new_rset->get_rfc_string( true ),
				'dtstart'     => Dates::immutable( $new_dtstart ),
				'rset'        => $new_rset,
			] );
		}

		return array_values( array_filter( $tuples ) );
	}

	/**
	 * Reduces the set of tuples by finding the ones that would produce only 1 Occurrence and
	 * transforming them into RDATEs of the first RSET.
	 *
	 * @since 6.0.0
	 *
	 * @param array $tuples The list of tuples representing the recurrence rules converted to
	 *                      iCalendar format strings.
	 *
	 * @return array The tuples, refreshed to have their DTSTART moved to the next available if an
	 *               EXDATE is "covering" it.
	 *
	 * @throws Migration_Exception If there's an issue building the required RSETs from the iCalendar
	 *                             format strings.
	 */
	private function reduce_tuples( array $tuples ) {
		// We have no tuples, just return empty array.
		if ( empty( $tuples ) ) {
			return [];
		}
		$first_tuple = array_shift( $tuples );
		if ( empty( $tuples ) ) {
			array_unshift( $tuples, $first_tuple );

			return $tuples;
		}

		/** @var RSet_Wrapper $first_rset */
		$first_rset = $first_tuple['rset'];

		$to_rdate = array_filter( array_column( $tuples, 'rset' ), static function ( RSet_Wrapper $rset ) {
			return $rset->isFinite() && 1 === $rset->count();
		} );

		if ( ! count( $to_rdate ) ) {
			// No tuple to remove, move on.
			array_unshift( $tuples, $first_tuple );

			return $tuples;
		}

		if ( count( $to_rdate ) ) {
			foreach ( array_keys( $to_rdate ) as $key ) {
				$first_rset->addDate( $tuples[ $key ]['rset']->offsetGet( 0 ) );
				unset( $tuples[ $key ] );
			}
		}

		$first_ical_string = $first_rset->get_rfc_string();
		$first_rset        = $this->get_rset_for_ical_string_dtstart( $first_ical_string, $first_tuple['dtstart'] );
		$first_tuple       = array_merge( $first_tuple, [
			'ical_string' => $first_ical_string,
			'rset'        => $first_rset,
		] );

		array_unshift( $tuples, $first_tuple );

		return $tuples;
	}

	/**
	 * Updates a tuple iCalendar string and RSET object to include the specified set of RDATEs.
	 *
	 * @since 6.0.0
	 *
	 * @param array<Date_Occurrence|DateTimeInterface> $rdates A set of RDATE objects to add to the
	 *                                                         rule tuple.
	 * @param array<string,mixed>                      $tuple  The tuple to update.
	 *
	 * @return array The tuple, its iCalendar string and RSET object modified to include the
	 *               RDATEs.
	 *
	 * @throws Migration_Exception If there's any issue building of modifying the RSET object.
	 */
	private function add_rdates_to_tuple( array $rdates, array $tuple ) {
		try {
			if ( ! count( $rdates ) ) {
				return $tuple;
			}
			$rdates_strings = $this->from_event_recurrence_converter->convert_recurrence_rules( $rdates, $this->duration, false );
			if ( ! count( $rdates_strings ) ) {
				return $tuple;
			}
			$rdates_strings = reset( $rdates_strings );
			/** @var RSet_Wrapper $head_rset */
			$head_rset = $tuple['rset'];
			$current_string = $head_rset->get_rfc_string();
			$new_head_rset = $this->get_rset_for_ical_string_dtstart(
				$current_string . "\n" . implode( "\n", $rdates_strings ),
				$this->dtstart
			);
			$tuple['ical_string'] = $new_head_rset->get_rfc_string();
			$tuple['rset'] = $new_head_rset;

			return $tuple;
		} catch ( Requirement_Error $e ) {
			throw new Migration_Exception( $e->getMessage(), $e->getCode(), $e );
		}
	}

	/**
	 * Applies exclusion rules and dates to each tuple element.
	 *
	 * @since 6.0.0
	 *
	 * @param array<array<string,mixed>> $exclusions  A set of exclusion rules in the format used by the
	 *                                                `_EventRecurrence` meta; these can include both rules
	 *                                                and dates.
	 * @param array<array<string,mixed>> $tuples      A set of tuples to update.
	 *
	 * @return array<array<string,mixed>> The updated tuples.
	 *
	 * @throws Migration_Exception If there's any issue building the RSET object required for the
	 *                             updates or there are issues with the exclusions' conversion.
	 */
	private function apply_exclusions_to_tuples( array $exclusions, array $tuples ) {
		try {
			$converted = $this->from_event_recurrence_converter
				->convert_exclusion_rules( $exclusions );

			if ( empty( $converted ) ) {
				return $tuples;
			}

			$exrules = array_filter( $converted, static function ( $exrule ) {
				return strpos( $exrule, 'EXRULE' ) === 0;
			} );
			$exdates = array_diff_key( $converted, $exrules );

			if ( count( $exrules ) > 1 ) {
				throw new Migration_Exception( 'Only 1 EXRULE should be present: ' . count( $exrules ) . ' found.' );
			}

			$exrule = reset( $exrules );

			foreach ( $tuples as &$tuple ) {
				$ical_string_raw = $tuple['ical_string'];

				if ( count( $exdates ) ) {
					$ical_string_raw .= "\n" . implode( "\n", $exdates );
				}

				if ( count( $exrules ) ) {
					$aligned_exrule = $this->realign_exrule_for_rrule( $exrule, $ical_string_raw, $this->dtstart, $tuple['dtstart'] );

					if ( $aligned_exrule ) {
						$ical_string_raw .= "\n" . $aligned_exrule;
					}
				}

				$new_rset = $this->get_rset_for_ical_string_dtstart(
					$ical_string_raw,
					$tuple['dtstart']
				);
				$new_ical_string = $new_rset->get_rfc_string( true );
				$tuple['rset'] = $new_rset;
				$tuple['ical_string'] = $new_ical_string;
			}
		} catch ( Requirement_Error $e ) {
			throw new Migration_Exception( $e->getMessage(), $e->getCode(), $e );
		}

		return $tuples;
	}

	/**
	 * Checks and updates the iCalendar format string to remove any "technical" never-ending
	 * limit value that might have been added for the purpose of working on it.
	 *
	 * @since 6.0.0
	 *
	 * @param string $ical_string The iCalendar format string to check and remove the technical
	 *                            limit value from.
	 *
	 * @return string The updated iCalendar format string.
	 */
	private function remove_ical_string_technical_never_ending_limit( string $ical_string ): string {
		$until_value = $this->get_ical_string_attribute_value( $ical_string, 'UNTIL', null );

		if ( $until_value === null ) {
			return $ical_string;
		}

		$seconds = (int) Dates::immutable( $until_value )->format( 's' );

		if ( $seconds !== 23 ) {
			return $ical_string;
		}

		return $this->set_ical_string_until_limit( $ical_string, null );
	}

	/**
	 * Updates a set of iCalendar format strings imposing an UNTIL limit on each never-ending
	 * RRULE to make it computable.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string> $ical_strings The set of iCalendar format strings to update.
	 *
	 *
	 * @return array<string> The udpated iCalendar format strings.
	 *
	 * @throws Exception If there's any issue building the date objects required
	 *                   for the computation.
	 */
	private function apply_technical_never_ending_limit( array $ical_strings ): array {
		// Apply an odd seconds limit that will be carried across the computation.
		$never_limit_date = $this->get_never_limit_date();
		$never_limit_date = $never_limit_date->setTime(
			$never_limit_date->format( 'H' ),
			$never_limit_date->format( 'i' ),
			23
		);
		$ical_strings = $this->limit_never_ending_strings( $ical_strings, $never_limit_date );

		return $ical_strings;
	}
}
<?php
/**
 * Class to add duplicate features to classic editor.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Duplicate
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Duplicate;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Series\Relationship;
use Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta as Blocks_Meta;
use Tribe\Events\Virtual\Event_Meta as Virtual_Meta;
use Tribe\Events\Virtual\Meetings\YouTube\Event_Meta as YouTube_Meta;
use Tribe\Events\Virtual\Meetings\Zoom\Event_Meta as Zoom_Meta;
use Tribe__Events__Main as TEC;
use Tribe__Utils__Array as Arr;
use WP_Post;

/**
 * Class Duplicate
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Duplicate
 */
class Duplicate {

	/**
	 * The name of the action used to duplicate an event.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public static $duplicate_action = 'tec-events-pro-duplicate-event';

	/**
	 * The name of the meta field added to a duplicated event.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public static $duplicate_key = '_tec_events_pro_duplicated_event';

	/**
	 * An instance to the provisional Post.
	 *
	 * @since 6.0.0
	 *
	 * @var \TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post provisional_post Reference to the provisional post.
	 */
	protected $provisional_post;

	/**
	 * Duplicate constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Url              $url              An instance of the URL handler.
	 * @param Provisional_Post $provisional_post Reference to the provisional post.
	 */
	public function __construct( Url $url, Provisional_Post $provisional_post ) {
		$this->url              = $url;
		$this->provisional_post = $provisional_post;
	}

	/**
	 * Add a duplicate link to single event classic editor.
	 *
	 * @since 6.0.0
	 *
	 * @param \WP_Post|null $post The post object to that can be duplicated or null.
	 */
	public function add_duplicate_link( $post = null ) {
		if ( is_null( $post ) && isset( $_GET['post'] ) ) {
			$id   = absint( wp_unslash( $_GET['post'] ) );
			$post = get_post( $id );
		}

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		// Hide on a new event.
		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		// Only show on single event post type.
		if ( TEC::POSTTYPE !== $post->post_type ) {
			return;
		}

		$duplicate_link = $this->get_duplicate_link( $post );
		if ( ! $duplicate_link ) {
			return;
		}

		?>
		<div class="tec-duplicate-action__container">
			<?php echo $duplicate_link; ?>
		</div>
		<?php
	}

	/**
	 * Add a duplicate link to event admin list table actions.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int|string> $actions An array of row action links.
	 * @param WP_Post           $post    The post object that can be duplicated.
	 */
	public function add_admin_list_duplicate_link( $actions, $post ) {
		if ( ! $post instanceof WP_Post ) {
			return $actions;
		}

		// Only show on single event post type.
		if ( TEC::POSTTYPE !== $post->post_type ) {
			return $actions;
		}

		if ( ! current_user_can( 'edit_tribe_events' ) ) {
			return $actions;
		}

		$duplicate_link = $this->get_duplicate_link( $post );
		if ( ! $duplicate_link ) {
			return $actions;
		}

		$actions['duplicate'] = $duplicate_link;

		return $actions;
	}

	/**
	 * Get the duplicate action link html.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $post The post object that can be duplicated.
	 *
	 * @return string Teh duplicate link html.
	 */
	protected function get_duplicate_link( WP_Post $post ): string {
		$aria_label = sprintf(
			'%1$s %2$s.',
				esc_html_x(
					'Duplicate event',
					'Event admin list duplicate link aria label.',
					'tribe-events-calendar-pro'
				),
				$post->post_title
			);

		$post_id = $this->get_post_id( $post );

		if( ! $post_id ) {
			return '';
		}

		ob_start();
		?>
		<a class="tec-event-duplicate-action__duplicate-link"
		   href="<?php echo $this->url->to_duplicate_event( $post_id ); ?>"
		   aria-label="<?php echo esc_html( $aria_label ); ?>"
		   target="_blank"
		>
			<?php echo esc_html_x( 'Duplicate', 'Event admin duplicate link label.', 'tribe-events-calendar-pro' ); ?>
		</a>
		<?php

		return ob_get_clean();
	}

	/**
	 * Handle the Duplicate Request.
	 *
	 * @since 6.0.0
	 */
	public function handle_duplicate_request() {
		if ( ! $nonce = tribe_get_request_var( '_wpnonce', '' ) ) {
			return '';
		}

		$action = static::$duplicate_action;
		if ( ! check_admin_referer( $action ) || ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die( _x( 'The provided nonce is not valid.', 'Admin referer error message.', 'tribe-events-calendar-pro' ) );
		}

		$event = $this->check_admin_post();
		if ( ! $event ) {
			wp_die( _x( 'The event is not valid.', 'Admin referer error message.', 'tribe-events-calendar-pro' ) );
		}

		$duplicated = $this->disconnecting_save_actions( [ $this, 'duplicate_event' ], $event );

		wp_redirect( get_edit_post_link( $duplicated->ID, 'url' ) );

		exit;
	}

	/**
	 * Duplicate the Provided Event.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $post The current event post object, as decorated by the `tribe_get_event` function.
	 * @param array<string,mixed> $overrides A set of override arguments to control the duplication output.
	 *
	 * @return bool|WP_Post The duplicated post object or false if not an event post object.
	 */
	public function duplicate_event( $post, array $overrides = [] ) {
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		if ( TEC::POSTTYPE !== $post->post_type ) {
			return false;
		}

		/**
		 * Action fired before an event is duplicated.
		 *
		 * @since 6.0.0
		 *
		 * @param WP_Post $event      The current event post object, as decorated by the `tribe_get_event` function.
		 */
		do_action( 'tec_events_pro_custom_tables_v1_before_duplicate_event', $post );

		$event   = tribe_get_event( $post );
		$postarr = wp_parse_args( $overrides, $this->get_event_data_for_duplication( $event ) );

		$duplicated_id = wp_insert_post( $postarr );
		$duplicated    = get_post( $duplicated_id );

		// Stuff we could not do with wp_insert_post()
		$organizer_ids = tribe_get_organizer_ids( $event->ID );
		foreach ( $organizer_ids as $organizer_id ) {
			add_post_meta( $duplicated_id, '_EventOrganizerID', $organizer_id );
		}

		if ( ! empty( $duplicated ) ) {
			if ( Event::upsert( [ 'post_id' ], Event::data_from_post( $duplicated->ID ) ) === false ) {
				do_action( 'tribe_log', 'error', 'Could not upsert event.', [
						'source'             => __CLASS__,
						'slug'               => 'duplicate-event-fail',
						'error'              => 'Could not upsert event.',
						'original_post_id'   => $event->post_id,
						'duplicated_post_id' => $duplicated->ID,
				] );
			}

			$event = Event::find( $duplicated->ID, 'post_id' );

			if ( ! $event instanceof Event ) {
				do_action( 'tribe_log', 'error', 'Could not find upserted event.', [
						'source'             => __CLASS__,
						'slug'               => 'duplicate-event-fail',
						'error'              => 'Could not find upserted event.',
						'original_post_id'   => $event->post_id,
						'duplicated_post_id' => $duplicated->ID,
				] );
			}

			$series = Series_Relationship::where( 'event_post_id', '=', $post->ID )
										 ->get();
			if ( $series && $event instanceof Event ) {
				$series_ids = array_map( static function ( Series_Relationship $series_relationship ) {
					return $series_relationship->series_post_id;
				}, $series );
				tribe( Relationship::class )->with_event( $event, $series_ids );
			}
		}

		/**
		 * Action fired after an event is duplicated.
		 *
		 * @since 6.0.0
		 *
		 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
		 * @param WP_Post $post       The current event post object, as decorated by the `tribe_get_event` function.
		 */
		do_action( 'tec_events_pro_custom_tables_v1_after_duplicate_event', $duplicated, $post );

		return $duplicated;
	}


	/**
	 * Get the data from an existing event to duplicate it.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $event The current event post object, as decorated by the `tribe_get_event` function.
	 *
	 * @return array<string|mixed> An array of values uses to duplicate an event.
	 */
	protected function get_event_data_for_duplication( $event ) {
		$duplicate_args = [
				'post_type'             => TEC::POSTTYPE,
				'post_title'            => $event->post_title,
				'post_content'          => $event->post_content,
				'post_content_filtered' => $event->post_content_filtered,
				'post_excerpt'          => $event->post_excerpt,
				'post_status'           => 'draft',
				'post_parent'           => $event->post_parent,
				'comment_status'        => $event->comment_status,
				'ping_status'           => $event->ping_status,
				'post_password'         => $event->post_password,
				'to_ping'               => $event->to_ping,
				'pinged'                => $event->pinged,
				'menu_order'            => $event->menu_order,
				'meta_input'            => [
						'_thumbnail_id'               => get_post_thumbnail_id( $event->ID ),
						'_EventStartDate'             => $event->start_date,
						'_EventEndDate'               => $event->end_date,
						'_EventStartDateUTC'          => $event->start_date_utc,
						'_EventEndDateUTC'            => $event->end_date_utc,
						'_EventAllDay'                => $event->all_day,
						'_EventTimezone'              => $event->timezone,
						'_EventTimezoneAbbr'          => get_post_meta( $event->ID, '_EventTimezoneAbbr', true ),
						'_EventHideFromUpcoming'      => get_post_meta( $event->ID, '_EventHideFromUpcoming', true ),
						'_tribe_featured'             => $event->featured,
						'_EventVenueID'               => tribe_get_venue_id( $event->ID ),
						'_EventShowMap'               => get_post_meta( $event->ID, '_EventShowMap', true ),
						'_EventShowMapLink'           => get_post_meta( $event->ID, '_EventShowMapLink', true ),
						'_tribe_events_status_reason' => get_post_meta( $event->ID, '_tribe_events_status_reason', true ),
						'_tribe_events_status'        => get_post_meta( $event->ID, '_tribe_events_status', true ),
						'_EventURL'                   => tribe_get_event_website_url( $event->ID ),
						'_EventCost'                  => get_post_meta( $event->ID, '_EventCost', true ),
						'_EventCurrencySymbol'        => get_post_meta( $event->ID, '_EventCurrencySymbol', true ),
						'_EventCurrencyPosition'      => get_post_meta( $event->ID, '_EventCurrencyPosition', true ),
						'_EventRecurrence'            => get_post_meta( $event->ID, '_EventRecurrence', true ),
						'_EventCostDescription'       => get_post_meta( $event->ID, '_EventCostDescription', true ),
						'_EventCurrencyCode'          => get_post_meta( $event->ID, '_EventCurrencyCode', true ),
						'_EventDateTimeSeparator'     => get_post_meta( $event->ID, '_EventDateTimeSeparator', true ),
						'_EventTimeRangeSeparator'    => get_post_meta( $event->ID, '_EventTimeRangeSeparator', true ),
						Blocks_Meta::$rules_key       => get_post_meta( $event->ID, Blocks_Meta::$rules_key, true ),
						Blocks_Meta::$exclusions_key  => get_post_meta( $event->ID, Blocks_Meta::$exclusions_key, true ),
						Blocks_Meta::$description_key => get_post_meta( $event->ID, Blocks_Meta::$description_key, true ),
				],
		];

		/**
		 * Filter the arguments used to duplicate an existing event.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string|mixed> $duplicate_args An array of values uses to duplicate an event.
		 * @param WP_Post             $event          The current event post object, as decorated by the `tribe_get_event` function.
		 */
		return (array) apply_filters( 'tec_events_pro_custom_tables_v1_duplicate_arguments', $duplicate_args, $event );
	}

	/**
	 * Save the taxonomies for a duplicated event.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
	 * @param WP_Post $event      The current event post object, as decorated by the `tribe_get_event` function.
	 */
	public function save_taxonomies( $duplicated, $event ) {
		// Get all possible taxonomies registered for the event post type.
		$taxonomies = get_object_taxonomies( TEC::POSTTYPE );

		/**
		 * Allow filtering of the arguments used to duplicate an existing event.
		 *
		 * @since 6.0.0
		 *
		 * @param array<integer|string> $taxonomies An array of taxonomies registered for the event post type.
		 * @param WP_Post               $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
		 * @param WP_Post               $event      The current event post object, as decorated by the `tribe_get_event` function.
		 */
		$taxonomies = apply_filters( 'tec_events_pro_custom_tables_v1_duplicate_event_taxonomies', $taxonomies, $duplicated, $event );

		foreach ( $taxonomies as $taxonomy ) {
			$post_terms = wp_get_object_terms( $event->ID, $taxonomy, [ 'fields' => 'slugs' ] );

			if ( ! is_array( $post_terms ) ) {
				continue;
			}

			$post_terms = array_values( array_filter( $post_terms, static function ( $term ) {
				return is_string( $term ) && $term !== '';
			} ) );

			wp_set_object_terms( $duplicated->ID, $post_terms, $taxonomy, false );
		}
	}

	/**
	 * Save the recurrence for the duplicated event.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
	 * @param WP_Post $event      The event post object, as decorated by the `tribe_get_event` function.
	 */
	public function save_recurrences( WP_Post $duplicated, WP_Post $event ): void {
		$post_id = $this->get_post_id( $event );

		if ( ! $post_id ) {
			return;
		}

		$rset = Event::find( $post_id, 'post_id' )->rset;

		if ( empty( $rset ) ) {
			return;
		}

		//TODO DUPLICATE - from here to the upsert might be unnecessary depending on the coding to solve the update prompt
		$event_model = Event::find( $post_id, 'post_id' );
		$data = [
			'post_id'        => $duplicated->ID,
			'rset'           => $rset,
			'start_date'     => $event_model->start_date,
			'end_date'       => $event_model->end_date,
			'timezone'       => $event_model->timezone,
			'duration'       => $event_model->duration,
			'start_date_utc' => $event_model->start_date_utc,
			'end_date_utc'   => $event_model->end_date_utc,
		];

		Event::upsert( [ 'post_id' ], $data );

		$recurrence_meta = get_post_meta( $event->ID, '_EventRecurrence', true );
		update_post_meta( $duplicated->ID, '_EventRecurrence', $recurrence_meta );

		$recurrence_rules_block_meta = get_post_meta( $event->ID, '_tribe_blocks_recurrence_rules', true );
		update_post_meta( $duplicated->ID, '_tribe_blocks_recurrence_rules', $recurrence_rules_block_meta );

		$recurrence_exclusions_block_meta = get_post_meta( $event->ID, '_tribe_blocks_recurrence_exclusions', true );
		update_post_meta( $duplicated->ID, '_tribe_blocks_recurrence_exclusions', $recurrence_exclusions_block_meta );

		$recurrence_description_block_meta = get_post_meta( $event->ID, '_tribe_blocks_recurrence_description', true );
		update_post_meta( $duplicated->ID, '_tribe_blocks_recurrence_description', $recurrence_description_block_meta );
	}

	/**
	 * Save the additional meta.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
	 * @param WP_Post $event      The event post object, as decorated by the `tribe_get_event` function.
	 */
	public function save_additional_meta( WP_Post $duplicated, WP_Post $event  ): void {
		$post_id = $this->get_post_id( $event );

		if ( ! $post_id ) {
			return;
		}

		$all_meta = get_post_meta( $post_id );
		$prefix = '_ecp_custom_';

		$additional_meta = Arr::flatten(
			array_filter(
				$all_meta,
				static function ( $meta_key ) use ( $prefix ) {
					return 0 === strpos( $meta_key, $prefix );
				},
				ARRAY_FILTER_USE_KEY
			)
		);

		/**
		 * Allow filtering of the additional meta when saving to the duplicated event.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string|mixed> $additional_meta An array of additional meta to save to the duplicated event.
		 * @param WP_Post             $duplicated      The duplicated event post object, as decorated by the `tribe_get_event` function.
		 * @param WP_Post             $event           The current event post object, as decorated by the `tribe_get_event` function.
		 */
		$additional_meta = apply_filters( 'tec_events_pro_custom_tables_v1_duplicate_event_additional_meta', $additional_meta, $duplicated, $event );

		foreach ( $additional_meta as $meta_key => $meta_value ) {
			update_post_meta( $duplicated->ID, $meta_key, $meta_value );
		}
	}

	/**
	 * Save the virtual meta.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
	 * @param WP_Post $event      The event post object, as decorated by the `tribe_get_event` function.
	 */
	public function save_virtual_meta( WP_Post $duplicated, WP_Post $event ): void {
		if ( ! class_exists( Virtual_Meta::class ) ) {
			return;
		}

		if ( ! $event->virtual ) {
			return;
		}

		// Save the default virtual data.
		$post_id = $this->get_post_id( $event );

		if ( ! $post_id ) {
			return;
		}

		$virtual_meta = Virtual_Meta::$virtual_event_keys;

		/**
		 * Allow filtering of the virtual meta when saving to the duplicated event.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string|mixed> $virtual_meta An array of virtual meta to save to the duplicated event.
		 * @param WP_Post             $duplicated   The duplicated event post object, as decorated by the `tribe_get_event` function.
		 * @param WP_Post             $event        The current event post object, as decorated by the `tribe_get_event` function.
		 */
		$virtual_meta = apply_filters( 'tec_events_pro_custom_tables_v1_duplicate_event_virtual_meta', $virtual_meta, $duplicated, $event );

		foreach ( $virtual_meta as $meta_key ) {
			$meta_value = get_post_meta( $post_id, $meta_key, true );
			update_post_meta( $duplicated->ID, $meta_key, $meta_value );
		}

		// Save Youtube or Zoom data if the event has that video source.
		if (
			'youtube' !== $event->virtual_video_source &&
			'zoom' !== $event->virtual_video_source
		) {
			return;
		}

		$zoom_meta = Zoom_Meta::get_post_meta( $event );
		$youtube_meta = YouTube_Meta::get_post_meta( $event );
		$meeting_meta = $zoom_meta + $youtube_meta;

		/**
		 * Allow filtering of the virtual meeting meta when saving to the duplicated event.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string|mixed> $meeting_meta An array of virtual meeting meta to save to the duplicated event.
		 * @param WP_Post             $duplicated   The duplicated event post object, as decorated by the `tribe_get_event` function.
		 * @param WP_Post             $event        The current event post object, as decorated by the `tribe_get_event` function.
		 */
		$meeting_meta = apply_filters( 'tec_events_pro_custom_tables_v1_duplicate_event_virtual_meeting_meta', $meeting_meta, $duplicated, $event );

		foreach ( $meeting_meta as $meta_key => $meta_value ) {
			update_post_meta( $duplicated->ID, $meta_key, $meta_value );
		}
	}

	/**
	 * Save a meta field to mark the event as duplicated.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
	 */
	public function save_duplicate_marker( $duplicated ) {
		update_post_meta( $duplicated->ID, self::$duplicate_key, true );
	}

	/**
	 * Update the duplicate marker on first save of the event.
	 *
	 * @since 6.0.0
	 *
	 * @param int $event_id The id of the event being saved.
	 */
	public function update_duplicate_marker( $event_id ) {
		$duplicate_marker = get_post_meta( $event_id, self::$duplicate_key, true );

		if ( ! tribe_is_truthy( $duplicate_marker ) ) {
			return;
		}

		// Update the duplicated event marker to a timestamp to inform that the duplicated event has been saved once.
		update_post_meta( $event_id, self::$duplicate_key, 	current_time( 'mysql' ) );
	}

	/**
	 * Get the WordPress post id for the provided event object from the provisional id.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $event The event post object, as decorated by the `tribe_get_event` function.
	 *
	 * @return int|null The post id for an event, or `null` if no Occurrence for the provisional
	 *                  post ID can be found.
	 */
	protected function get_post_id( WP_Post $event ): ?int {
		$is_provisional = $this->provisional_post->is_provisional_post_id( $event->ID );

		if ( ! $is_provisional ) {
			return $event->ID;
		}

		$occurrence_id = $this->provisional_post->normalize_provisional_post_id( $event->ID );

		$model = Occurrence::find( $occurrence_id, 'occurrence_id' );

		return $model instanceof Occurrence ? $model->post_id : null;
	}

	/**
	 * Checks the request post ID is set and corresponds to an event.
	 *
	 * @since 6.0.0
	 *
	 * @param int|null $post_id The post ID of the post to check or `null` to use the one from the request variable.
	 *
	 * @return WP_Post Either the event post object, as decorated by the `tribe_get_event` function, or wp_die to end the request.
	 */
	protected function check_admin_post( $post_id = null ) {
		$post_id = $post_id ? $post_id : tribe_get_request_var( 'post_id', false );

		if ( empty( $post_id ) ) {
			wp_die( _x( 'The post ID is missing from the request.', 'An error raised in the context of duplicating an event.', 'tribe-events-calendar-pro' ) );
		}

		$event = tribe_get_event( $post_id );
		if ( ! $event instanceof WP_Post ) {
			wp_die( _x( 'A valid event could not be found.', 'An error raised in the context of duplicating an event.', 'tribe-events-calendar-pro' ) );
		}

		return $event;
	}

	/**
	 * Removes save_post hooks and calls the callable argument, then adds the hooks back.
	 *
	 * @since 6.0.0
	 *
	 * @param callable $do
	 * @param ...$args
	 *
	 * @return mixed The response from the $do parameter.
	 */
	private function disconnecting_save_actions( callable $do, ...$args ) {
		$tec_adding_meta = has_action( 'save_post', [ TEC::instance(), 'addEventMeta' ] );

		if ( $tec_adding_meta ) {
			remove_action( 'save_post', [ TEC::instance(), 'addEventMeta' ], 15 );
		}

		$result = $do( ...$args );

		if ( $tec_adding_meta ) {
			add_action( 'save_post', [ TEC::instance(), 'addEventMeta' ], 15, 2 );
		}

		return $result;
	}

	/**
	 * Fetches the Event <> Series information.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Event post ID to fetch the Series information from.
	 *
	 * @return array<int> An array of Series IDs the Event is related with.
	 */
	private function get_series( $post_id ) {
		// Find out if we need to clone our current Event's Series to the new Event just created.
		$related_series = Series_Relationship::where( 'event_post_id', '=', $post_id )
											 ->get();

		if ( empty( $related_series ) ) {
			// Nothing bad happened, the original Event just did not have a Series <> Event relation.
			return [];
		}

		$series_ids = array_map( static function ( Series_Relationship $series ) {
			return $series->series_post_id;
		}, $related_series );

		return $series_ids;
	}
}

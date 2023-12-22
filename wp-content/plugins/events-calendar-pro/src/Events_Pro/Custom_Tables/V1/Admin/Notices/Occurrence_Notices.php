<?php
/**
 * Class in charge of handling he notices for the new occurrences inside the admin classic editor.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Admin\Notices
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Admin\Notices;

use stdClass;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Admin\Notices\Occurrence_Notices as Notices;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Repository\Events;
use TEC\Events_Pro\Custom_Tables\V1\Tables\Series_Relationships;
use TEC\Events_Pro\Custom_Tables\V1\Templates\Single_Event_Modifications;
use Tribe__Admin__Helpers as Admin_Helpers;
use Tribe__Admin__Notices as Admin_Notices;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use WP_Post;

/**
 * Class Occurrences
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Admin\Notices
 */
class Occurrence_Notices {

	/**
	 * The name of the meta used to store the count of updated and inserted occurrences.
	 */
	const META_KEY = '_EventOccurrencesCount';
	/**
	 * THe name of the prefix used for the classic occurrences.
	 */
	const NOTICE_SLUG_PREFIX = 'classic-occurrences-';

	/**
	 * @var Events The CT1 events repository.
	 */
	protected $events_repository;

	/**
	 * Occurrence_Notices constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Events $events_repo
	 */
	public function __construct( Events $events_repo ) {
		$this->events_repository = $events_repo;
	}

	/**
	 * Wrapper function to process the steps from an updater to create a notice for an event.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $post_id THe post ID of the event being updated.
	 * @param array $meta    The metadata for this notice.
	 */
	public function create( $post_id, $meta = [] ) {
		$this->reset( $post_id );
		$this->update( $post_id, $meta );
		$this->register( $post_id );
	}

	/**
	 * Wrapper around the `register_notice` method from `Tribe__Admin__Notices` to set up the notice value.
	 *
	 * @since 6.0.0
	 *
	 * @param $post_id
	 */
	public function register( $post_id ) {
		Admin_Notices::instance()->register_transient(
			self::NOTICE_SLUG_PREFIX . $post_id,
			[ $this, 'render_classic_notices' ],
			[
				'type'      => 'success',
				'dismiss'   => true,
				'recurring' => true,
			],
			MONTH_IN_SECONDS
		);
	}

	/**
	 * Override into the maybe_dismiss action in order to delete the transient from the post IDs that saved the
	 * IDs of each post.
	 *
	 * @since 6.0.0
	 */
	public function on_dismiss() {
		if ( empty( $_GET[ Admin_Notices::$meta_key ] ) ) {
			wp_send_json( false );
		}

		$slug = sanitize_title_with_dashes( $_GET[ Admin_Notices::$meta_key ] );

		// Review if the notice being removed is a classic occurrence make sure to remove the meta from the post ID.
		$re = '/' . self::NOTICE_SLUG_PREFIX . '(\d+)/';
		preg_match( $re, $slug, $matches, PREG_OFFSET_CAPTURE );

		if ( is_array( $matches ) && count( $matches ) >= 1 ) {
			$match = $matches[1];
			if ( is_array( $match ) ) {
				$this->delete( (int) reset( $match ) );
			}
		}

		// Send a JSON answer with the status of dismissal
		wp_send_json( Admin_Notices::instance()->dismiss( $slug ) );
	}

	/**
	 * Render the created occurrence notice for the classic editor.
	 *
	 * @since 6.0.0
	 *
	 * @param stdClass $notice The class of the current notice to be rendered.
	 *
	 * @return string The notice markup.
	 */
	public function render_classic_notices( $notice ) {
		if ( ! Admin_Helpers::instance()->is_post_type_screen( TEC::POSTTYPE ) ) {
			return '';
		}

		if ( empty( $_REQUEST['post'] ) || empty( $_REQUEST['action'] ) || $_REQUEST['action'] !== 'edit' ) {
			return '';
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}

		// This is only for classic editor
		if ( tribe( 'events.editor.compatibility' )->is_blocks_editor_toggled_on() ) {
			return '';
		}

		$post_id = tribe( Single_Event_Modifications::class )->normalize_post_id( $post_id );
		$slug    = self::NOTICE_SLUG_PREFIX . $post_id;
		// Prevent to render the notice of a different post.
		if ( $notice === null || ! property_exists( $notice, 'slug' ) || $notice->slug !== $slug ) {
			return '';
		}

		$message = $this->get_message( $post_id );
		$this->delete( $post_id );

		if ( empty( $message ) ) {
			return '';
		}

		return '<p>' . $message . '</p>';
	}

	/**
	 * Get the message for the specified Event post ID or Occurrence ID.
	 *
	 * @since 6.0.0
	 * @since 6.0.2 Fix logic to get correct message in cases of non-series events.
	 *
	 * @param integer $post_id The ID of the post or the provisional ID.
	 *
	 * @return string The notice message for this event
	 */
	public function get_message( $post_id ) {
		$event = Event::find( $post_id, 'post_id' );

		// @todo this check will not make sense when a user removes the RSET from an Event: we should move forward.
		// @todo From above: (Do we want to track the "changed" state of our ORM fields? Have done this before... (!$event->rset && $event->has_changed('rset') )
		// If this is a valid event or a single event with no repeated rules the notice is not required.
		if ( ! $event instanceof Event ) {

			return '';
		}

		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {

			return '';
		}

		$data = get_post_meta( $post_id, self::META_KEY, true );
		if ( empty( $data ) || ! is_array( $data ) ) {

			return '';
		}
		$event_label                  = tribe_get_event_label_singular();
		$event_label_lowercase        = tribe_get_event_label_singular_lowercase();
		$event_label_plural_lowercase = tribe_get_event_label_plural_lowercase();

		// Event / draft?
		if ( $post->post_status === 'draft' ) {
			$event_status_message = sprintf( __( 'Draft %s', 'tribe-events-calendar-pro' ), $event_label_lowercase );
		} else {
			$event_status_message = $event_label;
		}

		// The verbs are published, saved (for new draft events), and updated (for changes to an existing event of any post status).
		if ( $data['is_updated'] ) {
			$verb_message = sprintf( __( '%1$s updated.', 'tribe-events-calendar-pro' ), $event_status_message );
		} else if ( $data['is_inserted'] && $post->post_status === 'draft' ) {
			$verb_message = sprintf( __( '%1$s saved.', 'tribe-events-calendar-pro' ), $event_status_message );
		} else {
			$verb_message = sprintf( __( '%1$s published.', 'tribe-events-calendar-pro' ), $event_status_message );
		}

		// This event in a series?
		$event_series_relationship = Series_Relationship::find( $event->event_id, 'event_id' );
		if ( ! $event_series_relationship instanceof Series_Relationship ) {
			return sprintf(
				esc_html__( '%1$s %3$sView %2$s%3$s', 'tribe-events-calendar-pro' ),
				$verb_message,
				$event_label,
				'<a href="' . esc_url( get_permalink( $event->post_id ) ) . '">',
				'</a>'
			);
		}

		$event_type_message = sprintf( __( 'recurring %1$s', 'tribe-events-calendar-pro' ), $event_label_lowercase );

		if ( empty( $event->rset ) ) {
			$event_type_message = $event_label_lowercase;
		}
		$series_message     = __( 'This %1$s is part of a %2$sSeries%3$s with %4$d total %5$s through %6$s.', 'tribe-events-calendar-pro' );
		// Find the last occurrence in this series.
		$series_relationship_table = Series_Relationships::table_name( true );
		$last                      = Occurrence::join( $series_relationship_table, 'event_id', 'event_id' )
		                                       ->where_raw( "`$series_relationship_table`.series_post_id = %d", $event_series_relationship->series_post_id )
		                                       ->order_by( 'end_date', 'DESC' )
		                                       ->first();

		// Should not happen, but fail gracefully.
		if ( ! $last instanceof Occurrence ) {

			return $verb_message;
		}

		$through_date    = esc_html( Dates::immutable( $last->start_date, $event->timezone )->format( tribe_get_date_format( true ) ) );
		$total_in_series = $this->events_repository->get_occurrence_count_for_series( $event_series_relationship->series_post_id );
		$series_url      = get_edit_post_link( $event_series_relationship->series_post_id );

		// Example message: Event updated. This recurring event is part of a Series with 13 total events through February 2, 2032
		return sprintf( $verb_message . ' ' . $series_message,
			$event_type_message,
			'<a href="' . esc_url( $series_url ) . '" target="_blank" rel="noopener">',
			'</a>',
			$total_in_series,
			$event_label_plural_lowercase,
			$through_date
		);
	}

	/**
	 * If the notice was removed make sure is removed out of the dismissed notices.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id THe post ID of the main event to update.
	 */
	public function reset( $post_id ) {
		Admin_Notices::instance()->undismiss( self::NOTICE_SLUG_PREFIX . $post_id );
	}

	/**
	 * Update the meta value in the post ID with the occurrences values inserted or updated.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $post_id The Post ID of the event to update.
	 * @param array $meta    The metadata for this notice.
	 *
	 * @return bool|int value from `update_post_meta` when the meta is added /  updated
	 */
	public function update( $post_id, $meta = [] ) {

		if ( empty ( $meta ) ) {
			return $this->delete( $post_id );
		}

		return update_post_meta( $post_id, self::META_KEY, $meta );
	}

	/**
	 * Remove a notice from the transient, remove the meta associated with the meta as well.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post ID where the event is removed.
	 *
	 * @return bool The value from `delete_post_meta`when removing the meta from the post.
	 */
	public function delete( $post_id ) {
		// Make sure the once is also removed from the transient as well.
		Admin_Notices::instance()->remove_transient( self::NOTICE_SLUG_PREFIX . $post_id );

		return delete_post_meta( $post_id, self::META_KEY );
	}

	/**
	 * Registers the transient notice that will inform the user about the number of updated
	 * and inserted Occurrences.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $post_id The Event post ID the transient admin notice should be registered for.
	 * @param array $meta    Data to store with this notice.
	 */
	public function register_transient_notice( $post_id, $meta ) {
		// Update to make sure the data will be persisted in database.
		tribe( Notices::class )->update( $post_id, $meta );
		// Create a notice that will be show in the context of the Classic Editor.
		tribe( Notices::class )->create( $post_id, $meta );
	}

	/**
	 * Flag the metadata for an inserted event, on this notice.
	 *
	 * @since 6.0.0
	 *
	 * @param numeric $post_id The post ID to attach this notice to.
	 */
	public function on_inserted_event( $post_id ) {
		$meta = [
			'is_inserted' => true,
			'is_updated'  => false,
		];
		$this->register_transient_notice( $post_id, $meta );
	}

	/**
	 * Flag the metadata for an updated event, on this notice.
	 *
	 * @since 6.0.0
	 *
	 * @param numeric $post_id The post ID to attach this notice to.
	 */
	public function on_updated_event( $post_id ) {
		$meta = [
			'is_inserted' => false,
			'is_updated'  => true,
		];

		$this->register_transient_notice( $post_id, $meta );
	}
}

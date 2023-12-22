<?php
/**
 * Handles the post actions for the custom tables.
 *
 * @since   6.0.1
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use WP_Post;
use Tribe__Context as Context;

/**
 * Class Post_Actions.
 *
 * @since   6.0.1
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates;
 */
class Post_Actions {

	/**
	 * A reference to the current context model.
	 *
	 * @since 6.0.1
	 *
	 * @var Context|null
	 */
	private $context;

	/**
	 * Post_Actions constructor.
	 *
	 * since 6.0.1
	 *
	 * @param Context|null $context A reference to the current context model.
	 */
	public function __construct( Context $context = null ) {
		$this->context = $context ?? tribe_context();
	}

	/**
	 * Returns the action links for a post
	 *
	 * @since 6.0.1
	 *
	 * @param WP_Post $post The post to get the action links for.
	 *
	 * @return array<string,string> An map of action links.
	 */
	public function get_post_update_links( WP_Post $post ): array {
		if ( $post->post_type !== 'tribe_events' ) {
			return [];
		}

		if ( ! $this->context->is( 'event_manager' ) ) {
			return [];
		}

		if ( ! isset( $post->_tec_occurrence ) ) {
			return [];
		}

		$occurrence = $post->_tec_occurrence;

		return $this->get_occurrence_action_links( $occurrence );
	}

	/**
	 * Returns the action links for an occurrence.
	 *
	 * @since 6.0.1
	 *
	 * @param Occurrence $occurrence The occurrence to get the action links for.
	 *
	 * @return array<string,string> An map of action links.
	 */
	public function get_occurrence_action_links( Occurrence $occurrence ): array {
		$event = Event::where( 'event_id', $occurrence->event_id )->first();

		if ( ! ( $event instanceof Event && $event->rset ) ) {
			return [];
		}

		$actions['edit-single'] = sprintf(
			'<a data-confirm="%s" class="tec-edit-link" href="%s" target="_blank" rel="noreferrer noopener">%s</a>',
			esc_attr_x( 'Are you sure you want to convert this recurring event instance into a single event? This cannot be undone.', 'Confirmation message', 'tribe-events-calendar-pro' ),
			$occurrence->get_single_edit_post_link(),
			esc_attr_x( 'Convert to Single', 'Link title', 'tribe-events-calendar-pro' )
		);

		return $actions;
	}
}
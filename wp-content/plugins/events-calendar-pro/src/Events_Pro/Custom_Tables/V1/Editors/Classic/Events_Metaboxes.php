<?php
/**
 * Class to register metaboxes for the classic editor.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors\Classic
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors\Classic;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Traits\With_Unbound_Queries;
use TEC\Events_Pro\Custom_Tables\V1\Models\Event;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use TEC\Events_Pro\Custom_Tables\V1\Series\Relationship;
use WP_Post;

/**
 * Class Events_Metaboxes
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Classic
 */
class Events_Metaboxes {
	use With_Unbound_Queries;

	/**
	 * Render the series metabox into the admin of the events.
	 *
	 * @since 6.0.0
	 */
	public function relationship() {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$field_name = Relationship::EVENTS_TO_SERIES_REQUEST_KEY;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$series = $this->get_series();

		$series_selection_state = $this->get_series_selection( $series );
		/** @noinspection PhpUnusedLocalVariableInspection */
		$has_selection          = $series_selection_state[ 'has_selection' ];
		/** @noinspection PhpUnusedLocalVariableInspection */
		$edit_series_link       = $series_selection_state[ 'edit_series_link' ];

		/** @noinspection PhpUnusedLocalVariableInspection */
		$create_label = _x( 'Create or Find a Series', 'The label shown when no Series is available for selection.', 'tribe-events-calendar-pro' );

		/** @noinspection PhpUnusedLocalVariableInspection */
		$creation_enabled = current_user_can( get_post_type_object( Series::POSTTYPE )->cap->edit_post, get_the_ID() );

		$occurrence    = Occurrence::find_by_post_id( get_the_ID() );
		/** @noinspection PhpUnusedLocalVariableInspection */
		$clear_enabled = ! ( $occurrence instanceof Occurrence && $occurrence->has_recurrence );

		include __DIR__ . '/partials/event-series-relationship.php';
	}

	/**
	 * Returns the set of all series available for connection with the current Event.
	 *
	 * @since 6.0.0
	 *
	 * @return array<int,array<string|bool>> A map from each Series ID to its title, relation
	 *                                       with the Event, and its edit link.
	 */
	private function get_series() {
		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return [];
		}

		$event_id = $post->ID;

		// Override the ID of the occurrence with the ID of the real post as the real post has the relationship.
		if ( isset( $post->_tec_occurrence ) && $post->_tec_occurrence instanceof Occurrence ) {
			$event_id = $post->_tec_occurrence->post_id;
		}

		$related_series_id = Event::get_series_id( $event_id );

		$series_post_statuses = [ 'publish' ];

		if ( current_user_can( 'read_private_posts' ) ) {
			$series_post_statuses[] = 'pending';
			$series_post_statuses[] = 'draft';
			$series_post_statuses[] = 'future';
			$series_post_statuses[] = 'private';
		}

		$post_statuses = get_post_statuses();
		$series_posts  = $this->get_all_posts(
			[
				'post_type'   => Series::POSTTYPE,
				'post_status' => $series_post_statuses,
				'order'       => 'ASC',
				'orderby'     => 'title',
			]
		);

		return array_combine(
			wp_list_pluck( $series_posts, 'ID' ),
			array_map(
				static function ( WP_Post $series_post ) use ( $related_series_id, $post_statuses ) {
					$conditions = [];
					// Add the status of the post is not published.
					if ( $series_post->post_status !== 'publish' ) {
						if ( isset( $post_statuses[ $series_post->post_status ] ) ) {
							$conditions[] = $post_statuses[ $series_post->post_status ];
						}
					}

					if ( ! empty( $series_post->post_password ) ) {
						$conditions[] = __( 'Password Protected' );
					}

					$title = count( $conditions ) ?
						$series_post->post_title . ' ( ' . implode( ', ', $conditions ) . ' )'
						: $series_post->post_title;

					return [
						$title,
						$related_series_id === $series_post->ID,
						get_edit_post_link( $series_post ),
					];
				},
				$series_posts
			)
		);
	}

	/**
	 * Returns the selection state of the provided series.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,array<string|bool>> A map from each Series ID to its title, relation
	 *                                      with the Event, and its edit link.
	 *
	 * @return array<string,bool|string> The series selection state.
	 */
	private function get_series_selection( array $series = [] ) {
		return array_reduce(
			$series,
			static function ( $state, $series_post ) {
				if ( $state[ 'has_selection' ] ) {
					return $state;
				}

				if ( empty( $series_post[ 1 ] ) ) {
					return $state;
				}

				return [
					'has_selection'    => $series_post[ 1 ],
					'edit_series_link' => $series_post[ 2 ],
				];
			},
			[ 'has_selection' => false, 'edit_series_link' => '#' ]
		);
	}
}

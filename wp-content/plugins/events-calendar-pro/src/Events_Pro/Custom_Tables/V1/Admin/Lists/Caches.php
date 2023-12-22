<?php
/**
 * Handles the caches for the admin lists.
 *
 * @since   6.0.11
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Admin\Lists;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Admin\Lists;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use WP_Post;
use WP_Query;
use WP_Screen;
use Tribe__Events__Main as TEC;

/**
 * Class Caches.
 *
 * @since   6.0.11
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Admin\Lists;
 */
class Caches {
	const SERIES_MAP_KEY = 'ct1_post_id_to_series_id_map';

	/**
	 * Populate the admin caches for Series. *
	 * @since 6.0.0
	 *
	 * @param array    $posts The list of posts to filter.
	 * @param WP_Query $query The query object.
	 *
	 * @return array<int|WP_Post> The input list of posts, left intact.
	 */
	public function populate_series_admin_caches( array $posts, \WP_Query $query ): array {
		if ( is_array( tribe_cache()[ self::SERIES_MAP_KEY ] ) ) {
			// The cache is already populated.
			return $posts;
		}

		// The function might not exist in the context of the Customizer, not at this stage.
		$screen = function_exists( 'get_current_screen' ) ?
			get_current_screen()
			: null;

		if ( ! $screen instanceof WP_Screen ) {
			return $posts;
		}

		if ( $screen->post_type !== TEC::POSTTYPE ) {
			return $posts;
		}

		if ( ! $query->is_main_query() ) {
			return $posts;
		}

		$ids        = [];
		$events_ids = [];

		foreach ( $posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				$post = get_post( $post );
			}

			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			if ( isset( $post->_tec_occurrence ) && $post->_tec_occurrence instanceof Occurrence ) {
				if ( ! isset( $ids[ $post->_tec_occurrence->event_id ] ) ) {
					$ids[ $post->_tec_occurrence->event_id ] = [];
				}

				$ids[ $post->_tec_occurrence->event_id ][] = $post->ID;
			} else {
				$events_ids[] = $post->ID;
			}
		}

		$events = [];
		if ( ! empty( $events_ids ) ) {
			$events = Event::where_in( 'post_id', $events_ids )->get();
		}

		foreach ( $events as $event ) {
			if ( ! isset( $ids[ $event->event_id ] ) ) {
				$ids[ $event->event_id ] = [];
			}
			$ids[ $event->event_id ][] = $event->post_id;
		}

		$series_id_map = [];
		$events_ids    = array_keys( $ids );
		if ( ! empty( $events_ids ) ) {
			foreach ( Series_Relationship::where_in( 'event_id', $events_ids )->get() as $relationship ) {
				$posts_ids = $ids[ $relationship->event_id ] ?? [];
				foreach ( $posts_ids as $post_id ) {
					$series_id_map[ $post_id ] = $relationship->series_post_id;
				}
			}
		}

		// Hydrate a non-persistent cache entry to store the resolved map.
		tribe_cache()[ self::SERIES_MAP_KEY ] = $series_id_map;

		return $posts;
	}
}

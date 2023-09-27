<?php

namespace TEC\Events_Pro\Linked_Posts;

use Tribe\Utils\Post_Thumbnail;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Venue as Venue;

use WP_Post;

/**
 * Class Thumbnail_Inclusion
 *
 * @since   6.2.0
 *
 * @package TEC\Events_Pro\Linked_Posts
 */
class Thumbnail_Inclusion {
	/**
	 * Include the thumbnail in an object of Linked Post.
	 *
	 * @since 6.2.0
	 *
	 * @param WP_Post $post   The organizer post object, decorated with a set of custom properties.
	 * @param string  $output The output format to use.
	 * @param string  $filter The filter, or context of the fetch.
	 *
	 * @return WP_Post
	 */
	public function include_thumbnail( WP_Post $post, $output, $filter ): WP_Post {
		$post->thumbnail = new Post_Thumbnail( $post->ID );

		return $post;
	}


	/**
	 * Filters the `admin_post_thumbnail_html` to add image aspect ratio recommendation.
	 *
	 * @since 6.2.0
	 *
	 * @param string $html The HTML for the featured image box.
	 *
	 * @return string The modified html, if required.
	 */
	public function include_helper_text_post_metabox( string $html ): string {
		// Just to avoid weird scenarios.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $html;
		}

		$screen = get_current_screen();

		if ( ! $screen instanceof \WP_Screen ) {
			return $html;
		}

		$post_types = [ Venue::POSTTYPE, Organizer::POSTTYPE ];
		if ( ! in_array( $screen->post_type, $post_types, true ) ) {
			return $html;
		}

		return $html . '<p class="hide-if-no-js howto">' . __( 'We recommend a 16:9 aspect ratio for featured images.', 'tribe-events-calendar-pro' ) . '</p>';
	}
}
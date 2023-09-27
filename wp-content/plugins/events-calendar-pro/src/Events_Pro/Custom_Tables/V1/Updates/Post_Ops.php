<?php
/**
 * Handles miscellaneous post operations performed during creation or update.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use Tribe__Events__Main as TEC;

/**
 * Class Post_Ops.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates;
 */
class Post_Ops {
	/**
	 * A reference to the current Provisional Post constructor.
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_Post
	 */
	private $provisional_post;

	/**
	 * Post_Ops constructor.
	 *
	 * since 6.0.0
	 *
	 * @param Provisional_Post $provisional_post A reference to the current Provisional Post handler.
	 */
	public function __construct( Provisional_Post $provisional_post ) {
		$this->provisional_post = $provisional_post;
	}

	/**
	 * Filters the unique post slug generated, or set, for an Event Occurrence.
	 *
	 * @since 6.0.0
	 *
	 * @param string $desired_slug  The desired post slug.
	 * @param int    $post_id       Post ID.
	 * @param string $post_type     Post type.
	 * @param string $original_slug The original post slug.
	 *
	 * @return string The filtered unique post slug.
	 */
	public function get_occurrence_post_slug( $desired_slug, int $post_id, string $post_type, string $original_slug ): string {
		if ( TEC::POSTTYPE !== $post_type ) {
			return $desired_slug;
		}

		global $wpdb;
		if ( ! $this->provisional_post->is_provisional_post_id( $post_id ) ) {
			return $desired_slug;
		}

		$occurrence_id = $this->provisional_post->normalize_provisional_post_id( $post_id );

		if ( ! ( $occurrence = Occurrence::find( $occurrence_id ) ) ) {
			return $desired_slug;
		}

		$real_post_id = $occurrence->post_id;
		$check_sql    = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND ID != %d LIMIT 1";
		$exists       = $wpdb->get_var( $wpdb->prepare( $check_sql, $original_slug, $post_type, $real_post_id ) );

		// Unique title?
		return $exists ? $desired_slug : $original_slug;
	}
}

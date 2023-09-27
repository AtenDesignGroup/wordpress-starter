<?php
/**
 * An adapter for the PRO `Tribe__Events__Pro__Recurrence__Meta_Builder` class
 * to wrap it in an injectable object without constructor side-effects.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Adapters
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Adapters;

use Tribe__Events__Pro__Recurrence__Meta_Builder as Pro_Meta_Builder;

/**
 * Class Recurrence_Meta_Builder
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Adapters
 */
class Recurrence_Meta_Builder {
	/**
	 * Prepares an Event Recurrence meta from a payload in the Classic Editor format.
	 *
	 * @since 6.0.0
	 *
	 * @param int                 $post_id The Event post ID.
	 * @param array<string,mixed> $data    The data from the post.
	 *
	 * @return array<string,mixed> The prepared Event Recurrence meta.
	 */
	public function build_meta( $post_id, array $data = [] ) {
		return ( new Pro_Meta_Builder( $post_id, $data ) )->build_meta();
	}
}

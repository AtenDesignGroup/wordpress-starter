<?php
/**
 * Class to manage duplicate urls.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Duplicate
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Duplicate;

/**
 * Class Url
 *
 * @since 6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Duplicate
 */
class Url {

	/**
	 * Returns the URL to duplicate an event.
	 *
	 * @since 6.0.0
	 *
	 * @return string The URL to duplicate an event or an empty string if no id passed.
	 */
	public function to_duplicate_event( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return '';
		}

		$nonce = wp_create_nonce( Duplicate::$duplicate_action );

		return add_query_arg( [
			'action'   => 'tec_events_pro_duplicate_event',
			'post_id'  => $post_id,
			'_wpnonce' => $nonce,
		], admin_url( 'admin.php' ) );
	}
}

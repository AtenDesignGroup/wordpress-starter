<?php
/**
 * An error to represent the fact that something that's required
 * for the plugin business logic to work is missing.
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Errors
 * @since   6.0.0
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Errors;

use Tribe__Utils__Array as Arr;

/**
 * Class Requirement_Error
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Errors
 * @since   6.0.0
 */
class Requirement_Error extends \Exception {

	/**
	 * Returns an exception for a required and missing meta value.
	 *
	 * @since 6.0.0
	 *
	 * @param string $meta_key The missing meta key.
	 * @param int    $post_id  The ID of the post that is missing the meta key.
	 *
	 * @return \TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error A built error instance.
	 */
	public static function due_to_missing_meta( $meta_key, $post_id ) {
		// translators: this is a message to indicate the ID of a post missing a required string key.
		return new static( sprintf( __( 'The post %1$d is missing the %2$s meta key.', 'tribe-events-calendar-pro' ), $post_id, $meta_key ) );
	}

	/**
	 * Returns an exception for a required and missing piece of information.
	 *
	 * @since 6.0.0
	 *
	 * @param string $what The missing information.
	 * @param int    $where  The context where the required information is supposed to exist.
	 * @param string $string_data A human-readable version of the relevant data missing the required
	 *                            information.
	 *
	 * @return \TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error A built error instance.
	 */
	public static function due_to_missing_required_information( $what, $where, $string_data ) {
		// translators: this is a message to indicate that a payload is missing some required information.
		return new static( sprintf( __( 'The %1$s required information is missing from %2$s; data: %3$s', 'tribe-events-calendar-pro' ), $what, $where, $string_data ) );
	}

	/**
	 * Returns an exception for malformed piece of information.
	 *
	 * @since 6.0.0
	 *
	 * @param string $what        The missing information.
	 * @param int    $where       The context where the information is supposed to be used.
	 * @param string $string_data A human-readable version of the relevant data missing the required
	 *                            information.
	 *
	 * @return \TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error A built error instance.
	 */
	public static function due_to_malformed_information( $what, $where, $string_data ) {
		// translators: this is a message to indicate that a payload presents malformed some required information.
		return new static( sprintf( __( 'The %1$s information is malformed from %2$s; data: %3$s', 'tribe-events-calendar-pro' ), $what, $where, $string_data ) );
	}

	/**
	 * Returns an exception for a non supported post type.
	 *
	 * @since 6.0.0
	 *
	 * @param string       $post_type The non supported post type.
	 * @param string|array $supported_post_types The supported post type or an array of supported post types.
	 *
	 * @return \TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error A built error instance.
	 */
	public static function due_to_not_supported_post_type( $post_type, $supported_post_types = '' ) {
		$supported = Arr::to_list( $supported_post_types, ', ' );

		return new static(
			sprintf(
				// translators: this is a message to indicate that a post type is not supported.
				__( 'The %1$s post type(s) is not supported; supported post types are: %2$s', 'tribe-events-calendar-pro' ),
				$post_type,
				$supported
			)
		);
	}

	/**
	 * Returns an exception for missing or empty data..
	 *
	 * @since 6.0.0
	 *
	 * @return \TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error A built error instance.
	 */
	public static function due_to_empty_data() {
		return new static( __( 'The data passed to the method is empty.', 'tribe-events-calendar-pro' ) );
	}

	/**
	 * Returns an exception cast of a more generic exception.
	 *
	 * @since 6.0.0
	 *
	 * @param \Exception $e The generic exception.
	 *
	 * @return \TEC\Events_Pro\Custom_Tables\V1\Errors\Requirement_Error A built error instance.
	 */
	public static function casting( \Exception $e ) {
		return new static( __( 'Error: ', 'tribe-events-calendar-pro' ) . ' ' . $e->getMessage() );
	}
}

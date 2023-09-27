<?php
use \TEC\Events_Pro\Linked_Posts\Organizer\Taxonomy\Category as Organizer_Category;
use \TEC\Events_Pro\Linked_Posts\Organizer\Controller;
use \TEC\Events_Pro\Linked_Posts\Organizer\Email_Visibility_Modifier;
use \TEC\Events_Pro\Linked_Posts\Organizer\Phone_Visibility_Modifier;

if ( ! function_exists( 'tec_events_pro_organizer_phone_is_visible' ) ) {
	/**
	 * Determines if the organizer phone number should be visible.
	 *
	 * @since 6.2.0
	 *
	 * @param string $area Which area we are potentially displaying the phone number.
	 *
	 * @return bool
	 */
	function tec_events_pro_organizer_phone_is_visible( string $area ): bool {
		$organizer_controller = tribe( Controller::class );

		// If the controller is not active, return true to show the phone number.
		if ( ! $organizer_controller->is_active() ) {
			return true;
		}

		$phone_visibility = tribe( Phone_Visibility_Modifier::class );

		return $phone_visibility->is_visible( $area );
	}
}

if ( ! function_exists( 'tec_events_pro_organizer_email_is_visible' ) ) {
	/**
	 * Determines if the organizer email should be visible.
	 *
	 * Display functions for use in WordPress templates.
	 *
	 * @since 6.2.0
	 *
	 * @param string $area Which area we are potentially displaying the email.
	 *
	 * @return bool
	 */
	function tec_events_pro_organizer_email_is_visible( string $area ): bool {
		$organizer_controller = tribe( Controller::class );

		// If the controller is not active, return true to show the phone number.
		if ( ! $organizer_controller->is_active() ) {
			return true;
		}

		$email_visibility = tribe( Email_Visibility_Modifier::class );

		return $email_visibility->is_visible( $area );
	}
}


if ( ! function_exists( 'tec_events_pro_get_organizer_categories' ) ) {
	/**
	 * Get the categories for an organizer.
	 *
	 * @param int|string|WP_Post $organizer The organizer ID.
	 *
	 * @return array
	 */
	function tec_events_pro_get_organizer_categories( $organizer ) {
		$organizer = Tribe__Main::post_id_helper( $organizer );
		$organizer_category_controller = tribe( Organizer_Category::class );

		return wp_get_object_terms( $organizer, $organizer_category_controller->get_wp_slug(), [ 'fields' => 'id=>name' ] );
	}
}

<?php

namespace TEC\Events_Pro\Linked_Posts\Organizer;

use TEC\Events_Pro\Modifiers\Contracts\Visibility_Modifier_Abstract;
use \WP_Post;

/**
 * Class Phone_Visibility_Modifier.
 *
 * This class is used to manage the visibility of organizer phone numbers in different event views.
 *
 * @since   6.2.0
 *
 * @package TEC\Events_Pro\Linked_Posts\Organizer
 */
class Phone_Visibility_Modifier extends Visibility_Modifier_Abstract {
	/**
	 * @inheritDoc
	 */
	public function get_slug(): string {
		return 'organizer_phone';
	}

	/**
	 * @inheritDoc
	 */
	public function get_setting_label(): string {
		return esc_html__( 'Phone number', 'tribe-events-calendar-pro' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_setting_key(): string {
		return 'organizer-phone-visibility';
	}

	/**
	 * @inheritDoc
	 */
	public function define_setting_options(): array {
		return [
			'event-single'     => [
				'label'   => sprintf(
					// translators: %1$s: Event label singular.
					esc_html_x( 'Show phone number on %1$s single', 'Label for the event single phone number visibility setting', 'tribe-events-calendar-pro' ),
					tribe_get_event_label_singular()
				),
				'default' => true,
			],
			'organizer-single' => [
				'label'   => sprintf(
					// translators: %1$s: Organizer label singular.
					esc_html_x( 'Show phone number on %1$s single', 'Label for the organizer single phone number visibility setting', 'tribe-events-calendar-pro' ),
					tribe_get_organizer_label_singular()
				),
				'default' => true,
			],
		];
	}


	/**
	 * Given an Organizer object, return the phone if it is visible.
	 *
	 * @since 6.2.0
	 *
	 * @param int|string|WP_Post $organizer
	 *
	 * @return int|string|WP_Post|null
	 */
	public function hide_for_event_single_classic_meta( $organizer ) {
		if ( $this->is_visible( 'event-single', $organizer ) ) {
			return $organizer;
		}

		// When not visible null will hide the meta.
		return null;
	}
}

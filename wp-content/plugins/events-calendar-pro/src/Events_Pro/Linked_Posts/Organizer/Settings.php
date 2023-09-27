<?php

namespace TEC\Events_Pro\Linked_Posts\Organizer;

use Tribe__Main as Common_Main;

/**
 * Class Settings.
 *
 * This class is used to manage the display settings for the Organizers.
 *
 * @since   6.2.0
 *
 * @package TEC\Events_Pro\Linked_Posts\Organizer
 */
class Settings {

	/**
	 * Get the display settings.
	 *
	 * @since   6.2.0
	 * @return array The display settings for phone and email visibility in the format {organizer-visibility-settings-title => ....
	 */
	public function get_display_settings(): array {
		$phone_visibility = tribe( Phone_Visibility_Modifier::class );
		$email_visibility = tribe( Email_Visibility_Modifier::class );
		$settings         = [
			'organizer-visibility-settings-title' => [
				'type' => 'html',
				'html' => '<h3 id="tec-organizer-display-settings">' . esc_html_x( 'Organizers', 'Header for the organizer settings section header', 'tribe-events-calendar-pro' ) . '</h3>',
			],
			$phone_visibility->get_setting_key()  => $phone_visibility->get_setting_definition(),
			$email_visibility->get_setting_key()  => $email_visibility->get_setting_definition(),
		];

		return $settings;
	}

	/**
	 * Inject display settings into the provided fields.
	 *
	 * @since   6.2.0
	 *
	 * @param array $fields The fields into which the display settings should be injected.
	 *
	 * @return array The fields with the injected display settings.
	 */
	public function inject_display_settings( array $fields ): array {
		// Insert the organizer settings before the date format settings.
		return Common_Main::array_insert_before_key(
			'tribeEventsDateFormatSettingsTitle',
			$fields,
			$this->get_display_settings()
		);
	}
}

<?php
/**
 * Handles the rewriting of string translations to mention Series in place of Recurring Events.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */

namespace TEC\Events_Pro\Custom_Tables\V1;

/**
 * Class Gettext
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1
 */
class Gettext {

	/**
	 * A map from the English default version of the string to the translated
	 * version of the one supporting the Custom Tables implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,string>
	 */
	private $translations;

	/**
	 * Gettext constructor.
	 *
	 * Primes the translations at run time to make sure they will be picked up by translation
	 * software and they will be picked up by translation filtering.
	 *
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->prime_translations();
	}

	/**
	 * Hook the class filtering of gettext strings.
	 *
	 * Note: the hooking is done here and not in the Provider to avoid infinite loops.
	 *
	 * @since 6.0.0
	 */
	public function hook() {
		add_filter( 'gettext_tribe-events-calendar-pro', [ $this, 'filter_gettext' ], 10, 2 );
	}

	/**
	 * Replaces default ECP translations with ones aligned to the Custom Tables based implementation.
	 *
	 * @since 6.0.0
	 *
	 * @param string $translation The translated, localized, version of the original string text.
	 * @param string $text The original, English, version of the text; will be used as key to find
	 *                     the correct wording.
	 *
	 * @return string The updated version of the English text, if required.
	 */
	public function filter_gettext( $translation, $text ) {
		if ( isset( $this->translations[ $text ] ) ) {
			return $this->translations[ $text ];
		}

		return $translation;
	}

	/**
	 * Primes the translations, at most once, allowing their detection by i18n scripts and
	 * their filtering by i10n code.
	 *
	 * @since 6.0.0
	 */
	private function prime_translations(){
		if( null !== $this->translations ){
			return;
		}

		$this->translations = [
			'Edit Series'                                                                                                                                                                                                                                                                                                                                                                                                                                                                               => __('Edit Recurring Event','tribe-events-calendar-pro'),
			'Break from Series'                                                                                                                                                                                                                                                                                                                                                                                                                                                                         => __('Break from Recurring Event','tribe-events-calendar-pro'),
			'Break this event out of its series and edit it independently'                                                                                                                                                                                                                                                                                                                                                                                                                              => __('Break this occurrence out of the recurring event and edit it independently','tribe-events-calendar-pro'),
			'Split the series in two at this point, creating a new series out of this and all subsequent events'                                                                                                                                                                                                                                                                                                                                                                                        => __('Split the recurring event in two at this point, creating a new recurring event out of this and all subsequent events','tribe-events-calendar-pro'),
			'Edit all events in this series'                                                                                                                                                                                                                                                                                                                                                                                                                                                            => __('Edit all occurrences of this recurring event','tribe-events-calendar-pro'),
			'Move all events in this series to the Trash'                                                                                                                                                                                                                                                                                                                                                                                                                                               => __('Move all occurrences of this recurring event to the Trash','tribe-events-calendar-pro'),
			'Trash Series'                                                                                                                                                                                                                                                                                                                                                                                                                                                                              => __('Trash Recurring Event','tribe-events-calendar-pro'),
			'Delete all events in this series permanently'                                                                                                                                                                                                                                                                                                                                                                                                                                              => __('Delete all occurrences of this recurring event permanently','tribe-events-calendar-pro'),
			'Delete Series Permanently'                                                                                                                                                                                                                                                                                                                                                                                                                                                                 => __('Delete Recurring Event Permanently','tribe-events-calendar-pro'),
			'Restore all events in this series from the Trash'                                                                                                                                                                                                                                                                                                                                                                                                                                          => __('Restore all occurrences of this recurring event from the Trash','tribe-events-calendar-pro'),
			'Restore Series'                                                                                                                                                                                                                                                                                                                                                                                                                                                                            => __('Restore Recurring Event','tribe-events-calendar-pro'),
			"You are about to split this series in two.\n\nThe event you selected and all subsequent events in the series will be separated into a new series of events that you can edit independently of the original series.\n\nThis action cannot be undone.\n\nWhen you break events from a series their URLs will change, so any users trying to use the original URLs will receive a 404 Not Found error. If this is a concern, consider using a suitable plugin to setup and manage redirects." => __("You are about to split this recurring event in two.\n\nThe occurrence you selected and all subsequent occurrences in the recurring event will be separated into a new recurring event that you can edit independently of the original recurring event.\n\nThis action cannot be undone.\n\nWhen you break occurrences from a recurring event their URLs will change, so any users trying to use the original URLs will receive a 404 Not Found error. If this is a concern, consider using a suitable plugin to setup and manage redirects.",'tribe-events-calendar-pro'),
			"You are about to break this event out of its series.\n\nYou will be able to edit it independently of the original series.\n\nThis action cannot be undone.\n\nWhen you break events from a series their URLs will change, so any users trying to use the original URLs will receive a 404 Not Found error. If this is a concern, consider using a suitable plugin to setup and manage redirects."                                                                                          => __("You are about to break this occurrence out of its recurring event.\n\nYou will be able to edit it independently of the original recurring event.\n\nThis action cannot be undone.\n\nWhen you break occurrences from a recurring event their URLs will change, so any users trying to use the original URLs will receive a 404 Not Found error. If this is a concern, consider using a suitable plugin to setup and manage redirects.",'tribe-events-calendar-pro'),
			'Event Series:'                                                                                                                                                                                                                                                                                                                                                                                                                                                                             => __('Recurring Event:','tribe-events-calendar-pro'),
			'Event Series Recurrence Day of Week'                                                                                                                                                                                                                                                                                                                                                                                                                                                       => __('Recurring Event Day of Week','tribe-events-calendar-pro'),
			'Series ends on this date'                                                                                                                                                                                                                                                                                                                                                                                                                                                                  => __('Recurring event ends on this date','tribe-events-calendar-pro'),
			'Series ends'                                                                                                                                                                                                                                                                                                                                                                                                                                                                               => __('Recurring event ends','tribe-events-calendar-pro'),
			'Add Exclusion'                                                                                                                                                                                                                                                                                                                                                                                                                                                                             => __('Add Exception','tribe-events-calendar-pro'),
			'Are you sure you want to delete this exclusion?'                                                                                                                                                                                                                                                                                                                                                                                                                                           => __('Are you sure you want to delete this exception?','tribe-events-calendar-pro')
		];
	}
}

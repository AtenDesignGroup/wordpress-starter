<?php
/**
 * Provides the strings that will replace the ones used by TEC in the context of the
 * Migration UI.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Migration;

/**
 * Class Strings.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration;
 */
class String_Dictionary {
	public function filter_map( array $map = [] ) {
		return array_merge( $map, [
			'preview-in-progress-screenshot-url'                       => plugins_url(
				'src/resources/images/migration/preview-in-progress-screenshot.png',
				EVENTS_CALENDAR_PRO_FILE
			),
			'preview-prompt-screenshot-url'                            => plugins_url(
				'src/resources/images/migration/preview-prompt-screenshot.png',
				EVENTS_CALENDAR_PRO_FILE
			),
			'cancel-complete-screenshot-url'                           => plugins_url(
				'src/resources/images/migration/preview-prompt-screenshot.png',
				EVENTS_CALENDAR_PRO_FILE
			),
			'migration-completed-site-upgraded'                        => __(
				'Your site is now using the upgraded recurring events system. See the report below to learn ' .
				'how your events may have been adjusted during the migration process.',
				'tribe-events-calendar-pro'
			),
			'preview-prompt-get-ready'                                 => __(
				'Get ready for the new recurring events!',
				'tribe-events-calendar-pro'
			),
			'preview-prompt-upgrade-cta'                               => __( 'Upgrade your recurring events.', 'tribe-events-calendar-pro' ),
			'preview-prompt-features'                                  => __(
				'Faster event editing. Smarter save options. More flexibility. Events Calendar Pro 6.0  ' .
				'is full of features to make managing recurring and connected events better than ever. ' .
				'Before you get started, we need to migrate your existing events into the new system. ' .
				'As with any significant site change, we recommend %screating a site backup%s before beginning the migration process.',
				'tribe-events-calendar-pro'
			),
			'preview-prompt-ready'                                     => __(
				'Ready to go? The first step is a migration preview.',
				'tribe-events-calendar-pro'
			),
			'migration-prompt-strategy-tec-ecp-single-rule-strategy'   => sprintf(
				__( 'The following recurring %1$s will be part of a new Series of the same name:', 'tribe-events-calendar-pro' ),
				tribe_get_event_label_plural_lowercase()
			),
			'migration-prompt-strategy-tec-ecp-multi-rule-strategy'    => sprintf(
				__( 'The following recurring %1$s have multiple recurrence rules and will be split into multiple recurring %1$s with identical content. All of these %1$s will be part of a new Series of the same name:', 'tribe-events-calendar-pro' ),
				tribe_get_event_label_plural_lowercase()
			),
			'migration-complete-strategy-tec-ecp-single-rule-strategy' => sprintf(
				__( 'The following recurring %1$s are now part of a new Series of the same name:', 'tribe-events-calendar-pro' ),
				tribe_get_event_label_plural_lowercase()
			),
			'migration-complete-strategy-tec-ecp-multi-rule-strategy'  => sprintf(
				__( 'The following recurring %1$s had multiple recurrence rules and were split into multiple recurring events with identical content. All of these %1$s are part of a new Series of the same name:', 'tribe-events-calendar-pro' ),
				tribe_get_event_label_plural_lowercase()
			),
			'preview-prompt-scan-events'                               => sprintf(
				__( 'We\'ll scan all existing %1$s and let you know what to expect from the migration process. You\'ll also get an idea of how long your migration will take. The preview runs in the background, so you\'ll be able to continue using your site.', 'tribe-events-calendar-pro' ),
				tribe_get_event_label_plural_lowercase()
			),
			'unsupported-weekly-interval-gt-1'                         => sprintf(
				__( 'Migration of Weekly recurrence or exclusion rules with an interval greater than 1 is not yet supported. Remove the %1$s or wait to migrate until a path is available.', 'tribe-events-calendar-pro' ),
				tribe_get_event_label_plural_lowercase()
			),
			'migration-complete-paragraph' => __( 'Your site is now using the upgraded recurring events system. See the report below to learn how your events may have been adjusted during the migration process. Go ahead and %1$scheck out your events%2$s or %3$sview your calendar.%2$s',  'tribe-events-calendar-pro' ),
		] );
	}
}

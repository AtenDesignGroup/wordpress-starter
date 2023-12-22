<?php
/**
 * Class to handle recurrence strings editor.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors;

/**
 * Class Recurrence_Strings
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors
 */
class Recurrence_Strings {
	/**
	 * Updates the recurrence strings for recurrence.
	 * Recurrence strings have keys "date", "recurrence", and "exclusion", this only
	 * updates the value of the key "recurrence".
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,string> $strings The strings to be updated.
	 *
	 * @return array<string,string> The updated strings.
	 */
	public function update_recurrence_recurrence_strings( $strings ) {
		// Expecting array, return if not array.
		if ( ! is_array( $strings ) ) {
			return $strings;
		}

		return array_merge(
			$strings,
			[
				// daily, ending on a specific date
				'daily-on'                                 => __( 'An event every [interval] day(s) that begins at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'daily-allday-on'                          => __( 'An all day event every [interval] day(s), starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'daily-allday-on-at'                       => __( 'An all day event every [interval] day(s) at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'daily-multi-on'                           => __( 'A multi-day event every [interval] day(s), starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'daily-multi-on-at'                        => __( 'A multi-day event every [interval] day(s) at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),

				// daily, after a specific number of events
				'daily-after'                              => __( 'An event every [interval] day(s) that begins at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'daily-allday-after'                       => __( 'An all day event every [interval] day(s), starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'daily-allday-after-at'                    => __( 'An all day event every [interval] day(s) at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'daily-multi-after'                        => __( 'A multi-day event every [interval] day(s), starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'daily-multi-after-at'                     => __( 'A multi-day event every [interval] day(s) at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),

				// daily, never ending
				'daily-never'                              => __( 'An event every [interval] day(s) that begins at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'daily-allday-never'                       => __( 'An all day event every [interval] day(s), starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'daily-allday-never-at'                    => __( 'An all day event every [interval] day(s) at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'daily-multi-never'                        => __( 'A multi-day event every [interval] day(s), starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'daily-multi-never-at'                     => __( 'A multi-day event every [interval] day(s) at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),

				// weekly, ending on a specific date
				'weekly-on'                                => __( 'An event every [interval] week(s) that begins at [first_occurrence_start_time] on [days_of_week], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'weekly-allday-on'                         => __( 'An all day event every [interval] week(s) on [days_of_week], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'weekly-allday-on-at'                      => __( 'An all day event every [interval] week(s) on [days_of_week] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'weekly-multi-on'                          => __( 'A multi-day event every [interval] week(s) starting on [days_of_week], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'weekly-multi-on-at'                       => __( 'A multi-day event every [interval] week(s) starting on [days_of_week] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),

				// weekly, after a specific number of events
				'weekly-after'                             => __( 'An event every [interval] week(s) that begins at [first_occurrence_start_time] on [days_of_week], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'weekly-allday-after'                      => __( 'An all day event every [interval] week(s) on [days_of_week], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'weekly-allday-after-at'                   => __( 'An all day event every [interval] week(s) on [days_of_week] at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'weekly-multi-after'                       => __( 'A multi-day event every [interval] week(s) starting on [days_of_week], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'weekly-multi-after-at'                    => __( 'A multi-day event every [interval] week(s) starting on [days_of_week] at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),

				// weekly, never ending
				'weekly-never'                             => __( 'An event every [interval] week(s) that begins at [first_occurrence_start_time] on [days_of_week], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'weekly-allday-never'                      => __( 'An all day event every [interval] week(s) on [days_of_week], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'weekly-allday-never-at'                   => __( 'An all day event every [interval] week(s) on [days_of_week] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'weekly-multi-never'                       => __( 'A multi-day event every [interval] week(s) starting on [days_of_week], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'weekly-multi-never-at'                    => __( 'A multi-day event every [interval] week(s) starting on [days_of_week] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),

				// monthly, with a relative day, ending on a specific date
				'monthly-on'                               => __( 'An event every [interval] month(s) that begins at [first_occurrence_start_time] on [month_day_description], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'monthly-allday-on'                        => __( 'An all day event every [interval] month(s) on [month_day_description], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'monthly-allday-on-at'                     => __( 'An all day event every [interval] month(s) on [month_day_description] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'monthly-multi-on'                         => __( 'A multi-day event every [interval] month(s) starting on [month_day_description], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'monthly-multi-on-at'                      => __( 'A multi-day event every [interval] month(s) starting on [month_day_description] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),

				// monthly, with a numeric day, ending on a specific date
				'monthly-numeric-on'                       => __( 'An event every [interval] month(s) that begins at [first_occurrence_start_time] on day [month_number] of the month, starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'monthly-allday-numeric-on'                => __( 'An all day event every [interval] month(s) on day [month_number] of the month, starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'monthly-allday-numeric-on-at'             => __( 'An all day event every [interval] month(s) on day [month_number] of the month at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'monthly-multi-numeric-on'                 => __( 'A multi-day event every [interval] month(s) starting on day [month_number] of the month, starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'monthly-multi-numeric-on-at'              => __( 'A multi-day event every [interval] month(s) starting on day [month_number] of the month at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),

				// monthly, with a relative day, after a specific number of events
				'monthly-after'                            => __( 'An event every [interval] month(s) that begins at [first_occurrence_start_time] on [month_day_description], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'monthly-allday-after'                     => __( 'An all day event every [interval] month(s) on [month_day_description], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'monthly-allday-after-at'                  => __( 'An all day event every [interval] month(s) on [month_day_description] at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'monthly-multi-after'                      => __( 'A multi-day event every [interval] month(s) starting on [month_day_description], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'monthly-multi-after-at'                   => __( 'A multi-day event every [interval] month(s) starting on [month_day_description] at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),

				// monthly, with a numeric day, after a specific number of events
				'monthly-numeric-after'                    => __( 'An event every [interval] month(s) that begins at [first_occurrence_start_time] on day [month_number] of the month, starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'monthly-allday-numeric-after'             => __( 'An all day event every [interval] month(s) on day [month_number] of the month, starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'monthly-allday-numeric-after-at'          => __( 'An all day event every [interval] month(s) on day [month_number] of the month at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'monthly-multi-numeric-after'              => __( 'A multi-day event every [interval] month(s) starting on day [month_number] of the month, starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'monthly-multi-numeric-after-at'           => __( 'A multi-day event every [interval] month(s) starting on day [month_number] of the month at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),

				// monthly, with a relative day, never ending
				'monthly-never'                            => __( 'An event every [interval] month(s) that begins at [first_occurrence_start_time] on [month_day_description], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'monthly-allday-never'                     => __( 'An all day event every [interval] month(s) on [month_day_description], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'monthly-allday-never-at'                  => __( 'An all day event every [interval] month(s) on [month_day_description] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'monthly-multi-never'                      => __( 'A multi-day event every [interval] month(s) starting on [month_day_description], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'monthly-multi-never-at'                   => __( 'A multi-day event every [interval] month(s) starting on [month_day_description] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),

				// monthly, with a numeric day, never ending
				'monthly-numeric-never'                    => __( 'An event every [interval] month(s) that begins at [first_occurrence_start_time] on day [month_number] of the month, starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'monthly-allday-numeric-never'             => __( 'An all day event every [interval] month(s) on day [month_number] of the month, starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'monthly-allday-numeric-never-at'          => __( 'An all day event every [interval] month(s) on day [month_number] of the month at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'monthly-multi-numeric-never'              => __( 'A multi-day event every [interval] month(s) starting on day [month_number] of the month, starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'monthly-multi-numeric-never-at'           => __( 'A multi-day event every [interval] month(s) starting on day [month_number] of the month at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),

				// yearly, with a relative day, ending on a specific date
				'yearly-on'                                => __( 'An event every [interval] year(s) that begins at [first_occurrence_start_time] on [month_day_description] of [month_names], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'yearly-allday-on-at'                      => __( 'An all day event every [interval] year(s) on [month_day_description] of [month_names], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'yearly-allday-on'                         => __( 'An all day event every [interval] year(s) on [month_day_description] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'yearly-multi-on'                          => __( 'A multi-day event every [interval] year(s) starting on [month_day_description] of [month_names], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'yearly-multi-on-at'                       => __( 'A multi-day event every [interval] year(s) starting on [month_day_description] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),

				// yearly, with a numeric day, ending on a specific date
				'yearly-numeric-on'                        => __( 'An event every [interval] year(s) that begins at [first_occurrence_start_time] on day [month_number] of [month_names], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'yearly-allday-numeric-on'                 => __( 'An all day event every [interval] year(s) on day [month_number] of [month_names], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'yearly-allday-numeric-on-at'              => __( 'An all day event every [interval] year(s) on day [month_number] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'yearly-multi-numeric-on'                  => __( 'A multi-day event every [interval] year(s) starting on day [month_number] of [month_names], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),
				'yearly-multi-numeric-on-at'               => __( 'A multi-day event every [interval] year(s) starting on day [month_number] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating until [series_end_date]', 'tribe-events-calendar-pro' ),

				// yearly, with a relative day, after a specific number of events
				'yearly-after'                             => __( 'An event every [interval] year(s) that begins at [first_occurrence_start_time] on [month_day_description] of [month_names], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'yearly-allday-after'                      => __( 'An all day event every [interval] year(s) on [month_day_description] of [month_names], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'yearly-allday-after-at'                   => __( 'An all day event every [interval] year(s) on [month_day_description] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'yearly-multi-after'                       => __( 'A multi-day event every [interval] year(s) starting on [month_day_description] of [month_names], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'yearly-multi-after-at'                    => __( 'A multi-day event every [interval] year(s) starting on [month_day_description] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),

				// yearly, with a numeric day, after a specific number of events
				'yearly-numeric-after'                     => __( 'An event every [interval] year(s) that begins at [first_occurrence_start_time] on day [month_number] of [month_names], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'yearly-allday-numeric-after'              => __( 'An all day event every [interval] year(s) on day [month_number] of [month_names], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'yearly-allday-numeric-after-at'           => __( 'An all day event every [interval] year(s) on day [month_number] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'yearly-multi-numeric-after'               => __( 'A multi-day event every [interval] year(s) starting on day [month_number] of [month_names], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),
				'yearly-multi-numeric-after-at'            => __( 'A multi-day event every [interval] year(s) starting on day [month_number] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and happening [count] times', 'tribe-events-calendar-pro' ),

				// yearly, with a relative day, never ending
				'yearly-never'                             => __( 'An event every [interval] year(s) that begins at [first_occurrence_start_time] on [month_day_description] of [month_names], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'yearly-allday-never'                      => __( 'An all day event every [interval] year(s) on [month_day_description] of [month_names], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'yearly-allday-never-at'                   => __( 'An all day event every [interval] year(s) on [month_day_description] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'yearly-multi-never'                       => __( 'A multi-day event every [interval] year(s) starting on [month_day_description] of [month_names], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'yearly-multi-never-at'                    => __( 'A multi-day event every [interval] year(s) starting on [month_day_description] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),

				// yearly, with a numeric day, never ending
				'yearly-numeric-never'                     => __( 'An event every [interval] year(s) that begins at [first_occurrence_start_time] on day [month_number] of [month_names], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'yearly-allday-numeric-never'              => __( 'An all day event every [interval] year(s) on day [month_number] of [month_names], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'yearly-allday-numeric-never-at'           => __( 'An all day event every [interval] year(s) on day [month_number] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'yearly-multi-numeric-never'               => __( 'A multi-day event every [interval] year(s) starting on day [month_number] of [month_names], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
				'yearly-multi-numeric-never-at'            => __( 'A multi-day event every [interval] year(s) starting on day [month_number] of [month_names] at [first_occurrence_start_time], starting [first_occurrence_date] and repeating indefinitely', 'tribe-events-calendar-pro' ),
			]
		);
	}

	/**
	 * Updates the recurrence strings.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array> $strings The recurrence strings to be updated.
	 *
	 * @return array<string,array> The updated recurrence strings.
	 */
	public function update_recurrence_strings( $strings ) {
		// Expecting array, return if not array.
		if ( ! is_array( $strings ) ) {
			return $strings;
		}

		// @todo: replace strings in dialog once RRULE UI work is merged.
		return array_merge(
			$strings,
			[
				'customTablesV1' => [
					'dialog'     => [
						'editRecurringEvent'         => sprintf(
							/* Translators: %s: event (singular) */
							_x( 'Edit recurring $s', 'dialog title for edit recurring event', 'tribe-events-calendar-pro' ),
							tribe_get_event_label_singular_lowercase()
						),
						'trashRecurringEvent'         => sprintf(
							/* Translators: %s: event (singular) */
							_x( 'Trash recurring $s', 'dialog title for trash recurring event', 'tribe-events-calendar-pro' ),
							tribe_get_event_label_singular_lowercase()
						),
						'followingEventsDescription' => sprintf(
							/* Translators: %s: event (plural) */
							_x( 'These changes will affect this event and all following %s', 'dialog description for this and following events', 'tribe-events-calendar-pro' ),
							tribe_get_event_label_plural_lowercase()
						),
						'followingEvents'            => sprintf(
							/* Translators: %s: event (plural) */
							_x( 'This and following %s', 'dialog option for this and following events', 'tribe-events-calendar-pro' ),
							tribe_get_event_label_plural_lowercase()
						),
						'allEvents'                  => sprintf(
							/* Translators: %s: event (plural) */
							_x( 'All %s', 'dialog option for all events', 'tribe-events-calendar-pro' ),
							tribe_get_event_label_plural_lowercase()
						),
						'ok'                         => _x( 'OK', 'dialog confirm button text', 'tribe-events-calendar-pro' ),
						'cancel'                     => _x( 'Cancel', 'dialog cancel button text', 'tribe-events-calendar-pro' ),
					],
					'dayOfMonth' => [
						'pattern' => [
							'firstMonday'   => [
								'ordinal' => 'First',
								'day'     => '1',
								'label'   => _x( 'first Monday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'secondMonday'    => [
								'ordinal' => 'Second',
								'day'     => '1',
								'label'   => _x( 'second Monday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'thirdMonday'     => [
								'ordinal' => 'Third',
								'day'     => '1',
								'label'   => _x( 'third Monday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fourthMonday'    => [
								'ordinal' => 'Fourth',
								'day'     => '1',
								'label'   => _x( 'fourth Monday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fifthMonday'     => [
								'ordinal' => 'Fifth',
								'day'     => '1',
								'label'   => _x( 'fifth Monday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'lastMonday'      => [
								'ordinal' => 'Last',
								'day'     => '1',
								'label'   => _x( 'last Monday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'firstTuesday'    => [
								'ordinal' => 'First',
								'day'     => '2',
								'label'   => _x( 'first Tuesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'secondTuesday'   => [
								'ordinal' => 'Second',
								'day'     => '2',
								'label'   => _x( 'second Tuesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'thirdTuesday'    => [
								'ordinal' => 'Third',
								'day'     => '2',
								'label'   => _x( 'third Tuesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fourthTuesday'   => [
								'ordinal' => 'Fourth',
								'day'     => '2',
								'label'   => _x( 'fourth Tuesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fifthTuesday'    => [
								'ordinal' => 'Fifth',
								'day'     => '2',
								'label'   => _x( 'fifth Tuesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'lastTuesday'     => [
								'ordinal' => 'Last',
								'day'     => '2',
								'label'   => _x( 'last Tuesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'firstWednesday'  => [
								'ordinal' => 'First',
								'day'     => '3',
								'label'   => _x( 'first Wednesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'secondWednesday' => [
								'ordinal' => 'Second',
								'day'     => '3',
								'label'   => _x( 'second Wednesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'thirdWednesday'  => [
								'ordinal' => 'Third',
								'day'     => '3',
								'label'   => _x( 'third Wednesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fourthWednesday' => [
								'ordinal' => 'Fourth',
								'day'     => '3',
								'label'   => _x( 'fourth Wednesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fifthWednesday'  => [
								'ordinal' => 'Fifth',
								'day'     => '3',
								'label'   => _x( 'fifth Wednesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'lastWednesday'   => [
								'ordinal' => 'Last',
								'day'     => '3',
								'label'   => _x( 'last Wednesday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'firstThursday'   => [
								'ordinal' => 'First',
								'day'     => '4',
								'label'   => _x( 'first Thursday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'secondThursday'  => [
								'ordinal' => 'Second',
								'day'     => '4',
								'label'   => _x( 'second Thursday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'thirdThursday'   => [
								'ordinal' => 'Third',
								'day'     => '4',
								'label'   => _x( 'third Thursday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fourthThursday'  => [
								'ordinal' => 'Fourth',
								'day'     => '4',
								'label'   => _x( 'fourth Thursday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fifthThursday'   => [
								'ordinal' => 'Fifth',
								'day'     => '4',
								'label'   => _x( 'fifth Thursday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'lastThursday'    => [
								'ordinal' => 'Last',
								'day'     => '4',
								'label'   => _x( 'last Thursday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'firstFriday'     => [
								'ordinal' => 'First',
								'day'     => '5',
								'label'   => _x( 'first Friday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'secondFriday'    => [
								'ordinal' => 'Second',
								'day'     => '5',
								'label'   => _x( 'second Friday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'thirdFriday'     => [
								'ordinal' => 'Third',
								'day'     => '5',
								'label'   => _x( 'third Friday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fourthFriday'    => [
								'ordinal' => 'Fourth',
								'day'     => '5',
								'label'   => _x( 'fourth Friday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fifthFriday'     => [
								'ordinal' => 'Fifth',
								'day'     => '5',
								'label'   => _x( 'fifth Friday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'lastFriday'      => [
								'ordinal' => 'Last',
								'day'     => '5',
								'label'   => _x( 'last Friday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'firstSaturday'   => [
								'ordinal' => 'First',
								'day'     => '6',
								'label'   => _x( 'first Saturday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'secondSaturday'  => [
								'ordinal' => 'Second',
								'day'     => '6',
								'label'   => _x( 'second Saturday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'thirdSaturday'   => [
								'ordinal' => 'Third',
								'day'     => '6',
								'label'   => _x( 'third Saturday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fourthSaturday'  => [
								'ordinal' => 'Fourth',
								'day'     => '6',
								'label'   => _x( 'fourth Saturday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fifthSaturday'   => [
								'ordinal' => 'Fifth',
								'day'     => '6',
								'label'   => _x( 'fifth Saturday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'lastSaturday'    => [
								'ordinal' => 'Last',
								'day'     => '6',
								'label'   => _x( 'last Saturday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'firstSunday'     => [
								'ordinal' => 'First',
								'day'     => '7',
								'label'   => _x( 'first Sunday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'secondSunday'    => [
								'ordinal' => 'Second',
								'day'     => '7',
								'label'   => _x( 'second Sunday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'thirdSunday'     => [
								'ordinal' => 'Third',
								'day'     => '7',
								'label'   => _x( 'third Sunday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fourthSunday'    => [
								'ordinal' => 'Fourth',
								'day'     => '7',
								'label'   => _x( 'fourth Sunday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'fifthSunday'     => [
								'ordinal' => 'Fifth',
								'day'     => '7',
								'label'   => _x( 'fifth Sunday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'lastSunday'      => [
								'ordinal' => 'Last',
								'day'     => '7',
								'label'   => _x( 'last Sunday', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
							'lastDay'         => [
								'ordinal' => 'Last',
								'day'     => '8',
								'label'   => _x( 'last day', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							],
						],
						'date'    => [
							'1'  => _x( 'day 1', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'2'  => _x( 'day 2', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'3'  => _x( 'day 3', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'4'  => _x( 'day 4', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'5'  => _x( 'day 5', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'6'  => _x( 'day 6', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'7'  => _x( 'day 7', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'8'  => _x( 'day 8', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'9'  => _x( 'day 9', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'10' => _x( 'day 10', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'11' => _x( 'day 11', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'12' => _x( 'day 12', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'13' => _x( 'day 13', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'14' => _x( 'day 14', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'15' => _x( 'day 15', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'16' => _x( 'day 16', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'17' => _x( 'day 17', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'18' => _x( 'day 18', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'19' => _x( 'day 19', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'20' => _x( 'day 20', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'21' => _x( 'day 21', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'22' => _x( 'day 22', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'23' => _x( 'day 23', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'24' => _x( 'day 24', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'25' => _x( 'day 25', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'26' => _x( 'day 26', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'27' => _x( 'day 27', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'28' => _x( 'day 28', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'29' => _x( 'day 29', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'30' => _x( 'day 30', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
							'31' => _x( 'day 31', 'label for day of month dropdown', 'tribe-events-calendar-pro' ),
						],
					],
					'ruleTypes'  => [
						'custom' => [
							'Weekly'  => _x( 'weekly (custom)', 'custom weekly option for recurrence rule type dropdown', 'tribe-events-calendar-pro' ),
							'Monthly' => _x( 'monthly (custom)', 'custom monthly option for recurrence rule type dropdown', 'tribe-events-calendar-pro' ),
							'Yearly'  => _x( 'yearly (custom)', 'custom yearly option for recurrence rule type dropdown', 'tribe-events-calendar-pro' ),
						],
					],
					'recurrence' => [
						'lockIconTooltip' => __( 'Adjust the event start date to change the recurrence pattern.', 'tribe-events-calendar-pro' ),
					],
				],
			]
		);
	}

}

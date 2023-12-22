<?php
/**
 * View: Summary View Month separator
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/month-separator.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @version 5.7.0
 *
 * @var \Tribe\Utils\Date_I18n_Immutable $group_date       The date for the date group.
 * @var array                            $events           The array of events for the date group.
 * @var WP_Post                          $event            The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( ! $event->summary_view->should_show_month_separator ) {
	return;
}
?>
<div class="tribe-events-pro-summary__month-separator">
	<time
		class="tribe-common-h7 tribe-common-h6--min-medium tribe-common-h--alt tribe-events-pro-summary__event-date-tag tribe-events-pro-summary__month-separator-text"
		datetime="<?php echo esc_attr( $group_date->format( 'Y-m' ) ); ?>"
	>
		<?php echo esc_html( $group_date->format_i18n( 'M Y' ) ); ?>
	</time>
</div>

<?php
/**
 * Multi-venue suffix
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/components/multi-venue/suffix.php
 *
 * @version 6.2.0
 *
 * @package TribeEventsCalendarPro
 *
 * @var \WP_Post $event The event post object.
 */

$num_venues = count( tec_get_venue_ids( $event->ID ) );

if ( $num_venues < 2 ) {
	return;
}

?>
<span class="tec-view__event-venue-multi-suffix">
	<span class="tec-view__event-venue-multi-suffix-separator" aria-hidden="true">&nbsp;</span>
	<?php
	/* translators: %d: venue count */
	printf(
		esc_html_x(
			'+%d more',
			'Venue count suffix',
			'tribe-events-calendar-pro'
		),
		$num_venues - 1
	);
	?>
</span>
<?php
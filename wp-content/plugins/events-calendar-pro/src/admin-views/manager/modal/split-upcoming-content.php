<?php
/**
 * Admin View: Events Manager - Split Upcoming warning content
 *
 * @version  5.9.0
 */
?>
<p>
	<?php esc_html_e( 'The event you selected and all subsequent events in the series will be separated into a new series of events that you can edit independently from the original series.', 'tribe-events-calendar-pro' ); ?>
	<strong><?php esc_html_e( 'This action cannot be undone.', 'tribe-events-calendar-pro' ); ?></strong>
	<?php esc_html_e( 'When you break events from a series their URLs will change, so any users trying to use their original URLs will receive a 404 not found error. If this is a concern, consider using a suitable plugin to set up and manage redirects.', 'tribe-events-calendar-pro' ); ?>
</p>
<?php
/**
 * Widget: Events List Event Website
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/widgets/widget-events-list/event/website.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1aiy
 *
 * @version 6.0.12
 *
 * @var                    $website The event website url.
 * @var WP_Post            $event   The event post object with properties added by the `tribe_get_event` function.
 * @var array<string,bool> $display Associative array of display settings for event meta.
 *
 * @see tribe_get_event() For the format of the event object.
 */

$website = tribe_get_event_website_link( $event->ID );

if ( empty( $website ) || empty( $display['website'] ) ) {
	return;
}

?>
<div class="tribe-common-b2 tribe-events-widget-events-list__event-website">
	<span class="tribe-events-widget-events-list__event-website">
		<?php echo $website; ?>
	</span>
</div>

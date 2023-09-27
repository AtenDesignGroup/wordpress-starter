<?php
/**
 * Widget: Countdown Complete
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/widgets/widget-countdown/complete.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1aiy
 *
 * @version 5.3.0
 *
 * @var boolean $event_done A boolean of whether the event has already started.
 * @var string  $complete   The User-supplied event completion message.
 */

$classes = [
	'tribe-common-h6',
	'tribe-common-h--alt',
	'tribe-events-widget-countdown__complete',
	'tribe-common-a11y-hidden' => ! $event_done,
];
?>
<p
	<?php tribe_classes( $classes ); ?>
	data-js="tribe-events-widget-countdown-complete"
>
	<?php echo html_entity_decode( $complete ); ?>
</p>

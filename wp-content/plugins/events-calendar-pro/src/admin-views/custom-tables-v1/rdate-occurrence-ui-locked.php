<?php
/**
 * The template that will display above the recurrence rules section when editing an RDATE Occurrence.
 *
 * @since 6.0.0
 *
 * @var string $link A link to the Event Occurrence that will allow the user to edit the recurrence rules.
 */
?>
<div>
	<p><strong><?php echo wp_kses( sprintf( _x(
					'This is a single occurrence. To change recurrence rules, go to %1$s.',
					'The message containing an actionable link to allow the user to move to an Event that will allow editing recurrence rules.',
					'tribe-events-calendar-pro'
			), $link ), 'post' ); ?></strong></p>
</div>
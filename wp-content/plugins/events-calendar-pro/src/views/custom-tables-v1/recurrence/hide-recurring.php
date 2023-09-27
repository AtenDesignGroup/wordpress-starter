<?php
/**
 * View: Top Bar Hide Recurring Events Toggle
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/\events-pro/custom-tables-v1/recurrence/hide-recurring.php
 *
 * @link https://evnt.is/1aiy
 *
 * @version 6.0.0
 *
 */
// The value might come from the bar as template value or from the request.
$is_checked = tribe_events_template_var( [ 'bar', 'hide_recurring' ], false ) || tribe_get_request_var( 'hide_subsequent_recurrences', false );
?>
<div class="tribe-common-form-control-toggle tribe-events-c-top-bar__hide-recurring">
	<input
		class="tribe-common-form-control-toggle__input tribe-events-c-top-bar__hide-recurring-input"
		id="hide-recurring"
		name="hide-recurring"
		type="checkbox"
		data-js="tribe-events-pro-top-bar-toggle-recurrence"
		<?php echo checked( $is_checked ) ?>
		autocomplete="off"
	/>
	<label
		class="tribe-common-form-control-toggle__label tribe-events-c-top-bar__hide-recurring-label"
		for="hide-recurring"
	>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %1$s: Events (plural) */
				__( 'Condense %1$s Series', 'tribe-events-calendar-pro' ),
				tribe_get_event_label_plural()
			)
		);
		?>
	</label>
</div>

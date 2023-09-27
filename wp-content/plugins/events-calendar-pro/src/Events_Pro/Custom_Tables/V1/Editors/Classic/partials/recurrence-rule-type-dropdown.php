<?php
/**
 * The template for the recurrence rule type dropdown.
 */
?>
<span class="tec-events-pro-rule-type">
	<span class="tec-events-pro-rule-type__pretext"><?php echo esc_html_x( 'Happens', 'pretext for recurrence rule type dropdown', 'tribe-events-calendar-pro' ); ?></span>
	<select
		class="tec-events-pro-rule-type__dropdown tribe-dropdown"
		data-hide-search
		data-prevent-clear
	>
		<option class="tec-events-pro-rule-type__dropdown-option" value="Date"><?php echo esc_html_x( 'once', 'once option for recurrence rule type dropdown', 'tribe-events-calendar-pro' ); ?></option>
		<option class="tec-events-pro-rule-type__dropdown-option" value="Daily"><?php echo esc_html_x( 'daily', 'daily option for recurrence rule type dropdown', 'tribe-events-calendar-pro' ); ?></option>
		<option class="tec-events-pro-rule-type__dropdown-option" value="Weekly"><?php echo esc_html_x( 'weekly', 'weekly option for recurrence rule type dropdown', 'tribe-events-calendar-pro' ); ?></option>
		<option class="tec-events-pro-rule-type__dropdown-option" value="Monthly"><?php echo esc_html_x( 'monthly', 'monthly option for recurrence rule type dropdown', 'tribe-events-calendar-pro' ); ?></option>
		<option class="tec-events-pro-rule-type__dropdown-option" value="Yearly"><?php echo esc_html_x( 'yearly', 'yearly option for recurrence rule type dropdown', 'tribe-events-calendar-pro' ); ?></option>
	</select>
	<span
		class="tec-events-pro-rule-type__tooltip dashicons dashicons-info tribe-sticky-tooltip"
		title="<?php esc_attr_e( 'This custom recurrence rule was created in a different calendar system and cannot be edited. Select a different option to create a new rule.', 'tribe-events-calendar-pro' ); ?>"
	></span>
</span>

<div class="recurrence-row custom-recurrence-months">
	<?php
	/**
	 * Filters the recurrence custom recurrence months before template for the recurrence UI.
	 *
	 * @param $template The recurrence custom recurrence months before template.
	 */
	$template = apply_filters( 'tribe_events_pro_recurrence_template_custom_recurrence_months_before', '' );
	if ( ! empty( $template ) ) {
		echo $template;
	}
	?>
	<input
			name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][month][same-day]"
			id="<?php echo esc_attr( $rule_prefix ); ?>_rule_--_month_same_day"
			type="hidden"
			value="no"
	/>
	<span		class="recurrence-month-on-the"	>
		<select
			name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][month][number]"
			id="<?php echo esc_attr( $rule_prefix ); ?>_rule_--_month_number"
			class="tribe-dropdown"
			data-field="custom-month-number"
			data-hide-search
			data-prevent-clear
		>
			{{#tribe_recurrence_select custom.month.number}}
				<optgroup label="<?php esc_attr_e( 'Use pattern:', 'tribe-events-calendar-pro' ); ?>">
					<option value="First"><?php esc_html_e( 'first', 'tribe-events-calendar-pro' ); ?></option>
					<option value="Second"><?php esc_html_e( 'second', 'tribe-events-calendar-pro' ); ?></option>
					<option value="Third"><?php esc_html_e( 'third', 'tribe-events-calendar-pro' ); ?></option>
					<option value="Fourth"><?php esc_html_e( 'fourth', 'tribe-events-calendar-pro' ); ?></option>
					<option value="Fifth"><?php esc_html_e( 'fifth', 'tribe-events-calendar-pro' ); ?></option>
					<option value="Last"><?php esc_html_e( 'last', 'tribe-events-calendar-pro' ); ?></option>
				</optgroup>
				<optgroup label="<?php esc_attr_e( 'Use date:', 'tribe-events-calendar-pro' ); ?>">
					<?php for ( $i = 1; $i <= 31; $i ++ ): ?>
						<option value="<?php echo $i ?>"><?php echo $i; ?></option>
					<?php endfor; ?>
				</optgroup>
			{{/tribe_recurrence_select}}
		</select>
		<span
			class="tribe-dependent"
			data-depends="#<?php echo esc_attr( $rule_prefix ); ?>_rule_--_month_number"
			data-condition-is-not-numeric
		>
			<select
				name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][month][day]"
				class="tribe-dropdown"
				data-field="custom-month-day"
				data-hide-search
				data-prevent-clear
			>
				{{#tribe_recurrence_select custom.month.day}}
					<option value="1"><?php esc_html_e( 'Monday', 'tribe-events-calendar-pro' ); ?></option>
					<option value="2"><?php esc_html_e( 'Tuesday', 'tribe-events-calendar-pro' ); ?></option>
					<option value="3"><?php esc_html_e( 'Wednesday', 'tribe-events-calendar-pro' ); ?></option>
					<option value="4"><?php esc_html_e( 'Thursday', 'tribe-events-calendar-pro' ); ?></option>
					<option value="5"><?php esc_html_e( 'Friday', 'tribe-events-calendar-pro' ); ?></option>
					<option value="6"><?php esc_html_e( 'Saturday', 'tribe-events-calendar-pro' ); ?></option>
					<option value="7"><?php esc_html_e( 'Sunday', 'tribe-events-calendar-pro' ); ?></option>
					<option value="-">--</option>
					<option value="8"><?php esc_html_e( 'day', 'tribe-events-calendar-pro' ); ?></option>
				{{/tribe_recurrence_select}}
			</select>
		</span>
		<span
			class="tribe-dependent tribe-field-inline-text"
			data-depends="#<?php echo esc_attr( $rule_prefix ); ?>_rule_--_month_number"
			data-condition-is-numeric
		>
			<?php echo esc_html_x( 'of the month', 'As in: day 12 of the month', 'tribe-events-calendar-pro' ); ?>
		</span>
	</span>
</div>

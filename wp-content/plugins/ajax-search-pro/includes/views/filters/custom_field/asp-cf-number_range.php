<?php foreach($filter->get() as $range): ?>
	<div class="asp-nr-container">
		<input type="text" class="asp-number-range"
			   data-asp-type="number"
			   data-asp-tsep="<?php echo esc_attr($filter->data['range_t_separator']); ?>"
			   data-asp-min="<?php echo esc_attr($filter->data['range_from']); ?>"
			   data-asp-max="<?php echo esc_attr($filter->data['range_to']); ?>"
			   id="number-range-low-<?php echo $fieldset_id; ?>"
			   aria-label="<?php echo esc_attr($filter->data['placeholder1']); ?>"
			   placeholder="<?php echo esc_attr($filter->data['placeholder1']); ?>"
			   name="aspf[<?php echo $field_name; ?>][lower]" value="<?php echo $range->value[0]; ?>">
		<input type="text" class="asp-number-range"
			   data-asp-type="number"
			   data-asp-tsep="<?php echo esc_attr($filter->data['range_t_separator']); ?>"
			   data-asp-min="<?php echo esc_attr($filter->data['range_from']); ?>"
			   data-asp-max="<?php echo esc_attr($filter->data['range_to']); ?>"
			   id="number-range-high-<?php echo $fieldset_id; ?>"
			   aria-label="<?php echo esc_attr($filter->data['placeholder2']); ?>"
			   placeholder="<?php echo esc_attr($filter->data['placeholder2']); ?>"
			   name="aspf[<?php echo $field_name; ?>][upper]" value="<?php echo $range->value[1]; ?>">
	</div>
<?php endforeach; ?>
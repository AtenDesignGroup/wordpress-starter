<?php foreach ( $filter->get() as $ck => $cpt_field ): ?>
<label class="asp_label">
	<input name="customset[]"
		   type="radio"
		   class="asp_radio"
		   <?php echo $cpt_field->default ? 'data-origvalue="1"' : ''; ?>
		   value="<?php echo esc_attr($cpt_field->value); ?>"
		<?php echo $cpt_field->selected ? 'checked="checked"' : ''; ?>>
	<?php echo esc_attr($cpt_field->label); ?>
</label>
<?php endforeach; ?>
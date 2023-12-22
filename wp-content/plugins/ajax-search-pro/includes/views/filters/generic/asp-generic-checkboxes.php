<?php foreach ( $filter->get() as $fe_field ): ?>
<div class="asp_option" tabindex="0">
    <div class="asp_option_inner">
        <input type="checkbox" value="<?php echo esc_attr($fe_field->value); ?>" id="set_<?php echo esc_attr($fe_field->value).$id; ?>"
               <?php echo $fe_field->default ? 'data-origvalue="1"' : ''; ?>
               aria-label="<?php echo esc_attr($fe_field->label); ?>"
               name="asp_gen[]" <?php echo $fe_field->selected ? ' checked="checked"' : ''; ?>/>
		<div class="asp_option_checkbox"></div>
    </div>
    <div class="asp_option_label">
        <?php echo esc_html($fe_field->label); ?>
    </div>
</div>
<?php endforeach; ?>
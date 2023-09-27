<div class='asp_select_label asp_select_single'>
<select
    aria-label="<?php echo esc_html($filter->label); ?>"
    name="asp_gen[]">
<?php foreach ( $filter->get() as $fe_field ): ?>
    <option value="<?php echo esc_attr($fe_field->value); ?>" class="asp_option"
            <?php echo $fe_field->default ? 'data-origvalue="1"' : ''; ?>
        <?php echo $fe_field->selected ? "selected='selected'" : ""; ?>>
        <?php echo esc_attr($fe_field->label); ?>
    </option>
<?php endforeach; ?>
</select>
</div>
<div>
<?php foreach ( $filter->get() as $fe_field ): ?>
    <label class="asp_label">
        <input type="radio" class="asp_radio" name="asp_gen[]"
            <?php echo $fe_field->default ? 'data-origvalue="1"' : ''; ?>
            <?php echo $fe_field->selected ? "checked='checked'" : ""; ?> value="<?php echo esc_attr($fe_field->value); ?>">
        <?php echo esc_html($fe_field->label); ?>
    </label><br>
<?php endforeach; ?>
</div>
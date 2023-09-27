<div class="asp_select_label asp_select_single">
    <select name="customset[]" aria-label="<?php echo esc_attr($filter->label); ?>">
        <?php foreach ( $filter->get() as $ck => $cpt_field ): ?>
        <option
                <?php echo $cpt_field->default ? 'data-origvalue="1"' : ''; ?>
                value="<?php echo esc_attr($cpt_field->value); ?>" <?php echo $cpt_field->selected ? 'selected="selected"' : ''; ?>>
            <?php echo esc_attr($cpt_field->label); ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>
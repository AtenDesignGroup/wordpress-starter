<div class='asp_select_label asp_select_single'>
    <select name="asp_ctf[]"
            aria-label="<?php echo esc_html($filter->label); ?>">
        <?php foreach ( $filter->get() as $ctfield ): ?>
            <option value="<?php echo esc_attr($ctfield->value); ?>" class="asp_option"
                <?php echo $ctfield->default ? 'data-origvalue="1"' : ''; ?>
                <?php echo $ctfield->selected ? "selected='selected'" : ""; ?>>
                <?php echo esc_html($ctfield->label); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
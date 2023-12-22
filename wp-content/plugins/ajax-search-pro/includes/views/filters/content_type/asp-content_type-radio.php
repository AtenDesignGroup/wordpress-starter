<div>
    <?php foreach ( $filter->get() as $ctfield ): ?>
        <label class="asp_label">
            <input type="radio" class="asp_radio" name="asp_ctf[]"
                <?php echo $ctfield->default ? 'data-origvalue="1"' : ''; ?>
                <?php echo $ctfield->selected ? "checked='checked'" : ""; ?> value="<?php echo esc_attr($ctfield->value); ?>">
            <?php echo esc_html($ctfield->label); ?>
        </label><br>
    <?php endforeach; ?>
</div>

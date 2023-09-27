<?php foreach($filter->get() as $radio): ?>
    <label class="asp_label">
        <input type="radio" class="asp_radio" name="aspf[<?php echo $field_name; ?>]"
                <?php echo $radio->default ? 'data-origvalue="1"' : ''; ?>
               value="<?php echo esc_attr($radio->value); ?>"
            <?php echo $radio->selected ? "checked='checked'" : ""; ?>/>
        <?php echo esc_html($radio->label); ?>
    </label><br>
<?php endforeach; ?>
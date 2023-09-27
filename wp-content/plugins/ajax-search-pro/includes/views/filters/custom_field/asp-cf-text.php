<?php foreach($filter->get() as $text): ?>
<input type="text"
       aria-label="<?php echo esc_html($text->label); ?>"
       value="<?php echo esc_attr($text->value); ?>"
       <?php echo $text->default ? 'data-origvalue="'.esc_attr($text->default).'"' : ''; ?>
       id="aspf<?php echo $fieldset_id; ?>[<?php echo $field_name; ?>]" name="aspf[<?php echo $field_name; ?>]">
<?php endforeach; ?>
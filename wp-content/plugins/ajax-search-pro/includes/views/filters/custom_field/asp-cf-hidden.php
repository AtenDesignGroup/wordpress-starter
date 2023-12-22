<?php foreach($filter->get() as $hidden): ?>
<input type="hidden"
       value="<?php echo esc_attr($hidden->value); ?>"
       <?php echo $hidden->default ? 'data-origvalue="'.esc_attr($hidden->value).'"' : ''; ?>
       id="aspf<?php echo $fieldset_id; ?>[<?php echo $field_name; ?>]" name="aspf[<?php echo $field_name; ?>]">
<?php endforeach; ?>
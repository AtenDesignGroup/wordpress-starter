<?php foreach($filter->get() as $date): ?>
    <textarea class="asp_datepicker_format" style="display:none !important;"><?php echo $filter->data['date_format']; ?></textarea>
    <input type="text"
           aria-label="<?php echo esc_html($filter->label); ?>"
           class="asp_datepicker asp_datepicker_field"
           placeholder="<?php echo esc_html($filter->data['placeholder']); ?>"
           value="<?php echo esc_attr($date->value); ?>"
           <?php echo 'data-origvalue="'.esc_attr($date->default).'"'; ?>
           id="aspf<?php echo $fieldset_id; ?><?php echo $field_name; ?>_real" 
           name="aspf[<?php echo $field_name; ?>_real]">
    <input type="hidden" class="asp_datepicker_hidden" 
           value="" 
           id="aspf<?php echo $fieldset_id; ?>[<?php echo $field_name; ?>]" 
           name="aspf[<?php echo $field_name; ?>]">
<?php endforeach; ?>
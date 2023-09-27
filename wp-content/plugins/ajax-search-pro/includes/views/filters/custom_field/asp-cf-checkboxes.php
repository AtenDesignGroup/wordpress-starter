<?php foreach($filter->get() as $k => $checkbox): ?>
    <div class="asp_option asp_option_cff" tabindex="0">
        <div class="asp_option_inner">
            <input type="checkbox" value="<?php echo esc_attr($checkbox->value); ?>"
                   id="aspf<?php echo $fieldset_id; ?>[<?php echo $field_name; ?>][<?php echo $k; ?>]"
                   aria-label="<?php echo esc_attr( $checkbox->label ); ?>"
                   <?php echo $checkbox->default ? 'data-origvalue="1"' : ''; ?>
                   <?php echo !empty($checkbox->select_all) ?
                       " data-targetclass='asp_cf_select_".esc_attr($field_name_nws)."' " :
                       " class='asp_cf_select_".esc_attr($field_name_nws)."' "; ?>
                   name="<?php echo !empty($checkbox->select_all) ? '' : "aspf[$field_name][$k]"; ?>"
                <?php echo $checkbox->selected ? ' checked="checked"' : ''; ?>>
			<div class="asp_option_checkbox"></div>
        </div>
        <div class="asp_option_label"><?php echo esc_html($checkbox->label); ?></div>
    </div>
<?php endforeach; ?>
<?php if ($filter->display_mode != 'hidden'): ?>
<fieldset
        data-asp_invalid_msg="<?php echo asp_icl_t("CF [".$filter->data['field']."] invalid input text" . " ($real_id)", $filter->data['invalid_input_text'], true); ?>"
        class="asp_custom_f<?php echo in_array($filter->display_mode, array('checkboxes', 'radio')) ? ' asp_sett_scroll' : ''; ?> asp_filter_cf_<?php echo esc_attr($filter->data['field']); ?> asp_filter_id_<?php echo $filter->id; ?> asp_filter_n_<?php echo $filter->position; ?><?php echo $filter->data['required'] ? ' asp_required' : ''; ?>">
    <legend><?php echo $filter->label; ?></legend>
<?php endif; ?>
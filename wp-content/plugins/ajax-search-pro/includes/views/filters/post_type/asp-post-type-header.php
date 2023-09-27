<fieldset
    data-asp_invalid_msg="<?php echo asp_icl_t("Post type filter invalid input text" . " ($real_id)", $filter->data['invalid_input_text'], true); ?>"
    class="asp_filter_cpt asp_sett_scroll<?php echo !$filter->isEmpty() ? '' : ' hiddend'; ?><?php echo $filter->display_mode == 'checkboxes' ? ' asp_checkboxes_filter_box' : ''; ?><?php echo $filter->data['required'] ? ' asp_required' : ''; ?>">
    <?php if ($filter->label != ''): ?>
    <legend><?php echo esc_html($filter->label);  ?></legend>
    <?php endif; ?>
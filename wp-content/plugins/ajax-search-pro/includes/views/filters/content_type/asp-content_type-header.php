<fieldset
        data-asp_invalid_msg="<?php echo asp_icl_t("Content type filter invalid input text", $filter->data['invalid_input_text'], true); ?>"
        class="asp_filter_content_type asp_content_type_filters asp_filter_id_<?php echo $filter->id; ?> asp_filter_n_<?php echo $filter->position; ?><?php echo $filter->data['required'] ? ' asp_required' : ''; ?>">
    <?php if ($filter->label != ''): ?>
        <legend><?php echo asp_icl_t("Content type filter label", $filter->label, true);  ?></legend>
    <?php endif; ?>
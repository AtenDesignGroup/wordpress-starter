<?php if ( $filter->isMixed() ): ?>
<fieldset data-asp_invalid_msg="<?php echo asp_icl_t("Taxonomy [".$taxonomy."] invalid input text" . " ($real_id)", $filter->data['invalid_input_text'], true); ?>"
          class="asp_filter_tax asp_filter_tax_mixed asp_cat_filter_field<?php echo $filter->display_mode == 'checkboxes' ? ' asp_checkboxes_filter_box' : ''; ?> asp_filter_id_<?php echo $filter->id; ?> asp_filter_n_<?php echo $filter->position; ?><?php echo $filter->data['required'] ? ' asp_required' : ''; ?>">
<?php else: ?>
<fieldset data-asp_invalid_msg="<?php echo asp_icl_t("Taxonomy [".$taxonomy."] invalid input text" . " ($real_id)", $filter->data['invalid_input_text'], true); ?>"
          class="asp_filter_tax asp_filter_tax_<?php echo esc_attr($filter->data['taxonomy']); ?> asp_<?php echo $filter->display_mode; ?>_filter_box asp_filter_id_<?php echo $filter->id; ?> asp_filter_n_<?php echo $filter->position; ?><?php echo $filter->data['required'] ? ' asp_required' : ''; ?>">
<?php endif; ?>
    <legend><?php echo asp_icl_t("Taxonomy [".$taxonomy."] filter box text" . " ($real_id)",  $filter->label); ?></legend>
    <div class='<?php echo $taxonomy; ?>_filter_box categoryfilter<?php echo $filter->display_mode != 'checkboxes' ? '' : ' asp_sett_scroll'; ?>'>
<fieldset class="asp_filter_generic asp_filter_id_<?php echo $filter->id; ?> asp_filter_n_<?php echo $filter->position; ?><?php echo ($filter->data['visible']) ? "" : " hiddend"; ?>">
    <?php if ($filter->label != ''): ?>
        <legend><?php echo asp_icl_t("Generic filter label", $filter->label, true);  ?></legend>
    <?php endif; ?>
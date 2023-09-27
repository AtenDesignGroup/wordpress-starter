<div class='term_filter_box asp_sett_scroll'>
    <?php foreach ($filter->get() as $kk => $term): ?>
        <?php if ($term->id <= 0): ?>
            <label class="asp_label">
                <input type="radio" class="asp_radio"
                    <?php if (isset($filter->data['custom_name'])): ?>
                        name="<?php echo $filter->data['custom_name']; ?>"
                    <?php else: ?>
                        name="<?php echo $filter->isMixed() ? "termset_single" : "termset[" . $taxonomy . "][]"; ?>"
                    <?php endif; ?>
                    <?php echo $term->default ? 'data-origvalue="1"' : ''; ?>
                    <?php echo $term->selected ? "checked='checked'" : ""; ?> value="-1">
                <?php echo asp_icl_t("Chose one option [" . $taxonomy . "]" . " ($real_id)", $term->label); ?>
            </label><br>
        <?php else: ?>
            <label class="asp_label">
                <input type="radio" class="asp_radio"
                       value="<?php echo $term->id; ?>"
                    <?php echo $term->default ? 'data-origvalue="1"' : ''; ?>
                       name='<?php echo $filter->isMixed() ? "termset_single" : "termset[" . $taxonomy . "][]"; ?>'
                    <?php echo $term->selected ? ' checked="checked"' : ''; ?>>
                <?php echo $term->label; ?>
            </label><br>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
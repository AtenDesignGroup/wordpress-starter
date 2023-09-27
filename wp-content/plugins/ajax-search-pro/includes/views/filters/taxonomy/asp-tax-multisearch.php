<div class='asp_select_label asp_select_multiple'>
    <select aria-label="<?php echo asp_icl_t('Taxonomy select [' . $taxonomy . "] ($real_id)", 'Taxonomy select for ' . $taxonomy, true) ?>"
            class='asp_gochosen asp_goselect2'
            multiple
            data-placeholder="<?php echo !empty($filter->data['placeholder']) ?
                        asp_icl_t("Multiselect placeholder [".$taxonomy."]" . " ($real_id)", $filter->data['placeholder']) : ''; ?>"
            <?php if (isset($filter->data['custom_name'])): ?>
                name="<?php echo $filter->data['custom_name']; ?>"
            <?php else: ?>
                name="<?php echo $filter->isMixed() ? "termset_single" : "termset[".$taxonomy."][]"; ?>"
            <?php endif; ?>>
        <?php foreach ( $filter->get() as $kk => $term ): ?>
            <?php if ( $term->id <= 0 ): ?>
            <option value="-1" class="asp_option_cat asp_option_cat_level-0"
                <?php echo $term->default ? 'data-origvalue="1"' : ''; ?>
                <?php echo $term->selected ? "selected='selected'" : ""; ?>>
                <?php echo asp_icl_t("Chose one option [".$taxonomy."]" . " ($real_id)", $term->label); ?>
            </option>
            <?php else: ?>
            <option class="asp_option_cat  asp_option_cat_level-<?php echo $term->level; ?>"
                    <?php echo $term->default ? 'data-origvalue="1"' : ''; ?>
                    asp_cat_parent="<?php echo $term->parent; ?>"
                    value="<?php echo $term->id; ?>"
                <?php echo $term->selected ? "selected='selected'" : ""; ?>>
                <?php echo str_repeat("&nbsp;&nbsp;", $term->level) . $term->label; ?>
            </option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
</div>
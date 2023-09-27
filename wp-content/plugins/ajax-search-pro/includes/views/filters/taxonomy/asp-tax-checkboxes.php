<?php foreach ($filter->get() as $kk => $term): ?>
    <?php if ($term->id == 0): ?>
        <div class="asp_option_cat asp_option asp_option asp_option_cat_level-0 asp_option_selectall" tabindex="0">
            <div class="asp_option_inner">
                <input id="asp_<?php echo $ch_class; ?>_all<?php echo $id; ?>"
                       aria-label="<?php echo asp_icl_t("Select all text [" . $taxonomy . "]" . " ($real_id)", $term->label, true); ?>"
                       type="checkbox" data-targetclass="asp_<?php echo $ch_class; ?>_checkbox"
                    <?php echo $term->default ? 'data-origvalue="1"' : ''; ?>
                    <?php echo($term->selected ? 'checked="checked"' : ''); ?>/>
				<div class="asp_option_checkbox"></div>
            </div>
            <div class="asp_option_label"><?php echo asp_icl_t("Select all text [" . $taxonomy . "]" . " ($real_id)", $term->label); ?></div>
        </div>
        <div class="asp_select_spacer"></div>
    <?php else: ?>
        <div class="asp_option_cat asp_option asp_option asp_option_cat_level-<?php echo $term->level; ?>"
             data-lvl="<?php echo $term->level; ?>"
             asp_cat_parent="<?php echo $term->parent; ?>" tabindex="0">
            <div class="asp_option_inner">
                <input type="checkbox" value="<?php echo $term->id; ?>" class="asp_<?php echo $ch_class; ?>_checkbox"
                       aria-label="<?php echo esc_html($term->label); ?>"
                    <?php if (isset($filter->data['custom_name'])): ?>
                        name="<?php echo $filter->data['custom_name']; ?>"
                    <?php else: ?>
                        name="<?php echo "termset[" . $term->taxonomy . "]"; ?>[]"
                    <?php endif; ?>
                       id="<?php echo $id; ?>termset_<?php echo $term->id; ?>"
                    <?php echo $term->default ? 'data-origvalue="1"' : ''; ?>
                    <?php echo($term->selected ? 'checked="checked"' : ''); ?>/>
				<div class="asp_option_checkbox"></div>
            </div>
            <div class="asp_option_label">
                <?php echo $term->label; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
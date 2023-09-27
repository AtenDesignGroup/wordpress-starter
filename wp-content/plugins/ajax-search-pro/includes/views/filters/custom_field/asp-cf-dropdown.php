<div class="asp_select_label asp_select_<?php echo $filter->data['multiple'] ? 'multiple' : 'single'; ?>">
    <select class="asp_nochosen asp_noselect2"
            aria-label="<?php echo esc_html($filter->label); ?>"
            data-placeholder="<?php echo esc_attr($filter->data['placeholder']); ?>"
        <?php echo $filter->data['multiple'] ? 'name="aspf['.$field_name.'][]" multiple' : 'name="aspf['.$field_name.']"'; ?>>
        <?php $optgroup_open = false; ?>
        <?php foreach($filter->get() as $dropdown): ?>
            <?php if ( $dropdown->option_group ): ?>
                <?php if ( $optgroup_open ): ?>
                    </optgroup><optgroup label="<?php echo esc_html( $dropdown->label ); ?>">
                    <?php $optgroup_open = false; ?>
                <?php else: ?>
                    <optgroup label="<?php echo esc_html( $dropdown->label ); ?>">
                    <?php $optgroup_open = true; ?>
                <?php endif; ?>
            <?php else: ?>
                <option value="<?php echo esc_attr( $dropdown->value ); ?>"
                    <?php echo $dropdown->default ? 'data-origvalue="1"' : ''; ?>
                    <?php echo $dropdown->selected ? ' selected="selected"' : ''; ?>><?php echo esc_html( $dropdown->label ); ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ( $optgroup_open ): ?>
            </optgroup>
            <?php $optgroup_open = false; ?>
        <?php endif; ?>
    </select>
</div>
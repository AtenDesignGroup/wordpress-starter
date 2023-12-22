<div class="asp_select_label asp_select_single">
    <select class="asp_gochosen asp_goselect2"
            aria-label="<?php echo esc_html($filter->label); ?>"
            data-placeholder="<?php echo esc_attr($filter->data['placeholder']); ?>"
        <?php echo 'name="aspf['.$field_name.']"'; ?>>
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
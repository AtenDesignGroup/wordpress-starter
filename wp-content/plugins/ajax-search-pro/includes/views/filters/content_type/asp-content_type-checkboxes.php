<?php foreach ( $filter->get() as $ctfield ): ?>
<div class="asp_option asp_option_cat <?php echo $ctfield->value == -1 ? 'asp_option_selectall' : ''; ?>" tabindex="0">
    <div class="asp_option_inner">
        <input type="checkbox" value="<?php echo esc_attr($ctfield->value); ?>" id="set_<?php echo esc_attr($ctfield->field).$id; ?>"
               aria-label="<?php echo esc_html($ctfield->label); ?>"
               <?php echo $ctfield->default ? 'data-origvalue="1"' : ''; ?>
               <?php echo $ctfield->value == -1 ?
                   " data-targetclass='asp_ctf_cbx' " :
                   " class='asp_ctf_cbx' "; ?>
               name="asp_ctf[]" <?php echo $ctfield->selected ? ' checked="checked"' : ''; ?>/>
		<div class="asp_option_checkbox"></div>
    </div>
    <div class="asp_option_label">
        <?php echo esc_html($ctfield->label); ?>
    </div>
</div>
<?php endforeach; ?>
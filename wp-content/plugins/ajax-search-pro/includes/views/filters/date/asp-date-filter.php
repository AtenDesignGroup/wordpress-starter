<?php foreach($filter->get() as $date): ?>
    <div class="asp_<?php echo esc_attr($date->name); ?>">
    <?php if ( $date->label != "" ): ?>
    <legend><?php echo esc_html($date->label); ?></legend>
    <?php endif; ?>
    <textarea class="asp_datepicker_format"
              aria-hidden="true"
              aria-label="<?php echo esc_attr($date->label); ?>"
              style="display:none !important;"><?php echo esc_html($date->format); ?></textarea>
    <input type="text"
           aria-label="<?php echo esc_attr($date->label); ?>"
           placeholder="<?php echo esc_attr($date->placeholder); ?>"
           class="asp_datepicker"
           name="<?php echo esc_attr($date->name); ?>_real"
           data-origvalue="<?php echo esc_attr($date->default); ?>"
           value="<?php echo esc_attr($date->value); ?>">
    <input type="hidden" class="asp_datepicker_hidden" name="<?php echo esc_attr($date->name); ?>" value="">
    </div>
<?php endforeach; ?>
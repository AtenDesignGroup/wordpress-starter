<?php foreach ( $filter->get() as $button ): ?>
<div class="<?php echo $button->container_class; ?>">
    <button class="<?php echo $button->button_class; ?>"><?php echo esc_html($button->label); ?></button>
</div>
<?php endforeach; ?>
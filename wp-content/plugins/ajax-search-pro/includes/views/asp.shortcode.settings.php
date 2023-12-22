<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if ( function_exists('qtranxf_getLanguage') ) {
    $qtr_lg = qtranxf_getLanguage();
} else if ( function_exists('qtrans_getLanguage') ) {
    $qtr_lg = qtrans_getLanguage();
} else {
    $qtr_lg = 0;
}
?>
<form name='options' class="asp-fss-<?php echo $style['fss_column_layout']; ?>"
	  aria-label="<?php echo esc_attr(asp_icl_t('Search Settings form aria-Label', $style['aria_settings_form_label'])); ?>"
	  autocomplete = 'off'>
    <?php do_action('asp_layout_in_form', $real_id); ?>
    <input type="hidden" name="current_page_id" value="<?php echo get_the_ID() !== false ? get_the_ID() : '-1'; ?>">
    <?php if ( function_exists('get_woocommerce_currency') ): ?>
        <input type="hidden" name="woo_currency" value="<?php echo get_woocommerce_currency(); ?>">
    <?php endif; ?>
    <?php if ( class_exists('PeepSo') ): ?>
        <input type="hidden" name="peepso_object_type" value="<?php echo apply_filters('peepso_object_type', 0); ?>">
        <input type="hidden" name="peepso_object_id" value="<?php echo apply_filters('peepso_object_id', 0); ?>">
    <?php endif; ?>
	<?php if ( $style['result_page_highlight'] == 1 ): ?>
		<input type="hidden" name="asp_highlight" value="1">
	<?php endif; ?>
    <input type='hidden' name='qtranslate_lang'
               value='<?php echo $qtr_lg; ?>'/>
    <?php if ( function_exists("pll_current_language") ): ?>
        <input type='hidden' name='polylang_lang' style="display:none;"
               value='<?php echo pll_current_language(); ?>'/>
    <?php endif; ?>
	<?php if (defined('ICL_LANGUAGE_CODE')
	          && ICL_LANGUAGE_CODE != ''
	          && defined('ICL_SITEPRESS_VERSION')
	): ?>
		<input type='hidden' name='wpml_lang'
		       value='<?php echo ICL_LANGUAGE_CODE; ?>'/>
	<?php endif; ?>
    <input type="hidden" name="filters_changed" value="0">
    <input type="hidden" name="filters_initial" value="1">
    <?php
    do_action('asp_layout_settings_before_first_item', $id);

    $fields = w_isset_def($style['field_order'], 'general|custom_post_types|custom_fields|categories_terms');
    if (strpos($fields, "general") === false) $fields = "general|" . $fields;
    if (strpos($fields, "post_tags") === false) $fields .= "|post_tags";
    if (strpos($fields, "date_filters") === false) $fields .= "|date_filters";
    $field_order = explode( '|', $fields );
    foreach ($field_order as $field)
        include("asp.shortcode.$field.php");
    ?>
    <div style="clear:both;"></div>
</form>
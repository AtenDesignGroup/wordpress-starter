<?php
/* Prevent direct access */

defined('ABSPATH') or die("You can't access this file directly.");

/**
 * A better method to store the init data.
 *
 * The JSON data is stored inside this invisible element, the it is parsed
 * and passed as an argument to the initialization method.
 */
?>
<?php ob_start(); ?>
{
    <?php if ( is_admin() ): ?>
    "homeurl": "<?php echo home_url("/"); ?>",
    <?php else: ?>
    "homeurl": "<?php echo function_exists("pll_home_url") ? PLL()->links->get_home_url( '', true ) : home_url("/"); ?>",
    <?php endif; ?>
	"is_results_page": <?php echo isset($_GET['s']) ? 1 : 0; ?>,
    "resultstype": "<?php echo $style['resultstype']; ?>",
    "resultsposition": "<?php echo $style['resultsposition']; ?>",
    "resultsSnapTo": "<?php echo $style['results_snap_to']; ?>",
    "results": {
        "width": "<?php echo $style['results_width']; ?>",
        "width_tablet": "<?php echo $style['results_width_tablet']; ?>",
        "width_phone": "<?php echo $style['results_width_phone']; ?>"
    },
    "itemscount": <?php echo $style['v_res_show_scrollbar'] == 1 ? $style['itemscount'] : "0"; ?>,
    "charcount":  <?php echo $style['charcount']; ?>,
    "highlight": <?php echo $style['highlight']; ?>,
    "highlightWholewords": <?php echo $style['highlightwholewords']; ?>,
    "singleHighlight": <?php echo $style['single_highlight']; ?>,
    "scrollToResults": {
        "enabled": <?php echo $style['scroll_to_results']; ?>,
        "offset": <?php echo intval($style['scroll_to_results_offset']); ?>
    },
    "autocomplete": {
        "enabled": <?php echo $style['autocomplete'] == 1 || $style['autocomplete'] == 2 ? 1 : 0; ?>,
		"trigger_charcount": <?php echo intval($style['autoc_trigger_charcount']); ?>,
        "googleOnly": <?php echo $style['autocomplete_source'] == 'google' ? 1 : 0; ?>,
        "lang": "<?php echo $style['autocomplete_google_lang']; ?>",
        "mobile": <?php echo $style['autocomplete'] == 1 || $style['autocomplete'] == 3 ? 1 : 0; ?>
    },
    "trigger": {
        "delay": <?php echo $style['trigger_delay']; ?>,
        "autocomplete_delay": <?php echo $style['autocomplete_trigger_delay']; ?>,
        "update_href": <?php echo $style['trigger_update_href']; ?>,
        "facet": <?php echo $style['trigger_on_facet']; ?>,
        "type": <?php echo $style['triggerontype'] == 1 ? 1 : 0; ?>,
        "click": "<?php echo $style['click_action']; ?>",
        "click_location": "<?php echo $style['click_action_location']; ?>",
        "return": "<?php echo $style['return_action']; ?>",
        "return_location": "<?php echo $style['return_action_location']; ?>",
        "redirect_url": "<?php echo apply_filters( "asp_redirect_url", $style['redirect_url'], $real_id ); ?>",
        "elementor_url": "<?php echo $style['redirect_elementor']; ?>"
    },
    "overridewpdefault": <?php echo $style['override_default_results']; ?>,
    "override_method": "<?php echo $style['override_method']; ?>",
    "settings": {
        "hideChildren": <?php echo $style['frontend_terms_hide_children']; ?>
    },
    "settingsimagepos": "<?php echo $style['settingsimagepos']; ?>",
    "settingsVisible": <?php echo $style['frontend_search_settings_visible']; ?>,
    "settingsHideOnRes": <?php echo $style['fss_hide_on_results']; ?>,
    "prescontainerheight": "<?php echo $style['prescontainerheight']; ?>",
    "closeOnDocClick": <?php echo $style['close_on_document_click']; ?>,
    "focusOnPageload": <?php echo $style['focus_on_pageload']; ?>,
    "isotopic": {
        "itemWidth": "<?php echo is_numeric($style['i_item_width']) ? $style['i_item_width'].'px' : $style['i_item_width']; ?>",
        "itemWidthTablet": "<?php echo is_numeric($style['i_item_width_tablet']) ? $style['i_item_width_tablet'].'px' : $style['i_item_width_tablet']; ?>",
        "itemWidthPhone": "<?php echo is_numeric($style['i_item_width_phone']) ? $style['i_item_width_phone'].'px' : $style['i_item_width_phone']; ?>",
        "itemHeight": "<?php echo is_numeric($style['i_item_height']) ? $style['i_item_height'].'px' : $style['i_item_height']; ?>",
        "itemHeightTablet": "<?php echo is_numeric($style['i_item_height_tablet']) ? $style['i_item_height_tablet'].'px' : $style['i_item_height_tablet']; ?>",
        "itemHeightPhone": "<?php echo is_numeric($style['i_item_height_phone']) ? $style['i_item_height_phone'].'px' : $style['i_item_height_phone']; ?>",
        "pagination": <?php echo $style['i_pagination']; ?>,
        "rows": <?php echo $style['i_rows']; ?>,
        "gutter": <?php echo $style['i_item_margin']; ?>,
        "showOverlay": <?php echo $style['i_overlay']; ?>,
        "blurOverlay": <?php echo $style['i_overlay_blur']; ?>,
        "hideContent": <?php echo $style['i_hide_content']; ?>
    },
    "loaderLocation": "<?php echo $style['loader_display_location']; ?>",
    "show_more": {
        "enabled": <?php echo $style['showmoreresults']; ?>,
        "url": "<?php echo apply_filters( "asp_show_more_url", $style['more_redirect_url'], $real_id ); ?>",
        "elementor_url": "<?php echo $style['more_redirect_elementor']; ?>",
        "action": "<?php echo $style['more_results_action']; ?>",
        "location": "<?php echo $style['more_redirect_location']; ?>",
        "infinite": <?php echo $style['more_results_infinite'] == 1 && $style['more_results_action'] == 'ajax' ? 1 : 0; ?>
    },
    "mobile": {
        "trigger_on_type": <?php echo $style['mob_trigger_on_type']; ?>,
        "click_action": "<?php echo $style['mob_click_action'] == 'same' ? $style['click_action'] : $style['mob_click_action']; ?>",
        "return_action": "<?php echo apply_filters( "asp_show_more_url", $style['mob_return_action'] == 'same' ? $style['return_action'] : $style['mob_return_action'], $real_id); ?>",
        "click_action_location": "<?php echo $style['mob_click_action'] == 'same' ? $style['click_action_location'] : $style['mob_click_action_location']; ?>",
        "return_action_location": "<?php echo $style['mob_return_action'] == 'same' ? $style['return_action_location'] : $style['mob_return_action_location']; ?>",
        "redirect_url": "<?php echo $style['mob_click_action'] == 'custom_url' || $style['mob_return_action'] == 'custom_url' ? $style['mob_redirect_url'] : $style['redirect_url']; ?>",
        "elementor_url": "<?php echo $style['mob_click_action'] == 'same' ? $style['redirect_elementor'] : $style['mob_redirect_elementor']; ?>",
        "menu_selector": "<?php echo $style['mob_auto_focus_menu_selector']; ?>",
        "hide_keyboard": <?php echo $style['mob_hide_keyboard']; ?>,
        "force_res_hover": <?php echo $style['mob_force_res_hover']; ?>,
        "force_sett_hover": <?php echo $style['mob_force_sett_hover']; ?>,
        "force_sett_state": "<?php echo $style['mob_force_sett_state']; ?>"
    },
    "compact": {
        "enabled": <?php echo $style['box_compact_layout']; ?>,
        "focus": <?php echo $style['box_compact_layout_focus_on_open']; ?>,
        "width": "<?php echo $style['box_compact_width']; ?>",
        "width_tablet": "<?php echo $style['box_compact_width_tablet']; ?>",
        "width_phone": "<?php echo $style['box_compact_width_phone']; ?>",
        "closeOnMagnifier": <?php echo $style['box_compact_close_on_magn']; ?>,
        "closeOnDocument": <?php echo $style['box_compact_close_on_document']; ?>,
        "position": "<?php echo $style['box_compact_position']; ?>",
        "overlay": <?php echo $style['box_compact_overlay']; ?>
    },
    "sb": {
        "redirect_action": "<?php echo $style['fe_sb_action']; ?>",
        "redirect_location": "<?php echo $style['fe_sb_action_location']; ?>",
        "redirect_url": "<?php echo $style['fe_sb_redirect_url']; ?>",
        "elementor_url": "<?php echo $style['fe_sb_redirect_elementor']; ?>"
    },
    "rb": {
        "action": "<?php echo $style['fe_rb_action']; ?>"
    },
    "animations": {
        "pc": {
            "settings": {
                "anim" : "<?php echo $style['sett_box_animation']; ?>",
                "dur"  : <?php echo $style['sett_box_animation_duration']; ?>
            },
            "results" : {
                "anim" : "<?php echo $style['res_box_animation']; ?>",
                "dur"  : <?php echo $style['res_box_animation_duration']; ?>
            },
            "items" : "<?php echo $style['res_items_animation']; ?>"
        },
        "mob": {
            "settings": {
                "anim" : "<?php echo$style['sett_box_animation_m']; ?>",
                "dur"  : <?php echo $style['sett_box_animation_duration_m']; ?>
            },
            "results" : {
                "anim" : "<?php echo $style['res_box_animation_m']; ?>",
                "dur"  : <?php echo $style['res_box_animation_duration_m']; ?>
            },
            "items" : "<?php echo $style['res_items_animation_m']; ?>"
        }
    },
    "select2": {
        "nores": "<?php echo esc_html(asp_icl_t("Searchable select filter placeholder" . " ($real_id)", $style['jquery_select2_nores'])); ?>"
    },
    "detectVisibility" : <?php echo $style['visual_detect_visbility']; ?>,
    "autop": {
        "state": "<?php echo $style['auto_populate']; ?>",
        "phrase": "<?php echo $style['auto_populate_phrase']; ?>",
        "count": <?php echo $style['auto_populate_count']; ?>
    },
	"wooShop": {
		"useAjax": <?php echo ( \WPDRMS\ASP\Utils\WooCommerce::isShop() && $style['woo_shop_live_search']) ? 1 : 0; ?>,
		"selector": "<?php echo $style['woo_shop_live_selector']; ?>",
		"url": "<?php echo ( \WPDRMS\ASP\Utils\WooCommerce::isShop() && $style['woo_shop_live_search']) ? get_permalink( wc_get_page_id( 'shop' ) ) : ''; ?>"
	},
	"taxArchive": {
		"useAjax": <?php echo ( \WPDRMS\ASP\Utils\Archive::isTaxonomyArchive() && $style['taxonomy_archive_live_search']) ? 1 : 0; ?>,
		"selector": "<?php echo $style['taxonomy_archive_live_selector']; ?>",
		"url": "<?php echo \WPDRMS\ASP\Utils\Archive::getCurrentArchiveURL(); ?>"
	},
	"cptArchive": {
		"useAjax": <?php echo ( \WPDRMS\ASP\Utils\Archive::isPostTypeArchive() && $style['cpt_archive_live_search']) ? 1 : 0; ?>,
		"selector": "<?php echo $style['cpt_archive_live_selector']; ?>",
		"url": "<?php echo \WPDRMS\ASP\Utils\Archive::getCurrentArchiveURL(); ?>"
	},
    "resPage": {
        "useAjax": <?php echo (is_search() && $style['res_live_search']) ? 1 : 0; ?>,
        "selector": "<?php echo $style['res_live_selector']; ?>",
        "trigger_type": <?php echo $style['res_live_trigger_type'] ?>,
        "trigger_facet": <?php echo $style['res_live_trigger_facet'] ?>,
        "trigger_magnifier": <?php echo $style['res_live_trigger_click'] ?>,
        "trigger_return": <?php echo $style['res_live_trigger_return'] ?>
    },
    "fss_layout": "<?php echo $style['fss_column_layout']; ?>",
    "scrollBar": {
        "horizontal": {
            "enabled": <?php echo $style['h_res_show_scrollbar']; ?>
        }
    },
	"divi": {
		"bodycommerce": <?php echo defined("DE_DB_WOO_VERSION") ? 1 : 0 ?>
	},
    "preventBodyScroll": <?php echo wd_asp()->o['asp_compatibility']['js_prevent_body_scroll']; ?>,
    "statistics": <?php echo get_option('asp_stat', 0) == 0 ? 0 : 1; ?>
}
<?php

$_asp_script_out = ob_get_clean();
$_asp_script_out = json_encode(json_decode($_asp_script_out));
wd_asp()->instances->add_script_data($real_id, $_asp_script_out);
?>
<div class="asp_init_data"
	 style="display:none !important;"
	 id="asp_init_id_<?php echo $id; ?>"
	 data-asp-id="<?php echo $real_id; ?>"
	 data-asp-instance="<?php echo self::$perInstanceCount[$real_id]; ?>"
	 data-aspdata="<?php echo base64_encode($_asp_script_out); ?>"></div>

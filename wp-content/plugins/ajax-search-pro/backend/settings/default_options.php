<?php
/**
 * This is a function wrapper for the options, to avoid variable scope mix ups
 */
function asp_do_init_options() {
    global $wd_asp;

    $wd_asp->options = array();
    $options = &$wd_asp->options;
    $wd_asp->o = &$wd_asp->options;

    $options['asp_glob_d'] = array(
        'additional_tag_posts' => array() // Store post IDs that have additional tags
    );

    /* Performance Tracking options */
    $options['asp_performance_def'] = array(
        'enabled' => 1
    );

    /* Index table options */
    $options['asp_it_def'] = array(
        'it_index_title' => 1,
        'it_index_content' => 1,
        'it_index_excerpt' => 1,
        'it_post_types' => array('post', 'page'),
        'it_index_tags' => 0,
        'it_index_categories' => 0,
        'it_index_taxonomies' => '',
        'it_attachment_mime_types' => 'image/jpeg, image/gif, image/png',

        'it_index_pdf_content'   => 0,
        'it_index_pdf_method'    => 'auto',
        'it_index_text_content'    => 0,
        'it_index_richtext_content'    => 0,
        'it_index_msword_content'    => 0,
        'it_index_msexcel_content'    => 0,
        'it_index_msppt_content'    => 0,
        'it_media_service_send_file'   => 1,

        'it_synonyms_as_keywords' => 0,

        'it_index_permalinks' => 0,
        'it_index_customfields' => '',
        'it_post_statuses' => 'publish',
        'it_post_password_protected' => 1,
        'it_index_author_name' => 0,
        'it_index_author_bio' => 0,
        'it_blog_ids' => '',
        'it_limit' => 25,
        'it_use_stopwords' => 0,
        'it_stopwords' => @file_get_contents(ASP_PATH . '/stopwords.txt'),
        'it_min_word_length' => 1,
        'it_extract_iframes' => 0,
        'it_extract_gutenberg_blocks' => 1,
        'it_extract_shortcodes' => 1,
        'it_exclude_shortcodes' => 'wpdreams_rpl, wpdreams_rpp',
        'it_index_on_save' => 1,
        'it_index_on_update_post_meta' => 0,
        'it_cron_enable' => 0,
        'it_cron_period' => "asp_cr_five_minutes",
        // performance
        'it_pool_size_auto'     => 1,
        'it_pool_size_one'      => 5000,
        'it_pool_size_two'      => 8000,
        'it_pool_size_three'    => 10000,
        'it_pool_size_rest'     => 10000
    );

    /* Analytics options */
    $options['asp_analytics_def'] = array(
        'analytics' => 0, // 0, event
        'analytics_tracking_id' => "",
        // Gtag on input focus
        'gtag_focus' => 1,
        'gtag_focus_action' => 'focus',
        'gtag_focus_ec' => 'ASP {search_id} | {search_name}',
        'gtag_focus_el' => 'Input focus',
        'gtag_focus_value' => '1',
        // Gtag on search start
        'gtag_search_start' => 0,
        'gtag_search_start_action' => 'search_start',
        'gtag_search_start_ec' => 'ASP {search_id} | {search_name}',
        'gtag_search_start_el' => 'Phrase: {phrase}',
        'gtag_search_start_value' => '1',
        // Gtag on search end
        'gtag_search_end' => 1,
        'gtag_search_end_action' => 'search_end',
        'gtag_search_end_ec' => 'ASP {search_id} | {search_name}',
        'gtag_search_end_el' => '{phrase} | {results_count}',
        'gtag_search_end_value' => '1',
        // Gtag on magnifier
        'gtag_magnifier' => 1,
        'gtag_magnifier_action' => 'magnifier',
        'gtag_magnifier_ec' => 'ASP {search_id} | {search_name}',
        'gtag_magnifier_el' => 'Magnifier clicked',
        'gtag_magnifier_value' => '1',
        // Gtag on return
        'gtag_return' => 1,
        'gtag_return_action' => 'return',
        'gtag_return_ec' => 'ASP {search_id} | {search_name}',
        'gtag_return_el' => 'Return button pressed',
        'gtag_return_value' => '1',
        // Gtag on try this click
        'gtag_try_this' => 1,
        'gtag_try_this_action' => 'try_this',
        'gtag_try_this_ec' => 'ASP {search_id} | {search_name}',
        'gtag_try_this_el' => 'Try this click | {phrase}',
        'gtag_try_this_value' => '1',
        // Gtag on facet change
        'gtag_facet_change' => 0,
        'gtag_facet_change_action' => 'facet_change',
        'gtag_facet_change_ec' => 'ASP {search_id} | {search_name}',
        'gtag_facet_change_el' => '{option_label} | {option_value}',
        'gtag_facet_change_value' => '1',
        // Gtag on result click
        'gtag_result_click' => 1,
        'gtag_result_click_action' => 'result_click',
        'gtag_result_click_ec' => 'ASP {search_id} | {search_name}',
        'gtag_result_click_el' => '{result_title} | {result_url}',
        'gtag_result_click_value' => '1',
    );


    /* Default caching options */
    $options['asp_caching_def'] = array(
        'caching' => 0,
        'caching_method' => 'file', // sc_file, file or db
        'image_cropping' => 0,
        'cachinginterval' => 43200
    );


    /* Compatibility Options */

// CSS and JS
    $options['asp_compatibility_def'] = array(
        'jsminified' => 1,
        'js_source' => "jqueryless-min",
        'js_source_def' => array(
            array('option' => 'Non minified', 'value' => 'jqueryless-nomin'),
            array('option' => 'Minified (default)', 'value' => 'jqueryless-min')
        ),
        'detect_ajax' => 1,
        'js_prevent_body_scroll' => 0,
        'css_compatibility_level' => "medium",
        'css_minify' => 1,
        'usetimbthumb' => 1,
        'usecustomajaxhandler' => 0,

        // JS and CSS load
        'load_google_fonts' => 1,
        'script_loading_method' => 'optimized',
        'init_instances_inviewport_only' => 1,
        'load_lazy_js' => 0,
		'css_loading_method' => 'optimized',	// optimized, inline, file
        'selective_enabled' => 0,
        'selective_front' => 1,
        'selective_archive' => 1,
        'selective_exin_logic' => 'exclude',
        'selective_exin' => '',

        // Query options
        'query_soft_check' => 0,
        'db_force_case_selects' => array(
            array('option' => 'None', 'value' => 'none'),
            array('option' => 'Sensitivity', 'value' => 'sensitivity'),
            array('option' => 'InSensitivity', 'value' => 'insensitivity')
        ),
        'use_acf_getfield' => 1,
        'db_force_case' => 'none',
        'db_force_unicode' => 0,
        'db_force_utf8_like' => 0,

        // Other options
        'rest_api_enabled' => 0,
        'meta_box_post_types' => 'post|page|product'
    );

    // MISC
    $_frontend_fields = array(
        'exact'     => 'Exact matches only',
        'title'     => 'Search in title',
        'content'   => 'Search in content',
        'excerpt'   => 'Search in excerpt'
    );

    // Content type filter defaults
    $_content_type_filter = array(
        'any'           => 'Choose One/Select all',
        'cpt'           => 'Custom post types',
        'comments'      => 'Comments',
        'taxonomies'    => 'Taxonomy terms',
        'users'         => 'Users',
        'blogs'         => 'Multisite blogs',
        'buddypress'    => 'BuddyPress content',
        'attachments'   => 'Attachments'
    );


    /* Default new search options */
    $options['asp_defaults'] = array(
// General options

        // Generic
        'owner' => 0,   // Ownership 0, aka any administrator

        // Behavior
        'search_engine' => 'regular',
        'trigger_on_facet' => 1,
        'triggerontype' => 1,
        'trigger_update_href' => 0,
        'charcount' => 0,
        'trigger_delay' => 300,              // invisible
        'autocomplete_trigger_delay' => 310, // invisible
        'click_action'  => 'results_page',   // ajax_search, first_result, results_page, woo_results_page, custom_url
        'return_action' => 'results_page',   // ajax_search, first_result, results_page, woo_results_page, custom_url
        'click_action_location' => 'same',
        'return_action_location' => 'same',
        'redirect_url' => '?s={phrase}',
        'redirect_elementor' => '',
        'override_default_results' => 1,
        'override_method' => 'get',
        'res_live_search' => 0,
        'res_live_selector' => '#main',
		'woo_shop_live_search' => 0,
		'woo_shop_live_selector' => '#main',
		'taxonomy_archive_live_search' => 0,
		'taxonomy_archive_live_selector' => '#main',
		'cpt_archive_live_search' => 0,
		'cpt_archive_live_selector' => '#main',
        'res_live_trigger_type' => 1,
        'res_live_trigger_facet' => 1,
        'res_live_trigger_click' => 0,
        'res_live_trigger_return' => 0,

        // Mobile Behavior
        'mob_display_search' => 1,
        'desktop_display_search' => 1,
        'mob_trigger_on_type' => 1,
        'mob_click_action'  => 'same',   // ajax_search, first_result, results_page, woo_results_page, custom_url
        'mob_return_action' => 'same',   // ajax_search, first_result, results_page, woo_results_page, custom_url
        'mob_click_action_location' => 'same',
        'mob_return_action_location' => 'same',
        'mob_redirect_elementor' => '',
        'mob_redirect_url' => '?s={phrase}',
        'mob_auto_focus_menu_selector' => '#menu-toggle',
        'mob_hide_keyboard' => 0,
        'mob_force_res_hover' => 0,
        'mob_force_sett_hover' => 0,
        'mob_force_sett_state' => 'none',

        'customtypes' => array('post', 'page'),
        'searchinproducts' => 1,
        'searchintitle' => 1,
        'searchincontent' => 1,
        'searchincomments' => 0,
        'searchinexcerpt' => 1,
        'search_in_permalinks' => 0,
        'search_in_ids' => 0,
        'search_all_cf' => 0,
        'customfields' => "",
        'searchinbpusers' => 0,
        'searchinbpgroups' => 0,
        'searchinbpforums' => 0,
        'post_status' => 'publish',
        'post_password_protected' => 1,
        'exactonly' => 0,
        'exact_m_secondary' => 0,
        'exact_match_location' => 'anywhere',
        'min_word_length' => 2,
        'searchinterms' => 0,

// General/Sources 2
        'return_categories' => 0,
        'return_tags' => 0,
        'return_terms' => '',
        'search_term_meta' => 0,
        'search_term_titles' => 1,
        'search_term_descriptions' => 1,
        'display_number_posts_affected' => 0,
        'return_terms_exclude_empty' => 0,
        'return_terms_exclude' => '',

// General / Attachments
        'attachments_use_index' => 'regular',
        'return_attachments' => 0,
        'search_attachments_title' => 1,
        'search_attachments_content' => 1,
        'search_attachments_caption' => 1,
        'search_attachments_terms' => 0,
        'search_attachments_ids' => 1,
        'search_attachments_cf_filters' => 0,
// base64: image/jpeg, image/gif, image/png, image/tiff, image/x-icon
        'attachment_mime_types' => 'aW1hZ2UvanBlZywgaW1hZ2UvZ2lmLCBpbWFnZS9wbmcsIGltYWdlL3RpZmYsIGltYWdlL3gtaWNvbg==',
        'attachment_use_image' => 1,
        'attachment_link_to' => 'file',
        'attachment_link_to_secondary' => 'page',
        'attachment_exclude' => "",

// General / Ordering
        'use_post_type_order' => 0,
        'post_type_order' => get_post_types(array(
            "public" => true,
            "_builtin" => false
        ), "names", "OR"),
        'results_order' => 'terms|blogs|bp_activities|comments|bp_groups|bp_users|post_page_cpt|attachments|peepso_groups|peepso_activities',

// General / Grouping
        'groupby_cpt_title' => 0,
        'groupby_term_title' => 0,
        'groupby_user_title' => 0,
        'groupby_attachment_title' => 0,

// General/Limits
        'posts_limit' => 10,
        'posts_limit_override' => 50,
        'posts_limit_distribute' => 0,
        'results_per_page' => "auto",
        'taxonomies_limit'  => 10,
        'taxonomies_limit_override' => 20,
        'users_limit' => 10,
        'users_limit_override' => 20,
        'blogs_limit' => 10,
        'blogs_limit_override' => 20,
        'buddypress_limit' => 10,
        'buddypress_limit_override' => 20,
        'comments_limit' => 10,
        'comments_limit_override' => 20,
        'attachments_limit' => 10,
        'attachments_limit_override' => 20,
        'peepso_groups_limit' => 10,
        'peepso_groups_limit_override' => 20,
        'peepso_activities_limit' => 10,
        'peepso_activities_limit_override' => 20,

        'keyword_logic' => 'and',
        'secondary_kw_logic' => 'none',

        'orderby_primary' => 'relevance DESC',
        'orderby' => 'post_date DESC',
        'orderby_primary_cf' => '',
        'orderby_secondary_cf' => '',
        'orderby_primary_cf_type' => 'numeric',
        'orderby_secondary_cf_type' => 'numeric',

// General/Image
        'show_images' => 1,
        'image_transparency' => 1,
        'image_bg_color' => "#FFFFFF",
        'image_width' => 70,
        'image_height' => 70,
        'image_display_mode' => 'cover',
        'image_apply_content_filter' => 0,
        'image_sources' => array(
            array('option' => __('Featured image', 'ajax-search-pro'), 'value' => 'featured'),
            array('option' => __('Post Content', 'ajax-search-pro'), 'value' => 'content'),
            array('option' => __('Post Excerpt', 'ajax-search-pro'), 'value' => 'excerpt'),
            array('option' => __('Custom field', 'ajax-search-pro'), 'value' => 'custom'),
            array('option' => __('Page Screenshot', 'ajax-search-pro'), 'value' => 'screenshot'),
            array('option' => __('Default image', 'ajax-search-pro'), 'value' => 'default'),
            array('option' => __('Post format icon', 'ajax-search-pro'), 'value' => 'post_format'),
            array('option' => __('Disabled', 'ajax-search-pro'), 'value' => 'disabled')
        ),

        'image_source1' => 'featured',
        'image_source2' => 'content',
        'image_source3' => 'excerpt',
        'image_source4' => 'custom',
        'image_source5' => 'default',

        'image_source_featured' => 'original',

        'image_default' => "",
        'image_custom_field' => '',
        'attachment_pdf_image' => 0,
        'tax_image_custom_field' => '',
        'tax_image_default' => '',
        'user_image_default' => '',
        'image_parser_image_number' => 1,
        'image_parser_exclude_filenames' => '',

        /* BuddyPress Options */
        'search_in_bp_activities' => 0,
        'search_in_bp_groups' => 0,
        'search_in_bp_groups_public' => 0,
        'search_in_bp_groups_private' => 0,
        'search_in_bp_groups_hidden' => 0,

        /* Peepso */
        'peep_gs_public' => 0,
        'peep_gs_closed' => 0,
        'peep_gs_secret' => 0,
        'peep_gs_title' => 1,
        'peep_gs_content' => 1,
        'peep_gs_categories' => 0,
        'peep_gs_exclude' => '',
        'peep_s_posts' => 0,
        'peep_s_comments' => 0,
        'peep_pc_follow' => 0,
        'peep_pc_public' => 0,
        'peep_pc_closed' => 0,
        'peep_pc_secret' => 0,

        /* User Search Options */
        'user_search' => 0,
        'user_login_search' => 1,
        'user_display_name_search' => 1,
        'user_first_name_search' => 1,
        'user_last_name_search' => 1,
        'user_bio_search' => 1,
        'user_email_search' => 0,
        'user_orderby_primary' => 'relevance DESC',
        'user_orderby_secondary' => 'date DESC',
        'user_orderby_primary_cf' => '',
        'user_orderby_secondary_cf' => '',
        'user_orderby_primary_cf_type' => 'numeric',
        'user_orderby_secondary_cf_type' => 'numeric',
        'user_search_exclude_roles' => "",
        "user_search_exclude_users" => array(
            "op_type" => "exclude",
            "users" => array(),
            "un_checked" => array() // store unchecked instead of checked, less overhead
        ),
        'user_search_display_images' => 1,
        'user_search_image_source' => 'default',
        'user_search_meta_fields' => array(),
        'user_bp_fields' => "",
        'user_search_title_field' => 'display_name',
        'user_search_description_field' => 'bio',
        'user_search_advanced_title_field' => '{titlefield}',
        'user_search_advanced_description_field' => '{descriptionfield}',
        'user_search_url_source' => 'default',
        'user_search_custom_url' => '?author={USER_ID}',


        /* Multisite Options */
        'searchinblogtitles' => 0,
        'blogresultstext' => "Blogs",
        'blogs' => "",

        /* Frontend search settings Options */
// suggestions
        'frontend_show_suggestions' => 0,
        'frontend_suggestions_text' => "Try these:",
        'frontend_suggestions_text_color' => "rgb(85, 85, 85)",
        'frontend_suggestions_keywords' => "phrase 1, phrase 2, phrase 3",
        'frontend_suggestions_keywords_color' => "rgb(255, 181, 86)",

        // date
        'date_filter_from' => 'disabled|2018-01-01|0,0,0',
        'date_filter_from_t' => 'Content from',
        'date_filter_from_placeholder' => 'Choose date',
        'date_filter_from_format' => 'dd-mm-yy',
        'date_filter_to' => 'disabled|2018-01-01|0,0,0',
        'date_filter_to_t' => 'Content to',
        'date_filter_to_placeholder' => 'Choose date',
        'date_filter_to_format' => 'dd-mm-yy',
        'date_filter_required' => 0,
        'date_filter_invalid_input_text' => 'Please select a date!',

// general
        'show_frontend_search_settings' => 0,
        'frontend_search_settings_visible' => 0,
        'frontend_search_settings_position' => 'hover',
        'fss_hide_on_results' => 0,

        'fss_column_layout' => 'flex',

        'fss_hover_columns' => 1,
        'fss_block_columns' => "auto",
        'fss_column_width' => 200,

        'searchinbpuserstext' => "Search in users",
        'searchinbpgroupstext' => "Search in groups",
        'searchinbpforumstext' => "Search in forums",

        'showcustomtypes' => '',
        'custom_types_label' => 'Filter by Custom Post Type',
        'cpt_display_mode' => 'checkboxes',
        'cpt_filter_default' => 'post',
        'cpt_cbx_show_select_all' => 0,
        'cpt_cbx_show_select_all_text' => 'Select all',
        'cpt_required' => 0,
        'cpt_invalid_input_text' => 'This field is required!',

        'show_frontend_tags' => "0|checkboxes|all|checked|||",
        'frontend_tags_placeholder' => 'Select tags',
        'frontend_tags_required' => 0,
        'frontend_tags_invalid_input_text' => 'This field is required!',
        'frontend_tags_header' => "Filter by Tags",
        'frontend_tags_logic' => "or",
        'frontend_tags_empty' => 0,

        'display_all_tags_option' => 0,
        'all_tags_opt_text' => 'All tags',
        'display_all_tags_check_opt' => 0,
        'all_tags_check_opt_state' => 'checked',
        'all_tags_check_opt_text' => 'Check/uncheck all',

        'settings_boxes_height' => "220px",
        'showsearchintaxonomies' => 1,
        //'terms_display_mode' => "checkboxes",

        //'showterms' => "",
        'generic_filter_label' => 'Generic filters',
        'frontend_fields' => array(
            'display_mode' => 'checkboxes', // checkboxes, dropdown, radio
            'labels' => $_frontend_fields,          // This is overwritten on save
            'selected' => array(),
            'unselected' => array('exact', 'title', 'content', 'excerpt'),
            'checked' => array('title', 'content', 'excerpt')
        ),

        'content_type_filter_label' => 'Filter by content type',
        'content_type_filter' => array(
            'display_mode' => 'checkboxes', // checkboxes, dropdown, radio
            'labels' => $_content_type_filter,          // This is overwritten on save
            'selected' => array(),
            'unselected' => array(),
            'checked' => array()
        ),

        "show_terms" => array(
            "op_type" => "include",
            "display_mode" => array(),
            "terms" => array(),
            "un_checked" => array() // store unchecked instead of checked, less overhead
        ),

        // Search button
        'fe_search_button' => 0,
        'fe_sb_action' => 'ajax_search',
        'fe_sb_action_location' => 'same',
        'fe_sb_redirect_elementor' => '',
        'fe_sb_redirect_url' => '?s={phrase}',
        'fe_sb_text' => 'Search!',
        'fe_sb_align' => 'center',
        'fe_sb_padding' => '6px 14px 6px 14px',
        'fe_sb_margin' => '4px 0 0 0',
        'fe_sb_bg' => 'rgb(212, 58, 50)',
        'fe_sb_border' => 'border:1px solid rgb(179, 51, 51);border-radius:3px 3px 3px 3px;',
        'fe_sb_boxshadow' => 'box-shadow:0px 0px 0px 0px rgba(255, 255, 255, 0);',
        'fe_sb_font' => 'font-weight:normal;font-family:Open Sans;color:rgb(255, 255, 255);font-size:13px;line-height:16px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',

        // Reset button
        'fe_reset_button' => 0,
        'fe_rb_text' => 'Reset',
        'fe_rb_action' => 'nothing',
        'fe_rb_position' => 'before',
        'fe_rb_align' => 'center',
        'fe_rb_padding' => '6px 14px 6px 14px',
        'fe_rb_margin' => '4px 0 0 0',
        'fe_rb_bg' => 'rgb(255, 255, 255)',
        'fe_rb_border' => 'border:1px solid rgb(179, 51, 51);border-radius:0px 0px 0px 0px;',
        'fe_rb_boxshadow' => 'box-shadow:0px 0px 0px 0px rgba(255, 255, 255, 0);',
        'fe_rb_font' => 'font-weight:normal;font-family:Open Sans;color:rgb(179, 51, 51);font-size:13px;line-height:16px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',

        'term_logic' => 'and',
        'taxonomy_logic' => 'and',
        'frontend_terms_empty' => 1,
        'frontend_terms_ignore_empty' => 1,
        'frontend_terms_hide_children' => 0,
        'frontend_term_hierarchy' => 1,
        'frontend_terms_hide_empty' => 0,
        'frontend_term_order' => 'name||ASC',
        'custom_field_items' => '',
        'cf_null_values' => 0,
        'cf_logic' => 'AND',
        'cf_allow_null' => 0,
        'field_order' => 'general|custom_post_types|custom_fields|categories_terms|post_tags|date_filters|search_button',

        /* Layout Options */
        // Search box
        'defaultsearchtext' => 'Search here...',
        'focus_on_pageload' => 0,
        'box_alignment' => 'inherit',
        'box_sett_hide_box' => 0,
        'auto_populate' => 'disabled',
        'auto_populate_phrase' => '',
        'auto_populate_count' => 10,

        'resultstype' => 'vertical',
        'resultsposition' => 'hover',
        'results_snap_to' => 'left',
        'results_margin' => '12px 0 0 0',
        'results_width' => 'auto',
        'results_width_phone' => 'auto',
        'results_width_tablet' => 'auto',

        'results_top_box' => 0,
        'results_top_box_text' => 'Results for <strong>{phrase}</strong> (<strong>{results_count}</strong> of <strong>{results_count_total}</strong>)',
        'results_top_box_text_nophrase' => 'Displaying <strong>{results_count}</strong> results of <strong>{results_count_total}</strong>',

        'showmoreresults' => 0,
        'showmoreresultstext' => 'More results...',
        'more_results_infinite' => 1,
        'more_results_action' => 'ajax', // ajax, redirect, results_page, woo_results_page
        'more_redirect_elementor' => '',
        'more_redirect_url' => '?s={phrase}',
        'more_redirect_location' => 'same',
        'showmorefont' => 'font-weight:normal;font-family:Open Sans;color:rgba(5, 94, 148, 1);font-size:12px;line-height:15px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'showmorefont_bg' => '#FFFFFF',

        'results_click_blank' => 0,
        'scroll_to_results' => 0,
        'scroll_to_results_offset' => 0,
        'resultareaclickable' => 1,
        'close_on_document_click' => 1,
        'show_close_icon' => 1,
        'showauthor' => 0,
        'author_field' => "display_name",
        'showdate' => 0,
        'custom_date' => 0,
        'custom_date_format' => "Y-m-d H:i:s",
        'showdescription' => 1,
        'descriptionlength' => 130,
        'description_context' => 1,
        'description_context_depth' => 15000,
        'tax_res_showdescription' => 1,
        'tax_res_descriptionlength' => 130,
        'user_res_showdescription' => 1,
        'user_res_descriptionlength' => 130,
        'noresultstext' => 'No results[ for "{phrase}"]!',
        'didyoumeantext' => "Did you mean:",
        'highlight' => 0,
        'highlightwholewords' => 1,
        'highlightcolor' => "#d9312b",
        'highlightbgcolor' => "#eee",

        'single_highlight' => 0,
        'result_page_highlight' => 0,
        'single_highlightwholewords' => 1,
        'single_highlightcolor' => "#d9312b",
        'single_highlightbgcolor' => "#eee",
        'single_highlight_scroll' => 0,
        'single_highlight_offset' => 0,
        'single_highlight_selector' => "#content",

        /* Layout Options / Compact Search Layout */
        'box_compact_layout' => 0,
        'box_compact_layout_desktop' => 1,
        'box_compact_layout_tablet' => 1,
        'box_compact_layout_mobile' => 1,
        'box_compact_layout_focus_on_open' => 1,
        'box_compact_close_on_magn' => 1,
        'box_compact_close_on_document' => 0,
        'box_compact_width' => "100%",
        'box_compact_width_tablet' => "480px",
        'box_compact_width_phone' => "320px",
        'box_compact_overlay' => 0,
        'box_compact_overlay_color' => "rgba(255, 255, 255, 0.5)",
        'box_compact_float' => "inherit",
        'box_compact_position' => "static",
        'box_compact_screen_position' => '||20%||auto||0px||auto||',
        'box_compact_position_z' => '1000',

        /* Autocomplete & Keyword suggestion options */
        'keywordsuggestions' => 1,
        'result_suggestions' => 1,
        'keyword_suggestion_source' => 'titles',
        'kws_google_places_api' => '',
        'keywordsuggestionslang' => "en",
        'keyword_suggestion_count' => 10,
        'keyword_suggestion_length' => 60,

        'autocomplete' => 1,
        'autocomplete_mode' => 'input',
        'autocomplete_instant' => 'auto',
        'autocomplete_instant_limit' => 1500,
        'autocomplete_instant_status' => 0,
        'autocomplete_instant_gen_config' => '',
        'autocomplete_source' => 'google',
        'autoc_trigger_charcount' => 0,
        'autocompleteexceptions' => '',
        'autoc_google_places_api' => '',
        'autocomplete_length' => 60,
        'autocomplete_google_lang' => "en",

// Advanced Options - Content
        'striptagsexclude' => '<abbr><b>',
        'shortcode_op' => 'remove',

        'primary_titlefield' => 0,
        'primary_titlefield_cf' => '',
        'secondary_titlefield' => -1,
        'secondary_titlefield_cf' => '',

        'primary_descriptionfield' => 1,
        'primary_descriptionfield_cf' => '',
        'secondary_descriptionfield' => 0,
        'secondary_descriptionfield_cf' => '',

        'advtitlefield' => '{titlefield}',
        'advdescriptionfield' => '{descriptionfield}',

        "exclude_content_by_users" => array(
            "op_type" => "exclude",
            "users" => array(),
            "un_checked" => array() // store unchecked instead of checked, less overhead
        ),

        'exclude_post_tags' => '',
        //'excludeterms' => '',
        'exclude_by_terms' => array(
            "op_type" => "exclude",
            "display_mode" => array(),
            "terms" => array(),
            "un_checked" => array()
        ),
        'include_by_terms' => array(
            "op_type" => "include",
            "display_mode" => array(),
            "terms" => array(),
            "un_checked" => array()
        ),
        'excludeposts' => '',
        'exclude_dates' => "exclude|disabled|date|2000-01-01|2000-01-01|0,0,0|0,0,0",
        'exclude_dates_on' => 0,

        'exclude_cpt' => array(
            'ids' => array(),
            'parent_ids' => array(),
            'op_type' => 'exclude'
        ),

        'include_cpt' => array(
            'ids' => array(),
            'parent_ids' => array(),
            'op_type' => 'include'
        ),

// Advanced Options - Grouping
        'group_by' => 'none',
        'group_header_prefix' => 'Results from',
        'group_header_suffix' => '',

        "groupby_terms" => array(
            "op_type" => "include",
            "terms" => array(),
            "ex_terms" => array(),
            "un_checked" => array() // store unchecked instead of checked, less overhead
        ),
        //"selected-groupby_terms" => array(),

        'groupby_cpt' => array(),

        "groupby_content_type" => array(
                "terms" => "Taxonomy Terms",
                "blogs" => "Blogs",
                "bp_activities" => "BuddyPress Activities",
                "comments" => "Comments",
                "bp_groups" => "BuddyPress groups",
                "users" => "Users",
                "post_page_cpt" => "Blog Content",
                "attachments" => "Attachments",
                'peepso_groups' => 'Peepso Groups',
                'peepso_activities' => 'Peepso Activities'
        ),

        'group_reorder_by_pr' => 0,
        'group_result_no_group' => 'display',
        'group_other_location' => 'bottom',
        'group_other_results_head' => 'Other results',
        'group_exclude_duplicates' => 0,

        'excludewoocommerceskus' => 0,
        'group_result_count' => 1,
        'group_show_empty' => 0,
        'group_show_empty_position' => 'default', // default, bottom, top

        'wpml_compatibility' => 1,
        'polylang_compatibility' => 1,

// Advanced Options - Visibility
        'visual_detect_visbility' => 0,
// Advanced Options - Other options
        'jquery_select2_nores' => 'No results match',
// Advanced Options - Animations
// Desktop
        'sett_box_animation' => "fadedrop",
        'sett_box_animation_duration' => 300,
        'res_box_animation' => "fadedrop",
        'res_box_animation_duration' => 300,
        'res_items_animation' => "fadeInDown",
// Mobile
        'sett_box_animation_m' => "fadedrop",
        'sett_box_animation_duration_m' => 300,
        'res_box_animation_m' => "fadedrop",
        'res_box_animation_duration_m' => 300,
        'res_items_animation_m' => "voidanim",

        // Exceptions
        'kw_exceptions' => "",
        'kw_exceptions_e' => "",

		// Accessibility
		'aria_search_form_label' => 'Search form',
		'aria_settings_form_label' => 'Search settings form',
		'aria_search_input_label' => 'Search input',
		'aria_search_autocomplete_label' => 'Search autocomplete input',
		'aria_magnifier_label' => 'Search magnifier button',


        /* Theme options */
        'themes' => 'Lite version - Simple red (default)',

        'box_width' => '100%',
        'box_width_tablet' => '100%',
        'box_width_phone' => '100%',
        'boxheight' => '34px',
        'box_margin_top' => 0,
        'box_margin_bottom' => 0,
        'boxbackground' => '0-60-rgb(225, 99, 92)-rgb(225, 99, 92)',
        'boxborder' => 'border:0px none rgb(141, 213, 239);border-radius:0px 0px 0px 0px;',
        'boxshadow' => 'box-shadow:0px 0px 0px 0px #000000 ;',

        'boxmargin' => '0px',
        'inputbackground' => '0-60-rgba(0, 0, 0, 0)-rgba(0, 0, 0, 0)',
        'inputborder' => 'border:0px solid rgb(104, 174, 199);border-radius:0px 0px 0px 0px;',
        'inputshadow' => 'box-shadow:0px 0px 0px 0px rgb(181, 181, 181) inset;',
        'inputfont' => 'font-weight:normal;font-family:Open Sans;color:rgb(255, 255, 255);font-size:12px;line-height:15px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',

        'settingsimagepos' => 'right',
        'settingsimage' => 'ajax-search-pro/img/svg/control-panel/cp4.svg',
        'settingsimage_color' => 'rgb(255, 255, 255)',
        'settingsbackground' => '1-185-rgb(190, 76, 70)-rgb(190, 76, 70)',
        'settingsbackgroundborder' => 'border:0px solid rgb(104, 174, 199);border-radius:0px 0px 0px 0px;',
        'settingsboxshadow' => 'box-shadow:0px 0px 0px 0px rgba(255, 255, 255, 0.63) ;',

        'settings_overflow_autohide' => 0,
        'settings_overflow_color' => '0-60-rgba(0, 0, 0, 0.5)-rgba(0, 0, 0, 0.5)',
        'settingsdropbackground' => '1-185-rgb(190, 76, 70)-rgb(190, 76, 70)',
        'settingsdropboxshadow' => 'box-shadow:0px 0px 0px 0px rgb(0, 0, 0) ;',
        'settingsdropfont' => 'font-weight:bold;font-family:Open Sans;color:rgb(255, 255, 255);font-size:12px;line-height:15px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'exsearchincategoriestextfont' => 'font-weight:normal;font-family:Open Sans;color:rgb(31, 31, 31);font-size:13px;line-height:15px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'settingsdroptickcolor' => 'rgb(255, 255, 255)',
        'settingsdroptickbggradient' => '1-180-rgb(34, 34, 34)-rgb(69, 72, 77)',

        'magnifier_position' => 'right',
        'magnifierimage' => 'ajax-search-pro/img/svg/magnifiers/magn6.svg',
        'magnifierimage_color' => 'rgb(255, 255, 255)',
        'magnifierbackground' => '1-180-rgb(190, 76, 70)-rgb(190, 76, 70)',
        'magnifierbackgroundborder' => 'border:0px solid rgb(0, 0, 0);border-radius:0px 0px 0px 0px;',
        'magnifierboxshadow' => 'box-shadow:0px 0px 0px 0px rgba(255, 255, 255, 0.61) ;',

        'close_icon_background' => 'rgb(51, 51, 51)',
        'close_icon_fill' => 'rgb(254, 254, 254)',
        'close_icon_outline' => 'rgba(255, 255, 255, 0.9)',

        'loader_display_location' => 'auto',
        'loader_image' => 'simple-circle',
        'loadingimage_color' => 'rgb(255, 255, 255)',

// Theme options - Search Text Button
        'display_search_text' => '0',
        'hide_magnifier' => '0',
        'search_text' => "Search",
        'search_text_position' => 'right',
        'search_text_font' => 'font-weight:normal;font-family:Open Sans;color:rgba(51, 51, 51, 1);font-size:15px;line-height:normal;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',

// Theme options - Results Information Box
        'ritb_font' => 'font-weight:normal;font-family:Open Sans;color:rgb(74, 74, 74);font-size:13px;line-height:16px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'ritb_padding' => '6px 12px 6px 12px',
        'ritb_margin' => '0 0 0 0',
        'ritb_bg' => 'rgb(255, 255, 255)',
        'ritb_border' => 'border:1px none rgb(81, 81, 81);border-radius:0px 0px 0px 0px;',

        'vresultinanim' => 'rollIn',
        'vresulthbg' => '0-60-rgb(245, 245, 245)-rgb(245, 245, 245)',
        'resultsborder' => 'border:0px none #000000;border-radius:0px 0px 0px 0px;',
        'resultshadow' => 'box-shadow:0px 0px 0px 0px #000000 ;',
        'resultsbackground' => 'rgb(225, 99, 92)',
        'resultscontainerbackground' => 'rgb(255, 255, 255)',
        'resultscontentbackground' => '#ffffff',
        'titlefont' => 'font-weight:bold;font-family:Open Sans;color:rgba(20, 84, 169, 1);font-size:14px;line-height:20px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'import-titlefont' => "@import url(https://fonts.googleapis.com/css?family=Open+Sans:300|Open+Sans:400|Open+Sans:700);",
		'authorfont' => 'font-weight:bold;font-family:Open Sans;color:rgba(161, 161, 161, 1);font-size:12px;line-height:13px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'datefont' => 'font-weight:normal;font-family:Open Sans;color:rgba(173, 173, 173, 1);font-size:12px;line-height:15px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'descfont' => 'font-weight:normal;font-family:Open Sans;color:rgba(74, 74, 74, 1);font-size:13px;line-height:13px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'import-descfont' => "@import url(https://fonts.googleapis.com/css?family=Lato:300|Lato:400|Lato:700);",
        'groupfont' => 'font-weight:normal;font-family:Open Sans;color:rgba(74, 74, 74, 1);font-size:13px;line-height:13px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'groupingbordercolor' => 'rgb(248, 248, 248)',
        'spacercolor' => 'rgba(204, 204, 204, 1)',

// Theme options - Results Information Box
		'kw_suggest_font' => 'font-weight:normal;font-family:inherit;color:rgba(74, 74, 74, 1);font-size:1rem;line-height:1.2rem;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
		'kw_suggest_kw_font_color' => 'rgba(20, 84, 169, 1)',
		'kw_suggest_didyoumean_font_color' => 'rgba(234, 67, 53, 1)',
		'kw_suggest_bg' => 'rgb(255, 255, 255)',
		'kw_suggest_border' => 'border:0px solid rgb(0, 0, 0);border-radius:0px 0px 0px 0px;',
		'kw_suggest_box_shadow' => 'box-shadow:0px 5px 5px -5px #dfdfdf;',
        'kw_suggest_padding' => '6px 12px 6px 12px',
        'kw_suggest_margin' => '0 0 0 0',

// Theme options - Vertical results
        'resultitemheight' => "auto",
        'itemscount' => 4,
        'v_res_overflow_autohide' => 1,
        'v_res_overflow_color' => '0-60-rgba(0, 0, 0, 0.5)-rgba(0, 0, 0, 0.5)',
        'v_res_max_height' => 'none',
        'v_res_show_scrollbar' => 1,
        'v_res_max_height_tablet' => 'none',
        'v_res_max_height_phone' => 'none',
        'v_res_column_count' => 1,
        'v_res_column_min_width' => '200px',
        'v_res_column_min_width_tablet' => '200px',
        'v_res_column_min_width_phone' => '200px',

// Theme options - Settings image
        'settingsimage_custom' => "",
        'magnifierimage_custom' => "",

        'loadingimage' => "/ajax-search-pro/img/svg/loading/loading-spin.svg",
        'loadingimage_custom' => "",

        'groupbytextfont' => 'font-weight:bold;font-family:Open Sans;color:rgba(5, 94, 148, 1);font-size:11px;line-height:13px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'exsearchincategoriesboxcolor' => "rgb(246, 246, 246)",

        'blogtitleorderby' => 'desc',

        'hreswidth' => '150px',
        'h_res_show_scrollbar' => 1,
        'hor_img_height' => '150px',
        'horizontal_res_height' => 'auto',
        'hressidemargin' => '8px',
        'hrespadding' => '7px',
        'hresultinanim' => 'bounceIn',
        'hboxbg' => '1-60-rgb(225, 99, 92)-rgb(225, 99, 92)',
        'h_res_overflow_autohide' => 1,
        'h_res_overflow_color' => '0-60-rgba(0, 0, 0, 0.5)-rgba(0, 0, 0, 0.5)',
        'hboxborder' => 'border:0px solid rgb(219, 233, 238);border-radius:0px 0px 0px 0px;',
        'hboxshadow' => 'box-shadow:0px 0px 4px -3px rgb(0, 0, 0) inset;',
        'hresultbg' => '0-60-rgba(255, 255, 255, 1)-rgba(255, 255, 255, 1)',
        'hresulthbg' => '0-60-rgba(255, 255, 255, 1)-rgba(255, 255, 255, 1)',
        'hresultborder' => 'border:0px none rgb(250, 250, 250);border-radius:0px 0px 0px 0px;',
        'hresultshadow' => 'box-shadow:0px 0px 6px -3px rgb(0, 0, 0);',
        'hresultimageborder' => 'border:0px none rgb(250, 250, 250);border-radius:0px 0px 0px 0px;',
        'hresultimageshadow' => 'box-shadow:0px 0px 9px -6px rgb(0, 0, 0) inset;',
        'hhidedesc' => 0,

//Isotopic Syle options
        'i_ifnoimage' => "description",
        'i_item_width' => '200px',
        'i_item_width_tablet' => '200px',
        'i_item_width_phone' => '200px',
        'i_item_height' => '200px',
        'i_item_height_tablet' => '200px',
        'i_item_height_phone' => '200px',
        'i_item_margin' => 5,
        'i_res_item_background' => 'rgb(255, 255, 255);',
        'i_res_item_content_background' => 'rgba(0, 0, 0, 0.28);',

        'i_res_magnifierimage' => "/ajax-search-pro/img/svg/magnifiers/magn4.svg",
        'i_res_custom_magnifierimage' => "",

        'i_overlay' => 1,
        'i_overlay_blur' => 1,
        'i_hide_content' => 1,
        'i_animation' => 'bounceIn',
        'i_pagination' => 1,
        'i_rows' => 2,
        'i_res_container_bg' => 'rgba(255, 255, 255, 0);',

        'i_pagination_position' => "top",
        'i_pagination_background' => "rgb(228, 228, 228);",
        'i_pagination_arrow' => "/ajax-search-pro/img/svg/arrows/arrow1.svg",
        'i_pagination_arrow_background' => "rgb(76, 76, 76);",
        'i_pagination_arrow_color' => "rgb(255, 255, 255);",
        'i_pagination_page_background' => "rgb(244, 244, 244);",
        'i_pagination_font_color' => "rgb(126, 126, 126);",


//Polaroid Style options
        'pifnoimage' => "removeres",
        'pshowdesc' => 1,
        'prescontainerheight' => '400px',
        'preswidth' => '200px',
        'presheight' => '300px',
        'prespadding' => '25px',
        'prestitlefont' => 'font-weight:normal;font-family:Open Sans;color:rgba(167, 160, 162, 1);font-size:16px;line-height:20px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'pressubtitlefont' => 'font-weight:normal;font-family:Open Sans;color:rgba(133, 133, 133, 1);font-size:13px;line-height:18px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'pshowsubtitle' => 0,

        'presdescfont' => 'font-weight:normal;font-family:Open Sans;color:rgba(167, 160, 162, 1);font-size:14px;line-height:17px;text-shadow:0px 0px 0px rgba(255, 255, 255, 0);',
        'prescontainerbg' => '0-60-rgba(221, 221, 221, 1)-rgba(221, 221, 221, 1)',
        'pdotssmallcolor' => '0-60-rgba(170, 170, 170, 1)-rgba(170, 170, 170, 1)',
        'pdotscurrentcolor' => '0-60-rgba(136, 136, 136, 1)-rgba(136, 136, 136, 1)',
        'pdotsflippedcolor' => '0-60-rgba(85, 85, 85, 1)-rgba(85, 85, 85, 1)',

// Custom CSS
        'custom_css' => '',
        'custom_css_h' => '',
        'res_z_index' => 11000,
        'sett_z_index' => 11001,

//Relevance options
        'userelevance' => 1,
        'etitleweight' => 10,
        'econtentweight' => 9,
        'eexcerptweight' => 9,
        'etermsweight' => 7,
        'titleweight' => 3,
        'contentweight' => 2,
        'excerptweight' => 2,
        'termsweight' => 2,

        'it_title_weight' => 100,
        'it_content_weight' => 20,
        'it_excerpt_weight' => 10,
        'it_terms_weight' => 10,
        'it_cf_weight' => 8,
        'it_author_weight' => 8
    );

}

/**
 * Merge the default options with the stored options.
 */
function asp_parse_options() {
    foreach ( wd_asp()->o as $def_k => $o ) {
        if ( preg_match("/\_def$/", $def_k) ) {
            $ok = preg_replace("/\_def$/", '', $def_k);

            // Dang, I messed up this elegant solution..
            if ( $ok == "asp_it")
                $ok = "asp_it_options";

            wd_asp()->o[$ok] = asp_decode_params( get_option($ok, wd_asp()->o[$def_k]) );
            wd_asp()->o[$ok] = array_merge(wd_asp()->o[$def_k], wd_asp()->o[$ok]);
        }
    }
    // Long previous version compatibility
    if ( wd_asp()->o['asp_caching'] === false )
        wd_asp()->o['asp_caching'] = wd_asp()->o['asp_caching_def'];

    // The globals are a sitewide options
    wd_asp()->o['asp_glob'] = get_site_option('asp_glob', wd_asp()->o['asp_glob_d']);
    wd_asp()->o['asp_glob'] = array_merge(wd_asp()->o['asp_glob_d'], wd_asp()->o['asp_glob']);
}

function asp_reset_option($key, $global = false) {
    if ( isset(wd_asp()->o[$key], wd_asp()->o[$key . '_def']) ) {
        wd_asp()->o[$key] = wd_asp()->o[$key . '_def'];
        asp_save_option($key, $global);
    }
}

/*
 * Updates the option value from the wd_asp()->o[key] array to the database
 */
function asp_save_option($key, $global = false) {
    if ( !isset(wd_asp()->o[$key]) )
        return false;

    if ( $global ) {
        return update_site_option($key, wd_asp()->o[$key]);
    } else {
        return update_option($key, wd_asp()->o[$key]);
    }
}

/**
 * This is the same as wd_asp()->instances->decode_params()
 * Needed, because the wd_asp()->instances is not set at this point yet.
 * Decodes the base encoded params after getting them from the DB
 *
 * @param $params
 * @return mixed
 */
function asp_decode_params( $params ) {
    /**
     * New method for future use.
     * Detects if there is a _decode_ prefixed input for the current field.
     * If so, then decodes and overrides the posted value.
     */
    foreach ($params as $k=>$v) {
        if (gettype($v) === "string" && substr($v, 0, strlen('_decode_')) == '_decode_') {
            $real_v = substr($v, strlen('_decode_'));
            $params[$k] = json_decode(base64_decode($real_v), true);
        }
    }
    return $params;
}

asp_do_init_options();
asp_parse_options();
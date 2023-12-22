jQuery(function($){
    if (typeof wp != 'undefined' && typeof wp.i18n != 'undefined') {
        var {__, _x, _n, _nx} = wp.i18n;
    } else {
        var __ = _x = _n = _nx = function (s) {
            return s;
        };
    }

    function submit_mimic(action, method, input) {
        'use strict';
        var form;
        form = $('<form />', {
            action: action,
            method: method,
            style: 'display: none;'
        });
        if (typeof input !== 'undefined' && input !== null) {
            $.each(input, function (name, value) {
                $('<input />', {
                    type: 'hidden',
                    name: name,
                    value: value
                }).appendTo(form);
            });
        }
        form.appendTo('body').submit();
    }

    // Coditional rules via attributes
    WPD.Conditionals.init('div[tabid=1]');
    WPD.Conditionals.init('div[tabid=5]');
    WPD.Conditionals.init('div[tabid=404]');
    WPD.Conditionals.init('div[tabid=405]');

    // --- Safety check on max_input_vars
    if ( $('#asp_options_serialized').length > 0 ) {
        $('form[name="asp_data"]').submit(function(e){
            if ( typeof(WD_Helpers.Base64) != "undefined" ) {
                // Send the back-up form instead, with 1 variable only
                e.preventDefault();
                $('#asp_options_serialized').val( WD_Helpers.Base64.encode($('form[name="asp_data"]').serialize()) );
                $('form[name="asp_data_serialized"]').submit();
            }
       });
    }

    // -- Input restrictions for text inputs
    $('#wpdreams input.wpd-integer-only').on("input keydown keyup mousedown mouseup select contextmenu drop paste", function() {
        if ( /[^0-9]|^0+(?!$)/g.test($(this).val()) ) {
            // Filter non-digits from input value.
            $(this).val( $(this).val().replace(/[^0-9]|^0+(?!$)/g, '') );
        }
    });

    // -- Reset search instance to defaults.
    $('#wpdreams input[name^=reset_][type=button].asp_submit.asp_submit_reset').on('click', function(){
        var r = confirm("Are you sure you want to reset the options for this instance to defaults? All changes to this search will be lost!");
        if ( r == true) {
            var name = $(this).attr('name');
            var data = {
                'asp_sett_nonce': $('#asp_sett_nonce').val()
            };
            data[name] = name;
            submit_mimic('', 'post', data);
        }
    });

    // --- SHORTCODES AND GENERATOR ---
    $('.asp_b_shortcodes_menu').on('click', function(){
        $(this).parent().toggleClass('asp_open');
    });

    function sc_generate() {
        var items = [];
        var ratios = [];
        var sid = $('#wpd_shortcode_modal').attr('sid');

        $('#wpd_shortcode_modal ul li').each(function(){
            if ( !$(this).hasClass('hiddend') ) {
                items.push($(this).attr('item'));
                ratios.push($('input',this).val());
            }
        });

        var elements = items.join(',');
        if ( elements != "" )
            elements = " elements='" + elements + "'";
        var ratio = ratios.join('%,');
        if ( ratio != "" )
            ratio = " ratio='" + ratio + "%'";

        $('#wpd_shortcode_modal textarea').val('[wd_asp' + elements + ratio + " id=" + sid + "]");
    }

    $('#shortcode_generator').on('click', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        sc_generate();
        $('#wpd_shortcode_modal').removeClass('hiddend');
        $('#wpd_shortcode_modal_bg').css('display', 'block');
    });
    $('#wpd_shortcode_modal .wpd-modal-close, #wpd_shortcode_modal_bg').on('click', function(){
        $('#wpd_shortcode_modal').addClass('hiddend');
        $('#wpd_shortcode_modal_bg').css('display', 'none');
    });

    $('#wpd_shortcode_modal li a.deleteIcon').on('click', function(){
        $(this).parent().addClass('hiddend');
        $('#wpd_shortcode_modal button[item=' + $(this).parent().attr('item') + ']').removeAttr('disabled');
        sc_generate();
    });
    $('#wpd_shortcode_modal li input').on('change', function(){
        $(this).parent().parent().css('width', $(this).val() + "%");
        sc_generate();
    });
    $('#wpd_shortcode_modal .wpd_generated_shortcode button').on('click', function(){
        $(this).attr('disabled', 'disabled');
        $('#wpd_shortcode_modal li[item=' + $(this).attr('item') + ']').detach().appendTo($("#wpd_shortcode_modal .sortablecontainer .ui-sortable"));
        $('#wpd_shortcode_modal li[item=' + $(this).attr('item') + ']').removeClass('hiddend');
        sc_generate();
    });

    $("#wpd_shortcode_modal .sortablecontainer .ui-sortable").sortable({}, {
        update: function (event, ui) {}
    }).disableSelection();
    $("#wpd_shortcode_modal .sortablecontainer .ui-sortable").on('sortupdate', function(event, ui) {
        sc_generate();
    });

    $('#wpd_shortcode_modal .wpd_generated_shortcode select').on('change', function(){
        var items = ['search', 'settings', 'results'];
        var _val = $(this).val().split('|');
        var elements = _val[0].split(',');
        var ratios = _val[1].split(',');

        $('#wpd_shortcode_modal li a.deleteIcon').trigger('click');
        $.each(elements, function(i, v) {
            $('#wpd_shortcode_modal .wpd_generated_shortcode button[item='+ items[v] +']').trigger('click');
            $('#wpd_shortcode_modal li[item=' + items[v] + '] input').val(ratios[i]).trigger('change');
        });

        sc_generate();
    });
    // --------------------------------

    // --------------- Navigate to a tab on specific links -----------------
    $(".item a.asp_to_tab").on("click", function(){
        var tabid = $(this).attr("tabid");
        $('.tabs a[tabid=' + Math.floor( tabid / 100 ) + ']').trigger('click');
        $('.tabs a[tabid=' + tabid + ']').trigger('click');
        if ( typeof $(this).data('asp-os-highlight') !== 'undefined' ) {
            $('.asp-os-highlighted').removeClass("asp-os-highlighted");
            $("*[name='"+$(this).data('asp-os-highlight')+"']").closest('.item').addClass("asp-os-highlighted");
        }
    });
    // ----------------------------------------------------------------------

    //var ajaxurl = '<?php bloginfo("url"); ?>' + "/wp-content/plugins/ajax-search-pro/ajax_search.php";
    $('.tabs a[tabid=6]').on('click', function() {
        $('.tabs a[tabid=601]').trigger('click');
    });
    $('.tabs a[tabid=1]').on('click', function() {
        $('.tabs a[tabid=101]').trigger('click');
    });
    $('.tabs a[tabid=2]').on('click', function() {
        $('.tabs a[tabid=201]').trigger('click');
    });
    $('.tabs a[tabid=4]').on('click', function() {
        $('.tabs a[tabid=401]').trigger('click');
    });
    $('.tabs a[tabid=3]').on('click', function() {
        $('.tabs a[tabid=301]').trigger('click');
    });
    $('.tabs a[tabid=5]').on('click', function() {
        $('.tabs a[tabid=501]').trigger('click');
    });
    $('.tabs a[tabid=7]').on('click', function() {
        $('.tabs a[tabid=701]').trigger('click');
    });
    $('.tabs a[tabid=8]').on('click', function() {
        $('.tabs a[tabid=801]').trigger('click');
    });

    $('.tabs a').on('click', function(){
        $('#sett_tabid').val($(this).attr('tabid'));
        location.hash = $(this).attr('tabid');
    });

    /*$('select[name="cpt_display_mode"]').on('change', function(){
        if ($(this).val() == "checkboxes") {
            $('input[name=cpt_cbx_show_select_all]')
                .closest('div').removeClass('disabled');
            $('select[name="cpt_filter_default"]').attr('disabled', 'disabled');
        } else {
            $('input[name=cpt_cbx_show_select_all]').val(0)
                .closest('div').addClass('disabled').find('.triggerer').trigger('click');
            $('select[name="cpt_filter_default"]').removeAttr('disabled');
        }
    });
    $('select[name="cpt_display_mode"]').trigger('change');*/

    $('input[name=cpt_cbx_show_select_all]').on('change', function(){
        if ($(this).val() == 0) {
            $('input[name=cpt_cbx_show_select_all_text]').closest('div').addClass('disabled');
        } else {
            $('input[name=cpt_cbx_show_select_all_text]').closest('div').removeClass('disabled');
        }
    });
    $('input[name=cpt_cbx_show_select_all]').trigger('change');

    $('input[name=cpt_required]').on('change', function(){
        if ($(this).val() == 0) {
            $('input[name=cpt_invalid_input_text]').closest('div').addClass('disabled');
        } else {
            $('input[name=cpt_invalid_input_text]').closest('div').removeClass('disabled');
        }
    });
    $('input[name=cpt_required]').trigger('change');

    // ---------------------- General/Sources 1 ------------------------
    /*$('input[name="search_all_cf"]').on('change', function(){
        if ($(this).val() == 1)
            $('input[name="customfields"]').parent().addClass('disabled');
        else
            $('input[name="customfields"]').parent().removeClass('disabled');
    });
    $('input[name="search_all_cf"]').trigger('change');*/
    // -----------------------------------------------------------------

    // ---------------------- General/Behavior ------------------------
    /*$('select[name=click_action], select[name=return_action]').on('change', function(){
        var redirect = false;
        var elementor = false;
        $('select[name=click_action], select[name=return_action]').each(function(i, v) {
            if ( $(v).val() == 'custom_url' ) {
                redirect = true;
            }
            if ( $(v).val() == 'elementor_page' ) {
                elementor = true;
            }
        });
        if ( redirect ) {
            $('input[name=redirect_url]').closest('.item').removeClass('hiddend');
        } else {
            $('input[name=redirect_url]').closest('.item').addClass('hiddend');
        }
        if ( elementor ) {
            $('input[name=redirect_elementor]').closest('.item').removeClass('hiddend');
        } else {
            $('input[name=redirect_elementor]').closest('.item').addClass('hiddend');
        }

        var $loc = $('select[name*=_action_location]', $(this).closest('.item')).parent();
        if (
            $(this).val() == 'ajax_search' ||
            $(this).val() == 'nothing' ||
            $(this).val() == 'same'
        ) {
            $loc.addClass('hiddend');
        } else{
            $loc.removeClass('hiddend');
        }
    });
    $('select[name=click_action]').trigger('change');
    $('select[name=return_action]').trigger('change');*/

    $('select[name=mob_click_action], select[name=mob_return_action]').on('change', function(){
        var redirect = false;
        var elementor = false;
        $('select[name=mob_click_action], select[name=mob_return_action]').each(function(i, v) {
            if ( $(v).val() == 'custom_url' ) {
                redirect = true;
            }
            if ( $(v).val() == 'elementor_page' ) {
                elementor = true;
            }
        });
        if ( redirect ) {
            $('input[name=mob_redirect_url]').closest('.item').removeClass('hiddend');
        } else {
            $('input[name=mob_redirect_url]').closest('.item').addClass('hiddend');
        }
        if ( elementor ) {
            $('input[name=mob_redirect_elementor]').closest('.item').removeClass('hiddend');
        } else {
            $('input[name=mob_redirect_elementor]').closest('.item').addClass('hiddend');
        }

        var $loc = $('select[name*=_action_location]', $(this).closest('.item')).parent();
        if (
            $(this).val() == 'ajax_search' ||
            $(this).val() == 'nothing' ||
            $(this).val() == 'same'
        ) {
            $loc.addClass('hiddend');
        } else{
            $loc.removeClass('hiddend');
        }
    });
    $('select[name=mob_click_action]').trigger('change');
    $('select[name=mob_return_action]').trigger('change');

    $('input[name="exactonly"]').on('change', function(){
        if ($(this).val() == 0 || $('select[name="secondary_kw_logic"]').val() == 'none') {
            $('input[name="exact_m_secondary"]').val(0);
            $('input[name="exact_m_secondary"]').closest('div').find('.triggerer').trigger('click');
        }
    }).trigger('change');
    $('select[name="secondary_kw_logic"]').on('change', function(){
        if ($(this).val() == 'none' || $('input[name="exactonly"]').val() == 0) {
            $('input[name="exact_m_secondary"]').val(0);
            $('input[name="exact_m_secondary"]').closest('div').find('.triggerer').trigger('click');
        }
    }).trigger('change');

    $('select[name="orderby_primary"]').on('change', function(){
        if ($(this).val().indexOf('customf') == -1) {
            $('input[name="orderby_primary_cf"]').parent().addClass('hiddend');
            $('select[name="orderby_primary_cf_type"]').parent().addClass('hiddend');
        } else {
            $('input[name="orderby_primary_cf"]').parent().removeClass('hiddend');
            $('select[name="orderby_primary_cf_type"]').parent().removeClass('hiddend');
        }
    });
    $('select[name="orderby_primary"]').trigger('change');
    $('select[name="orderby"]').on('change', function(){
        if ($(this).val().indexOf('customf') == -1) {
            $('input[name="orderby_secondary_cf"]').parent().addClass('hiddend');
            $('select[name="orderby_secondary_cf_type"]').parent().addClass('hiddend');
        } else {
            $('input[name="orderby_secondary_cf"]').parent().removeClass('hiddend');
            $('select[name="orderby_secondary_cf_type"]').parent().removeClass('hiddend');
        }
    });
    $('select[name="orderby"]').trigger('change');

    $('input[name="override_default_results"]').on('change', function(){
        if ($(this).val() == 0)
            $('input[name="results_per_page"]').parent().addClass('disabled');
        else
            $('input[name="results_per_page"]').parent().removeClass('disabled');
    });
    $('input[name="override_default_results"]').trigger('change');

    $('input[name=res_live_search]').on('change', function(){
        if ($(this).val() == 0)
            $('input[name="res_live_selector"]').closest('.item').addClass('disabled');
        else
            $('input[name="res_live_selector"]').closest('.item').removeClass('disabled');
    });
    $('input[name=res_live_search]').trigger('change');
    // -----------------------------------------------------------------

    // ---------------------- General/User search ----------------------
   /* $('select[name="user_orderby_primary"]').on('change', function(){
        if ($(this).val().indexOf('customf') == -1) {
            $('input[name="user_orderby_primary_cf"]').parent().addClass('hiddend');
            $('select[name="user_orderby_primary_cf_type"]').parent().addClass('hiddend');
        } else {
            $('input[name="user_orderby_primary_cf"]').parent().removeClass('hiddend');
            $('select[name="user_orderby_primary_cf_type"]').parent().removeClass('hiddend');
        }
    });
    $('select[name="user_orderby_primary"]').trigger('change');

    $('select[name="user_orderby_secondary"]').on('change', function(){
        if ($(this).val().indexOf('customf') == -1) {
            $('input[name="user_orderby_secondary_cf"]').parent().addClass('hiddend');
            $('select[name="user_orderby_secondary_cf_type"]').parent().addClass('hiddend');
        } else {
            $('input[name="user_orderby_secondary_cf"]').parent().removeClass('hiddend');
            $('select[name="user_orderby_secondary_cf_type"]').parent().removeClass('hiddend');
        }
    });
    $('select[name="user_orderby_secondary"]').trigger('change');*/
    // -----------------------------------------------------------------

    // -------------------- Peepso -------------------------------------
    $('input[name=peep_gs_public], input[name=peep_gs_closed], input[name=peep_gs_secret]').on('change', function(){
        var enabled =
            $('input[name=peep_gs_public]').val() == 1 ||
            $('input[name=peep_gs_closed]').val() == 1 ||
            $('input[name=peep_gs_secret]').val() == 1;
        if ( enabled ) {
            $('input[name=peep_gs_title]').closest('.item').removeClass('disabled');
            $('textarea[name=peep_gs_exclude]').closest('.item').removeClass('disabled');
        } else {
            $('input[name=peep_gs_title]').closest('.item').addClass('disabled');
            $('textarea[name=peep_gs_exclude]').closest('.item').addClass('disabled');
        }
    });
    $('input[name=peep_gs_public]').trigger('change');
    $('input[name=peep_s_posts], input[name=peep_s_comments]').on('change', function(){
        var enabled =
            $('input[name=peep_s_posts]').val() == 1 ||
            $('input[name=peep_s_comments]').val() == 1;
        if ( enabled ) {
            $('input[name=peep_pc_follow]').closest('.item').removeClass('disabled');
            $('input[name=peep_pc_secret]').closest('.item').removeClass('disabled');
        } else {
            $('input[name=peep_pc_follow]').closest('.item').addClass('disabled');
            $('input[name=peep_pc_secret]').closest('.item').addClass('disabled');
        }
    });
    $('input[name=peep_s_posts]').trigger('change');
    // -----------------------------------------------------------------

    // -------------------- Generic Filters ----------------------------
    $('select[name=search_engine]').on('change', function() {
        $('a.asp_be_rel_subtab').addClass('tab_disabled');
        if ( $(this).val() == 'index' ) {
            $('a.asp_be_rel_subtab.asp_be_rel_index').removeClass('tab_disabled');
            $('#genericFilterErr').removeClass('hiddend');
        } else {
            $('a.asp_be_rel_subtab.asp_be_rel_regular').removeClass('tab_disabled');
            $('#genericFilterErr').addClass('hiddend');
        }
    });
    $('select[name=search_engine]').trigger('change');

    $('input[name=date_filter_required]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $('input[name=date_filter_invalid_input_text]').closest('div').addClass('disabled');
        } else {
            $('input[name=date_filter_invalid_input_text]').closest('div').removeClass('disabled');
        }
    }).trigger('change');
    // -----------------------------------------------------------------

    // ----------------------- Date Filters ----------------------------
    $('input[name=date_filter_from]').closest('.wd_DateFilter').find('select.wd_di_state').on('change', function(){
        if ( $(this).val() == 'disabled' ) {
            $('input[name=date_filter_from_t]').closest('.item').addClass('disabled');
            $('input[name=date_filter_from_format]').closest('.item').addClass('disabled');
        } else {
            $('input[name=date_filter_from_t]').closest('.item').removeClass('disabled');
            $('input[name=date_filter_from_format]').closest('.item').removeClass('disabled');
        }
    }).trigger('change');
    $('input[name=date_filter_to]').closest('.wd_DateFilter').find('select.wd_di_state').on('change', function(){
        if ( $(this).val() == 'disabled' ) {
            $('input[name=date_filter_to_t]').closest('.item').addClass('disabled');
            $('input[name=date_filter_to_format]').closest('.item').addClass('disabled');
        } else {
            $('input[name=date_filter_to_t]').closest('.item').removeClass('disabled');
            $('input[name=date_filter_to_format]').closest('.item').removeClass('disabled');
        }
    }).trigger('change');
    $('input[name=showsearchintaxonomies]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $(this).closest('div[tabid]').find('>.item').addClass('disabled');
            $(this).closest('.item').removeClass('disabled');
        } else {
            $(this).closest('div[tabid]').find('>.item').removeClass('disabled');
        }
    }).trigger('change');
    // -----------------------------------------------------------------

    // -----------------------------------------------------------------
    $('select[name="term_logic"]').on('change', function() {
        if ( $(this).val() == 'andex' )
            $('#term_logic_MSG').removeClass("hiddend");
        else
            $('#term_logic_MSG').addClass("hiddend");
    });
    $('select[name="term_logic"]').trigger('change');


    // ------------------------- Custom Search Button ------------------
    $('input[name=fe_search_button]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $('#fe_sb_functionality').addClass('disabled');
            $('#fe_search_button').addClass('disabled');
        } else {
            $('#fe_sb_functionality').removeClass('disabled');
            $('#fe_search_button').removeClass('disabled');
        }
    });
    $('input[name=fe_search_button]').trigger('change');

    $('select[name=fe_sb_action]').on('change', function(){

        if ( $(this).val() == 'custom_url' ) {
            $('input[name=fe_sb_redirect_url]').closest('.item').removeClass('hiddend');
        } else {
            $('input[name=fe_sb_redirect_url]').closest('.item').addClass('hiddend');
        }
        if ( $(this).val() == 'elementor_page' ) {
            $('input[name=fe_sb_redirect_elementor]').closest('.item').removeClass('hiddend');
        } else {
            $('input[name=fe_sb_redirect_elementor]').closest('.item').addClass('hiddend');
        }


        var $loc = $(this).closest('.item').find('select[name=fe_sb_action_location]').parent();
        if ( $(this).val() == 'ajax_search' ) {
            $loc.addClass('hiddend');
        } else{
            $loc.removeClass('hiddend');
        }
    });
    $('select[name=fe_sb_action]').trigger('change');

    $('#fe_search_button *[isparam=1]').on('change keyup', function(){
        var p = $('#fe_search_button');
        var sb = $('#fe_sb_preview button');
        var css = $('#fe_sb_css');

        sb.val(p.find('input[name=fe_sb_text]').val()).text(p.find('input[name=fe_sb_text]').val());
        $('#fe_sb_preview').css({
           'text-align': p.find('select[name=fe_sb_align]').val()
        });
        sb.css({
            'background': p.find('input[name=fe_sb_bg]').val(),
            'padding': p.find('input[name=fe_sb_padding]').val(),
            'margin': p.find('input[name=fe_sb_margin]').val()
        });
        var ccss =  p.find('input[name=fe_sb_border]').val() + p.find('input[name=fe_sb_boxshadow]').val();
        ccss += p.find('input[name=fe_sb_font]').val();
        css.text('#fe_sb_preview button.asp_search_btn.asp_s_btn {' + ccss + '}');
    });
    $('select[name=fe_sb_align]').trigger('change');

    $('#fe_sb_trigger, #fe_sb_preview').on('click', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        $('#fe_sb_popup').removeClass("hiddend");
        $('#fe_sb_popup').css({
            "top": $('#fe_sb_trigger').position().top - 50
        });

        if ( $('#fe_sb_popup').find('>div').length == 0 ) {
            var themes = JSON.parse( $('#fe_sb_themes').text() );
            $.each(themes, function(key, theme){
                if ( key == 'default')
                    return true; // Continue
                else
                    theme = $.extend(themes.default, theme);
                var n = $('<div>');
                var css = '';
                var skip = ['fe_sb_bg', 'fe_sb_margin', 'fe_sb_padding'];
                $.each(theme, function(name, t){
                    if ( skip.indexOf(name) > -1 )
                        return true;
                    css += t;
                });
                n.data('theme', key);
                css = '#fe_sb_popup button.asp_search_btn.asp_s_btn.asp_s_btn_'+key+'{'+css+'}';
                n.html('<button class="asp_search_btn asp_s_btn asp_s_btn_'+key+'">Search!</button><style>' + css + '</style>');
                n.find('button').css({
                    'padding': theme.fe_sb_padding,
                    'margin': theme.fe_sb_margin,
                    'background': theme.fe_sb_bg
                }).data('theme', key);
                n.appendTo('#fe_sb_popup');
            });
            $('#fe_sb_popup button').on('click', function(e){
                e.preventDefault();
                e.stopImmediatePropagation();
                $('input[name=fe_sb_theme]').val( $(this).data('theme') )
                    .parent().find('>.triggerer').trigger('click');
            });
        }
    });
    $("body").on("click", function(){
        $('#fe_sb_popup').addClass("hiddend");
    });
    $('#fe_sb_popup').bind("click touchend", function (e) {
        e.stopImmediatePropagation();
    });


    $('input[name=fe_sb_theme]').parent().find('>.triggerer').on('click', function(){
        var tname = $(this).parent().find('input[name=fe_sb_theme]').val();
        var themes = JSON.parse( $('#fe_sb_themes').text() );
        var parent = $('#fe_search_button');
        if ( typeof themes[tname] != 'undefined' ) {
            $.each(themes.default, function(key, value){
                var param = $('input[name="' + key + '"]', parent);
                if (param.length == 0)
                    param = $('select[name="' + key + '"]', parent);
                if (param.length == 0)
                    param = $('textarea[name="' + key + '"]', parent);
                if ( typeof themes[tname][key] != 'undefined' )
                    param.val(themes[tname][key]);
                else
                    param.val(value);
                param.parent().find('>.triggerer').trigger('click');
            });
        }
    });
    // -----------------------------------------------------------------

    // ------------------------- Custom Reset Button -------------------
    $('input[name=fe_reset_button]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $('#fe_reset_button').addClass('disabled');
            $('#fe_rb_functionality').addClass('disabled');
        } else {
            $('#fe_reset_button').removeClass('disabled');
            $('#fe_rb_functionality').removeClass('disabled');
        }
    });
    $('input[name=fe_reset_button]').trigger('change');


    $('#fe_reset_button *[isparam=1]').on('change keyup', function(){
        var p = $('#fe_reset_button');
        var sb = $('#fe_rb_preview button');
        var css = $('#fe_rb_css');

        sb.val(p.find('input[name=fe_rb_text]').val()).text(p.find('input[name=fe_rb_text]').val());
        $('#fe_rb_preview').css({
           'text-align': p.find('select[name=fe_rb_align]').val()
        });
        sb.css({
            'background': p.find('input[name=fe_rb_bg]').val(),
            'padding': p.find('input[name=fe_rb_padding]').val(),
            'margin': p.find('input[name=fe_rb_margin]').val()
        });
        var ccss =  p.find('input[name=fe_rb_border]').val() + p.find('input[name=fe_rb_boxshadow]').val();
        ccss += p.find('input[name=fe_rb_font]').val();
        css.text('#fe_rb_preview button.asp_reset_btn.asp_r_btn {' + ccss + '}');
    });
    $('select[name=fe_rb_align]').trigger('change');

    $('#fe_rb_trigger, #fe_rb_preview').on('click', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        $('#fe_rb_popup').removeClass("hiddend");
        $('#fe_rb_popup').css({
            "top": $('#fe_rb_trigger').position().top - 50
        });

        if ( $('#fe_rb_popup').find('>div').length == 0 ) {
            var themes = JSON.parse( $('#fe_rb_themes').text() );
            $.each(themes, function(key, theme){
                if ( key == 'default')
                    return true; // Continue
                else
                    theme = $.extend(themes.default, theme);
                var n = $('<div>');
                var css = '';
                var skip = ['fe_sb_bg', 'fe_sb_margin', 'fe_sb_padding'];
                $.each(theme, function(name, t){
                    if ( skip.indexOf(name) > -1 )
                        return true;
                    css += t;
                });
                n.data('theme', key);
                css = '#fe_rb_popup button.asp_reset_btn.asp_r_btn.asp_r_btn_'+key+'{'+css+'}';
                n.html('<button class="asp_reset_btn asp_r_btn asp_r_btn_'+key+'">Reset!</button><style>' + css + '</style>');
                n.find('button').css({
                    'padding': theme.fe_sb_padding,
                    'margin': theme.fe_sb_margin,
                    'background': theme.fe_sb_bg
                }).data('theme', key);
                n.appendTo('#fe_rb_popup');
            });
            $('#fe_rb_popup button').on('click', function(e){
                e.preventDefault();
                e.stopImmediatePropagation();
                $('input[name=fe_rb_theme]').val( $(this).data('theme') )
                    .parent().find('>.triggerer').trigger('click');
            });
        }
    });
    $("body").on("click", function(){
        $('#fe_rb_popup').addClass("hiddend");
    });
    $('#fe_rb_popup').bind("click touchend", function (e) {
        e.stopImmediatePropagation();
    });


    $('input[name=fe_rb_theme]').parent().find('>.triggerer').on('click', function(){
        var tname = $(this).parent().find('input[name=fe_rb_theme]').val();
        var themes = JSON.parse( $('#fe_rb_themes').text() );
        var parent = $('#fe_reset_button');
        if ( typeof themes[tname] != 'undefined' ) {
            $.each(themes.default, function(key, value){
                var ikey = key.replace('sb_', 'rb_');
                var param = $('input[name="' + ikey + '"]', parent);
                if (param.length == 0)
                    param = $('select[name="' + ikey + '"]', parent);
                if (param.length == 0)
                    param = $('textarea[name="' + ikey + '"]', parent);
                if ( typeof themes[tname][key] != 'undefined' )
                    param.val(themes[tname][key]);
                else
                    param.val(value);
                param.parent().find('>.triggerer').trigger('click');
            });
        }
    });
    // -----------------------------------------------------------------

    // ------------------------- Tags stuff ----------------------------
    $('input[name="display_all_tags_option"]').on('change', function(){
        if ( $(this).val() == 1 )
            $('input[name="all_tags_opt_text"]').removeAttr("disabled");
        else
            $('input[name="all_tags_opt_text"]').attr('disabled', 'disabled');
    });
    $('input[name="display_all_tags_option"]').trigger('change');

    $('input[name="display_all_tags_check_opt"]').on('change', function(){
        if ( $(this).val() == 1 )
            $('input[name="all_tags_check_opt_text"], select[name="all_tags_check_opt_state"]').removeAttr("disabled");
        else
            $('input[name="all_tags_check_opt_text"], select[name="all_tags_check_opt_state"]').attr('disabled', 'disabled');
    });
    $('input[name="display_all_tags_check_opt"]').trigger('change');

    $('input[name="frontend_tags_required"]').on('change', function(){
        if ( $(this).val() == 1 )
            $('input[name="frontend_tags_invalid_input_text"]').closest('div').removeClass("disabled");
        else
            $('input[name="frontend_tags_invalid_input_text"]').closest('div').addClass('disabled');
    });
    $('input[name="frontend_tags_required"]').trigger('change');

    $("select.wd_tagDisplayMode", $('input[name="show_frontend_tags"]').parent()).on('change', function(){
        $(this).closest('fieldset').find('.item[class*=wd_tag_mode_]').addClass('hiddend');
        $(this).closest('fieldset').find('.item.wd_tag_mode_' + $(this).val()).removeClass('hiddend');
    }).trigger('change');

    $('select.wd_tagDisplayMode').on('change', function(){
        if ( $(this).val() !='multisearch' )
            $('input[name=frontend_tags_placeholder]').closest('.item').addClass('disabled');
        else
            $('input[name=frontend_tags_placeholder]').closest('.item').removeClass('disabled');
        if ( $(this).val() !='checkboxes' )
            $('select[name=frontend_tags_logic]').parent().parent().addClass('disabled');
        else
            $('select[name=frontend_tags_logic]').parent().parent().removeClass('disabled');
    });
    $('select.wd_tagDisplayMode').trigger('change');
    // -----------------------------------------------------------------

    $("select[name='frontend_search_settings_position']").on('change', function(){
        if ( $(this).val() == 'hover' ) {
            $("select[name='fss_hover_columns']").parent().removeClass("hiddend");
            $("select[name='fss_block_columns']").parent().addClass("hiddend");

            $("input[name='fss_hide_on_results']").closest('.item').removeClass('disabled');
        } else {
            $("select[name='fss_hover_columns']").parent().addClass("hiddend");
            $("select[name='fss_block_columns']").parent().removeClass("hiddend");

            $("input[name='fss_hide_on_results']").closest('.item').addClass('disabled');
        }
    });
    $("select[name='frontend_search_settings_position']").trigger('change');

    $('input[name="exclude_dates_on"] + .wpdreamsYesNoInner').on('click', function(){
        if ($(this).prev().val() == 0)
            $('input[name="exclude_dates"]').parent().addClass('disabled');
        else
            $('input[name="exclude_dates"]').parent().removeClass('disabled');
    });
    if ( $('input[name="exclude_dates_on"]').val() == 0 )
        $('input[name="exclude_dates"]').parent().addClass('disabled');
    else
        $('input[name="exclude_dates"]').parent().removeClass('disabled');

    $("select[name='auto_populate']").on('change', function(){
        if ( $(this).val() == 'phrase' )
            $("input[name='auto_populate_phrase']").parent().css("visibility", "");
        else
            $("input[name='auto_populate_phrase']").parent().css("visibility", "hidden");
    });
    $("select[name='auto_populate']").trigger('change');

    $('input[name="use_post_type_order"]').on('change', function(){
        if ($(this).val() == 0)
            $('input[name="post_type_order"]').parent().parent().addClass('disabled');
        else
            $('input[name="post_type_order"]').parent().parent().removeClass('disabled');
    });
    $('input[name="use_post_type_order"]').trigger('change');

    // ---------------------- Load more and highlighter ------------------------
    function showHideInfinite() {
        if (
            $("input[name='showmoreresults']").val() == 1 &&
            $("select[name='more_results_action']").val() == 'ajax'
        ) {
            $('input[name=more_results_infinite]').closest('.item').removeClass("disabled");
        } else {
            $('input[name=more_results_infinite]').closest('.item').addClass("disabled");
        }
    }
    $("input[name='showmoreresults']").on('change', function(){
        if ( $(this).val() == 1 ) {
            $("select[name='more_results_action']").closest('div').removeClass("disabled");
            $("input[name='showmoreresultstext']").closest('div').removeClass("hiddend");
            $("select[name='more_results_action']").trigger('change');
        } else {
            $("select[name='more_results_action']").closest('div').addClass("disabled");
            $("input[name='more_redirect_url']").closest('.item').addClass("hiddend");
            $("select[name='more_redirect_location']").closest('div').addClass("hiddend");
            $("input[name='showmoreresultstext']").closest('div').addClass("hiddend");
        }
        // Auto populate results count
        if ( $(this).val() == 1 && $("select[name='more_results_action']").val() == 'ajax' ) {
            $('input[name=auto_populate_count]').closest('div').addClass('disabled');
            $('.autop-count-err').removeClass('hiddend');
        } else {
            $('input[name=auto_populate_count]').closest('div').removeClass('disabled');
            $('.autop-count-err').addClass('hiddend');
        }
        showHideInfinite();
    });
    $("input[name='showmoreresults']").trigger('change');

    $("select[name='more_results_action']").on('change', function(){
        if ( $(this).val() == 'redirect' )
            $("input[name='more_redirect_url']").closest('.item').removeClass("hiddend");
        else
            $("input[name='more_redirect_url']").closest('.item').addClass("hiddend");
        if ( $(this).val() != 'ajax' ) {
            $("select[name='more_redirect_location']").closest('div').removeClass("hiddend");
        } else {
            $("select[name='more_redirect_location']").closest('div').addClass("hiddend");
        }
        if ( $(this).val() == 'elementor_page' ) {
            $("input[name='more_redirect_elementor']").closest('.item').removeClass("hiddend");
        } else {
            $("input[name='more_redirect_elementor']").closest('.item').addClass("hiddend");
        }
        // Auto populate results count
        if ( $("input[name='showmoreresults']").val() == 1 && $(this).val() == 'ajax' ) {
            $('input[name=auto_populate_count]').closest('div').addClass('disabled');
        } else {
            $('input[name=auto_populate_count]').closest('div').removeClass('disabled');
        }
        showHideInfinite();
    });
    $("select[name='more_results_action']").trigger('change');

    $('input[name=highlight]').on('change', function(){
        if ( $(this).val() == 1 ) {
            $("input[name='highlightwholewords']").closest('.item').removeClass("disabled");
            $("input[name='highlightcolor']").closest('.item').removeClass("disabled");
            $("input[name='highlightbgcolor']").closest('.item').removeClass("disabled");
        } else {
            $("input[name='highlightwholewords']").closest('.item').addClass("disabled");
            $("input[name='highlightcolor']").closest('.item').addClass("disabled");
            $("input[name='highlightbgcolor']").closest('.item').addClass("disabled");
        }
    }).trigger('change');

    // ---------------------- Layout options ------------------------
    $("select[name='resultstype']").on('change', function(){
        var val = $(this).val();
        $('.item:not(.item-rlayout)', $('.item-rlayout').parent()).addClass('hiddend');
        $('.item:not(.item-rlayout)', $('.item-rlayout-' + val).parent()).removeClass('hiddend');
        $('.item-rlayout').removeClass('hiddend');
        $('.item-rlayout-' + val).addClass('hiddend');
        $('.item-rlayout p span').html(val);

        $('.subtheme-tabs a.subtheme.subtheme-rlayout').addClass('disabled').filter('.subtheme-' + val).removeClass('disabled');
    });
    $("select[name='resultstype']").trigger('change');
    $(".item-rlayout a, .item-rinfobox a").on("click", function(){
        var tabid = $(this).attr("tabid");
        $('.tabs a[tabid=' + Math.floor( tabid / 100 ) + ']').trigger('click');
        $('.tabs a[tabid=' + tabid + ']').trigger('click');
        if ( typeof $(this).data('asp-os-highlight') !== 'undefined' ) {
            $('.asp-os-highlighted').removeClass("asp-os-highlighted");
            $("*[name='"+$(this).data('asp-os-highlight')+"']").closest('.item').addClass("asp-os-highlighted");
        }
    });

    $('input[name=results_top_box]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $('.item *[name=results_top_box_text]').closest('.item').addClass('disabled');
        } else {
            $('.item *[name=results_top_box_text]').closest('.item').removeClass('disabled');
        }
    }).trigger('change');

    $('input[name=results_top_box]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $('.subtheme-tabs a.subtheme-rinfobox').addClass('disabled');
            $('fieldset.rinfobox .item').addClass('hiddend');
            $('fieldset.rinfobox .item.item-rinfobox').removeClass('hiddend');
        } else {
            $('.subtheme-tabs a.subtheme-rinfobox').removeClass('disabled');
            $('fieldset.rinfobox .item').removeClass('hiddend');
            $('fieldset.rinfobox .item.item-rinfobox').addClass('hiddend');
        }
    }).trigger('change');

    $('input[name=showauthor]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $("select[name='author_field']").parent().addClass('disabled');
        } else {
            $("select[name='author_field']").parent().removeClass('disabled');
        }
    }).trigger('change');

    $('input[name=showdate]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $("input[name='custom_date']").parent().addClass('disabled');
            $("input[name='custom_date_format']").parent().addClass('disabled');
        } else {
            $("input[name='custom_date']").parent().removeClass('disabled');
            $("input[name='custom_date_format']").parent().removeClass('disabled');
        }
    }).trigger('change');

    $('input[name=showdescription]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $("input[name='descriptionlength']").parent().addClass('disabled');
            $("input[name='description_context']").parent().addClass('disabled');
            $("input[name='description_context_depth']").parent().addClass('disabled');
        } else {
            $("input[name='descriptionlength']").parent().removeClass('disabled');
            $("input[name='description_context']").parent().removeClass('disabled');
            $("input[name='description_context_depth']").parent().removeClass('disabled');
        }
    }).trigger('change');

    $('input[name=tax_res_showdescription]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $("input[name='tax_res_descriptionlength']").parent().addClass('disabled');
        } else {
            $("input[name='tax_res_descriptionlength']").parent().removeClass('disabled');
        }
    }).trigger('change');

    $('input[name=user_res_showdescription]').on('change', function(){
        if ( $(this).val() == 0 ) {
            $("input[name='user_res_descriptionlength']").parent().addClass('disabled');
        } else {
            $("input[name='user_res_descriptionlength']").parent().removeClass('disabled');
        }
    }).trigger('change');

    $('input[name=scroll_to_results]').on('change', function(){
        if ( $(this).val() == 1 ) {
            $("input[name='scroll_to_results_offset']").closest('.wpdreamsTextSmall').removeClass('disabled');
        } else {
            $("input[name='scroll_to_results_offset']").closest('.wpdreamsTextSmall').addClass('disabled');
        }
    }).trigger('change');
    // ----------------------------------------------------------------------------------


    // ------------------------- Autocomplte & Suggestions ------------------------------
    // Show-Hide the API input fields
    var $_autoc_s = $("input[name='autocomplete_source']");
    $("ul.connectedSortable", $_autoc_s.parent()).on("sortupdate", function(){
        var v = $_autoc_s.val();
        if ( v.indexOf("google_places") > -1 ) {
            $("input[name='autoc_google_places_api']").parent().parent().removeClass("hiddend");
        } else {
            $("input[name='autoc_google_places_api']").parent().parent().addClass("hiddend");
        }

        if ( v == '' || v == 'google_places' || v == 'google' || v == 'google_places|google' || v == 'google|google_places' ) {
            $('select[name=autocomplete_instant]').val('auto').trigger('change');
            $('select[name=autocomplete_instant]').attr('disabled', 'disabled');
        } else {
            var sv = $('select[name=autocomplete_instant]').val();
            // sv is an 'object' but it is null in most cases, leave this check like this
            if ( typeof sv == 'undefined' || sv == null || sv == 'auto' ) {
                $('select[name=autocomplete_instant]').val('disabled').trigger('change');
            }
            $('select[name=autocomplete_instant]').removeAttr('disabled');
        }
    });
    $("ul.connectedSortable", $_autoc_s.parent()).trigger("sortupdate");

    $("ul.connectedSortable", $("input[name='keyword_suggestion_source']").parent()).on("sortupdate", function(){
        if ( $("input[name='keyword_suggestion_source']").val().indexOf("google_places") > -1 ) {
            $("input[name='kws_google_places_api']").parent().parent().removeClass("hiddend");
        } else {
            $("input[name='kws_google_places_api']").parent().parent().addClass("hiddend");
        }
    });
    $("ul.connectedSortable", $("input[name='keyword_suggestion_source']").parent()).trigger("sortupdate");
    // ----------------------------------------------------------------------------------


    // ---------------------------- THEME OPTIONS PANEL ---------------------------------
    $('input[name="v_res_show_scrollbar"]').on('change', function(){
        if ($(this).val() == 0) {
            $('.asp_v_res_scroll_dependent').addClass('disabled');
        } else {
            $('.asp_v_res_scroll_dependent').removeClass('disabled');
        }
    }).trigger('change');

    $('input[name="i_pagination"]').on('change', function(){
        if ($(this).val() == 0) {
            $('input[name="i_rows"]').closest('.wpdreamsTextSmall').addClass('disabled');
            $('.item-iso-nav').addClass('disabled');
        } else {
            $('input[name="i_rows"]').closest('.wpdreamsTextSmall').removeClass('disabled');
            $('.item-iso-nav').removeClass('disabled');
        }
    });
    $('input[name="i_pagination"]').trigger('change');

    $('input[name="settingsimage_custom"]').on('input change', function(){
        if ($(this).val().trim() == '') {
            $('input[name="settingsimage"]').closest('.item').removeClass('disabled');
        } else {
            $('input[name="settingsimage"]').closest('.item').addClass('disabled');
        }
    });
    $('input[name="settingsimage_custom"]').trigger('input');

    $('input[name="magnifierimage_custom"]').on('input change', function(){
        if ($(this).val().trim() == '') {
            $('input[name="magnifierimage"]').closest('.item').removeClass('disabled');
        } else {
            $('input[name="magnifierimage"]').closest('.item').addClass('disabled');
        }
    });
    $('input[name="magnifierimage_custom"]').trigger('input');

    $('input[name="loadingimage_custom"]').on('input change', function(){
        if ($(this).val().trim() == '') {
            $('input[name="loader_image"]').closest('.item').removeClass('disabled');
            $('input[name="loadingimage_color"]').closest('.item').removeClass('disabled');
        } else {
            $('input[name="loader_image"]').closest('.item').addClass('disabled');
            $('input[name="loadingimage_color"]').closest('.item').addClass('disabled');
        }
    });
    $('input[name="loadingimage_custom"]').trigger('input');

    $('input[name="show_close_icon"]').on('change', function(){
        if ($(this).val() == 1) {
            $('input[name="close_icon_background"]').closest('.item').removeClass('disabled');
        } else {
            $('input[name="close_icon_background"]').closest('.item').addClass('disabled');
        }
    });
    $('input[name="show_close_icon"]').trigger('change');
    // ---------------------------- THEME OPTIONS PANEL ---------------------------------

    // -------------------------- ADVANCED OPTIONS PANEL --------------------------------
    $("select[name='group_by']").on('change', function(){
        if ( $(this).val() == 'none' ) {
            $("#wpdreams .item.wd_groupby_op").addClass('hiddend');
            $("#wpdreams .item.wd_groupby").addClass('hiddend');
        } else {
            $("#wpdreams .item.wd_groupby_op").removeClass('hiddend');
            $("#wpdreams .item.wd_groupby").addClass('hiddend');
            $("#wpdreams .item.wd_groupby_" + $(this).val()).removeClass('hiddend');
        }
    });
    $("select[name='group_by']").trigger('change');

    $("select[name='group_result_no_group']").on('change', function(){
        if ( $(this).val() == 'remove' ) {
            $("input[name='group_other_results_head']").parent().parent().css("display", "none");
        } else {
            $("input[name='group_other_results_head']").parent().parent().css("display", "");
        }
    });
    $("select[name='group_result_no_group']").trigger('change');

    // Primary and Secondary fields for custom fields
    $.each(['primary_titlefield', 'secondary_titlefield', 'primary_descriptionfield', 'secondary_descriptionfield'],
    function(i, v){
        $("select[name='"+v+"']").on('change', function(){
            if ( $(this).val() != 'c__f' ) {
                $("input[name='"+v+"_cf']").parent().css("display", "none");
            } else {
                $("input[name='"+v+"_cf']").parent().css("display", "");
            }
        });
        $("select[name='"+v+"']").trigger('change');
    });

    // Empty group position
    $('input[name="group_show_empty"]').on('change', function(){
        if ($(this).val() == 0)
            $('select[name="group_show_empty_position"]').closest('.wpdreamsCustomSelect').addClass('disabled');
        else
            $('select[name="group_show_empty_position"]').closest('.wpdreamsCustomSelect').removeClass('disabled');
    });
    $('input[name="group_show_empty"]').trigger('change');

    // User search custom URL
    $('select[name="user_search_url_source"]').on('change', function(){
        if ($(this).val() == 'custom')
            $('input[name="user_search_custom_url"]').closest('.item').removeClass('disabled');
        else
            $('input[name="user_search_custom_url"]').closest('.item').addClass('disabled');
    });
    $('select[name="user_search_url_source"]').trigger('change');
    // -------------------------- ADVANCED OPTIONS PANEL --------------------------------

    // -------------------------------- MODAL MESSAGES ----------------------------------
    var _tmp = {
        'type'   : 'warning', // warning, info
        'header' : 'Load more results',
        'headerIcons': true,
        'content': __('Please note, that "Load more results via ajax" feature is automatically disabled with the current search configuration.', 'ajax-search-pro') +
        '<br><br>' + __('You are seeing this notice, because:', 'ajax-search-pro') + ' ' +
        '<br> - ' + __('The more results action is set to', 'ajax-search-pro') + ' <a href="#405" data-tabid="405" data-optname="more_results_action">' + __('Load more ajax results', 'ajax-search-pro') + '</a>' +
        '<br> - ' + __('..and the {{loadmore_option}}', 'ajax-search-pro'),
        'buttons': {
            'okay': {
                'text': __('Okay', 'ajax-search-pro'),
                'type': 'okay',
                'click': function(e, button){}
            }
        }
    };
    var modalItems = [
        {
            'args': {
                'type'   : 'warning', // warning, info
                'header' : __('Are you sure?', 'ajax-search-pro'),
                'headerIcons': true,
                'content': __('Using exact matches and the index table engine at the same time will automatically ignore the Index table engine, are you sure?', 'ajax-search-pro'),
                'buttons': {
                    'cancel': {
                        'text': __('No, please revert this option', 'ajax-search-pro'),
                        'type': 'cancel',
                        'click': function(e, button){}
                    },
                    'okay': {
                        'text': __('Yes, I am sure', 'ajax-search-pro'),
                        'type': 'okay',
                        'click': function(e, button){}
                    }
                }
            }, // Modal args
            'items': [
                ['search_engine', 'index'], // Item name => value
                ['exactonly', '1']
            ]
        },
        {
            'args': {
                'type'   : 'warning', // warning, info
                'header' : __('Notice', 'ajax-search-pro'),
                'headerIcons': true,
                'content': __('Using <strong>Compact box layout</strong> mode and the <strong>Live Results Page Loader</strong> at the same time is not possible, this option will be reverted.', 'ajax-search-pro') +
                '<br><br>' +  __('Compact box layout option: <strong>Layout Options -> Compact box Layout</strong>', 'ajax-search-pro') +
                '<br>' + __('The Live Results Page Loader option: <strong>General Options -> Logic & Behavior</strong>', 'ajax-search-pro'),
                'buttons': {
                    'cancel': {
                        'text': __('Okay', 'ajax-search-pro'),
                        'type': 'cancel',
                        'click': function(e, button){}
                    }
                }
            }, // Modal args
            'items': [
                ['box_compact_layout', '1'],
                ['res_live_search', '1']
            ]
        },
        {
            'args': {
                'type'   : 'info', // warning, info
                'header' : __('GDPR & Cookie Notice', 'ajax-search-pro'),
                'headerIcons': true,
                'content': __('When using this option cookies might be set during the search redirection, to store the search filter status and the phrase for pagination.', 'ajax-search-pro') +
                    __(' These cookies are <strong>functional</strong> only, they are not used for marketing nor any other purposes.', 'ajax-search-pro') +
                '<br><br>' + __('The cookie names are: <i>asp_data, asp_id, asp_phrase</i>', 'ajax-search-pro') +
                '<br><br>' + __('For more information you can read the <a target="_blank" href="https://documentation.ajaxsearchpro.com/gdpr-and-cookie-policy">GDPR and Cookie policy documentation</a>.', 'ajax-search-pro'),
                'buttons': {
                    'okay': {
                        'text': __('Okay', 'ajax-search-pro'),
                        'type': 'okay',
                        'click': function(e, button){}
                    }
                }
            }, // Modal args
            'items': [
                ['override_method', 'post']
            ]
        },
        {
            'args': JSON.parse(JSON.stringify(_tmp)), // Modal args
            'items': [
                ['showmoreresults', '1'],
                ['more_results_action', 'ajax'],
                ['resultstype', 'isotopic'],
                ['i_ifnoimage', 'removeres']
            ]
        },
        {
            'args': JSON.parse(JSON.stringify(_tmp)), // Modal args
            'items': [
                ['showmoreresults', '1'],
                ['more_results_action', 'ajax'],
                ['resultstype', 'polaroid']
            ]
        }
    ];
    function modal_check(items) {
        var ret = false;
        // If at least one of the values does not match, it is a pass, return true
        $.each(items, function(k, item){
            if ( typeof item[2] != 'undefined' ) {
                if ( $('*[name='+item[0]+']').val() == item[1] ) {
                    ret = true;
                    return false;
                }
            } else if ( $('*[name='+item[0]+']').val() != item[1] ) {
                ret = true;
                return false;
            }

        });
        return ret;
    }
    $.each(modalItems, function(k, item){
       $.each(item.items, function(kk, _item){
           $('*[name='+_item[0]+']').data('oldval', $('*[name='+_item[0]+']').val());
           $('*[name='+_item[0]+']').on('change', function() {
                var _this = this;
                if ( !modal_check(item.items) ) {
                    if ( typeof item.args.buttons != 'undefined' ) {
                        if ( typeof item.args.buttons.cancel != 'undefined' )
                            item.args.buttons.cancel.click = function ( e, button ) {
                                if ( $(_this).data('oldval') !== undefined ) {
                                    $(_this).val($(_this).data('oldval'));
                                    $('.triggerer', $(_this).closest('div')).trigger('click');
                                }
                                $(_this).data('oldval', $(_this).val());
                            };
                        if ( typeof item.args.buttons.okay != 'undefined' )
                            item.args.buttons.okay.click = function ( e, button ) {
                                $(_this).data('oldval', $(_this).val());
                            };
                    }
                    if ( typeof item.args.content != 'undefined' ) {
                        if ( item.args.content.indexOf('{{loadmore_option}}') > 0 ) {
                            var str = '';
                            if ( $('select[name=group_by]').val() != 'none' ) {
                                str = 'Grouping <a href="#702" data-tabid="702" data-optname="group_by">is enabled</a>';
                            } else if ( $('select[name=resultstype]').val() == 'polaroid' ) {
                                str = 'The Polaroid layout <a href="#402" data-tabid="402" data-optname="resultstype">is selected</a>';
                            } else if ( $('select[name=resultstype]').val() == 'isotopic' && $('select[name=i_ifnoimage]').val() == 'removeres' ) {
                                str = 'The Isotopic layout is selected, and <a href="#605" data-tabid="605" data-optname="i_ifnoimage">the results are set to be removed, when no images are found</a>';
                            }
                            item.args.content = item.args.content.replace('{{loadmore_option}}', str);
                            $('#wpd_modal').on('click', 'a[data-optname]', function(e){
                                e.preventDefault();
                                var hash = parseInt( $(this).data('tabid') );
                                $('.tabs a[tabid=' + Math.floor( hash / 100 ) + ']').trigger('click');
                                $('.tabs a[tabid=' + hash + ']').trigger('click');
                                $('select[name=' + $(this).data('optname') + ']').closest('.item').addClass('asp-os-highlighted');
                            });
                        }
                    }
                    WPD_Modal.show(item.args);
                } else {
                    $(_this).data('oldval', $(_this).val());
                }
           });
       });
    });
    // -------------------------------- MODAL MESSAGES END ------------------------------

    // Remove the # from the hash, as different browsers may or may not include it
    var hash = location.hash.replace('#','');

    if(hash != ''){
        hash = parseInt(hash);
        $('.tabs a[tabid=' + Math.floor( hash / 100 ) + ']').trigger('click');
        $('.tabs a[tabid=' + hash + ']').trigger('click');
    } else {
        $('.tabs a[tabid=1]').trigger('click');
    }

    $('#wpdreams .settings').on('click', function() {
        $("#asp_preview_window input[name=refresh]").attr('searchid', $(this).attr('searchid'));
    });
    $("select[id^=wpdreamsThemeChooser]").on('change', function() {
        $("#asp_preview_window input[name=refresh]").trigger('click');
    });
    $("#asp_preview_window .refresh").on('click', function(e) {
        e.preventDefault();
        var $this = $(this).parent();
        var id = $('#wpdreams').data('searchid');
        var loading = $('.big-loading', $this);
        var nonce = $('#asp_backend_preview_nonce').val();

        ASP.instances.destroy();

        $('.data', $this).html("");
        $('.data', $this).addClass('hidden');
        loading.removeClass('hidden');
        var data = {
            action: 'ajaxsearchpro_preview',
            asp_backend_preview_nonce: nonce,
            asid: id,
            formdata: $('form[name="asp_data"]').serialize()
        };

        $.post(ajaxurl, data, function (response) {
            loading.addClass('hidden');
            $('.data', $this).html(response);
            $('.data', $this).removeClass('hidden');
            delete window.ASP_INSTANCES;
            ASP.initialize();
        });
    });

    $("#asp_preview_window .maximise").on('click', function(e) {
        e.preventDefault();
        $this = $(this.parentNode);
        if ($(this).html() == "Show") {
            $this.animate({
                bottom: "-2px",
                height: "90%"
            });
            $(this).html('Hide');
            $("#asp_preview_window a.refresh").trigger('click');
        } else {
            $this.animate({
                bottom: "-2px",
                height: "40px"
            });
            $(this).html('Show');
        }
    });

    if (typeof ($.fn.spectrum) != 'undefined')
        $("#bgcolorpicker").spectrum({
            showInput: true,
            showPalette: true,
            showSelectionPalette: true,
            change: function (color) {
                $("#asp_preview_window").css("background", color.toHexString()); // #ff0000
            }
        });

    // Social stuff
    var url = encodeURIComponent('http://bit.ly/buy_asp');
    var fb_share_url = "https://www.facebook.com/share.php?u=";
    var tw_share_url = "https://twitter.com/intent/tweet";

    function winOpen(url) {
        var width = 575, height = 400,
            left = (document.documentElement.clientWidth / 2 - width / 2),
            top = (document.documentElement.clientHeight - height) / 2,
            opts = 'status=1,resizable=yes' +
                ',width=' + width + ',height=' + height +
                ',top=' + top + ',left=' + left,
            win = window.open(url, '', opts);
        win.focus();
        return win;
    }

    $("#asp_tw_share").on("click", function(e){
        var $this = $(this);
        e.preventDefault();
        winOpen(tw_share_url + "?text=" + encodeURIComponent($this.data("text")) + "&url=" + url + "&via=ernest_marcinko");
    });
    $("#asp_fb_share").on("click", function(e){
        e.preventDefault();
        winOpen(fb_share_url + url);
    });
});
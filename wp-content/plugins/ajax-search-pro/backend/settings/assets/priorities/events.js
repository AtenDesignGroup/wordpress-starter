jQuery(function($){
// --------------------------------------- INIT VARIABLES --------------------------------------------------------------
    var $pgEditor = $('#asp_pg_editor').detach();
    var openedRuleGroupDef = {
        priority: 100,
        name: 'Priority Group',
        phrase: '',
        phrase_logic: 'disabled',
        instance: 0,
        logic: 'and',
        rules: []
    };
    var openedRuleGroup = JSON.parse(JSON.stringify(openedRuleGroupDef));   // Reference to the currently opened group
    // Reference to the whole groups array - this is gettings saved
    var groupsObject = JSON.parse(WD_Helpers.Base64.decode($('#priority_groups').val()));

    var initialValue = $('#priority_groups').val();     // Used to check whenever the user leaves the page
    var submit_clicked = false;                         // Used to check whenever the user leaves the page

    listController.init({
        // Node storage
        $container: $('#pg_container'),
        $noPG: $('#pg_no_pg'),
        $pgInput: $('#priority_groups'),
        // Others objects
        groupsObject: groupsObject
    });
    mgController.init({
        $group: {
            editor: $pgEditor,
            name: $('#ruleset_name', $pgEditor),            // Group name input
            priority: $('#pg_priority', $pgEditor),         // Group priority input
            instance: $('#pg_instance', $pgEditor),         // Search instance to affect
            phrase: $('#pg_phrase', $pgEditor),             // Group Phrase
            phraseLogic: $('#pg_phrase_logic', $pgEditor),  // Group Phrase Logic
            ruleLogic: $('#pg_rule_logic', $pgEditor)       // Group Rules Logic
        },

        $rules: $('#pg_rules_container', $pgEditor),
        $ruleEditor: $('#pg_rule_editor', $pgEditor),
        $selectedTaxTerms: $('#pg_selected_tax_terms', $pgEditor),
        $addRuleBTN: $('#pg_add_rule', $pgEditor),
        $fields: {
            name: $('#pg_rule_editor', $pgEditor).find('input[name=rule_name]'),
            field: $('#pg_rule_editor', $pgEditor).find('select[name=rule_field]'),
            termOperator: $('#pg_rule_editor', $pgEditor).find('select[name=term_operator]'),
            cfOperator: $('#pg_rule_editor', $pgEditor).find('select[name=cf_operator]'),
            titleOperator: $('#pg_rule_editor', $pgEditor).find('select[name=title_operator]'),
            termValues: $('#pg_rule_editor', $pgEditor).find('#pg_selected_tax_terms'),
            cfField: $('#pg_rule_editor', $pgEditor).find('input[name=pg_search_cf]'),
            cfVal1: $('#pg_rule_editor', $pgEditor).find('input[name=cf_val1]'),
            cfVal2: $('#pg_rule_editor', $pgEditor).find('input[name=cf_val2]'),
            titleValue: $('#pg_rule_editor', $pgEditor).find('input[name=title_value]')
        },

        groupsObject: groupsObject,
        openedRuleGroup: openedRuleGroup,
        defaultRule: {
            'name'          : 'Rule name',
            'field'         : 'tax',       // 'tax', 'cf', 'title'
            'operator'      : 'in',        // in, not in, like, not like, elike, =, <>, >, <, >=, <=, BETWEEN
            'values'        : []
        }
    });
    listController.printGroups();

    // Modal controller shorthand reference
    var mw = window.WPD_Modal;
    // Set the modal content only once, to prevent event detaching
    // ..use it later with the 'leaveContent: true' param
    mw.options({
        'content': $pgEditor
    });
    mw.layout({
        'max-width': '640px',
        'width': '640px'
    });
// ---------------------------------------------------------------------------------------------------------------------

// -------------------------------------- GROUP LISTING & BUTTONS ------------------------------------------------------
    // Add new group
    $('#pg_add_new').on('click', function(){
        $pgEditor.removeClass('hiddend');
        mw.options({
            'type': 'info',
            'header': msg('msg_npg'),
            'leaveContent': true,
            'buttons': {
                'okay': {
                    'text': msg('msg_sav'),
                    'type': 'okay',
                    'click': function(e, button){
                        mgController.saveOpenRule();
                        mgController.closeRuleEditor();
                        mgController.saveRuleGroup(-1);
                        listController.printGroups();
                        listController.saveGroups();
                    }
                },
                'cancel': {
                    'text': msg('msg_can'),
                    'type': 'cancel',
                    'click': function(e, button){}
                }
            }
        });
        openedRuleGroup = JSON.parse(JSON.stringify(openedRuleGroupDef));
        mgController.loadRuleGroup(openedRuleGroup);
        mw.show();
    });

    // Edit existing group
    $('#pg_container').on('click', '.pg_rg_edit', function(){
        var id = $(this).closest('.pg_rule_group').data('groupid') - 1;
        if ( typeof groupsObject[id] == 'undefined' )
            return false;
        openedRuleGroup = JSON.parse(JSON.stringify(groupsObject[id]));
        $pgEditor.removeClass('hiddend');
        mw.options({
            'type': 'info',
            'header': msg('msg_epg') + ' ' + openedRuleGroup.name,
            'leaveContent': true,
            'buttons': {
                'okay': {
                    'text': msg('msg_sav'),
                    'type': 'okay',
                    'click': function(e, button){
                        mgController.saveOpenRule();
                        mgController.closeRuleEditor();
                        mgController.saveRuleGroup(id);
                        listController.printGroups();
                        $('#priority_groups').val(WD_Helpers.Base64.encode(JSON.stringify(groupsObject)));
                    }
                },
                'cancel': {
                    'text': msg('msg_can'),
                    'type': 'cancel',
                    'click': function(e, button){}
                }
            }
        });
        mgController.loadRuleGroup(openedRuleGroup);
        mw.show();
    });

    // Delete a group
    $('#pg_container').on('click', '.pg_rg_delete', function(){
        var id = $(this).closest('.pg_rule_group').data('groupid') - 1;
        if ( typeof groupsObject[id] == 'undefined' )
            return false;
        var sure = confirm( sprintf(msg('msg_del'), groupsObject[id].name) );
        if ( sure ) {
            listController.deleteGroup(id);
            listController.printGroups();
        }
    });

    // Save all groups
    $('#pg_save').on('click', function(){
        submit_clicked = true;
        $(this).closest('form').submit();
    });

    // Delete all groups
    $('#pg_remove_all').on('click', function(e){
        e.preventDefault();
        if ( confirm( msg('msg_dal') ) ) {
            listController.deleteGroups();
            submit_clicked = true;
            $(this).closest('form').submit();
        }
    });
// ---------------------------------------------------------------------------------------------------------------------

// -------------------------------------- GROUP EDITOR INTERACTIONS ----------------------------------------------------
    // Phrase logic change
    $('#pg_phrase_logic').on('change', function(){
        if ( $(this).val() == 'disabled' ) {
            $('#pg_phrase').attr('disabled', 'disabled');
            $('label[for=pg_phrase]').attr('disabled', 'disabled');
        } else {
            $('#pg_phrase').removeAttr('disabled');
            $('label[for=pg_phrase]').removeAttr('disabled');
        }
    });
    $('#pg_phrase_logic').trigger('change');

    // Chose rule type
    $('#wpd_modal_inner').on('change', 'select[name=rule_field]', function(){
        $('#pg_rule_editor').find('.pg_rule_tax, .pg_rule_cf, .pg_rule_title').addClass('hiddend');
        if ( $(this).val() != 0 ) {
            $('#pg_rule_editor').find('.pg_rule_' + $(this).val()).removeClass('hiddend');
        }
    });

    // Priority input
    $('#wpd_modal_inner').on('input', '#pg_priority', function(e){
        var val = $(this).val();
        if ( val == '' ) {
            $(this).val(0);
        } else {
            val = parseInt($(this).val());
            var fval = Math.min(5000, Math.max(0, val));
            if (val != fval)
                $(this).val(fval);
        }
    });
// ---------------------------------------------------------------------------------------------------------------------

// -------------------------------------- RULE EDITOR BUTTONS ----------------------------------------------------------
    // New rule button
    $('#wpd_modal_inner').on('click', '#pg_add_rule', function(){
        mgController.saveRule(-1);
        mgController.printRules();
        mgController.closeRuleEditor();
        mgController.maxRulesCheck();
        mgController.openRuleEditor();
    });
    // Edit rule icon
    $('#wpd_modal_inner').on('click', '.pg_edit_rule', function(){
        mgController.openRuleEditor($(this).closest('p').data('id'));
    });
    // Delete rule icon
    $('#wpd_modal_inner').on('click', '.pg_delete_rule', function(){
        if ( confirm( msg('msg_dru') ) ) {
            mgController.closeRuleEditor();
            mgController.deleteRule($(this).closest('p').data('id'));
            mgController.printRules();
            mgController.maxRulesCheck();
        }
    });
    // Save rule button
    $('#wpd_modal_inner').on('click', '#pg_editor_save_rule', function(){
        mgController.saveOpenRule();
        mgController.closeRuleEditor();
        mgController.printRules();
        mgController.maxRulesCheck();
    });
    // Delete rule button
    $('#wpd_modal_inner').on('click', '#pg_editor_delete_rule', function(){
        if ( confirm( msg('msg_dru') ) ) {
            var id = $('#pg_rule_editor').data('rule-id');
            mgController.deleteRule(id);
            mgController.printRules();
            mgController.maxRulesCheck();
            mgController.closeRuleEditor();
        }
    });
// ---------------------------------------------------------------------------------------------------------------------

// -------------------------------------- RULE EDITOR OTHER INTERACTIONS -----------------------------------------------
    // Taxonomy search interactions
    $('#asp_pg_editor')
        .on('wd_taxterm_search_end wd_taxterm_open_results', '.wd_taxterm_search', function(e, input, results){
            e.stopPropagation();    // Prevent multiple fire
            $('#asp_pg_editor .wd_taxterm_search_res li').removeClass('wtsr_selected');
            $('#pg_selected_tax_terms li').each(function(i, o){
                var iclass = $(this).attr('class');
                $('#asp_pg_editor .wd_taxterm_search_res li.' + iclass).addClass('wtsr_selected');
            });
        });
    // Taxonomy search adding new elements
    $('#asp_pg_editor .wd_taxterm_search_res').on('click', 'li:not(.wtsr_selected)', function(e){
        if ( $('#pg_selected_tax_terms li').length < 11 ) {
            $(this).clone().appendTo('#pg_selected_tax_terms');
            $('#asp_pg_editor .wd_taxterm_search').trigger('wd_taxterm_open_results');
        } else {
            alert( msg('msg_cru') );
        }
    });
    // Taxonomy search removing elements
    $('#pg_selected_tax_terms').on('click', 'li', function(e){
        $(this).remove();
        $('#asp_pg_editor .wd_taxterm_search').trigger('wd_taxterm_open_results');
    });

    // Change custom field operator type
    $('#wpd_modal_inner').on('change', 'select[name=cf_operator]', function(){
        if ( $(this).val() == 'between' ) {
            $('input[name=cf_val2]').css('display', 'inline');
        } else {
            $('input[name=cf_val2]').css('display', 'none');
        }
    });
// ---------------------------------------------------------------------------------------------------------------------

// -------------------------------------- OTHER ------------------------------------------------------------------------
    // User is about to leave, check for unsaved changes
    $(window).on('beforeunload', function(e){
        if ( !submit_clicked && initialValue != $('#priority_groups').val() ) {
            return msg('msg_uns');
        }
    });
// ---------------------------------------------------------------------------------------------------------------------
// --------------------------------------- ETC -------------------------------------------------------------------------
    function msg(k) {
        return typeof ASP_EVTS[k] != 'undefined' ? ASP_EVTS[k] : '';
    }
    function sprintf(s) {
        var i = 1, r = '';
        while ( s.indexOf('%s') !== false ) {
            r = typeof arguments[i] !== 'undefined' ? arguments[i] : '';
                s = s.replace('%s', r);
          i++;
          if ( i > 40 )
            break;
        }
        return s;
    }
});
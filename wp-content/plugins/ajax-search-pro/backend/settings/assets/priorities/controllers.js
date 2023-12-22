/**
 *  Priority groups list controller
 */
window.$ = jQuery;
var listController = {
    // Node storage
    $container: '',
    $noPG: '',
    $pgInput: '',

    // Others objects
    groupsObject: {},

    init: function (args) {
        // this.$variable = args[variable];
        var $this = this;

        $.each(args, function (k, v) {
            $this[k] = v;
        });
        $this.$groupSample = $this.$container.find('.pg_rule_group').detach();
    },
    printGroups: function () {
        var $this = this;

        $this.$container.html('');
        $.each($this.groupsObject, function (i, o) {
            var n = $this.$groupSample.clone();
            var groupID = i + 1;
            n.data('groupid', groupID);
            $('span.pg_name', n).html(o.name);
            var plural = o.rules.length == 1 ? '' : 's';
            $('span.pg_info', n).html(o.rules.length + ' rule' + plural + '. Priority: ' + o.priority);
            n.appendTo($this.$container);
        });
        if ($this.groupsObject.length == 0) {
            $this.$noPG.removeClass('hiddend');
        } else {
            $this.$noPG.addClass('hiddend');
        }
    },
    saveGroups: function () {
        var $this = this;
        $this.$pgInput.val(WD_Helpers.Base64.encode(JSON.stringify($this.groupsObject)));
    },
    deleteGroup: function (id) {
        var $this = this;
        this.groupsObject.splice(id, 1);
        this.saveGroups();
    },
    deleteGroups: function () {
        var $this = this;
        this.groupsObject = [];
        this.saveGroups();
    }
};

/**
 *  Modal window Group and Rule editor controller
 */
var mgController = {
    $group: {},             // Group node list (except the rule editor)
    $rules: '',             // Rules container
    $ruleEditor: '',        // Rule editor container
    $selectedTaxTerms: '',  // Selected taxonomy terms container
    $fields: '',            // Fields in Rule editor
    $addRuleBTN: '',        // Add Rule button
    $ruleSample: '',

    // Others objects
    groupsObject: {},
    modalOriginalStates: {},
    editorOriginalStates: {},
    openedRuleGroup: {},
    defaultRule: {},

    _taxTermNames: {},  // Temporary storage for taxonomy term names in use

    init: function(args) {
        var $this = this;
        // this.$variable = args[variable];
        $.each(args, function(k, v){
            $this[k] = v;
        });
        $('select, input', $this.$group.editor).each(function(){
            $this.modalOriginalStates[$(this).attr('name')] = $(this).val();
        });
        $this.$ruleEditor.find('select, input').each(function(){
            $this.editorOriginalStates[$(this).attr('name')] = $(this).val();
        });
        $this.$ruleSample = $this.$rules.find('p').detach();
    },
    resetRuleEditor: function() {
        var $this = this;

        $.each($this.editorOriginalStates, function(k, o){
            $('*[name="' + k + '"]', $this.$ruleEditor).val(o);
        });
        $this.$selectedTaxTerms.html('');
    },
    openRuleEditor: function(id) {
        var $this = this;

        $this.resetRuleEditor();
        var rules = $this.openedRuleGroup.rules;
        // Set the latest rule, if the id is not defined
        id = typeof id == 'undefined' ? rules.length - 1 : id;
        if ( id < 0 )
            return false;
        var name = typeof rules[id] != 'undefined' ? rules[id].name : 'New rule';
        if ( id > -1 ) {
            $this.loadRule(id);
        }
        $this.$ruleEditor.data('rule-id', id);
        $('.re_label', $this.$ruleEditor).html('Editing: ' + name);
        $this.$ruleEditor.removeClass('hiddend');
        $this.$rules.addClass('hiddend');
        $this.$addRuleBTN.addClass('hiddend');
        $('>p', $this.$ruleEditor).addClass('hiddend');
    },
    closeRuleEditor: function () {
        var $this = this;

        $this.$rules.removeClass('hiddend');
        $this.$ruleEditor.addClass('hiddend');
        $this.$addRuleBTN.removeClass('hiddend');
        $('>p', $this.$ruleEditor).removeClass('hiddend');
    },
    printRules: function() {
        var $this = this;

        var rules = $this.openedRuleGroup.rules;
        $this.$rules.html('');
        $.each(rules, function(i, r){
            var $r = $this.$ruleSample.clone();
            var id = i + 0;
            $r.addClass('pg_rule_' + id);
            $r.data('id', id);
            $r.find('span').html(r.name);
            $r.appendTo($this.$rules);
        });
    },
    maxRulesCheck: function() {
        var $this = this;

        var maxRules = 4;
        if ( $this.openedRuleGroup.rules.length >= maxRules ) {
            $this.$addRuleBTN.attr('disabled', 'disabled');
        } else {
            $this.$addRuleBTN.removeAttr('disabled');
        }
    },
    loadRule: function(id) {
        var $this = this;

        var rules = $this.openedRuleGroup.rules;
        if (
            typeof rules[id] != 'undefined'
        ) {
            var rule = rules[id];
            if ( rule.rule_field != 0 ) {
                $this.toEditor(rule);
                $('.pg_rule_'+rule.field, $this.$ruleEditor).removeClass('hiddend');
            }
        }
    },
    saveOpenRule: function() {
        var $this = this;
        if ( !$this.$ruleEditor.hasClass('hiddend') ) {
            var id = $this.$ruleEditor.data('rule-id');
            return $this.saveRule(id);
        }
    },
    saveRule: function(id) {
        var $this = this;

        var rules = $this.openedRuleGroup.rules;
        if ( id == -1 ) {
            // Safe add new rule
            rules.push( JSON.parse(JSON.stringify($this.defaultRule)) );
        } else {
            var rule = $this.fromEditor();
            if ( typeof(rules[id]) != 'undefined' )
                rules[id] = rule;
            else
                rules.push(rule);
        }
        return rules;
    },
    deleteRule: function(id) {
        var $this = this;

        var rules = $this.openedRuleGroup.rules;
        if ( typeof(rules[id]) != 'undefined' ) {
            rules.splice(id, 1);
        }
        return rules;
    },
    fromEditor: function() {
        var $this = this;

        var rule = $.extend({}, $this.defaultRule);
        var $f = $this.$fields;
        rule.name = $f.name.val();
        rule.field = $f.field.val();

        switch(rule.field) {
            case 'tax':
                rule.operator = $f.termOperator.val();
                rule.values = {};
                $.each($f.termValues.find('li'), function(i, o){
                    var tax = $(this).data('taxonomy');
                    var id = $(this).data('id');
                    rule.values[tax] = rule.values[tax] || [];
                    if ( $.inArray(id, rule.values[tax]) )
                        rule.values[tax].push(id);
                    $this._taxTermNames[tax] = $this._taxTermNames[tax] || {};
                    $this._taxTermNames[tax][id] = $(this).text();
                });
                break;
            case 'cf':
                rule.operator = $f.cfOperator.val();
                rule.values = {};
                rule.values[$f.cfField.val()] = [];
                if ( $f.cfVal1.val() != '' )
                    rule.values[$f.cfField.val()].push($f.cfVal1.val());
                if ( $f.cfVal2.val() != '' )
                    rule.values[$f.cfField.val()].push($f.cfVal2.val());
                break;
            case 'title':
                rule.operator = $f.titleOperator.val();
                rule.values = [];
                if ( $f.titleValue.val() != '' )
                    rule.values.push($f.titleValue.val());
                break;
        }
        return rule;
    },
    toEditor: function(rule) {
        var $this = this;

        // Load rule values to editor
        var $f = $this.$fields;
        $f.name.val(rule.name).trigger('change');
        $f.field.val(rule.field).trigger('change');

        switch(rule.field) {
            case 'tax':
                $f.termOperator.val(rule.operator).trigger('change');
                $.each(rule.values, function(tax, ids){
                    $.each(ids, function(kk, id){
                        var $li = $('<li>');
                        $li.html($this._findTermName(rule, tax, id));
                        $li.addClass('t_'+tax+'_'+id);
                        $li.data('taxonomy', tax);
                        $li.data('id', id);
                        $li.appendTo($this.$selectedTaxTerms);
                    });
                });
                break;
            case 'cf':
                $f.cfOperator.val(rule.operator).trigger('change');
                $.each(rule.values, function(k, o){
                    $f.cfField.val(k);
                    if ( typeof o[0] !== 'undefined' )
                        $f.cfVal1.val(o[0]);
                    if ( typeof o[1] !== 'undefined' )
                        $f.cfVal2.val(o[1]);
                    return false;
                });
                break;
            case 'title':
                $f.titleOperator.val(rule.operator);
                if ( typeof rule.values[0] !== 'undefined' )
                    $f.titleValue.val(rule.values[0]);
                break;
        }
    },
    saveRuleGroup: function(id) {
        var $this = this;

        $this.openedRuleGroup.name = $this.$group.name.val();
        $this.openedRuleGroup.instance = $this.$group.instance.val();
        $this.openedRuleGroup.priority = $this.$group.priority.val();
        $this.openedRuleGroup.phrase_logic = $this.$group.phraseLogic.val();
        $this.openedRuleGroup.logic = $this.$group.ruleLogic.val();
        if ( $this.openedRuleGroup.phrase_logic != 'disabled' ) {
            $this.openedRuleGroup.phrase = $this.$group.phrase.val();
        } else {
            $this.openedRuleGroup.phrase = '';
        }

        if ( id == -1 ) {
            $this.groupsObject.push($this.openedRuleGroup);
        } else {
            $this.groupsObject[id] = $this.openedRuleGroup;
        }
    },
    loadRuleGroup: function(g) {
        var $this = this;

        if ( typeof g != 'undefined' )
            $this.openedRuleGroup = g;

        $.each($this.modalOriginalStates, function (k, o) {
            $('*[name="' + k + '"]', $this.$group.editor).val(o);
        });
        if ( typeof $this.openedRuleGroup != 'undefined' ) {
            // Group editor stuff
            $this.$group.name.val($this.openedRuleGroup.name);
            $this.$group.instance.val($this.openedRuleGroup.instance);
            $this.$group.priority.val($this.openedRuleGroup.priority);
            $this.$group.phrase.val($this.openedRuleGroup.phrase);
            $this.$group.phraseLogic.val($this.openedRuleGroup.phrase_logic).trigger('change');
            $this.$group.ruleLogic.val($this.openedRuleGroup.logic).trigger('change');
            // Rule editor stuff
            if ( typeof $this.openedRuleGroup.rules != 'undefined' ) {
                $this.closeRuleEditor();
                //ruleEditorPrintRules(g.rules);
                $this.printRules();
                $this.maxRulesCheck();
            }
        }
    },

    // ----------------- HELPER PRIVATE METHODS ------------------------
    _findTermName: function(rule, tax, id) {
        var $this = this;
        // Look within recent tax term names
        if ( typeof $this._taxTermNames[tax] != 'undefined' &&
            typeof $this._taxTermNames[tax][id] != 'undefined'
        )
            return $this._taxTermNames[tax][id];

        // Look within the initial printed tax term names
        if ( typeof rule._values != 'undefined' &&
            typeof rule._values[tax] != 'undefined' &&
            typeof rule._values[tax][id] != 'undefined'
        )
            return rule._values[tax][id];

        return '';
    }
};

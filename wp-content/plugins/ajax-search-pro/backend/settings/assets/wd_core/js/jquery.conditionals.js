// noinspection JSUnresolvedFunction
jQuery(function($){
    window.window.WPD = window.WPD || {};
    let conditionals = {};
    conditionals = $.extend({
        'types': {
            'wd-show-on': {
                'remove': 'hiddend'
            },
            'wd-hide-on': {
                'add': 'hiddend'
            },
            'wd-disable-on': {
                'add': 'disabled'
            },
            'wd-enable-on': {
                'remove': 'disabled'
            }
        },
        init: function(selector) {
            this.events.init(selector);
        },
        load: function( node ) {
            $.each(Object.keys(this.types), function(i, type) {
                $(node).find('*[' + type + ']').each(function () {
                    let rules = $(this).attr(type).split(';'),
                        last = rules[rules.length - 1],
                        attr = last.split(':')[0];
                    $(node).find('*[attr="' + attr + '"], [noattr="' + attr + '"]').last().trigger('conditionalchange');
                });
            });
        },
        events: {
            /**
             * Allows custom syntax on nodes to show/hide/enable/disable on specific conditions
             * Ex.:
             *      <node wd-show-on='name1:value1,value2;name2:valueX;..'>
             */
            init: function( selector ) {
                $(selector).each(function(){
                    let parent = this;
                    $.each(Object.keys(conditionals.types), function(i, type){
                        $(parent).find('*[' + type + ']').each(function(){
                            let target = this,
                                rules = $(this).attr(type).split(';'),
                                logic = $(this).attr('wd-conditional-logic') || 'and',
                                length = rules.length;
                            $.each(rules, function(i, rule){
                                let attr = rule.split(':')[0];
                                $(parent).find('*[attr="' + attr + '"], *[noattr="' + attr + '"], *[isparam=1][name="' + attr + '"]').each(function(){
                                    $(this).on('input', function (){
                                        $(this).trigger('conditionalchange');
                                    });
                                    $(this).on('conditionalchange', function(){
                                        if ( conditionals.functions.check(rules, parent, logic) ) {
                                            if ( typeof conditionals.types[type].add != "undefined" ) {
                                                $(target).addClass(conditionals.types[type].add);
                                            } else if ( typeof conditionals.types[type].remove != "undefined" ) {
                                                $(target).removeClass(conditionals.types[type].remove);
                                            }
                                        } else {
                                            if ( typeof conditionals.types[type].add != "undefined" ) {
                                                $(target).removeClass(conditionals.types[type].add);
                                            } else if ( typeof conditionals.types[type].remove != "undefined" ) {
                                                $(target).addClass(conditionals.types[type].remove);
                                            }
                                        }
                                    });
                                    if ( i == (length - 1) ) {
                                        $(this).trigger('conditionalchange');
                                    }
                                });
                            });
                        });
                    });

                    let observer = new MutationObserver(function(){
                        conditionals.load(parent);
                    });
                    observer.observe(parent, { attributes: false, childList: true, subtree: true });
                });
            }
        },
        functions: {
            /**
             * Checks if all/any the conditional rules are matching within the parent scope
             *
             * @param {[]} rules Array of rules in format "name:value1,value2,..,valueN"
             * @param {{}} parent Scope in whic to look for the rules
             * @param {'and'} logic and||or logic for the rules
             * @returns {boolean}
             */
            check: function(rules, parent, logic) {
                let allRulesMatched = true,
                    anyRuleMatched = false;
                $.each(rules, function(i, rule){
                    let attr = rule.split(':')[0],
                        values = rule.split(':').slice(1)[0].split(',');
                    $(parent).find('*[attr="' + attr + '"], [noattr="' + attr + '"], *[isparam=1][name="' + attr + '"]').each(function(){
                        let value = conditionals.functions.getNodeValue(this),
                            match = false;
                        $.each(values, function(ii, val){
                            if ( Array.isArray(value) ) {
                                if ( value.includes(val) ) {
                                    match = true;
                                    return false;
                                }
                            } else {
                                if ( value == val ) {
                                    match = true;
                                    return false;
                                }
                            }
                        });
                        if ( logic == 'and' ) {
                            if ( !match ) {
                                allRulesMatched = false;
                                return false;
                            }
                        } else {
                            if ( match ) {
                                anyRuleMatched = true;
                                return false;
                            }
                        }
                    });
                    if (
                        ( logic == 'and' && !allRulesMatched ) ||
                        ( logic == 'or' && anyRuleMatched )
                    ) {
                        return false;
                    }
                });

                return logic == 'and' ? allRulesMatched : anyRuleMatched;
            },
            getNodeValue: function(node) {
                let name = node.nodeName.toLowerCase(), ret;

                if ( name == 'select' || name == 'textarea' ) {
                    ret = $(node).val();
                } else if ( name == 'input' ) {
                    let type = $(node).attr('type');
                    if ( type == 'checkbox' ) {
                        ret = $(node).is(':checked');
                    } else if ( node.type == 'text' || node.type == 'number' || node.type == 'hidden' ) {
                        ret = $(node).val();
                    }
                }

                ret = ret === null ? '' : ret;
                return ret;
            }
        }
    }, conditionals);
    window.WPD.Conditionals = conditionals;
});
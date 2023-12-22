// Init is done via wp_localize_script, but just to make sure
window.WD_FrontFilters = window.WD_FrontFilters || {};
jQuery(function($){
    var controller = window.WD_FrontFilters;

    // Reverse extend, so the actual data moves from the WD_FrontFilters -> controller
    controller = $.extend({
        'columns': [],          // column information
        'filters': [],          // user created filters array

        'initialized': false,
        'modulesDefault': {},   // default module data
        'modules': {},
        'currentModule': {},    // currently opened module
        'newPosition': {
            'column': 1,
            'row': 1,
            'position': 1
        },

        'nodes': {
            'columns': ".wd_frontfilters_column",
            'container': "#wd_frontfilters",
            'param': "#wd_frontfilters input[isparam=1]",
            'filter': '.wd_frontfilters_filter',
            'addFilter': '.wd_frontfilters_add_filter',
            'columnCount': 'select[name=wd_frontfilters_columns_count]',
            'newFilter': '.wd_frontfilters_new_filter',
            'newFilterContainer': '#wd_frontfilters_new_filter_container',
            'newFilterOuter': '#wd_frontfilters_new_filter',
            'newFilterModulesContainer': '#wd_frontfilters_modules_container',
            'filterSave': '#wd_frontfilters input[name=wd_frontfilters_save]',
            'filterCancel': '#wd_frontfilters input[name=wd_frontfilters_cancel]',
            'filterDelete': '#wd_frontfilters input[name=wd_frontfilters_delete]',
            'filterTitle': '.wd_ff_filter_title',
            'module': '.wd_frontfilters_module'
        },

        'tmp': {}, // Temporary object variable, modules can use this if needed

        init: function() {
            if ( !this.initialized ) {
                this.events.init();
                this.initialized = true;
            }
        },

        // Module related methods
        module: {
            register: function( module ) {
                if ( typeof controller.modules[module.type] == "undefined" ) {
                    controller.modules[module.type] = module;
                    controller.modules[module.type].init();
                    console.log('Controller - init: ', module.type, module);
                }
            },

            get: function( type ) {
                if ( typeof controller.modules[type] != "undefined" ) {
                    return controller.modules[type];
                } else {
                    return {};
                }
            },

            getData: function( type ) {
                if ( typeof controller.modules[type] != "undefined" ) {
                    return controller.modules[type].data();
                } else {
                    return {};
                }
            }
        },

        // Filter related methods
        filter: {
            new: function(filter) {
                var newFilter = $.extend({}, filter);
                newFilter.id = WD_Helpers.uuidv4();
                controller.filters.push(newFilter);
                controller.visual.addFilter(newFilter);
                controller.private._saveData();
                return newFilter.id;
            },
            delete: function(id) {
                var index = controller.filter.get(id);
                if (index !== false) {
                    controller.visual.removeFilter(id);
                    controller.filters.splice(index, 1);
                    controller.private._saveData();
                }
            },
            change: function(filter, id) {
                var index = controller.filter.get(id);
                if (index !== false) {
                    controller.filters[index] = filter;
                    controller.visual.changeFilter(filter, id);
                    controller.private._saveData();
                }
            },
            get: function(id) {
                var index = false;
                $.each(controller.filters, function(i, filter){
                    if ( filter.id == id ) {
                        index = i;
                        return false;
                    }
                });
                return index;
            }
        },

        // Event handlers
        events: {
            // To store the module data, before the module editor is opened - to check for changes
            moduleDataBeforeOpen: null,

            init: function() {
                var _n = controller.nodes;

                // Init the columns
                this.initColumns(_n);

                // Change the column numbers
                this.initColumnCounter(_n);

                // Add new filter
                this.initNewFilterModal(_n);

                // New module selection
                this.initNewModuleSelection(_n);

                // Edit or Add new filter module
                this.initModuleEditor(_n);

                // Coditional rules via attributes
                WPD.Conditionals.init(_n.module);
            },

            initColumns: function(_n) {
                // Init the columns
                $(_n.columns).sortable({
                    items: _n.filter,
                    connectWith: _n.columns
                }).disableSelection();
                $(_n.columns).on('sortstop', function(e, ui){
                    $(this).closest(_n.container).find(_n.addFilter).each(function(){
                        var $parent = $(this).closest(_n.columns);
                        var $add = $(this).detach();
                        $add.appendTo($parent);
                    });
                    controller.private._updatePositions();
                });
            },

            initColumnCounter: function(_n) {
                // Change the column numbers
                $(_n.columnCount).on('change', function(){
                    var $this = $(this);
                    var $lastVisible = $( $(_n.columns).get(0) );
                    $(_n.columns).css('display', 'none');
                    $(_n.columns).each(function(i, o){
                        if ( i >= parseInt($this.val()) ) {
                            $(this).find(_n.filter).detach().appendTo( $lastVisible );
                        } else {
                            $lastVisible = $(this);
                            $(this).css('display', 'block');
                        }
                    });
                    $(_n.columns).trigger('sortstop');
                }).trigger('change');
            },

            initNewFilterModal:  function(_n) {
                $(_n.addFilter).on('click', function(){
                    controller.visual.openFilterModal();
                    controller.newPosition = controller.visual._getNextFilterPosition( $(this).closest(_n.columns) );
                });
                $(_n.newFilterContainer).on('click', function(e){
                    if ( e.target == this ) {
                        controller.visual.closeFilterModal();
                    }
                });
            },

            initNewModuleSelection: function(_n) {
                $(_n.newFilterOuter).on('click', _n.newFilter + ':not([disabled])', function() {
                    var type = $(this).data('moduletype');
                    controller.visual.closeFilterModal();
                    controller.currentModule = controller.modules[type];
                    controller.currentModule.load();
                    var node = controller.visual.openModuleEditor(type);
                    controller.events.moduleDataBeforeOpen = "";
                });
            },

            initModuleEditor: function(_n) {
                var moduleChange;

                $(_n.columns).on('click', _n.filter, function() {
                    //var module = $(this).data('moduletype');
                    var index = controller.filter.get( $(this).data('id') );
                    if (index !== false) {
                        controller.visual.closeFilterModal();
                        controller.currentModule = controller.modules[controller.filters[index].type];
                        controller.currentModule.load(controller.filters[index]);
                        controller.events.moduleDataBeforeOpen = JSON.stringify(controller.currentModule.getData());
                        var node = controller.visual.openModuleEditor(controller.filters[index].type);
                    }
                });

                // Save module
                $(_n.filterSave).on('click', function(e){
                    e.preventDefault();
                    var moduleData = controller.currentModule.getData();
                    if ( controller.currentModule.validate(moduleData) ) {
                        controller.visual.closeModuleEditor();
                        if (moduleData.id === false) {   // New filter
                            controller.filter.new($.extend(moduleData, controller.newPosition));
                        } else {
                            controller.filter.change(moduleData, moduleData.id);
                        }
                    }
                });

                // Cancel module
                $(_n.filterCancel).on('click', function(e){
                    e.preventDefault();
                    if (
                        controller.events.moduleDataBeforeOpen == JSON.stringify(controller.currentModule.getData()) ||
                        confirm('Are you sure you cancel changes to this filter?')
                    ) {
                        controller.visual.closeModuleEditor();
                    }
                });
                $(_n.newFilterModulesContainer).on('mousedown', function(e){
                    if (
                        e.target == this &&
                        (
                            controller.events.moduleDataBeforeOpen == JSON.stringify(controller.currentModule.getData()) ||
                            confirm('Are you sure you cancel changes to this filter?')
                        )
                    ) {
                        controller.visual.closeModuleEditor();
                    }
                });

                // Delete module
                $(_n.filterDelete).on('click', function(e){
                    e.preventDefault();
                    var moduleData = controller.currentModule.getData();
                    if ( moduleData.id !== false ) {
                        if ( confirm('Are you sure you want to delete this filter?') ) {
                            controller.filter.delete(moduleData.id);
                            controller.visual.closeModuleEditor();
                        }
                    }
                });
            }
        },

        __delete__conditionals: {
            /**
             * Allows custom syntax on nodes to show/hide/enable/disable on specific conditions
             * Ex.:
             *      <node wd-show-on='name1:value1,value2;name2:valueX;..'>
             */
            init: function() {
                $(controller.nodes.module).each(function(){
                    var parent = this;
                    var types = ['wd-show-on', 'wd-hide-on', 'wd-disable-on', 'wd-enable-on'];
                    $.each(types, function(i, type){
                        $(parent).find('*[' + type + ']').each(function(){
                            var target = this;
                            var rules = $(this).attr(type).split(';');
                            var length = rules.length;
                            $.each(rules, function(i, rule){
                                var attr = rule.split(':')[0];
                                $(parent).find('*[attr="' + attr + '"], [noattr="' + attr + '"]').each(function(){
                                    $(this).on('input', function (){
                                        $(this).trigger('conditionalchange');
                                    });
                                    $(this).on('conditionalchange', function(e){
                                        var allRulesMatch = controller.conditionals.check(rules, parent);
                                        if ( allRulesMatch ) {
                                            switch (type) {
                                                case 'wd-hide-on':
                                                    $(target).addClass('hiddend');
                                                    break;
                                                case 'wd-enable-on':
                                                    $(target).removeClass('disabled');
                                                    break;
                                                case 'wd-disable-on':
                                                    $(target).addClass('disabled');
                                                    break;
                                                default:    // show-on
                                                    $(target).removeClass('hiddend');
                                            }
                                        } else {
                                            switch (type) {
                                                case 'wd-hide-on':
                                                    $(target).removeClass('hiddend');
                                                    break;
                                                case 'wd-enable-on':
                                                    $(target).addClass('disabled');
                                                    break;
                                                case 'wd-disable-on':
                                                    $(target).removeClass('disabled');
                                                    break;
                                                default:    // show-on
                                                    $(target).addClass('hiddend');
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
                });
            },

            load: function( node ) {
                var types = ['wd-show-on', 'wd-hide-on', 'wd-disable-on', 'wd-enable-on'];
                $.each(types, function(i, type) {
                    $(node).find('*[' + type + ']').each(function () {
                        var rules = $(this).attr(type).split(';');
                        var last = rules[rules.length - 1];
                        var attr = last.split(':')[0];
                        $(node).find('*[attr="' + attr + '"], [noattr="' + attr + '"]').last().trigger('conditionalchange');
                    });
                });
            },

            /**
             * Checks if all the conditional rules are matching within the parent scope
             *
             * @param {[]} rules Array of rules in format "name:value1,value2,..,valueN"
             * @param {{}} parent Scope in whic to look for the rules
             * @returns {boolean}
             */
            check: function(rules, parent) {
                var allRulesMatched = true;
                $.each(rules, function(i, rule){
                    var attr = rule.split(':')[0];
                    var values = rule.split(':').slice(1)[0].split(',');
                    $(parent).find('*[attr="' + attr + '"], [noattr="' + attr + '"]').each(function(){
                        var value = controller.helper.getNodeValue(this);
                        var match = false;
                        $.each(values, function(ii, val){
                            if ( value == val ) {
                                match = true;
                                return false;
                            }
                        });
                        if ( !match ) {
                            allRulesMatched = false;
                            return false;
                        }
                    });
                    if ( !allRulesMatched ) {
                        return false;
                    }
                });

                return allRulesMatched;
            }
        },

        // Private methods
        private: {
            _updatePositions: function() {
                $(controller.nodes.filter).each(function(){
                    var d = controller.visual._getFilterPosition( $(this) );
                    var index = controller.filter.get( $(this).data('id') );
                    if (index !== false) {
                        controller.filters[index] = $.extend(controller.filters[index], d);
                    }
                });
                controller.private._saveData();
            },

            _saveData: function() {
                $(controller.nodes.param).val(
                    '_decode_' + WD_Helpers.Base64.encode(JSON.stringify({
                        'columns': controller.columns,
                        'filters': controller.filters
                    }))
                );
            },

            _filterTypeExists: function(type) {
                var exists = false;
                $.each(controller.filters, function(i, filter) {
                    if (filter.type == type) {
                        exists = true;
                        return false;
                    }
                });
                return exists;
            }
        },

        // Visual Controller
        visual: {
            _getSampleFilter: function() {
                return $('#wd_frontfilters_footer_sample_data .wd_frontfilters_filter').clone();
            },
            _getFilterPosition: function( $filter ) {
                return {
                    'position': $filter.index() + 1,
                    'column': $filter.closest(controller.nodes.columns).index() + 1,
                    'row': 1
                };
            },
            _getNextFilterPosition: function( $column ) {
                if ( $column.find(controller.nodes.filter).length > 0 ) {
                    var $n = $column.find(controller.nodes.filter).last();
                    return {
                        'position': $n.index() + 1,
                        'column': $column.index() + 1,
                        'row': 1
                    }
                } else {
                    return {
                        'position': 1,
                        'column': $column.index() + 1,
                        'row': 1
                    }
                }
            },
            addFilter: function( filter ){
                var $n = this._getSampleFilter();
                // Do NOT use .data(..), it cannot be queried
                $n.attr('data-id', filter.id);
                $n.find(controller.nodes.filterTitle).html(filter.label.text);
                $n.appendTo($($(controller.nodes.columns).get(filter.column - 1)));
                $(controller.nodes.columns).trigger('sortstop');
            },
            removeFilter: function( id ) {
                $(controller.nodes.columns).find('*[data-id="' + id + '"]').remove();
            },
            changeFilter: function( filter, id ) {
                var $n = $('.wd_frontfilters_column').find('*[data-id="' + id + '"]');
                $n.find(controller.nodes.filterTitle).html(filter.label.text);
            },
            openModuleEditor: function(type) {
                var _n = controller.nodes;
                $(_n.newFilterModulesContainer).css({'display':'flex', 'opacity': 1});
                $(_n.newFilterModulesContainer).find(_n.module).css('display', 'none');
                $(_n.newFilterModulesContainer).find(_n.module + '_' + type).css('display', 'block');
                if ( controller.currentModule.getData().id === false ) { // new filter
                    $(_n.filterDelete).css('display', 'none');
                } else {
                    $(_n.filterDelete).css('display', 'inline');
                }
                return $(_n.newFilterModulesContainer).find(_n.module + '_' + type);
            },
            closeModuleEditor: function() {
                $('#wd_frontfilters_modules_container').css({'display':'none', 'opacity': 0});
            },
            openFilterModal: function() {
                $(controller.nodes.newFilter).removeAttr('disabled').each( function(i, o){
                    if (
                        controller.modules[$(this).data('moduletype')].single &&
                        controller.private._filterTypeExists($(this).data('moduletype'))
                    ) {
                        $(this).attr('disabled', 'disabled');
                    }
                });
                $(controller.nodes.newFilterContainer).css({'display':'flex', 'opacity': 1});
            },
            closeFilterModal: function() {
                $(controller.nodes.newFilterContainer).css({'display':'none', 'opacity': 0});
            }
        },

        // Helper functions, useful for modules
        helper: {
            /**
             * Sets the [attr] attributes on all descendants of "node", when exist in "data" object
             *
             * Single depth
             *      <input attr[myattr]
             * Multiple depth
             *      <input attr[myattr.sub1.sub2...subN]
             *
             * @param {{}} node
             * @param {{}} data
             * @param {string} [exclude=''] - Excluded attributes
             */
            setAttributes: function(node, data, exclude) {
                exclude = exclude || '';
                var ex = exclude.replace(/\s/g,'').split(',').filter(function(v){ return v!==''});

                $(node).find('[attr]').each(function(){
                    var att = $(this).attr('attr');
                    if ( $.inArray(att, ex) == -1 ) {
                        if ( att.split('.').length > 0 ) {  // Multiple depth
                            var pointer = data;
                            var arr = att.split('.');
                            var _this = this;
                            $.each(arr, function(index, v){
                                if ( typeof pointer[v] != 'undefined' ) {
                                    if ( index == (arr.length - 1) ) {	// last item
                                        controller.helper.setNodeValue(_this, pointer[v]);
                                    } else {
                                        pointer = pointer[v];
                                    }
                                }
                            });
                        } else if ( typeof data[att] != 'undefined' ) {
                            controller.helper.setNodeValue(this, data[att]);
                        }
                    }
                });
            },

            /**
             * Gets the [attr] attributes from all descendants of "node"
             *
             * Single depth
             *      <input attr[myattr]
             * Multiple depth
             *      <input attr[myattr.sub1.sub2...subN]
             *
             * @param {{}} node
             * @param {string} [attributes=''] - List of attributes
             * @returns {{}}
             */
            getAttributes: function(node, attributes) {
                var ret = {};
                attributes = attributes || '';
                attributes = attributes.replace(/\s/g,'').split(',').filter(function(v){ return v!==''});
                if ( attributes.length == 0 ) {
                    $(node).find('[attr]').each(function(){
                        var att = $(this).attr('attr');
                        if ( att.split('.').length > 0 ) {  // Multiple depth
                            var pointer = ret;
                            var arr = att.split('.');
                            var _this = this;
                            $.each(arr, function(index, v){
                                pointer[v] = typeof pointer[v] == 'undefined' ? {} : pointer[v];
                                if ( index == (arr.length - 1) ) {	// last item
                                    pointer[v] = controller.helper.getNodeValue(_this);
                                } else {
                                    pointer = pointer[v];
                                }
                            });
                        } else {                            // Single depth
                            ret[att] = controller.helper.getNodeValue(this);
                        }
                    });
                } else {
                    $.each(attributes, function(i, att){
                        $(node).find('[attr=' + att + ']').each(function(){
                            ret[att] = controller.helper.getNodeValue(this);
                        });
                    });
                }

                return ret;
            },

            /**
             * Sets the node value, depending on node type
             *
             * @param {{}} node
             * @param {string} value
             */
            setNodeValue: function(node, value) {
                var name = node.nodeName.toLowerCase();
                if ( name == 'select' || name == 'textarea' ) {
                    $(node).val(value);
                } else if ( name == 'input' ) {
                    var type = $(node).attr('type');
                    if ( type == 'checkbox' ) {
                        $(node).prop('checked', value);
                    } else if ( node.type == 'text' || node.type == 'number' || node.type == 'hidden' ) {
                        $(node).val(value);
                    }
                } else if ( name == 'span' ) {
                    $(node).html(value);
                }
            },

            /**
             * Gets the node value, based on the node type
             *
             * @param {{}} node
             * @returns {string}
             */
            getNodeValue: function(node) {
                var name = node.nodeName.toLowerCase(), ret;

                if ( name == 'select' || name == 'textarea' ) {
                    ret = $(node).val();
                } else if ( name == 'input' ) {
                    var type = $(node).attr('type');
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
    }, controller);

    // Restore the reference, that $.extend breaks
    window.WD_FrontFilters = controller;

    // Init
    if ( window.location.hash.substr(1) == 314 ) {
        controller.init();
        console.log('init via load');
    } else {
        $('a[tabid=314], a[tabid=3]').on('click', function () {
            // Trigger the init only
            console.log('init via click');
            controller.init();
        });
    }
});
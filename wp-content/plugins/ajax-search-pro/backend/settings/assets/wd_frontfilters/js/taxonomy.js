jQuery(function($){
    var controller = WD_FrontFilters;

    var module = {
        'type': 'taxonomy',      // REQUIRED
        'single': false,         // REQUIRED
        'initialized': false,   // REQUIRED
        'data': {},             // REQUIRED - current data, loaded for editing
        'default': {},          // REQUIRED - default module data

        '_cache': {
            'taxonomy': {
                // key => terms (key = taxonomy + '__' + search phrase)
            },
            'names': {
                // key => name (key = taxonomy + '__' + term_id)
            }
        },

        'nodes': {
            'container': '.taxonomy_filter_container',
            'right': '#taxonomy_sortable',
            'left': '#taxonomy_draggable',
            'attributes': '.taxonomy_filter_attributes',
            'fields': '.taxonomy_filter_fields',
            'taxonomySelect': '.taxonomy_filter_attributes select[attr=taxonomy]',
            'modeSelect': '.taxonomy_filter_attributes select[attr=mode]',
            'otherAttributes': '.taxonomy_filter_other_attributes',
            'sampleTerm': '.taxonomy_filter_sample>li',
            'label': '.taxonomy_filter_attributes input[attr="label.text"]',
            'remove': '.taxonomy_filter_remove',
            'excludedLabel': '.taxonomy_filter_mode_label_excluded',
            'includedLabel': '.taxonomy_filter_mode_label_included',
            'loader': '.taxonomy_filter_loader',
            'search': '.taxonomy_draggable_search',
            'selected': '.taxonomy_filter_layout_options input[attr="display_mode_args.dropdown.default"]',
            'selectedSearch': 'input.taxonomy_filter_search',
            'selectedSearchNores': '.taxonomy_filter_search_nores',
            'selectedLabel': '.taxonomy_filter_selected_name',
            'selectedSearchResults': '.taxonomy_filter_search_res',
            'displayMode': '.taxonomy_filter_attributes select[attr=display_mode]'
        },

        // Automatically called
        init: function() {      // REQUIRED
            if ( !this.initialized ) {
                module.default = $.extend(module.default, controller.modulesDefault[this.type].data);
                this.visual.init();
                this.events.init();
                this.initialized = true;
            }
        },
        // Load the filter data to the modal window
        load: function(data) {  // REQUIRED
            data = data || {};
            this.data = $.extend({}, this.default.option, data);
            this.data.display_mode_args.dropdown.default =
                this.data.display_mode_args.dropdown.default.trim() == '' ?
                    this.default.option.display_mode_args.dropdown.default : this.data.display_mode_args.dropdown.default;
            this.visual.load();
            console.log('Module - load:', this.type, data);
        },
        // Validate the filter data
        validate: function( data ) {
            data = data || this.getData();
            return this.visual.validate(data);
        },
        // Return the manipulated filter data
        getData: function() {      // REQUIRED
            var _this = this;
            this.data.items = [];
            this.data.type = this.type;
            // items
            $(module.nodes.right).find('li').each(function(){
                // item attributes
                _this.data.items.push($.extend({},
                    _this.default.item,
                    controller.helper.getAttributes(this), {
                    'field': $(this).data('field')
                }));
            });
            // all other generic attributes
            this.data = $.extend({},
                this.data,
                controller.helper.getAttributes(module.nodes.attributes),
                controller.helper.getAttributes(module.nodes.otherAttributes)
            );

            return this.data;
        },

        events: {
            init: function() {
                var mv = module.visual, me = module.events, mn = module.nodes;
                $(mn.right).on('sortupdate', function(){
                    $(mn.left).find('li').removeClass("ui-state-disabled");
                    $(mn.right).find('li').each(function () {
                        $(mn.left).find("li[data-id='"+$(this).data('id')+"']").addClass("ui-state-disabled");
                    });
                });

                // Remove item button
                $(mn.right).on('click', mn.remove, function(){
                    $(this).closest('li[data-id]').remove();
                    $(mn.right).trigger('sortupdate');
                });

                $(mn.container).on('click', function(){
                   mv.removeValidation();
                });

                $(mn.modeSelect).on('change', function(){
                    mv.changeMode();
                });

                $(mn.displayMode).on('change', function(){
                    mv.changeDisplayMode();
                });

                // Set selected
                $(mn.selectedSearchResults).on('click', 'li', function(){
                    mv.setSelected($(this).text(), $(this).attr('key'));
                });

                // Remove selected
                $(mn.otherAttributes + ' ' + mn.selectedLabel).prev('.fa').on('click', function(){
                    mv.removeSelected();
                });

                me.initTaxonomySelector();
                me.initTaxonomyTermSearch();
                me.initSelectedSearch();
            },

            initTaxonomySelector: function() {
                var mv = module.visual;
                $(module.nodes.taxonomySelect).attr('prevValue', $(module.nodes.taxonomySelect).val());
                $(module.nodes.taxonomySelect).on('change', function(e){
                    if (
                        (
                            $(module.nodes.right).find('li').length == 0 &&
                            !( $(module.nodes.selected).val() > 0 )
                        ) || confirm('Do you want to change taxonomy? It will remove all previously selected terms.')
                    ) {
                        var taxonomy = $(this).val();
                        if ( module.cache.getTaxonomy(taxonomy) !== false ) {
                            mv.printTerms(module.cache.getTaxonomy(taxonomy));
                            mv.clearRight();
                            mv.initLeft();
                            if ( $(module.nodes.left).find('li').length == 0 ) {
                                mv.hideSearch();
                            } else {
                                mv.showSearch();
                            }
                        } else {
                            var data = {
                                'action': 'wd_ff_get_taxonomy_terms',
                                'wd_required': 1,
                                'wd_taxonomy': taxonomy
                            };
                            mv.showLoader();
                            $.post(ajaxurl, data, function (response) {
                                if (response.length > 0) {
                                    var terms = response.match(/!!ASPSTART_HTML!!(.*[\s\S]*)!!ASPEND_HTML!!/);
                                    if (typeof (terms[1]) != "undefined") {
                                        terms = JSON.parse(terms[1]);
                                        module.cache.setTaxonomy(taxonomy, terms);
                                        mv.printTerms(terms);
                                        mv.clearRight();
                                        mv.initLeft();
                                        mv.hideLoader();
                                        if ( $(module.nodes.left).find('li').length == 0 ) {
                                            mv.hideSearch();
                                        } else {
                                            mv.showSearch();
                                        }
                                    }
                                }
                            }, "text");
                        }

                        $(module.nodes.taxonomySelect).attr('prevValue', $(module.nodes.taxonomySelect).val());
                        mv.showSelectedResults();
                        mv.removeSelected();
                    } else {
                        $(module.nodes.taxonomySelect).val( $(module.nodes.taxonomySelect).attr('prevValue') );
                    }
                });
            },

            initTaxonomyTermSearch: function() {
                var mv = module.visual;
                
                $(module.nodes.selected).find('input').on('keypress', function(e){
                    var key = e.keyCode || e.which;
                    var phrase = $(this).val();
                    var taxonomy = $(module.nodes.taxonomySelect).val();
                    if ( key == 13 ) {
                        e.preventDefault();
                        if ( module.cache.getTaxonomy(taxonomy + '__' + phrase) !== false ) {
                            mv.printTerms(module.cache.getTaxonomy(taxonomy + '__' + phrase));
                            mv.initLeft();
                        } else {
                            var data = {
                                'action': 'wd_ff_get_taxonomy_terms',
                                'wd_required': 1,
                                'wd_taxonomy': $(module.nodes.taxonomySelect).val(),
                                'wd_phrase': phrase
                            };
                            mv.showLoader();
                            $.post(ajaxurl, data, function (response) {
                                if (response.length > 0) {
                                    var terms = response.match(/!!ASPSTART_HTML!!(.*[\s\S]*)!!ASPEND_HTML!!/);
                                    if (typeof (terms[1]) != "undefined") {
                                        terms = JSON.parse(terms[1]);
                                        module.cache.setTaxonomy(taxonomy + '__' + phrase, terms);
                                        mv.printTerms(terms);
                                        mv.initLeft();
                                        mv.hideLoader();
                                    }
                                }
                            }, "text");
                        }
                    }
                });
            },

            initSelectedSearch: function() {
                // Selected search
                var t;
                $(module.nodes.selectedSearch).on('input', function(e){
                    var key = e.keyCode || e.which;
                    var phrase = $(this).val();
                    var taxonomy = $(module.nodes.taxonomySelect).val();
                    if ( key == 13 ) {
                        e.preventDefault();
                    }
                    clearTimeout(t);
                    t = setTimeout(function(){
                        e.preventDefault();
                        if ( module.cache.getTaxonomy(taxonomy + '__' + phrase) !== false ) {
                            module.visual.showSelectedResults(module.cache.getTaxonomy(taxonomy + '__' + phrase));
                        } else {
                            var data = {
                                'action': 'wd_ff_get_taxonomy_terms',
                                'wd_required': 1,
                                'wd_taxonomy': $(module.nodes.taxonomySelect).val(),
                                'wd_phrase': phrase
                            };
                            module.visual.showLoader();
                            $.post(ajaxurl, data, function (response) {
                                if (response.length > 0) {
                                    var terms = response.match(/!!ASPSTART_HTML!!(.*[\s\S]*)!!ASPEND_HTML!!/);
                                    if (typeof (terms[1]) != "undefined") {
                                        terms = JSON.parse(terms[1]);
                                        module.cache.setTaxonomy(taxonomy + '__' + phrase, terms);
                                        module.visual.showSelectedResults(terms);
                                        module.visual.hideLoader();
                                    }
                                }
                            }, "text");
                        }
                    }, 350);
                });
            }
        },

        visual: {
            init: function() {
                // This is re-used
                module.visual.initLeft();

                $(module.nodes.right).sortable({
                    update: function (event, ui) {
                        var $item = $(ui.item);
                        $item.css({
                            "width": "",
                            "height": ""
                        });
                    },
                    cancel: '.taxonomy_filter_mode_label'
                }).disableSelection();

                // Internal items
                $(module.nodes.selectedSearchResults).find('li').each(function(){
                    if ( $(this).attr('key').trim() != '' ) {
                        module.cache.setName(
                            {'id': $(this).attr('key'), 'label': $(this).text()},
                            '_internal');
                    }
                });
            },

            load: function() {
                var mv = module.visual;
                
                mv.clearRight();
                mv.clearLeft();

                $(module.nodes.taxonomySelect).val(module.data.taxonomy);
                $(module.nodes.selectedSearchResults).find('li').first().trigger('click');
                mv.loadTermsAndSelected();
                controller.helper.setAttributes(module.nodes.attributes, module.data);
                controller.helper.setAttributes(module.nodes.otherAttributes, module.data);
                $(module.nodes.right).trigger('sortupdate');
                mv.changeMode();
                mv.changeDisplayMode();
                mv.removeValidation();
                mv.showSelectedResults();
            },

            initLeft: function() {
                $(module.nodes.left).find('li').draggable({
                    connectToSortable: module.nodes.right,
                    update: function (event, ui) {},
                    drag: function(e, ui) {
                        if ( typeof ui.helper != 'undefined')
                            $(ui.helper).css({ height: 84, width: 260, zIndex: 1 });   //drag dimensions
                    },
                    cancel: ".ui-state-disabled, input, label",
                    helper: "clone"
                }).disableSelection();
            },

            clearRight: function () {
                $(module.nodes.right).find('li').detach();
            },

            clearLeft: function() {
                $(module.nodes.left).find('>span, >li').detach();
            },

            changeMode: function() {
                var n = module.nodes;
                if ( $(n.modeSelect).val() == 'include' ) {
                    $(n.excludedLabel).addClass('hiddend');
                    $(n.includedLabel).removeClass('hiddend');
                    $(n.right).addClass('taxonomy_filter_include').removeClass('taxonomy_filter_exclude');
                } else {
                    $(n.includedLabel).addClass('hiddend');
                    $(n.excludedLabel).removeClass('hiddend');
                    $(n.right).removeClass('taxonomy_filter_include').addClass('taxonomy_filter_exclude');
                }
            },

            changeDisplayMode: function() {
                var mn = module.nodes;
                if ( $(mn.displayMode).val() == 'checkboxes' ) {
                    $(mn.right).removeAttr('noncheckbox');
                } else {
                    $(mn.right).attr('noncheckbox', '');
                }
            },

            loadTermsAndSelected: function() {
                var cachedTerms, cachedSelected, d=module.data, mv = module.visual;
                
                cachedTerms = module.cache.getNames(d.items, d.taxonomy);
                cachedSelected = module.cache.getName(d.display_mode_args.dropdown.default, '_internal');
                cachedSelected =
                    cachedSelected === false ?
                        module.cache.getName(d.display_mode_args.dropdown.default, d.taxonomy) :
                        cachedSelected;
console.log(cachedTerms, cachedSelected);
                if ( cachedTerms !== false && cachedSelected !== false ) {   // Reopening of filter without page refresh
                    mv.printTerms(cachedTerms, false);
                    mv.initLeft();
                    mv.setSelected(cachedSelected, d.display_mode_args.dropdown.default);
                } else {
                    var data = {
                        'action': 'wd_ff_get_selected_taxonomy_terms',
                        'wd_required': 1,
                        'wd_taxonomy': d.taxonomy,
                        'wd_items': d.items
                    };
                    mv.showLoader();
                    $.post(ajaxurl, data, function (response) {
                        if (response.length > 0) {
                            var terms = response.match(/!!ASPSTART_HTML!!(.*[\s\S]*)!!ASPEND_HTML!!/);
                            if (typeof (terms[1]) != "undefined") {
                                terms = JSON.parse(terms[1]);
                                mv.printTerms(terms, false);
                                mv.initLeft();
                                mv.hideLoader();
                            }
                            var selected = response.match(/!!ASPSTART_SELECTED!!(.*[\s\S]*)!!ASPEND_SELECTED!!/);
                            if (typeof (selected[1]) != "undefined") {
                                selected = JSON.parse(selected[1]);
                                mv.setSelected(selected['id'], selected['label']);
                            }
                        }
                    }, "text");
                }
            },

            validate: function(data) {
                var valid = true, mn=module.nodes;

                if ( data.taxonomy == null || data.taxonomy == '' ||data.taxonomy == 'select' ) {
                    $(mn.taxonomySelect).parent().addClass('wd_ff_invalid');
                    valid = false;
                }
                if ( data.label.text.trim() == '' ) {
                    $(mn.label).addClass('wd_ff_invalid');
                    valid = false;
                }
                if ( data.mode == 'include' && data.items.length == 0 ) {
                    $(mn.right).addClass('wd_ff_invalid');
                    valid = false;
                }

                if ( !valid && typeof $('body').get(0).scrollIntoView != 'undefined' ) {
                    $(mn.container).find('.wd_ff_invalid').get(0).scrollIntoView();
                }

                return valid;
            },

            removeValidation: function() {
                $(module.nodes.container).find('*').removeClass('wd_ff_invalid');
            },

            showLoader: function() {
                $(module.nodes.loader).removeClass('hiddend');
            },

            hideLoader: function() {
                $(module.nodes.loader).addClass('hiddend');
            },

            showSearch: function() {
                $(module.nodes.search).removeClass('hiddend').find('input').val('');
            },

            hideSearch: function() {
                $(module.nodes.search).addClass('hiddend');
            },

            printTerms: function(terms, left) {
                left = typeof left == 'undefined' ? true : left;
                if ( left ) {
                    module.visual.clearLeft();
                } else {
                    module.visual.clearRight();
                }
                $.each(terms, function(index, term){
                    var $n;
                    if ( left ) {
                        $n = $(module.nodes.sampleTerm).clone().appendTo($(module.nodes.left));
                    } else {
                        $n = $(module.nodes.sampleTerm).clone().appendTo($(module.nodes.right));
                    }
                    $n.attr('data-id', term['id']);
                    $n.attr('term_level', term['level']);
                    controller.helper.setAttributes($n.get(0), term);
                });
                module.cache.setNames(terms, $(module.nodes.taxonomySelect).val());
            },

            setSelected: function(label, id) {
                var n = module.nodes;
                $(n.selectedLabel).text(label);
                $(n.selected).val(id);
                $(n.selectedSearch).addClass('hiddend');
                $(n.selectedLabel).parent().removeClass('hiddend');
                module.cache.setName(
                    {'id': id, 'label': label},
                    id > 0 ? $(n.taxonomySelect).val() : '_internal'
                );
            },

            removeSelected: function() {
                var n = module.nodes;
                $(n.selectedSearch).removeClass('hiddend');
                $(n.selectedLabel).parent().addClass('hiddend');
                $(n.selectedSearch).val('');
                $(n.selectedLabel).val('');
                $(n.selected).val('');
            },

            showSelectedResults: function(items) {
                var mn = module.nodes;
                items = items || [];
                $(mn.selectedSearchResults).find('li').each(function(i){
                   if ( i > 2 ) {
                       $(this).detach();
                   }
                });
                $.each(items, function(i, item){
                    var $n = $($(mn.selectedSearchResults).find('li').get(0)).clone();
                    $n.attr('key', item['id']);
                    $n.text(item['label']);
                    $n.appendTo($(mn.selectedSearchResults).find('ul'));
                });
                $(mn.selectedSearchResults).removeClass('hiddend');
            },

            hideSelectedResults: function() {
                $(module.nodes.selectedSearchResults).addClass('hiddend');
            }
        },
        cache: {
            getTaxonomy: function(key) {
                if ( typeof module._cache['taxonomy'][key] !== "undefined" ) {
                    return module._cache['taxonomy'][key];
                }

                return false;
            },
            setTaxonomy: function(key, value) {
                module._cache['taxonomy'][key] = value;
            },
            setName: function(item, taxonomy) {
                module._cache['names'][taxonomy] =
                    typeof module._cache['names'][taxonomy] == 'undefined' ? {} : module._cache['names'][taxonomy];
                module._cache['names'][taxonomy][item.id] = item.label;
            },
            getName: function(item, taxonomy) {
                if (
                    typeof module._cache['names'][taxonomy] == 'undefined' ||
                    typeof module._cache['names'][taxonomy][item] == 'undefined'
                ) {
                    return false;
                } else {
                    return module._cache['names'][taxonomy][item];
                }
            },
            setNames: function(items, taxonomy) {
                $.each(items, function(index, item){
                    module.cache.setName(item, taxonomy);
                });
            },
            getNames: function(items, taxonomy) {
                var foundAll = true;
                $.each(items, function(index, item){
                    var label = module.cache.getName(item.id, taxonomy);
                    if ( label === false ) {
                        foundAll = false;
                        return false;
                    } else {
                        item.label = label;
                    }
                });
                return foundAll ? items : foundAll;
            }
        }
    }

    controller.module.register(module);
});
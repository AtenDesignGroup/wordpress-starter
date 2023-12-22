jQuery(function($){
    var controller = WD_FrontFilters;

    var module = {
        'type': 'post_type',      // REQUIRED
        'single': true,         // REQUIRED
        'initialized': false,   // REQUIRED
        'data': {},             // REQUIRED - current data, loaded for editing
        'default': {},          // REQUIRED - default module data

        'nodes': {
            'container': '.post_type_filter_container',
            'right': '#post_type_sortable',
            'left': '#post_type_draggable',
            'attributes': '.post_type_filter_attributes',
            'otherAttributes': '.post_type_filter_other_attributes',
            'label': '.post_type_filter_attributes input[attr="label.text"]',
            'remove': '.post_type_filter_remove'
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
            $(module.nodes.right).find('li').each(function(i, item){
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
                $(module.nodes.right).on('sortupdate', function(){
                    $(module.nodes.left).find('li').removeClass("ui-state-disabled");
                    $(module.nodes.right).find('li').each(function () {
                        $(module.nodes.left).find("li[data-field='"+$(this).data('field')+"']").addClass("ui-state-disabled");
                    });
                });

                $(module.nodes.right).on('click', module.nodes.remove, function(){
                    $(this).closest('li[data-field]').remove();
                    $(module.nodes.right).trigger('sortupdate');
                });

                $(module.nodes.container).on('click', function(){
                   module.visual.removeValidation();
                });
            }
        },

        visual: {
            init: function() {
                $(module.nodes.left).find('li').draggable({
                    connectToSortable: module.nodes.right,
                    update: function (event, ui) {},
                    drag: function(e, ui) {
                        if ( typeof ui.helper != 'undefined')
                            $(ui.helper).css({ height: 84, width: 260, zIndex: 1 });   //drag dimensions
                    },
                    cancel: ".ui-state-disabled",
                    helper: "clone"
                }).disableSelection();

                $(module.nodes.right).sortable({
                    update: function (event, ui) {
                        var $item = $(ui.item);
                        $item.css({
                            "width": "",
                            "height": ""
                        });
                    }
                }).disableSelection();
            },

            load: function() {
                $(module.nodes.right).html('');
                $(module.nodes.left).find('li').removeClass("ui-state-disabled");
                if ( typeof module.data.items.length != 'undefined' && module.data.items.length > 0 ) {
                    $.each(module.data.items, function (i, item) {
                        var $n = $(module.nodes.left).find('li[data-field=' + item.field + ']').clone().appendTo($(module.nodes.right));
                        controller.helper.setAttributes($n.get(0), item);
                    });
                }
                controller.helper.setAttributes(module.nodes.attributes, module.data);
                controller.helper.setAttributes(module.nodes.otherAttributes, module.data);
                $(module.nodes.right).trigger('sortupdate');
                module.visual.removeValidation();
            },

            validate: function(data) {
                var valid = true, mn=module.nodes;

                if ( data.label.text.trim() == '' ) {
                    $(mn.label).addClass('wd_ff_invalid');
                    valid = false;
                }
                if ( data.items.length == 0 ) {
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
            }
        }
    }

    controller.module.register(module);
});
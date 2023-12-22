jQuery(function($){
    var controller = WD_FrontFilters;

    /**
     * This extra "function" step is required here to be able to do: new controller._tmp_date_func(..)
     * Reasons:
     *      1. $.extend(true, {}, controller.modules.get('date_from')) -> NOT WORKING
     *      2. JSON.parse(JSON.stringify(..)) -> NOT WORKING
     * Otherwise there is no way to use two separate instances of this same controller with params for "date_to" type
     *
     * @param type
     * @param nodes
     * @returns {{}} module
     * @private
     */
    controller.tmp.dateFromClass = function( type, nodes ) {

        var module = {
            'type': 'date_from',      // REQUIRED
            'single': true,         // REQUIRED
            'initialized': false,   // REQUIRED
            'data': {},             // REQUIRED - current data, loaded for editing
            'default': {},          // REQUIRED - default module data

            'nodes': {
                'container': '.date_from_filter_container',
                'attributes': '.date_from_filter_attributes',
                'otherAttributes': '.date_from_filter_other_attributes',
                'label': '.date_from_filter_container input[attr="label.text"]',
                'displayMode': '.date_from_filter_attributes select[attr=display_mode]',
                'relDateContainer': '.date_from_filter_rel_date',
                'relDateYear': '.date_from_filter_rel_date input[attr="relative_date.year"]',
                'relDateMonth': '.date_from_filter_rel_date input[attr="relative_date.month"]',
                'relDateDay': '.date_from_filter_rel_date input[attr="relative_date.day"]',
                'dateContainer': '.date_from_filter_date',
                'datePicker': '.date_from_filter_date input[attr="date"]'
            },

            // Automatically called
            init: function () {      // REQUIRED
                if (!this.initialized) {
                    this.default = $.extend(this.default, controller.modulesDefault[this.type].data);
                    this.visual.init();
                    this.events.init();
                    this.initialized = true;
                }
            },
            // Load the filter data to the modal window
            load: function (data) {  // REQUIRED
                data = data || {};
                this.data = $.extend({}, this.default.option, data);
                this.visual.load();
                console.log('Module - load:', this.type, data);
            },
            // Validate the filter data
            validate: function (data) {
                data = data || this.getData();
                return this.visual.validate(data);
            },
            // Return the manipulated filter data
            getData: function () {      // REQUIRED
                this.data.type = this.type;

                // all other generic attributes
                this.data = $.extend({},
                    this.data,
                    controller.helper.getAttributes(module.nodes.attributes),
                    controller.helper.getAttributes(module.nodes.otherAttributes)
                );
                return this.data;
            },

            events: {
                init: function () {
                    var mn = module.nodes;

                    $(mn.container).on('click', function(){
                        module.visual.removeValidation();
                    });
                }
            },

            visual: {
                init: function () {
                    $(module.nodes.datePicker).datepicker({
                        dateFormat: "yy-mm-dd",
                        changeMonth: true,
                        changeYear: true
                    });
                },

                load: function () {
                    controller.helper.setAttributes(module.nodes.attributes, module.data);
                    controller.helper.setAttributes(module.nodes.otherAttributes, module.data);
                    $(module.nodes.displayMode).trigger('change');
                    module.visual.removeValidation();
                },

                validate: function (data) {
                    var valid = true, mn=module.nodes;

                    if ( data.label.text.trim() == '' ) {
                        $(mn.label).addClass('wd_ff_invalid');
                        valid = false;
                    }
                    if ( data.display_mode == 'relative_date' ) {
                        if (data.relative_date.year == '') {
                            valid = false;
                            $(mn.relDateYear).addClass('wd_ff_invalid');
                        }
                        if (data.relative_date.month == '') {
                            valid = false;
                            $(mn.relDateMonth).addClass('wd_ff_invalid');
                        }
                        if (data.relative_date.day == '') {
                            valid = false;
                            $(mn.relDateDay).addClass('wd_ff_invalid');
                        }
                    }

                    if ( !valid && typeof $('body').get(0).scrollIntoView != 'undefined' ) {
                        $(mn.container).find('.wd_ff_invalid').get(0).scrollIntoView();
                    }

                    return valid;
                },

                removeValidation: function () {
                    $(module.nodes.container).find('*').removeClass('wd_ff_invalid');
                }
            }
        }

        if ( typeof type != 'undefined' ) {
            module.type = type;
        }

        if ( typeof nodes != 'undefined' ) {
            module.nodes = nodes;
        }

        return module;
    }

    controller.module.register(new controller.tmp.dateFromClass());
});
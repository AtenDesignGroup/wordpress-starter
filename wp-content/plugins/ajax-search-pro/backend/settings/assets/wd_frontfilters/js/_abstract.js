jQuery(function($){
    var controller = WD_FrontFilters;
    var module = {
        'type': 'abstract',     // REQUIRED
        'single': false,        // REQUIRED - only one of this module is allowed
        'initialized': false,   // REQUIRED
        'data': {               // REQUIRED - current data, loaded for editing
            'label': {
                'text': 'Filter label'  // REQUIRED at minimum
            }
        },
        'default': {},          // REQUIRED - default module data

        // Automatically called
        init: function() {      // REQUIRED
            if ( !this.initialized ) {
                module.default = $.extend(module.default, controller.modulesDefault[this.type].data);
                this.visual.init();
                this.initialized = true;
            }
        },
        // Load the filter data to the modal window
        load: function( data ) {  // REQUIRED
            data = data || {};
            this.data = $.extend(this.default, data);
        },
        // Validate the filter data
        validate: function( data ) {
            data = data || this.getData();
            return true;
        },
        // Return the maniplated filter data
        getData: function() {      // REQUIRED
            this.data.type = this.type; // set explicitly for front-end use
            return this.data;
        }
    }

    // Register with the controller WD_FrontFilters
    controller.module.register(module);
});
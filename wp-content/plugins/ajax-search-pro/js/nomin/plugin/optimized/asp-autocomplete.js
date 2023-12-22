(function($){
    "use strict";
    let functions = {
        autocomplete: function () {
            let $this = this,
                val = $this.n('text').val();

            if ($this.n('text').val() == '') {
                $this.n('textAutocomplete').val('');
                return;
            }
            let autocompleteVal = $this.n('textAutocomplete').val();

            if (autocompleteVal != '' && autocompleteVal.indexOf(val) == 0) {
                return;
            } else {
                $this.n('textAutocomplete').val('');
            }
            // noinspection JSUnresolvedVariable
            if ( $this.n('text').val().length >= $this.o.autocomplete.trigger_charcount ) {
                let data = {
                    action: 'ajaxsearchpro_autocomplete',
                    asid: $this.o.id,
                    sauto: $this.n('text').val(),
                    asp_inst_id: $this.o.rid,
                    options: $('form', $this.n('searchsettings')).serialize()
                };
                // noinspection JSUnresolvedVariable
                $this.postAuto = $.fn.ajax({
                    'url': ASP.ajaxurl,
                    'method': 'POST',
                    'data': data,
                    'success': function (response) {
                        if (response.length > 0) {
                            response = $('<textarea />').html(response).text();
                            response = response.replace(/^\s*[\r\n]/gm, "");
                            response = val + response.substr(val.length);
                        }
                        $this.n('textAutocomplete').val(response);
                        $this.fixAutocompleteScrollLeft();
                    }
                });
            }
        },

        // If only google source is used, this is much faster..
        autocompleteGoogleOnly: function () {
            let $this = this,
                val = $this.n('text').val();
            if ($this.n('text').val() == '') {
                $this.n('textAutocomplete').val('');
                return;
            }
            let autocompleteVal = $this.n('textAutocomplete').val();
            if (autocompleteVal != '' && autocompleteVal.indexOf(val) == 0) {
                return;
            } else {
                $this.n('textAutocomplete').val('');
            }

            let lang = $this.o.autocomplete.lang;
            ['wpml_lang', 'polylang_lang', 'qtranslate_lang'].forEach( function(v){
                if (
                    $('input[name="'+v+'"]', $this.n('searchsettings')).length > 0 &&
                    $('input[name="'+v+'"]', $this.n('searchsettings')).val().length > 1
                ) {
                    lang = $('input[name="' + v + '"]', $this.n('searchsettings')).val();
                }
            });
            // noinspection JSUnresolvedVariable
            if ( $this.n('text').val().length >= $this.o.autocomplete.trigger_charcount ) {
                $.fn.ajax({
                    url: 'https://clients1.google.com/complete/search',
                    cors: 'no-cors',
                    data: {
                        q: val,
                        hl: lang,
                        nolabels: 't',
                        client: 'hp',
                        ds: ''
                    },
                    success: function (data) {
                        if (data[1].length > 0) {
                            let response = data[1][0][0].replace(/(<([^>]+)>)/ig, "");
                            response = $('<textarea />').html(response).text();
                            response = response.substr(val.length);
                            $this.n('textAutocomplete').val(val + response);
                            $this.fixAutocompleteScrollLeft();
                        }
                    }
                });
            }
        },

        fixAutocompleteScrollLeft: function() {
            this.n('textAutocomplete').get(0).scrollLeft = this.n('text').get(0).scrollLeft;
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initAutocompleteEvent: function () {
            let $this = this,
                tt;
            if (
                ($this.o.autocomplete.enabled == 1 && !helpers.isMobile()) ||
                ($this.o.autocomplete.mobile == 1 && helpers.isMobile())
            ) {
                $this.n('text').on('keyup', function (e) {
                    $this.keycode =  e.keyCode || e.which;
                    $this.ktype = e.type;

                    let thekey = 39;
                    // Lets change the keykode if the direction is rtl
                    if ($('body').hasClass('rtl'))
                        thekey = 37;
                    if ($this.keycode == thekey && $this.n('textAutocomplete').val() != "") {
                        e.preventDefault();
                        $this.n('text').val($this.n('textAutocomplete').val());
                        if ( $this.o.trigger.type != 0 ) {
                            $this.searchAbort();
                            $this.search();
                        }
                    } else {
                        clearTimeout(tt);
                        if ($this.postAuto != null) $this.postAuto.abort();
                        //This delay should be greater than the post-result delay..
                        //..so the
                        // noinspection JSUnresolvedVariable
                        if ($this.o.autocomplete.googleOnly == 1) {
                            $this.autocompleteGoogleOnly();
                        } else {
                            // noinspection JSUnresolvedVariable
                            tt = setTimeout(function () {
                                $this.autocomplete();
                                tt = null;
                            }, $this.o.trigger.autocomplete_delay);
                        }
                    }
                });
                $this.n('text').on('keyup mouseup input blur select', function(){
                   $this.fixAutocompleteScrollLeft();
                });
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);
(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        showSettings: function ( animations ) {
            let $this = this;

            $this.initSettings?.();

            animations =  typeof  animations == 'undefined' ? true :  animations;
            $this.n('s').trigger("asp_settings_show", [$this.o.id, $this.o.iid], true, true);

            if ( !animations ) {
                $this.n('searchsettings').css({
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': 1
                });
            } else {
                $this.n('searchsettings').css($this.settAnim.showCSS);
                $this.n('searchsettings').removeClass($this.settAnim.hideClass).addClass($this.settAnim.showClass);
            }

            // noinspection JSUnresolvedVariable
            if ( $this.o.fss_layout == "masonry" && $this.sIsotope == null && !(helpers.isMobile() && helpers.detectIOS()) ) {
                if (typeof rpp_isotope !== 'undefined') {
                    setTimeout(function () {
                        let id = $this.n('searchsettings').attr('id');
                        $this.n('searchsettings').css("width", "100%");
                        // noinspection JSPotentiallyInvalidConstructorUsage
                        $this.sIsotope = new rpp_isotope("#" + id + " form", {
                            isOriginLeft: !$('body').hasClass('rtl'),
                            itemSelector: 'fieldset',
                            layoutMode: 'masonry',
                            transitionDuration: 0,
                            masonry: {
                                columnWidth: $this.n('searchsettings').find('fieldset:not(.hiddend)').outerWidth()
                            }
                        });
                    }, 20);
                } else {
                    // Isotope is not included within the scripts, alert the user!
                    return false;
                }
            }

            if (typeof $this.select2jQuery != 'undefined') {
                $this.select2jQuery($this.n('searchsettings').get(0)).find('.asp_gochosen,.asp_goselect2').trigger("change.asp_select2");
            }

            $this.n('prosettings').data('opened', 1);

            $this.fixSettingsPosition(true);
            $this.fixSettingsAccessibility();
        },
        hideSettings: function () {
            let $this = this;

            $this.initSettings?.();

            $this.n('s').trigger("asp_settings_hide", [$this.o.id, $this.o.iid], true, true);

            $this.n('searchsettings').removeClass($this.settAnim.showClass).addClass($this.settAnim.hideClass);
            setTimeout(function(){
                $this.n('searchsettings').css($this.settAnim.hideCSS);
            }, $this.settAnim.duration);

            $this.n('prosettings').data('opened', 0);

            if ( $this.sIsotope != null ) {
                setTimeout(function () {
                    $this.sIsotope.destroy();
                    $this.sIsotope = null;
                }, $this.settAnim.duration);
            }

            if (typeof $this.select2jQuery != 'undefined' && typeof $this.select2jQuery.fn.asp_select2 != 'undefined') {
                $this.select2jQuery($this.n('searchsettings').get(0)).find('.asp_gochosen,.asp_goselect2').asp_select2('close');
            }

            $this.hideArrowBox?.();
        },
        reportSettingsValidity: function() {
            let $this = this,
                valid = true;

            // Automatically valid, when settings can be closed, or are hidden
            if ( $this.n('searchsettings').css('visibility') == 'hidden' )
                return true;

            $this.n('searchsettings').find('fieldset.asp_required').each(function(){
                let $_this = $(this),
                    fieldset_valid = true;
                // Text input
                $_this.find('input[type=text]:not(.asp_select2-search__field)').each(function(){
                    if ( $(this).val() == '' ) {
                        fieldset_valid = false;
                    }
                });
                // Select drop downs
                $_this.find('select').each(function(){
                    if (
                        $(this).val() == null || $(this).val() == '' ||
                        ( $(this).closest('fieldset').is('.asp_filter_tax, .asp_filter_content_type') && $(this).val() == '-1')
                    ) {
                        fieldset_valid = false;
                    }
                });
                // Check for checkboxes
                if ( $_this.find('input[type=checkbox]').length > 0 ) {
                    // Check if all of them are checked
                    if ( $_this.find('input[type=checkbox]:checked').length === 0 ) {
                        fieldset_valid = false;
                    } else if (
                        $_this.find('input[type=checkbox]:checked').length === 1 &&
                        $_this.find('input[type=checkbox]:checked').val() === ''
                    ) {
                        // Select all checkbox
                        fieldset_valid = false;
                    }
                }
                // Check for checkboxes
                if ( $_this.find('input[type=radio]').length > 0 ) {
                    // Check if all of them are checked
                    if ( $_this.find('input[type=radio]:checked').length === 0 ) {
                        fieldset_valid = false;
                    }
                    if ( fieldset_valid ) {
                        $_this.find('input[type=radio]').each(function () {
                            if (
                                $(this).prop('checked') &&
                                (
                                    $(this).val() == '' ||
                                    (
                                        $(this).closest('fieldset').is('.asp_filter_tax, .asp_filter_content_type') &&
                                        $(this).val() == '-1')
                                    )
                            ) {
                                fieldset_valid = false;
                            }
                        });
                    }
                }

                if ( !fieldset_valid ) {
                    $_this.addClass('asp-invalid');
                    valid = false;
                } else {
                    $_this.removeClass('asp-invalid');
                }
            });

            if ( !valid ) {
                $this.n('searchsettings').find('button.asp_s_btn').prop('disabled', true);
            } {
                $this.n('searchsettings').find('button.asp_s_btn').prop('disabled', false);
            }

            return valid;
        },

        showArrowBox: function(element, text) {
            let $this = this,
                offsetTop, left,
                $body = $('body'),
                $box = $body.find('.asp_arrow_box');
            if ( $box.length === 0 ) {
                $body.append( "<div class='asp_arrow_box'></div>" );
                $box = $body.find('.asp_arrow_box');
                $box.on('mouseout', function(){
                    $this.hideArrowBox?.();
                });
            }

            // getBoundingClientRect() is not giving correct values, use different method
            let space = $(element).offset().top - window.scrollY,
                fixedp = false,
                n = element;

            while (n) {
                n = n.parentElement;
                if ( n != null && window.getComputedStyle(n).position == 'fixed' ) {
                    fixedp = true;
                    break;
                }
            }

            if ( fixedp ) {
                $box.css('position', 'fixed');
                offsetTop = 0;
            } else {
                $box.css('position', 'absolute');
                offsetTop = window.scrollY;
            }
            $box.html(text);
            $box.css('display', 'block');

            // Count after text is added
            left = (element.getBoundingClientRect().left + ($(element).outerWidth() / 2) - ($box.outerWidth() / 2) ) + 'px';

            if ( space > 100 ) {
                $box.removeClass('asp_arrow_box_bottom');
                $box.css({
                    top: offsetTop + element.getBoundingClientRect().top - $box.outerHeight() - 4 + 'px',
                    left: left
                });
            } else {
                $box.addClass('asp_arrow_box_bottom');
                $box.css({
                    top: offsetTop + element.getBoundingClientRect().bottom + 4 + 'px',
                    left: left
                });
            }
        },

        hideArrowBox: function() {
            $('body').find('.asp_arrow_box').css('display', 'none');
        },

        showNextInvalidFacetMessage: function() {
            let $this = this;
            if ( $this.n('searchsettings').find('.asp-invalid').length > 0 ) {
                $this.showArrowBox(
                    $this.n('searchsettings').find('.asp-invalid').first().get(0),
                    $this.n('searchsettings').find('.asp-invalid').first().data('asp_invalid_msg')
                );
            }
        },

        scrollToNextInvalidFacetMessage: function() {
            let $this = this;
            if ( $this.n('searchsettings').find('.asp-invalid').length > 0 ) {
                let $n = $this.n('searchsettings').find('.asp-invalid').first();
                if ( !$n.inViewPort(0) ) {
                    if ( typeof $n.get(0).scrollIntoView != "undefined" ) {
                        $n.get(0).scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
                    } else {
                        let stop = $n.offset().top - 20,
                            $adminbar = $("#wpadminbar");
                        // noinspection JSJQueryEfficiency
                        if ( $adminbar.length > 0 )
                            stop -= $adminbar.height();
                        stop = stop < 0 ? 0 : stop;
                        window.scrollTo({top: stop, behavior:"smooth"});
                    }
                }
            }
        },

        settingsCheckboxToggle: function( $node, checkState ) {
            let $this = this;
            checkState = typeof checkState == 'undefined' ? true : checkState;
            let $parent = $node,
                $checkbox = $node.find('input[type="checkbox"]'),
                lvl = parseInt($node.data("lvl")) + 1,
                i = 0;
            while (true) {
                $parent = $parent.next();
                if ( $parent.length > 0 &&
                    typeof $parent.data("lvl") != "undefined" &&
                    parseInt($parent.data("lvl")) >= lvl
                ) {
                    if ( checkState )
                        $parent.find('input[type="checkbox"]').prop("checked", $checkbox.prop("checked"));
                    // noinspection JSUnresolvedVariable
                    if ( $this.o.settings.hideChildren ) {
                        if ( $checkbox.prop("checked") ) {
                            $parent.removeClass("hiddend");
                        } else {
                            $parent.addClass("hiddend");
                        }
                    }
                }
                else
                    break;
                i++;
                if ( i > 400 ) break; // safety first
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initDatePicker: function() {
            let $this = this;
            WPD.intervalUntilExecute(function(_$){
                function onSelectEvent(dateText, inst, _this, nochange, nochage) {
                    let obj;
                    if ( _this != null ) {
                        obj = _$(_this);
                    } else {
                        obj = _$("#" + inst.id);
                    }

                    let prevValue = _$(".asp_datepicker_hidden", _$(obj).parent()).val(),
                        newValue = '';

                    if ( obj.datepicker("getDate") == null ) {
                        _$(".asp_datepicker_hidden", _$(obj).parent()).val('');
                    } else {
                        // noinspection RegExpRedundantEscape
                        let d = String( obj.datepicker("getDate") ),
                            date = new Date( d.match(/(.*?)00\:/)[1].trim() ),
                            year = String( date.getFullYear() ),
                            month = ("0" + (date.getMonth() + 1)).slice(-2),
                            day = ("0" + String(date.getDate()) ).slice(-2);
                        newValue = year +'-'+ month +'-'+ day;
                        _$(".asp_datepicker_hidden", _$(obj).parent()).val(newValue);
                    }

                    // Trigger change event. $ scope is used ON PURPOSE
                    // ..otherwise scoped version would not trigger!
                    if ( (typeof nochage == "undefined" || nochange == null) && newValue != prevValue )
                        $(obj.get(0)).trigger('change');
                }

                _$(".asp_datepicker, .asp_datepicker_field", $this.n('searchsettings').get(0)).each(function(){
                    let format = _$(".asp_datepicker_format", _$(this).parent()).val(),
                        _this = this,
                        origValue = _$(this).val();
                    _$(this).removeClass('hasDatepicker'); // Cloned versions can already have the date picker class
                    _$(this).datepicker({
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: 'yy-mm-dd',
                        onSelect: onSelectEvent,
                        beforeShow: function() {
                            _$('#ui-datepicker-div').addClass("asp-ui");
                        }
                    });
                    // Set to empty date if the field is empty
                    if ( origValue == "") {
                        _$(this).datepicker("setDate", "");
                    } else {
                        _$(this).datepicker("setDate", origValue);
                    }
                    _$(this).datepicker( "option", "dateFormat", format );
                    // Call the select event to refresh the date pick value
                    onSelectEvent(null, null, _this, true);

                    // Assign the no change select event to a new triggerable event
                    _$(this).on('selectnochange', function(){
                        onSelectEvent(null, null, _this, true);
                    });

                    // When the user deletes the value, empty the hidden field as well
                    _$(this).on('keyup', function(){
                        if ( _$(_this).datepicker("getDate") == null ) {
                            _$(".asp_datepicker_hidden", _$(_this).parent()).val('');
                        }
                        _$(_this).datepicker("hide");
                    });
                });
                // IOS Safari backwards button reinit
                if ( helpers.isMobile() && helpers.detectIOS() ) {
                    _$(window).on('pageshow', function (e) {
                        if (e.originalEvent.persisted) {
                            setTimeout(function () {
                                _$(".asp_datepicker, .asp_datepicker_field", $this.n('searchsettings').get(0)).each(function () {
                                    let format = _$(this).datepicker("option", 'dateFormat' );
                                    _$(this).datepicker("option", 'dateFormat', 'yy-mm-dd');
                                    _$(this).datepicker("setDate", _$(this).next('.asp_datepicker_hidden').val() );
                                    _$(this).datepicker("option", 'dateFormat', format);
                                });
                            }, 100);
                        }
                    });
                }
            }, function(){
                return helpers.whichjQuery('datepicker');
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initFacetEvents: function() {
            let $this = this,
                gtagTimer = null,
                inputCorrectionTimer = null;

            $('.asp_custom_f input[type=text]:not(.asp_select2-search__field):not(.asp_datepicker_field):not(.asp_datepicker)', $this.n('searchsettings')).on('input', function(e) {
                let code = e.keyCode || e.which,
                    _this = this;
                $this.ktype = e.type;
                if ( code == 13 ) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
                if ( $(this).data('asp-type') == 'number' ) {
                    if ( this.value != '' ) {
                        let inputVal = this.value.replaceAll($(this).data('asp-tsep'), '');
                        let correctedVal = helpers.inputToFloat(this.value);
                        let _this = this;
                        _this.value = correctedVal;
                        correctedVal = correctedVal < parseFloat($(this).data('asp-min')) ? $(this).data('asp-min') : correctedVal;
                        correctedVal = correctedVal > parseFloat($(this).data('asp-max')) ? $(this).data('asp-max') : correctedVal;
                        clearTimeout(inputCorrectionTimer);
                        inputCorrectionTimer = setTimeout(function(){
                            _this.value = helpers.addThousandSeparators(correctedVal, $(_this).data('asp-tsep'));
                        }, 400);
                        if (correctedVal.toString() !== inputVal) {
                            return false;
                        }
                    }
                }
                clearTimeout(gtagTimer);
                gtagTimer = setTimeout(function(){
                    $this.gaEvent?.('facet_change', {
                        'option_label': $(_this).closest('fieldset').find('legend').text(),
                        'option_value': $(_this).val()
                    });
                }, 1400);
                $this.n('searchsettings').find('input[name=filters_changed]').val(1);
                $this.setFilterStateInput(65);
                if ( $this.o.trigger.facet != 0 )
                    $this.searchWithCheck(240);
            });

            // Add the thousand separators
            $this.n('searchsettings').find('.asp-number-range[data-asp-tsep]').forEach(function(){
                this.value = helpers.addThousandSeparators(this.value, $(this).data('asp-tsep'));
            });

            // This needs to be here, submit prevention on input text fields is still needed
            if ($this.o.trigger.facet == 0) return;

            // Dropdown
            $('select', $this.n('searchsettings')).on('change slidechange', function(e){
                $this.ktype = e.type;
                $this.n('searchsettings').find('input[name=filters_changed]').val(1);
                $this.gaEvent?.('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).find('option:checked').get().map(function(item){return item.text;}).join()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
                if ( $this.sIsotope != null ) {
                    $this.sIsotope.arrange();
                }
            });

            // Any other
            //$('input[type!=checkbox][type!=text][type!=radio]', $this.n('searchsettings')).on('change slidechange', function(){
            $('input:not([type=checkbox]):not([type=text]):not([type=radio])', $this.n('searchsettings')).on('change slidechange', function(e){
                $this.ktype = e.type;
                $this.n('searchsettings').find('input[name=filters_changed]').val(1);
                $this.gaEvent?.('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).val()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });

            // Radio
            $('input[type=radio]', $this.n('searchsettings')).on('change slidechange', function(e){
                $this.ktype = e.type;
                $this.n('searchsettings').find('input[name=filters_changed]').val(1);
                $this.gaEvent?.('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).closest('label').text()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });

            $('input[type=checkbox]', $this.n('searchsettings')).on('asp_chbx_change', function(e){
                $this.ktype = e.type;
                $this.n('searchsettings').find('input[name=filters_changed]').val(1);
                $this.gaEvent?.('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).closest('.asp_option').find('.asp_option_label').text() + ($(this).prop('checked') ? '(checked)' : '(unchecked)')
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });
            $('input.asp_datepicker, input.asp_datepicker_field', $this.n('searchsettings')).on('change', function(e){
                $this.ktype = e.type;
                $this.n('searchsettings').find('input[name=filters_changed]').val(1);
                $this.gaEvent?.('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).val()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });
            $('div[id*="-handles"]', $this.n('searchsettings')).each(function(e){
                $this.ktype = e.type;
                if ( typeof this.noUiSlider != 'undefined') {
                    this.noUiSlider.on('change', function(values) {
                        let target = typeof this.target != 'undefined' ? this.target : this;
                        $this.gaEvent?.('facet_change', {
                            'option_label': $(target).closest('fieldset').find('legend').text(),
                            'option_value': values
                        });
                        $this.n('searchsettings').find('input[name=filters_changed]').val(1);
                        // Gtag analytics is handled on the update event, not here
                        $this.setFilterStateInput(65);
                        $this.searchWithCheck(80);
                    });
                }
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let functions = {
        initNoUIEvents: function () {
            let $this = this,
                $sett = $this.nodes.searchsettings,
                slider;

            $sett.find("div[class*=noui-slider-json]").each(function(el, index){

                let jsonData = $(this).data("aspnoui");
                if (typeof jsonData === "undefined") return false;

                jsonData = WPD.Base64.decode(jsonData);
                if (typeof jsonData === "undefined" || jsonData == "") return false;

                let args = JSON.parse(jsonData);
                Object.keys(args.links).forEach(function(k){
                    args.links[k].target = '#' + $sett.get(0).id + ' ' + args.links[k].target;
                });
                if ( $(args.node, $sett).length > 0 ) {
                    slider = $(args.node, $sett).get(0);
                    // Initialize the main
                    let $handles = $(el).parent().find('.asp_slider_hidden');
                    if ($handles.length > 1) {
                        args.main.start = [$handles.first().val(), $handles.last().val()];
                    } else {
                        args.main.start = [$handles.first().val()];
                    }
                    if (typeof noUiSlider !== 'undefined') {
                        if (typeof slider.noUiSlider != 'undefined') {
                            slider.noUiSlider.destroy();
                        }
                        slider.innerHTML = '';
                        noUiSlider.create(slider, args.main);
                    } else {
                        // NoUiSlider is not included within the scripts, alert the user!
                        return false;
                    }

                    $this.noUiSliders[index] = slider;

                    slider.noUiSlider.on('update', function (values, handle) {
                        let value = values[handle];
                        if (handle) { // true when 1, if upper
                            // Params: el, i, arr
                            args.links.forEach(function (el) {
                                let wn = wNumb(el.wNumb);
                                if (el.handle == "upper") {
                                    if ($(el.target, $sett).is('input'))
                                        $(el.target, $sett).val(value);
                                    else
                                        $(el.target, $sett).html(wn.to(parseFloat(value)));
                                }
                                $(args.node, $sett).on('slide', function (e) {
                                    e.preventDefault();
                                });
                            });
                        } else {        // 0, lower
                            // Params: el, i, arr
                            args.links.forEach(function (el) {
                                let wn = wNumb(el.wNumb);
                                if (el.handle == "lower") {
                                    if ($(el.target, $sett).is('input'))
                                        $(el.target, $sett).val(value);
                                    else
                                        $(el.target, $sett).html(wn.to(parseFloat(value)));
                                }
                                $(args.node, $sett).on('slide', function (e) {
                                    e.preventDefault();
                                });
                            });
                        }
                    });
                }
            });

        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initSettingsSwitchEvents: function() {
            let $this = this;
            $this.n('prosettings').on("click", function () {
                if ($this.n('prosettings').data('opened') == 0) {
                    $this.showSettings?.();
                } else {
                    $this.hideSettings?.();
                }
            });

            // noinspection JSUnresolvedVariable
            if ( helpers.isMobile() ) {
                // noinspection JSUnresolvedVariable
                if (
                    $this.o.mobile.force_sett_state == "open" ||
                    ( $this.o.mobile.force_sett_state == "none" && $this.o.settingsVisible == 1 )
                ) {
                    $this.showSettings?.(false);
                }
            } else {
                // noinspection JSUnresolvedVariable
                if ($this.o.settingsVisible == 1) {
                    $this.showSettings?.(false);
                }
            }
        },

        initSettingsEvents: function() {
            let $this = this, t;
            let formDataHandler = function(){
                // Let everything initialize (datepicker etc..), then get the form data
                if ( typeof $this.originalFormData === 'undefined' ) {
                    $this.originalFormData = helpers.formData($('form', $this.n('searchsettings')));
                }
                $this.n('searchsettings').off('mousedown touchstart mouseover', formDataHandler);
            };
            $this.n('searchsettings').on('mousedown touchstart mouseover', formDataHandler);

            let handler = function (e) {
                if ( $(e.target).closest('.asp_w').length == 0 ) {
                    if (
                        $this.att('blocking') == false &&
                        !$this.dragging &&
                        $(e.target).closest('.ui-datepicker').length == 0 &&
                        $(e.target).closest('.noUi-handle').length == 0 &&
                        $(e.target).closest('.asp_select2').length == 0 &&
                        $(e.target).closest('.asp_select2-container').length == 0
                    ) {
                        $this.hideSettings?.();
                    }
                }
            };
            $this.documentEventHandlers.push({
                'node': document,
                'event': $this.clickTouchend,
                'handler': handler
            });
            $(document).on($this.clickTouchend, handler);

            // Note if the settings have changed
            $this.n('searchsettings').on('click', function(){
                $this.settingsChanged = true;
            });

            $this.n('searchsettings').on($this.clickTouchend, function (e) {
                if ( !$this.dragging ) {
                    $this.updateHref();
                }

                /**
                 * Stop propagation on settings clicks, except the noUiSlider handler event.
                 * If noUiSlider event propagation is stopped, then the: set, end, change events does not fire properly.
                 */
                if ( typeof e.target != 'undefined' && !$(e.target).hasClass('noUi-handle') ) {
                    e.stopImmediatePropagation();
                } else {
                    // For noUI case, still cancel if this is a click (desktop device)
                    if ( e.type == 'click' )
                        e.stopImmediatePropagation();
                }
            });

            // Category level automatic checking and hiding
            $('.asp_option_cat input[type="checkbox"]', $this.n('searchsettings')).on('asp_chbx_change', function(){
                $this.settingsCheckboxToggle( $(this).closest('.asp_option_cat') );
            });
            // Init the hide settings
            $('.asp_option_cat', $this.n('searchsettings')).each(function(el){
                $this.settingsCheckboxToggle( $(el), false );
            });


            // Emulate click on checkbox on the whole option
            //$('div.asp_option', $this.nodes.searchsettings).on('mouseup touchend', function(e){
            $('div.asp_option', $this.n('searchsettings')).on($this.mouseupTouchend, function(e){
                e.preventDefault(); // Stop firing twice on mouseup and touchend on mobile devices
                e.stopImmediatePropagation();

                if ( $this.dragging ) {
                    return false;
                }
                $(this).find('input[type="checkbox"]').prop("checked", !$(this).find('input[type="checkbox"]').prop("checked"));
                // Trigger a custom change event, for max compatibility
                // .. the original change is buggy for some installations.
                clearTimeout(t);
                let _this = this;
                t = setTimeout(function() {
                    $(_this).find('input[type="checkbox"]').trigger('asp_chbx_change');
                }, 50);
            });

            // Tabbed element selection with enter or spacebar
            $('div.asp_option', $this.n('searchsettings')).on('keyup', function(e){
                e.preventDefault();
                let keycode =  e.keyCode || e.which;
                if ( keycode == 13 || keycode == 32 ) {
                    $(this).trigger('mouseup');
                }
            });

            // Change the state of the choose any option if all of them are de-selected
            $('fieldset.asp_checkboxes_filter_box', $this.n('searchsettings')).each(function(){
                let all_unchecked = true;
                $(this).find('.asp_option:not(.asp_option_selectall) input[type="checkbox"]').each(function(){
                    if ($(this).prop('checked') == true) {
                        all_unchecked = false;
                        return false;
                    }
                });
                if ( all_unchecked ) {
                    $(this).find('.asp_option_selectall input[type="checkbox"]').prop('checked', false).removeAttr('data-origvalue');
                }
            });

            // Mark last visible options
            $('fieldset' ,$this.n('searchsettings')).each(function(){
                $(this).find('.asp_option:not(.hiddend)').last().addClass("asp-o-last");
            });

            // Select all checkboxes
            $('.asp_option_cat input[type="checkbox"], .asp_option_cff input[type="checkbox"]', $this.n('searchsettings')).on('asp_chbx_change', function(){
                let className = $(this).data("targetclass");
                if ( typeof className == 'string' && className != '')
                    $("input." + className, $this.n('searchsettings')).prop("checked", $(this).prop("checked"));
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        /**
         * This function should be called on-demand to init the settings. Do not call on init, only when needed.
         */
        initSettings: function() {
            if ( !this.settingsInitialized ) {
                this.loadASPFonts?.();
                this.initSettingsBox?.();
                this.initSettingsEvents?.();
                this.initButtonEvents?.();
                this.initNoUIEvents?.();
                this.initDatePicker?.();
                this.initSelect2?.();
                this.initFacetEvents?.();
            }
        },
        initSettingsBox: function() {
            let $this = this;
            let appendSettingsTo = function($el) {
                let old = $this.n('searchsettings').get(0);
                $this.nodes.searchsettings = $this.nodes.searchsettings.clone();
                $el.append($this.nodes.searchsettings);


                $(old).find('*[id]').forEach(function(el){
                    if ( el.id.indexOf('__original__') < 0 ) {
                        el.id = '__original__' + el.id;
                    }
                });
                $this.n('searchsettings').find('*[id]').forEach(function(el){
                    if ( el.id.indexOf('__original__') > -1 ) {
                        el.id =  el.id.replace('__original__', '');
                    }
                });
            }
            let makeSetingsBlock = function() {
                $this.n('searchsettings').attr(
                    "id",
                    $this.n('searchsettings').attr("id").replace('prosettings', 'probsettings')
                );
                $this.n('searchsettings').removeClass('asp_s asp_s_' + $this.o.id + ' asp_s_' + $this.o.rid)
                    .addClass('asp_sb asp_sb_' + $this.o.id + ' asp_sb_' + $this.o.rid);
                $this.dynamicAtts['blocking'] = true;
            }
            let makeSetingsHover = function() {
                $this.n('searchsettings').attr(
                    "id",
                    $this.n('searchsettings').attr("id").replace('probsettings', 'prosettings')
                );
                $this.n('searchsettings').removeClass('asp_sb asp_sb_' + $this.o.id + ' asp_sb_' + $this.o.rid)
                    .addClass('asp_s asp_s_' + $this.o.id + ' asp_s_' + $this.o.rid);
                $this.dynamicAtts['blocking'] = false;
            }


            // Calculates the settings animation attributes
            $this.initSettingsAnimations?.();

            // noinspection JSUnresolvedVariable
            if (
                ( $this.o.compact.enabled == 1 && $this.o.compact.position == 'fixed'  ) ||
                ( helpers.isMobile() && $this.o.mobile.force_sett_hover == 1 )
            ) {
                makeSetingsHover();
                appendSettingsTo($('body'));

                $this.n('searchsettings').css({
                    'position': 'absolute'
                });
                $this.dynamicAtts['blocking'] = false;
            } else {
                if ( $this.n('settingsAppend').length > 0 ) {
                    // There is already a results box there
                    if ( $this.n('settingsAppend').find('.asp_ss_' + $this.o.id).length > 0 ) {
                        $this.nodes.searchsettings = $this.nodes.settingsAppend.find('.asp_ss_' + $this.o.id);
                        if ( typeof $this.nodes.searchsettings.get(0).referenced !== 'undefined' ) {
                            ++$this.nodes.searchsettings.get(0).referenced;
                        } else {
                            $this.nodes.searchsettings.get(0).referenced = 1;
                        }
                    } else {
                        if ( $this.att('blocking') == false ) {
                            makeSetingsBlock();
                        }
                        appendSettingsTo($this.nodes.settingsAppend);
                    }

                } else if ($this.att('blocking') == false) {
                    appendSettingsTo($('body'));
                }
            }
            $this.n('searchsettings').get(0).id = $this.n('searchsettings').get(0).id.replace('__original__', '');
            $this.detectAndFixFixedPositioning();

            $this.settingsInitialized = true;
        },
        initSettingsAnimations: function() {
            let $this = this;
            $this.settAnim = {
                "showClass": "",
                "showCSS": {
                    "visibility": "visible",
                    "display": "block",
                    "opacity": 1,
                    "animation-duration": $this.animOptions.settings.dur + 'ms'
                },
                "hideClass": "",
                "hideCSS": {
                    "visibility": "hidden",
                    "opacity": 0,
                    "display": "none"
                },
                "duration": $this.animOptions.settings.dur + 'ms'
            };

            if ($this.animOptions.settings.anim == "fade") {
                $this.settAnim.showClass = "asp_an_fadeIn";
                $this.settAnim.hideClass = "asp_an_fadeOut";
            }

            if ($this.animOptions.settings.anim == "fadedrop" &&
                !$this.att('blocking') ) {
                $this.settAnim.showClass = "asp_an_fadeInDrop";
                $this.settAnim.hideClass = "asp_an_fadeOutDrop";
            } else if ( $this.animOptions.settings.anim == "fadedrop" ) {
                // If does not support transitio, or it is blocking layout
                // .. fall back to fade
                $this.settAnim.showClass = "asp_an_fadeIn";
                $this.settAnim.hideClass = "asp_an_fadeOut";
            }

            $this.n('searchsettings').css({
                "-webkit-animation-duration": $this.settAnim.duration + "ms",
                "animation-duration": $this.settAnim.duration + "ms"
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);
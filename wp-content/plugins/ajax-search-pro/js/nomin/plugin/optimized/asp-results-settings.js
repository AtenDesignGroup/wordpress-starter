(function($){
    let functions = {
        showSettings: function () {
            let $this = this;

            $this.n.c.trigger("asp_settings_show", [$this.o.id, $this.o.iid]);

            $this.n.searchsettings.css($this.settAnim.showCSS);
            $this.n.searchsettings.removeClass($this.settAnim.hideClass).addClass($this.settAnim.showClass);

            if ($this.settScroll == null && ($this.is_scroll) ) {
                $this.settScroll = [];
                $('.asp_sett_scroll', $this.n.searchsettings).each(function(o,i){
                    let _this = this;
                    // Small delay to fix a rendering issue
                    setTimeout(function(){
                        // noinspection JSUnresolvedFunction,JSUnresolvedVariable
                        $this.settScroll[i] = new asp_SimpleBar($(_this).get(0), {
                            direction: $('body').hasClass('rtl') ? 'rtl' : 'ltr',
                            autoHide: $this.o.scrollBar.settings.autoHide
                        });
                    }, 15);
                });
            }

            // noinspection JSUnresolvedVariable
            if ( $this.o.fss_layout == "masonry" && $this.sIsotope == null ) {
                if (typeof rpp_isotope !== 'undefined') {
                    setTimeout(function () {
                        let id = $this.n.searchsettings.attr('id');
                        $this.n.searchsettings.css("width", "100%");
                        // noinspection JSPotentiallyInvalidConstructorUsage
                        $this.sIsotope = new rpp_isotope("#" + id + " form", {
                            isOriginLeft: !$('body').hasClass('rtl'),
                            itemSelector: 'fieldset',
                            layoutMode: 'masonry',
                            transitionDuration: 0,
                            masonry: {
                                columnWidth: $this.n.searchsettings.find('fieldset').outerWidth()
                            }
                        });
                    }, 20);
                } else {
                    // Isotope is not included within the scripts, alert the user!
                    return false;
                }
            }

            if (typeof $this.select2jQuery != 'undefined') {
                $this.select2jQuery($this.n.searchsettings.get(0)).find('.asp_gochosen,.asp_goselect2').trigger("change.asp_select2");
            }

            $this.n.prosettings.data('opened', 1);

            $this.fixSettingsPosition(true);
            $this.fixAccessibility();
        },
        hideSettings: function () {
            let $this = this;

            $this.n.c.trigger("asp_settings_hide", [$this.o.id, $this.o.iid]);

            $this.n.searchsettings.removeClass($this.settAnim.showClass).addClass($this.settAnim.hideClass);
            setTimeout(function(){
                $this.n.searchsettings.css($this.settAnim.hideCSS);
            }, $this.settAnim.duration);

            $this.n.prosettings.data('opened', 0);

            if ( $this.sIsotope != null ) {
                setTimeout(function () {
                    $this.sIsotope.destroy();
                    $this.sIsotope = null;
                }, $this.settAnim.duration);
            }

            if (typeof $this.select2jQuery != 'undefined') {
                $this.select2jQuery($this.n.searchsettings.get(0)).find('.asp_gochosen,.asp_goselect2').asp_select2('close');
            }

            $this.hideArrowBox();
        },
        reportSettingsValidity: function() {
            let $this = this,
                valid = true;

            // Automatically valid, when settings can be closed, or are hidden
            if ( $this.n.searchsettings.css('visibility') == 'hidden' )
                return true;

            $this.n.searchsettings.find('fieldset.asp_required').each(function(){
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
                    if ( !$_this.find('input[type=checkbox]').is(':checked') ) {
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
                    if ( !$_this.find('input[type=radio]').is(':checked') ) {
                        fieldset_valid = false;
                    }
                    if ( fieldset_valid ) {
                        $_this.find('input[type=radio]').each(function () {
                            if (
                                $(this).is(':checked') &&
                                ( $(this).val() == '' || ( $(this).closest('fieldset').is('.asp_filter_tax, .asp_filter_content_type') && $(this).val() == '-1') )
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
                $this.n.searchsettings.find('button.asp_s_btn').prop('disabled', true);
            } {
                $this.n.searchsettings.find('button.asp_s_btn').prop('disabled', false);
            }

            return valid;
        },

        showArrowBox: function(element, text) {
            let $this = this,
                offsetTop, left, $body = $('body');
            if ( $body.find('.asp_arrow_box').length === 0 ) {
                $body.append( "<div class='asp_arrow_box'></div>" );
                $body.find('.asp_arrow_box').on('mouseout', function(){
                    $this.hideArrowBox();
                });
            }

            // getBoundingClientRect() is not giving correct values, use different method
            let space = $(element).offset().top - window.scrollY,
                fixedp = false,
                n = element,
                $box = $body.find('.asp_arrow_box');

            while (n) {
                n = n.parentNode;
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
            $box.css('display', 'block');
        },

        hideArrowBox: function() {
            $('body').find('.asp_arrow_box').css('display', 'none');
        },

        showNextInvalidFacetMessage: function() {
            let $this = this;
            if ( $this.n.searchsettings.find('.asp-invalid').length > 0 ) {
                $this.showArrowBox(
                    $this.n.searchsettings.find('.asp-invalid').first().get(0),
                    $this.n.searchsettings.find('.asp-invalid').first().data('asp_invalid_msg')
                );
            }
        },

        scrollToNextInvalidFacetMessage: function() {
            let $this = this;
            if ( $this.n.searchsettings.find('.asp-invalid').length > 0 ) {
                let $n = $this.n.searchsettings.find('.asp-invalid').first();
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
        },
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initDatePicker: function() {
            let $this = this;

            WPD.intervalUntilExecute(function(){
                // We need jQuery UI here, pure jQuery scope
                let _$ = helpers.whichjQuery('datepicker');
                if ( _$ === false ) {
                    return false;
                }

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
                        $(obj).trigger('change');
                }

                _$(".asp_datepicker", $this.n.searchsettings).each(function(){
                    let format = _$(".asp_datepicker_format", _$(this).parent()).val(),
                        _this = this,
                        origValue = _$(this).val();

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
                    if ( origValue == "")
                        _$(this).datepicker("setDate", "");
                    else
                        _$(this).datepicker("setDate", origValue );

                    _$(this).datepicker( "option", "dateFormat", format );

                    // Call the select event to refresh the date pick value
                    onSelectEvent(null, null, _this, true);

                    // Assign the no change select event to a new triggerable event
                    $(this).on('selectnochange', function(){
                        onSelectEvent(null, null, _this, true);
                    });

                    // When the user deletes the value, empty the hidden field as well
                    $(this).on('keyup', function(){
                        if ( $(_this).datepicker("getDate") == null ) {
                            _$(".asp_datepicker_hidden", $(_this).parent()).val('');
                        }
                        $(_this).datepicker("hide");
                    });
                });
            }, function(){
                return helpers.whichjQuery('datepicker');
            });
        },

        initCFDatePicker: function() {
            let $this = this;
            WPD.intervalUntilExecute(function(){
                // We need jQuery UI here, pure jQuery scope
                let _$ = helpers.whichjQuery('datepicker');
                if ( _$ === false ) {
                    return false;
                }

                // Define a global to the function
                //var _this = null;
                function onSelectEvent( dateText, inst, _this, nochange ) {
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
                            day = ("0" + String(date.getDate()) ).slice(-2),
                            newValue = year + month + day;
                        _$(".asp_datepicker_hidden", _$(obj).parent()).val(newValue);
                    }

                    // Trigger change event. $ scope is used ON PURPOSE
                    // ..otherwise scoped version would not trigger!
                    if ( (typeof nochange == "undefined" || nochange == null) && newValue != prevValue )
                        $(obj).trigger('change');
                }

                _$(".asp_datepicker_field", $this.n.searchsettings).each(function(){
                    let format = _$(".asp_datepicker_format", _$(this).parent()).val(),
                        _this = this,
                        origValue = _$(this).val();

                    _$(this).datepicker({
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: 'dd/mm/yy',
                        onSelect: onSelectEvent,
                        beforeShow: function() {
                            _$('#ui-datepicker-div').addClass("asp-ui");
                        }
                    });
                    // Set to empty date if the field is empty
                    if ( origValue == "")
                        _$(this).datepicker("setDate", "");
                    else
                        _$(this).datepicker("setDate", origValue );
                    // Call the selec event to refresh the date pick value

                    _$(this).datepicker( "option", "dateFormat", format );
                    onSelectEvent(null, null, _this, true);

                    // Assign the no change select event to a new triggerable event
                    $(this).on('selectnochange', function(){
                        onSelectEvent(null, null, _this, true);
                    });

                    // When the user deletes the value, empty the hidden field as well
                    $(this).on('keyup', function(){
                        if ( $(_this).datepicker("getDate") == null ) {
                            _$(".asp_datepicker_hidden", $(_this).parent()).val('');
                        }
                        $(_this).datepicker("hide");
                    });
                });
            }, function(){
                return helpers.whichjQuery('datepicker');
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    let functions = {
        initFacetEvents: function() {
            let $this = this,
                gtagTimer = null;

            $('.asp_custom_f input[type=text]:not(.asp_select2-search__field):not(.asp_datepicker_field):not(.asp_datepicker)', $this.n.searchsettings).on('keydown', function(e) {
                let code = e.keyCode || e.which,
                    _this = this;
                if ( code == 13 ) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
                clearTimeout(gtagTimer);
                gtagTimer = setTimeout(function(){
                    $this.gaEvent('facet_change', {
                        'option_label': $(_this).closest('fieldset').find('legend').text(),
                        'option_value': $(_this).val()
                    });
                }, 1400);
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.setFilterStateInput(65);
                if ( $this.o.trigger.facet != 0 )
                    $this.searchWithCheck(240);
            });

            // This needs to be here, submit prevention on input text fields is still needed
            if ($this.o.trigger.facet == 0) return;

            // Dropdown
            $('select', $this.n.searchsettings).on('change slidechange', function(){
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.gaEvent('facet_change', {
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
            //$('input[type!=checkbox][type!=text][type!=radio]', $this.n.searchsettings).on('change slidechange', function(){
            $('input:not([type=checkbox]):not([type=text]):not([type=radio])', $this.n.searchsettings).on('change slidechange', function(){
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.gaEvent('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).val()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });

            // Radio
            $('input[type=radio]', $this.n.searchsettings).on('change slidechange', function(){
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.gaEvent('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).closest('label').text()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });

            $('input[type=checkbox]', $this.n.searchsettings).on('asp_chbx_change', function(){
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.gaEvent('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).closest('.asp_option').find('.asp_option_label').text() + ($(this).prop('checked') ? '(checked)' : '(unchecked)')
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });
            $('input.asp_datepicker, input.asp_datepicker_field', $this.n.searchsettings).on('change', function(){
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.gaEvent('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).val()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });
            $('div[id*="-handles"]', $this.n.searchsettings).each(function(){
                if ( typeof this.noUiSlider != 'undefined') {
                    this.noUiSlider.on('change', function(values) {
                        let target = typeof this.target != 'undefined' ? this.target : this;
                        $this.gaEvent('facet_change', {
                            'option_label': $(target).closest('fieldset').find('legend').text(),
                            'option_value': values
                        });
                        $this.n.searchsettings.find('input[name=filters_changed]').val(1);
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
    let functions = {
        initNoUIEvents: function () {
            let $this = this,
                slider;

            $(".noui-slider-json" + $this.o.rid).each(function(el, index){

                let jsonData = $(this).data("aspnoui");
                if (typeof jsonData === "undefined") return false;

                jsonData = WPD.base64.decode(jsonData);
                if (typeof jsonData === "undefined" || jsonData == "") return false;

                let args = JSON.parse(jsonData);
                if ( $(args.node).length > 0 )
                    slider = $(args.node).get(0);

                // Initialize the main
                if (typeof noUiSlider !== 'undefined') {
                    noUiSlider.create(slider, args.main);
                } else {
                    // NoUiSlider is not included within the scripts, alert the user!
                    return false;
                }

                $this.noUiSliders[index] = slider;

                slider.noUiSlider.on('update', function( values, handle ) {
                    let value = values[handle];
                    if ( handle ) { // true when 1, if upper
                        // Params: el, i, arr
                        args.links.forEach(function(el){
                            let wn = wNumb(el.wNumb);
                            if ( el.handle == "upper") {
                                if ( $(el.target).is('input') )
                                    $(el.target).val(value);
                                else
                                    $(el.target).html( wn.to(parseFloat(value)) );
                            }
                            $(args.node).on('slide', function(e) { e.preventDefault(); } );
                        });
                    } else {        // 0, lower
                        // Params: el, i, arr
                        args.links.forEach(function(el){
                            let wn = wNumb(el.wNumb);
                            if ( el.handle == "lower") {
                                if ( $(el.target).is('input') )
                                    $(el.target).val(value);
                                else
                                    $(el.target).html( wn.to(parseFloat(value)) );
                            }
                            $(args.node).on('slide', function(e) { e.preventDefault(); } );
                        });
                    }
                });
            });

        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initSettingsEvents: function() {
            let $this = this;

            // Note if the settings have changed
            $this.n.searchsettings.on('click', function(){
                $this.settingsChanged = true;
            });

            $this.n.searchsettings.on($this.clickTouchend, function (e) {
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

            $this.n.prosettings.on("click", function () {
                if ($this.n.prosettings.data('opened') == 0) {
                    $this.showSettings();
                } else {
                    $this.hideSettings();
                }
            });

            // noinspection JSUnresolvedVariable
            if ( helpers.isMobile() ) {
                // noinspection JSUnresolvedVariable
                if (
                    $this.o.mobile.force_sett_state == "open" ||
                    ( $this.o.mobile.force_sett_state == "none" && $this.o.settingsVisible == 1 )
                ) {
                    $this.n.prosettings.trigger('click');
                }
            } else {
                // noinspection JSUnresolvedVariable
                if ($this.o.settingsVisible == 1) {
                    $this.n.prosettings.trigger('click');
                }
            }

            // Category level automatic checking and hiding
            $('.asp_option_cat input[type="checkbox"]', $this.n.searchsettings).on('asp_chbx_change', function(){
                $this.settingsCheckboxToggle( $(this).closest('.asp_option_cat') );
            });
            // Init the hide settings
            $('.asp_option_cat', $this.n.searchsettings).each(function(el){
                $this.settingsCheckboxToggle( $(el), false );
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initSettingsBox: function() {
            let $this = this;

            if ( helpers.isMobile() && $this.o.mobile.force_sett_hover == 1) {
                $this.n.searchsettings.attr(
                    "id",
                    $this.n.searchsettings.attr("id").replace('probsettings', 'prosettings')
                );
                $this.n.searchsettings.removeClass('asp_sb asp_sb_' + $this.o.id + ' asp_sb_' + $this.o.rid)
                    .addClass('asp_s asp_s_' + $this.o.id + ' asp_s_' + $this.o.rid);

                $('body').append($this.n.searchsettings.detach());
                $this.n.searchsettings.css({
                    'position': 'absolute'
                });
                $this.o.blocking = false;
                $this.detectAndFixFixedPositioning();
                return true;
            }

            if ( $this.n.settingsAppend.length > 0 ) {
                // There is already a results box there
                if ( $this.n.settingsAppend.find('.asp_w').length > 0 ) {
                    $this.n.searchsettings = $this.n.settingsAppend.find('.asp_w');
                } else {
                    if ( $this.o.blocking == false ) {
                        $this.n.searchsettings.attr(
                            "id",
                            $this.n.searchsettings.attr("id").replace('prosettings', 'probsettings')
                        );
                        $this.o.blocking = true;
                    }
                    $this.n.settingsAppend.append($this.n.searchsettings.detach());
                }

            } else if ($this.o.blocking == false) {
                document.body.appendChild(
                    $this.n.searchsettings.get(0).parentNode.removeChild($this.n.searchsettings.get(0))
                );
            }
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
                !$this.o.blocking ) {
                $this.settAnim.showClass = "asp_an_fadeInDrop";
                $this.settAnim.hideClass = "asp_an_fadeOutDrop";
            } else if ( $this.animOptions.settings.anim == "fadedrop" ) {
                // If does not support transitio, or it is blocking layout
                // .. fall back to fade
                $this.settAnim.showClass = "asp_an_fadeIn";
                $this.settAnim.hideClass = "asp_an_fadeOut";
            }

            $this.n.searchsettings.css({
                "-webkit-animation-duration": $this.settAnim.duration + "ms",
                "animation-duration": $this.settAnim.duration + "ms"
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);
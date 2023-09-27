(function(){
    "use strict";

    window.WPD = typeof window.WPD !== 'undefined' ? window.WPD : {};
    window.WPD.ajaxsearchpro = new function (){
        this.firstIteration = true;
        this.helpers = {};
        this.plugin = {};
        this.addons = {
            addons: [],
            add: function(addon) {
                if ( this.addons.indexOf(addon) == -1 ) {
                    let k = this.addons.push(addon);
                    this.addons[k-1].init();
                }
            },
            remove: function(name) {
                this.addons.filter(function(addon){
                    if ( addon.name == name ) {
                        if ( typeof addon.destroy != 'undefined' ) {
                            addon.destroy();
                        }
                        return false;
                    } else {
                        return true;
                    }
                });
            }
        }
    };
})();(function($){
    "use strict";
    let functions = {
        addAnimation: function () {
            let $this = this,
                i = 0,
                j = 1,
                delay = 25,
                checkViewport = true;

            // No animation for the new elements via more results link
            if ( $this.call_num > 0 || $this._no_animations ) {
                $this.n('results').find('.item, .asp_group_header').removeClass("opacityZero").removeClass("asp_an_" + $this.animOptions.items);
                return false;
            }

            $this.n('results').find('.item, .asp_group_header').each(function () {
                let x = this;
                // The first item must be in the viewport, if not, then we won't use this at all
                if ( j === 1) {
                    checkViewport = $(x).inViewPort(0);
                }

                // No need to animate everything
                if (
                    ( j > 1 && checkViewport && !$(x).inViewPort(0) ) ||
                    j > 80
                ) {
                    $(x).removeClass("opacityZero");
                    return true;
                }

                // noinspection JSUnresolvedVariable
                if ($this.o.resultstype == 'isotopic' && j>$this.il.itemsPerPage) {
                    // Remove this from the ones not affected by the animation
                    $(x).removeClass("opacityZero");
                    return;
                }

                setTimeout(function () {
                    $(x).addClass("asp_an_" + $this.animOptions.items);
                    /**
                     * The opacityZero class must be removed just a little bit after
                     * the animation starts. This way the opacity is not reset to 1 yet,
                     * and not causing flashing effect on the results.
                     *
                     * If the opacityZero is not removed, the after the removeAnimation()
                     * call the opacity flashes back to 0 - window rezise or pagination events
                     */
                    $(x).removeClass("opacityZero");
                }, (i + delay));
                i = i + 45;
                j++;
            });

        },

        removeAnimation: function () {
            let $this = this;
            this.n('items').each(function () {
                $(this).removeClass("asp_an_" + $this.animOptions.items);
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        setFilterStateInput: function( timeout ) {
            let $this = this;
            if ( typeof timeout == 'undefined' ) {
                timeout = 65;
            }
            let process = function(){
                if ( JSON.stringify($this.originalFormData) != JSON.stringify(helpers.formData($('form', $this.n('searchsettings')))) )
                    $this.n('searchsettings').find('input[name=filters_initial]').val(0);
                else
                    $this.n('searchsettings').find('input[name=filters_initial]').val(1);
            };
            if ( timeout == 0 ) {
                process();
            } else {
                // Need a timeout > 50, as some checkboxes are delayed (parent-child selection)
                setTimeout(function () {
                    process();
                }, timeout);
            }
        },

        resetSearchFilters: function() {
            let $this = this;
            helpers.formData($('form', $this.n('searchsettings')), $this.originalFormData);
            // Reset the sliders first
            $this.resetNoUISliderFilters();

            if ( typeof $this.select2jQuery != "undefined" ) {
                $this.select2jQuery($this.n('searchsettings').get(0)).find('.asp_gochosen,.asp_goselect2').trigger("change.asp_select2");
            }
            $this.n('text').val('');
            $this.n('proloading').css('display', 'none');
            $this.hideLoader();
            $this.searchAbort();
            $this.setFilterStateInput(0);
        },

        resetNoUISliderFilters: function() {
            if ( this.noUiSliders.length > 0 ) {
                this.noUiSliders.forEach(function (slider){
                    if ( typeof slider.noUiSlider != 'undefined') {
                        let vals = [];
                        $(slider).parent().find('.asp_slider_hidden').forEach(function(el){
                            vals.push($(el).val());
                        });
                        if ( vals.length > 0 ) {
                            slider.noUiSlider.set(vals);
                        }
                    }
                });
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let functions = {
        showMoreResLoader: function( ) {
            let $this = this;
            $this.n('resultsDiv').addClass('asp_more_res_loading');
        },

        showLoader: function( recall ) {
            let $this = this;
            recall = typeof recall !== 'undefined' ? recall : false;

            // noinspection JSUnresolvedVariable
            if ( $this.o.loaderLocation == "none" ) return;

            // noinspection JSUnresolvedVariable
            if ( !$this.n('search').hasClass("hiddend")  && ( $this.o.loaderLocation != "results" )  ) {
                $this.n('proloading').css({
                    display: "block"
                });
            }

            // stop at this point, if this is a 'load more' call
            if ( recall !== false ) {
                return false;
            }

            // noinspection JSUnresolvedVariable
            if ( ( $this.n('search').hasClass("hiddend") && $this.o.loaderLocation != "search" ) ||
                ( !$this.n('search').hasClass("hiddend") && ( $this.o.loaderLocation == "both" || $this.o.loaderLocation == "results" ) )
            ) {
                if ( !$this.usingLiveLoader() ) {
                    if ( $this.n('resultsDiv').find('.asp_results_top').length > 0 )
                        $this.n('resultsDiv').find('.asp_results_top').css('display', 'none');
                    $this.showResultsBox();
                    $(".asp_res_loader", $this.n('resultsDiv')).removeClass("hiddend");
                    $this.n('results').css("display", "none");
                    $this.n('showmoreContainer').css("display", "none");
                    if ( typeof $this.hidePagination !== 'undefined' ) {
                        $this.hidePagination();
                    }
                }
            }
        },

        hideLoader: function( ) {
            let $this = this;
            $this.n('proloading').css({
                display: "none"
            });
            $(".asp_res_loader", $this.n('resultsDiv')).addClass("hiddend");
            $this.n('results').css("display", "");
            $this.n('resultsDiv').removeClass('asp_more_res_loading');
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let functions = {
        loadASPFonts: function() {
            if ( ASP.font_url!== false  ) {
                this.fontsLoaded = true;
                let font = new FontFace(
                    'asppsicons2',
                    'url(' + ASP.font_url + ')',
                    { style: 'normal', weight: 'normal', 'font-display': 'swap' }
                );
                font.load().then(function(loaded_face) {
                    document.fonts.add(loaded_face);
                }).catch(function(er) {});
                ASP.font_url = false;
            }
        },

        /**
         * Updates the document address bar with the ajax live search attributes, without push state
         */
        updateHref: function( anchor ) {
            anchor = anchor || window.location.hash;
            if ( this.o.trigger.update_href && !this.usingLiveLoader() ) {
                if (!window.location.origin) {
                    window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
                }
                let url = this.getStateURL() + (this.resultsOpened ? '&asp_s=' : '&asp_ls=') + this.n('text').val() + anchor;
                history.replaceState('', '', url.replace(location.origin, ''));
            }
        },

        stat_addKeyword: function(id, keyword) {
            let data = {
                action: 'ajaxsearchpro_addkeyword',
                id: id,
                keyword: keyword
            };
            // noinspection JSUnresolvedVariable
            $.fn.ajax({
                'url': ASP.ajaxurl,
                'method': 'POST',
                'data': data,
                'success': function (response) {}
            })
        },
        /**
         * Checks if an element with the same ID and Instance was already registered
         */
        fixClonedSelf: function() {
            let $this = this,
                oldInstanceId = $this.o.iid,
                oldRID = $this.o.rid;
            while ( !ASP.instances.set($this) ) {
                ++$this.o.iid;
                if ($this.o.iid > 50) {
                    break;
                }
            }
            // oof, this was cloned
            if ( oldInstanceId != $this.o.iid ) {
                $this.o.rid = $this.o.id + '_' + $this.o.iid;
                $this.n('search').get(0).id = "ajaxsearchpro" + $this.o.rid;
                $this.n('search').removeClass('asp_m_' + oldRID).addClass('asp_m_' + $this.o.rid).data('instance', $this.o.iid);
                $this.n('searchsettings').get(0).id = $this.n('searchsettings').get(0).id.replace('settings'+ oldRID, 'settings' + $this.o.rid);
                if ( $this.n('searchsettings').hasClass('asp_s_' + oldRID) ) {
                    $this.n('searchsettings').removeClass('asp_s_' + oldRID)
                        .addClass('asp_s_' + $this.o.rid).data('instance', $this.o.iid);
                } else {
                    $this.n('searchsettings').removeClass('asp_sb_' + oldRID)
                        .addClass('asp_sb_' + $this.o.rid).data('instance', $this.o.iid);
                }
                $this.n('resultsDiv').get(0).id = $this.n('resultsDiv').get(0).id.replace('prores'+ oldRID, 'prores' + $this.o.rid);
                $this.n('resultsDiv').removeClass('asp_r_' + oldRID)
                    .addClass('asp_r_' + $this.o.rid).data('instance', $this.o.iid);
                $this.n('container').find('.asp_init_data').data('instance', $this.o.iid);
                $this.n('container').find('.asp_init_data').get(0).id =
                    $this.n('container').find('.asp_init_data').get(0).id.replace('asp_init_id_'+ oldRID, 'asp_init_id_' + $this.o.rid);

                $this.n('prosettings').data('opened', 0);
            }
        },
        destroy: function () {
            let $this = this;
            Object.keys($this.nodes).forEach(function(k){
                $this.nodes[k].off?.();
            });
            if ( typeof $this.n('searchsettings').get(0).referenced !== 'undefined' ) {
                --$this.n('searchsettings').get(0).referenced;
                if ( $this.n('searchsettings').get(0).referenced < 0 ) {
                    $this.n('searchsettings').remove();
                }
            } else {
                $this.n('searchsettings').remove();
            }
            if ( typeof $this.n('resultsDiv').get(0).referenced !== 'undefined' ) {
                --$this.n('resultsDiv').get(0).referenced;
                if ( $this.n('resultsDiv').get(0).referenced < 0 ) {
                    $this.n('resultsDiv').remove?.();
                }
            } else {
                $this.n('resultsDiv').remove?.();
            }
            $this.n('trythis').remove?.();
            $this.n('search').remove?.();
            $this.n('container').remove?.();
            $this.documentEventHandlers.forEach(function(h){
                $(h.node).off(h.event, h.handler);
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);// noinspection HttpUrlsUsage,JSUnresolvedVariable

(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        isRedirectToFirstResult: function() {
            let $this = this;
            // noinspection JSUnresolvedVariable
            return (
                    $('.asp_res_url', $this.n('resultsDiv')).length > 0 ||
                    $('.asp_es_' + $this.o.id + ' a').length > 0 ||
                    ( $this.o.resPage.useAjax && $($this.o.resPage.selector + 'a').length > 0)
                ) &&
                (
                    ($this.o.redirectOnClick == 1 && $this.ktype == 'click' && $this.o.trigger.click == 'first_result') ||
                    ($this.o.redirectOnEnter == 1 && ($this.ktype == 'input' || $this.ktype == 'keyup') && $this.keycode == 13 && $this.o.trigger.return == 'first_result') ||
                    ($this.ktype == 'button' && $this.o.sb.redirect_action == 'first_result')
                );
        },

        doRedirectToFirstResult: function() {
            let $this = this,
                _loc, url;

            if ( $this.ktype == 'click' ) {
                _loc = $this.o.trigger.click_location;
            } else if ( $this.ktype == 'button' ) {
                // noinspection JSUnresolvedVariable
                _loc = $this.o.sb.redirect_location;
            } else {
                _loc = $this.o.trigger.return_location;
            }

            if ( $('.asp_res_url', $this.n('resultsDiv')).length > 0 ) {
                url =  $( $('.asp_res_url', $this.n('resultsDiv')).get(0) ).attr('href');
            } else if ( $('.asp_es_' + $this.o.id + ' a').length > 0 ) {
                url =  $( $('.asp_es_' + $this.o.id + ' a').get(0) ).attr('href');
            } else if ( $this.o.resPage.useAjax && $($this.o.resPage.selector + 'a').length > 0 ) {
                url =  $( $($this.o.resPage.selector + 'a').get(0) ).attr('href');
            }

            if ( url != '' ) {
                if (_loc == 'same') {
                    location.href = url;
                } else {
                    helpers.openInNewTab(url);
                }

                $this.hideLoader();
                $this.hideResults();
            }
            return false;
        },

        doRedirectToResults: function( ktype ) {
            let $this = this,
                _loc;

            if ( typeof $this.reportSettingsValidity != 'undefined' && !$this.reportSettingsValidity() ) {
                $this.showNextInvalidFacetMessage?.();
                return false;
            }

            if ( ktype == 'click' ) {
                _loc = $this.o.trigger.click_location;
            } else if ( ktype == 'button' ) {
                // noinspection JSUnresolvedVariable
                _loc = $this.o.sb.redirect_location;
            } else {
                _loc = $this.o.trigger.return_location;
            }
            let url = $this.getRedirectURL(ktype);

            // noinspection JSUnresolvedVariable
            if ($this.o.overridewpdefault) {
                // noinspection JSUnresolvedVariable
                if ( $this.o.resPage.useAjax == 1 ) {
                    $this.hideResults();
                    // noinspection JSUnresolvedVariable
                    $this.liveLoad($this.o.resPage.selector, url);
                    $this.showLoader();
                    if ($this.att('blocking') == false) {
                        $this.hideSettings?.();
                    }
                    return false;
                }
                // noinspection JSUnresolvedVariable
                if ( $this.o.override_method == "post") {
                    helpers.submitToUrl(url, 'post', {
                        asp_active: 1,
                        p_asid: $this.o.id,
                        p_asp_data: $('form', $this.n('searchsettings')).serialize()
                    }, _loc);
                } else {
                    if ( _loc == 'same' ) {
                        location.href = url;
                    } else {
                        helpers.openInNewTab(url);
                    }
                }
            } else {
                // The method is not important, just send the data to memorize settings
                helpers.submitToUrl(url, 'post', {
                    np_asid: $this.o.id,
                    np_asp_data: $('form', $this.n('searchsettings')).serialize()
                }, _loc);
            }

            $this.n('proloading').css('display', 'none');
            $this.hideLoader();
            if ($this.att('blocking') == false) $this.hideSettings?.();
            $this.hideResults();
            $this.searchAbort();
        },
        getRedirectURL: function(ktype) {
            let $this = this,
                url, source, final, base_url;
            ktype = typeof ktype !== 'undefined' ? ktype : 'enter';

            if ( ktype == 'click' ) {
                source = $this.o.trigger.click;
            } else if ( ktype == 'button' ) {
                source = $this.o.sb.redirect_action;
            } else {
                source = $this.o.trigger.return;
            }

            if ( source == 'results_page' ) {
                url = '?s=' + helpers.nicePhrase( $this.n('text').val() );
            } else if ( source == 'woo_results_page' ) {
                url = '?post_type=product&s=' + helpers.nicePhrase( $this.n('text').val() );
            } else {
                if ( ktype == 'button' ) {
                    base_url = source == 'elementor_page' ? $this.o.sb.elementor_url : $this.o.sb.redirect_url;
                    // This function is heavy, do not do it on init
                    base_url = helpers.decodeHTMLEntities(base_url);
                    url = $this.parseCustomRedirectURL(base_url, $this.n('text').val());
                } else {
                    base_url = source == 'elementor_page' ? $this.o.trigger.elementor_url : $this.o.trigger.redirect_url;
                    // This function is heavy, do not do it on init
                    base_url = helpers.decodeHTMLEntities(base_url);
                    url = $this.parseCustomRedirectURL(base_url, $this.n('text').val());
                }
            }
            // Is this an URL like xy.com/?x=y
            if ( $this.o.homeurl.indexOf('?') > 1 && url.indexOf('?') === 0 ) {
                url = url.replace('?', '&');
            }

            if ( $this.o.overridewpdefault && $this.o.override_method != 'post' ) {
                // We are about to add a query string to the URL, so it has to contain the '?' character somewhere.
                // ..if not, it has to be added
                let start = '&';
                if ( ( $this.o.homeurl.indexOf('?') === -1 || source == 'elementor_page' ) && url.indexOf('?') === -1 ) {
                    start = '?';
                }
                let addUrl = url + start + "asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + $('form', $this.n('searchsettings')).serialize();
                if ( source == 'elementor_page' ) {
                    final = addUrl;
                } else {
                    final = $this.o.homeurl + addUrl;
                }
            } else {
                if ( source == 'elementor_page' ) {
                    final = url;
                } else {
                    final = $this.o.homeurl + url;
                }
            }

            // Double backslashes - negative lookbehind (?<!:) is not supported in all browsers yet, ECMA2018
            // This section should be only: final.replace(//(?<!:)\/\//g, '/');
            // Bypass solution, but it works at least everywhere
            final = final.replace('https://', 'https:///');
            final = final.replace('http://', 'http:///');
            final = final.replace(/\/\//g, '/');

            final = helpers.Hooks.applyFilters('asp_redirect_url', final, $this.o.id, $this.o.iid);
            final = helpers.wp_hooks_apply_filters('asp_redirect_url', final, $this.o.id, $this.o.iid);

            return final;
        },
        parseCustomRedirectURL: function(url ,phrase) {
            let $this = this,
                u = helpers.decodeHTMLEntities(url).replace(/{phrase}/g, helpers.nicePhrase(phrase)),
                items = u.match(/{(.*?)}/g);
            if ( items !== null ) {
                items.forEach(function(v){
                    v = v.replace(/[{}]/g, '');
                    let node = $('input[type=radio][name*="aspf\[' +  v + '_"]:checked', $this.n('searchsettings'));
                    if ( node.length == 0 )
                        node =  $('input[type=text][name*="aspf\[' +  v + '_"]', $this.n('searchsettings'));
                    if ( node.length == 0 )
                        node =  $('input[type=hidden][name*="aspf\[' +  v + '_"]', $this.n('searchsettings'));
                    if ( node.length == 0 )
                        node =  $('select[name*="aspf\[' +  v + '_"]:not([multiple])', $this.n('searchsettings'));
                    if ( node.length == 0 )
                        node =  $('input[type=radio][name*="termset\[' +  v + '"]:checked', $this.n('searchsettings'));
                    if ( node.length == 0 )
                        node =  $('input[type=text][name*="termset\[' +  v + '"]', $this.n('searchsettings'));
                    if ( node.length == 0 )
                        node =  $('input[type=hidden][name*="termset\[' +  v + '"]', $this.n('searchsettings'));
                    if ( node.length == 0 )
                        node =  $('select[name*="termset\[' +  v + '"]:not([multiple])', $this.n('searchsettings'));
                    if ( node.length == 0 )
                        return true; // Continue

                    let val = node.val();
                    val = "" + val; // Convert anything to string, okay-ish method
                    u = u.replace('{' + v + '}', val);
                });
            }
            return u;
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        showResults: function( ) {
            let $this = this;

            helpers.Hooks.applyFilters('asp/results/show/start', $this);

            $this.initResults();

            // Create the scrollbars if needed
            // noinspection JSUnresolvedVariable
            if ($this.o.resultstype == 'horizontal') {
                $this.createHorizontalScroll();
            } else {
                // noinspection JSUnresolvedVariable
                if ($this.o.resultstype == 'vertical') {
                    $this.createVerticalScroll();
                }
            }

            // noinspection JSUnresolvedVariable
            switch ($this.o.resultstype) {
                case 'horizontal':
                    $this.showHorizontalResults();
                    break;
                case 'vertical':
                    $this.showVerticalResults();
                    break;
                case 'polaroid':
                    $this.showPolaroidResults();
                    //$this.disableMobileScroll = true;
                    break;
                case 'isotopic':
                    $this.showIsotopicResults();
                    break;
                default:
                    $this.showHorizontalResults();
                    break;
            }

            $this.showAnimatedImages();
            $this.hideLoader();

            $this.n('proclose').css({
                display: "block"
            });

            // When opening the results box only
            // noinspection JSUnresolvedVariable
            if ( helpers.isMobile() && $this.o.mobile.hide_keyboard && !$this.resultsOpened )
                document.activeElement.blur();

            // noinspection JSUnresolvedVariable
            if ( $this.o.settingsHideOnRes && $this.att('blocking') == false )
                $this.hideSettings?.();

            if ( typeof WPD.lazy != 'undefined' ) {
                setTimeout(function(){
                    // noinspection JSUnresolvedVariable
                    WPD.lazy('.asp_lazy');
                }, 100)
            }

            $this.eh.resulsDivHoverMouseEnter = $this.eh.resulsDivHoverMouseEnter || function () {
                $('.item', $this.n('resultsDiv')).removeClass('hovered');
                $(this).addClass('hovered');
            };
            $this.eh.resulsDivHoverMouseLeave = $this.eh.resulsDivHoverMouseLeave || function () {
                $('.item', $this.n('resultsDiv')).removeClass('hovered');
            };
            $this.n('resultsDiv').find('.item').on('mouseenter', $this.eh.resulsDivHoverMouseEnter);
            $this.n('resultsDiv').find('.item').on('mouseleave', $this.eh.resulsDivHoverMouseLeave);

            $this.fixSettingsAccessibility();
            $this.resultsOpened = true;

            helpers.Hooks.addFilter('asp/results/show/end', $this);
        },

        hideResults: function( blur ) {
            let $this = this;
            blur = typeof blur == 'undefined' ? true : blur;

            $this.initResults();

            if ( !$this.resultsOpened ) return false;

            $this.n('resultsDiv').removeClass($this.resAnim.showClass).addClass($this.resAnim.hideClass);
            setTimeout(function(){
                $this.n('resultsDiv').css($this.resAnim.hideCSS);
            }, $this.resAnim.duration);

            $this.n('proclose').css({
                display: "none"
            });

            if ( helpers.isMobile() && blur )
                document.activeElement.blur();

            $this.resultsOpened = false;
            // Re-enable mobile scrolling, in case it was disabled
            //$this.disableMobileScroll = false;

            if ( typeof $this.ptstack != "undefined" )
                delete $this.ptstack;

            $this.hideArrowBox?.();

            $this.n('s').trigger("asp_results_hide", [$this.o.id, $this.o.iid], true, true);
        },

        updateResults: function( html ) {
            let $this = this;
            if (
                html.replace(/^\s*[\r\n]/gm, "") === "" ||
                $(html).hasClass('asp_nores') ||
                $(html).find('.asp_nores').length > 0
            ) {
                // Something went wrong, as the no-results container was returned
                $this.n('showmoreContainer').css("display", "none");
                $('span', $this.n('showmore')).html("");
            } else {
                // noinspection JSUnresolvedVariable
                if (
                    $this.o.resultstype == 'isotopic' &&
                    $this.call_num > 0 &&
                    $this.isotopic != null &&
                    typeof $this.isotopic.appended != 'undefined' &&
                    $this.n('items').length > 0
                ) {
                    let $items = $(html),
                        $last = $this.n('items').last(),
                        last = parseInt( $this.n('items').last().attr('data-itemnum') );
                    $items.get().forEach( function(el){
                        $(el).attr('data-itemnum', ++last).css({
                            'width': $last.css('width'),
                            'height': $last.css('height')
                        })
                    });
                    $this.n('resdrg').append( $items );

                    $this.isotopic.appended( $items.get() );
                    $this.nodes.items = $('.item', $this.n('resultsDiv')).length > 0 ? $('.item', $this.n('resultsDiv')) : $('.photostack-flip', $this.n('resultsDiv'));
                } else {
                    // noinspection JSUnresolvedVariable
                    if ( $this.call_num > 0 && $this.o.resultstype == 'vertical' ) {
                        $this.n('resdrg').html($this.n('resdrg').html() + '<div class="asp_v_spacer"></div>' + html);
                    } else {
                        $this.n('resdrg').html($this.n('resdrg').html() + html);
                    }
                }
            }
        },

        showResultsBox: function() {
            let $this = this;

            $this.initResults();

            $this.n('s').trigger("asp_results_show", [$this.o.id, $this.o.iid], true, true);

            $this.n('resultsDiv').css({
                display: 'block',
                height: 'auto'
            });

            $this.n('results').find('.item, .asp_group_header').addClass($this.animationOpacity);

            $this.n('resultsDiv').css($this.resAnim.showCSS);
            $this.n('resultsDiv').removeClass($this.resAnim.hideClass).addClass($this.resAnim.showClass);

            $this.fixResultsPosition(true);
        },

        addHighlightString: function( $items ) {
            // Results highlight on results page
            let $this = this,
                phrase = $this.n('text').val().replace(/["']/g, '');

            $items = typeof $items == 'undefined' ? $this.n('items').find('a.asp_res_url') : $items;
            if ( $this.o.singleHighlight == 1 && phrase != '' && $items.length > 0 ) {
                $items.forEach( function(){
                    try {
                        const url = new URL($(this).attr('href'));
                        url.searchParams.set('asp_highlight', phrase);
                        url.searchParams.set('p_asid', $this.o.id);
                        $(this).attr('href', url.href);
                    } catch (e) {}
                });
            }
        },

        scrollToResults: function( ) {
            let $this = this,
                tolerance = Math.floor( window.innerHeight * 0.1 ),
                stop;

            if (
                !$this.resultsOpened ||
                $this.call_num > 0 ||
                $this.o.scrollToResults.enabled !=1 ||
                $this.n('search').closest(".asp_preview_data").length > 0 ||
                $this.o.compact.enabled == 1 ||
                $this.n('resultsDiv').inViewPort(tolerance)
            ) return;

            if ($this.o.resultsposition == "hover") {
                stop = $this.n('probox').offset().top - 20;
            } else {
                stop = $this.n('resultsDiv').offset().top - 20;
            }
            stop = stop + $this.o.scrollToResults.offset;

            let $adminbar = $("#wpadminbar");
            if ($adminbar.length > 0)
                stop -= $adminbar.height();
            stop = stop < 0 ? 0 : stop;
            window.scrollTo({top: stop, behavior:"smooth"});
        },

        scrollToResult: function(id) {
            let $el = $(id);
            if ( $el.length && !$el.inViewPort(40) ) {
                $el.get(0).scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
            }
        },

        showAnimatedImages: function() {
            let $this = this;
            $this.n('items').each(function () {
                let $image = $(this).find('.asp_image[data-src]'),
                    src = $image.data('src');
                if (typeof src != 'undefined' && src != null && src !== '' && src.indexOf('.gif') > -1) {
                    if ($image.find('canvas').length == 0) {
                        $image.prepend($('<div class="asp_item_canvas"><canvas></canvas></div>').get(0));
                        let c = $(this).find('canvas').get(0),
                            $cc = $(this).find('.asp_item_canvas'),
                            ctx = c.getContext("2d"),
                            img = new Image;
                        img.crossOrigin = "anonymous";
                        img.onload = function () {
                            $(c).attr({
                                "width": img.width,
                                "height": img.height
                            });
                            ctx.drawImage(img, 0, 0, img.width, img.height); // Or at whatever offset you like
                            $cc.css({
                                "background-image": 'url(' + c.toDataURL() + ')'
                            });
                        };
                        img.src = src;
                    }
                }
            });
        },

        updateNoResultsHeader: function() {
            let $this = this,
                $new_nores = $this.n('resdrg').find('.asp_nores'), $old_nores;

            if ( $new_nores.length > 0 ) {
                $new_nores = $new_nores.detach();
            }

            $old_nores = $this.n('resultsDiv').find('.asp_nores')
            if ( $old_nores.length > 0 ) {
                $old_nores.remove();
            }

            if ( $new_nores.length > 0 ) {
                $this.n('resultsDiv').prepend($new_nores);

                $this.n('resultsDiv').find(".asp_keyword").on('click', function () {
                    $this.n('text').val(helpers.decodeHTMLEntities($(this).text()));
                    $this.n('textAutocomplete').val('');
                    // Is any ajax trigger enabled?
                    if ($this.o.redirectOnClick == 0 ||
                        $this.o.redirectOnEnter == 0 ||
                        $this.o.trigger.type == 1) {
                        $this.search();
                    }
                });
            }
        },

        updateInfoHeader: function( totalCount ) {
            let $this = this,
                content,
                $rt = $this.n('resultsDiv').find('.asp_results_top'),
                phrase = $this.n('text').val().trim();

            if ( $rt.length > 0 ) {
                if ( $this.n('items').length <= 0 || $this.n('resultsDiv').find('.asp_nores').length > 0 ) {
                    $rt.css('display', 'none');
                } else {
                    // Results information box original texts
                    if ( typeof $this.resInfoBoxTxt == 'undefined' ) {
                        $this.resInfoBoxTxt =
                            $this.n('resultsDiv').find('.asp_results_top .asp_rt_phrase').length > 0 ?
                                $this.n('resultsDiv').find('.asp_results_top .asp_rt_phrase').html() : '';
                        $this.resInfoBoxTxtNoPhrase =
                            $this.n('resultsDiv').find('.asp_results_top .asp_rt_nophrase').length > 0 ?
                                $this.n('resultsDiv').find('.asp_results_top .asp_rt_nophrase').html() : '';
                    }

                    if ( phrase !== '' && $this.resInfoBoxTxt !== '' ) {
                        content = $this.resInfoBoxTxt;
                    } else if ( phrase === '' && $this.resInfoBoxTxtNoPhrase !== '') {
                        content = $this.resInfoBoxTxtNoPhrase;
                    }
                    if ( content !== '' ) {
                        content = content.replaceAll('{phrase}', helpers.escapeHtml($this.n('text').val()));
                        content = content.replaceAll('{results_count}', $this.n('items').length);
                        content = content.replaceAll('{results_count_total}', totalCount);
                        $rt.html(content);
                        $rt.css('display', 'block');
                    } else {
                        $rt.css('display', 'none');
                    }
                }
            }
        }

    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        createResultsScroll: function(type) {
            let $this = this,
                t, $resScroll = $this.n('results');
            type = typeof type == 'undefined' ? 'vertical' : type;
            // noinspection JSUnresolvedVariable

            $resScroll.on('scroll', function() {
                document.dispatchEvent(new Event('wpd-lazy-trigger'));
                // noinspection JSUnresolvedVariable
                if ( $this.o.show_more.infinite ) {
                    clearTimeout(t);
                    t = setTimeout(function () {
                        $this.checkAndTriggerInfiniteScroll(type);
                    }, 60);
                }
            });
        },

        createVerticalScroll: function () {
            this.createResultsScroll('vertical')
        },

        createHorizontalScroll: function () {
            this.createResultsScroll('horizontal')
        },

        checkAndTriggerInfiniteScroll: function( caller ) {
            let $this = this,
                $r = $('.item', $this.n('resultsDiv'));
            caller = typeof caller == 'undefined' ? 'window' : caller;

            // Show more might not even visible
            if ($this.n('showmore').length == 0 || $this.n('showmoreContainer').css('display') == 'none') {
                return false;
            }

            if ( caller == 'window' || caller == 'horizontal' ) {
                // Isotopic pagination present? Abort.
                // noinspection JSUnresolvedVariable
                if (
                    $this.o.resultstype == 'isotopic' &&
                    $('nav.asp_navigation', $this.n('resultsDiv')).css('display') != 'none'
                ) {
                    return false;
                }

                let onViewPort = $r.last().inViewPort(0, $this.n('resultsDiv').get(0)),
                    onScreen = $r.last().inViewPort(0);
                if (
                    !$this.searching &&
                    $r.length > 0 &&
                    onViewPort && onScreen
                ) {
                    $this.n('showmore').find('a.asp_showmore').trigger('click');
                }
            } else if ( caller == 'vertical' ) {
                let $scrollable = $this.n('results');
                if ( helpers.isScrolledToBottom($scrollable.get(0), 20) ) {
                    $this.n('showmore').find('a.asp_showmore').trigger('click');
                }
            } else if ( caller == 'isotopic' ) {
                if (
                    !$this.searching &&
                    $r.length > 0 &&
                    $this.n('resultsDiv').find('nav.asp_navigation ul li').last().hasClass('asp_active')
                ) {
                    $this.n('showmore').find('a.asp_showmore').trigger('click');
                }
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);// noinspection JSUnresolvedVariable

(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        isDuplicateSearchTriggered: function() {
            let $this = this;
            for (let i=0;i<25;i++) {
                let id = $this.o.id + '_' + i;
                if ( id != $this.o.rid ) {
                    if ( ASP.instances.get($this.o.id, i) !== false ) {
                        return ASP.instances.get($this.o.id, i).searching;
                    }
                }
            }
            return false;
        },

        searchAbort: function() {
            let $this = this;
            if ( $this.post != null ) {
                $this.post.abort();
                $this.isAutoP = false;
            }
        },

        searchWithCheck: function( timeout ) {
            let $this = this;
            if ( typeof timeout == 'undefined' )
                timeout = 50;
            if ($this.n('text').val().length < $this.o.charcount) return;
            $this.searchAbort();

            clearTimeout($this.timeouts.searchWithCheck);
            $this.timeouts.searchWithCheck = setTimeout(function() {
                $this.search();
            }, timeout);
        },

        search: function ( count, order, recall, apiCall, supressInvalidMsg ) {
            let $this = this,
                abort = false;
            if ( $this.isDuplicateSearchTriggered() )
                return false;

            recall = typeof recall == "undefined" ? false : recall;
            apiCall = typeof apiCall == "undefined" ? false : apiCall;
            supressInvalidMsg = typeof supressInvalidMsg == "undefined" ? false : supressInvalidMsg;

            let data = {
                action: 'ajaxsearchpro_search',
                aspp: $this.n('text').val(),
                asid: $this.o.id,
                asp_inst_id: $this.o.rid,
                options: $('form', $this.n('searchsettings')).serialize()
            };

            data = helpers.Hooks.applyFilters('asp_search_data', data, $this.o.id, $this.o.iid);
            data = helpers.wp_hooks_apply_filters('asp_search_data', data, $this.o.id, $this.o.iid);

            $this.hideArrowBox?.();
            if ( typeof $this.reportSettingsValidity != 'undefined' && !$this.isAutoP && !$this.reportSettingsValidity() ) {
                if ( !supressInvalidMsg ) {
                    $this.showNextInvalidFacetMessage?.();
                    $this.scrollToNextInvalidFacetMessage?.();
                }
                abort = true;
            }

            if ( $this.isAutoP ) {
                data.autop = 1;
            }

            if ( !recall && !apiCall && (JSON.stringify(data) === JSON.stringify($this.lastSearchData)) ) {
                if ( !$this.resultsOpened && !$this.usingLiveLoader() ) {
                    $this.showResults();
                }
                if ( $this.isRedirectToFirstResult() ) {
                    $this.doRedirectToFirstResult();
                    return false;
                }
                abort = true;
            }

            if ( abort ) {
                $this.hideLoader();
                $this.searchAbort();
                return false;
            }

            $this.n('s').trigger("asp_search_start", [$this.o.id, $this.o.iid, $this.n('text').val()], true, true);

            $this.searching = true;

            $this.n('proclose').css({
                display: "none"
            });

            $this.showLoader( recall );

            // If blocking, or hover but facetChange activated, dont hide the settings for better UI
            if ( $this.att('blocking') == false && $this.o.trigger.facet == 0 ) $this.hideSettings?.();

            if ( recall ) {
                $this.call_num++;
                data.asp_call_num = $this.call_num;
                /**
                 * The original search started with an auto populate, so set the call number correctly
                 */
                if ( $this.autopStartedTheSearch ) {
                    data.options += '&' + $.fn.serializeForAjax( $this.autopData );
                    --data.asp_call_num;
                }
            } else {
                $this.call_num = 0;
                /**
                 * Mark the non search phrase type of auto populate.
                 * In that case, we need to pass the post IDs to exclude, as well as the next
                 * "load more" query has to act as the first call (call_num=0)
                 */
                $this.autopStartedTheSearch = !!data.autop;
            }

            let $form = $('form[name="asp_data"]');
            if ( $form.length > 0 ) {
                data.asp_preview_options = $form.serialize();
            }

            if ( typeof count != "undefined" && count !== false ) {
                data.options += "&force_count=" + parseInt(count);
            }
            if ( typeof order != "undefined" && order !== false ) {
                data.options += "&force_order=" + parseInt(order);
            }

            $this.gaEvent?.('search_start');

            if ( $('.asp_es_' + $this.o.id).length > 0 ) {
                $this.liveLoad('.asp_es_' + $this.o.id, $this.getCurrentLiveURL(), $this.o.trigger.update_href);
            } else if ( $this.o.resPage.useAjax ) {
                $this.liveLoad($this.o.resPage.selector, $this.getRedirectURL());
            } else if ( $this.o.wooShop.useAjax ) {
                $this.liveLoad($this.o.wooShop.selector, $this.getLiveURLbyBaseLocation($this.o.wooShop.url));
            } else if ( $this.o.taxArchive.useAjax ) {
                $this.liveLoad($this.o.taxArchive.selector, $this.getLiveURLbyBaseLocation($this.o.taxArchive.url));
            } else if ( $this.o.cptArchive.useAjax ) {
                $this.liveLoad($this.o.cptArchive.selector, $this.getLiveURLbyBaseLocation($this.o.cptArchive.url));
            }else {
                $this.post = $.fn.ajax({
                    'url': ASP.ajaxurl,
                    'method': 'POST',
                    'data': data,
                    'success': function (response) {
                        $this.searching = false;
                        response = response.replace(/^\s*[\r\n]/gm, "");
                        let html_response = response.match(/___ASPSTART_HTML___(.*[\s\S]*)___ASPEND_HTML___/),
                            data_response = response.match(/___ASPSTART_DATA___(.*[\s\S]*)___ASPEND_DATA___/);

                        if (html_response == null || typeof (html_response) != "object" || typeof (html_response[1]) == "undefined") {
                            $this.hideLoader();
                            alert('Ajax Search Pro Error:\r\n\r\nPlease look up "The response data is missing" from the documentation at\r\n\r\n documentation.ajaxsearchpro.com');
                            return false;
                        } else {
                            html_response = html_response[1];
                            html_response = helpers.Hooks.applyFilters('asp_search_html', html_response, $this.o.id, $this.o.iid);
                            html_response = helpers.wp_hooks_apply_filters('asp_search_html', html_response, $this.o.id, $this.o.iid);
                        }
                        data_response = JSON.parse(data_response[1]);
                        $this.n('s').trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n('text').val(), data_response], true, true);

                        if ( $this.autopStartedTheSearch ) {
                            // This is an auto populate query (first on page load only)
                            if ( typeof data.autop != 'undefined' ) {
                                $this.autopData['not_in'] = {};
                                $this.autopData['not_in_count'] = 0;
                                if ( typeof data_response.results != 'undefined' ) {
                                    let res = [];
                                    if ( typeof data_response.results.groups != 'undefined') {
                                        Object.keys(data_response.results.groups).forEach(function(k){
                                            if ( typeof data_response.results.groups[k].items != 'undefined' ) {
                                                let group = data_response.results.groups[k].items;
                                                if (Array.isArray(group)) {
                                                    group.forEach(function (result) {
                                                        res.push(result);
                                                    })
                                                }
                                            }
                                        });
                                    } else {
                                        res = Array.isArray( data_response.results ) ? data_response.results : res;
                                    }
                                    res.forEach(function (r) {
                                        if (typeof $this.autopData['not_in'][r['content_type']] == 'undefined') {
                                            $this.autopData['not_in'][r['content_type']] = [];
                                        }
                                        $this.autopData['not_in'][r['content_type']].push(r['id']);
                                        ++$this.autopData['not_in_count'];
                                    });
                                }
                            } else {
                                // In subsequent queries adjust, because this is goint to be deducted in the query
                                data_response.full_results_count += $this.autopData['not_in_count'];
                            }
                        }

                        if (!recall) {
                            $this.initResults();
                            $this.n('resdrg').html("");
                            $this.n('resdrg').html(html_response);
                            $this.results_num = data_response.results_count;
                            if ($this.o.statistics)
                                $this.stat_addKeyword($this.o.id, $this.n('text').val());
                        } else {
                            $this.updateResults(html_response);
                            $this.results_num += data_response.results_count;
                        }

                        $this.updateNoResultsHeader();

                        $this.nodes.items = $('.item', $this.n('resultsDiv')).length > 0 ? $('.item', $this.n('resultsDiv')) : $('.photostack-flip', $this.n('resultsDiv'));

                        $this.addHighlightString();

                        $this.gaEvent?.('search_end', {'results_count': $this.n('items').length});

                        if ($this.isRedirectToFirstResult()) {
                            $this.doRedirectToFirstResult();
                            return false;
                        }
                        $this.hideLoader();
                        $this.showResults();
                        if (
                            window.location.hash != '' &&
                            window.location.hash.indexOf('#asp-res-') > -1 &&
                            $(window.location.hash).length > 0
                        ) {
                            $this.scrollToResult(window.location.hash);
                        } else {
                            $this.scrollToResults();
                        }

                        $this.lastSuccesfulSearch = $('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim();
                        $this.lastSearchData = data;

                        $this.updateInfoHeader(data_response.full_results_count);

                        $this.updateHref();

                        if ($this.n('showmore').length > 0) {
                            if (
                                $('span', $this.n('showmore')).length > 0 &&
                                data_response.results_count > 0 &&
                                (data_response.full_results_count - $this.results_num) > 0
                            ) {
                                if ( $this.n('showmore').data('text') == '' ) {
                                    $this.n('showmore').data('text', $this.n('showmore').html());
                                }
                                $this.n('showmore').html($this.n('showmore').data('text').replaceAll('{phrase}', helpers.escapeHtml($this.n('text').val())));
                                $this.n('showmoreContainer').css("display", "block");
                                $this.n('showmore').css("display", "block");
                                $('span', $this.n('showmore')).html("(" + (data_response.full_results_count - $this.results_num) + ")");

                                let $a = $('a', $this.n('showmore'));
                                $a.attr('href', "");
                                $a.off();
                                $a.on($this.clickTouchend, function (e) {
                                    e.preventDefault();
                                    e.stopImmediatePropagation();   // Stopping either click or touchend

                                    if ($this.o.show_more.action == "ajax") {
                                        // Prevent duplicate triggering, don't use .off, as re-opening the results box this will fail
                                        if ($this.searching)
                                            return false;
                                        $this.showMoreResLoader();
                                        $this.search(false, false, true);
                                    } else {
                                        let url, base_url;
                                        // Prevent duplicate triggering
                                        $(this).off();
                                        if ($this.o.show_more.action == 'results_page') {
                                            url = '?s=' + helpers.nicePhrase($this.n('text').val());
                                        } else if ($this.o.show_more.action == 'woo_results_page') {
                                            url = '?post_type=product&s=' + helpers.nicePhrase($this.n('text').val());
                                        } else {
                                            if ($this.o.show_more.action == 'elementor_page') {
                                                url = $this.parseCustomRedirectURL($this.o.show_more.elementor_url, $this.n('text').val());
                                            } else {
                                                url = $this.parseCustomRedirectURL($this.o.show_more.url, $this.n('text').val());
                                            }
                                            url = $('<textarea />').html(url).text();
                                        }

                                        // Is this an URL like xy.com/?x=y
                                        if ($this.o.show_more.action != 'elementor_page' && $this.o.homeurl.indexOf('?') > 1 && url.indexOf('?') == 0) {
                                            url = url.replace('?', '&');
                                        }

                                        base_url = $this.o.show_more.action == 'elementor_page' ? url : $this.o.homeurl + url;
                                        if ($this.o.overridewpdefault) {
                                            if ($this.o.override_method == "post") {
                                                helpers.submitToUrl(base_url, 'post', {
                                                    asp_active: 1,
                                                    p_asid: $this.o.id,
                                                    p_asp_data: $('form', $this.n('searchsettings')).serialize()
                                                }, $this.o.show_more.location);
                                            } else {
                                                let final = base_url + "&asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + $('form', $this.n('searchsettings')).serialize();
                                                if ($this.o.show_more.location == 'same') {
                                                    location.href = final;
                                                } else {
                                                    helpers.openInNewTab(final);
                                                }
                                            }
                                        } else {
                                            // The method is not important, just send the data to memorize settings
                                            helpers.submitToUrl(base_url, 'post', {
                                                np_asid: $this.o.id,
                                                np_asp_data: $('form', $this.n('searchsettings')).serialize()
                                            }, $this.o.show_more.location);
                                        }
                                    }
                                });
                            } else {
                                $this.n('showmoreContainer').css("display", "none");
                                $('span', $this.n('showmore')).html("");
                            }
                        }
                        $this.isAutoP = false;
                    },
                    'fail': function(jqXHR){
                        if ( jqXHR.aborted )
                            return;
                        $this.n('resdrg').html("");
                        $this.n('resdrg').html('<div class="asp_nores">The request failed. Please check your connection! Status: ' + jqXHR.status + '</div>');
                        $this.nodes.item = $('.item', $this.n('resultsDiv')).length > 0 ? $('.item', $this.n('resultsDiv')) : $('.photostack-flip', $this.n('resultsDiv'));
                        $this.results_num = 0;
                        $this.searching = false;
                        $this.hideLoader();
                        $this.showResults();
                        $this.scrollToResults();
                        $this.isAutoP = false;
                    }
                });
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        searchFor: function( phrase ) {
            if ( typeof phrase != 'undefined' ) {
                this.n('text').val(phrase);
            }
            this.n('textAutocomplete').val('');
            this.search(false, false, false, true);
        },

        searchRedirect: function( phrase ) {
            let url = this.parseCustomRedirectURL(this.o.trigger.redirect_url, phrase);

            // Is this an URL like xy.com/?x=y
            // noinspection JSUnresolvedVariable
            if ( this.o.homeurl.indexOf('?') > 1 && url.indexOf('?') == 0 ) {
                url = url.replace('?', '&');
            }

            // noinspection JSUnresolvedVariable
            if (this.o.overridewpdefault) {
                // noinspection JSUnresolvedVariable
                if ( this.o.override_method == "post") {
                    // noinspection JSUnresolvedVariable
                    helpers.submitToUrl(this.o.homeurl + url, 'post', {
                        asp_active: 1,
                        p_asid: this.o.id,
                        p_asp_data: $('form', this.n('searchsettings')).serialize()
                    });
                } else {
                    // noinspection JSUnresolvedVariable
                    location.href = this.o.homeurl + url + "&asp_active=1&p_asid=" + this.o.id + "&p_asp_data=1&" + $('form', this.n('searchsettings')).serialize();
                }
            } else {
                // The method is not important, just send the data to memorize settings
                // noinspection JSUnresolvedVariable
                helpers.submitToUrl(this.o.homeurl + url, 'post', {
                    np_asid: this.o.id,
                    np_asp_data: $('form', this.n('searchsettings')).serialize()
                });
            }
        },

        toggleSettings: function( state ) {
            // state explicitly given, force behavior
            if (typeof state != 'undefined') {
                if ( state == "show") {
                    this.showSettings?.();
                } else {
                    this.hideSettings?.();
                }
            } else {
                if ( this.n('prosettings').data('opened') == 1 ) {
                    this.hideSettings?.();
                } else {
                    this.showSettings?.();
                }
            }
        },

        closeResults: function( clear ) {
            if (typeof(clear) != 'undefined' && clear) {
                this.n('text').val("");
                this.n('textAutocomplete').val("");
            }
            this.hideResults();
            this.n('proloading').css('display', 'none');
            this.hideLoader();
            this.searchAbort();
        },

        getStateURL: function() {
            let url = location.href,
                sep;
            url = url.split('p_asid');
            url = url[0];
            url = url.replace('&asp_active=1', '');
            url = url.replace('?asp_active=1', '');
            url = url.slice(-1) == '?' ? url.slice(0, -1) : url;
            url = url.slice(-1) == '&' ? url.slice(0, -1) : url;
            sep = url.indexOf('?') > 1 ? '&' :'?';
            return url + sep + "p_asid=" + this.o.id + "&p_asp_data=1&" + $('form', this.n('searchsettings')).serialize();
        },

        resetSearch: function() {
            this.resetSearchFilters();
        },

        filtersInitial: function() {
            return this.n('searchsettings').find('input[name=filters_initial]').val() == 1;
        },

        filtersChanged: function() {
            return this.n('searchsettings').find('input[name=filters_changed]').val() == 1;
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let functions = {
        Hooks: window.WPD.Hooks,

        deviceType: function () {
            let w = window.innerWidth;
            if ( w <= 640 ) {
                return 'phone';
            } else if ( w <= 1024 ) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        },
        detectIOS: function() {
            if (
                typeof window.navigator != "undefined" &&
                typeof window.navigator.userAgent != "undefined"
            )
                return window.navigator.userAgent.match(/(iPod|iPhone|iPad)/) != null;
            return false;
        },
        /**
         * IE <11 detection, excludes EDGE
         * @returns {boolean}
         */
        detectIE: function() {
            let ua = window.navigator.userAgent,
                msie = ua.indexOf('MSIE '),         // <10
                trident = ua.indexOf('Trident/');   // 11

            if ( msie > 0 || trident > 0 )
                return true;

            // other browser
            return false;
        },
        isMobile: function() {
            try {
                document.createEvent("TouchEvent");
                return true;
            } catch(e){
                return false;
            }
        },
        isTouchDevice: function() {
            return "ontouchstart" in window;
        },

        isSafari: function() {
            return (/^((?!chrome|android).)*safari/i).test(navigator.userAgent);
        },

        escapeHtml: function(unsafe) {
            return unsafe.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
        },

        /**
         * Gets the jQuery object, if "plugin" defined, then also checks if the plugin exists
         * @param plugin
         * @returns {boolean|function}
         */
        whichjQuery: function( plugin ) {
            let jq = false;

            if ( typeof window.$ != "undefined" ) {
                if ( typeof plugin === "undefined" ) {
                    jq = window.$;
                } else {
                    if ( typeof window.$.fn[plugin] != "undefined" ) {
                        jq = window.$;
                    }
                }
            }
            if ( jq === false && typeof window.jQuery != "undefined" ) {
                jq = window.jQuery;
                if ( typeof plugin === "undefined" ) {
                    jq = window.jQuery;
                } else {
                    if ( typeof window.jQuery.fn[plugin] != "undefined" ) {
                        jq = window.jQuery;
                    }
                }
            }

            return jq;
        },
        formData: function(form, data) {
            let $this = this,
                els = form.find('input,textarea,select,button').get();
            if ( arguments.length === 1 ) {
                // return all data
                data = {};

                els.forEach(function(el) {
                    if (el.name && !el.disabled && (el.checked
                        || /select|textarea/i.test(el.nodeName)
                        || /text/i.test(el.type)
                        || $(el).hasClass('hasDatepicker')
                        || $(el).hasClass('asp_slider_hidden'))
                    ) {
                        if(data[el.name] == undefined){
                            data[el.name] = [];
                        }
                        if ( $(el).hasClass('hasDatepicker') ) {
                            data[el.name].push($(el).parent().find('.asp_datepicker_hidden').val());
                        } else {
                            data[el.name].push($(el).val());
                        }
                    }
                });
                return JSON.stringify(data);
            } else {
                if ( typeof data != "object" ) {
                    data = JSON.parse(data);
                }
                els.forEach(function(el) {
                    if (el.name) {
                        if (data[el.name]) {
                            let names = data[el.name],
                                _this = $(el);
                            if(Object.prototype.toString.call(names) !== '[object Array]'){
                                names = [names]; //backwards compat to old version of this code
                            }
                            if(el.type == 'checkbox' || el.type == 'radio') {
                                let val = _this.val(),
                                    found = false;
                                for(let i = 0; i < names.length; i++){
                                    if(names[i] == val){
                                        found = true;
                                        break;
                                    }
                                }
                                _this.prop("checked", found);
                            } else {
                                _this.val(names[0]);

                                if ( $(el).hasClass('asp_gochosen') || $(el).hasClass('asp_goselect2') ) {
                                    WPD.intervalUntilExecute(function(_$){
                                        _$(el).trigger("change.asp_select2");
                                    }, function(){
                                        return $this.whichjQuery('asp_select2');
                                    }, 50, 3);
                                } else if ( $(el).hasClass('hasDatepicker') ) {
                                    WPD.intervalUntilExecute(function(_$){
                                        let value = names[0],
                                            format = _$(_this.get(0)).datepicker("option", 'dateFormat' );
                                        _$(_this.get(0)).datepicker("option", 'dateFormat', 'yy-mm-dd');
                                        _$(_this.get(0)).datepicker("setDate", value );
                                        _$(_this.get(0)).datepicker("option", 'dateFormat', format);
                                        _$(_this.get(0)).trigger('selectnochange');
                                    }, function(){
                                        return $this.whichjQuery('datepicker');
                                    }, 50, 3);
                                }
                            }
                        } else {
                            if(el.type == 'checkbox' || el.type == 'radio') {
                                $(el).prop("checked", false);
                            }
                        }
                    }
                });

                return form;
            }
        },
        submitToUrl: function(action, method, input, target) {
            let form;
            form = $('<form style="display: none;" />');
            form.attr('action', action);
            form.attr('method', method);
            $('body').append(form);
            if (typeof input !== 'undefined' && input !== null) {
                Object.keys(input).forEach(function (name) {
                    let value = input[name];
                    let $input = $('<input type="hidden" />');
                    $input.attr('name', name);
                    $input.attr('value', value);
                    form.append($input);
                });
            }
            if ( typeof (target) != 'undefined' && target == 'new') {
                form.attr('target', '_blank');
            }
            form.get(0).submit();
        },
        openInNewTab: function(url) {
            Object.assign(document.createElement('a'), { target: '_blank', href: url}).click();
        },
        isScrolledToBottom: function(el, tolerance) {
            return el.scrollHeight - el.scrollTop - $(el).outerHeight() < tolerance;
        },
        getWidthFromCSSValue: function(width, containerWidth) {
            let min = 100,
                ret;

            width = width + '';
            // Pixel value
            if ( width.indexOf('px') > -1 ) {
                ret = parseInt(width, 10);
            } else if ( width.indexOf('%') > -1 ) {
                // % value, calculate against the container
                if ( typeof containerWidth != 'undefined' && containerWidth != null ) {
                    ret = Math.floor(parseInt(width, 10) / 100 * containerWidth);
                } else {
                    ret = parseInt(width, 10);
                }
            } else {
                ret = parseInt(width, 10);
            }

            return ret < 100 ? min : ret;
        },

        nicePhrase: function(s) {
            // noinspection RegExpRedundantEscape
            return encodeURIComponent(s).replace(/\%20/g, '+');
        },

        unqoutePhrase: function(s) {
            return s.replace(/["']/g, '');
        },

        /**
         * Used for input fields to only restrict to valid number user inputs
         */
        inputToFloat(input) {
            return input.replace(/^[.]/g, '').replace(/[^0-9.-]/g, '').replace(/^[-]/g, 'x').replace(/[-]/g, '').replace(/[x]/g, '-').replace(/(\..*?)\..*/g, '$1');
        },

        addThousandSeparators(n, s) {
            if ( s != '' ) {
                s = s || ",";
                return String(n).replace(/(?:^|[^.\d])\d+/g, function(n) {
                        return n.replace(/\B(?=(?:\d{3})+\b)/g, s);
                    });
            } else {
                return n;
            }
        },

        decodeHTMLEntities: function(str) {
            let element = document.createElement('div');
            if(str && typeof str === 'string') {
                // strip script/html tags
                str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
                str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
                element.innerHTML = str;
                str = element.textContent;
                element.textContent = '';
            }
            return str;
        },

        isScrolledToRight: function(el) {
            return el.scrollWidth - $(el).outerWidth() === el.scrollLeft;
        },

        isScrolledToLeft: function(el) {
            return el.scrollLeft === 0;
        },

        /**
         * @deprecated 2022 Q1
         * @returns {any|boolean}
         */
        wp_hooks_apply_filters: function() {
            if ( typeof wp != 'undefined' && typeof wp.hooks != 'undefined' && typeof wp.hooks.applyFilters != 'undefined' ) {
                return wp.hooks.applyFilters.apply(null, arguments);
            } else {
                return typeof arguments[1] != 'undefined' ? arguments[1] : false;
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.helpers, functions);
})(WPD.dom);// noinspection JSUnresolvedVariable

(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        detectAndFixFixedPositioning: function() {
            let $this = this,
                fixedp = false,
                n = $this.n('search').get(0);

            while (n) {
                n = n.parentElement;
                if ( n != null && window.getComputedStyle(n).position == 'fixed' ) {
                    fixedp = true;
                    break;
                }
            }

            if ( fixedp || $this.n('search').css('position') == 'fixed' ) {
                if ( $this.n('resultsDiv').css('position') == 'absolute' ) {
                    $this.n('resultsDiv').css({
                        'position':'fixed',
                        'z-index': 2147483647
                    });
                }
                if ( !$this.att('blocking') ) {
                    $this.n('searchsettings').css({
                        'position':'fixed',
                        'z-index': 2147483647
                    });
                }
            } else {
                if ( $this.n('resultsDiv').css('position') == 'fixed' )
                    $this.n('resultsDiv').css('position', 'absolute');
                if ( !$this.att('blocking') )
                    $this.n('searchsettings').css('position', 'absolute');
            }
        },
        fixSettingsAccessibility: function() {
            let $this = this;
            /**
             * These are not translated on purpose!!
             * These are invisible to any user. The only purpose is to bypass false-positive WAVE tool errors.
             */
            $this.n('searchsettings').find('input.asp_select2-search__field').attr('aria-label', 'Select2 search');
        },

        fixTryThisPosition: function() {
            let $this = this;
            $this.n('trythis').css({
                left: $this.n('search').position().left
            });
        },

        fixResultsPosition: function(ignoreVisibility) {
            ignoreVisibility = typeof ignoreVisibility == 'undefined' ? false : ignoreVisibility;
            let $this = this,
                $body = $('body'),
                bodyTop = 0,
                rpos = $this.n('resultsDiv').css('position');

            if ( $._fn.bodyTransformY() != 0 || $body.css("position") != "static" ) {
                bodyTop = $body.offset().top;
            }

            /**
             * When CSS transform is present, then Fixed element are no longer fixed
             * even if the CSS declaration says. It is better to change them to absolute then.
             */
            if ( $._fn.bodyTransformY() != 0 && rpos == 'fixed' ) {
                rpos = 'absolute';
                $this.n('resultsDiv').css('position', 'absolute');
            }

            // If still fixed, no need to remove the body position
            if ( rpos == 'fixed' ) {
                bodyTop = 0;
            }

            if ( rpos != 'fixed' && rpos != 'absolute' ) {
                return;
            }

            if (ignoreVisibility == true || $this.n('resultsDiv').css('visibility') == 'visible') {
                let _rposition = $this.n('search').offset(),
                    bodyLeft = 0;

                if ( $._fn.bodyTransformX() != 0 || $body.css("position") != "static" ) {
                    bodyLeft = $body.offset().left;
                }

                if ( typeof _rposition != 'undefined' ) {
                    let vwidth, adjust = 0;
                    if ( helpers.deviceType() == 'phone' ) {
                        vwidth = $this.o.results.width_phone;
                    } else if ( helpers.deviceType() == 'tablet' ) {
                        vwidth = $this.o.results.width_tablet;
                    } else {
                        vwidth = $this.o.results.width;
                    }
                    if ( vwidth == 'auto') {
                        vwidth = $this.n('search').outerWidth() < 240 ? 240 : $this.n('search').outerWidth();
                    }
                    $this.n('resultsDiv').css('width', !isNaN(vwidth) ? vwidth + 'px' : vwidth);
                    if ( $this.o.resultsSnapTo == 'right' ) {
                        adjust = $this.n('resultsDiv').outerWidth() - $this.n('search').outerWidth();
                    } else if (( $this.o.resultsSnapTo == 'center' )) {
                        adjust = Math.floor( ($this.n('resultsDiv').outerWidth() - parseInt($this.n('search').outerWidth())) / 2 );
                    }

                    $this.n('resultsDiv').css({
                        top: (_rposition.top + $this.n('search').outerHeight(true) - bodyTop) + 'px',
                        left: (_rposition.left - adjust - bodyLeft) + 'px'
                    });
                }
            }
        },

        fixSettingsPosition: function(ignoreVisibility) {
            ignoreVisibility = typeof ignoreVisibility == 'undefined' ? false : ignoreVisibility;
            let $this = this,
                $body = $('body'),
                bodyTop = 0,
                settPos = $this.n('searchsettings').css('position');

            if ( $._fn.bodyTransformY() != 0 || $body.css("position") != "static" ) {
                bodyTop = $body.offset().top;
            }

            /**
             * When CSS transform is present, then Fixed element are no longer fixed
             * even if the CSS declaration says. It is better to change them to absolute then.
             */
            if ( $._fn.bodyTransformY() != 0 && settPos == 'fixed' ) {
                settPos = 'absolute';
                $this.n('searchsettings').css('position', 'absolute');
            }

            // If still fixed, no need to remove the body position
            if ( settPos == 'fixed' ) {
                bodyTop = 0;
            }

            if ( ( ignoreVisibility == true || $this.n('prosettings').data('opened') != 0 ) && $this.att('blocking') != true ) {
                let $n, sPosition, top, left,
                    bodyLeft = 0;

                if ( $._fn.bodyTransformX() != 0 || $body.css("position") != "static" ) {
                    bodyLeft = $body.offset().left;
                }
                $this.fixSettingsWidth();

                if ( $this.n('prosettings').css('display') != 'none' ) {
                    $n = $this.n('prosettings');
                } else {
                    $n = $this.n('promagnifier');
                }

                sPosition = $n.offset();

                top = (sPosition.top + $n.height() - 2 - bodyTop) + 'px';
                left = ($this.o.settingsimagepos == 'left' ?
                    sPosition.left : (sPosition.left + $n.width() - $this.n('searchsettings').width()) );
                left = left - bodyLeft + 'px';

                $this.n('searchsettings').css({
                    display: "block",
                    top: top,
                    left: left
                });
            }
        },

        fixSettingsWidth: function () {
            let $this = this;

            if ( $this.att('blocking') || $this.o.fss_layout == 'masonry') return;
            $this.n('searchsettings').css({"width": "100%"});
            if ( ($this.n('searchsettings').width() % $("fieldset", $this.n('searchsettings')).outerWidth(true)) > 10 ) {
                let newColumnCount = Math.floor( $this.n('searchsettings').width() / $("fieldset", $this.n('searchsettings')).outerWidth(true) );
                newColumnCount = newColumnCount <= 0 ? 1 : newColumnCount;
                $this.n('searchsettings').css({
                    "width": ( newColumnCount * $("fieldset", $this.n('searchsettings')).outerWidth(true) + 8 ) + 'px'
                });
            }
        },

        hideOnInvisibleBox: function() {
            let $this = this;
            if (
                $this.o.detectVisibility == 1 &&
                $this.o.compact.enabled == 0 &&
                !$this.n('search').hasClass('hiddend') &&
                !$this.n('search').isVisible()
            ) {
                $this.hideSettings?.();
                $this.hideResults();
            }
        }
    }

    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initMagnifierEvents: function() {
            let $this = this, t;
            $this.n('promagnifier').on('click', function (e) {
                let compact = $this.n('search').attr('data-asp-compact')  || 'closed';
                $this.keycode = e.keyCode || e.which;
                $this.ktype = e.type;

                // If compact closed or click on magnifier in opened compact mode, when closeOnMagnifier enabled
                if ( $this.o.compact.enabled == 1 ) {
                    // noinspection JSUnresolvedVariable
                    if (
                        compact == 'closed' ||
                        ( $this.o.compact.closeOnMagnifier == 1 && compact == 'open' )
                    ) {
                        return false;
                    }
                }

                $this.gaEvent?.('magnifier');

                // If redirection is set to the results page, or custom URL
                // noinspection JSUnresolvedVariable
                if (
                    $this.n('text').val().length >= $this.o.charcount &&
                    $this.o.redirectOnClick == 1 &&
                    $this.o.trigger.click != 'first_result'
                ) {
                    $this.doRedirectToResults('click');
                    clearTimeout(t);
                    return false;
                }

                if ( !( $this.o.trigger.click == 'ajax_search' || $this.o.trigger.click == 'first_result' ) ) {
                    return false;
                }

                $this.searchAbort();
                clearTimeout($this.timeouts.search);
                $this.n('proloading').css('display', 'none');

                if ( $this.n('text').val().length >= $this.o.charcount ) {
                    $this.timeouts.search = setTimeout(function () {
                        // If the user types and deletes, while the last results are open
                        if (
                            ($('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim()) != $this.lastSuccesfulSearch ||
                            (!$this.resultsOpened && !$this.usingLiveLoader())
                        ) {
                            $this.search();
                        } else {
                            if ($this.isRedirectToFirstResult())
                                $this.doRedirectToFirstResult();
                            else
                                $this.n('proclose').css('display', 'block');
                        }
                    }, $this.o.trigger.delay);
                }
            });
        },
        initButtonEvents: function() {
            let $this = this;

            $this.n('searchsettings').find('button.asp_s_btn').on('click', function(e){
                $this.ktype = 'button';
                e.preventDefault();
                // noinspection JSUnresolvedVariable
                if ( $this.n('text').val().length >= $this.o.charcount ) {
                    // noinspection JSUnresolvedVariable
                    if ( $this.o.sb.redirect_action != 'ajax_search' ) {
                        // noinspection JSUnresolvedVariable
                        if ($this.o.sb.redirect_action != 'first_result') {
                            $this.doRedirectToResults('button');
                        } else {
                            if ( $this.isRedirectToFirstResult() ) {
                                $this.doRedirectToFirstResult();
                                return false;
                            }
                            $this.search();
                        }
                    } else {
                        if (
                            ($('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim()) != $this.lastSuccesfulSearch ||
                            !$this.resultsOpened
                        ) {
                            $this.search();
                        }
                    }
                    clearTimeout($this.timeouts.search);
                }
            });

            $this.n('searchsettings').find('button.asp_r_btn').on('click', function(e){
                let currentFormData = helpers.formData($('form', $this.n('searchsettings'))),
                    lastPhrase = $this.n('text').val();

                e.preventDefault();
                $this.resetSearchFilters();
                // noinspection JSUnresolvedVariable
                if ( $this.o.rb.action == 'live' &&
                    (
                        JSON.stringify(currentFormData) != JSON.stringify(helpers.formData($('form', $this.n('searchsettings')))) ||
                        lastPhrase != ''
                    )
                ) {
                    $this.search(false, false, false, true, true);
                } else { // noinspection JSUnresolvedVariable
                    if ( $this.o.rb.action == 'close' ) {
                        $this.hideResults();
                    }
                }
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initInputEvents: function() {
            let $this = this, initialized = false;
            let initTriggers = function() {
                $this.n('text').off('mousedown touchstart keydown', initTriggers);
                if ( !initialized ) {
                    $this._initFocusInput();
                    if ( $this.o.trigger.type ) {
                        $this._initSearchInput();
                    }
                    $this._initEnterEvent();
                    $this._initFormEvent();
                    $this.initAutocompleteEvent?.();
                    initialized = true;
                }
            };
            $this.n('text').on('mousedown touchstart keydown', initTriggers, {passive: true});
        },

        _initFocusInput: function() {
            let $this = this;

            // Some kind of crazy rev-slider fix
            $this.n('text').on('click', function(e){
                /**
                 * In some menus the input is wrapped in an <a> tag, which has an event listener attached.
                 * When clicked, the input is blurred. This prevents that.
                 */
                e.stopPropagation();
                e.stopImmediatePropagation();

                $(this).trigger('focus');
                $this.gaEvent?.('focus');

                // Show the results if the query does not change
                if (
                    ($('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim()) == $this.lastSuccesfulSearch
                ) {
                    if ( !$this.resultsOpened && !$this.usingLiveLoader() ) {
                        $this._no_animations = true;
                        $this.showResults();
                        $this._no_animations = false;
                    }
                    return false;
                }
            });
            $this.n('text').on('focus input', function(){
                if ( $this.searching ) {
                    return;
                }
                if ( $(this).val() != '' ) {
                    $this.n('proclose').css('display', 'block');
                } else {
                    $this.n('proclose').css({
                        display: "none"
                    });
                }
            });
        },

        _initSearchInput: function() {
            let $this = this,
                previousInputValue = $this.n('text').val();

            $this.n('text').on('input', function(e){
                $this.keycode =  e.keyCode || e.which;
                $this.ktype = e.type;
                if ( helpers.detectIE() ) {
                    if ( previousInputValue == $this.n('text').val() ) {
                        return false;
                    } else {
                        previousInputValue = $this.n('text').val();
                    }
                }

                $this.updateHref();

                // Trigger on redirection/magnifier
                if ( !$this.o.trigger.type ) {
                    $this.searchAbort();
                    clearTimeout($this.timeouts.search);
                    $this.hideLoader();
                    return false;
                }

                $this.hideArrowBox?.();

                // Is the character count sufficient?
                // noinspection JSUnresolvedVariable
                if ( $this.n('text').val().length < $this.o.charcount ) {
                    $this.n('proloading').css('display', 'none');
                    if ($this.att('blocking') == false) $this.hideSettings?.();
                    $this.hideResults(false);
                    $this.searchAbort();
                    clearTimeout($this.timeouts.search);
                    return false;
                }

                $this.searchAbort();
                clearTimeout($this.timeouts.search);
                $this.n('proloading').css('display', 'none');

                $this.timeouts.search = setTimeout(function () {
                    // If the user types and deletes, while the last results are open
                    if (
                        ($('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim()) != $this.lastSuccesfulSearch ||
                        (!$this.resultsOpened && !$this.usingLiveLoader())
                    ) {
                        $this.search();
                    } else {
                        if ( $this.isRedirectToFirstResult() )
                            $this.doRedirectToFirstResult();
                        else
                            $this.n('proclose').css('display', 'block');
                    }
                }, $this.o.trigger.delay);
            });
        },

        _initEnterEvent: function() {
            let $this = this,
                rt, enterRecentlyPressed = false;
            // The return event has to be dealt with on a keyup event, as it does not trigger the input event
            $this.n('text').on('keyup', function(e) {
                $this.keycode =  e.keyCode || e.which;
                $this.ktype = e.type;

                // Prevent rapid enter key pressing
                if ( $this.keycode == 13 ) {
                    clearTimeout(rt);
                    rt = setTimeout(function(){
                        enterRecentlyPressed = false;
                    }, 300);
                    if ( enterRecentlyPressed ) {
                        return false;
                    } else {
                        enterRecentlyPressed = true;
                    }
                }

                let isInput = $(this).hasClass("orig");

                // noinspection JSUnresolvedVariable
                if ( $this.n('text').val().length >= $this.o.charcount && isInput && $this.keycode == 13 ) {
                    $this.gaEvent?.('return');
                    if ( $this.o.redirectOnEnter == 1 ) {
                        if ($this.o.trigger.return != 'first_result') {
                            $this.doRedirectToResults($this.ktype);
                        } else {
                            $this.search();
                        }
                    } else if ( $this.o.trigger.return == 'ajax_search' ) {
                        if (
                            ($('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim()) != $this.lastSuccesfulSearch ||
                            !$this.resultsOpened
                        ) {
                            $this.search();
                        }
                    }
                    clearTimeout($this.timeouts.search);
                }
            });
        },

        _initFormEvent: function(){
            let $this = this;
            // Handle the submit/mobile search button event
            $($this.n('text').closest('form').get(0)).on('submit', function (e, args) {
                e.preventDefault();
                // Mobile keyboard search icon and search button
                if ( helpers.isMobile() ) {
                    if ( $this.o.redirectOnEnter ) {
                        let event = new Event("keyup");
                        event.keyCode = event.which = 13;
                        this.n('text').get(0).dispatchEvent(event);
                    } else {
                        $this.search();
                        document.activeElement.blur();
                    }
                } else if (typeof(args) != 'undefined' && args == 'ajax') {
                    $this.search();
                }
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let functions = {
        initNavigationEvents: function () {
            let $this = this;

            let handler = function (e) {
                let keycode =  e.keyCode || e.which;
                // noinspection JSUnresolvedVariable
                if (
                    $('.item', $this.n('resultsDiv')).length > 0 && $this.n('resultsDiv').css('display') != 'none' &&
                    $this.o.resultstype == "vertical"
                ) {
                    if ( keycode == 40 || keycode == 38 ) {
                        let $hovered = $this.n('resultsDiv').find('.item.hovered');
                        $this.n('text').trigger('blur');
                        if ( $hovered.length == 0 ) {
                            $this.n('resultsDiv').find('.item').first().addClass('hovered');
                        } else {
                            if (keycode == 40) {
                                if ( $hovered.next('.item').length == 0 ) {
                                    $this.n('resultsDiv').find('.item').removeClass('hovered').first().addClass('hovered');
                                } else {
                                    $hovered.removeClass('hovered').next('.item').addClass('hovered');
                                }
                            }
                            if (keycode == 38) {
                                if ( $hovered.prev('.item').length == 0 ) {
                                    $this.n('resultsDiv').find('.item').removeClass('hovered').last().addClass('hovered');
                                } else {
                                    $hovered.removeClass('hovered').prev('.item').addClass('hovered');
                                }
                            }
                        }
                        e.stopPropagation();
                        e.preventDefault();
                        if ( !$this.n('resultsDiv').find('.resdrg .item.hovered').inViewPort(50, $this.n('resultsDiv').get(0)) ) {
                            let n = $this.n('resultsDiv').find('.resdrg .item.hovered').get(0);
                            if ( n != null && typeof n.scrollIntoView != "undefined" ) {
                                n.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
                            }
                        }
                    }

                    // Trigger click on return key
                    if ( keycode == 13 && $('.item.hovered', $this.n('resultsDiv')).length > 0 ) {
                        e.stopPropagation();
                        e.preventDefault();
                        $('.item.hovered a.asp_res_url', $this.n('resultsDiv')).get(0).click();
                    }

                }
            };
            $this.documentEventHandlers.push({
                'node': document,
                'event': 'keydown',
                'handler': handler
            });
            $(document).on('keydown', handler);
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let _static = window.WPD.ajaxsearchpro;
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initOtherEvents: function() {
            let $this = this, handler, handler2;

            if ( helpers.isMobile() && helpers.detectIOS() ) {
                /**
                 * Memorize the scroll top when the input is focused on IOS
                 * as fixed elements scroll freely, resulting in incorrect scroll value
                 */
                $this.n('text').on('touchstart', function () {
                    $this.savedScrollTop = window.scrollY;
                    $this.savedContainerTop = $this.n('search').offset().top;
                });
            }

            if ( $this.o.focusOnPageload ) {
                $(window).on('load', function(){
                    $this.n('text').get(0).focus();
                }, {'options': {'once': true}});
            }

            $this.n('proclose').on($this.clickTouchend, function (e) {
                //if ($this.resultsOpened == false) return;
                e.preventDefault();
                e.stopImmediatePropagation();
                $this.n('text').val("");
                $this.n('textAutocomplete').val("");
                $this.hideResults();
                $this.n('text').trigger('focus');

                $this.n('proloading').css('display', 'none');
                $this.hideLoader();
                $this.searchAbort();


                if ( $('.asp_es_' + $this.o.id).length > 0 ) {
                    $this.showLoader();
                    $this.liveLoad('.asp_es_' + $this.o.id, $this.getCurrentLiveURL(), $this.o.trigger.update_href);
                } else {
                    const array = ['resPage', 'wooShop', 'taxArchive', 'cptArchive'];
                    for (let i = 0; i < array.length; i++) {
                        if ( $this.o[array[i]].useAjax ) {
                            $this.showLoader();
                            $this.liveLoad($this.o[array[i]].selector, $this.getCurrentLiveURL());
                            break;
                        }
                    }
                }

                $this.n('text').get(0).focus();
            });

            if ( helpers.isMobile() ) {
                handler = function () {
                    $this.orientationChange();
                    // Fire once more a bit delayed, some mobile browsers need to re-zoom etc..
                    setTimeout(function(){
                        $this.orientationChange();
                    }, 600);
                };
                $this.documentEventHandlers.push({
                    'node': window,
                    'event': 'orientationchange',
                    'handler': handler
                });
                $(window).on("orientationchange", handler);
            } else {
                handler = function () {
                    $this.resize();
                };
                $this.documentEventHandlers.push({
                    'node': window,
                    'event': 'resize',
                    'handler': handler
                });
                $(window).on("resize", handler, {passive: true});
            }

            handler2 = function () {
                $this.scrolling(false);
            };
            $this.documentEventHandlers.push({
                'node': window,
                'event': 'scroll',
                'handler': handler2
            });
            $(window).on('scroll', handler2, {passive: true});

            // Mobile navigation focus
            // noinspection JSUnresolvedVariable
            if ( helpers.isMobile() && $this.o.mobile.menu_selector != '' ) {
                // noinspection JSUnresolvedVariable
                $($this.o.mobile.menu_selector).on('touchend', function(){
                    let _this = this;
                    setTimeout(function () {
                        let $input = $(_this).find('input.orig');
                        $input = $input.length == 0 ? $(_this).next().find('input.orig') : $input;
                        $input = $input.length == 0 ? $(_this).parent().find('input.orig') : $input;
                        $input = $input.length == 0 ? $this.n('text') : $input;
                        if ( $this.n('search').inViewPort() ) {
                            $input.get(0).focus();
                        }
                    }, 300);
                });
            }

            // Prevent zoom on IOS
            if ( helpers.detectIOS() && helpers.isMobile() && helpers.isTouchDevice() ) {
                if ( parseInt($this.n('text').css('font-size')) < 16 ) {
                    $this.n('text').data('fontSize', $this.n('text').css('font-size')).css('font-size', '16px');
                    $this.n('textAutocomplete').css('font-size', '16px');
                    $('body').append('<style>#ajaxsearchpro'+$this.o.rid+' input.orig::-webkit-input-placeholder{font-size: 16px !important;}</style>');
                }
            }
        },

        orientationChange: function() {
            let $this = this;
            $this.detectAndFixFixedPositioning();
            $this.fixSettingsPosition();
            $this.fixResultsPosition();
            $this.fixTryThisPosition();

            if ( $this.o.resultstype == "isotopic" && $this.n('resultsDiv').css('visibility') == 'visible' ) {
                $this.calculateIsotopeRows();
                $this.showPagination(true);
                $this.removeAnimation();
            }
        },

        resize: function () {
            let $this = this;
            $this.detectAndFixFixedPositioning();
            $this.fixSettingsPosition();
            $this.fixResultsPosition();
            $this.fixTryThisPosition();
            $this.hideArrowBox?.();

            if ( $this.o.resultstype == "isotopic" && $this.n('resultsDiv').css('visibility') == 'visible' ) {
                $this.calculateIsotopeRows();
                $this.showPagination(true);
                $this.removeAnimation();
            }
        },

        scrolling: function (ignoreVisibility) {
            let $this = this;
            $this.detectAndFixFixedPositioning();
            $this.hideOnInvisibleBox();
            $this.fixSettingsPosition(ignoreVisibility);
            $this.fixResultsPosition(ignoreVisibility);
        },

        initTryThisEvents: function() {
            let $this = this;
            // Try these search button events
            if ( $this.n('trythis').find('a').length > 0 ) {
                $this.n('trythis').find('a').on('click touchend', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    if ($this.o.compact.enabled) {
                        let state = $this.n('search').attr('data-asp-compact') || 'closed';
                        if (state == 'closed')
                            $this.n('promagnifier').trigger('click');
                    }
                    document.activeElement.blur();
                    $this.n('textAutocomplete').val('');
                    $this.n('text').val($(this).html());
                    $this.gaEvent?.('try_this');
                    if ( $this.o.trigger.type ) {
                        $this.searchWithCheck(80);
                    }
                });

                // Make the try-these keywords visible, this makes sure that the styling occurs before visibility
                $this.n('trythis').css({
                    visibility: "visible"
                });
            }
        },

        initSelect2: function() {
            let $this = this;
            window.WPD.intervalUntilExecute(function(jq){
                if ( typeof jq.fn.asp_select2 !== 'undefined' ) {
                    $this.select2jQuery = jq;
                    $('select.asp_gochosen, select.asp_goselect2', $this.n('searchsettings')).each(function () {
                        $(this).removeAttr('data-asp_select2-id'); // Duplicate init protection
                        $(this).find('option[value=""]').val('__any__');
                        $this.select2jQuery(this).asp_select2({
                            width: '100%',
                            theme: 'flat',
                            allowClear: $(this).find('option[value=""]').length > 0,
                            "language": {
                                "noResults": function () {
                                    return $this.o.select2.nores;
                                }
                            }
                        });
                        // Trigger WPD dom change on the original jQuery change event
                        $this.select2jQuery(this).on('change', function () {
                            $(this).trigger('change');
                        });
                    });
                }
            }, function(){
                return helpers.whichjQuery('asp_select2');
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let functions = {
        initResultsEvents: function() {
            let $this = this;

            $this.n('resultsDiv').css({
                opacity: "0"
            });
            let handler = function (e) {
                let keycode =  e.keyCode || e.which,
                    ktype = e.type;

                if ( $(e.target).closest('.asp_w').length == 0 ) {
                    $this.hideOnInvisibleBox();

                    // Any hints
                    $this.hideArrowBox?.();

                    // If not right click
                    if( ktype != 'click' || ktype != 'touchend' || keycode != 3 ) {
                        if ($this.o.compact.enabled) {
                            let compact = $this.n('search').attr('data-asp-compact') || 'closed';
                            // noinspection JSUnresolvedVariable
                            if ($this.o.compact.closeOnDocument == 1 && compact == 'open' && !$this.resultsOpened) {
                                $this.closeCompact();
                                $this.searchAbort();
                                $this.hideLoader();
                            }
                        } else {
                            // noinspection JSUnresolvedVariable
                            if ($this.resultsOpened == false || $this.o.closeOnDocClick != 1) return;
                        }

                        if ( !$this.dragging ) {
                            $this.hideLoader();
                            $this.searchAbort();
                            $this.hideResults();
                        }
                    }
                }
            };
            $this.documentEventHandlers.push({
                'node': document,
                'event': $this.clickTouchend,
                'handler': handler
            });
            $(document).on($this.clickTouchend, handler);


            // GTAG on results click
            $this.n('resultsDiv').on('click', '.results .item', function() {
                if ( $(this).attr('id') != '' ) {
                    $this.updateHref('#' + $(this).attr('id'));
                }

                $this.gaEvent?.('result_click', {
                    'result_title': $(this).find('a.asp_res_url').text(),
                    'result_url': $(this).find('a.asp_res_url').attr('href')
                });
            });

            // Isotope results swipe event
            // noinspection JSUnresolvedVariable
            if ( $this.o.resultstype == "isotopic" ) {
                $this.n('resultsDiv').on("swiped-left", function(){
                    if ( $this.visiblePagination() )
                        $this.n('resultsDiv').find("a.asp_next").trigger('click');
                });
                $this.n('resultsDiv').on("swiped-right", function(){
                    if ( $this.visiblePagination() )
                        $this.n('resultsDiv').find("a.asp_prev").trigger('click');
                });
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let functions = {
        monitorTouchMove: function() {
            let $this = this;
            $this.dragging = false;
            $("body").on("touchmove", function(){
                $this.dragging = true;
            }).on("touchstart", function(){
                $this.dragging = false;
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let _static = window.WPD.ajaxsearchpro;
    let functions = {
        initAutop: function () {
            let $this = this;
            if ( $this.o.autop.state == "disabled" ) return false;

            let location = window.location.href;
            // Correct previous query arguments (in case of paginated results)
            let stop = location.indexOf('asp_ls=') > -1 || location.indexOf('asp_ls&') > -1;
            if ( stop ) {
                return false;
            }
            // noinspection JSUnresolvedVariable
            let count = $this.o.show_more.enabled && $this.o.show_more.action == 'ajax' ? false : $this.o.autop.count;
            $this.isAutoP = true;
            if ( $this.o.compact.enabled == 1 ) {
                $this.openCompact();
            }
            if ($this.o.autop.state == "phrase") {
                if ( !$this.o.is_results_page ) {
                    $this.n('text').val($this.o.autop.phrase);
                }
                $this.search(count);
            } else if ($this.o.autop.state == "latest") {
                $this.search(count, 1);
            } else {
                $this.search(count, 2);
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initEtc: function() {
            let $this = this;

            // Isotopic Layout variables
            $this.il = {
                columns: 3,
                rows: $this.o.isotopic.pagination ? $this.o.isotopic.rows : 10000,
                itemsPerPage: 6,
                lastVisibleItem: -1
            };
            // Isotopic filter functions
            $this.filterFns = {
                number: function (i, el) {
                    if ( typeof el === 'undefined' || typeof i === 'object' ) {
                        el = i;
                    }
                    const number = $(el).attr('data-itemnum'),
                        currentPage = $this.currentPage,
                        itemsPerPage = $this.il.itemsPerPage;

                    if ((number % ($this.il.columns * $this.il.rows)) < ($this.il.columns * ($this.il.rows - 1)))
                        $(el).addClass('asp_gutter_bottom');
                    else
                        $(el).removeClass('asp_gutter_bottom');

                    return (
                        (parseInt(number, 10) < itemsPerPage * currentPage) &&
                        (parseInt(number, 10) >= itemsPerPage * (currentPage - 1))
                    );
                }
            };

            helpers.Hooks.applyFilters('asp/init/etc', $this);
        },

        initInfiniteScroll: function() {
            // NOTE: Custom Scrollbar triggers are under the scrollbar script callbacks -> OnTotalScroll callbacks
            let $this = this;

            // noinspection JSUnresolvedVariable
            if ( $this.o.show_more.infinite && $this.o.resultstype != 'polaroid' ) {
                // Vertical & Horizontal: Regular scroll + when custom scrollbar scroll is not present
                // Isotopic: Regular scroll on non-paginated layout
                let t, handler;
                handler = function () {
                    clearTimeout(t);
                    t = setTimeout(function(){
                        $this.checkAndTriggerInfiniteScroll('window');
                    }, 80);
                };
                $this.documentEventHandlers.push({
                    'node': window,
                    'event': 'scroll',
                    'handler': handler
                });
                $(window).on('scroll', handler);
                $this.n('results').on('scroll', handler);

                let tt;
                $this.n('resultsDiv').on('nav_switch', function () {
                    // Delay this a bit, in case the user quick-switches
                    clearTimeout(tt);
                    tt = setTimeout(function(){
                        $this.checkAndTriggerInfiniteScroll('isotopic');
                    }, 800);
                });
            }
        },

        hooks: function() {
            let $this = this;

            // After elementor results get printed
            $this.n('s').on('asp_elementor_results', function(e, id){
                if ( $this.o.id == id ) {
                    // Lazy load for jetpack
                    // noinspection JSUnresolvedVariable
                    if (typeof jetpackLazyImagesModule == 'function') {
                        setTimeout(function () {
                            // noinspection JSUnresolvedFunction
                            jetpackLazyImagesModule();
                        }, 300);
                    }
                }
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);// noinspection JSUnresolvedVariable

(function($){
    "use strict";
    let _static = window.WPD.ajaxsearchpro;
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        init: function (options, elem) {
            let $this = this;

            $this.searching = false;
            $this.triggerPrevState = false;

            $this.isAutoP = false;
            $this.autopStartedTheSearch = false;
            $this.autopData = {};

            $this.settingsInitialized = false;
            $this.resultsInitialized = false;
            $this.settingsChanged = false;
            $this.resultsOpened = false;
            $this.fontsLoaded = false;
            $this.post = null;
            $this.postAuto = null;
            $this.savedScrollTop = 0;   // Save the window scroll on IOS devices
            $this.savedContainerTop = 0;
            $this.disableMobileScroll = false;
            /**
             * on IOS touch (iPhone, iPad etc..) the 'click' event does not fire, when not bound to a clickable element
             * like a link, so instead, use touchend
             * Stupid solution, but it works..
             */
            $this.clickTouchend = 'click touchend';
            $this.mouseupTouchend = 'mouseup touchend';
            // NoUiSliders storage
            $this.noUiSliders = [];

            // An object to store various timeout events across methods
            $this.timeouts = {
                "compactBeforeOpen": null,
                "compactAfterOpen": null,
                "search": null,
                "searchWithCheck": null
            };

            $this.eh = {}; // this.EventHandlers -> storage for event handler references
            // Document and Window event handlers. Used to detach them in the destroy() method
            $this.documentEventHandlers = [
                /**
                 * {"node": document|window, "event": event_name, "handler": function()..}
                 */
            ];

            $this.currentPage = 1;
            $this.currentPageURL = location.href;
            $this.isotopic = null;
            $this.sIsotope = null;
            $this.lastSuccesfulSearch = ''; // Holding the last phrase that returned results
            $this.lastSearchData = {};      // Store the last search information
            $this._no_animations = false; // Force override option to show animations
            // Repetitive call related
            $this.call_num = 0;
            $this.results_num = 0;

            // this.n and this.o available afterwards
            // also, it corrects the clones and fixes the node varialbes
            $this.o = $.fn.extend({}, options);
            $this.dynamicAtts = {};
            $this.nodes = {};
            $this.nodes.search = $(elem);


            // Make parsing the animation settings easier
            if ( helpers.isMobile() )
                $this.animOptions = $this.o.animations.mob;
            else
                $this.animOptions = $this.o.animations.pc;

            // Fill up the this.n and correct the cloned notes as well

            $this.initNodeVariables();
            /**
             * Default animation opacity. 0 for IN types, 1 for all the other ones. This ensures the fluid
             * animation. Wrong opacity causes flashes.
             */
            $this.animationOpacity = $this.animOptions.items.indexOf("In") < 0 ? "opacityOne" : "opacityZero";

            // Result page live loader disabled for compact layout modes
            $this.o.resPage.useAjax = $this.o.compact.enabled ? 0 : $this.o.resPage.useAjax;
            // Mobile changes
            if (helpers.isMobile()) {
                $this.o.trigger.type = $this.o.mobile.trigger_on_type;
                $this.o.trigger.click = $this.o.mobile.click_action;
                $this.o.trigger.click_location = $this.o.mobile.click_action_location;
                $this.o.trigger.return = $this.o.mobile.return_action;
                $this.o.trigger.return_location = $this.o.mobile.return_action_location;
                $this.o.trigger.redirect_url = $this.o.mobile.redirect_url;
                $this.o.trigger.elementor_url = $this.o.mobile.elementor_url;
            }
            $this.o.redirectOnClick = $this.o.trigger.click != 'ajax_search' && $this.o.trigger.click != 'nothing';
            $this.o.redirectOnEnter = $this.o.trigger.return != 'ajax_search' && $this.o.trigger.return != 'nothing';
            if ( $this.usingLiveLoader() ) {
                $this.o.trigger.type = $this.o.resPage.trigger_type;
                $this.o.trigger.facet = $this.o.resPage.trigger_facet;
                if ($this.o.resPage.trigger_magnifier) {
                    $this.o.redirectOnClick = 0;
                    $this.o.trigger.click = 'ajax_search';
                }

                if ($this.o.resPage.trigger_return) {
                    $this.o.redirectOnEnter = 0;
                    $this.o.trigger.return = 'ajax_search';
                }
            }

            // Reset autocomplete
            //$this.nodes.textAutocomplete.val('');

            if ($this.o.compact.overlay == 1 && $("#asp_absolute_overlay").length == 0) {
                $('body').append("<div id='asp_absolute_overlay'></div>");
            }
            
            if ( $this.usingLiveLoader() ) {
                $this.initLiveLoaderPopState?.();
            }

            // Fixes the fixed layout mode if compact mode is active and touch device fixes
            if ( typeof $this.initCompact !== "undefined" ) {
                $this.initCompact();
            }

            // Try detecting a parent fixed position, and change the results and settings position accordingly
            // $this.detectAndFixFixedPositioning();

            // Sets $this.dragging to true if the user is dragging on a touch device
            $this.monitorTouchMove();

            // Rest of the events
            $this.initEvents();

            // Auto populate init
            $this.initAutop();

            // Etc stuff..
            $this.initEtc();

            // Custom hooks
            $this.hooks();

            // After the first execution, this stays false
            _static.firstIteration = false;

            // Init complete event trigger
            $this.n('s').trigger("asp_init_search_bar", [$this.o.id, $this.o.iid], true, true);

            return this;
        },

        n: function(k){
            if ( typeof this.nodes[k] !== 'undefined' ) {
                return this.nodes[k];
            } else {
                switch( k ) {
                    case 's':
                        this.nodes[k] = this.nodes.search;
                        break;
                    case 'container':
                        this.nodes[k] = this.nodes.search.closest('.asp_w_container');
                        break;
                    case 'searchsettings':
                        this.nodes[k] = $('.asp_ss', this.n('container'));
                        break;
                    case 'resultsDiv':
                        this.nodes[k] = $('.asp_r', this.n('container'));
                        break;
                    case 'probox':
                        this.nodes[k] = $('.probox', this.nodes.search);
                        break;
                    case 'proinput':
                        this.nodes[k] = $('.proinput', this.nodes.search);
                        break;
                    case 'text':
                        this.nodes[k] = $('.proinput input.orig', this.nodes.search);
                        break;
                    case 'textAutocomplete':
                        this.nodes[k] = $('.proinput input.autocomplete', this.nodes.search);
                        break;
                    case 'proloading':
                        this.nodes[k] = $('.proloading', this.nodes.search);
                        break;
                    case 'proclose':
                        this.nodes[k] = $('.proclose', this.nodes.search);
                        break;
                    case 'promagnifier':
                        this.nodes[k] = $('.promagnifier', this.nodes.search);
                        break;
                    case 'prosettings':
                        this.nodes[k] = $('.prosettings', this.nodes.search);
                        break;
                    case 'settingsAppend':
                        this.nodes[k] = $('#wpdreams_asp_settings_' + this.o.id);
                        break;
                    case 'resultsAppend':
                        this.nodes[k] = $('#wpdreams_asp_results_' + this.o.id);
                        break;
                    case 'trythis':
                        this.nodes[k] = $("#asp-try-" + this.o.rid);
                        break;
                    case 'hiddenContainer':
                        this.nodes[k] = $('.asp_hidden_data', this.n('container'));
                        break;
                    case 'aspItemOverlay':
                        this.nodes[k] = $('.asp_item_overlay', this.n('hiddenContainer'));
                        break;
                    case 'showmoreContainer':
                        this.nodes[k] = $('.asp_showmore_container', this.n('resultsDiv'));
                        break;
                    case 'showmore':
                        this.nodes[k] = $('.showmore', this.n('resultsDiv'));
                        break;
                    case 'items':
                        this.nodes[k] = $('.item', this.n('resultsDiv')).length > 0 ? $('.item', this.n('resultsDiv')) : $('.photostack-flip', this.n('resultsDiv'));
                        break;
                    case 'results':
                        this.nodes[k] = $('.results', this.n('resultsDiv'));
                        break;
                    case 'resdrg':
                        this.nodes[k] = $('.resdrg', this.n('resultsDiv'));
                        break;
                }
                return this.nodes[k];
            }
        },

        att: function( k ) {
            if ( typeof this.dynamicAtts[k] !== 'undefined' ) {
                return this.dynamicAtts[k];
            } else {
                switch (k) {
                    case 'blocking':
                        this.dynamicAtts[k] = this.n('searchsettings').hasClass('asp_sb');
                }
            }
            return this.dynamicAtts[k];
        },

        initNodeVariables: function(){
            let $this = this;

            $this.o.id = $this.nodes.search.data('id');
            $this.o.iid = $this.nodes.search.data('instance');
            $this.o.rid = $this.o.id + "_" + $this.o.iid;
            // Fix any potential clones and adjust the variables
            $this.fixClonedSelf();
        },

        initEvents: function () {
            this.initSettingsSwitchEvents?.();
            this.initOtherEvents();
            this.initTryThisEvents();
            this.initMagnifierEvents();
            this.initInputEvents();
            if (this.o.compact.enabled == 1) {
                this.initCompactEvents();
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        /**
         * This function should be called on-demand to init the results events and all. Do not call on init, only when needed.
         */
        initResults: function() {
            if ( !this.resultsInitialized ) {
                this.initResultsBox();
                this.initResultsEvents();
                if ( this.o.resultstype == "vertical" ) {
                    this.initNavigationEvents?.();
                }
                if ( this.o.resultstype == "isotopic" ) {
                    this.initIsotopicPagination?.();
                }
            }
        },
        initResultsBox: function() {
            let $this = this;

            // Calculates the results animation attributes
            $this.initResultsAnimations();

            if ( helpers.isMobile() && $this.o.mobile.force_res_hover == 1) {
                $this.o.resultsposition = 'hover';
                //$('body').append($this.n('resultsDiv').detach());
                $this.nodes.resultsDiv = $this.n('resultsDiv').clone();
                $('body').append($this.nodes.resultsDiv);
                $this.n('resultsDiv').css({
                    'position': 'absolute'
                });
            } else {
                // Move the results div to the correct position
                if ($this.o.resultsposition == 'hover' && $this.n('resultsAppend').length <= 0) {
                    $this.nodes.resultsDiv = $this.n('resultsDiv').clone();
                    $('body').append($this.nodes.resultsDiv);
                } else  {
                    $this.o.resultsposition = 'block';
                    $this.n('resultsDiv').css({
                        'position': 'static'
                    });
                    if ( $this.n('resultsAppend').length > 0  ) {
                        if ( $this.n('resultsAppend').find('.asp_r_' + $this.o.id).length > 0 ) {
                            $this.nodes.resultsDiv = $this.n('resultsAppend').find('.asp_r_' + $this.o.id);
                            if ( typeof $this.nodes.resultsDiv.get(0).referenced !== 'undefined' ) {
                                ++$this.nodes.resultsDiv.get(0).referenced;
                            } else {
                                $this.nodes.resultsDiv.get(0).referenced = 1;
                            }
                        } else {
                            $this.nodes.resultsDiv = $this.nodes.resultsDiv.clone();
                            $this.nodes.resultsAppend.append($this.nodes.resultsDiv);
                        }
                    }
                }
            }

            $this.nodes.showmore = $('.showmore', $this.nodes.resultsDiv);
            $this.nodes.items = $('.item', $this.n('resultsDiv')).length > 0 ? $('.item', $this.nodes.resultsDiv) : $('.photostack-flip', $this.nodes.resultsDiv);
            $this.nodes.results = $('.results', $this.nodes.resultsDiv);
            $this.nodes.resdrg = $('.resdrg', $this.nodes.resultsDiv);
            $this.nodes.resultsDiv.get(0).id = $this.nodes.resultsDiv.get(0).id.replace('__original__', '');
            $this.detectAndFixFixedPositioning();

            // Init infinite scroll
            $this.initInfiniteScroll();

            $this.resultsInitialized = true;
        },

        initResultsAnimations: function() {
            let $this = this,
                rpos = $this.n('resultsDiv').css('position'),
                blocking = rpos != 'fixed' && rpos != 'absolute';
            $this.resAnim = {
                "showClass": "",
                "showCSS": {
                    "visibility": "visible",
                    "display": "block",
                    "opacity": 1,
                    "animation-duration": $this.animOptions.results.dur + 'ms'
                },
                "hideClass": "",
                "hideCSS": {
                    "visibility": "hidden",
                    "opacity": 0,
                    "display": "none"
                },
                "duration": $this.animOptions.results.dur + 'ms'
            };

            if ($this.animOptions.results.anim == "fade") {
                $this.resAnim.showClass = "asp_an_fadeIn";
                $this.resAnim.hideClass = "asp_an_fadeOut";
            }

            if ( $this.animOptions.results.anim == "fadedrop" && !blocking ) {
                $this.resAnim.showClass = "asp_an_fadeInDrop";
                $this.resAnim.hideClass = "asp_an_fadeOutDrop";
            } else if ( $this.animOptions.results.anim == "fadedrop" ) {
                // If does not support transition, or it is blocking layout
                // .. fall back to fade
                $this.resAnim.showClass = "asp_an_fadeIn";
                $this.resAnim.hideClass = "asp_an_fadeOut";
            }

            $this.n('resultsDiv').css({
                "-webkit-animation-duration": $this.resAnim.duration + "ms",
                "animation-duration": $this.resAnim.duration + "ms"
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    // Top and latest searches widget
    $(".ajaxsearchprotop").each(function () {
        let params = JSON.parse( $(this).data("aspdata") ),
            id = params.id;

        if (params.action == 0) {
            $('a', $(this)).on('click', function (e) {
                e.preventDefault();
            });
        } else if (params.action == 2) {
            $('a', $(this)).on('click', function (e) {
                e.preventDefault();
                ASP.api(id, 'searchFor', $(this).html());
                $('html').animate({
                        scrollTop: $('div[id*=ajaxsearchpro' + id + '_]').first().offset().top - 40
                }, 500);
            });
        } else if (params.action == 1) {
            $('a', $(this)).on('click', function (e) {
                if ( ASP.api(id, 'exists') ) {
                    e.preventDefault();
                    return ASP.api(id, 'searchRedirect', $(this).html());
                }
            });
        }
    });
})(WPD.dom);window.ASP = typeof window.ASP !== 'undefined' ? window.ASP : {};
window.ASP.api = (function() {
    "use strict";
    let a4 = function(id, instance, func, args) {
        let s = ASP.instances.get(id, instance);
        return s !== false && s[func].apply(s, [args]);
    },
    a3 = function(id, func, args) {
        let s;
        if ( !isNaN(parseFloat(func)) && isFinite(func) ) {
            s = ASP.instances.get(id, func);
            return s !== false && s[args].apply(s);
        } else {
            s = ASP.instances.get(id);
            return s !== false && s.forEach(function(i){
                i[func].apply(i, [args]);
            });
        }
    },
    a2 = function(id, func) {
        let s;
        if ( func == 'exists' ) {
            return ASP.instances.exist(id);
        }
        s = ASP.instances.get(id);
        return s !== false && s.forEach(function(i){
            i[func].apply(i);
        });
    };
    if ( arguments.length == 4 ){
        return(
            a4.apply( this, arguments )
        );
    } else if ( arguments.length == 3 ) {
        return(
            a3.apply( this, arguments )
        );
    } else if ( arguments.length == 2 ) {
        return(
            a2.apply( this, arguments )
        );
    } else if ( arguments.length == 0 ) {
        console.log("Usage: ASP.api(id, [optional]instance, function, [optional]args);");
        console.log("For more info: https://knowledgebase.ajaxsearchpro.com/other/javascript-api");
    }
});
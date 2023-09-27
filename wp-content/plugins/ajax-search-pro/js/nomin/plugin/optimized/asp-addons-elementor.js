(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let addon = function() {
        this.name = "Elementor Widget Fixes";
        this.init = function(){
            helpers.Hooks.addFilter('asp/init/etc', this.fixElementorPostPagination, 10, this);

            helpers.Hooks.addFilter('asp/live_load/selector', this.fixSelector, 10, this);
            helpers.Hooks.addFilter('asp/live_load/url', this.url, 10, this);
            helpers.Hooks.addFilter('asp/live_load/start', this.start, 10, this);
            helpers.Hooks.addFilter('asp/live_load/replacement_node', this.fixElementorLoadMoreResults, 10, this);
            helpers.Hooks.addFilter('asp/live_load/finished', this.finished, 10, this);
        };
        this.fixSelector = function( selector ) {
            if ( selector.indexOf('asp_es_') > -1 ) {
                selector += ' .elementor-widget-container';
            }
            return selector;
        };
        this.url = function(url, obj, selector, widget) {
            // Remove initial pagination query argument on new search
            if ( url.indexOf('asp_force_reset_pagination=1') >= 0 ) {
                url = url.replace(/\?product\-page\=[0-9]+\&/, '?');
            }
            return url;
        };
        this.start = function(url, obj, selector, widget) {
            let isNewSearch = ($('form', obj.n('searchsettings')).serialize() + obj.n('text').val().trim()) != obj.lastSuccesfulSearch;
            if ( !isNewSearch && $(widget).find('.e-load-more-spinner').length > 0 ) {
                $(widget).css('opacity', 1);
            }
        };
        this.finished = function(url, obj, selector, widget) {
            let $el = $(widget);
            if (
                selector.indexOf('asp_es_') !== false &&
                typeof elementorFrontend != 'undefined' &&
                typeof elementorFrontend.init != 'undefined' &&
                $el.find('.asp_elementor_nores').length == 0
            ) {
                let widgetType = $el.parent().data('widget_type');
                if ( widgetType != '' && typeof jQuery != 'undefined' ) {
                    elementorFrontend.hooks.doAction('frontend/element_ready/' + widgetType, jQuery($el.parent().get(0)) );
                }
                // Fix Elementor Pagination
                this.fixElementorPostPagination(obj, url);

                if ( obj.o.scrollToResults.enabled ) {
                    this.scrollToResultsIfNeeded($el);
                }

                // Elementor results action
                obj.n('s').trigger("asp_elementor_results", [obj.o.id, obj.o.iid, $el.parent().get(0)], true, true);
            }
        };
        this.scrollToResultsIfNeeded = function($el) {
            let $first = $el.find('.elementor-post, .product').first();
            if ( $first.length && !$first.inViewPort(40) ) {
                $first.get(0).scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
            }
        };
        this.fixElementorPostPagination = function(obj, url) {
            let $this = obj, _this = this, $es = $('.asp_es_' + $this.o.id);
            url = typeof url == 'undefined' ? location.href : url;
            if ( $es.length > 0 ) {
                _this.elementorHideSpinner($es.get(0));
                let i = url.indexOf('?');
                if ( i >= 0 ) {
                    let queryString = url.substring(i+1);
                    if ( queryString ) {
                        queryString = queryString.replace(/&asp_force_reset_pagination=1/gmi, '');
                        if ( $es.find('.e-load-more-anchor').length > 0 && $es.find('.elementor-pagination a').length == 0 ) {
                            let handler = function(e){
                                e.preventDefault();
                                e.stopPropagation();
                                if ( !obj.searching ) {
                                    let page = $es.data('page') == '' ? 2 : parseInt($es.data('page')) + 1;
                                    let newQS = queryString.split('&page=');
                                    $es.data('page', page);
                                    $this.showLoader();
                                    _this.elementorShowSpinner($es.get(0));
                                    $this.liveLoad('.asp_es_' + $this.o.id,
                                        url.split('?')[0] + '?' + newQS[0] + '&page=' + page,
                                        false, true
                                    );
                                }
                            };
                            $es.find('.e-load-more-anchor').next('.elementor-button-wrapper').find('a').attr('href', '');
                            $es.find('.e-load-more-anchor').next('.elementor-button-wrapper').offForced().on('click', handler);
                            $es.find('.asp_e_load_more_anchor').on('asp_e_load_more', handler);
                        } else {
                            $es.find('.elementor-pagination a, .elementor-widget-container .woocommerce-pagination a').each(function() {
                                let a = $(this).attr('href');
                                if (a.indexOf('asp_ls=') < 0 && a.indexOf('asp_ls&') < 0) {
                                    if (a.indexOf('?') < 0) {
                                        $(this).attr('href', a + '?' + queryString);
                                    } else {
                                        $(this).attr('href', a + '&' + queryString);
                                    }
                                } else {
                                    // Still, make sure that the force reset pagination is not accidentally printed
                                    $(this).attr('href', $(this).attr('href').replace(/&asp_force_reset_pagination=1/gmi, ''));
                                }
                            });
                            $es.find('.elementor-pagination a, .elementor-widget-container .woocommerce-pagination a').on('click', function(e){
                                e.preventDefault();
                                e.stopImmediatePropagation();
                                e.stopPropagation();
                                $this.showLoader();
                                $this.liveLoad('.asp_es_' + $this.o.id, $(this).attr('href'), false, true);
                            });
                        }
                    }
                }
            }

            // Need to return the first argument, as this is a FILTER hook with OBJECT reference argument, and will override with the return value
            return $this;
        };
        this.fixElementorLoadMoreResults = function(replacementNode, obj, originalNode, data) {
            let settings = $(originalNode).closest('div[data-settings]').data('settings'),
                $aspLoadMoreAnchor = $(originalNode).find('.asp_e_load_more_anchor');
            if ( settings != null && settings != '' ) {
                settings = JSON.parse(settings);
                if (
                    settings.pagination_type == 'load_more_infinite_scroll' &&
                    $aspLoadMoreAnchor.length == 0
                ) {
                    $('.e-load-more-anchor').css('display', 'none');
                    $(originalNode).append('<div class="asp_e_load_more_anchor"></div>');
                    $aspLoadMoreAnchor = $(originalNode).find('.asp_e_load_more_anchor');
                    let handler = function(){
                        if ( $aspLoadMoreAnchor.inViewPort(50) ) {
                            $aspLoadMoreAnchor.trigger('asp_e_load_more');
                            $aspLoadMoreAnchor.remove();
                        }
                    };
                    obj.documentEventHandlers.push({
                        'node': window,
                        'event': 'scroll',
                        'handler': handler
                    });
                    $(window).on("scroll", handler);
                }
                if ( $(replacementNode).find('.e-load-more-spinner').length > 0 ) {
                    $(originalNode).removeClass('e-load-more-pagination-loading');
                    let isNewSearch = ($('form', obj.n('searchsettings')).serialize() + obj.n('text').val().trim()) != obj.lastSuccesfulSearch,
                        $loadMoreButton = $(originalNode).find('.e-load-more-anchor').next('.elementor-button-wrapper'),
                        $loadMoreMessage = $(originalNode).find('.e-load-more-message'),
                        $article = $(replacementNode).find('article');
                    if ( $article.length > 0 && $article.parent().length > 0 && $(originalNode).find('article').parent().length > 0 ) {
                        let newData = $article.get(0).innerHTML,
                            previousData = $(originalNode).data('asp-previous-data');
                        if (previousData == '' || isNewSearch) {
                            $(originalNode).find('article').parent().get(0).innerHTML = newData;
                            $(originalNode).data('asp-previous-data', newData);
                            $loadMoreButton.css('display', 'block');
                            $loadMoreMessage.css('display', 'none');
                        } else if (previousData == newData) {
                            $loadMoreButton.css('display', 'none');
                            $loadMoreMessage.css('display', 'block');
                            $aspLoadMoreAnchor.remove();
                        } else {
                            $(originalNode).find('article').parent().get(0).innerHTML += newData;
                            $(originalNode).data('asp-previous-data', newData);
                        }
                    } else {
                        $loadMoreButton.css('display', 'none');
                        $loadMoreMessage.css('display', 'block');
                        $aspLoadMoreAnchor.remove();
                    }
                    return null;
                }
            }

            return replacementNode;
        };
        this.elementorShowSpinner = function(widget) {
            $(widget).addClass('e-load-more-pagination-loading');
            $(widget).find('.e-load-more-spinner>*').addClass('eicon-animation-spin');
            $(widget).css('opacity', 1);
        };
        this.elementorHideSpinner = function(widget) {
            $(widget).removeClass('e-load-more-pagination-loading');
            $(widget).find('.eicon-animation-spin').removeClass('eicon-animation-spin');
        };
    }
    window.WPD.ajaxsearchpro.addons.add(new addon());
})(WPD.dom);
(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        showIsotopicResults: function () {
            let $this = this;

            // When re-opening existing results, just stop here
            if ( $this._no_animations == true ) {
                $this.showResultsBox();
                $this.addAnimation();
                $this.searching = false;
                return true;
            }

            $this.preProcessIsotopicResults();
            $this.showResultsBox();

            if ($this.n('items').length > 0) {
                $this.n('results').css({
                    height: "auto"
                });
                if ($this.o.highlight == 1) {
                    // noinspection JSUnresolvedVariable
                    $("div.item", $this.n('resultsDiv')).highlight($this.n('text').val().split(" "), {
                        element: 'span', className: 'highlighted', wordsOnly: $this.o.highlightWholewords
                    });
                }
            }

            if ( $this.call_num == 0 )
                $this.calculateIsotopeRows();

            $this.showPagination();
            $this.isotopicPagerScroll();

            if ($this.n('items').length == 0) {
                $this.n('results').css({
                    height: '11110px'
                });
                $this.n('results').css({
                    height: 'auto'
                });
                $this.n('resdrg').css({
                    height: 'auto'
                });
            } else {
                // Initialize the main
                if (typeof rpp_isotope !== 'undefined') {
                    if ( $this.isotopic != null && typeof $this.isotopic.destroy != 'undefined' && $this.call_num == 0 )
                        $this.isotopic.destroy();

                    if ( $this.call_num == 0 || $this.isotopic == null ) {
                        // noinspection JSPotentiallyInvalidConstructorUsage
                        $this.isotopic = new rpp_isotope('#ajaxsearchprores' + $this.o.rid + " .resdrg", {
                            // options
                            isOriginLeft: !$('body').hasClass('rtl'),
                            itemSelector: 'div.item',
                            layoutMode: 'masonry',
                            filter: $this.filterFns['number'],
                            masonry: {
                                "gutter": $this.o.isotopic.gutter
                            }
                        });
                    }
                } else {
                    // Isotope is not included within the scripts, alert the user!
                    return false;
                }
            }
            $this.addAnimation();
            $this.initIsotopicClick();
            $this.searching = false;
        },
        initIsotopicClick: function(){
            let $this = this;
            $this.eh.isotopicClickhandle = $this.eh.isotopicClickhandle || function(e) {
                if ( !$this.dragging ) {
                    let $a = $(this).find('.asp_content a.asp_res_url');
                    let url = $a.attr('href');
                    if (url !== '') {
                        e.preventDefault();
                        if ( e.which == 2 || $a.attr('target') == '_blank' ) {
                            helpers.openInNewTab(url);
                        } else {
                            location.href = url;
                        }
                    }
                }
            }
            $this.n('resultsDiv').find('.asp_isotopic_item').on('click', $this.eh.isotopicClickhandle);
        },
        preProcessIsotopicResults: function() {
            let $this = this,
                j = 0,
                overlay = "";

            // In some cases the hidden data is not present for some reason..
            if ($this.o.isotopic.showOverlay && $this.n('aspItemOverlay').length > 0)
                overlay = $this.n('aspItemOverlay').get(0).outerHTML;

            $this.n('items').forEach(function (el) {

                let image = "",
                    overlayImage = "",
                    hasImage = $(el).find('.asp_image').length > 0,
                    $img = $(el).find('.asp_image');

                if (hasImage) {
                    // noinspection JSUnresolvedVariable
                    let src = $img.data('src'),
                        filter = $this.o.isotopic.blurOverlay && !helpers.isMobile() ? "aspblur" : "no_aspblur";

                    overlayImage = $("<div data-src='"+src+"' ></div>");
                    if ( typeof WPD.lazy == 'undefined' ) {
                        overlayImage.css({
                            "background-image": "url(" + src + ")"
                        });
                    }
                    overlayImage.css({
                        "filter": "url(#" + filter + ")",
                        "-webkit-filter": "url(#" + filter + ")",
                        "-moz-filter": "url(#" + filter + ")",
                        "-o-filter": "url(#" + filter + ")",
                        "-ms-filter": "url(#" + filter + ")"
                    }).addClass('asp_item_overlay_img asp_lazy');
                    overlayImage = overlayImage.get(0).outerHTML;
                }

                $(el).prepend(overlayImage + overlay + image);
                $(el).attr('data-itemnum', j);

                j++;
            });

        },
        isotopicPagerScroll: function () {
            let $this = this;

            if ( $('nav>ul li.asp_active', $this.n('resultsDiv')).length <= 0 )
                return false;

            let $activeLeft = $('nav>ul li.asp_active', $this.n('resultsDiv')).offset().left,
                $activeWidth = $('nav>ul li.asp_active', $this.n('resultsDiv')).outerWidth(true),
                $nextLeft = $('nav>a.asp_next', $this.n('resultsDiv')).offset().left,
                $prevLeft = $('nav>a.asp_prev', $this.n('resultsDiv')).offset().left;

            if ( $activeWidth <= 0) return;

            let toTheLeft = Math.ceil( ( $prevLeft - $activeLeft + 2 * $activeWidth ) / $activeWidth );
            if (toTheLeft > 0) {
                // If the active is the first, go to the beginning
                if ( $('nav>ul li.asp_active', $this.n('resultsDiv')).prev().length == 0) {
                    $('nav>ul', $this.n('resultsDiv')).css({
                        "left": $activeWidth + "px"
                    });
                    return;
                }

                // Otherwise go left
                $('nav>ul', $this.n('resultsDiv')).css({
                    "left": $('nav>ul', $this.n('resultsDiv')).position().left  +( $activeWidth * toTheLeft) + "px"
                });
            } else {
                let toTheRight;
                // One step if it is the last element, 2 steps for any other
                if ( $('nav>ul li.asp_active', $this.n('resultsDiv')).next().length == 0 ) {
                    toTheRight = Math.ceil(($activeLeft - $nextLeft + $activeWidth) / $activeWidth);
                } else {
                    toTheRight = Math.ceil(($activeLeft - $nextLeft + 2 * $activeWidth) / $activeWidth);
                }

                if (toTheRight > 0) {
                    $('nav>ul', $this.n('resultsDiv')).css({
                        "left": $('nav>ul', $this.n('resultsDiv')).position().left -( $activeWidth * toTheRight) + "px"
                    });
                }
            }
        },
        showPagination: function ( force_refresh ) {
            let $this = this;
            force_refresh = typeof force_refresh !== 'undefined' ? force_refresh : false;

            if ( !$this.o.isotopic.pagination ) {
                // On window resize event, simply rearrange without transition
                if ( $this.isotopic != null && force_refresh )
                    $this.isotopic.arrange({
                        transitionDuration: 0,
                        filter: $this.filterFns['number']
                    });
                return false;
            }

            if ( $this.call_num < 1 || force_refresh)
                $('nav.asp_navigation ul li', $this.n('resultsDiv')).remove();
            $('nav.asp_navigation', $this.n('resultsDiv')).css('display', 'none');

            //$('nav.asp_navigation ul', $this.n('resultsDiv')).removeAttr("style");

            if ($this.n('items').length > 0) {
                let start = 1;
                if ($this.call_num > 0 && !force_refresh) {
                    // Because the nav can be both top and bottom, make sure to get only 1 to calculate, not both
                    start = $this.n('resultsDiv').find('nav.asp_navigation ul').first().find('li').length + 1;
                }
                let pages = Math.ceil($this.n('items').length / $this.il.itemsPerPage);
                if (pages > 1) {

                    // Calculate which page to activate, after a possible orientation change
                    let newPage = force_refresh && $this.il.lastVisibleItem > 0 ? Math.ceil($this.il.lastVisibleItem/$this.il.itemsPerPage) : 1;
                    newPage = newPage <= 0 ? 1 : newPage;

                    for (let i = start; i <= pages; i++) {
                        if (i == newPage)
                            $('nav.asp_navigation ul', $this.n('resultsDiv')).append("<li class='asp_active'><span>" + i + "</span></li>");
                        else
                            $('nav.asp_navigation ul', $this.n('resultsDiv')).append("<li><span>" + i + "</span></li>");
                    }
                    $('nav.asp_navigation', $this.n('resultsDiv')).css('display', 'block');

                    /**
                     * Always trigger the pagination!
                     * This will make sure that the isotope.arrange method is triggered in this case as well.
                     */
                    if ( force_refresh )
                        $('nav.asp_navigation ul li.asp_active', $this.n('resultsDiv')).trigger('click_trigger');
                    else
                        $('nav.asp_navigation ul li.asp_active', $this.n('resultsDiv')).trigger('click');

                } else {
                    // No pagination, but the pagination is enabled
                    // On window resize event, simply rearrange without transition
                    if ( $this.isotopic != null && force_refresh )
                        $this.isotopic.arrange({
                            transitionDuration: 0,
                            filter: $this.filterFns['number']
                        });
                }
            }
        },

        hidePagination: function () {
            let $this = this;
            $('nav.asp_navigation', $this.n('resultsDiv')).css('display', 'none');
        },

        visiblePagination: function() {
            let $this = this;
            return $('nav.asp_navigation', $this.n('resultsDiv')).css('display') != 'none';
        },

        calculateIsotopeRows: function () {
            let $this = this,
                itemWidth, itemHeight,
                containerWidth = parseFloat($this.n('results').width());

            if ( helpers.deviceType() === 'desktop' ) {
                // noinspection JSUnresolvedVariable
                itemWidth = helpers.getWidthFromCSSValue($this.o.isotopic.itemWidth, containerWidth);
                // noinspection JSUnresolvedVariable
                itemHeight = helpers.getWidthFromCSSValue($this.o.isotopic.itemHeight, containerWidth);
            } else if ( helpers.deviceType() === 'tablet' ) {
                // noinspection JSUnresolvedVariable
                itemWidth = helpers.getWidthFromCSSValue($this.o.isotopic.itemWidthTablet, containerWidth);
                // noinspection JSUnresolvedVariable
                itemHeight = helpers.getWidthFromCSSValue($this.o.isotopic.itemHeightTablet, containerWidth);
            } else {
                // noinspection JSUnresolvedVariable
                itemWidth = helpers.getWidthFromCSSValue($this.o.isotopic.itemWidthPhone, containerWidth);
                // noinspection JSUnresolvedVariable
                itemHeight = helpers.getWidthFromCSSValue($this.o.isotopic.itemHeightPhone, containerWidth);
            }
            let realColumnCount = containerWidth / itemWidth,
                gutterWidth = $this.o.isotopic.gutter,
                floorColumnCount = Math.floor(realColumnCount);
            if (floorColumnCount <= 0)
                floorColumnCount = 1;

            if (Math.abs(containerWidth / floorColumnCount - itemWidth) >
                Math.abs(containerWidth / (floorColumnCount + 1) - itemWidth)) {
                floorColumnCount++;
            }

            let newItemW = containerWidth / floorColumnCount - ( (floorColumnCount-1) * gutterWidth  / floorColumnCount ),
                newItemH = (newItemW / itemWidth) * itemHeight;

            $this.il.columns = floorColumnCount;
            $this.il.itemsPerPage = floorColumnCount * $this.il.rows;
            $this.il.lastVisibleItem = 0;
            $this.n('results').find('.asp_isotopic_item').forEach(function(el, index){
                if ( $(el).css('display') != 'none' ) {
                    $this.il.lastVisibleItem = index;
                }
            });

            // This data needs do be written to the DOM, because the isotope arrange can't see the changes
            if ( !isNaN($this.il.columns) && !isNaN($this.il.itemsPerPage) ) {
                $this.n('resultsDiv').data("colums", $this.il.columns);
                $this.n('resultsDiv').data("itemsperpage", $this.il.itemsPerPage);
            }

            $this.currentPage = 1;

            $this.n('items').css({
                width: Math.floor(newItemW) + 'px',
                height: Math.floor(newItemH) + 'px'
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        initIsotopicPagination: function () {
            let $this = this;
            $this.n('resultsDiv').on($this.clickTouchend + ' click_trigger', 'nav>a', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                let $li = $(this).closest('nav').find('li.asp_active');
                let direction = $(this).hasClass('asp_prev') ? 'prev' : 'next';
                if ( direction == "next"  ) {
                    if ( $li.next('li').length > 0 ) {
                        $li.next('li').trigger('click');
                    } else {
                        $(this).closest('nav').find('li').first().trigger('click');
                    }
                } else {
                    if ( $li.prev('li').length > 0 ) {
                        $li.prev('li').trigger('click');
                    } else {
                        $(this).closest('nav').find('li').last().trigger('click');
                    }
                }
            });
            $this.n('resultsDiv').on($this.clickTouchend + ' click_trigger', 'nav>ul li', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                let etype = e.type,
                    _this = this,
                    timeout = 1;
                if ( helpers.isMobile() ) {
                    $this.n('text').trigger('blur');
                    timeout = 300;
                }
                setTimeout( function() {
                    $this.currentPage = parseInt($(_this).find('span').html(), 10);
                    $('nav>ul li', $this.n('resultsDiv')).removeClass('asp_active');
                    $('nav', $this.n('resultsDiv')).each(function (el) {
                        $($(el).find('ul li').get($this.currentPage - 1)).addClass('asp_active');
                    });
                    if ( etype === 'click_trigger' ) {
                        $this.isotopic.arrange({
                            transitionDuration: 0,
                            filter: $this.filterFns['number']
                        });
                    } else {
                        $this.isotopic.arrange({
                            transitionDuration: 400,
                            filter: $this.filterFns['number']
                        });
                    }
                    $this.isotopicPagerScroll();
                    $this.removeAnimation();

                    // Trigger lazy load refresh
                    if ( typeof WPD.lazy != 'undefined' ) {
                        document.dispatchEvent(new Event('wpd-lazy-trigger'));
                    }

                    $this.n('resultsDiv').trigger('nav_switch');
                }, timeout);
            });
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);
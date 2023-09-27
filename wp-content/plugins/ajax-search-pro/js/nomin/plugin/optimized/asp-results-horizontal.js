(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        showHorizontalResults: function () {
            let $this = this;

            $this.showResultsBox();

            $this.n('items').css("opacity", $this.animationOpacity);

            if ($this.o.resultsposition == 'hover') {
                $this.n('resultsDiv').css(
                    'width',
                    //($this.n('search').width() - ($this.n('resultsDiv').outerWidth(true) - $this.n('resultsDiv').innerWidth())) + 'px'
                    ($this.n('search').width() - ($this.n('resultsDiv').outerWidth(true) - $this.n('resultsDiv').width())) + 'px'
                );
            }

            // noinspection JSUnresolvedVariable
            if ($this.n('items').length > 0 && $this.o.scrollBar.horizontal.enabled ) {
                let el_m = parseInt($this.n('items').css("marginLeft")),
                    el_w = $this.n('items').outerWidth() + el_m * 2;
                $this.n('resdrg').css("width", $this.n('items').length * el_w + el_m * 2 + "px");
            } else {
                $this.n('results').css("overflowX", "hidden");
                $this.n('resdrg').css("width", "auto");
            }

            if ($this.o.highlight == 1) {
                // noinspection JSUnresolvedVariable
                $("div.item", $this.n('resultsDiv')).highlight(
                    $this.n('text').val().split(" "),
                    { element: 'span', className: 'highlighted', wordsOnly: $this.o.highlightWholewords == 1 }
                );
            }

            if ( $this.call_num < 1 ) {
                // Scroll to the beginning
                let $container = $this.n('results');
                $container.get(0).scrollLeft = 0;

                // noinspection JSUnresolvedVariable
                if ( $this.o.scrollBar.horizontal.enabled ) {
                    let prevDelta = 0,
                        prevTime = Date.now();
                    $container.off('mousewheel');
                    $container.on('mousewheel', function (e) {
                        let deltaFactor = typeof e.deltaFactor != 'undefined' ? e.deltaFactor : 65,
                            delta = e.deltaY > 0 ? 1 : -1,
                            diff = Date.now() - prevTime,
                            speed = diff > 100 ? 1 : 3 - (2 * diff / 100);
                        if (prevDelta != e.deltaY)
                            speed = 1;
                        $(this).animate(false).animate({
                            "scrollLeft": this.scrollLeft + (delta * deltaFactor * 2 * speed)
                        }, 250, "easeOutQuad");
                        prevDelta = e.deltaY;
                        prevTime = Date.now();
                        if (
                            !(
                                (helpers.isScrolledToRight($container.get(0)) && delta == 1) ||
                                (helpers.isScrolledToLeft($container.get(0)) && delta == -1)
                            )
                        )
                            e.preventDefault();
                    });
                }
            }

            $this.showResultsBox();
            $this.addAnimation();
            $this.searching = false;
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);
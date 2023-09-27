(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let functions = {
        openCompact: function() {
            let $this = this;

            if ( !$this.n('search').is("[data-asp-compact-w]") ) {
                $this.n('probox').attr('data-asp-compact-w', $this.n('probox').innerWidth());
                $this.n('search').attr('data-asp-compact-w', $this.n('search').innerWidth());
            }

            $this.n('search').css({
                "width": $this.n('search').width() + 'px'
            });

            $this.n('probox').css({width: "auto"});

            // halftime delay on showing the input, etc.. for smoother animation
            setTimeout(function(){
                $this.n('search').find('.probox>div:not(.promagnifier)').removeClass('hiddend');
            }, 80);

            // Clear this timeout first, in case of fast clicking..
            clearTimeout($this.timeouts.compactBeforeOpen);
            $this.timeouts.compactBeforeOpen = setTimeout(function(){
                let width;
                if ( helpers.deviceType() == 'phone' ) {
                    // noinspection JSUnresolvedVariable
                    width = $this.o.compact.width_phone;
                } else if ( helpers.deviceType() == 'tablet' ) {
                    // noinspection JSUnresolvedVariable
                    width = $this.o.compact.width_tablet;
                } else {
                    width = $this.o.compact.width;
                }

                width = helpers.Hooks.applyFilters('asp_compact_width', width, $this.o.id, $this.o.iid);
                width = helpers.wp_hooks_apply_filters('asp_compact_width', width, $this.o.id, $this.o.iid);

                width = !isNaN(width) ? width + 'px' : width;
                if ( $this.o.compact.position != 'static' ) {
                    $this.n('search').css({
                        "max-width": width,
                        "width": width
                    });
                } else {
                    $this.n('container').css({
                        "max-width": width,
                        "width": width
                    });
                    $this.n('search').css({
                        "max-width": '100%',
                        "width": '100%'
                    });
                }

                if ($this.o.compact.overlay == 1) {
                    $this.n('search').css('z-index', 999999);
                    $this.n('searchsettings').css('z-index', 999999);
                    $this.n('resultsDiv').css('z-index', 999999);
                    $this.n('trythis').css('z-index', 999998);
                    $('#asp_absolute_overlay').css({
                        'opacity': 1,
                        'width': "100%",
                        "height": "100%",
                        "z-index": 999990
                    });
                }

                $this.n('search').attr('data-asp-compact', 'open');
            }, 50);

            // Clear this timeout first, in case of fast clicking..
            clearTimeout($this.timeouts.compactAfterOpen);
            $this.timeouts.compactAfterOpen = setTimeout(function(){
                $this.resize();
                $this.n('trythis').css({
                    display: 'block'
                });
                if ($this.o.compact.enabled == 1 && $this.o.compact.position != 'static') {
                    $this.n('trythis').css({
                        top: ( $this.n('search').offset().top + $this.n('search').outerHeight(true) ) + 'px',
                        left: $this.n('search').offset().left + 'px'
                    });
                }
                if ( $this.o.compact.focus ) {
                    $this.n('text').get(0).focus();
                }
                $this.n('text').trigger('focus');
                $this.scrolling();
            }, 500);
        },

        closeCompact: function() {
            let $this = this;

            /**
             * Clear every timeout from the opening script to prevent issues
             */
            clearTimeout($this.timeouts.compactBeforeOpen);
            clearTimeout($this.timeouts.compactAfterOpen);

            $this.timeouts.compactBeforeOpen = setTimeout(function(){
                $this.n('search').attr('data-asp-compact', 'closed');
            }, 50);

            $this.n('search').find('.probox>div:not(.promagnifier)').addClass('hiddend');

            //$this.n('search').css({width: "auto"});
             if ( $this.o.compact.position != 'static' ) {
                $this.n('search').css({width: "auto"});
            } else {
                $this.n('container').css({width: "auto"});
                $this.n('search').css({
                    "max-width": 'unset',
                    "width": 'auto'
                });
            }

            $this.n('probox').css({width: $this.n('probox').attr('data-asp-compact-w') + 'px'});

            $this.n('trythis').css({
                left: $this.n('search').position().left,
                display: "none"
            });


            if ($this.o.compact.overlay == 1) {
                $this.n('search').css('z-index', '');
                $this.n('searchsettings').css('z-index', '');
                $this.n('resultsDiv').css('z-index', '');
                $this.n('trythis').css('z-index', '');
                $('#asp_absolute_overlay').css({
                    'opacity': 0,
                    'width': 0,
                    "height": 0,
                    "z-index": 0
                });
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let functions = {
        initCompactEvents: function () {
            let $this = this,
                scrollTopx = 0;

            $this.n('promagnifier').on('click', function(){
                let compact = $this.n('search').attr('data-asp-compact')  || 'closed';

                scrollTopx = window.scrollY;
                $this.hideSettings?.();
                $this.hideResults();

                if (compact == 'closed') {
                    $this.openCompact();
                    $this.n('text').trigger('focus');
                } else {
                    // noinspection JSUnresolvedVariable
                    if ($this.o.compact.closeOnMagnifier != 1) return;
                    $this.closeCompact();
                    $this.searchAbort();
                    $this.n('proloading').css('display', 'none');
                }
            });

        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);(function($){
    "use strict";
    let functions = {
        initCompact: function() {
            let $this = this;

            // Reset the overlay no matter what, if the is not fixed
            if ( $this.o.compact.enabled == 1 && $this.o.compact.position != 'fixed' )
                $this.o.compact.overlay = 0;

            if ( $this.o.compact.enabled == 1 )
                $this.n('trythis').css({
                    display: "none"
                });

            if ( $this.o.compact.enabled == 1 && $this.o.compact.position == 'fixed' ) {

                /**
                 * If the conditional CSS loader is enabled, the required
                 * search CSS file is not present when this code is executed.
                 */
                window.WPD.intervalUntilExecute(function(){
                    let $body = $('body');
                    // Save the container element, otherwise it will get lost
                    $this.nodes['container'] = $this.n('search').closest('.asp_w_container');
                    $body.append( $this.n('search').detach() );
                    $body.append( $this.n('trythis').detach() );
                    // Fix the container position to a px value, even if it is set to % value initially, for better compatibility
                    $this.n('search').css({
                        top: ( $this.n('search').position().top ) + 'px'
                    });
                },  function() {
                    return $this.n('search').css('position') == "fixed"
                });
            }
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);
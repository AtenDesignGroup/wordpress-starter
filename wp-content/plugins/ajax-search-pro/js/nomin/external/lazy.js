(function(){
    window.WPD = window.WPD || {};
    WPD.lazy = function() {
        if ( typeof WPD.lazy.fn == "undefined" ) {
            WPD.lazy.fn = {
                initialized: false,
                elAttached: false,
                n: [],
                init: function () {
                    if ( !this.initialized ) {
                        this.events();
                        this.initialized = true;
                    }
                    document.removeEventListener('wpd-dom-viewport-loaded', WPD.lazy.fn.init);
                    this.handler();
                },
                events: function () {
                    document.addEventListener('scroll', this.handler.bind(WPD.lazy.fn), {passive: true});
                    document.addEventListener('resize', this.handler.bind(WPD.lazy.fn), {passive: true});
                    document.addEventListener('wpd-lazy-trigger', this.handler.bind(WPD.lazy.fn), {passive: true});
                },
                handler: function () {
                    let $ = WPD.dom;
                    $(this.n.join(',')).each(function (el) {
                        if (
                            (typeof el._wpd_lazy_loaded == "undefined" ) &&
                            $(el).inViewPort(-50)
                        ) {
                            if ( el.tagName.toLowerCase() == 'img' ) {
                                el.src = $(el).attr('data-src');
                            } else {
                                el.style.backgroundImage = "url('" + $(el).attr('data-src') + "')";
                            }
                            el._wpd_lazy_loaded = true;
                        }
                    });
                }
            };
        }

        if ( arguments.length == 1 ) {
            if ( WPD.lazy.fn.n.indexOf(arguments[0]) === -1 ) {
                WPD.lazy.fn.n.push(arguments[0]);
            }
        }


        if (typeof WPD.dom == 'undefined' || typeof WPD.dom.fn.inViewPort == 'undefined') {
            if ( !WPD.lazy.fn.elAttached ) {
                console.log('WPD Lazy: waiting for dependency to load.');
                document.addEventListener('wpd-dom-viewport-loaded', WPD.lazy.fn.init.bind(WPD.lazy.fn));
                WPD.lazy.fn.elAttached = true;
            }
        } else {
            WPD.lazy.fn.init();
        }
    };
    document.dispatchEvent(new Event('wpd-lazy-loaded'));
}());
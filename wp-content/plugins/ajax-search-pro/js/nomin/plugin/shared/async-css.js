(function(){
    "use strict";
    /* exported loadCSS */
    window.WPD = window.WPD || {};
    /**
     *
     * @param href the URL for your CSS file.
     * @param {Element} [before=] element the script should use as a reference for injecting our stylesheet <link> before
     * @param {string} [media='all'] media type or query of the stylesheet. By default it will be 'all'
     * @returns {HTMLLinkElement}
     */
    window.WPD.loadCSS = function( href, before, media ){
        let doc = window.document,
            ss = doc.createElement( "link" ),
            ref, refs, sheets;
        if( before ){
            ref = before;
        }
        else {
            refs = ( doc.body || doc.getElementsByTagName( "head" )[ 0 ] ).childNodes;
            ref = refs[ refs.length - 1];
        }

        sheets = doc.styleSheets;
        ss.rel = "stylesheet";
        ss.href = href;
        ss.href = ss.href.replace('https://', '//');
        // noinspection HttpUrlsUsage
        ss.href = ss.href.replace('http://', '//');
        // temporarily set media to something inapplicable to ensure it'll fetch without blocking render
        ss.media = "only x";

        // wait until body is defined before injecting link. This ensures a non-blocking load in IE11.
        function ready( cb ){
            if( doc.body ){
                return cb();
            }
            setTimeout(function(){
                ready( cb );
            });
        }
        // Inject link
        // Note: the ternary preserves the existing behavior of "before" argument, but we could choose to change the argument to "after" in a later release and standardize on ref.nextSibling for all refs
        // Note: `insertBefore` is used instead of `appendChild`, for safety re: http://www.paulirish.com/2011/surefire-dom-element-insertion/
        ready( function(){
            ref.parentNode.insertBefore( ss, ( before ? ref : ref.nextSibling ) );
        });
        // A method (exposed on return object for external use) that mimics onload by polling document.styleSheets until it includes the new sheet.
        let onloadcssdefined = function( cb ){
            let resolvedHref = ss.href,
                i = sheets.length;
            while( i-- ){
                if( sheets[ i ].href === resolvedHref ){
                    return cb();
                }
            }
            setTimeout(function() {
                onloadcssdefined( cb );
            });
        };

        function loadCB(){
            if( ss.addEventListener ){
                ss.removeEventListener( "load", loadCB );
            }
            ss.media = media || "all";
        }

        // once loaded, set link's media back to `all` so that the stylesheet applies once it loads
        if( ss.addEventListener ){
            ss.addEventListener( "load", loadCB);
        }
        ss.onloadcssdefined = onloadcssdefined;
        onloadcssdefined( loadCB );
        return ss;
    };
}());// noinspection JSUnresolvedVariable

(function(){
    "use strict";
    let loadCSS = function() {
        let arr = [],
            els = document.getElementsByClassName("asp_m"),
            media_query = "def";
        for (let i = 0; i < els.length; i++) {
            if (typeof els[i].dataset != 'undefined') {
                arr[els[i].dataset.id] = true;
            }
        }

        if ( typeof ASP.media_query != "undefined" ) {
            media_query = ASP.media_query;
        }

        // If any active instances were found, load the basic JS
        if (arr.length > 0) {
            window.WPD.loadCSS(ASP.css_basic_url + "?mq=" + media_query);

            // Parse through and load only the required CSS files
            let last;
            for (let i = 0; i < arr.length; i++) {
                if (typeof arr[i] != "undefined") {
                    last = window.WPD.loadCSS(ASP.upload_url + "search" + i + ".css?mq=" + media_query);
                }
            }
            last.onload = function () {
                document.head.insertAdjacentHTML("beforeend", '<style>body .wpdreams_asp_sc{display: block; max-height: none; overflow: visible;}</style>');
                window.ASP.css_loaded = true;
            };
        }
    }
    if ( typeof ASP != 'undefined' && typeof ASP.css_basic_url != 'undefined' ) {
        loadCSS();
    } else {
        let t, i = 0;
        t = setInterval(function(){
            ++i;
            if ( typeof ASP != 'undefined' && typeof ASP.css_basic_url != 'undefined' ) {
                loadCSS();
                clearInterval(t);
            }
            if ( i > 50 ) {
                clearInterval(t);
            }
        }, 100);
    }
}());
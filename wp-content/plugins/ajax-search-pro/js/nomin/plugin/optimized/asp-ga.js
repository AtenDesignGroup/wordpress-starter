(function($){
    "use strict";
    let functions = {
        gaEvent: function(which, data) {
            let $this = this;
            let tracking_id = $this.gaGetTrackingID();
            // noinspection JSUnresolvedVariable
            if ( typeof ASP.analytics == 'undefined' || ASP.analytics.method != 'event' )
                return false;

            // Get the scope
            let _gtag = typeof gtag == "function" ? gtag : false;

            if ( _gtag === false && typeof window.dataLayer == 'undefined' )
                return false;

            // noinspection JSUnresolvedVariable
            if (
                typeof (ASP.analytics.event[which]) != 'undefined' &&
                ASP.analytics.event[which].active == 1
            ) {
                let def_data = {
                    "search_id": $this.o.id,
                    "search_name": $this.n('search').data('name'),
                    "phrase": $this.n('text').val(),
                    "option_name": '',
                    "option_value": '',
                    "result_title": '',
                    "result_url": '',
                    "results_count": ''
                };
                // noinspection JSUnresolvedVariable
                let event = {
                    'event_category': ASP.analytics.event[which].category,
                    'event_label': ASP.analytics.event[which].label,
                    'value': ASP.analytics.event[which].value
                };
                data = $.fn.extend(def_data, data);
                Object.keys(data).forEach(function (k) {
                    let v = data[k];
                    v = String(v).replace(/[\s\n\r]+/g, " ").trim();
                    Object.keys(event).forEach(function (kk) {
                        let regex = new RegExp('\{' + k + '\}', 'gmi');
                        event[kk] = event[kk].replace(regex, v);
                    });
                });
                if ( _gtag !== false ) {
                    if ( tracking_id !== false ) {
                        tracking_id.forEach(function(id){
                            event.send_to = id;
                            // noinspection JSUnresolvedVariable
                            _gtag('event', ASP.analytics.event[which].action, event);
                        });
                    } else {
                        // noinspection JSUnresolvedVariable
                        _gtag('event', ASP.analytics.event[which].action, event);
                    }
                } else if ( typeof window.dataLayer.push != 'undefined' ) {
                    window.dataLayer.push({
                        'event': 'asp_event',
                        'event_name': ASP.analytics.event[which].action,
                        'event_category': event.event_category,
                        'event_label': event.event_label,
                        'event_value': event.value
                    });
                }
            }
        },

        gaGetTrackingID: function() {
            let ret = false;
            // noinspection JSUnresolvedVariable
            if ( typeof ASP.analytics == 'undefined' )
                return ret;

            // noinspection JSUnresolvedVariable
            if ( typeof ASP.analytics.tracking_id != 'undefined' && ASP.analytics.tracking_id != '' ) {
                // noinspection JSUnresolvedVariable
                return [ASP.analytics.tracking_id];
            } else {
                // GTAG bypass pageview tracking method
                let _gtag = typeof window.gtag == "function" ? window.gtag : false;
                if ( _gtag === false && typeof window.ga != 'undefined' && typeof window.ga.getAll != 'undefined' ) {
                    let id = [];
                    window.ga.getAll().forEach( function(tracker) {
                        id.push( tracker.get('trackingId') );
                    });
                    return id.length > 0 ? id : false;
                }
            }

            return ret;
        }
    }
    $.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
})(WPD.dom);
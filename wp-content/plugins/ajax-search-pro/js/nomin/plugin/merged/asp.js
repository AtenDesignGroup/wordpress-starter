(function(){
    "use strict";

    const version = 1;

    window.WPD = typeof window.WPD !== 'undefined' ? window.WPD : {};

    if ( typeof WPD.dom != "undefined" ) {
        return false;	// Terminate
    }

    WPD.dom = function() {
        if ( typeof WPD.dom.fn == "undefined" || typeof WPD.dom.fn.a == "undefined") {
            WPD.dom.fn = {
                a: [],
                is_wpd_dom: true,
                length: 0,
                get: function (n) {
                    return typeof n == "undefined" ? this.a.slice() : (typeof this.a[n] != 'undefined' ? this.a[n] : null);
                },
                _: function (s) {
                    if ( s.charAt(0) === '<' ) {
                        return WPD.dom._fn.createElementsFromHTML(s);
                    }
                    return Array.prototype.slice.call(document.querySelectorAll(s));
                },
                $: function (s, $node) {
                    let _this = this.copy(this, true);
                    if ( typeof $node != "undefined" ) {
                        _this.a = $node !== null ? $node.find(s).get() : [];
                    } else {
                        if (typeof s == "string") {
                            _this.a = _this._(s);
                        } else {
                            _this.a = s!== null ? [s] : [];
                        }
                    }
                    _this.length = _this.a.length;
                    return _this;
                },
                extend: function () {
                    for (let i = 1; i < arguments.length; i++)
                        for (let key in arguments[i])
                            if (arguments[i].hasOwnProperty(key))
                                arguments[0][key] = arguments[i][key];
                    return arguments[0];
                },
                copy: function(source, deep) {
                    let o, prop, type;
                    if (typeof source != 'object' || source === null) {
                        // What do to with functions, throw an error?
                        o = source;
                        return o;
                    }
                    o = new source.constructor();
                    for (prop in source) {
                        if (source.hasOwnProperty(prop)) {
                            type = typeof source[prop];
                            if (deep && type === 'object' && source[prop] !== null) {
                                o[prop] = this.copy(source[prop]);
                            } else {
                                o[prop] = source[prop];
                            }
                        }
                    }
                    return o;
                },
                parent: function (s) {
                    let el = this.get(0);
                    let _this = this.copy(this, true);
                    _this.a = [];
                    if (el != null) {
                        el = el.parentElement;
                        if (typeof s != 'undefined') {
                            if (el.matches(s)) {
                                _this.a = [el];
                            }
                        } else {
                            _this.a = el == null ? [] : [el];
                        }
                        return _this;
                    }
                    return _this;
                },
                first: function () {
                    let _this = this.copy(this, true);
                    _this.a = typeof _this.a[0] != 'undefined' ? [_this.a[0]] : [];
                    _this.length = _this.a.length;
                    return _this;
                },
                last: function () {
                    let _this = this.copy(this, true);
                    _this.a = _this.a.length > 0 ? [_this.a[_this.a.length - 1]] : [];
                    _this.length = _this.a.length;
                    return _this;
                },
                prev: function (s) {
                    let _this = this.copy(this, true);
                    if ( typeof s == "undefined" ) {
                        _this.a = typeof _this.a[0] != 'undefined' && _this.a[0].previousElementSibling != null ?
                            [_this.a[0].previousElementSibling] : [];
                    } else {
                        if ( typeof _this.a[0] != 'undefined' ) {
                            let n = _this.a[0].previousElementSibling;
                            _this.a = [];
                            while ( n != null ) {
                                if ( n.matches(s) ) {
                                    _this.a = [n];
                                    break;
                                }
                                n = n.previousElementSibling;
                            }
                        }
                    }
                    _this.length = _this.a.length;
                    return _this;
                },
                next: function (s) {
                    let _this = this.copy(this, true);
                    if ( typeof s == "undefined" ) {
                        _this.a = typeof _this.a[0] != 'undefined' && _this.a[0].nextElementSibling != null ?
                            [_this.a[0].nextElementSibling] : [];
                    } else {
                        if ( typeof _this.a[0] != 'undefined' ) {
                            let n = _this.a[0].nextElementSibling;
                            _this.a = [];
                            while ( n != null ) {
                                if ( n.matches(s) ) {
                                    _this.a = [n];
                                    break;
                                }
                                n = n.nextElementSibling;
                            }
                        }
                    }
                    _this.length = _this.a.length;
                    return _this;
                },
                closest: function (s) {
                    let el = this.get(0);
                    let _this = this.copy(this, true);
                    _this.a = [];
                    if ( typeof s === "string" ) {
                        if (el !== null && typeof el.matches != 'undefined' && s !== '') {
                            if (!el.matches(s)) {
                                // noinspection StatementWithEmptyBodyJS
                                while ((el = el.parentElement) && !el.matches(s)) ;
                            }
                            _this.a = el == null ? [] : [el];
                        }
                    } else {
                        if (el !== null && typeof el.matches != 'undefined' && typeof s.matches != 'undefined') {
                            if ( el !== s ) {
                                // noinspection StatementWithEmptyBodyJS
                                while ((el = el.parentElement) && el !== s) ;
                            }
                            _this.a = el == null ? [] : [el];
                        }
                    }
                    _this.length = _this.a.length;
                    return _this;
                },
                add: function( el ) {
                    if ( typeof el !== "undefined" ) {
                        if (typeof el.nodeType !== "undefined") {
                            if (this.a.indexOf(el) == -1) {
                                this.a.push(el);
                            }
                        } else if (typeof el.a !== "undefined") {
                            let _this = this;
                            el.a.forEach(function (el) {
                                if (_this.a.indexOf(el) == -1) {
                                    _this.a.push(el);
                                }
                            });
                        }
                    }
                    return this;
                },
                find: function (s) {
                    let _this = this.copy(this, true);
                    _this.a = [];
                    this.forEach(function(el){
                        if ( el !== null && typeof el.querySelectorAll != 'undefined') {
                            _this.a = _this.a.concat(Array.prototype.slice.call(el.querySelectorAll(s)));
                        }
                    });
                    _this.length = _this.a.length;
                    return _this;
                },
                forEach: function (callback) {
                    this.a.forEach(function (node, index, array) {
                        callback.apply(node, [node, index, array]);
                    });
                    return this;
                },
                each: function (c) {
                    return this.forEach(c);
                },
                hasClass: function (c) {
                    let el = this.get(0);
                    return el != null ? el.classList.contains(c) : false;
                },
                addClass: function (c) {
                    let args = c;
                    if (typeof c == 'string') {
                        args = c.split(' ');
                    }
                    args = args.filter(function (i) {
                        return i.trim() !== ''
                    });
                    if (args.length > 0) {
                        this.forEach(function (el) {
                            el.classList.add.apply(el.classList, args);
                        });
                    }
                    return this;
                },
                removeClass: function (c) {
                    if ( typeof c != 'undefined' ) {
                        let args = c;
                        if (typeof c == 'string') {
                            args = c.split(' ');
                        }
                        args = args.filter(function (i) {
                            return i.trim() !== ''
                        });
                        if (args.length > 0) {
                            this.forEach(function (el) {
                                el.classList.remove.apply(el.classList, args);
                            });
                        }
                    } else {
                        this.forEach(function (el) {
                            if ( el.classList.length > 0 ) {
                                el.classList.remove.apply(el.classList, el.classList);
                            }
                        });
                    }
                    return this;
                },
                is: function(s){
                    let el = this.get(0);
                    if ( el != null ) {
                        return el.matches(s);
                    }
                    return false;
                },
                val: function(v) {
                    let el = this.get(0);
                    if ( el != null ) {
                        if (arguments.length == 1) {
                            if ( el.type == 'select-multiple' ) {
                                v = typeof v === 'string' ? v.split(',') : v;
                                for ( let i = 0, l = el.options.length, o; i < l; i++ ) {
                                    o = el.options[i];
                                    o.selected = v.indexOf(o.value) != -1;
                                }
                            } else {
                                el.value = v;
                            }
                        } else {
                            if ( el.type == 'select-multiple' ) {
                                return Array.prototype.map.call(el.selectedOptions, function(x){ return x.value });
                            } else {
                                return el.value;
                            }
                        }
                    }
                    return this;
                },
                isVisible: function() {
                    let el = this.get(0), visible = true, style;
                    while (el !== null) {
                        style = window.getComputedStyle(el);
                        if (
                            style['display'] == 'none' ||
                            style['visibility'] == 'hidden' ||
                            style['opacity'] == 0
                        ) {
                            visible = false;
                            break;
                        }
                        el = el.parentElement;
                    }
                    return visible;
                },
                attr: function (a, v) {
                    let ret, args = arguments, _this = this;
                    this.forEach(function(el) {
                        if ( args.length == 2 ) {
                            el.setAttribute(a, v);
                            ret = _this;
                        } else {
                            if ( typeof a === 'object' ) {
                                Object.keys(a).forEach(function(k){
                                    el.setAttribute(k, a[k]);
                                });
                            } else {
                                ret = el.getAttribute(a);
                            }
                        }
                    });
                    return ret;
                },
                removeAttr: function(a) {
                    this.forEach(function(el) {
                        el.removeAttribute(a);
                    });
                    return this;
                },
                prop: function(a, v) {
                    let ret, args = arguments;
                    this.forEach(function(el) {
                        if ( args.length == 2 ) {
                            el[a] = v;
                        } else {
                            ret = typeof el[a] != "undefined" ? el[a] : null;
                        }
                    });
                    if ( args.length == 2 ) {
                        return this;
                    } else {
                        return ret;
                    }
                },
                data: function(d, v) {
                    let el = this.get(0),
                        s = d.replace(/-([a-z])/g, function (g) {
                        return g[1].toUpperCase();
                    });
                    if ( el != null ) {
                        if ( arguments.length == 2 ) {
                            el.dataset[s] = v;
                            return this;
                        } else {
                            return typeof el.dataset[s] == "undefined" ? '' : el.dataset[s];
                        }
                    }
                    return '';
                },
                html: function(v) {
                    let el = this.get(0);
                    if ( el != null ) {
                        if ( arguments.length == 1 ) {
                            el.innerHTML = v;
                            return this;
                        } else {
                            return el.innerHTML;
                        }
                    }
                    return '';
                },
                text: function(v) {
                    let el = this.get(0);
                    if ( el != null ) {
                        if ( arguments.length == 1 ) {
                            el.textContent = v;
                            return this;
                        } else {
                            return el.textContent;
                        }
                    }
                    return '';
                },
                css: function(prop, v) {
                    let els = this.get(), el;
                    for (let i=0; i<els.length; i++) {
                        el = els[i];
                        if ( arguments.length == 1 ) {
                            if ( typeof prop == "object" ) {
                                Object.keys(prop).forEach(function(k){
                                    el.style[k] = prop[k];
                                });
                            } else {
                                return window.getComputedStyle(el)[prop];
                            }
                        } else {
                            el.style[prop] = v;
                        }
                    }
                    return this;
                },
                position: function() {
                    let el = this.get(0);
                    if ( el != null ) {
                        return {'top': el.offsetTop, 'left': el.offsetLeft};
                    } else {
                        return {'top': 0, 'left': 0};
                    }
                },
                offset: function() {
                    let el = this.get(0);
                    if ( el != null ) {
                        if ( WPD.dom._fn.hasFixedParent(el) ) {
                            return el.getBoundingClientRect();
                        } else {
                            return WPD.dom._fn.absolutePosition(el);
                        }
                    } else {
                        return {'top': 0, 'left': 0};
                    }
                },
                outerWidth: function(margin) {
                    margin = margin || false;
                    let el = this.get(0);
                    if ( el != null ) {
                        return !margin ? parseInt( el.offsetWidth ) :
                            (
                                parseInt( el.offsetWidth ) +
                                parseInt( this.css('marginLeft') ) +
                                parseInt( this.css('marginRight') )
                            );
                    }
                },
                outerHeight: function(margin) {
                    margin = margin || false;
                    return !margin ? parseInt( this.css('height') ) :
                        (
                            parseInt( this.css('height') ) +
                            parseInt( this.css('marginTop') ) +
                            parseInt( this.css('marginBottom') )
                        );
                },
                innerWidth: function() {
                    let el = this.get(0);
                    if ( el != null ) {
                        let cs = window.getComputedStyle(el);
                        return this.outerWidth() - parseFloat(cs.borderLeftWidth) - parseFloat(cs.borderRightWidth);
                    }
                    return 0;
                },
                width: function() {
                    return this.outerWidth();
                },
                height: function() {
                    return this.outerHeight();
                },
                on: function() {
                    let args = arguments,
                        func = function(args, e) {
                            let $el;
                            if ( e.type == 'mouseenter' || e.type == 'mouseleave' || e.type == 'hover' ) {
                                let el = document.elementFromPoint(e.clientX, e.clientY);
                                if ( !el.matches(args[1]) ) {
                                    // noinspection StatementWithEmptyBodyJS
                                    while ((el = el.parentElement) && !el.matches(args[1])) ;
                                }
                                if ( el != null ) {
                                    $el = WPD.dom(el);
                                }
                            } else {
                                $el = WPD.dom(e.target).closest(args[1]);
                            }
                            if (
                                $el != null &&
                                $el.closest(this).length > 0
                            ){
                                let argd = [];
                                argd.push(e);
                                if ( typeof args[4] != 'undefined' ) {
                                    for (let i=4; i<args.length; i++) {
                                        argd.push(args[i]);
                                    }
                                }
                                args[2].apply($el.get(0), argd);
                            }
                        };
                    let events = args[0].split(' ');
                    for (let i=0;i<events.length;i++) {
                        let type = events[i];
                        if ( typeof args[1] == "string" ) {
                            this.forEach(function(el){
                                if ( !WPD.dom._fn.hasEventListener(el, type, args[2]) ) {
                                    let f = func.bind(el, args);
                                    el.addEventListener(type, f, args[3]);
                                    // Store the trigger in the selected elements, not the parent node
                                    el._wpd_el = typeof el._wpd_el == "undefined" ? [] : el._wpd_el;
                                    el._wpd_el.push({
                                        'type': type,
                                        'selector': args[1],
                                        'func': f,  // The bound function called by the event listener
                                        'trigger': args[2], // The function within the bound function, used in this.trigger(..)
                                        'args': args[3]
                                    });
                                }
                            });
                        } else {
                            for (let i=0;i<events.length;i++) {
                                let type = events[i];
                                this.forEach(function (el) {
                                    if ( !WPD.dom._fn.hasEventListener(el, type, args[1]) ) {
                                        el.addEventListener(type, args[1], args[2]);
                                        el._wpd_el = typeof el._wpd_el == "undefined" ? [] : el._wpd_el;
                                        el._wpd_el.push({
                                            'type': type,
                                            'func': args[1],
                                            'trigger': args[1],
                                            'args': args[2]
                                        });
                                    }
                                });
                            }
                        }
                    }
                    return this;
                },
                off: function(listen, callback) {
                    this.forEach(function (el) {
                        if ( typeof el._wpd_el != "undefined" && el._wpd_el.length > 0 ) {
                            if ( typeof listen === 'undefined' ) {
                                let cb;
                                while (cb = el._wpd_el.pop()) {
                                    el.removeEventListener(cb.type, cb.func, cb.args);
                                }
                                el._wpd_el = [];
                            } else {
                                listen.split(' ').forEach(function(type){
                                    if (typeof callback == "undefined") {
                                        let cb;
                                        while (cb = el._wpd_el.pop()) {
                                            el.removeEventListener(type, cb.func, cb.args);
                                        }
                                        el._wpd_el = [];
                                    } else {
                                        let remains = [];
                                        el._wpd_el.forEach(function(cb){
                                            if ( cb.type == type && cb.trigger == callback ) {
                                                el.removeEventListener(type, cb.func, cb.args);
                                            } else {
                                                remains.push(cb);
                                            }
                                        });
                                        el._wpd_el = remains;
                                    }
                                });
                            }
                        }
                    });
                    return this;
                },
                offForced: function(){
                    let _this = this;
                    this.forEach(function(el, i){
                        let ne = el.cloneNode(true);
                        el.parentNode.replaceChild(ne, el);
                        _this.a[i] = ne;
                    });
                    return this;
                },
                trigger: function(type, args, native ,jquery) {
                    native = native || false;
                    jquery = jquery || false;
                    this.forEach(function(el){
                        let triggered = false;
                        // noinspection JSUnresolvedVariable,JSUnresolvedFunction
                        if (
                            jquery &&
                            typeof jQuery != "undefined" &&
                            typeof jQuery._data != 'undefined' &&
                            typeof jQuery._data(el, 'events') != 'undefined' &&
                            typeof jQuery._data(el, 'events')[type] != 'undefined'
                        ) {
                            // noinspection JSUnresolvedVariable,JSUnresolvedFunction
                            jQuery(el).trigger(type, args);
                            triggered = true;
                        }
                        if ( !triggered && native ) {
                            // Native event handler
                            let event = new Event(type);
                            event.detail = args;
                            el.dispatchEvent(event);
                        }

                        if (typeof el._wpd_el != "undefined") {
                            // Case 1, regularly attached
                            el._wpd_el.forEach(function(data){
                                if ( data.type == type ) {
                                    let event = new Event(type);
                                    data.trigger.apply(el, [event].concat(args));
                                }
                            });
                        } else {
                            // Case 2, attached to a selector: $elem.on('click', 'selector'...
                            let found = false, p = el;
                            // Find parents, where event infomration is stored
                            while ( true ) {
                                p = p.parentElement;
                                if ( p == null ) {
                                    break;
                                }
                                if (typeof p._wpd_el != "undefined") {
                                    p._wpd_el.forEach(function(data){
                                        if ( typeof data.selector !== "undefined" ) {
                                            let targets = WPD.dom(p).find(data.selector);
                                            if (
                                                targets.length > 0 &&
                                                targets.get().indexOf(el) >=0 &&
                                                data.type == type
                                            ) {
                                                let event = new Event(type);
                                                data.trigger.apply(el, [event].concat(args));
                                                found = true;
                                            }
                                        }
                                    });
                                }
                                if ( found ) {
                                    break;
                                }
                            }
                        }
                    });
                    return this;
                },
                clone: function() {
                    let el = this.get(0);
                    if ( el != null ) {
                        this.a = [el.cloneNode(true)];
                        this.length = this.a.length;
                    } else {
                        this.a = [];
                    }
                    this.length = this.a.length;
                    return this;
                },
                remove: function(elem) {
                    if ( typeof elem != "undefined" ) {
                        return elem.parentElement.removeChild(elem);
                    } else {
                        this.forEach(function(el) {
                            if ( el.parentElement != null ) {
                                return el.parentElement.removeChild(el);
                            }
                        });
                        this.a = [];
                        this.length = this.a.length;
                        return null;
                    }
                },
                detach: function() {
                    let _this = this, n = [];
                    this.forEach(function(elem){
                        let el = _this.remove(elem);
                        if ( el != null ) {
                            n.push(el)
                        }
                    });
                    this.a = n;
                    this.length = this.a.length;
                    return this;
                },
                prepend: function(prepend) {
                    if ( typeof prepend == 'string' ) {
                        prepend = WPD.dom._fn.createElementsFromHTML(prepend);
                    }
                    prepend = Array.isArray(prepend) ? prepend : [prepend];
                    this.forEach(function(el){
                        prepend.forEach(function(pre){
                            if ( typeof pre.is_wpd_dom != 'undefined' ) {
                                pre.forEach(function(pr){
                                    el.insertBefore(pr, el.children[0]);
                                });
                            } else {
                                el.insertBefore(pre, el.children[0]);
                            }
                        });
                    });
                    return this;
                },
                append: function(append) {
                    if ( typeof append == 'string' ) {
                        append = WPD.dom._fn.createElementsFromHTML(append);
                    }
                    append = Array.isArray(append) ? append : [append];
                    this.forEach(function(el){
                        append.forEach(function(app) {
                            if ( app != null ) {
                                if (typeof app.is_wpd_dom != 'undefined') {
                                    app.forEach(function (ap) {
                                        el.appendChild(ap);
                                    });
                                } else {
                                    el.appendChild(app.cloneNode(true));
                                }
                            }
                        });
                    });
                    return this;
                },
                uuidv4: function() {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                        let r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                }
            }
            WPD.dom._fn = {
                bodyTransform: function() {
                    let x = 0, y = 0;
                    if ( typeof WebKitCSSMatrix !== 'undefined' ) {
                        let style = window.getComputedStyle(document.body);
                        if ( typeof style.transform != 'undefined' ) {
                            let matrix = new WebKitCSSMatrix(style.transform);
                            if ( matrix.m41 != 'undefined' ) {
                                x = matrix.m41;
                            }
                            if ( matrix.m42 != 'undefined' ) {
                                y = matrix.m42;
                            }
                        }
                    }

                    return {x: x, y: y};
                },
                bodyTransformY: function() {
                    return this.bodyTransform().y;
                },
                bodyTransformX: function() {
                    return this.bodyTransform().x;
                },
                hasFixedParent: function(el) {
                    /**
                     * When CSS transform is present, then Fixed element are no longer fixed
                     * even if the CSS declaration says.
                     */
                    if ( WPD.dom._fn.bodyTransformY() != 0 ) {
                        return false;
                    }
                    do {
                        if ( window.getComputedStyle(el)['position'] == 'fixed' ) {
                            return true;
                        }
                    } while( el = el.parentElement );
                    return false;
                },

                hasEventListener: function(el, type, trigger) {
                    if (typeof el._wpd_el == "undefined") {
                        return false;
                    }
                    for (let i = 0; i < el._wpd_el.length; i++) {
                        if ( el._wpd_el[i].trigger == trigger && el._wpd_el[i].type == type ) {
                            return true;
                        }
                    }
                    return false;
                },

                allDescendants: function(node) {
                    let nodes = [], _this = this;
                    if ( !Array.isArray(node) ) {
                        node = [node];
                    }
                    node.forEach( function(n){
                        for (let i = 0; i < n.childNodes.length; i++) {
                            let child = n.childNodes[i];
                            nodes.push(child);
                            nodes = nodes.concat(_this.allDescendants(child));
                        }
                    });
                    return nodes;
                },

                createElementsFromHTML: function(htmlString) {
                    let template = document.createElement('template');
                    template.innerHTML = htmlString.replace(/(\r\n|\n|\r)/gm, "");
                    return Array.prototype.slice.call(template.content.childNodes);
                },

                absolutePosition: function(el) {
                    if ( !el.getClientRects().length ) {
                        return { top: 0, left: 0 };
                    }

                    let rect = el.getBoundingClientRect();
                    let win = el.ownerDocument.defaultView;
                    return ({
                        top: rect.top + win.pageYOffset,
                        left: rect.left + win.pageXOffset
                    });
                },

                // Create a plugin based on a defined object
                plugin: function (name, object) {
                    WPD.dom.fn[name] = function (options) {
                        if ( typeof(options) != 'undefined' && object[options] ) {
                            return object[options].apply( this, Array.prototype.slice.call( arguments, 1 ));
                        } else {
                            return this.each(function (elem) {
                                elem['wpd_dom_' + name] = Object.create(object).init(options, elem);
                            });
                        }

                    };
                }
            }

            WPD.dom.version = version;
        }

        if ( arguments.length >= 1 ) {
            return WPD.dom.fn.$.apply(WPD.dom.fn, arguments);
        } else {
            return WPD.dom.fn;
        }
    };
    WPD.dom();
    document.dispatchEvent(new Event('wpd-dom-core-loaded'));
}());(function() {
    // Prevent duplicate loading
    if ( typeof WPD.dom.fn.animate != "undefined" ) {
        return false;	// Terminate
    }
    WPD.dom.fn._animate = {
        "easing": {
            "linear": function(x) { return x; },
            "easeInOutQuad": function(x) {
                return x < 0.5 ? 2 * x * x : 1 - Math.pow(-2 * x + 2, 2) / 2;
            },
            "easeOutQuad": function(x) {
                return 1 - (1 - x) * (1 - x);
            }
        }
    };
    WPD.dom.fn.animate = function(props, duration, easing) {
        let _this = this;
        duration = duration || 200;
        easing = easing || "linear";
        this.forEach(function(el){
            let frames, currentFrame = 0, fps = 60, multiplier, origProps = {}, propsDiff = {},
                handlers, handler, easingFn;
            handlers = _this.prop('_wpd_dom_animations');
            handlers = handlers == null ? [] : handlers;

            if ( props === false ) {
                handlers.forEach(function(handler){
                    // noinspection JSCheckFunctionSignatures
                    clearInterval(handler);
                });
            } else {
                if ( typeof _this._animate.easing[easing] != "undefined" ) {
                    easingFn = _this._animate.easing[easing];
                } else {
                    easingFn = _this._animate.easing.easeInOutQuad;
                }
                Object.keys(props).forEach(function(prop){
                    if ( prop.indexOf('scroll') > -1 ) {
                        origProps[prop] = el[prop];
                        propsDiff[prop] = props[prop] - origProps[prop];
                    } else {
                        origProps[prop] = parseInt( window.getComputedStyle(el)[prop] );
                        propsDiff[prop] = props[prop] - origProps[prop];
                    }
                });

                function move() {
                    currentFrame++;
                    if ( currentFrame > frames ) {
                        clearInterval(handler);
                        return;
                    }
                    multiplier = easingFn(currentFrame / frames);
                    Object.keys(propsDiff).forEach(function(prop){
                        if ( prop.indexOf('scroll') > -1 ) {
                            el[prop] = origProps[prop] + propsDiff[prop] * multiplier;
                        } else {
                            el.style[prop] =
                                origProps[prop] + propsDiff[prop] * multiplier + 'px';
                        }
                    });
                }

                frames = duration / 1000 * fps;

                handler = setInterval(move, 1000 / fps);
                handlers.push(handler);
                _this.prop('_wpd_dom_animations', handlers);
            }
        });
        return this;
    };
    document.dispatchEvent(new Event('wpd-dom-animate-loaded'));
}());/*
 * WPD.dom Highlight plugin
 *
 * Based on highlight v3 by Johann Burkard
 * http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html
 * Copyright (c) 2009 Bartek Szopka
 *
 * Licensed under MIT license.
 *
 */
(function() {
    let $ = WPD.dom;

    // Prevent duplicate loading
    if ( typeof WPD.dom.fn.unhighlight != "undefined" ) {
        return false;	// Terminate
    }

    WPD.dom.fn.unhighlight = function (options) {
        let settings = {className: 'highlight', element: 'span'};
        $.fn.extend(settings, options);

        return this.find(settings.element + "." + settings.className).each(function () {
            let parent = this.parentNode;
            parent.replaceChild(this.firstChild, this);
            parent.normalize();
        });
    };

    WPD.dom.fn.highlight = function (words, options) {
        let settings = {
            className: 'highlight',
            element: 'span',
            caseSensitive: false,
            wordsOnly: false,
            excludeParents: ''
        };
        $.fn.extend(settings, options);

        if (words.constructor === String) {
            words = [words];
        }
        words = words.filter(function(el){
            return el != '';
        });
        words.forEach(function(w, i, o){
            o[i] = w.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        });

        if (words.length == 0) {
            return this;
        }

        let flag = settings.caseSensitive ? "" : "i";
        let pattern = "(" + words.join("|") + ")";
        if (settings.wordsOnly) {
            pattern = "(?:,|^|\\s)" + pattern + "(?:,|$|\\s)";
        }
        let re = new RegExp(pattern, flag);
        function highlight(node, re, nodeName, className, excludeParents) {
            excludeParents = excludeParents == '' ? '.exhghttt' : excludeParents;
            if (node.nodeType === 3) {
                let normalized = node.data.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                let match = normalized.match(re);
                if (match) {
                    let highlight = document.createElement(nodeName || 'span');
                    let index;
                    highlight.className = className || 'highlight';
                    if (/\.|,|\s/.test(match[0].charAt(0)))
                        index = match.index + 1;
                    else
                        index = match.index;
                    let wordNode = node.splitText(index);
                    wordNode.splitText(match[1].length);
                    let wordClone = wordNode.cloneNode(true);
                    highlight.appendChild(wordClone);
                    wordNode.parentNode.replaceChild(highlight, wordNode);
                    return 1; //skip added node in parent
                }
            } else if ((node.nodeType === 1 && node.childNodes) && // only element nodes that have children
                !/(script|style)/i.test(node.tagName) && // ignore script and style nodes
                !$(node).closest(excludeParents).length > 0 &&
                !(node.tagName === nodeName.toUpperCase() && node.className === className)) { // skip if already highlighted
                for (let i = 0; i < node.childNodes.length; i++) {
                    i += highlight(node.childNodes[i], re, nodeName, className, excludeParents);
                }
            }
            return 0;
        }

        return this.each(function (el) {
            highlight(el, re, settings.element, settings.className, settings.excludeParents);
        });
    };
}());(function() {
    // Prevent duplicate loading
    if ( typeof WPD.dom.fn.serialize != "undefined" ) {
        return false;	// Terminate
    }
    WPD.dom.fn.serialize = function() {
        let form = this.get(0);
        if ( !form || form.nodeName !== "FORM" ) {
            return;
        }
        let i, j, q = [];
        for (i = form.elements.length - 1; i >= 0; i = i - 1) {
            if (form.elements[i].name === "") {
                continue;
            }
            switch (form.elements[i].nodeName) {
                case 'INPUT':
                    switch (form.elements[i].type) {
                        case 'text':
                        case 'hidden':
                        case 'password':
                        case 'button':
                        case 'reset':
                        case 'submit':
                            q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                            break;
                        case 'checkbox':
                        case 'radio':
                            if (form.elements[i].checked) {
                                q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                            }
                            break;
                        case 'file':
                            break;
                    }
                    break;
                case 'TEXTAREA':
                    q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                    break;
                case 'SELECT':
                    switch (form.elements[i].type) {
                        case 'select-one':
                            q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                            break;
                        case 'select-multiple':
                            for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
                                if (form.elements[i].options[j].selected) {
                                    q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].options[j].value));
                                }
                            }
                            break;
                    }
                    break;
                case 'BUTTON':
                    switch (form.elements[i].type) {
                        case 'reset':
                        case 'submit':
                        case 'button':
                            q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                            break;
                    }
                    break;
            }
        }
        return q.join("&");
    };
    WPD.dom.fn.serializeForAjax = function(obj, prefix) {
        let str = [],
            p;
        for (p in obj) {
            if (obj.hasOwnProperty(p)) {
                let k = prefix ? prefix + "[" + p + "]" : p,
                    v = obj[p];
                str.push((v !== null && typeof v === "object") ?
                    WPD.dom.fn.serializeForAjax(v, k) :
                    encodeURIComponent(k) + "=" + encodeURIComponent(v));
            }
        }
        return str.join("&");
    };
    document.dispatchEvent(new Event('wpd-dom-serialize-loaded'));
}());(function() {
    // Prevent duplicate loading
    if ( typeof WPD.dom.fn.inViewPort != "undefined" ) {
        return false;	// Terminate
    }
    WPD.dom.fn.inViewPort = function (tolerance, viewport) {
        "use strict";
        let element = this.get(0), vw, vh;
        if (element == null)
            return false;
        tolerance = typeof tolerance == 'undefined' ? 0 : tolerance;
        viewport = typeof viewport == 'undefined' ? window :
            ( typeof viewport == 'string' ? document.querySelector(viewport) : viewport );
        let ref = element.getBoundingClientRect(),
            top = ref.top, bottom = ref.bottom,
            left = ref.left, right = ref.right,
            invisible = false;

        if (viewport == null) {
            viewport = window;
        }
        if (viewport === window) {
            vw = window.innerWidth || 0;
            vh = window.innerHeight || 0;
        } else {
            vw = viewport.clientWidth
            vh = viewport.clientHeight
            let vr = viewport.getBoundingClientRect();

            // recalculate these relative to viewport
            top = top - vr.top;
            bottom = bottom - vr.top;
            left = left - vr.left;
            right = right - vr.left;
        }

        tolerance = ~~Math.round(parseFloat(tolerance));
        if (right <= 0 || left >= vw) {
            return invisible
        }

        // if the element is bound to some tolerance
        invisible = tolerance > 0 ? top >= tolerance && bottom < (vh - tolerance) :
            ( bottom > 0 && top <= (vh - tolerance) ) |
            ( top <= 0 && bottom > tolerance);

        return invisible;
    };
    document.dispatchEvent(new Event('wpd-dom-viewport-loaded'));
}());(function() {
    // Prevent duplicate loading
    if ( typeof WPD.dom.fn.ajax != "undefined" ) {
        return false;	// Terminate
    }
    WPD.dom.fn.ajax = function(args) {
        let defaults = {
            'url': '',
            'method': 'GET',
            'cors': 'cors', // cors, no-cors
            'data': {},
            'success': null,
            'fail': null,
            'accept': 'text/html',
            'contentType': 'application/x-www-form-urlencoded; charset=UTF-8'
        }
        args = this.extend(defaults, args);

        if ( args.cors != 'cors' ) {
            let fn = 'ajax_cb_' + this.uuidv4().replaceAll('-', '');
            WPD.dom.fn[fn] = function() {
                args.success.apply(this, arguments);
                delete WPD.dom.fn[args.data.fn];
            };
            args.data.callback = 'WPD.dom.fn.' + fn;
            args.data.fn = fn;
            let script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = args.url + '?' + this.serializeForAjax(args.data);
            script.onload = function(){this.remove();};
            document.body.appendChild(script);
        } else {
            let xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if ( args.success != null ) {
                    if ( this.readyState == 4 && this.status == 200 ) {
                        args.success(this.responseText);
                    }
                }
                if ( args.fail != null ) {
                    if ( this.readyState == 4 && this.status >= 400 ) {
                        args.fail(this);
                    }
                }
            };

            xhttp.open(args.method.toUpperCase(), args.url, true);
            xhttp.setRequestHeader('Content-type', args.contentType);
            xhttp.setRequestHeader('Accept', args.accept);

            xhttp.send(this.serializeForAjax(args.data));
            return xhttp;
        }
    };
    document.dispatchEvent(new Event('wpd-dom-xhttp-loaded'));
}());window.WPD = window.WPD || {};
window.WPD.Base64 = {
    // private property
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

    // public method for encoding
    encode: function (input) {
        let output = "", chr1, chr2, chr3, enc1, enc2, enc3, enc4, i = 0;

        input = this._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output +
                this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },

    // public method for decoding
    decode: function (input) {
        let output = "", chr1, chr2, chr3, enc1, enc2, enc3, enc4, i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = this._utf8_decode(output);

        return output;

    },

    // private method for UTF-8 encoding
    _utf8_encode: function (string) {
        string = string.replace(/\r\n/g, "\n");
        let utftext = "";

        for (let n = 0; n < string.length; n++) {

            let c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode: function (utftext) {
        let string = "", i = 0, c = 0, c2, c3;

        while (i < utftext.length) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            } else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

};// https://gist.github.com/rheinardkorf/c6592b59fb061f9f8310
(function(){
    window.WPD = window.WPD || {};
    WPD.Hooks = WPD.Hooks || {};
    let Hooks = WPD.Hooks;
    Hooks.filters = Hooks.filters || {};
    /**
     * Adds a callback function to a specific programmatically triggered tag (hook)
     *
     * @param tag - the hook name
     * @param callback - the callback function variable name
     * @param priority - (optional) default=10
     * @param scope - (optional) function scope. When a function is executed within an object scope, the object variable should be passed.
     */
    Hooks.addFilter = function( tag, callback, priority, scope ) {
        priority = typeof priority === "undefined" ? 10 : priority;
        scope = typeof scope === "undefined" ? null : scope;
        Hooks.filters[ tag ] = Hooks.filters[ tag ] || [];
        Hooks.filters[ tag ].push( { priority: priority, scope: scope, callback: callback } );
    }

    /**
     * Removes a callback function from a hook
     *
     * @param tag - the hook name
     * @param callback - the callback function variable
     */
    Hooks.removeFilter = function( tag, callback ) {
        if ( typeof Hooks.filters[ tag ] != 'undefined' ) {
            if ( typeof callback == "undefined" ) {
                Hooks.filters[tag] = [];
            } else {
                Hooks.filters[tag].forEach(function (filter, i) {
                    if (filter.callback === callback) {
                        Hooks.filters[tag].splice(i, 1);
                    }
                });
            }
        }
    }
    Hooks.applyFilters = function( tag ) {
        let filters = [],
            args = Array.prototype.slice.call(arguments),
            value = arguments[1];
        if( typeof Hooks.filters[ tag ] !== "undefined" && Hooks.filters[ tag ].length > 0 ) {
            Hooks.filters[ tag ].forEach( function( hook ) {
                filters[ hook.priority ] = filters[ hook.priority ] || [];
                filters[ hook.priority ].push( {
                    scope: hook.scope,
                    callback: hook.callback
                } );
            } );
            args.splice(0, 2);
            filters.forEach( function( hooks ) {
                hooks.forEach( function( obj ) {
                    /**
                     * WARNING!
                     * If, this function is called with a referanced parameter like OBJECT or ARRAY argument
                     * as the first argument - then the callback function MUST return that value, otherwise
                     * it is overwritten with NULL!
                     * Ex.:
                     * Hooks.applyFilters('my_filter', object);
                     * Hooks.addFilter('my_filter', function(obj){
                     *     do things..
                     *     return obj; <--- IMPORTANT IN EVERY CASE
                     * });
                     */
                    value = obj.callback.apply( obj.scope, [value].concat(args) );
                } );
            } );
        }
        return value;
    }
}());window.WPD = window.WPD || {};
/**
 * Checks "criteria" until not false, then executes function "f". No delay on first execution, like with simple
 * setInterval().
 * @param f
 * @param criteria Function or variable reference - preferably function
 * @param interval
 * @param maxTries
 * @returns {*}
 */
window.WPD.intervalUntilExecute = function(f, criteria, interval, maxTries) {
    let t, tries = 0,
        res = typeof criteria === "function" ? criteria() : criteria;
    interval = typeof interval == "undefined" ? 100 : interval;
    maxTries = typeof maxTries == "undefined" ? 50 : maxTries;

    if ( res === false ) {
        t = setInterval(function (){
            res = typeof criteria === "function" ? criteria() : criteria;
            tries++;
            if ( tries > maxTries ) {
                clearInterval(t);
                return false;
            }
            if ( res !== false ) {
                clearInterval(t);
                return f(res);
            }
        }, interval)
    } else {
        return f(res);
    }
};/**
 * swiped-events.js - v@version@
 * Pure JavaScript swipe events
 * https://github.com/john-doherty/swiped-events
 * @inspiration https://stackoverflow.com/questions/16348031/disable-scrolling-when-touch-moving-certain-element
 * @author John Doherty <www.johndoherty.info>
 * @license MIT
 */
(function (window, document) {

    'use strict';

    // patch CustomEvent to allow constructor creation (IE/Chrome)
    if (typeof window.CustomEvent !== 'function') {

        window.CustomEvent = function (event, params) {

            params = params || { bubbles: false, cancelable: false, detail: undefined };

            var evt = document.createEvent('CustomEvent');
            evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
            return evt;
        };

        window.CustomEvent.prototype = window.Event.prototype;
    }

    document.addEventListener('touchstart', handleTouchStart, false);
    document.addEventListener('touchmove', handleTouchMove, false);
    document.addEventListener('touchend', handleTouchEnd, false);

    var xDown = null;
    var yDown = null;
    var xDiff = null;
    var yDiff = null;
    var timeDown = null;
    var startEl = null;

    /**
     * Fires swiped event if swipe detected on touchend
     * @param {object} e - browser event object
     * @returns {void}
     */
    function handleTouchEnd(e) {

        // if the user released on a different target, cancel!
        if (startEl !== e.target) return;

        var swipeThreshold = parseInt(getNearestAttribute(startEl, 'data-swipe-threshold', '20'), 10); // default 20px
        var swipeTimeout = parseInt(getNearestAttribute(startEl, 'data-swipe-timeout', '500'), 10);    // default 500ms
        var timeDiff = Date.now() - timeDown;
        var eventType = '';
        var changedTouches = e.changedTouches || e.touches || [];

        if (Math.abs(xDiff) > Math.abs(yDiff)) { // most significant
            if (Math.abs(xDiff) > swipeThreshold && timeDiff < swipeTimeout) {
                if (xDiff > 0) {
                    eventType = 'swiped-left';
                }
                else {
                    eventType = 'swiped-right';
                }
            }
        }
        else if (Math.abs(yDiff) > swipeThreshold && timeDiff < swipeTimeout) {
            if (yDiff > 0) {
                eventType = 'swiped-up';
            }
            else {
                eventType = 'swiped-down';
            }
        }

        if (eventType !== '') {

            var eventData = {
                dir: eventType.replace(/swiped-/, ''),
                xStart: parseInt(xDown, 10),
                xEnd: parseInt((changedTouches[0] || {}).clientX || -1, 10),
                yStart: parseInt(yDown, 10),
                yEnd: parseInt((changedTouches[0] || {}).clientY || -1, 10)
            };

            // fire `swiped` event event on the element that started the swipe
            startEl.dispatchEvent(new CustomEvent('swiped', { bubbles: true, cancelable: true, detail: eventData }));

            // fire `swiped-dir` event on the element that started the swipe
            startEl.dispatchEvent(new CustomEvent(eventType, { bubbles: true, cancelable: true, detail: eventData }));
        }

        // reset values
        xDown = null;
        yDown = null;
        timeDown = null;
    }

    /**
     * Records current location on touchstart event
     * @param {object} e - browser event object
     * @returns {void}
     */
    function handleTouchStart(e) {

        // if the element has data-swipe-ignore="true" we stop listening for swipe events
        if (e.target.getAttribute('data-swipe-ignore') === 'true') return;

        startEl = e.target;

        timeDown = Date.now();
        xDown = e.touches[0].clientX;
        yDown = e.touches[0].clientY;
        xDiff = 0;
        yDiff = 0;
    }

    /**
     * Records location diff in px on touchmove event
     * @param {object} e - browser event object
     * @returns {void}
     */
    function handleTouchMove(e) {

        if (!xDown || !yDown) return;

        var xUp = e.touches[0].clientX;
        var yUp = e.touches[0].clientY;

        xDiff = xDown - xUp;
        yDiff = yDown - yUp;
    }

    /**
     * Gets attribute off HTML element or nearest parent
     * @param {object} el - HTML element to retrieve attribute from
     * @param {string} attributeName - name of the attribute
     * @param {any} defaultValue - default value to return if no match found
     * @returns {any} attribute value or defaultValue
     */
    function getNearestAttribute(el, attributeName, defaultValue) {

        // walk up the dom tree looking for data-action and data-trigger
        while (el && el !== document.documentElement) {

            var attributeValue = el.getAttribute(attributeName);

            if (attributeValue) {
                return attributeValue;
            }

            el = el.parentNode;
        }

        return defaultValue;
    }

}(window, document));(function(){
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
})();WPD.dom._fn.plugin('ajaxsearchpro', window.WPD.ajaxsearchpro.plugin);(function($){
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
})(WPD.dom);(function($){
						"use strict";
						let helpers = window.WPD.ajaxsearchpro.helpers;
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
    , 
        autocomplete: function () {
            let $this = this,
                val = $this.n('text').val();

            if ($this.n('text').val() == '') {
                $this.n('textAutocomplete').val('');
                return;
            }
            let autocompleteVal = $this.n('textAutocomplete').val();

            if (autocompleteVal != '' && autocompleteVal.indexOf(val) == 0) {
                return;
            } else {
                $this.n('textAutocomplete').val('');
            }
            // noinspection JSUnresolvedVariable
            if ( $this.n('text').val().length >= $this.o.autocomplete.trigger_charcount ) {
                let data = {
                    action: 'ajaxsearchpro_autocomplete',
                    asid: $this.o.id,
                    sauto: $this.n('text').val(),
                    asp_inst_id: $this.o.rid,
                    options: $('form', $this.n('searchsettings')).serialize()
                };
                // noinspection JSUnresolvedVariable
                $this.postAuto = $.fn.ajax({
                    'url': ASP.ajaxurl,
                    'method': 'POST',
                    'data': data,
                    'success': function (response) {
                        if (response.length > 0) {
                            response = $('<textarea />').html(response).text();
                            response = response.replace(/^\s*[\r\n]/gm, "");
                            response = val + response.substr(val.length);
                        }
                        $this.n('textAutocomplete').val(response);
                        $this.fixAutocompleteScrollLeft();
                    }
                });
            }
        },

        // If only google source is used, this is much faster..
        autocompleteGoogleOnly: function () {
            let $this = this,
                val = $this.n('text').val();
            if ($this.n('text').val() == '') {
                $this.n('textAutocomplete').val('');
                return;
            }
            let autocompleteVal = $this.n('textAutocomplete').val();
            if (autocompleteVal != '' && autocompleteVal.indexOf(val) == 0) {
                return;
            } else {
                $this.n('textAutocomplete').val('');
            }

            let lang = $this.o.autocomplete.lang;
            ['wpml_lang', 'polylang_lang', 'qtranslate_lang'].forEach( function(v){
                if (
                    $('input[name="'+v+'"]', $this.n('searchsettings')).length > 0 &&
                    $('input[name="'+v+'"]', $this.n('searchsettings')).val().length > 1
                ) {
                    lang = $('input[name="' + v + '"]', $this.n('searchsettings')).val();
                }
            });
            // noinspection JSUnresolvedVariable
            if ( $this.n('text').val().length >= $this.o.autocomplete.trigger_charcount ) {
                $.fn.ajax({
                    url: 'https://clients1.google.com/complete/search',
                    cors: 'no-cors',
                    data: {
                        q: val,
                        hl: lang,
                        nolabels: 't',
                        client: 'hp',
                        ds: ''
                    },
                    success: function (data) {
                        if (data[1].length > 0) {
                            let response = data[1][0][0].replace(/(<([^>]+)>)/ig, "");
                            response = $('<textarea />').html(response).text();
                            response = response.substr(val.length);
                            $this.n('textAutocomplete').val(val + response);
                            $this.fixAutocompleteScrollLeft();
                        }
                    }
                });
            }
        },

        fixAutocompleteScrollLeft: function() {
            this.n('textAutocomplete').get(0).scrollLeft = this.n('text').get(0).scrollLeft;
        }
    , 
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
    , 
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
    , 
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
    , 
        liveLoad: function(origSelector, url, updateLocation, forceAjax, cache) {
            let selector = origSelector;
            if ( selector == 'body' || selector == 'html' ) {
                console.log('Ajax Search Pro: Do not use html or body as the live loader selector.');
                return false;
            }

            let $this = this;

            if ( ASP.pageHTML != "" ) {
                $this.setLiveLoadCache(ASP.pageHTML, origSelector);
            }

            function process(html) {
                let data = helpers.Hooks.applyFilters('asp/live_load/raw_data', html, $this);
                let parser = new DOMParser;
                let dataNode = parser.parseFromString(data, "text/html");
                let $dataNode = $(dataNode);

                // noinspection JSUnresolvedVariable
                if ( $this.o.statistics ) {
                    $this.stat_addKeyword($this.o.id, $this.n('text').val());
                }
                if ( data != '' && $dataNode.length > 0 && $dataNode.find(selector).length > 0 ) {
                    data = data.replace(/&asp_force_reset_pagination=1/gmi, '');
                    data = data.replace(/%26asp_force_reset_pagination%3D1/gmi, '');
                    data = data.replace(/&#038;asp_force_reset_pagination=1/gmi, '');

                    // Safari having issues with srcset when ajax loading
                    if ( helpers.isSafari() ) {
                        data = data.replace(/srcset/gmi, 'nosrcset');
                    }

                    data = helpers.Hooks.applyFilters('asp_live_load_html', data, $this.o.id, $this.o.iid);
                    data = helpers.wp_hooks_apply_filters('asp_live_load_html', data, $this.o.id, $this.o.iid);
                    $dataNode = $(parser.parseFromString(data, "text/html"));

                    //$el.replaceWith($dataNode.find(selector).first());
                    let replacementNode = $dataNode.find(selector).get(0);
                    replacementNode = helpers.Hooks.applyFilters('asp/live_load/replacement_node', replacementNode, $this, $el.get(0), data);
                    if ( replacementNode != null ) {
                        $el.get(0).parentNode.replaceChild(replacementNode, $el.get(0));
                    }

                    // get the element again, as it no longer exists
                    $el = $(selector).first();
                    if ( updateLocation ) {
                        document.title = dataNode.title;
                        history.pushState({}, null, url);
                    }

                    // WooCommerce ordering fix
                    $(selector).first().find(".woocommerce-ordering select.orderby").on("change", function(){
                        if ( $(this).closest("form").length > 0 ) {
                            $(this).closest("form").get(0).submit();
                        }
                    });

                    $this.addHighlightString($(selector).find('a'));

                    helpers.Hooks.applyFilters('asp/live_load/finished', url, $this, selector, $el.get(0));

                    // noinspection JSUnresolvedVariable
                    ASP.initialize();
                    $this.lastSuccesfulSearch = $('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim();
                    $this.lastSearchData = data;

                    $this.setLiveLoadCache(html, origSelector);
                }
                $this.n('s').trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n('text').val(), data], true, true);
                $this.gaEvent?.('search_end', {'results_count': 'unknown'});
                $this.hideLoader();
                $el.css('opacity', 1);
                $this.searching = false;
                if ( $this.n('text').val() != '' ) {
                    $this.n('proclose').css({
                        display: "block"
                    });
                }
            }

            updateLocation = typeof updateLocation == 'undefined' ? true : updateLocation;
            forceAjax = typeof forceAjax == 'undefined' ? false : forceAjax;

            // Alternative possible selectors from famous themes
            let altSel = [
                '.search-content',
                '#content', '#Content', 'div[role=main]',
                'main[role=main]', 'div.theme-content', 'div.td-ss-main-content',
                'main.l-content', '#primary'
            ];
            if ( selector != '#main' )
                altSel.unshift('#main');

            if ( $(selector).length < 1 ) {
                altSel.forEach(function(s, i){
                    if ( $(s).length > 0 ) {
                        selector = s;
                        return false;
                    }
                });
                if ( $(selector).length < 1 ) {
                    console.log('Ajax Search Pro: The live search selector does not exist on the page.');
                    return false;
                }
            }

            selector = helpers.Hooks.applyFilters('asp/live_load/selector', selector, this);

            let $el = $(selector).first();

            $this.searchAbort();
            $el.css('opacity', 0.4);

            url = helpers.Hooks.applyFilters('asp/live_load/url', url, $this, selector, $el.get(0));
            helpers.Hooks.applyFilters('asp/live_load/start', url, $this, selector, $el.get(0));

            if (
                !forceAjax &&
                $this.n('searchsettings').find('input[name=filters_initial]').val() == 1 &&
                $this.n('text').val() == ''
            ) {
                window.WPD.intervalUntilExecute(function(){
                    process(ASP.pageHTML);
                }, function(){
                    return ASP.pageHTML != ''
                });
            } else {
                if ( typeof cache != 'undefined' ) {
                    process(cache.html);
                } else {
                    $this.searching = true;
                    $this.post = $.fn.ajax({
                        url: url,
                        method: 'GET',
                        success: function(data){
                            process(data);
                            $this.isAutoP = false;
                        },
                        dataType: 'html',
                        fail: function(jqXHR){
                            $el.css('opacity', 1);
                            if ( jqXHR.aborted ) {
                                return;
                            }
                            $el.html("This request has failed. Please check your connection.");
                            $this.hideLoader();
                            $this.searching = false;
                            $this.n('proclose').css({
                                display: "block"
                            });
                            $this.isAutoP = false;
                        }
                    });
                }
            }
        },
        usingLiveLoader: function() {
            let $this = this;
            $this._usingLiveLoader = typeof $this._usingLiveLoader == 'undefined' ?
                (
                    $('.asp_es_' + $this.o.id).length > 0 ||
                    ( $this.o.resPage.useAjax  && $($this.o.resPage.selector).length > 0 ) ||
                    ( $this.o.wooShop.useAjax  && $($this.o.wooShop.selector).length > 0 ) ||
                    ( $this.o.cptArchive.useAjax  && $($this.o.cptArchive.selector).length > 0 ) ||
                    ( $this.o.taxArchive.useAjax  && $($this.o.taxArchive.selector).length > 0 )
                ) : $this._usingLiveLoader;
            return $this._usingLiveLoader;
        },
        getLiveURLbyBaseLocation( location ) {
            let $this = this,
                url = 'asp_ls=' + helpers.nicePhrase( $this.n('text').val() ),
                start = '&';

            if ( location.indexOf('?') === -1 ) {
                start = '?';
            }

            let final = location + start + url + "&asp_active=1&asp_force_reset_pagination=1&p_asid=" +
                $this.o.id + "&p_asp_data=1&" + $('form', $this.n('searchsettings')).serialize();
            // Possible issue when the URL ends with '?' and the start is '&'
            final = final.replace('?&', '?');
            final = final.replace('&&', '&');

            return final;
        },
        getCurrentLiveURL: function() {
            let $this = this;
            let url = 'asp_ls=' + helpers.nicePhrase( $this.n('text').val() ),
                start = '&',
                location = window.location.href;

            // Correct previous query arguments (in case of paginated results)
            location = location.indexOf('asp_ls=') > -1 ? location.slice(0, location.indexOf('asp_ls=')) : location;
            location = location.indexOf('asp_ls&') > -1 ? location.slice(0, location.indexOf('asp_ls&')) : location;

            // Was asp_ls missing but there are ASP related arguments? (ex. when using ASP.api('getStateURL'))
            location = location.indexOf('p_asid=') > -1 ? location.slice(0, location.indexOf('p_asid=')) : location;
            location = location.indexOf('asp_') > -1 ? location.slice(0, location.indexOf('asp_')) : location;

            if ( location.indexOf('?') === -1 ) {
                start = '?';
            }

            let final = location + start + url + "&asp_active=1&asp_force_reset_pagination=1&p_asid=" +
                $this.o.id + "&p_asp_data=1&" + $('form', $this.n('searchsettings')).serialize();
            // Possible issue when the URL ends with '?' and the start is '&'
            final = final.replace('?&', '?');
            final = final.replace('&&', '&');

            return final;
        },
        initLiveLoaderPopState: function() {
            let $this = this;
            $this.liveLoadCache = [];
            window.addEventListener('popstate', (event) => {
                let data = $this.getLiveLoadCache();
                if ( data !== false ) {
                    $this.n('text').val(data.phrase);
                    helpers.formData( $('form', $this.n('searchsettings')), data.settings );
                    $this.resetNoUISliderFilters();
                    $this.liveLoad(data.selector, document.location.href, false, false, data);
                }
            });

            // Store the current page HTML for the live loaders
            // It needs to be requested here as the dom does store the processed HTML, and it is no good.
            if ( ASP.pageHTML == "" ) {
                if ( typeof ASP._ajax_page_html === 'undefined' ) {
                    ASP._ajax_page_html = true;
                    $.fn.ajax({
                        url: $this.currentPageURL,
                        method: 'GET',
                        success: function(data){
                            ASP.pageHTML = data;
                        },
                        dataType: 'html'
                    });
                }
            }
        },

        setLiveLoadCache: function( html, selector ) {
            let $this = this;
            if ( $this.liveLoadCache.filter((item)=>{
                return item.href ==  document.location.href;
            }).length == 0 ) {
                $this.liveLoadCache.push({
                    'href':  html == ASP.pageHTML ? $this.currentPageURL : document.location.href,
                    'phrase': html == ASP.pageHTML ? '' : $this.n('text').val(),
                    'selector': selector,
                    'html': html,
                    'settings': html == ASP.pageHTML ? $this.originalFormData : helpers.formData($('form', $this.n('searchsettings')))
                });
            }
        },

        getLiveLoadCache: function() {
            let $this = this;
            let res = $this.liveLoadCache.filter((item)=>{
                return item.href == document.location.href;
            });
            return res.length > 0 ? res[0] : false;
        }
    , 
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
    , 
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
    , 
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
    , 
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

    , 
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
    , 
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
    , 
        showPolaroidResults: function () {
            let $this = this;

            this.loadASPFonts?.();

            $this.n('results').addClass('photostack');

            $('.photostack>nav', $this.n('resultsDiv')).remove();
            let figures = $('figure', $this.n('resultsDiv'));

            $this.showResultsBox();

            if (figures.length > 0) {
                // noinspection JSUnresolvedVariable
                $this.n('results').css({
                    height: $this.o.prescontainerheight
                });

                if ($this.o.highlight == 1) {
                    // noinspection JSUnresolvedVariable,JSUnresolvedFunction
                    $("figcaption", $this.n('resultsDiv')).highlight($this.n('text').val().split(" "), {
                        element: 'span', className: 'highlighted', wordsOnly: $this.o.highlightWholewords
                    });
                }

                // Initialize the main
                // noinspection JSUnresolvedVariable,JSUnresolvedFunction
                if (typeof Photostack !== 'undefined') {
                    // noinspection JSUnresolvedVariable,JSUnresolvedFunction
                    $this.ptstack = new Photostack($this.n('results').get(0), {
                        callback: function (item) {
                        }
                    });
                } else {
                    // PhotoStack is not included within the scripts, alert the user!;
                    return false;
                }
            }
            if (figures.length == 0) {
                $this.n('results').css({
                    height: '11110px'
                });
                $this.n('results').css({
                    height: "auto"
                });
            }
            $this.addAnimation();
            $this.fixResultsPosition(true);
            $this.searching = false;
            $this.initPolaroidEvents(figures);
        },

        initPolaroidEvents: function (figures) {
            let $this = this,
                i = 1,
                span = '.photostack>nav span';
            figures.each(function () {
                if (i > 1)
                    $(this).removeClass('photostack-current');
                $(this).attr('idx', i);
                i++;
            });

            figures.on('click', function (e) {
                if ($(this).hasClass("photostack-current")) return;
                e.preventDefault();
                let idx = $(this).attr('idx');
                $('.photostack>nav span:nth-child(' + idx + ')', $this.n('resultsDiv')).trigger('click', [], true);
            });

            figures.on('mousewheel', function (e) {
                e.preventDefault();
                let delta = e.deltaY > 0 ? 1 : -1
                if (delta >= 1) {
                    if ($(span + '.current', $this.n('resultsDiv')).next().length > 0) {
                        $(span + '.current', $this.n('resultsDiv')).next().trigger('click', [], true);
                    } else {
                        $(span + ':nth-child(1)', $this.n('resultsDiv')).trigger('click', [], true);
                    }
                } else {
                    if ($(span + '.current', $this.n('resultsDiv')).prev().length > 0) {
                        $(span + '.current', $this.n('resultsDiv')).prev().trigger('click', [], true);
                    } else {
                        $(span + ':nth-last-child(1)', $this.n('resultsDiv')).trigger('click', [], true);
                    }
                }
            });

            $this.n('resultsDiv').on("swiped-left", function() {
                if ($(span + '.current', $this.n('resultsDiv')).next().length > 0) {
                    $(span + '.current', $this.n('resultsDiv')).next().trigger('click', [], true);
                } else {
                    $(span + ':nth-child(1)', $this.n('resultsDiv')).trigger('click', [], true);
                }
            });
            $this.n('resultsDiv').on("swiped-right", function() {
                if ($(span + '.current', $this.n('resultsDiv')).prev().length > 0) {
                    $(span + '.current', $this.n('resultsDiv')).prev().trigger('click', [], true);
                } else {
                    $(span + ':nth-last-child(1)', $this.n('resultsDiv')).trigger('click', [], true);
                }
            });
        }
    , 
        showVerticalResults: function () {
            let $this = this;

            $this.showResultsBox();

            if ($this.n('items').length > 0) {
                // noinspection JSUnresolvedVariable
                let count = (($this.n('items').length < $this.o.itemscount) ? $this.n('items').length : $this.o.itemscount);
                count = count <= 0 ? 9999 : count;
                let groups = $('.asp_group_header', $this.n('resultsDiv'));

                // So if the result list is short, we dont even need to do the match
                // noinspection JSUnresolvedVariable
                if ($this.o.itemscount == 0 || $this.n('items').length <= $this.o.itemscount) {
                    $this.n('results').css({
                        height: 'auto'
                    });
                } else {

                    // Set the height to a fictive value to refresh the scrollbar
                    // .. otherwise the height is not calculated correctly, because of the scrollbar width.
                    if ( $this.call_num < 1 )
                        $this.n('results').css({
                            height: "30px"
                        });

                    if ( $this.call_num < 1 ) {
                        // Here now we have the correct item height values with the scrollbar enabled
                        let i = 0,
                            h = 0,
                            final_h = 0,
                            highest = 0;

                        $this.n('items').each(function () {
                            h += $(this).outerHeight(true);
                            if ($(this).outerHeight(true) > highest)
                                highest = $(this).outerHeight(true);
                            i++;
                        });

                        // Get an initial height based on the highest item x viewport
                        final_h = highest * count;
                        // Reduce the final height to the overall height if exceeds it
                        if (final_h > h)
                            final_h = h;

                        // Count the average height * viewport size
                        i = i < 1 ? 1 : i;
                        h = h / i * count;

                        /*
                         Groups need a bit more calculation
                         - determine group position by index and occurence
                         - one group consists of group header, items + item spacers per item
                         - only groups within the viewport are calculated
                         */
                        if (groups.length > 0) {
                            groups.each(function (el, index) {
                                let position = Array.prototype.slice.call(el.parentNode.children).indexOf(el),
                                    group_position = position - index - Math.floor(position / 3);
                                if (group_position < count) {
                                    final_h += $(this).outerHeight(true);
                                }
                            });
                        }
                        $this.n('results').css({
                            height: final_h + 'px'
                        });

                    }
                }

                // Mark the last item
                $this.n('items').last().addClass('asp_last_item');
                // Before groups as well
                $this.n('results').find('.asp_group_header').prev('.item').addClass('asp_last_item');
                if ($this.o.highlight == 1) {
                    // noinspection JSUnresolvedVariable
                    $("div.item", $this.n('resultsDiv')).highlight($this.n('text').val().split(" "), {
                        element: 'span', className: 'highlighted', wordsOnly: $this.o.highlightWholewords
                    });
                }


            }
            $this.resize();
            if ($this.n('items').length == 0) {
                $this.n('results').css({
                    height: 'auto'
                });
            }

            if ( $this.call_num < 1 ) {
                // Scroll to top
                $this.n('results').get(0).scrollTop = 0;
            }

            // Preventing body touch scroll
            // noinspection JSUnresolvedVariable
            if ( $this.o.preventBodyScroll ) {
                let t,
                    $body = $('body'),
                    bodyOverflow = $body.css('overflow'),
                    bodyHadNoStyle = typeof $body.attr('style') === 'undefined';
                $this.n('results').off("touchstart");
                $this.n('results').off("touchend");
                $this.n('results').on("touchstart", function () {
                    clearTimeout(t);
                    $('body').css('overflow', 'hidden');
                }).on('touchend', function () {
                    clearTimeout(t);
                    t = setTimeout(function () {
                        if (bodyHadNoStyle) {
                            $('body').removeAttr('style');
                        } else {
                            $('body').css('overflow', bodyOverflow);
                        }
                    }, 300);
                });
            }

            $this.addAnimation();
            $this.fixResultsPosition(true);
            $this.searching = false;
        }
    , 
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
    , 
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
    , 
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
    , 
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
    
						};
						$.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
					})(WPD.dom);// noinspection HttpUrlsUsage,JSUnresolvedVariable

// noinspection JSUnresolvedVariable

(function($){
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
})(WPD.dom);// noinspection JSUnresolvedVariable

(function($){
						"use strict";
						let helpers = window.WPD.ajaxsearchpro.helpers; let _static = window.WPD.ajaxsearchpro;
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
    , 
        initAutocompleteEvent: function () {
            let $this = this,
                tt;
            if (
                ($this.o.autocomplete.enabled == 1 && !helpers.isMobile()) ||
                ($this.o.autocomplete.mobile == 1 && helpers.isMobile())
            ) {
                $this.n('text').on('keyup', function (e) {
                    $this.keycode =  e.keyCode || e.which;
                    $this.ktype = e.type;

                    let thekey = 39;
                    // Lets change the keykode if the direction is rtl
                    if ($('body').hasClass('rtl'))
                        thekey = 37;
                    if ($this.keycode == thekey && $this.n('textAutocomplete').val() != "") {
                        e.preventDefault();
                        $this.n('text').val($this.n('textAutocomplete').val());
                        if ( $this.o.trigger.type != 0 ) {
                            $this.searchAbort();
                            $this.search();
                        }
                    } else {
                        clearTimeout(tt);
                        if ($this.postAuto != null) $this.postAuto.abort();
                        //This delay should be greater than the post-result delay..
                        //..so the
                        // noinspection JSUnresolvedVariable
                        if ($this.o.autocomplete.googleOnly == 1) {
                            $this.autocompleteGoogleOnly();
                        } else {
                            // noinspection JSUnresolvedVariable
                            tt = setTimeout(function () {
                                $this.autocomplete();
                                tt = null;
                            }, $this.o.trigger.autocomplete_delay);
                        }
                    }
                });
                $this.n('text').on('keyup mouseup input blur select', function(){
                   $this.fixAutocompleteScrollLeft();
                });
            }
        }
    , 
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
    , 
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
    , 
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
    , 
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
    , 
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
    , 
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
    , 
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
    , 
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
    , 
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
    , 
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
    , 
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
    , 
        monitorTouchMove: function() {
            let $this = this;
            $this.dragging = false;
            $("body").on("touchmove", function(){
                $this.dragging = true;
            }).on("touchstart", function(){
                $this.dragging = false;
            });
        }
    , 
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
    , 
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
    , 
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
    , 
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
    , 
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
    , 
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
    
						};
						$.fn.extend(window.WPD.ajaxsearchpro.plugin, functions);
					})(WPD.dom);// noinspection JSUnresolvedVariable

window.ASP = typeof window.ASP !== 'undefined' ? window.ASP : {};
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
});window._ASP_load = function () {
    "use strict";
    let $ = WPD.dom;

    window.ASP.instances = {
        instances: [],
        get: function(id, instance) {
            this.clean();
            if ( typeof id === 'undefined' || id == 0) {
                return this.instances;
            } else {
                if ( typeof instance === 'undefined' ) {
                    let ret = [];
                    for ( let i=0; i<this.instances.length; i++ ) {
                        if ( this.instances[i].o.id == id ) {
                            ret.push(this.instances[i]);
                        }
                    }
                    return ret.length > 0 ? ret : false;
                } else {
                    for ( let i=0; i<this.instances.length; i++ ) {
                        if ( this.instances[i].o.id == id && this.instances[i].o.iid == instance ) {
                            return this.instances[i];
                        }
                    }
                }
            }
            return false;
        },
        set: function(obj) {
            if ( !this.exist(obj.o.id, obj.o.iid) ) {
                this.instances.push(obj);
                return true;
            } else {
                return false;
            }
        },
        exist: function(id, instance) {
            this.clean();
            for ( let i=0; i<this.instances.length; i++ ) {
                if ( this.instances[i].o.id == id ) {
                    if (typeof instance === 'undefined') {
                        return true;
                    } else if (this.instances[i].o.iid == instance) {
                        return true;
                    }
                }
            }
            return false;
        },
        clean: function() {
            let unset = [], _this = this;
            this.instances.forEach(function(v, k){
                if ( $('.asp_m_' + v.o.rid).length == 0 ) {
                    unset.push(k);
                }
            });
            unset.forEach(function(k){
                if ( typeof _this.instances[k] !== 'undefined' ) {
                    _this.instances[k].destroy();
                    _this.instances.splice(k, 1);
                }
            });
        },
        destroy: function(id, instance) {
            let i = this.get(id, instance);
            if ( i !== false ) {
                if ( Array.isArray(i) ) {
                    i.forEach(function (s) {
                        s.destroy();
                    });
                    this.instances = [];
                } else {
                    let u = 0;
                    this.instances.forEach(function(v, k){
                        if ( v.o.id == id && v.o.iid == instance) {
                            u = k;
                        }
                    });
                    i.destroy();
                    this.instances.splice(u, 1);
                }
            }
        }
    };

    window.ASP.initialized = false;
    window.ASP.initializeSearchByID = function (id) {
        let instances = ASP.getInstances();
        if (typeof id !== 'undefined' && typeof id != 'object' ) {
            if ( typeof instances[id] !== 'undefined' ) {
                let ni = [];
                ni[id] = instances[id];
                instances = ni;
            } else {
                return false;
            }
        }
        instances.forEach(function (data, i) {
            // noinspection JSUnusedAssignment
            $.fn._('.asp_m_' + i).forEach(function(el){
                if ( typeof el.hasAsp != 'undefined') {
                    return true;
                }
                el.hasAsp = true;
                return $(el).ajaxsearchpro(data);
            });
        });
    }

    window.ASP.getInstances = function() {
        window.ASP_INSTANCES = typeof window.ASP_INSTANCES !== 'undefined' ? window.ASP_INSTANCES : [];
        let inst = window.ASP_INSTANCES;
        $.fn._('.asp_init_data').forEach(function (el) {
            let id = el.dataset['aspId'];
            if ( typeof inst[id] == 'undefined' || 1 ) {
                let data;
                if ( typeof el.dataset['aspdata'] != 'undefined' ) {
                    data = WPD.Base64.decode(el.dataset['aspdata']);
                }
                if (typeof data === "undefined" || data == "") return true;
                inst[id] = JSON.parse(data);
            }
        });
        return inst;
    }

// Call this function if you need to initialize an instance that is printed after an AJAX call
// Calling without an argument initializes all instances found.
    window.ASP.initialize = function (id) {
        // Some weird ajax loader problem prevention
        if (typeof ASP.version == 'undefined')
            return false;

        if( !!window.IntersectionObserver ){
            if ( ASP.script_async_load || ASP.init_only_in_viewport ) {
                let searches = document.querySelectorAll('.asp_w_container, .asp_m');
                if ( searches.length ) {
                    let observer = new IntersectionObserver(function(entries){
                        entries.forEach(function(entry){
                            if ( entry.isIntersecting ) {
                                ASP.initializeSearchByID(entry.target.dataset.id);
                                observer.unobserve(entry.target);
                            }
                        });
                    });
                    searches.forEach(function(search){
                        observer.observe(search);
                    });
                }
                ASP.getInstances().forEach(function(inst, id){
                    if ( inst.compact.enabled == 1 && inst.compact.position == 'fixed' ) {
                        ASP.initializeSearchByID(id);
                    }
                });
            } else {
                ASP.initializeSearchByID(id);
            }
        } else {
            ASP.initializeSearchByID(id);
        }

        ASP.initializeMutateDetector();
        ASP.initializeHighlight();
        ASP.initializeOtherEvents();

        ASP.initialized = true;
    };

    window.ASP.initializeHighlight = function() {
        let _this = this;
        if (_this.highlight.enabled) {
            let data = _this.highlight.data,
                selector = data.selector != '' && $(data.selector).length > 0 ? data.selector : 'article',
                $highlighted, phrase, s;
            selector = $(selector).length > 0 ? selector : 'body';

            s = new URLSearchParams(location.search);
            phrase = s.get('s') || s.get('asp_highlight');
            $(selector).unhighlight({className: 'asp_single_highlighted_' + data.id});
            if ( phrase !== null && phrase.trim() != '' ) {
                // noinspection JSUnresolvedVariable
                $(selector).highlight(phrase.trim().split(' '), {
                    element: 'span',
                    className: 'asp_single_highlighted_' + data.id,
                    wordsOnly: data.whole,
                    excludeParents: '.asp_w, .asp-try'
                });
                $highlighted = $('.asp_single_highlighted_' + data.id);
                if (data.scroll && $highlighted.length > 0) {
                    let stop = $highlighted.offset().top - 120;
                    let $adminbar = $("#wpadminbar");
                    if ($adminbar.length > 0)
                        stop -= $adminbar.height();
                    // noinspection JSUnresolvedVariable
                    stop = stop + data.scroll_offset;
                    stop = stop < 0 ? 0 : stop;
                    $('html').animate({
                        "scrollTop": stop
                    }, 500);
                }
            }
            return false;
        }
    };

    window.ASP.initializeOtherEvents = function() {
        let ttt, ts, $body = $('body'), _this = this;
        // Known slide-out and other type of menus to initialize on click
        ts = '#menu-item-search, .fa-search, .fa, .fas';
        // Avada theme
        ts = ts + ', .fusion-flyout-menu-toggle, .fusion-main-menu-search-open';
        // Be theme
        ts = ts + ', #search_button';
        // The 7 theme
        ts = ts + ', .mini-search.popup-search';
        // Flatsome theme
        ts = ts + ', .icon-search';
        // Enfold theme
        ts = ts + ', .menu-item-search-dropdown';
        // Uncode theme
        ts = ts + ', .mobile-menu-button';
        // Newspaper theme
        ts = ts + ', .td-icon-search, .tdb-search-icon';
        // Bridge theme
        ts = ts + ', .side_menu_button, .search_button';
        // Jupiter theme
        ts = ts + ', .raven-search-form-toggle';
        // Elementor trigger lightbox & other elementor stuff
        ts = ts + ', [data-elementor-open-lightbox], .elementor-button-link, .elementor-button';
        ts = ts + ', i[class*=-search], a[class*=-search]';

        // Attach this to the document ready, as it may not attach if this is loaded early
        $body.on('click touchend', ts, function () {
            clearTimeout(ttt);
            ttt = setTimeout(function () {
                _this.initializeSearchByID();
            }, 300);
        });

        // Elementor popup events (only works with jQuery)
        if ( typeof jQuery != 'undefined' ) {
            jQuery(document).on('elementor/popup/show', function(){
                setTimeout(function () {
                    _this.initializeSearchByID();
                }, 10);
            });
        }
    };

    window.ASP.initializeMutateDetector = function() {
        let t;
        if ( typeof ASP.detect_ajax != "undefined" && ASP.detect_ajax == 1 ) {
            let o = new MutationObserver(function() {
                clearTimeout(t);
                t = setTimeout(function () {
                    ASP.initializeSearchByID();
                }, 500);
            });
            o.observe(document.querySelector("body"), {subtree: true, childList: true});
        }
    };

    window.ASP.ready = function () {
        let _this = this;

        if (document.readyState === "complete" || document.readyState === "loaded"  || document.readyState === "interactive") {
            // document is already ready to go
            _this.initialize();
        } else {
            $(document).on('DOMContentLoaded', _this.initialize);
        }
    };

    window.ASP.loadScriptStack = function (stack) {
        let scriptTag;
        if ( stack.length > 0 ) {
            scriptTag = document.createElement('script');
            scriptTag.src = stack.shift()['src'];
            scriptTag.onload = function () {
                if ( stack.length > 0 ) {
                    window.ASP.loadScriptStack(stack)
                } else {
                    window.ASP.ready();
                }
            }
            document.body.appendChild(scriptTag);
        }
    }

    window.ASP.init = function () {
        // noinspection JSUnresolvedVariable
        if (ASP.script_async_load) {   // Opimized Normal
            // noinspection JSUnresolvedVariable
            window.ASP.loadScriptStack(ASP.additional_scripts);
        } else {
            if (typeof WPD.ajaxsearchpro !== 'undefined') {   // Classic normal
                window.ASP.ready();
            }
        }
    };


    window.WPD.intervalUntilExecute(window.ASP.init, function() {
        return typeof window.ASP.version != 'undefined' && $.fn.ajaxsearchpro != 'undefined'
    });
};
// Run on document ready
(function() {
    if ( navigator.userAgent.indexOf("Chrome-Lighthouse") === -1 ) {
        // Preload script executed?
        if (typeof WPD != 'undefined' && typeof WPD.dom != 'undefined') {
            window._ASP_load();
        } else {
            document.addEventListener('wpd-dom-core-loaded', window._ASP_load);
        }
    }
})();(function($){
    "use strict";
    let helpers = window.WPD.ajaxsearchpro.helpers;
    let addon = function() {
        this.name = "Divi Widget Fixes";
        this.init = function(){
            helpers.Hooks.addFilter('asp/init/etc', this.diviBodyCommerceResultsPage, 10, this);
        };
        this.diviBodyCommerceResultsPage = function( $this ) {
            if ( $this.o.divi.bodycommerce && $this.o.is_results_page ) {
                WPD.intervalUntilExecute(function($){
                    setTimeout(function(){
                        $('#divi_filter_button').trigger('click');
                    }, 50);
                }, function() {
                    return typeof jQuery !== "undefined" ? jQuery : false;
                });
            }

            // Need to return the first argument, as this is a FILTER hook with OBJECT reference argument, and will override with the return value
            return $this;
        };
    }
    window.WPD.ajaxsearchpro.addons.add(new addon());
})(WPD.dom);(function($){
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
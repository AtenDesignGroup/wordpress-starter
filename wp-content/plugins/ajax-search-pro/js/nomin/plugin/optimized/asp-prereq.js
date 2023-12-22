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

}(window, document));
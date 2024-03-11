/*!
 * jQuery Cookie Plugin v1.4.0
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as anonymous module.
		define(['jquery'], factory);
	} else {
		// Browser globals.
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
		} catch(e) {
			return;
		}

		try {
			// If we can't parse the cookie, ignore it, it's unusable.
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return (typeof converter === 'function') ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write
		if (value !== undefined && !(typeof value === 'function')) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setDate(t.getDate() + days);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires  ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path     ? '; path=' + options.path : '',
				options.domain   ? '; domain=' + options.domain : '',
				options.samesite ? '; samesite=' + options.samesite : '',
				options.secure   ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {};

		// To prevent the for loop in the first place assign an empty array
		// in case there are no cookies at all. Also prevents odd result when
		// calling $.cookie().
		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = parts.join('=');

			if (key && key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {
		'samesite' : 'lax'
	};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) !== undefined) {
			// Must not alter options, thus extending a fresh object...
			$.cookie(key, '', $.extend({}, options, { expires: -1 }));
			return true;
		}
		return false;
	};

}));


/**
 * A wrapper object for a user preference stored in a cookie.
 *
 * Created by Jānis Elsts. Added to the same file as the cookie library
 * to avoid a separate HTTP request.
 *
 * @param {string} name
 * @param {number} [expirationInDays]
 * @param {boolean} [jsonEncodingEnabled]
 * @constructor
 */
function WsAmePreferenceCookie(name, expirationInDays, jsonEncodingEnabled) {
	if (typeof expirationInDays === 'undefined') {
		expirationInDays = 90;
	}
	if (typeof jsonEncodingEnabled === 'undefined') {
		jsonEncodingEnabled = false;
	}

	//Full name = unique prefix + name with the first letter capitalized.
	this.fullCookieName = 'amePref' + name.charAt(0).toUpperCase() + name.slice(1);
	this.jsonEncodingEnabled = jsonEncodingEnabled;
	this.cookieOptions = {
		'path': '/',
		'samesite': 'lax'
	};
	if (expirationInDays > 0) {
		this.cookieOptions.expires = expirationInDays;
	}
}

WsAmePreferenceCookie.prototype.read = function (defaultValue) {
	let cookieValue = jQuery.cookie(this.fullCookieName);
	if (typeof cookieValue === 'undefined') {
		return defaultValue;
	}

	if (this.jsonEncodingEnabled) {
		if ((typeof cookieValue === 'string') && (cookieValue !== '')) {
			try {
				cookieValue = JSON.parse(cookieValue);
			} catch (error) {
				return defaultValue; //Use the default value if the stored JSON is invalid.
			}
		} else {
			return defaultValue;
		}
	}

	return cookieValue;
}

WsAmePreferenceCookie.prototype.write = function (value) {
	if (this.jsonEncodingEnabled) {
		value = JSON.stringify(value);
	}
	jQuery.cookie(this.fullCookieName, value, this.cookieOptions);
}

WsAmePreferenceCookie.prototype.removeCookie = function () {
	return jQuery.removeCookie(this.fullCookieName, this.cookieOptions);
}

/**
 * Read the cookie value, and if it's set, write it back to the cookie.
 * This extends the cookie's expiration date by the configured number of days.
 *
 * @param {*} defaultValue
 * @returns {*}
 */
WsAmePreferenceCookie.prototype.readAndRefresh = function (defaultValue) {
	const notFound = {};
	const value = this.read(notFound);
	if (value === notFound) {
		return defaultValue;
	}

	//phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.write -- This is not document.write(), but a custom method.
	this.write(value);
	return value;
}
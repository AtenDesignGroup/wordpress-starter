window.WD_Helpers = window.WD_Helpers || {};

window.WD_Helpers.Base64 = {

// private property
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

// public method for encoding
    encode: function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

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
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

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
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

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
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

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

}

window.WD_Helpers.uuidv4 = function uuidv4() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
};

// jQuery dependent
jQuery(function($){
    /**
     * Sets the [attr] attributes on all descendants of "node", when exist in "data" object
     *
     * Single depth
     *      <input attr[myattr]
     * Multiple depth
     *      <input attr[myattr.sub1.sub2...subN]
     *
     * @param {{}} node
     * @param {{}} data
     * @param {string} [exclude=''] - Excluded attributes
     */
    window.WD_Helpers.setAttributes = function(node, data, exclude) {
        exclude = exclude || '';
        var ex = exclude.replace(/\s/g,'').split(',').filter(function(v){ return v!==''});

        $(node).find('[attr]').each(function(){
            var att = $(this).attr('attr');
            if ( $.inArray(att, ex) == -1 ) {
                if ( att.split('.').length > 0 ) {  // Multiple depth
                    var pointer = data;
                    var arr = att.split('.');
                    var _this = this;
                    $.each(arr, function(index, v){
                        if ( typeof pointer[v] != 'undefined' ) {
                            if ( index == (arr.length - 1) ) {	// last item
                                controller.helper.setNodeValue(_this, pointer[v]);
                            } else {
                                pointer = pointer[v];
                            }
                        }
                    });
                } else if ( typeof data[att] != 'undefined' ) {
                    controller.helper.setNodeValue(this, data[att]);
                }
            }
        });
    },

        /**
         * Gets the [attr] attributes from all descendants of "node"
         *
         * Single depth
         *      <input attr[myattr]
         * Multiple depth
         *      <input attr[myattr.sub1.sub2...subN]
         *
         * @param {{}} node
         * @param {string} [attributes=''] - List of attributes
         * @returns {{}}
         */
        window.WD_Helpers.getAttributes = function(node, attributes) {
            var ret = {};
            attributes = attributes || '';
            attributes = attributes.replace(/\s/g,'').split(',').filter(function(v){ return v!==''});
            if ( attributes.length == 0 ) {
                $(node).find('[attr]').each(function(){
                    var att = $(this).attr('attr');
                    if ( att.split('.').length > 0 ) {  // Multiple depth
                        var pointer = ret;
                        var arr = att.split('.');
                        var _this = this;
                        $.each(arr, function(index, v){
                            pointer[v] = typeof pointer[v] == 'undefined' ? {} : pointer[v];
                            if ( index == (arr.length - 1) ) {	// last item
                                pointer[v] = controller.helper.getNodeValue(_this);
                            } else {
                                pointer = pointer[v];
                            }
                        });
                    } else {                            // Single depth
                        ret[att] = controller.helper.getNodeValue(this);
                    }
                });
            } else {
                $.each(attributes, function(i, att){
                    $(node).find('[attr=' + att + ']').each(function(){
                        ret[att] = controller.helper.getNodeValue(this);
                    });
                });
            }

            return ret;
        };

    /**
     * Sets the node value, depending on node type
     *
     * @param {{}} node
     * @param {string} value
     */
    window.WD_Helpers.setNodeValue = function(node, value) {
        var name = node.nodeName.toLowerCase();
        if ( name == 'select' || name == 'textarea' ) {
            $(node).val(value);
        } else if ( name == 'input' ) {
            var type = $(node).attr('type');
            if ( type == 'checkbox' ) {
                $(node).prop('checked', value);
            } else if ( node.type == 'text' || node.type == 'number' || node.type == 'hidden' ) {
                $(node).val(value);
            }
        } else if ( name == 'span' ) {
            $(node).html(value);
        }
    };

    /**
     * Gets the node value, based on the node type
     *
     * @param {{}} node
     * @returns {string}
     */
    window.WD_Helpers.getNodeValue = function(node) {
        var name = node.nodeName.toLowerCase(), ret;

        if ( name == 'select' || name == 'textarea' ) {
            ret = $(node).val();
        } else if ( name == 'input' ) {
            var type = $(node).attr('type');
            if ( type == 'checkbox' ) {
                ret = $(node).is(':checked');
            } else if ( node.type == 'text' || node.type == 'number' || node.type == 'hidden' ) {
                ret = $(node).val();
            }
        }

        ret = ret === null ? '' : ret;
        return ret;
    };
});
/**
 * Custom Utility Functions for use sitewide
 */

jQuery( document ).ready(function($) {
    // Converting a DOM element into a specified new type of element
	$.fn.convertElement = function(newType) {
		if(!$(this[0]) || $(this[0]) == undefined || !$(this[0]).length) {
			return;
		} else {
			var atts = {};
	
			$.each(this[0].attributes, function(idx, attr) {
				atts[attr.nodeName] = attr.nodeValue;
			});
		
			this.replaceWith(function() {
				return $("<" + newType + "/>", atts).append($(this).contents());
			});
		}
	}
});
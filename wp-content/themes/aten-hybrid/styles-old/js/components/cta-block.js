/**
 * Custom JS for the Call To Action block
 */

jQuery( document ).ready(function($) {
    var host = window.location.host;

    $('.cta-button').each(function() {
		let url = new URL($(this).attr('href'));
		let url_host = url.hostname;
		let new_tab = ($(this).attr('target') === '_blank');

		if(url_host !== host || new_tab === true) {
            $(this).addClass('external-link');
        }
    });
});
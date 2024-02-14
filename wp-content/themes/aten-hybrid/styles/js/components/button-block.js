/**
 * Custom JS for the Button block
 */

jQuery( document ).ready(function($) {
    var host = window.location.host;

    $('.wp-element-button, .custom-button').each(function() {
        let href = $(this).attr('href');
        let new_tab = ($(this).attr('target') === '_blank');

        if(new_tab === true) {
            $(this).addClass('external-link');
            let external_button_text = '<span class="a11y-visible">External Link</span>';
            $(this).append(external_button_text);
            return;
        } else {
            if(href && /(http(s?)):\/\//i.test(href)) {
                let url = new URL($(this).attr('href'));
                let url_host = url.hostname;
        
                if(url_host !== host) {
                    $(this).addClass('external-link');
                    let external_button_text = '<span class="a11y-visible">External Link</span>';
                    $(this).append(external_button_text);
                    return;
                }
            }
        }
    });
});
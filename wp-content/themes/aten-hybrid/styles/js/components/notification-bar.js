/**
 * Custom JS for the Easy Notifications Bar
 */

jQuery( document ).ready(function($) {
    $('.easy-notification-bar').addClass('ccc-notification-bar');
    $('.easy-notification-bar__close').prepend('<span>Dismiss</span>');
    $('.easy-notification-bar-message').wrapInner('<p />');
    $('.easy-notification-bar-button a').appendTo($('.easy-notification-bar-message p'));
    $('.easy-notification-bar-button').remove();
    $('.easy-notification-bar__close').appendTo('.easy-notification-bar-container');

    $('.easy-notification-bar-container').prepend('<h2 class="a11y-visible">Alerts</h2>');

    $('.easy-notification-bar').detach().insertAfter('a.skip-link.screen-reader-text');
});


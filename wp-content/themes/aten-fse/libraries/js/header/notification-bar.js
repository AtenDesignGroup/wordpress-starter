/**
 * Custom JS for the Easy Notifications Bar
 */

jQuery(document).ready(function ($) {
  $('.easy-notification-bar').addClass('aten-fse-notification-bar');
  $('.easy-notification-bar-container').prepend(
    '<span class="notification-icon" aria-hidden="true">warning</span>'
  );
  $('.easy-notification-bar__close').prepend('<span>Close</span>');

  var buttonWrapper = $('.easy-notification-bar-button');
  // Check if a button exists in the notification bar
  if (buttonWrapper.length) {
    let buttonLink = $(buttonWrapper).find('a');
    let href = $(buttonLink).attr('href')
      ? $(buttonLink).attr('href')
      : undefined;
    let target = $(buttonLink).attr('target')
      ? $(buttonLink).attr('target')
      : '_self';
    let rel = $(buttonLink).attr('rel')
      ? ' rel="' + $(buttonLink).attr('rel') + '"'
      : '';
    if (href !== undefined) {
      $('.easy-notification-bar-container').wrapInner(
        '<ol><li><a class="notification-bar-link" href="' +
          href +
          '" target="' +
          target +
          rel +
          '" />'
      );
    }
    $(buttonWrapper).remove();
  }

  $('.easy-notification-bar-container').prepend(
    '<h2 class="a11y-visible">Announcements</h2>'
  );

  $('.easy-notification-bar')
    .detach()
    .insertAfter('a.skip-link.screen-reader-text');
});

/**
 * Custom JS for sitewide form elements
 */

jQuery(document).ready(function ($) {
  $('.gfield_choice_all_toggle').click(function () {
    if ($(this).text() === 'Deselect All') {
      $(this).addClass('deselect-btn');
    } else {
      $(this).removeClass('deselect-btn');
    }
  });

  $('.gform_confirmation_message').wrapInner(
    '<p class="confirmation-contents" />'
  );

  $('.gsection_description').wrapInner('<p class="description-contents" />');

  $('#gform_fields_5 h3.gsection_title').convertElement('h2');

  $('.gf_progressbar_title').convertElement('h3');

  $('.gf_progressbar').attr('aria-hidden', 'false');

  $('.validation_message h1').convertElement('h2');

  $('.gf_progressbar').remove();
});

/**
 * Custom JS for the Featured Link Cards block
 */

jQuery(document).ready(function ($) {
  // Function to check if links are external
  const isLinkExternal = (link) => {
    let host = window.location.host;
    let url = new URL(link.attr('href'));
    let url_host = url.hostname;
    let new_tab = link.attr('target') === '_blank';

    return url_host !== host || new_tab === true;
  };

  $('.card-link').each(function () {
    // Add the external-link class to the <a> element if it's href value is an external website
    if (isLinkExternal($(this))) {
      $(this).addClass('external-link');
    }
  });
});

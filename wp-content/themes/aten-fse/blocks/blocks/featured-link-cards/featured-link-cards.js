/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/**
 * Custom JS for the Featured Link Cards block
 */

jQuery(document).ready(function ($) {
  // Function to check if links are external
  var isLinkExternal = function isLinkExternal(link) {
    var host = window.location.host;
    var url = new URL(link.attr('href'));
    var url_host = url.hostname;
    var new_tab = link.attr('target') === '_blank';
    return url_host !== host || new_tab === true;
  };
  $('.card-link').each(function () {
    // Add the external-link class to the <a> element if it's href value is an external website
    if (isLinkExternal($(this))) {
      $(this).addClass('external-link');
    }
  });
});
/******/ })()
;
//# sourceMappingURL=featured-link-cards.js.map
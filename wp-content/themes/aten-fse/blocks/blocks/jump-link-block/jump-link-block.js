/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/**
 * Custom JS for Jump Link functionality, utilized by both the All Services and Jump Link blocks
 */

jQuery(document).ready(function ($) {
  // Creating jump link list for all services block, prefixing the list items with a downward arrow icon
  var servicesJumpLinkList = createJumpLinkList('service-section-heading', 'arrow_downward');
  // Placing the services jump link list into the wrapper div of the block template
  $(servicesJumpLinkList).appendTo('.services-jump-link-wrapper');

  // Creating jump link list for jump link block
  var jumpLinkBlockList = createJumpLinkList('jump-link-section-title');
  // Placing the jump link block list into the wrapper div of the block template
  $(jumpLinkBlockList).appendTo('.jump-link-block-list-container');

  /**
   *
   * @param {string} targetElementClass
   * @param {string} prefixIconName (optional)
   * @returns string
   *
   * createJumpLinkList takes a target element class, adds anchor links to each element based on its text value,
   * and returns an HTML UL of alphabetically-sorted jump links for each element in the class.
   *
   * If prefixIconName is passed, the matching Material Icon will prefix each of the list items.
   */
  function createJumpLinkList(targetElementClass, prefixIconName) {
    // Creating an empty array to hold objects of element information
    var targetElements = [];

    // Looping through all target elements
    $('.' + targetElementClass).each(function () {
      // Creating a new object
      var targetElement = new Object();
      // Getting the name of the object based on its text content
      targetElement.name = $(this).text();
      // Creating an id for the object based on a kebab-case title
      targetElement.id = targetElement.name.replace(/\s+/g, '-').toLowerCase();
      // Creating an anchor element for each object, prefixed to avoid collisions and numeric title errors
      var anchor = '<a id="jump-to-' + targetElement.id + '"></a>';
      // Inserting the anchor element at the start of each target element
      $(anchor).prependTo(this);
      // Adding the object to the array of element objects
      targetElements.push(targetElement);
    });
    return generateListHTML(targetElements, prefixIconName);
  }

  /**
   *
   * @param {array} array
   * @param {string} prefixIconName (optional)
   * @returns string
   *
   * generateListHTML sorts an array alphabetically and creates an HTML UL element containing each
   * element of the array as a list item linking to the element's jump link
   *
   * If prefixIconName is passed, the matching Material Icon will prefix each of the list items.
   */
  function generateListHTML(array, prefixIconName) {
    // Sorting array by ID
    var sortedArray = array.sort(function (a, b) {
      return a.id.localeCompare(b.id);
    });
    var optionalIcon = '';

    // Checkiing for optional icon parameter
    if (prefixIconName !== undefined) {
      optionalIcon += '<span class="jump-link-icon" aria-hidden="true">' + prefixIconName + '</span>';
    }

    // Creating HTML UL
    var listHTML = '<ul class="jump-link-list">';
    $(sortedArray).each(function () {
      // Adding each element as a LI inside the UL
      listHTML += '<li class="jump-link-list-item"><a href="#jump-to-' + $(this)[0].id + '">' + optionalIcon + $(this)[0].name + '</a></li>';
    });
    listHTML += '</ul>';
    return listHTML;
  }
});
/******/ })()
;
//# sourceMappingURL=jump-link-block.js.map
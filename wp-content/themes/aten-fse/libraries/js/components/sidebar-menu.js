/**
 * Custom JS for the Sidebar Menu
 */

jQuery(document).ready(function ($) {
  // Collapsing all panels by default on page load
  collapseAllSidebarPanels();
  // Removing default hidden class
  $('.sidebar-accordion-panel.collapsed').removeClass('collapsed');
  // Expanding the current panel by default
  let default_panel = $('.sidebar-accordion-panel').has('li.active');
  let default_trigger = $(default_panel).attr('aria-labelledby');
  togglePanel('#' + default_trigger);

  // Check screen size on page load and adjust accordingly
  adjustMenuLocation();
  // Check screen size after page resize and adjust accordingly
  $(window).resize(adjustMenuLocation);

  // Toggle the panel open on click
  $('.sidebar-accordion-trigger').click(function () {
    togglePanel($(this));
  });

  // Collapse all sidebar accordion panels and set the aria-expanded value accordingly
  function collapseAllSidebarPanels() {
    $('.sidebar-accordion-panel').slideUp();
    $('.sidebar-item.active').removeClass('active');
    $('.sidebar-accordion-trigger').attr('aria-expanded', 'false');
  }

  // Toggle panel open and closed with animation
  function togglePanel(trigger) {
    let panel_id = $(trigger).attr('aria-controls');
    if ($(trigger).hasClass('sidebar-accordion-trigger')) {
      // If it's collapsed
      if ($(trigger).attr('aria-expanded') === 'false') {
        // Open the selected panel
        $(trigger).attr('aria-expanded', 'true');
        $(trigger).closest('.sidebar-item').addClass('active');
        $('#' + panel_id).slideDown();
      } else {
        // If it's already open, close it
        $('#' + panel_id).slideUp();
        $(trigger).closest('.sidebar-item').removeClass('active');
        $(trigger).attr('aria-expanded', 'false');
      }
    } else if ($(trigger).hasClass('mobile-trigger')) {
      // If it's collapsed
      if ($(trigger).attr('aria-expanded') === 'false') {
        // Open the mobile menu panel
        $(trigger).attr('aria-expanded', 'true');
        $('#' + panel_id).slideDown();
      } else {
        // If it's already open, close it
        $(trigger).attr('aria-expanded', 'false');
        $('#' + panel_id).slideUp();
      }
    }
  }

  // Checking the width of the screen and adjusting menu location accordingly
  function adjustMenuLocation() {
    // Using innerWidth for cross-browser accuracy
    let screen_size = window.innerWidth;
    let menu = $('.aten-fse-sidebar-menu');
    let menu_is_mobile = $(menu).hasClass('mobile-aligned');
    let menu_title = $('.mobile-button-title').text();
    let mobile_accordion_button = $(menu).find('h2 button');

    // If the screen is small and the menu is in the sidebar
    if (screen_size < 1199 && !menu_is_mobile) {
      // Adding " Menu" to the menu title
      $('.mobile-button-title').text(adjustMenuTitle(menu_title));
      // Activating the mobile accordion behavior
      $(mobile_accordion_button).addClass('mobile-trigger');
      // Toggling the menu closed manually
      $('.sidebar-item-wrapper').slideUp();
      // Adding class for mobile styling and moving it to the start of the main content block
      $(menu).addClass('mobile-aligned').prependTo('.sidebar-layout-content');
      // Activate mobile accordion button
      $('.mobile-trigger')
        .unbind()
        .click(function () {
          togglePanel($(this));
        });
      // If the screen is large and the menu is in mobile
    } else if (screen_size >= 1199 && menu_is_mobile) {
      // Removing " Menu" from the menu title
      $('.mobile-button-title').text(adjustMenuTitle(menu_title));
      // Disabling the mobile accordion behavior
      $(mobile_accordion_button).removeClass('mobile-trigger');
      // Toggling the menu open manually
      $('.sidebar-item-wrapper').slideDown();
      // Removing the class for mobile styling and moving it to the top of the sidebar block group
      $(menu).removeClass('mobile-aligned').prependTo('.basic-page-sidebar');
    }
  }

  // Appending " Menu" to the passed title string parameter, used for mobile view
  function adjustMenuTitle(title) {
    let new_title = title;
    if (!title.includes('Happening Recently')) {
      // Check to see if the title already includes " Menu" and remove if so
      if (title.includes(' Menu')) {
        new_title = title.replace(' Menu', '');
      } else {
        new_title = title + ' Menu';
      }
    }

    return new_title;
  }
});

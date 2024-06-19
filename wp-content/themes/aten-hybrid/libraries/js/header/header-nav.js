/**
 * Custom JS for the main site navigation bar
 */

jQuery(document).ready(function ($) {
  // Remove empty "Translate" option from GTranslate
  $('.gt_selector').find('option[value=""]').remove();

  // Add aria-label to the header nav wrappers
  $('#mega-menu-wrap-main-nav').attr('aria-label', 'Main Menu');
  $('#mega-menu-wrap-primary').attr('aria-label', 'Utility Menu');

  // Removing wrapper on mobile menu toggle button
  $('#mega-menu-wrap-main-nav .mega-toggle-label').unwrap();

  // Removing duplicate Menu text label added by Mega Menu plugin
  $(
    '#mega-menu-wrap-main-nav .mega-toggle-label .mega-toggle-label-open'
  ).remove();
  $(
    '#mega-menu-wrap-main-nav .mega-toggle-label .mega-toggle-label-closed'
  ).addClass('mega-toggle-label-open');

  // Adding icon before and after Translate link
  $('<span class="menu-icon">language</span>').appendTo('.gtranslate_wrapper');
  $('<span class="menu-icon right">expand_more</span>').appendTo(
    '.gtranslate_wrapper'
  );

  // Preventing icon spans from being translated so that icons remain the same regardless of language selected
  $('.menu-icon, .mobile-menu-icon, .wp-block-search__button').addClass(
    'notranslate'
  );

  // Converting the mobile menu trigger into a button element
  $('span.mega-toggle-label').convertElement('button');

  // Triggering mega menu using top level buttons on click/space bar
  $('button.mega-menu-button').on('click', function () {
    triggerMegaMenu($(this));
  });

  // Overriding the MMM plugin default behavior to allow more than one sub-menu open at a time on mobile when keyboard navigating
  $('.mega-sub-menu')
    .parent()
    .on('keyup.megamenu', function (e) {
      // Checking for the pressed key and the new focused element
      var keyCode = e.keyCode || e.which;
      var active_link = $(e.target);

      // Listening for Tab key
      if (keyCode === 9) {
        // Adding class from the plugin
        $(this).addClass('mega-keyboard-navigation');
        // If the new link is not a top-level element
        if (active_link.parent().parent().is('.max-mega-menu')) {
          // Override the menu collapse from the plugin to keep the submenu open
          e.stopImmediatePropagation();
        }
      }
    });

  // On resize and page load, assess window width and adjust menu structure
  customizeMenus();

  $(window).resize(function () {
    customizeMenus();
  });

  function customizeMenus() {
    arrangeMenus();
    activateButtonTriggers();
    standardizeEscapeFunctionality();
  }

  // Checks the screen size and overrides MMM plugin functionality to allow keyboard navigation in mobile mode
  function activateButtonTriggers() {
    let screen_size = window.innerWidth;
    if (screen_size < 1024) {
      // Triggering mega menu using top level buttons on enter key
      $('button.mega-menu-button')
        .unbind('keypress keyup')
        .on('keypress', function (e) {
          // Listening for the press of the enter key
          if (e.which == 13) {
            e.preventDefault();
            return false;
          }
        });
    } else {
      // Triggering mega menu using top level buttons on enter key
      $('button.mega-menu-button')
        .unbind('keypress keyup')
        .on('keydown', function (e) {
          // Listening for the press of the enter key
          if (e.which == 13) {
            e.preventDefault();
            e.stopImmediatePropagation();
            triggerMegaMenu($(this));
          }
        });
    }
  }

  // Checks the screen size and rearranges the menus accordingly
  function arrangeMenus() {
    // Get the screen size
    let screen_size = window.innerWidth;

    // If screen is mobile size
    if (screen_size < 768) {
      // If not already in mobile layout, restructure
      if (!$('header').hasClass('mobile-layout')) {
        // Add class for mobile layout
        $('header').addClass('mobile-layout');
        // If it's already in tablet layout
        if ($('header').hasClass('tablet-layout')) {
          // Remove the tablet layout class
          $('header').removeClass('tablet-layout');
        } else {
          // If it's already in desktop layout, move the utility menu and translation widget into main nav
          $('#mega-menu-primary')
            .children('li')
            .addClass('utility-items')
            .appendTo('#mega-menu-main-nav');
          $('.gtranslate_wrapper').wrap(
            '<li id="mobile-translate-widget"></li>'
          );
          $('#mobile-translate-widget').appendTo('#mega-menu-main-nav');
        }
        // Move the search into the main nav
        $('header form.wp-block-search').wrap(
          '<li id="mobile-search-widget"></li>'
        );
        $('#mobile-search-widget').prependTo('#mega-menu-main-nav');
      }
      // Adjust placement of mobile menu for notification bar height
      if ($('body').hasClass('has-easy-notification-bar')) {
        // Get height of notification bar
        let notificationBarHeight = $('.easy-notification-bar').outerHeight();
        // Add it to the default 70px offset
        let newTopVal = notificationBarHeight + 70;
        // Apply the new top style
        $('#mega-menu-main-nav').css({
          top: newTopVal + 'px',
        });
      } else {
        // Reset if the notification is closed
        $('#mega-menu-main-nav').css({
          top: '70px',
        });
      }
      // Tablet layout
    } else if (screen_size >= 768 && screen_size < 1024) {
      // If not already in tablet layout
      if (!$('header').hasClass('tablet-layout')) {
        // Add class for tablet layout
        $('header').addClass('tablet-layout');
        // If already in mobile layout
        if ($('header').hasClass('mobile-layout')) {
          // Remove class for mobile layout
          $('header').removeClass('mobile-layout');
          // Remove the search widget and place it back into the utility bar
          $('header form.wp-block-search')
            .detach()
            .insertAfter('#mega-menu-wrap-primary');
          $('#mobile-search-widget').remove();
        } else {
          // If in desktop layout, move the utility menu and translation widget into main nav
          $('#mega-menu-primary')
            .children('li')
            .addClass('utility-items')
            .appendTo('#mega-menu-main-nav');
          $('.gtranslate_wrapper').wrap(
            '<li id="mobile-translate-widget"></li>'
          );
          $('#mobile-translate-widget').appendTo('#mega-menu-main-nav');
        }
      }
      // Adjust placement of mobile menu for notification bar height
      if ($('body').hasClass('has-easy-notification-bar')) {
        // Get height of notification bar
        let notificationBarHeight = $('.easy-notification-bar').outerHeight();
        // Add it to the default 70px offset
        let newTopVal = notificationBarHeight + 70;
        // Apply the new top style
        $('#mega-menu-main-nav').css({
          top: newTopVal + 'px',
        });
      } else {
        // Reset if the notification is closed
        $('#mega-menu-main-nav').css({
          top: '70px',
        });
      }
      // Desktop layout
    } else {
      // If already in tablet or mobile layout
      if (
        $('header').hasClass('tablet-layout') ||
        $('header').hasClass('mobile-layout')
      ) {
        // Clear the tablet and mobile classes
        $('header').removeClass('mobile-layout tablet-layout');
        // Move utility nav back into its own nav container
        $('#mega-menu-main-nav')
          .children('.utility-items')
          .appendTo('#mega-menu-primary')
          .removeClass('utility-items');
        // Move the search form back into the utility bar
        $('header form.wp-block-search')
          .detach()
          .insertAfter('#mega-menu-wrap-primary');
        // Move the translation widget back into the utility bar
        $('.gtranslate_wrapper')
          .detach()
          .insertBefore('header form.wp-block-search');
        $('#mobile-search-widget, #mobile-translate-widget').remove();
      }

      // Closing other open sub-menus when a new one opens
      $('li.mega-menu-item').on('open_panel', function () {
        var current_menu = $(this).closest('ul.max-mega-menu');
        $('ul.max-mega-menu')
          .not(current_menu)
          .each(function () {
            $(this).data('maxmegamenu').hideAllPanels();
          });
      });
    }
  }

  // Checks screen size and adjusts ESC key functionality for submenus
  function standardizeEscapeFunctionality() {
    let screen_size = window.innerWidth;
    if (screen_size < 1024) {
      // Checking for pressed ESC key when submenu link is in focus
      $('.mega-menu-item-has-children .mega-sub-menu .mega-menu-link').on(
        'keydown',
        function (e) {
          // Listening for the press of the ESC key
          if (e.key === 'Escape') {
            // Preventing default collapse behavior
            e.stopImmediatePropagation();
            // Set focus to submenu parent item
            let parent_btn = $(this)
              .closest('.mega-menu-item-has-children')
              .find('.mega-menu-button');
            $(parent_btn).focus();
            // Collapsing the parent submenu
            triggerMegaMenu(parent_btn);
          }
        }
      );

      // Checking for pressed ESC key while in top-level focus
      $('.mega-menu-item-has-children > .mega-menu-button').on(
        'keydown',
        function (e) {
          // Listening for the press of the ESC key
          if (e.key === 'Escape') {
            // Returning focus to the menu button after main menu close
            $('.mega-toggle-label').focus();
          }
        }
      );
    }
  }

  // Triggering the mega menu from a button event using a hidden Mega Menu link
  function triggerMegaMenu(button) {
    var panel = button.siblings('.mega-menu-link');
    var menu = button.closest('ul');
    var item = button.closest('li');

    if (
      $(button).attr('aria-expanded') === 'false' &&
      !$(item).hasClass('menu-toggle-on')
    ) {
      $(button).attr('aria-expanded', 'true');
      $(menu).data('maxmegamenu').showPanel(panel);
    } else {
      $(button).attr('aria-expanded', 'false');
      $(menu).data('maxmegamenu').hidePanel(panel);
    }
  }
});

/**
 * JavaScript code to run in the front-end on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   'viewScript': 'file:./view.js'
 * }
 * ```
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 * 
 * TODO:
 * - on tab past submenu items, close submenu before moving focus to the next element
 * - verify that backward tab doesn't reopen submenu
 * - convert to JS once functional through jQuery
 */

jQuery(document).ready(function($){
    $('.adg-a11y-megamenu-nav-container').prepend('<button class="adg-a11y-mobile-menu-toggle" aria-expanded="false"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span><span class="dashicons dashicons-menu" aria-hidden="true"></span><span class="adg-a11y-mobile-menu-toggle-text">Menu</span></button>');

    $('.adg-a11y-megamenu-button').click(function(){
        toggleMenu($(this));
    });

    $( window ).on( "resize", function() {
        toggleMobileMenuActivation();
    } );

    toggleMobileMenuActivation();

    $('.adg-a11y-megamenu .menu-item-type-custom a, .adg-a11y-megamenu .adg-a11y-megamenu-button, .adg-a11y-megamenu .menu-item, .adg-a11y-mobile-menu-toggle').on('keydown', function(event) {
        event.stopImmediatePropagation();
        let next_list_item = $(this).closest('li').next();
        let prev_list_item = $(this).closest('li').prev();

        switch(event.key) {
            case "Escape":
                let target_button;
                if($(this).hasClass('adg-a11y-megamenu-button')) {
                    if($(this).attr('aria-expanded') == 'true') {
                        target_button = $(this);
                    } if($(this).parents('.submenu-expanded').length) {
                        target_button = $(this).closest('.submenu-expanded').siblings('.adg-a11y-megamenu-button');
                    } else {
                        target_button = $(this).closest('.adg-a11y-mobile-menu-wrapper').find('.adg-a11y-mobile-menu-toggle');
                    }
                } else {
                    target_button = $(this).closest('ul').siblings('button');
                }
                if($(target_button).attr('aria-expanded') == 'true') {
                    toggleMenu(target_button);
                }
                $(target_button).focus();
                break;
            case "ArrowLeft":
                /** TODO:
                 * If the current item is a top-level item, move focus to the previous top-level item.
                 * If the current item is NOT a top-level item, close the current open submenu and move focus to the previous top-level item.
                 * If the current item is at the start of the top level, loop to the end.
                 */
                event.preventDefault();
                if(prev_list_item.length) {
                    prev_list_item.find('a, button').focus();
                } else {
                    $(this).closest('ul').siblings('button').focus();
                }
                break;
            case "ArrowRight":
                event.preventDefault();
                if($(this).hasClass('adg-a11y-megamenu-button') && ($(this).attr('aria-expanded') == 'true')) {
                    toggleMenu($(this));
                } 
                if(!$(this).parents('.submenu-expanded').length) {
                    if(next_list_item.length) {
                        next_list_item.find('a, button').focus();
                    } else {
                        $(this).closest('.adg-a11y-megamenu').find('li').first().find('a, button').focus();
                    }
                } else {
                    let open_menu_buttons = $(this).parentsUntil('.adg-a11y-megamenu', '.submenu-expanded').siblings('.adg-a11y-megamenu-button.submenu-open');
                    if(open_menu_buttons.length) {
                        open_menu_buttons.each(function(){
                            toggleMenu($(this));
                        });
                    }
                    $(this).parents('.adg-a11y-megamenu > .menu-item-has-children').next().find('a, button').focus();
                }

                break;
            case "ArrowUp":
                event.preventDefault();
                if($(this).parents('.submenu-expanded').length && prev_list_item.length) {
                    prev_list_item.find('a, button').focus();
                } else {
                    $(this).closest('.submenu-expanded').find('li:last-child').find('a, button').focus();
                }
                break;
            case "ArrowDown":
                event.preventDefault();
                if($(this).hasClass('adg-a11y-mobile-menu-toggle')) {
                    if($(this).attr('aria-expanded') == 'false') {
                        toggleMenu($(this));
                    }
                    $(this).siblings('.menu-expanded').find('li').first().find('a, button').focus();   
                } else if($(this).hasClass('adg-a11y-megamenu-button')) {
                    if($(this).attr('aria-expanded') == 'false') {
                        toggleMenu($(this));
                    }  
                    $(this).siblings('.submenu-expanded').find('li').first().find('a, button').focus();
                } else {
                    if($(this).parents('.submenu-expanded').length && next_list_item.length) {
                        next_list_item.find('a, button').focus();
                    } else {
                        $(this).closest('.submenu-expanded').find('li').first().find('a, button').focus();
                    }
                }
                break;
            }
    });

    $('.adg-a11y-mobile-menu-toggle').click(function(){
        toggleMenu($(this));
    });

    function toggleMobileMenuActivation() {
        // Get viewport width and mobile breakpoint from block settings
        let viewport_width = $( window ).width();
        let mobile_breakpoint = $('nav.adg-a11y-megamenu-wrap').data('mobile-breakpoint');

        // If the viewport is smaller or equal to the mobile breakpoint
        if(viewport_width <= mobile_breakpoint) {
            // Add mobile classes to menu and menu wrapper
            $('.adg-a11y-mobile-menu-toggle').addClass('adg-a11y-mobile-menu-active');
            $('.adg-a11y-mobile-menu-toggle').closest('nav').addClass('adg-a11y-mobile-menu-wrapper');
        } else { // If the viewport is larger than the mobile breakpoint
            // Remove mobile classes if present
            if($('.adg-a11y-mobile-menu-toggle').hasClass('adg-a11y-mobile-menu-active')) {
                $('.adg-a11y-mobile-menu-toggle.adg-a11y-mobile-menu-active').removeClass('adg-a11y-mobile-menu-active');
                $('.adg-a11y-mobile-menu-toggle').closest('nav').removeClass('adg-a11y-mobile-menu-wrapper');
            }
        }
    }

    function toggleMenu(trigger_button) {
        // If expanding the mobile menu
        if($(trigger_button).hasClass('adg-a11y-mobile-menu-toggle')) {
            if($(trigger_button).attr('aria-expanded') == 'true') {
                $(trigger_button).siblings('ul.adg-a11y-megamenu').removeClass('menu-expanded');
                $(trigger_button).attr('aria-expanded', 'false');
                $(trigger_button).find('.dashicons-no-alt').removeClass('active-icon');
                $(trigger_button).find('.dashicons-menu').addClass('active-icon');
            } else {
                $(trigger_button).siblings('ul.adg-a11y-megamenu').addClass('menu-expanded');
                $(trigger_button).attr('aria-expanded', 'true');
                $(trigger_button).find('.dashicons-no-alt').addClass('active-icon');
                $(trigger_button).find('.dashicons-menu').removeClass('active-icon');
            }
        } else { // If expanding a submenu
            let target_sub_menu = $(trigger_button).siblings('.sub-menu');
            if($(trigger_button).hasClass('submenu-open')) {
                $(trigger_button).attr('aria-expanded','false');
                $(target_sub_menu).removeClass('submenu-expanded');
            } else {
                if(!$(trigger_button).parents('.submenu-expanded').length) {
                    $('.adg-a11y-megamenu-button.submenu-open').each(function(){
                        $(this).attr('aria-expanded','false');
                        $(this).removeClass('submenu-open');
                        $(this).siblings('.sub-menu').removeClass('submenu-expanded');
                    });
                }
                $(trigger_button).attr('aria-expanded','true');
                $(target_sub_menu).addClass('submenu-expanded');
            }

            $(trigger_button).toggleClass('submenu-open');
        }
    }
});
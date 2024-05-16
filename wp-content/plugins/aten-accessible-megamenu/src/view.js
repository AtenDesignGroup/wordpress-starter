/**
 * JavaScript code to run in the front-end on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 * 
 * TODO:
 * - convert to JS once functional through jQuery
 */

jQuery(document).ready(function($){
    document.querySelector('.adg-a11y-megamenu-nav-container').insertAdjacentHTML('afterbegin', '<button class="adg-a11y-mobile-menu-toggle" aria-expanded="false"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span><span class="dashicons dashicons-menu" aria-hidden="true"></span><span class="adg-a11y-mobile-menu-toggle-text">Menu</span></button>');

    window.addEventListener("resize", toggleMobileMenuActivation);

    toggleMobileMenuActivation();

    var megamenu_buttons = document.querySelectorAll('.adg-a11y-megamenu-button, .adg-a11y-mobile-menu-toggle');
    for (var i = 0; i < megamenu_buttons.length; i++) {
        megamenu_buttons[i].addEventListener("click", function(){
            toggleMenu(this);
        });
    }

    $('.adg-a11y-megamenu .menu-item-type-custom a, .adg-a11y-megamenu .adg-a11y-megamenu-button, .adg-a11y-megamenu .menu-item, .adg-a11y-mobile-menu-toggle').on('keydown', function(event) {
        event.stopImmediatePropagation();
        let next_list_item = $(this).closest('li').next();
        let prev_list_item = $(this).closest('li').prev();

        switch(event.key) {
            case "Escape":
                let target_button;
                if($(this).classList.contains('adg-a11y-megamenu-button')) {
                    if($(this).getAttribute('aria-expanded') == 'true') {
                        target_button = $(this);
                    } if($(this).parents('.submenu-expanded').length) {
                        target_button = $(this).closest('.submenu-expanded').siblings('.adg-a11y-megamenu-button');
                    } else {
                        target_button = $(this).closest('.adg-a11y-mobile-menu-wrapper').find('.adg-a11y-mobile-menu-toggle');
                    }
                } else {
                    target_button = $(this).closest('ul').siblings('button');
                }
                if($(target_button).getAttribute('aria-expanded') == 'true') {
                    toggleMenu(target_button);
                }
                $(target_button).focus();
                break;
            case "ArrowRight":
                event.preventDefault();
                if($(this).classList.contains('adg-a11y-megamenu-button') && ($(this).getAttribute('aria-expanded') == 'true')) {
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
                    if($(this).parents('.adg-a11y-menu-item-level-0').next().length) {
                        $(this).parents('.adg-a11y-menu-item-level-0').next().find('a, button').focus();
                    } else {
                        $(this).parents('.adg-a11y-megamenu').find('li').first().find('a, button').focus();
                    }   
                }
                break;
            case "ArrowLeft":
                event.preventDefault();
                if($(this).classList.contains('adg-a11y-megamenu-button') && ($(this).getAttribute('aria-expanded') == 'true')) {
                    toggleMenu($(this));
                } 
                if(!$(this).parents('.submenu-expanded').length) {
                    if(prev_list_item.length) {
                        prev_list_item.find('a, button').focus();
                    } else {
                        $(this).closest('.adg-a11y-megamenu').find('li:last-child').find('a, button').focus();
                    }
                } else {
                    let open_menu_buttons = $(this).parentsUntil('.adg-a11y-megamenu', '.submenu-expanded').siblings('.adg-a11y-megamenu-button.submenu-open');
                    if(open_menu_buttons.length) {
                        open_menu_buttons.each(function(){
                            toggleMenu($(this));
                        });
                    }
                    if($(this).parents('.adg-a11y-menu-item-level-0').prev().length) {
                        $(this).parents('.adg-a11y-menu-item-level-0').prev().find('a, button').focus();
                    } else {
                        $(this).parents('.adg-a11y-megamenu').find('li:last-child').find('a, button').focus();
                    }   
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
                if($(this).classList.contains('adg-a11y-mobile-menu-toggle')) {
                    if($(this).getAttribute('aria-expanded') == 'false') {
                        toggleMenu($(this));
                    }
                    $(this).siblings('.menu-expanded').find('li').first().find('a, button').focus();   
                } else if($(this).classList.contains('adg-a11y-megamenu-button')) {
                    if($(this).getAttribute('aria-expanded') == 'false') {
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
            case "Tab":
                if(event.shiftKey){
                    if((!prev_list_item.length) && $(this).parent('li').classList.contains('adg-a11y-menu-item-level-1')) {
                        toggleMenu($(this).parents('.adg-a11y-menu-item-level-0').find('a, button'));
                    }
                }  else if((!$(this).parent('li').classList.contains('menu-item-has-children')) && (!next_list_item.length) && (!event.shiftKey)) {
                    toggleMenu($(this).parents('.adg-a11y-menu-item-level-0').find('a, button'));
                }
                break;
        }
    });

    function toggleMobileMenuActivation() {
        // Get mobile breakpoint from block settings
        let mobile_breakpoint = document.querySelector('.adg-a11y-megamenu-wrap').dataset.mobileBreakpoint;
        let menu_toggle = document.querySelector('.adg-a11y-mobile-menu-toggle');

        // If the viewport is smaller or equal to the mobile breakpoint
        if(window.matchMedia("(max-width: " + mobile_breakpoint + "px)").matches) {
            // Add mobile classes to menu and menu wrapper
            menu_toggle.classList.add("adg-a11y-mobile-menu-active");
            menu_toggle.closest('nav').classList.add('adg-a11y-mobile-menu-wrapper');
        } else { // If the viewport is larger than the mobile breakpoint
            // Remove mobile classes if present
            if(menu_toggle.classList.contains('adg-a11y-mobile-menu-active')) {
                menu_toggle.classList.remove("adg-a11y-mobile-menu-active");
                menu_toggle.closest('nav').classList.remove('adg-a11y-mobile-menu-wrapper');
            }
        }
    }

    function toggleMenu(trigger_button) {
        // If expanding the mobile menu
        if(trigger_button.classList.contains('adg-a11y-mobile-menu-toggle')) {
            if(trigger_button.getAttribute('aria-expanded') == 'true') {
                $(trigger_button).siblings('ul.adg-a11y-megamenu').classList.remove('menu-expanded');
                trigger_button.setAttribute('aria-expanded', 'false');
                trigger_button.querySelector('.dashicons-no-alt').classList.remove('active-icon');
                trigger_button.querySelector('.dashicons-menu').classList.add('active-icon');
            } else {
                $(trigger_button).siblings('ul.adg-a11y-megamenu').classList.add('menu-expanded');
                trigger_button.setAttribute('aria-expanded', 'true');
                trigger_button.querySelector('.dashicons-no-alt').classList.add('active-icon');
                trigger_button.querySelector('.dashicons-menu').classList.remove('active-icon');
            }
        } else { // If expanding a submenu
            let target_sub_menu = $(trigger_button).siblings('.sub-menu');
            if(trigger_button.classList.contains('submenu-open')) {
                trigger_button.setAttribute('aria-expanded','false');
                $(target_sub_menu).classList.remove('submenu-expanded');
            } else {
                if(!$(trigger_button).parents('.submenu-expanded').length) {
                    $('.adg-a11y-megamenu-button.submenu-open').each(function(){
                        $(this).setAttribute('aria-expanded','false');
                        $(this).classList.remove('submenu-open');
                        $(this).siblings('.sub-menu').classList.remove('submenu-expanded');
                    });
                }
                trigger_button.setAttribute('aria-expanded','true');
                $(target_sub_menu).classList.add('submenu-expanded');
            }

            trigger_button.classList.toggle('submenu-open');
        }
    }
});
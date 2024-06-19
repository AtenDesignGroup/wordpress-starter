/**
 * Main Menu Navigation JS
 */
jQuery(document).ready(function($){

    // On resize and page load, assess window width and adjust menu structure
    adjustMenuStructure();

    $(window).resize(function(){
        adjustMenuStructure();
    });

    // ESC key functionality to close mobile menu and return focus to button
    $('.ccc-mobile-toggle-button.mobile-menu-active').on('keydown', function(event) {
        if(event.key == "Escape") {
            if($(this).attr("aria-expanded") == 'true') {
                toggleMenu($(this));
            }
        }
    });

    // Keyboard navigation for inside mobile menu
    $('.ccc-megamenu li').on('keydown', function(event) {
        switch (event.key) {
            case "Escape":
                let target_button = $(this).closest('ul').siblings('button');
                toggleMenu(target_button);
                $(target_button).focus();
                event.stopPropagation();
                break;
            case "ArrowLeft":
                $(this).prev('li').find('a').focus();
                break;
            case "ArrowRight":
                $(this).next('li').find('a').focus();
                break;
        }
    });

    // Toggle menu on button click
    $('.ccc-mobile-toggle-button').click(function(){
        toggleMenu($(this));
    });

    // Adjusting menu structure based on viewport width
    function adjustMenuStructure() {
        if($('.ccc-mobile-toggle-button.mobile-menu-active').attr('aria-expanded') == 'true') {
            toggleMenu('.ccc-mobile-toggle-button.mobile-menu-active');
        }
        // Get the screen size
        let screen_size = window.innerWidth;

        // If in mobile menu sizes
        if(screen_size < 1024) {
            // Add classes for mobile styling
            $('.ccc-mobile-toggle-button').addClass('mobile-menu-active');
            $('nav.ccc-megamenu-nav').addClass('mobile-menu-wrapper');
            $('.ccc-megamenu').addClass('transition-hidden');

            // Slide up the mobile menu and make sure it's not invisible
            $('nav.mobile-menu-wrapper .ccc-megamenu').slideUp(0, function() {
                $('nav.mobile-menu-wrapper .ccc-megamenu').find('li').animate({opacity: 0}, 0);
                $('nav.mobile-menu-wrapper .ccc-megamenu').css({
                    'opacity': '1',
                });
                $('.ccc-megamenu.transition-hidden').removeClass('transition-hidden');
            });

            // Deactivate desktop logo
            $('.ccc-logo-desktop').addClass('logo-inactive');
            // Swap for mobile logo depending on page light/dark mode
            if($('.header-logo-wrapper.dark-mode').length) {
                $('#ccc-logo-mobile-white').removeClass('logo-inactive');
            } else {
                $('#ccc-logo-mobile-color').removeClass('logo-inactive');
            }

        // If screen size requires desktop menu but mobile logo
        } else if(screen_size > 1023 && screen_size < 1100) {
            // Deactivate desktop logo
            $('.ccc-logo-desktop').addClass('logo-inactive');
            // Activate mobile logo depending on light/dark page mode
            if($('.header-logo-wrapper.dark-mode').length) {
                $('#ccc-logo-mobile-white').removeClass('logo-inactive');
            } else {
                $('#ccc-logo-mobile-color').removeClass('logo-inactive');
            }

            // Swap to desktop menu if in mobile mode
            if($('nav.ccc-megamenu-nav').hasClass('mobile-menu-wrapper')) {
                $('.ccc-mobile-toggle-button.mobile-menu-active').removeClass('mobile-menu-active');
                $('.ccc-mobile-toggle-button').closest('nav').removeClass('mobile-menu-wrapper');
                // Make sure logo is color version if not in dark mode
                if(!$('.header-logo-wrapper.dark-mode').length) { 
                    $('#ccc-logo-mobile-color').removeClass('logo-inactive');
                }
            }

            // Make sure menu is visible
            $('.ccc-megamenu').css({
                'opacity': '1',
                'display': 'flex'
            });
            $('.ccc-megamenu li').animate({opacity: 1}, 0);
        // If in desktop size for menu and logo
        } else {
            // Deactivate mobile menu mode
            if($('nav.ccc-megamenu-nav').hasClass('mobile-menu-wrapper')) {
                $('.ccc-mobile-toggle-button.mobile-menu-active').removeClass('mobile-menu-active');
                $('.ccc-mobile-toggle-button').closest('nav').removeClass('mobile-menu-wrapper');
            }
            // Activate desktop logo
            $('.ccc-logo-desktop').removeClass('logo-inactive');
            $('.ccc-logo-mobile').addClass('logo-inactive');

            // Make sure menu is visible
            $('.ccc-megamenu').delay('100').css({
                'opacity': '1',
                'display': 'flex'
            });
            $('.ccc-megamenu li').animate({opacity: 1}, 0);
        }
    }

    function toggleMenu(trigger_button) {
        
        if($(trigger_button).hasClass('ccc-mobile-toggle-button')) {
            if($(trigger_button).attr('aria-expanded') == 'true') {
                $(trigger_button).siblings('ul.ccc-megamenu').find('li').animate({opacity: 0});
                $(trigger_button).siblings('ul.ccc-megamenu').slideUp().removeClass('menu-expanded');
               
                $(trigger_button).attr('aria-expanded', 'false');
                $(trigger_button).find('.menu-expanded-icon').removeClass('active-icon');
                $(trigger_button).find('.menu-collapsed-icon').addClass('active-icon');
                if($('#ccc-logo-mobile-color').length) {
                    $('#ccc-logo-mobile-color').animate({opacity: 1}, 350).removeClass('logo-inactive');
                    $('#ccc-logo-mobile-white').animate({opacity: 0}, 450).addClass('logo-inactive');
                    $('#ccc-logo-mobile-color').closest('.header-logo-wrapper.dark-mode').removeClass('dark-mode');
                }
            } else {
                $(trigger_button).siblings('ul.ccc-megamenu').find('li').animate({opacity: 1});
                $(trigger_button).siblings('ul.ccc-megamenu').slideDown().addClass('menu-expanded');
                $(trigger_button).attr('aria-expanded', 'true');
                $(trigger_button).find('.menu-expanded-icon').addClass('active-icon');
                $(trigger_button).find('.menu-collapsed-icon').removeClass('active-icon');
                if($('#ccc-logo-mobile-color').length) {
                    $('#ccc-logo-mobile-color').closest('.header-logo-wrapper').addClass('dark-mode');
                    $('#ccc-logo-mobile-white').animate({opacity: 1}, 350).removeClass('logo-inactive');
                    $('#ccc-logo-mobile-color').animate({opacity: 0}, 450).addClass('logo-inactive');
                }
            }
        }
    }
});
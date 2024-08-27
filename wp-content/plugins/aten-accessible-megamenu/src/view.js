/**
 * JavaScript code to run in the front-end on posts/pages that contain this menu.
 */

document.addEventListener("DOMContentLoaded", function() {
    // Set up variables for use throughout the script.
    const menuContainer = document.querySelector('.adg-a11y-megamenu-nav-container');
    const mobileMenuToggle = document.createElement('button');
    mobileMenuToggle.className = 'adg-a11y-mobile-menu-toggle';
    mobileMenuToggle.setAttribute('aria-expanded', 'false');
    mobileMenuToggle.innerHTML = '<span class="dashicons dashicons-no-alt" aria-hidden="true"></span><span class="dashicons dashicons-menu" aria-hidden="true"></span><span class="adg-a11y-mobile-menu-toggle-text">Menu</span>';
    menuContainer.insertAdjacentElement('afterbegin', mobileMenuToggle);

    // Adjusting to mobile menu on resize and initial load.
    window.addEventListener("resize", toggleMobileMenuActivation);
    toggleMobileMenuActivation();

    // Adding toggle event listeners to all buttons in the menu.
    initializeMenuButtons();

    // Building keyboard navigation for the menu.
    initializeKeyboardNavigation();
});


/**
 * Enables keyboard navigation for the megamenu.
 */
function initializeKeyboardNavigation() {
    const menuItems = document.querySelectorAll('.adg-a11y-megamenu .menu-item-type-custom a, .adg-a11y-megamenu .adg-a11y-megamenu-button, .adg-a11y-megamenu .menu-item, .adg-a11y-mobile-menu-toggle');
    menuItems.forEach(function (item) {
        processKeyboardInput(item, toggleMenu);
    });
}

/**
 * Initializes the menu buttons and attaches a click event listener to each button.
 * When a button is clicked, it calls the provided toggleMenu function for that button's associated menu.
 */
function initializeMenuButtons() {
    const megamenuButtons = document.querySelectorAll('.adg-a11y-megamenu-button, .adg-a11y-mobile-menu-toggle');
    for (let i = 0; i < megamenuButtons.length; i++) {
        megamenuButtons[i].addEventListener("click", function () {
            toggleMenu(this);
        });
    }
}

/**
 * Attaches a keyboard event listener to the given item and handles keyboard interactions.
 *
 * @param {HTMLElement} item - The menu item to attach the event listener to.
 */
function processKeyboardInput(item) {
    item.addEventListener('keydown', function (event) {
        event.stopImmediatePropagation();
        let nextListItem;
        let prevListItem;
        // Set up variables for the next and previous list items.
        if (this.classList.contains('adg-a11y-mobile-menu-toggle')) {
            // If the current item is the mobile menu toggle button, there are no sibling items to target.
            nextListItem = this.nextElementSibling;
            prevListItem = null;
        } else {
            nextListItem = this.closest('li').nextElementSibling;
            prevListItem = this.closest('li').previousElementSibling;
        }

        switch (event.key) {
            // ESC key closes the current menu or submenu and focuses on the parent button.
            case "Escape":
                let targetButton;
                if (this.classList.contains('adg-a11y-megamenu-button')) {
                    if (this.getAttribute('aria-expanded') == 'true') {
                        targetButton = this;
                    }
                    if (this.closest('.submenu-expanded')) {
                        targetButton = this.closest('.submenu-expanded').previousElementSibling;
                    } else {
                        targetButton = this.closest('.adg-a11y-mobile-menu-wrapper').querySelector('.adg-a11y-mobile-menu-toggle');
                    }
                } else {
                    targetButton = this.closest('ul').previousElementSibling;
                }
                if (targetButton.getAttribute('aria-expanded') == 'true') {
                    toggleMenu(targetButton);
                }
                targetButton.focus();
                break;
            // Right arrow key moves focus to the next menu item, looping back to the first item if at the end.
            case "ArrowRight":
                event.preventDefault();
                if (this.classList.contains('adg-a11y-megamenu-button') && (this.getAttribute('aria-expanded') == 'true')) {
                    toggleMenu(this);
                }
                if (!this.closest('.submenu-expanded')) {
                    if (nextListItem) {
                        nextListItem.querySelector('a, button').focus();
                    } else {
                        this.closest('.adg-a11y-megamenu').querySelector('li').querySelector('a, button').focus();
                    }
                } else {
                    let openMenuButtons = this.closest('.submenu-expanded').previousElementSibling.querySelectorAll('.adg-a11y-megamenu-button.submenu-open');
                    if (openMenuButtons.length) {
                        openMenuButtons.forEach(function (button) {
                            toggleMenu(button);
                        });
                    }
                    if (this.closest('.adg-a11y-menu-item-level-0').nextElementSibling) {
                        this.closest('.adg-a11y-menu-item-level-0').nextElementSibling.querySelector('a, button').focus();
                    } else {
                        this.closest('.adg-a11y-megamenu').querySelector('li').querySelector('a, button').focus();
                    }
                }
                break;
            // Left arrow key moves focus to the previous menu item, looping back to the last item if at the beginning.
            case "ArrowLeft":
                event.preventDefault();
                if (this.classList.contains('adg-a11y-megamenu-button') && (this.getAttribute('aria-expanded') == 'true')) {
                    toggleMenu(this);
                }
                if (!this.closest('.submenu-expanded')) {
                    if (prevListItem) {
                        prevListItem.querySelector('a, button').focus();
                    } else {
                        this.closest('.adg-a11y-megamenu').lastElementChild.querySelector('a, button').focus();
                    }
                } else {
                    let openMenuButtons = this.closest('.submenu-expanded').previousElementSibling.querySelectorAll('.adg-a11y-megamenu-button.submenu-open');
                    if (openMenuButtons.length) {
                        openMenuButtons.forEach(function (button) {
                            toggleMenu(button);
                        });
                    }
                    if (this.closest('.adg-a11y-menu-item-level-0').previousElementSibling) {
                        this.closest('.adg-a11y-menu-item-level-0').previousElementSibling.querySelector('a, button').focus();
                    } else {
                        this.closest('.adg-a11y-megamenu').lastElementChild.querySelector('a, button').focus();
                    }
                }
                break;
            // Up arrow key moves focus to the previous menu item if in a submenu, looping back to the last item if at the beginning.
            case "ArrowUp":
                event.preventDefault();
                if (this.closest('.submenu-expanded') && prevListItem) {
                    prevListItem.querySelector('a, button').focus();
                } else {
                    this.closest('.submenu-expanded').lastElementChild.querySelector('a, button').focus();
                }
                break;
            // Down arrow key opens submenus and mobile menus, focusing on the first menu item. 
            // If in a submenu, down arrow key moves focus to the next menu item, looping back to the first item if at the end.
            case "ArrowDown":
                event.preventDefault();
                // If the current item is the mobile menu toggle button, open the menu and focus on the first item.
                if (this.classList.contains('adg-a11y-mobile-menu-toggle')) {
                    if (this.getAttribute('aria-expanded') == 'false') {
                        toggleMenu(this);
                    }
                    this.nextElementSibling.querySelector('li').querySelector('a, button').focus();
                } else if (this.classList.contains('adg-a11y-megamenu-button')) {
                    // If the current item is a submenu toggle button, open the menu and focus on the first item.
                    if (this.getAttribute('aria-expanded') == 'false') {
                        toggleMenu(this);
                    }
                    this.nextElementSibling.querySelector('li').querySelector('a, button').focus();
                } else {
                    if (this.closest('.submenu-expanded') && nextListItem) {
                        nextListItem.querySelector('a, button').focus();
                    } else {
                        this.closest('.submenu-expanded').querySelector('li:first-child').querySelector('a, button').focus();
                    }
                }
                break;
            // Tab key closes the currently open submenu and moves focus to the next menu item.
            case "Tab":
                // If reverse-tabbing out of a submenu, close the submenu and move focus to the parent menu item.
                if (event.shiftKey) {
                    if (!prevListItem && this.closest('li').classList.contains('adg-a11y-menu-item-level-1')) {
                        toggleMenu(this.closest('.adg-a11y-menu-item-level-0').querySelector('a, button'));
                    }
                } else if (!nextListItem && !event.shiftKey) {
                    // If tabbing past the last item of a submenu, close the submenu and move focus to the next main-level menu item.
                    toggleMenu(this.closest('.adg-a11y-menu-item-level-0').querySelector('a, button'));
                }
                break;
        }
    });
}

/**
 * Toggles the menu open or closed based on the provided trigger button.
 * @param {HTMLElement} triggerButton - The button element that triggers the menu toggle.
 */
 function toggleMenu(triggerButton) {
    // Mobile menu toggle actions.
    if (triggerButton.classList.contains('adg-a11y-mobile-menu-toggle')) {
        if (triggerButton.getAttribute('aria-expanded') == 'true') {
            triggerButton.nextElementSibling.classList.remove('menu-expanded');
            triggerButton.setAttribute('aria-expanded', 'false');
            triggerButton.querySelector('.dashicons-no-alt').classList.remove('active-icon');
            triggerButton.querySelector('.dashicons-menu').classList.add('active-icon');
        } else {
            triggerButton.nextElementSibling.classList.add('menu-expanded');
            triggerButton.setAttribute('aria-expanded', 'true');
            triggerButton.querySelector('.dashicons-no-alt').classList.add('active-icon');
            triggerButton.querySelector('.dashicons-menu').classList.remove('active-icon');
        }
    } else {
        // Submenu toggle actions.
        var targetSubMenu = triggerButton.nextElementSibling;
        if (triggerButton.classList.contains('submenu-open')) {
            triggerButton.setAttribute('aria-expanded', 'false');
            targetSubMenu.classList.remove('submenu-expanded');
        } else {
            if (!triggerButton.closest('.submenu-expanded')) {
                document.querySelectorAll('.adg-a11y-megamenu-button.submenu-open').forEach(function(button) {
                    button.setAttribute('aria-expanded','false');
                    button.classList.remove('submenu-open');
                    button.parentElement.querySelectorAll('.sub-menu').forEach(function(sibling){
                        sibling.classList.remove('submenu-expanded');
                    });
                });
            }
            triggerButton.setAttribute('aria-expanded', 'true');
            targetSubMenu.classList.add('submenu-expanded');
        }
        triggerButton.classList.toggle('submenu-open');
    }
}

/**
 * Toggles the activation of the mobile menu based on the window width.
 * Breakpoint is pulled from block settings as a data attribute.
 */
function toggleMobileMenuActivation() {
    const mobileBreakpoint = document.querySelector('.adg-a11y-megamenu-wrap').dataset.mobileBreakpoint;
    const menuToggle = document.querySelector('.adg-a11y-mobile-menu-toggle');
    if (window.matchMedia("(max-width: " + mobileBreakpoint + "px)").matches) {
        menuToggle.classList.add("adg-a11y-mobile-menu-active");
        menuToggle.closest('nav').classList.add('adg-a11y-mobile-menu-wrapper');
    } else {
        if (menuToggle.classList.contains('adg-a11y-mobile-menu-active')) {
            menuToggle.classList.remove("adg-a11y-mobile-menu-active");
            menuToggle.closest('nav').classList.remove('adg-a11y-mobile-menu-wrapper');
        }
    }
}
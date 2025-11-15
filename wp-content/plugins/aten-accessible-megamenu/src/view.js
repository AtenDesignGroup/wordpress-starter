/**
 * JavaScript code to run in the front-end on posts/pages that contain this menu.
 */

document.addEventListener("DOMContentLoaded", function() {
    const menus = document.querySelectorAll('.adg-a11y-megamenu-wrap');

    const generateUniqueId = (length) => {
        const characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let id = '';
        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * characters.length);
            id += characters[randomIndex];
        }
        return id;
    }

    window.addEventListener("resize", () => {
        menus.forEach((menu) => {
            toggleMobileMenuActivation(menu);
        });
    });
    
    menus.forEach((menu) => {
        // Set up variables for use throughout the script.
        const menuContainer = menu.querySelector('.adg-a11y-megamenu-nav-container');
        const mobileMenuToggle = document.createElement('button');

        // Building mobile menu toggle button.
        mobileMenuToggle.setAttribute('id', `adg-a11y-mobile-menu-toggle-${generateUniqueId(6)}`);
        mobileMenuToggle.className = 'adg-a11y-mobile-menu-toggle';
        mobileMenuToggle.setAttribute('aria-expanded', 'false');
        mobileMenuToggle.setAttribute('aria-haspopup', 'menu');
        mobileMenuToggle.setAttribute('aria-label', 'Main navigiation menu');
        mobileMenuToggle.innerHTML = '<span class="dashicons dashicons-no-alt" aria-hidden="true"></span><span class="dashicons dashicons-menu" aria-hidden="true"></span><span class="adg-a11y-mobile-menu-toggle-text">Menu</span>';
        menuContainer.insertAdjacentElement('afterbegin', mobileMenuToggle);

        // Adjusting to mobile menu on initial load.
        toggleMobileMenuActivation(menu);

        // Adding toggle event listeners to all buttons in the menu.
        initializeMenuButtons(menu);

        // Building keyboard navigation for the menu.
        initializeKeyboardNavigation(menu);
    });
});


/**
 * Enables keyboard navigation for the megamenu.
 */
const initializeKeyboardNavigation = (menu) => {
    const menuItems = menu.querySelectorAll('.adg-a11y-megamenu .menu-item-type-custom a, .adg-a11y-megamenu .adg-a11y-megamenu-button, .adg-a11y-megamenu .menu-item, .adg-a11y-mobile-menu-toggle');
    menuItems.forEach((item) => {
        processKeyboardInput(item, toggleMenu);
    });
}

/**
 * Initializes the menu buttons and attaches a click event listener to each button.
 * When a button is clicked, it calls the provided toggleMenu function for that button's associated menu.
 */
const initializeMenuButtons = (menu) => {
    const megamenuButtons = menu.querySelectorAll('.adg-a11y-megamenu-button, .adg-a11y-mobile-menu-toggle');
    for (let i = 0; i < megamenuButtons.length; i++) {
        megamenuButtons[i].addEventListener("click", function () {
            toggleMenu(this);
        });
    }
}

/**
 * Handles the Escape key to close the current menu or submenu and focus on the parent button.
 */
const handleEscape = (event) => {
    let targetButton;
    if(event.classList.contains('adg-a11y-mobile-menu-toggle') && event.getAttribute('aria-expanded') == 'true') {
        targetButton = event;
    } else if (event.classList.contains('adg-a11y-megamenu-button')) {
        if (event.getAttribute('aria-expanded') == 'true') {
            targetButton = event;
        }
        else if (event.closest('.submenu-expanded')) {
            targetButton = event.closest('.submenu-expanded').previousElementSibling;
        } else {
            targetButton = event.closest('.adg-a11y-mobile-menu-wrapper').querySelector('.adg-a11y-mobile-menu-toggle');
        }
    } else {
        targetButton = event.closest('ul').previousElementSibling;
    }
    if (targetButton.getAttribute('aria-expanded') == 'true') {
        toggleMenu(targetButton);
    }
    targetButton.focus();
}
/**
 * Handles the right arrow key press to navigate the menu.
 * @param {Event} event 
 * @param {HTMLElement} element 
 * @param {HTMLElement} nextListItem 
 */
const arrowRight = (event, element, nextListItem) => {
    event.preventDefault();
    if (element.classList.contains('adg-a11y-megamenu-button') && (element.getAttribute('aria-expanded') == 'true')) {
        toggleMenu(element);
    }
    if (!element.closest('.submenu-expanded')) {
        if (nextListItem) {
            nextListItem.querySelector('a, button').focus();
        } else {
            element.closest('.adg-a11y-megamenu').querySelector('li').querySelector('a, button').focus();
        }
    } else {
        let openMenuButton = element.closest('.submenu-expanded').previousElementSibling;
        if (openMenuButton) {
            toggleMenu(openMenuButton);
        }
        if (element.closest('.adg-a11y-menu-item-level-0').nextElementSibling) {
            element.closest('.adg-a11y-menu-item-level-0').nextElementSibling.querySelector('a, button').focus();
        } else {
            element.closest('.adg-a11y-megamenu').querySelector('li').querySelector('a, button').focus();
        }
    }
}

/**
 * Handles the left arrow key press to navigate the menu.
 * @param {Event} event 
 * @param {HTMLElement} element 
 * @param {HTMLElement} prevListItem 
 */
const arrowLeft = (event, element, prevListItem) => {
    event.preventDefault();
    if (element.classList.contains('adg-a11y-megamenu-button') && (element.getAttribute('aria-expanded') == 'true')) {
        toggleMenu(element);
    }
    if (!element.closest('.submenu-expanded')) {
        if (prevListItem) {
            prevListItem.querySelector('a, button').focus();
        } else {
            element.closest('.adg-a11y-megamenu').lastElementChild.querySelector('a, button').focus();
        }
    } else {
        let openMenuButton = element.closest('.submenu-expanded').previousElementSibling;
        if (openMenuButton) {
            toggleMenu(openMenuButton);
        }
        if (element.closest('.adg-a11y-menu-item-level-0').previousElementSibling) {
            element.closest('.adg-a11y-menu-item-level-0').previousElementSibling.querySelector('a, button').focus();
        } else {
            element.closest('.adg-a11y-megamenu').lastElementChild.querySelector('a, button').focus();
        }
    }
}

/**
 * Handles the up arrow key press to navigate the menu.
 * @param {Event} event 
 * @param {HTMLElement} element 
 * @param {HTMLElement} prevListItem 
 */
const arrowUp = (event, element, prevListItem) => {
    event.preventDefault();
    if (element.closest('.submenu-expanded') && prevListItem) {
        prevListItem.querySelector('a, button').focus();
    } else {
        element.closest('.submenu-expanded').lastElementChild.querySelector('a, button').focus();
    }
}

/**
 * Handles the down arrow key press to navigate the menu.
 * @param {Event} event 
 * @param {HTMLElement} element 
 * @param {HTMLElement} nextListItem 
 */
const arrowDown = (event, element, nextListItem) => {
    event.preventDefault();
    // If the current item is the mobile menu toggle button, open the menu and focus on the first item.
    if (element.classList.contains('adg-a11y-mobile-menu-toggle')) {
        if (element.getAttribute('aria-expanded') == 'false') {
            toggleMenu(element);
        }
        element.nextElementSibling.querySelector('li').querySelector('a, button').focus();
    } else if (element.classList.contains('adg-a11y-megamenu-button')) {
        // If the current item is a submenu toggle button, open the menu and focus on the first item.
        if (element.getAttribute('aria-expanded') == 'false') {
            toggleMenu(element);
        }
        element.nextElementSibling.querySelector('li').querySelector('a, button').focus();
    } else {
        if (element.closest('.submenu-expanded') && nextListItem) {
            nextListItem.querySelector('a, button').focus();
        } else {
            element.closest('.submenu-expanded').querySelector('li:first-child').querySelector('a, button').focus();
        }
    }
}

/**
 * Handles the tab key press to navigate the menu.
 * @param {Event} event 
 * @param {HTMLElement} element 
 * @param {HTMLElement} nextListItem 
 * @param {HTMLElement} prevListItem 
 */
const tab = (event, element, nextListItem, prevListItem) => {
    // If reverse-tabbing out of a submenu, close the submenu and move focus to the parent menu item.
    if (event.shiftKey) {
        if (!prevListItem && element.closest('li').classList.contains('adg-a11y-menu-item-level-1')) {
            toggleMenu(element.closest('.adg-a11y-menu-item-level-0').querySelector('a, button'));
        }
    } else if (!nextListItem && !event.shiftKey && !element.classList.contains('adg-a11y-megamenu-button')) {
        // If tabbing past the last item of a submenu, close the submenu and move focus to the next main-level menu item.
        toggleMenu(element.closest('.adg-a11y-menu-item-level-0').querySelector('a, button'));
    }
}

/**
 * Attaches a keyboard event listener to the given item and handles keyboard interactions.
 *
 * @param {HTMLElement} item - The menu item to attach the event listener to.
 * 
 * TODO: Refactor this function to reduce nesting and improve readability.
 */
const processKeyboardInput = (item) => {
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
                handleEscape(this);
                break;
            // Right arrow key moves focus to the next menu item, looping back to the first item if at the end.
            case "ArrowRight":
                arrowRight(event, this, nextListItem);
                break;
            // Left arrow key moves focus to the previous menu item, looping back to the last item if at the beginning.
            case "ArrowLeft":
                arrowLeft(event, this, prevListItem);
                break;
            // Up arrow key moves focus to the previous menu item if in a submenu, looping back to the last item if at the beginning.
            case "ArrowUp":
                arrowUp(event, this, prevListItem);
                break;
            // Down arrow key opens submenus and mobile menus, focusing on the first menu item. 
            // If in a submenu, down arrow key moves focus to the next menu item, looping back to the first item if at the end.
            case "ArrowDown":
                arrowDown(event, this, nextListItem);
                break;
            // Tab key closes the currently open submenu and moves focus to the next menu item.
            case "Tab":
                tab(event, this, nextListItem, prevListItem);
                break;
        }
    });
}

/**
 * Toggles the menu open or closed based on the provided trigger button.
 * @param {HTMLElement} triggerButton - The button element that triggers the menu toggle.
 */
const toggleMenu = (triggerButton) => {
    // Mobile menu toggle actions.
    if (triggerButton.classList.contains('adg-a11y-mobile-menu-toggle')) {
        if (triggerButton.getAttribute('aria-expanded') == 'true') {
            triggerButton.nextElementSibling.classList.remove('menu-expanded');
            triggerButton.setAttribute('aria-expanded', 'false');
            triggerButton.querySelector('.dashicons-no-alt').classList.remove('active-icon');
            triggerButton.querySelector('.dashicons-menu').classList.add('active-icon');
            document.body.classList.remove('adg-a11y-mobile-menu-open');
        } else {
            triggerButton.nextElementSibling.classList.add('menu-expanded');
            triggerButton.setAttribute('aria-expanded', 'true');
            triggerButton.querySelector('.dashicons-no-alt').classList.add('active-icon');
            triggerButton.querySelector('.dashicons-menu').classList.remove('active-icon');
            document.body.classList.add('adg-a11y-mobile-menu-open');
        }
    } else {
        // Submenu toggle actions.
        const targetSubMenu = triggerButton.nextElementSibling;
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
const toggleMobileMenuActivation = (menu) => {
    const mobileBreakpoint = menu.dataset.mobileBreakpoint;
    const menuToggle = menu.querySelector('.adg-a11y-mobile-menu-toggle');

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
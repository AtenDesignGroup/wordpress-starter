=== Accessible MegaMenu ===
Contributors:      Aten Design Group
Tags:              block
Tested up to:      6.1
Stable tag:        0.1.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

A block that generates a fully-accessible megamenu from any WordPress menu.

== Description ==

This plugin provides a custom WordPress Full-Site Editing block that generates a fully-accessible megamenu from any WordPress menu object.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/aten-accessible-megamenu` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

== How to Use ==

1. Add the block to the page or Site Editor region where the menu is to be displayed
2. Select the WordPress menu from the dropdown in the block options pane
3. Set the mobile breakpoint in pixels at which the menu should collapse into a dropdown for mobile devices. Set this value to 999999 to keep the menu collapsed at all times. Set this value to 0 to keep the menu from collapsing at any screen size. Default breakpoint value is 1024. 
4. Style the menu to match the site theming. No theming is provided for the menu out of the box for ease of customization.

== Changelog ==

= 0.1.0 =
* Release

== Roadmap ==

- Display the selected menu and minimal styling in the back end editor
- Animation options for menu transitions
- Stylized basic theming for out of the box basic usage
- Thorough documentation of code and functionality  
- Screenshots of implementation
- Icon fields for custom icons of menu toggle button
- CSS Variables to build optional stylesheet with customized colors, spacing, transitions, and fonts
- Move from JQuery to pure JS for no reliance on external libraries


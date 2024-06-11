=== Admin Menu Editor ===
Contributors: whiteshadow
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=A6P9S6CE3SRSW
Tags: admin, dashboard, menu, security, wpmu
Requires at least: 4.7
Tested up to: 6.4
Stable tag: 1.12.1

Lets you edit the WordPress admin menu. You can re-order, hide or rename menus, add custom menus and more. 

== Description ==
Admin Menu Editor lets you manually edit the Dashboard menu. You can reorder the menus, show/hide specific items, change permissions, and more.

**Features**

* Change menu titles, URLs, icons, CSS classes and so on.
* Organize menu items via drag & drop.
* Change menu permissions by setting the required capability or role.
* Move a menu item to a different submenu. 
* Create custom menus that point to any part of the Dashboard or an external URL.
* Hide/show any menu or menu item. A hidden menu is invisible to all users, including administrators.
* Create login redirects and logout redirects.

The [Pro version](http://w-shadow.com/AdminMenuEditor/) lets you set per-role menu permissions, hide a menu from everyone except a specific user, export your admin menu, drag items between menu levels, make menus open in a new window and more. [Try online demo](http://amedemo.com/wpdemo/demo.php).

**Shortcodes**

The plugin provides a few utility shortcodes. These are mainly intended to help with creating login/logout redirects, but you can also use them in posts and pages.

* `[ame-wp-admin]` - URL of the WordPress dashboard (with a trailing slash).
* `[ame-home-url]` - Site URL. Usually, this is the same as the URL in the "Site Address" field in *Settings -> General*.
* `[ame-user-info field="..."]` - Information about the logged-in user. Parameters:
    * `field` - The part of user profile to display. Supported fields include: `ID`, `user_login`, `display_name`, `locale`, `user_nicename`, `user_url`, and so on.
    * `placeholder` - Optional. Text that will be shown if the visitor is not logged in.
    * `encoding` - Optional. How to encode or escape the output. This is useful if you want to use the shortcode in your own HTML or JS code. Supported values: `auto` (default), `html`, `attr`, `js`, `none`.

**Notes**

* If you delete any of the default menus they will reappear after saving. This is by design. To get rid of a menu for good, either hide it or change it's access permissions.
* In the free version, it's not possible to give a role access to a menu item that it couldn't see before. You can only restrict menu access further.
* In case of emergency, you can reset the menu configuration back to the default by going to http://example.com/wp-admin/?reset\_admin\_menu=1 (replace example.com with your site URL). You must be logged in as an Administrator to do this.

== Installation ==

**Normal installation**

1. Download the admin-menu-editor.zip file to your computer.
2. Unzip the file.
3. Upload the `admin-menu-editor` directory to your `/wp-content/plugins/` directory.
4. Activate the plugin through the 'Plugins' menu in WordPress.

That's it. You can access the the menu editor by going to *Settings -> Menu Editor*. The plugin will automatically load your current menu configuration the first time you run it.

**WP MultiSite installation**

If you have WordPress set up in Multisite ("Network") mode, you can also install Admin Menu Editor as a global plugin. This will enable you to edit the Dashboard menu for all sites and users at once.

1. Download the admin-menu-editor.zip file to your computer.
2. Unzip the file.
3. Create a new directory named `mu-plugins` in your site's `wp-content` directory (unless it already exists).
4. Upload the `admin-menu-editor` directory to `/wp-content/mu-plugins/`.
5. Move `admin-menu-editor-mu.php` from `admin-menu-editor/includes` to `/wp-content/mu-plugins/`.

Plugins installed in the `mu-plugins` directory are treated as "always on", so you don't need to explicitly activate the menu editor. Just go to *Settings -> Menu Editor* and start customizing your admin menu :)

*Notes* 
* Instead of installing Admin Menu Editor in `mu-plugins`, you can also install it normally and then activate it globally via "Network Activate". However, this will make the plugin visible to normal users when it is inactive (e.g. during upgrades).
* When Admin Menu Editor is installed in `mu-plugins` or activated via "Network Activate", only the "super admin" user can access the menu editor page. Other users will see the customized Dashboard menu, but be unable to edit it.

== Screenshots ==

1. Plugin interface
2. A sample menu created by the plugin
3. Re-ordering menu items via drag and drop

== Changelog ==

= 1.12.1 =
* Made menu editor toolbars "sticky". They will now stay below the admin bar when scrolling down, which can be useful when editing very long menus.
* Restored the "custom item" indicator for the "Modern" editor color scheme. Previously, it was not visible in the menu editor when using that color scheme.
* Fixed a missing nonce check when hiding the "Upgrade to Pro" panel.
* Migrated to Lodash 4.
* Tested with WP 6.5-alpha.

= 1.12 =
* Added a search box for menu icons.
* Added more Dashicons. Now the icon dropdown should include all currently existing Dashicons.
* Fixed a visual bug where the icon would become unreasonably narrow when no icon was selected.
* Tested with WP 6.3.2 and 6.4-beta4.

= 1.11.2 =
* Fixed a conflict that prevented users from changing the menu icon of the Wordfence plugin (and possibly some other plugins that use similar CSS).
* Fixed a crash if the plugin encounters a supposed "menu item" that has the wrong data type, like a boolean. This was likely caused by a bug in an unidentified plugin or theme that modified the menu list incorrectly.
* Fixed plugin visibility restrictions not being applied when editing plugin files via AJAX.
* Tested with WP 6.3.1 and WP 6.4-alpha.

= 1.11.1 =
* Fixed a minor plugin conflict with "WPFunnels" and "Email Marketing Automation - Mail Mint" that caused hidden menu items created by those plugins to become visible when AME was activated.
* Fixed a conflict with the "Fortress" plugin that could reportedly cause an infinite loop.
* Fixed a conflict with "Da Reactions" 4.0.3 that triggered PHP warnings like "Warning: Array to string conversion in .../includes/menu-item.php on line 54".
* Fixed a potential fatal error when removing the number that represents pending updates/comments/etc from a menu title.
* Fixed a minor conflict with Essential Grid 3.0.17 that caused tooltips in AME dialogs to appear underneath the dialogs.
* Fixed a few jQuery deprecation warnings in the knockout-sortable library.
* Switched TypeScript to strict mode.

= 1.11 =
* Fixed a minor conflict with the WPForms plugin where the hidden menu item "Dashboard -> Welcome to WPForms" became visible when Admin Menu Editor was installed.
* Fixed a conflict with Toolset Types 3.4.7 that prevented redirect settings from being saved.
* Fixed a PHP warning triggered when a menu item didn't have a URL or a required capability.
* Fixed a plugin visibility bug where, if none of the user's roles had custom access settings for a specific plugin or in general, AME would immediatly deny access instead of also checking user capabilities. This could theoretically happen if all the user's roles were new or if the user didn't have any roles (they might still have access due to directly granted capabilities).
* Prevent a potential fatal error if JSON-encoded module settings stored in the database have been corrupted and can't be decoded.
* Added some missing `.map` files that could cause 404 errors for users looking at the developer console.
* Lots of internal reorganization that likely won't have any visible effects in this version.
* Tested up to WP 6.2.

= 1.10.4 =
* Fixed a layout bug in the menu editor where the submenu column did not increase its height to align with the currently selected top level menu.
* Tested up to WP 6.1.

= 1.10.3 =
* Increased the minimum required PHP version to 5.6. 
* Fixed a number of deprecation warnings related to PHP 8.
* Fixed a conflict with "Anti-Spam by CleanTalk" that could potentially cause a fatal error.
* Fixed a conflict with "Admin Theme - Musik" where the menu order settings in the other plugin would override the menu order configured in AME.
* Fixed missing padding in the "edit plugin details" panel (in the "Plugins" tab). Also updated the button layout to match the changed button layout of the "Quick Edit" panel introduced in WP 6.0.
* Fixed a visual issue where some form fields might briefly show up and then disappear if the settings page stylesheet(s) took a while to load.
* Removed dependency on the "icon16" CSS class.

= 1.10.2 =
* Added additional validation in escaping in multiple places.
* Fixed a number of issues related to the WordPress coding standard and the WordPress-VIP-Go coding standard.
* Fixed visual misalignment of menu property fields and accompanying dropdown buttons.
* Fixed inconsistent spacing aroud some radio buttons on the settings page.
* Introduced a limit to how many unique menu URLs can be remembered by the "highlight new menu items" feature. Previously, when this feature was enabled, the plugin would record each "seen" menu item, which could cause the associated database entry to grow endlessly. Now the plugin will remember up to 700 items per user.
* Tested with WordPress 6.0 (release candidate) and 6.1-alpha.

= 1.10.1 =
* Fixed the `[ame-user-info]` shortcode not working in login redirects. It would always output "(No user)" instead of the actual user data.
* Fixed a warning caused by a conflict with plugins and themes that call the "login_redirect" filter with only 1 parameter instead of the expected 3.
* Probably fixed a bug where menu items that use fully qualified URLs would lose their custom settings when the site URL changed (such as when migrating the site to a different domain).
* Fixed a minor conflict with the plugin "Google Analytics for WordPress by MonsterInsights" where the "Getting Started" menu item that is usually hidden would become visible when AME was activated.
* Fixed an edge case where the plugin would incorrectly show an "is this option enabled for everyone" checkbox in an indeterminate state when it was actually enabled for all roles but was not explicitly enabled (or disabled) for individual users.
* Fixed a bug where AME did not prefer submenu items when detecting the current menu item based on the current URL.
* Switched from `jQuery.toJSON()` to `JSON.stringify()`. The old jQuery JSON plugin appears to be unmaintained, and all modern browsers have supported `JSON.stringify()` for a long time.
* Other minor fixes.
* Tested up to WP 6.0-beta1.

= 1.10 =
* Added a "Redirects" feature. You can create login redirects, logout redirects, and registration redirects. You can configure redirects for specific roles and users. You can also set up a default redirect that will apply to everyone who doesn't have a specific setting. Redirect URLs can contain shortcodes, but not all shortcodes will work in this context.
* Added a few utility shortcodes: `[ame-wp-admin]`, `[ame-home-url]`, `[ame-user-info field="..."]`. These are mainly intended to be used to create dynamic redirects, but they will also work in posts and pages.
* Slightly improved the appearance of settings page tabs on small screens and in narrow browser windows.
* Fixed a minor conflict where several hidden menu items created by "WP Grid Builder" would unexpectedly show up when AME is active.
* Fixed a conflict with "LoftLoader Pro", "WS Form", and probably a few other plugins that create new admin menu items that link to the theme customizer. Previously, it was impossible to hide or edit those menu items.
* Fixed a few jQuery deprecation warnings.
* Fixed an "Undefined array key" warning that could appear if another plugin created a user role that did not have a "capabilities" key.
* Fixed a minor BuddyBoss Platform compatibility issue where the menu editor would show a "BuddyBoss -> BuddyBoss" menu item that was not present in the actual admin menu. The item is created by BuddyBoss Platform, but it is apparently intended to be hidden.
* Refactored the menu editor and added limited support for editing three level menus. While the free version doesn't have the ability to actually render nested items in the admin menu, it should at least load a menu configuration that includes more than two levels without crashing. This will probably only matter if someone edits the settings in the database or copies a menu configuration from the Pro version.

= 1.9.10 =
* Fixed a bug where the plugin could incorrectly identify a separator as the current menu item.
* Fixed submenu box not expanding to align with the selected parent item.
* Fixed a PHP 5 compatibility issue where the "Prevent bbPress from resetting role capabilities" would trigger notices and not work correctly. This bug did not affect newer PHP versions such as PHP 7.
* Fixed a couple of icon and separator rendering bugs where the hover marker - that is, the colored vertical bar that appears next to the currently hovered menu item, introduced in WP 5.7 - could either show up in the wrong place or show up when it's not supposed to.
* Fixed a jQuery Migrate warning about isFunction() being deprecated.

= 1.9.9 =
* Fixed a conflict with the "PRO Theme" plugin where "PRO Theme" would expand the wrong top level admin menu if the current submenu item had been moved from one parent menu to another.
* Fixed PHP notice "Undefined offset: 0 in /wp-includes/capabilities.php on line 70" (various line numbers).
* Fixed a conflict with "Stripe For WooCommerce" 3.2.12 where the "Stripe Gateway" menu had a wrong URL because a hidden menu item was not removed.
* Fixed a browser warning about the "ws_nmh_pending_seen_urls" cookie not using the SameSite attribute.
* Fixed a conflict with WooFunnels where changing the WooFunnels menu icon would result in both of the icons - the original one and the new one - showing up at the same time. The new icon was also misaligned.
* Minor visual changes.
* Tested with WordPress 5.7 and 5.8-alpha.

= 1.9.8 =
* Added a "bbPress override" option that prevents bbPress from resetting all changes that are made to dynamic bbPress roles. Enabling this option allows you to edit bbPress roles with any role editing plugin.
* Fixed a conflict that caused some hidden Simple Calendars menu items to show up when Admin Menu Editor was activated.
* Fixed a bug where menu items that had special characters like "&" and "/" in the slug could stop working if they were moved to a different submenu or to the top level.
* Fixed a bug where changing the menu icon to an external image (like a URL pointing to a PNG file) could result in the old and the new icon being displayed at once, either side by side or one below the other. This only affected menu items that had an icon set in CSS by using  a `::before` pseudo-element. 
* Fixed many jQuery deprecation warnings.
* Fixed a bug where some menu settings would not loaded from the database when another plugin triggered a filter that caused the menu configuration to be loaded before AME loaded its modules.
* Fixed bug that could cause an obscure conflict with plugins that change the admin URL, like "WP Hide & Security Enhancer". When a user tried to open "Dashboard -> Home", the plugin could incorrectly apply the permisssions of a another menu item to the "Home" item. If the other menu item was configured to be inaccessible, the user would get an error message when logging in (they were still successfully logged in).
* Improved error reporting in situations where the plugin can't parse menu data.

= 1.9.7 =
* Fixed a conflict with Elementor 3.0.0-beta that caused the "Theme Builder" menu item to have the wrong URL. 
* Minor performance optimization.

= 1.9.6 =
* Added an option to disable WPML support.
* Fixed a minor WP 5.5 compatibility issue where some of the boxes shown on the menu settings page were displayed incorrectly.
* Fixed a bug where hidden plugins were still visible under "Dashboard -> Updates" and were included in the number of updates shown in the admin menu, Toolbar and other places.
* Fixed a conflict with WP Job Manager where activating Admin Menu Editor made the hidden "Dashboard -> Setup" menu visible.
* Fixed a browser warning about cookies using "SameSite: None".
* Fixed a conflict with plugins that use a different, incompatible version of the jquery-cookie library. For example: Participants Database Field Group Tabs.
* Tested with WP 5.5-RC1 and 5.6-alpha.

= 1.9.5 =
* Fixed a conflict with Media Ace, Snax and "What's Your Reaction?" plugins where activating Admin Menu Editor would cause a number of previously hidden menu items become visible.
* Tested up to WP 5.4.

= 1.9.4 =
* Fixed another warning about get_magic_quotes_gpc() being deprecated in PHP 7.4. This instance was missed in the previous patch.
* Added a workaround for an issue with MailPoet 3 where some menu settings didn't work on MailPoet's admin pages.
* Added a workaround for an issue with Extended Widget Options where the "getting started" page that's added by that plugin showed up in the menu editor even though it was supposed to be hidden.
* Reduced the amount of space used by plugin visibility settings. This change will take effect the next time you save the settings.
* Extended the "compress menu configuration data" feature to use ZLIB compression in addition to menu data restructuring. This greatly decreases the amount of data stored in the database, but increases decompression overhead.

= 1.9.3 =
* Fixed a warning about get_magic_quotes_gpc() being deprecated in PHP 7.4.
* Fixed a conflict with plugins that use the "all_plugins" filter incorrectly.

= 1.9.2 =
* Updated the appearance of the settings page to match the admin CSS changes introduced in WordPress 5.3.
* Fixed inconsistent dialog title bar colours that could occur when another plugin loaded the default WP dialog styles.
* Fixed a bug where certain top level menus could stay permanently highlighted because some of their submenus were hidden via CSS/JS and unclickable. 
* When there's an error loading the menu configuration (e.g. because it's in an incompatible format), the plugin will now display an admin notice instead of letting through an uncaught exception.
* Removed the link to Visual Admin Customizer from the plugin settings page.
* Tested up to WP 5.3.

= 1.9.1 =
* Fixed a minor conflict with Toolset Types.
* Fixed a conflict with the MailPoet plugin where it was not possible to change the plugin's menu icon. 
* Fixed a bug where the plugin could misidentify certain core menus that have different URLs for different roles.
* Fixed a bug where the plugin could generate incorrect URLs for submenu items where the parent menu URL contained HTML entities like "&amp;".
* Fixed an issue where certain vulnerability scanners showed a warning about one of the plugin files because it used the eval() function. This particular instance of eval() was not a security flaw, but it has been now been removed to prevent false positives.
* Fixed a bug where the plugin could show an incorrect error message when a menu item was hidden due to there being another hidden menu item with the same URL.
* Fixed a minor issue with field alignment in menu properties.
* The "Site Health" menu will no longer be highlighted as new because it's part of WordPress core.

= 1.9 =
* Added an option to automatically hide new plugins. It was already possible, but previously this option was tied to the "show all plugins" checkbox. Now there is a separate "New plugins" checkbox.
* Fixed a bug where trying to change the icon of the Jetpack menu caused a JavaScript error that prevented the icon selector from being displayed.
* Tested up to WP 5.2.

= 1.8.8 =
* Added the ability to edit more plugin details like author name, site URL and version number. Note that this feature only changes how plugins are displayed. It doesn't actually modify plugin files.
* Fixed a PHP deprecation notice: "strpos(): Non-string needles will be interpreted as strings in the future". Hopefully this time it's been fixed for good.
* Fixed a couple of HTML validation errors.
* Fixed an inefficiency where the plugin would reinitialise the media frame every time the user tried to select an image from the media library. 
* Added a partial workaround for situations where menu icons that were more than 32 pixels wide would be displayed incorrectly. 
* Tested up to WP 5.1.1.

= 1.8.7 =
* Fixed a bug introcuded in 1.8.6 that caused a PHP warning "strpos(): Empty needle".

= 1.8.6 =
* Fixed a PHP warning being thrown when the WPMU_PLUGIN_DIR constant is not a valid path or the full path cannot be determined.
* Fixed a rare PHP warning "parameter 1 to be array, null given in menu-editor-core.php on line 4254" that was most likely caused by an unidentified plugin conflict.
* Fixed a rare warning about a class being redefined.
* Updated a number of internal dependencies.
* Tested with WP 5.0.

= 1.8.5 =
* Fixed a bug where very long submenus wouldn't be scrollable if the current item was one that was moved to the current submenu from a different top level menu.
* Fixed an obscure bug where clicking on an item in the current submenu could cause the entire submenu to "jump" up or down.
* Fixed AME not highlighting the correct menu item when there was a space in any of the query parameter values.
* Fixed another bug where the plugin didn't highlight the correct item if it was the first item in a submenu and also a custom item.

= 1.8.4 =
* Added a "Documentation" link below the plugin description. For people concerned about the recent GDPR legislation, the documentation now includes a page explaining [how the plugin processes personal data](https://adminmenueditor.com/free-version-docs/about-data-processing-free-version/). Short version: It usually doesn't.
* Tested with WP 4.9.6.

= 1.8.3 =
* Added a couple of tutorial links to the settings page.
* Fixed a potential crash that was caused by a bug in the "WP Editor" plugin version 1.2.6.3.
* Fixed some obsolete callback syntax that was still using "&$this".
* Changed the order of some menu settings and added separators between groups of settings.
* Removed the "Screen Options" panel from AME tabs that didn't need it like "Plugins".
* Tested with WP 4.9.5.

= 1.8.2 =
* Fixed the PHP warning "count(): Parameter must be an array or an object that implements Countable in menu-editor-core.php".
* Fixed a bug that could cause some network admin menus to be highlighted in green as if they were new.
* Fixed a conflict with WP Courseware 4.1.2 where activating AME would cause many extra menu items to show up unexpectedly.
* Fixed a conflict with Ultra WordPress Admin 7.4 that made it impossible to hide plugins.
* Replaced the "this is a new item" icon with a different one.
* Tested with WP 4.9.4.

= 1.8.1 =
* Added a workaround for a buggy "defer_parsing_of_js" code snippet that some users have added to their functions.php. This snippet produces invalid HTML code, which used to break the menu editor.
* Fixed a PHP warning that appeared when using this plugin together with WooCommerce or YITH WooCommerce Gift Cards and running PHP 7.1.
* Minor performance improvements.
* Tested with WP 4.8.3 and 4.9.

= 1.8 =
* You can edit plugin names and descriptions through the "Plugins" tab. This only changes how plugins are displayed on the "Plugins" page. It doesn't affect plugin files on disk.
* Added an option to highlight new menu items. This feature is off by default. You can enable it in the "Settings" tab.
* Added an option to compress menu data that the plugin stores in the database.
* Added a compatibility workaround for the Divi Training plugin. The hidden menu items that it adds to the "Dashboard" menu should no longer show up when you activate AME.
* Added a workaround that improves compatibility with plugins that set their menu icons using CSS.
* Fixed an old bug where sorting menu items would put all separators at the top. Now they'll stay near their preceding menu item.
* Fixed incorrect shadows on custom screen options links.
* Fixed a couple of UI layout issues that were caused by bugs in other plugins.
* Fixed a rare issue where hiding the admin bar would leave behind empty space.
* When you use the "A-Z" button to sort top level menus, it also sorts submenu items. To avoid compatibility issues, the first item of each submenu stays in its original position.
* Automatically reset plugin access if the only allowed user no longer exists. This should cut down on the number of users who accidentally lock themselves out by setting "Who can access the plugin" to "Only the current user" and then later deleting that user account.
* Minor performance optimizations.

= 1.7.3 =
* Fixed a bug where closing the menu properties of a custom menu item could set "extra capability" to "read". 
* Added a workaround for WooCommerce 2.6.8 to display the number of new orders in the "Orders" menu title.
* Minor cosmetic changes.
* Tested with WP 4.7 and 4.8-alpha.

= 1.7.2 =
* Added capability suggestions and access preview to the "Extra capability" dropdown.
* The plugin now remembers the last selected menu item and re-selects it after you save changes.
* Fixed a layout issue where menus with very long titles would appear incorrectly in the menu editor.
* When you change the menu title, the window title will also be changed to match it. You can still edit the window title separately if necessary.
* Moved the "Icon URL" field up and moved "Window title" down.

= 1.7.1 =
* Split the "required capability" field into two parts - a read-only field that shows the actual required capability, and an editable "extra capability" that you can use to restrict access to the menu.
* Added more detailed permission error messages. You can turn them off in the "Settings" tab by changing "Error verbosity level" to "Low".
* Tested up to WP 4.6.

= 1.7 =
* Added a "Plugins" tab. It lets you hide specific plugins from other users. Note that this only affects the list on the "Plugins" page and tasks like editing plugin files, but it doesn't affect the admin menu.
* Tested up to WordPress 4.6-beta3. 

= 1.6.2 =
* Fixed a bug that made menu items "jump" slightly to the left when you start to drag them.
* Fixed a Multisite-specific bug where temporarily switching to another site using the switch_to_blog() function could result in the user having the wrong permissions.
* When saving settings, the plugin will now compress the menu data before sending it to the server. This reduces the chances of exceeding request size limits that are imposed by some hosting companies.
* You can dismiss the "Settings saved" notification by clicking the "x" button.
* Tested up to WordPress 4.5.2.

= 1.6.1 =
* Fixed a bug introduced in version 1.6 that prevented the "collapse menu" link from working. In some cases, this bug also made it impossible to switch between "Help" tabs.

= 1.6 =
* Improved PHP 7 support.
* Added a few more menu icons.
* Added tabs to the settings page: "Admin Menu" and "Settings". These tabs replace the heading buttons that were previously used to switch between the menu editor and general plugin settings.
* Added basic support for the special "customize" and "delete_site" meta capabilities.
* Fixed a bug that prevented menu items with an empty slug (i.e. no URL) from showing up.
* Fixed a bug where collapsing menu properties would flag the "Icon URL" field as having a custom value even if you hadn't actually changed it.
* Fixed a rare WPML conflict that sometimes caused the admin menu to use a mix of different languages.
* Improved compatibility with buggy plugins and themes that throw JavaScript errors in their DOM-ready handlers.
* Renamed jquery.cookie.js to jquery.biscuit.js as a workaround for servers with overly aggressive ModSecurity configuration. Apparently, some servers block access to any URL that contains the text ".cookie".
* Added a compatibility workaround for the DW Question & Answer plugin. The hidden "Welcome", "Changelog" and "Credits" menu items should no longer show up when you activate AME.
* Added locking to reduce the risk of triggering a race condition when saving menu settings.
* Removed the non-functional "Embed WP page" option.
* Tested up to WordPress 4.5-RC1.

= 1.5 = 
* Added "Keep this menu open" checkbox. This setting keeps a top level menu expanded even if it is not the current menu.
* Added sort buttons to the top level menu toolbar.
* Added an arrow that points from the current submenu to the currently selected parent menu. This might help new users understand that the left column shows top level menus and the right column shows the corresponding submenu(s).
* Added a new editor colour scheme that makes the menu editor look more like other WordPress admin pages (e.g. Appearance -> Menus). You can enable it through the plugin settings page.
* New and unused menu items will now show up in the same relative position as they would be in the default admin menu. Alternatively, they can be displayed at the bottom of the menu. You can configure this in plugin settings. 
* Fixed a rare bug where the menu editor would crash if one of the menu items had a `null` menu title. Technically, it's not valid to set the title to `null`, but it turns out that some plugins do that anyway.
* Top level menus that have an empty title ("", an empty string) are no longer treated as separators.
* Made all text fields and dropdowns the same height and gave them consistent margins. 
* Fixed a number of layout bugs that could cause field labels to show up in the wrong place or get wrapped/broken in half when another plugin changed the default font or input size.
* Fixed a minor layout bug that caused the "expand menu properties" arrow to move down slightly when holding down the mouse button.
* Fixed a minor bug that could cause toolbar buttons to change size or position if another plugin happens to override the default link and image CSS.
* Added a workaround for plugins that create "Welcome", "What's New" or "Getting Started" menu items and then hide those items in a non-standard way. Now (some of) these items will no longer show up unnecessarily. If you find menus like that which still show up when not needed, please report them.
* Fixed a few other layout inconsistencies.
* Improved compatibility with buggy plugins that unintentionally corrupt the list of users' roles by misusing `array_shift`.
* Fixed a URL parsing bug that caused AME to mix up the "Customize", "Header" and "Background" menu items in some configurations.
* Fixed a layout issue where starting to drag one menu item would cause some other items to move around or change size very slightly.
* Fixed JavaScript error "_.empty is not a function".
* Increased minimum required WordPress version to 4.1.
* Renamed the "Show/Hide" button to "Hide without preventing access". Changed the icon from a grey puzzle piece to a rectangle with a dashed border.
* Made the plugin more resilient to JavaScript crashes caused by other plugins.
* Use `<h1>` headings for admin pages in WordPress 4.2 and above.
* Made the "delete" button appear disabled when the selected menu item can't be deleted.
* Moved the "new separator" button so that it's next to the "new menu" button.  
* Changed the close icon of plugin dialogs to a plain white "X".
* Increased tooltip text size.
* Improved compatibility with IP Geo Block.

= 1.4.5 =
* Fixed a `TypeError: invalid 'in' operand a` error that caused compatibility issues with WordPress 4.3.
* Fixed a bug where the current menu item wouldn't get highlighted if its URL included %-encoded query parameters.
* Fixed a bug in menu URL generation that could cause problems when moving a plugin menu from "Posts", "Pages" or a CPT to another menu. The URL of the menu item got changed in a way that could break some plugins.
* Fixed a .htaccess compatiblility issue with with Apache 2.3+.
* Fixed an incorrect directory name in an error message.
* The "Links" menu will no longer show up in the editor unless explicitly enabled. As of WP 3.5, the "Links" menu still exists in WordPress core but is inaccessible because the Links Manager is disabled by default.
* Tested with WordPress 4.3.

= 1.4.4 =
* Tested with WordPress 4.2.

= 1.4.3 =
* Trying to delete a non-custom menu item will now trigger a warning dialog that offers to hide the item instead. In general, it's impossible to permanently delete menus created by WordPress itself or other plugins (without editing their source code, that is).
* Added a workaround for a bug in W3 Total Cache 0.9.4.1 that could cause menu permissions to stop working properly when the CDN or New Relic modules were activated.
* Fixed a plugin conflict where certain menu items didn't show up in the editor because the plugin that created them used a very low priority.
* Signigicantly improved sanitization of menu properties. 
* Renamed the "Choose Icon" button to "Media Library".
* Minor compatibility improvements.

= 1.4.2 =
* Tested on WP 4.1 and 4.2-alpha.
* Fixed a bug that allowed Administrators to bypass custom permissions for the "Appearance -> Customize" menu item.
* Fixed a regression in the menu highlighting algorithm.
* Fixed an "array to string conversion" notice caused by passing array data in the query string. 
* Fixed menu scrolling occasionally not working when the user moved an item from one menu to another, much larger menu (e.g. having 20+ submenu items).
* Fixed a bug where moving a submenu item from a plugin menu that doesn't have a hook callback (i.e. an unusable menu serving as a placeholder) to a different menu would corrupt the menu item URL.
* Other minor bug fixes.

= 1.4.1 =
* Fixed "Appearance -> Customize" always showing up as "new" and ignoring custom settings.
* Fixed a WooCommerce 2.2.1+ compatibility issue that caused a superfluous "WooCommerce -> WooCommerce" submenu item to show up. Normally this item is invisible.
* Fixed a bug where the plugin would fail to determine the current menu if the user tries to add a new item of a custom post type that doesn't have an "Add New" menu. Now it highlights the CPT parent menu instead.
* Fixed a very obscure bug where certain old versions of PHP would crash if another plugin created a menu item using an absolute file name as the slug while AME was active. The crash was due to a known bug in PHP and only affected Windows systems with open_basedir enabled.
* Added more debugging information for situations where the plugin can't save menu settings due to server configuration problems.
* Other minor fixes.

= 1.4 = 
* Added a special target page option: "< None >". It makes the selected menu item unclickable. This could be useful for creating menu headers and so on.
* Added a new menu editor colour scheme that's similar to the default WordPress admin colour scheme. Click the "Settings" button next to the menu editor page title to switch colour schemes.
* Fixed strange boxes showing up in the icon selector in Internet Explorer.
* Fixed duplicate top level menus mysteriously disappearing. Now the plugin will properly warn the user that all top level menus must have unique URLs.
* Fixed an obscure bug where changing the "Target page" from the default setting to "Custom" and back would occasionally make some menu properties suddenly show up as modified for no apparent reason.
* Fixed incorrect submenu item height and margins in WP 4.0-beta.
* Fixed a minor layout bug where items with no title would be smaller than other items.
* Fixed combo-box dropdown button height for WP 3.9.x.
* Added a workaround for a bug in WordPress Mu Domain Mapping 0.5.4.3.
* Added a workaround for the very unusual situation where the "user_has_cap" filter is called without a capability.
* Fixed duplicates of bbPress menu items showing up.
* Changed the default custom menu icon to the generic "cogwheel" icon from Dashicons.
* Other small UI changes.
* Raised minimum requirements to WordPress 3.8 or later. This is mainly due to the increased reliance on Dashicons as menu icons.

= 1.3.2 =
* Added a large number of menu icons based on the Dashicons icon font. 
* Fixed default menu icons not showing up in WP 3.9. 
* Fixed a rare "$link.attr(...) is undefined" JavaScript error.
* Fixed a bug where a hidden submenu page with a URL like "options-general.php?page=something" would still be accessible via "admin.php?page=something".
* Fixed several other minor bugs.
* Tested up to WordPress 3.9-RC1. Minimum requirements increased to WP 3.5.

= 1.3.1 =
* Tested with WordPress 3.8.
* Fixed several minor UI/layout issues related to the new 3.8 admin style.
* Fixed a bug where moving an item to a plugin menu and then deactivating that plugin would cause the moved item to disappear.
* Fixed deleted submenus not being restored if their original parent menu is no longer available.
* Fixed a rare glitch where submenu separators added by certain other plugins would sometimes disappear.
* Fixed a conflict with Shopp 1.2.9.
* Made the plugin treat "users.php" and "profile.php" as the same parent menu. This fixes situations where it would be impossible to hide a "Users" submenu item from roles that don't have access to the "Users" menu and instead get a "Profile" menu.
* Added extra logging for situations where a menu item is hidden because a higher-priority item with the same URL is also hidden. 
* Minor performance improvements.

= 1.3 =
* Added a new settings page that lets you choose whether admin menu settings are per-site or network-wide, as well as specify who can access the plugin. To access this page, go to "Settings -> Menu Editor Pro" and click the small "Settings" link next to the page title.
* Added a way to show/hide advanced menu options through the settings page in addition to the "Screen Options" panel.
* Added a "Show menu access checks" option to make debugging menu permissions easier.
* Added partial WPML support. Now you can translate custom menu titles with WPML.
* The plugin will now display an error if you try to activate it when another version of it is already active.
* Added a "Target page" dropdown as an alternative to the "URL" field. To enter a custom URL, choose "Custom" from the dropdown.
* Fixed the "window title" setting only working for some menu items and not others.
* Fixed a number of bugs related to moving plugin menus around.
* Changed how the plugin stores menu settings. Note: The new format is not backwards-compatible with version 1.2.2.

= 1.2.2 =
* Replaced a number of icons from the "Silk" set with GPL-compatible alternatives.
* Tested with WP 3.6.

= 1.2.1 =
* Fixed a rare bug where the icon selector would appear at the bottom of the page instead of right below the icon button.
* Fixed incorrect icon alignment when running the MP6 admin UI.
* Tested on WP 3.6-beta1-24044.

= 1.2 =
* Added an icon drop-down that lets you pick one of the default WordPress menu icons or upload your own through the media library (only in WP 3.5+).
* Fixed misaligned button text in IE/Firefox.
* Fixed menus that have both a custom icon URL and a "menu-icon-*" class displaying two overlapping icons. You can still get this effect if you set the class and URL manually.
* Fixed a compatibility problem with Participants Database 1.4.5.2.
* Tested on WP 3.5.1 and WP 3.6-alpha.

= 1.1.13 =
* Fixed a layout glitch that would cause the editor sidebar to display incorrectly in WP 3.5.
* When trying to determine the current menu, the plugin will now ignore all links that contain nothing but an "#anchor". Various plugins use such links as separators and it wouldn't make sense to highlight them.
* Tested on WP 3.5 (RC6).

= 1.1.12 =
* Fixed several more small CPT-related bugs that would cause the wrong menu to be marked as current. 
* Tested on WP 3.5-beta2.

= 1.1.11 =
* Tested on WP 3.4.2 and WP 3.5-alpha-21879.
* Fixed a visual glitch related to the arrow that's used to expand menu settings. In certain situations clicking it would cause the arrow icon to be briefly replaced with multiple copies of the same icon.
* Fixed the position of the URL and capability dropdown lists. Now they should show up directly under the corresponding input box instead of appearing some distance down and to the right.
* Fixed the size of the toolbar buttons - now they're perfectly square.
* Fixed a rare bug that would sometimes cause the wrong menu to be marked as active/expanded.
* Only display the survey notice on the menu editor page, not on all admin pages.

= 1.1.10 =
* Added a new user survey. The notice will only appear for users who didn't complete or hide the previous one.
* Fixed a number of bugs in the code that determines which menu should be expanded.
* Fixed compatibility issues on sites running in SSL mode.

= 1.1.8 =
* Fix author URL (was 404).
* Tested on WP 3.4.1
* Update plugin description. Some notes were no longer accurate for the current version.

= 1.1.7 = 
* Tested on WP 3.4
* Fixed a rare "failed to decode input" error.
* Fixed menus not being expanded/collapsed properly when the current menu item has been moved to a different sub-menu.
* Added a shortlist of Pro version benefits to the editor page (can be hidden).

= 1.1.6.1 =
* Tested on WP 3.3.2
* Added a user survey.

= 1.1.6 =
* Tested on WP 3.3.1.
* Fixed a couple 404's in the readme and the plugin itself.

= 1.1.5 =
* Fixed an error where there would be no custom menu to show.
* Removed the "Feedback" button due to lack of use. You can still provide feedback via blog comments or email, of course.

= 1.1.4 =
* Fixed the updater's cron hook not being removed when the plugin is deactivated.
* Fixed updates not showing up in some situations.
* Fixed the "Feedback" button not responding to mouse clicks in some browsers.
* Fixed "Feedback" button style to be consistent with other WP screen meta buttons.
* Enforce the custom menu order by using the 'menu_order' filter. Fixes Jetpack menu not staying put.
* You can now copy/paste as many menu separators as you like without worrying about some of them mysteriously disappearing on save.
* Fixed a long-standing copying related bug where copied menus would all still refer to the same JS object instance.
* Added ALT attributes to the toolbar icon images.
* Removed the "Custom" checkbox. In retrospect, all it did was confuse people.
* Made it impossible to edit separator properties.
* Removed the deprecated "level_X" capabilities from the "Required capability" dropdown. You can still type them in manually if you want.

= 1.1.3 = 
* Tests for WordPress 3.2 compatibility.

= 1.1.2 = 
* Fixed a "failed to decode input" error that could show up when saving the menu.

= 1.1.1 = 
* WordPress 3.1.3 compatibility. Should also be compatible with the upcoming 3.2.
* Fixed spurious slashes sometimes showing up in menus.
* Fixed a fatal error concerning "Services_JSON".

= 1.1 = 
* WordPress 3.1 compatibility.
* Added the ability to drag & drop a menu item to a different menu.
* Added a drop-down list of Dashboard pages to the "File" box.
* When the menu editor is opened, the first top-level menu is now automatically selected and it's submenu displayed. Hopefully, this will make the UI slightly easier to understand for first-time users.
* All corners rounded on the "expand" link when not expanded.
* By popular request, the "Menu Editor" menu entry can be hidden again.

= 1.0.1 =
* Added "Super Admin" to the "Required capability" dropdown.
* Prevent users from accidentally making the menu editor inaccessible.
* WordPress 3.0.1 compatibility made official.

= 1.0 =
* Added a "Feedback" link.
* Added a dropdown list of all roles and capabilities to the menu editor.
* Added toolbar buttons for sorting menu items alphabetically.
* New "Add separator" button.
* New separator graphics.
* Minimum requirements upped to WP 3.0.
* Compatibility with WP 3.0 MultiSite mode.
* Plugin pages moved to different menus no longer stop working.
* Fixed moved pages not having a window title.
* Hide advanced menu fields by default (can be turned off in Screen Options).
* Changed a lot of UI text to be a bit more intuitive.
* In emergencies, administrators can now reset the custom menu by going to http://example.com/wp-admin/?reset\_admin\_menu=1
* Fixed the "Donate" link in readme.txt
* Unbundle the JSON.php JSON parser/encoder and use the built-in class-json.php instead.
* Use the native JSON decoding routine if it's available.
* Replaced the cryptic "Cannot redeclare whatever" activation error message with a more useful one.

= 0.2 =
* Provisional WPMU support.
* Missing and unused menu items now get different icons in the menu editor.
* Fixed some visual glitches.
* Items that are not present in the default menu will only be included in the generated menu if their "Custom" flag is set. Makes perfect sense, eh? The takeaway is that you should tick the "Custom" checkbox for the menus you have created manually if you want them to show up.
* You no longer need to manually reload the page to see the changes you made to the menu. Just clicking "Save Changes" is enough.
* Added tooltips to the small flag icons that indicate that a particular menu item is hidden, user-created or otherwise special.
* Updated the readme.txt

= 0.1.6 =
* Fixed a conflict with All In One SEO Pack 1.6.10. It was caused by that plugin adding invisible sub-menus to a non-existent top-level menu.

= 0.1.5 =
* First release on wordpress.org
* Moved all images into a separate directory.
* Added a readme.txt

== Upgrade Notice ==

= 1.1.11 =
This version fixes several minor bugs and layout problems.

= 1.1.9 =
Optional upgrade. Just adds a couple of screenshots for the WordPress.org plugin description.
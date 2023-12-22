=== Easy Notification Bar ===
Contributors: WPExplorer
Donate link: https://www.wpexplorer.com/donate/
Tags: notification, notification bar, notice, notice bar, top bar, banner
Requires at least: 5.2.0
Requires PHP: 7.0
Tested up to: 6.2.2
Stable Tag: 1.4.7
License: GNU Version 2 or Any Later Version.

== Description ==
Easily add a custom top bar notification message to on your site with live customization options via the WordPress customizer. The plugin allows you to enter your notification bar text as well as an optional button to display next to your text. Perfect for notifying visitors of a current sale, hot product, warnings or other important messages.

The [Easy Notification Bar](https://wordpress.org/plugins/easy-notification-bar/) plugin makes use of the newer "wp_body_open" action hook introduced in WordPress 5.2.0 which allows the plugin to work better with any theme that has been updated to support the tag. Contrary to other notice bar solutions which rely on absolute positioning, this plugin inserts the notice bar right after the body tag so it should display perfectly without any conflicts on any well-coded theme.

By default, the notification bar is "static" which means it displays at the top of your site so when you scroll down the page it will become "hidden". This is generally better for usability and [SEO](https://www.wpexplorer.com/wordpress-seo/). However, in version 1.4 we added a new **sticky option** which you can enable in the Customizer so that the notification bar remains visible as you scroll down the page. The sticky functionality makes use of the CSS sticky property (not javascript).

Although disabled by default, you can enable a **close icon** for your notice. When enabled, your visitors will see an "x" icon over the top bar which they can click to hide the message for their current and future sessions. This functionality makes use of localStorage (not cookies).

**Live Demo:** You can view a live demo for the notification bar on our [More Widgets Plugin Demo](https://demo.wpexplorer.com/more-widgets/)

== Features ==

* Sitewide (or homepage only) top notification bar.
* Easy setup via the WordPress customizer.
* Optional close icon.
* Optional sticky display.
* Custom background, color, text alignment and font size settings.
* Optional callout button.
* Responsive design so it looks good on mobile.
* Minimal code.
* Vanilla Javascript used for close icon (jQuery not needed).

== Installation ==

1. Go to your WordPress website admin panel
2. Select Plugins > Add New
3. Search for "Easy Notification Bar"
4. Click Install
5. Activate the plugin
6. A default notification should now appear on your site. If it does not then you need to update your theme to work properly (see FAQ).
7. Go Appearance > Customize > Easy Notification Bar to customize your notification

== Frequently Asked Questions ==

= Why doesn't the notification display on my site even though I enabled it? =
This plugin makes use of the WordPress core ["wp_body_open"](https://developer.wordpress.org/reference/functions/wp_body_open/) action hook which should be added to every theme header.php file and was introduced in Wordpress 5.2.0. You will need to properly add this action hook to your header.php file and or contact the theme developer so that your theme is updated accordingly.

Feel free to ask in the support forum if you need help updating your header.php file. If you are using a free theme on WordPress.org please link to the theme in question. If you are using a premium theme, contact the developers for support since you paid for it.

= Can I display the Notification on my homepage only? =
Yes. There is a setting available so you can enable display for the homepage/front-page only.

= Can I create multiple notifications? =
No. This plugin is intended to display a single message across your whole site to keep it as simple, fast and straight forward as possible. If you need multiple notifications you should look at using a different plugin.

= Is the Easy Notification Bar Free? =
Yes. The plugin is completely free of charge! All the features listed above are already included.

= If there a premium version? =
No. This plugin is intended to be free and has everything you need to setup a simple notification bar for your site or homepage. This means there aren't any upsells, advertisments or branding in the plugin.

= Is this top bar plugin GDPR compliant? =
Yes. The plugin does not collect or store any personal information and doesn't make use of any cookies.


== Changelog ==

= 1.4.7 =
* Fixed issue where renamed CSS files were not committed correctly causing the layout to break. Sorry!!

= 1.4.6 =
* Added new option added to customize the button padding.
* Updated there was some CSS loaded on the frontend that was only needed in the customizer. This will now load in it's own file when the customizer is open to prevent frontend bloat.
* Removed the default notification bar text so that nothing shows up when first activating the plugin.

= 1.4.5 =
* Fixed incompatibility issues with the AMP plugin (removed the collapsible functionality when using amp).

= 1.4.4 =
* Added options to change the button background and text color.
* Updated the hidden bar functionality (click to close) so repeat visitors will see new notices (if the message is changed) even if they had previously closed it.
* Fixed issue where if you remove a custom color from the customizer settings it wouldn't fall back to the default.

= 1.4.3 =
* Fixes issue where the "has-easy-notification-bar" class is still being added to the body element after the bar has been dismissed.

= 1.4.2 =

* Updated filters to pass on the current object ($this) so you can access class methods.
* Added new "easy_notification_bar_wrap_class" filter so you can add/remove classes from the notifcation bar wrapper element.
* Added new "easy_notification_bar_hook_name" filter so you can modify the default hook name used to insert the notification bar on the page (default is wp_body_open).

= 1.4.1 =

* Fixed issue where the new js file wasn't properly uploaded to the WordPress repository.

= 1.4 =

* Added "Show close icon?" setting.
* Added "Close Icon" setting (choose from plain or outline).
* Added "Enable Sticky?" setting.

= 1.3.1 =

* Updated notification bar CSS to use a border-box box-sizing layout for main container to prevent potential bugs.

= 1.3 =

* Added support for the official WP AMP plugin Legacy themes.
* Added "easy_notification_bar_hook_priority" filter for modifying the priority (default is 10) in the add_action functions hooked to wp_body_open and amp_post_template_body_open for displaying the notification bar.

= 1.2 =

* Added body tag 'has-easy-notification-bar' if the notice bar is enabled on the page.
* Added option to add rel="sponsored" to the button.
* Added filter "easy_notification_bar_button_rel".
* Updated the "Enable Notification Bar" and "Display on Front Page Only?" Customizer settings to use refresh instead of postMessage for the transport parameter.
* Updated to display full support for WP 5.6.

= 1.1.3 =

* Fixed potential customizer issues with the color settings.

= 1.1.2 =

* Fixed Issue where the Front page only setting wouldn't reflect changes in the Customizer.
* Tested with WordPress 5.4

= 1.1.1 =

* Fixed Customizer issue where disabling/enabling the notification wouldn't reflect the changes.

= 1.1 =

* Added Customizer setting to enable the notification bar for the front page only.
* Notification functions now run on the wp hook instead of init to better support conditional functions when using the easy_notification_bar_is_enabled filter.

= 1.0 =

* First official release
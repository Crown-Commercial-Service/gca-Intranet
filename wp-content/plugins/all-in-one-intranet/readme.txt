=== All-In-One Intranet ===
Contributors: slaFFik, jaredatch, smub
Tags: intranet, extranet, private, redirect, privacy
Requires at least: 5.5
Requires PHP: 7.0
Tested up to: 6.7
Stable tag: 1.8.0
License: GPL-3.0-or-later

Instantly turn your WordPress installation into a private corporate intranet.

== Description ==

WordPress is a popular platform for creating corporate intranets. The only problem is that it's built primarily for public-facing websites.

Companies often need a private setup, also known as an intranet. That is where All-In-One Intranet comes in.

There are plenty of free plugins available to add privacy and other requirements - but you'll need to cherry-pick the functionality you need, and make sure they all work well together.

All-In-One Intranet gives you everything you need in one plugin to lock down your site and start building your intranet.

= What is an Intranet? =

Intranets are basically private or internal websites for businesses. However, they can also be found as internal communications platform, collaboration tools, knowledge sharing platform, and even a social network. Each of which are items possible to do with a WordPress site.

= Features =

*  **Privacy** - one checkbox to make your entire site private to anyone not logged in. Also displays warnings if any core WordPress settings are currently allowing unauthorized users to register.
*  **Login Redirect** - your staff are logging in to read information as well as write it, so WordPress' default of logging users in to their profile page is unhelpful. Set any site URL as their new landing page.
*  **Auto Logout** - set a time interval for inactivity, after which users will be automatically logged out, protecting your sensitive company information.
*  **Multisite Sub-site Membership** – set a default role to be applied to all sub-sites when a new user (or site) is created. Saves having to manually add new users to sub-sites, or existing users to new sub-sites.
*  **Multisite Sub-site Privacy** – decide whether users need to be members of a sub-site in order to view it (presuming you already restricted the whole site to logged-in users only, in ‘Privacy’).

See [our docs](https://wp-glogin.com/docs/all-in-one-intranet/?utm_source=wprepo&utm_medium=link&utm_campaign=AllInOneIntranet) for details!

= Google Apps =

Does your organization use Google Apps?

Our [Google Apps Login](https://wp-glogin.com/glogin/?utm_source=wprepo&utm_medium=link&utm_campaign=AllInOneIntranet) plugin enables Google Apps domain admins to manage WordPress user accounts entirely from Google Apps.
This saves time and increases security - giving peace of mind that only authorized employees have access to the company's websites and intranet.

And our [Google Drive Embedder](https://wp-glogin.com/drive/?utm_source=wprepo&utm_medium=link&utm_campaign=AllInOneIntranet) plugin allows post/page authors to easily embed documents throughout your site directly from Google Drive.

Please see our website [https://wp-glogin.com/](https://wp-glogin.com/?utm_source=wprepo&utm_medium=link&utm_campaign=AllInOneIntranet) for more information about all our products.

== Screenshots ==

1. Regular settings page to configure intranet.
2. Network-specific settings page to configure intranet.

== Frequently Asked Questions ==

= Is it secure? =

Care has been taken to ensure the plugin offers the level of security promised for a standard WordPress installation.

Note that your media uploads (e.g. photos) will still be accessible to anyone who knows their direct URLs. This the way most privacy plugins work.

However, the author does not accept liability or offer any guarantee of security or functionality, and it is your responsibility to ensure that your site is secure and functions in the way you require.

In particular, other plugins may conflict with each other, and different WordPress versions and configurations may render your site insecure.

== Installation ==

Easiest way:

1. Go to your WordPress admin control panel's plugin page
1. Search for 'All-In-One Intranet'
1. Click Install
1. Click Activate in the plugin card
1. Go to 'All-In-One Intranet' under Settings in your WordPress admin area to configure the plugin

If you cannot install from the WordPress plugins directory for any reason, and need to install from ZIP file:

1. Upload `all-in-one-intranet` directory and contents to the `/wp-content/plugins/` directory, or upload the ZIP file directly in the Plugins section of your WordPress admin
1. Go to Plugins page in your WordPress admin
1. Click Activate
1. Go to 'All-In-One Intranet' under Settings in your WordPress admin area to configure the plugin

== Changelog ==

= 1.8.0 =
* IMPORTANT: The minimum WordPress version is now WordPress v5.5.
* IMPORTANT: The minimum PHP version is now PHP v7.0.
* Added: Multisite-specific options: "Require logged-in users to be members of a sub-site to view it"
* Added: "Sub-site Membership" - assign a user role for newly added users.
* Changed: Compatibility with WordPress 6.6.
* Fixed: Several security-related improvements in various parts of the plugin.
* Fixed: Code style improvements.

= 1.7.1 =
* Security update and added WordPress 5.7 compatibility.

= 1.7 =
* Security update and added WordPress 5.6 compatibility.

= 1.6 =
* Security update and added WordPress 5.4.1 compatibility.

= 1.5 =
* Ready for WP 4.9. Disables unauthenticated calls to WP REST API by default.

= 1.4 =
* Now supports localization - please contribute your translations!

= 1.3 =
* Changed which WordPress hooks are used to check for auto-logout. This is to widen compatibility with certain Themes.

= 1.2 =
* On non-multisite WordPress, now restricts access to users who have no role, as well as those who aren't logged in at all.

= 1.1 =
* Ready for public release.

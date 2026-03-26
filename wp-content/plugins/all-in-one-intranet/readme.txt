=== Intranet & Private Site - All-In-One Intranet ===
Contributors: slaFFik, jaredatch, smub
Tags: intranet, private-site, extranet, restrict-access, private
Requires at least: 5.5
Requires PHP: 7.0
Tested up to: 6.9
Stable tag: 1.8.1
License: GPL-3.0-or-later

Private intranet in one click. Auto-logout for security, login redirect, and multisite privacy controls included.

== Description ==

WordPress is one of the most popular platforms for building corporate intranets and private company websites. The problem is that WordPress was designed for public-facing sites. Making it work as a private intranet typically requires installing multiple plugins, configuring each one separately, and hoping they all play nicely together.

All-In-One Intranet solves this by giving you everything you need in a single plugin to turn your WordPress site into a fully private intranet. Enable privacy with one checkbox, set up auto-logout to protect sensitive information, configure where users land after login, and manage multisite access controls - all from one settings page.

Whether you are building a corporate intranet, a private knowledge base, a restricted client portal, or an internal communications hub, this plugin handles the foundational privacy and access control so you can focus on your content.

= What is an Intranet? =

An intranet is a private website or network used internally by an organization. Unlike a public website, an intranet is only accessible to authorized users - typically employees, contractors, or specific team members.

Common uses for a WordPress intranet include:

* Internal company communications and announcements
* Employee handbooks, policies, and procedures
* Knowledge bases and documentation wikis
* Project collaboration and team coordination
* HR portals for onboarding and training materials
* Client portals with restricted access to project files

WordPress is well suited for all of these because of its familiar editing interface, extensive plugin ecosystem, and flexible user role system. All-In-One Intranet provides the access control layer that makes it all work.

= Features =

All-In-One Intranet includes five core features designed to cover the most common intranet requirements:

= One-Click Private Site =

Enable the "Force site to be entirely private" checkbox, and your entire WordPress site becomes restricted to logged-in users only. Anyone who is not logged in gets redirected to the WordPress login page automatically.

This single setting handles multiple layers of privacy at once:

* **Page and post access** - all frontend content requires authentication
* **REST API protection** - unauthenticated REST API requests are blocked with a 401 error, preventing data leaks through the API
* **XML-RPC blocking** - XML-RPC is disabled entirely when privacy is active, closing another potential access vector
* **Search engine blocking** - the robots.txt file is automatically updated to disallow all crawling, keeping your private content out of search indexes
* **Pingback suppression** - outgoing pingbacks and trackbacks are disabled so your private site does not announce itself to external services

The plugin also monitors your WordPress registration settings. If "Anyone can register" is enabled on a single site, or if open registration is allowed on a multisite network, the plugin displays a warning on the settings page so you can fix it before it becomes a problem.

= Auto-Logout for Inactive Users =

Shared workstations and forgotten browser tabs are a real security risk for intranets. The auto-logout feature lets you set a maximum idle time - in minutes, hours, or days - after which users are automatically logged out.

The plugin tracks each user's last activity timestamp. On every page load, it checks whether the configured idle time has been exceeded. If a user has been inactive for too long, they are logged out immediately and redirected back to the page they were viewing, which triggers the login wall if the site is private.

This protects sensitive company information without requiring users to remember to log out manually. Set it to 30 minutes for high-security environments, a few hours for typical office use, or leave it blank to disable the feature entirely.

= Custom Login Redirect =

By default, WordPress sends users to the dashboard after they log in. For an intranet, this is not useful - your team is logging in to read content, not to manage the site.

The login redirect feature lets you set any URL on your site as the post-login landing page. Point it to your company homepage, a news feed, or a team dashboard so users see relevant content right away.

This redirect only applies when users log in directly through the standard WordPress login page. If a user tries to access a specific page and gets redirected to log in first, they will be sent back to that page after authentication - not to the custom redirect URL. This keeps the user experience smooth.

= Multisite Sub-site Privacy =

If you run a WordPress multisite network, you can require logged-in users to be members of a specific sub-site before they can view it. This is useful for organizations with multiple departments, teams, or client areas - each with their own sub-site that should only be visible to relevant people.

When a user who is logged in but not a member of the current sub-site tries to access it, they see a message listing all the sub-sites they do have access to, with clickable links to navigate there. Access to the Network Admin area is never restricted by this setting.

This option works in combination with the main privacy setting. Enable private site first, then enable sub-site membership requirements for granular access control across your network.

= Multisite Default Role Assignment =

Managing user access across multiple sub-sites in a WordPress network can be tedious. Every time you add a new user or create a new sub-site, you would need to manually assign roles across all the relevant sites.

The default role assignment feature automates this. Choose a role (Subscriber, Editor, Administrator, or any custom role), and the plugin handles the rest:

* When a **new user** is created, they are automatically added to every active sub-site in the network with the selected role
* When a **new sub-site** is created, all existing users are automatically added to it with the selected role

This saves significant administration time, especially for growing organizations where new employees and new sites are added regularly.

= How to Make Your WordPress Site Private =

Setting up a private WordPress site with All-In-One Intranet takes about one minute:

1. Install and activate the plugin from the WordPress plugin directory
2. Go to **Settings > All-In-One Intranet** in your WordPress admin (or **Network Admin > Settings > All-In-One Intranet** for multisite)
3. Check the box labeled **"Force site to be entirely private"**
4. Click **Save Changes**

That is all it takes. Your site is now private. Any visitor who is not logged in will be redirected to the WordPress login page. The REST API, XML-RPC, and search engine indexing are all locked down automatically.

If you see a warning about registration settings after enabling privacy, follow the link in the warning to disable open registration and close the gap.

= How to Set Up Auto-Logout for Inactive Users =

The auto-logout feature protects your intranet from unattended browser sessions:

1. Go to **Settings > All-In-One Intranet**
2. Find the **Auto Logout** section
3. Enter a number in the time field (e.g., 30)
4. Select the time unit from the dropdown: **Minutes**, **Hours**, or **Days**
5. Click **Save Changes**

Users who are inactive for longer than the configured period will be logged out on their next page interaction. Their activity timer resets on every page load, so active users are never interrupted.

To disable auto-logout, clear the time field and save.

= How to Configure Login Redirect =

To send users to a specific page after they log in:

1. Go to **Settings > All-In-One Intranet**
2. Find the **Login Redirect** section
3. Enter the full URL of your desired landing page (e.g., `https://example.com/welcome`)
4. Click **Save Changes**

Users who log in via `/wp-login.php` will now land on that page instead of the WordPress dashboard. Users who were redirected to the login page from a specific URL will still return to that URL after logging in.

= How to Set Up a WordPress Multisite Intranet =

For organizations running a WordPress multisite network:

1. Go to **Network Admin > Settings > All-In-One Intranet**
2. Enable **"Force site to be entirely private"** to restrict the entire network to logged-in users
3. Optionally enable **"Require logged-in users to be members of a sub-site to view it"** for per-site access control
4. Under **Sub-site Membership**, select a default role to automatically assign users to sub-sites
5. Click **Save Changes**

The privacy and membership settings apply network-wide. The default role assignment runs automatically when new users or new sub-sites are created. Existing sub-sites and users are not affected retroactively when you change the role setting.

= Security Features =

All-In-One Intranet takes a layered approach to access control:

* **Authentication enforcement** - uses WordPress's built-in `auth_redirect()` function for reliable login redirection
* **REST API lockdown** - blocks unauthenticated API requests, preventing data access through endpoints like `/wp-json/wp/v2/posts`
* **XML-RPC disabling** - completely disables XML-RPC when privacy is active
* **No-role user handling** - on single-site installations, users who are logged in but have no assigned role are logged out and shown an error message, preventing access by deactivated accounts
* **Registration monitoring** - displays admin warnings if WordPress is configured to allow open registration, which would undermine your private site setup
* **Nonce verification** - all settings forms use WordPress nonce validation to prevent cross-site request forgery
* **Capability checks** - settings pages require `manage_options` (single site) or `manage_network_options` (multisite) capabilities

Note that media uploads (images, PDFs, etc.) remain accessible to anyone who knows their direct URL. This is a limitation of how WordPress stores media files and is common to most privacy plugins. If you need to protect individual file downloads, consider a dedicated file protection plugin alongside All-In-One Intranet.

= For Developers =

All-In-One Intranet provides the `aioi_allow_public_access` filter for developers who need to make specific pages or endpoints accessible without authentication.

This filter runs during both the template redirect check and the REST API dispatch check. Return `true` to allow public access for the current request:

`add_filter( 'aioi_allow_public_access', function( $allow ) {
    // Allow public access to a specific page
    if ( is_page( 'public-landing' ) ) {
        return true;
    }
    return $allow;
} );`

This is useful for exposing specific landing pages, webhook endpoints, or custom API routes while keeping the rest of the site private.

= Google Workspace Integration =

If your organization uses Google Workspace (formerly Google Apps), two companion plugins extend your intranet:

* **[Google Apps Login](https://wp-glogin.com/glogin/?utm_source=wprepo&utm_medium=link&utm_campaign=AllInOneIntranet)** - lets employees sign in to WordPress using their Google Workspace accounts. Domain admins can manage WordPress access entirely from the Google Admin Console, and only authorized employees can access the intranet.

* **[Google Drive Embedder](https://wp-glogin.com/drive/?utm_source=wprepo&utm_medium=link&utm_campaign=AllInOneIntranet)** - allows authors to embed Google Docs, Sheets, Slides, and other Drive files directly into pages and posts. Useful for intranets where documentation lives in Google Drive.

Visit [wp-glogin.com](https://wp-glogin.com/?utm_source=wprepo&utm_medium=link&utm_campaign=AllInOneIntranet) for more information about these and other plugins.

== Screenshots ==

1. Regular settings page to configure intranet.
2. Network-specific settings page to configure intranet.

== Frequently Asked Questions ==

= How do I make my WordPress site completely private? =

Install and activate the plugin, then go to Settings > All-In-One Intranet and check "Force site to be entirely private." All pages, posts, and custom content types will require login. The REST API and XML-RPC are also locked down automatically.

= Does the plugin protect uploaded media files? =

No. Media files (images, PDFs, videos, etc.) that are uploaded through WordPress remain accessible to anyone who knows the direct URL. This is because WordPress serves media files directly through your web server, bypassing PHP and plugin logic. This limitation is common to most WordPress privacy plugins. If direct media file protection is a requirement, you would need a server-level solution or a dedicated download protection plugin in addition to All-In-One Intranet.

= Does it block the WordPress REST API? =

Yes. When the private site option is enabled, all unauthenticated REST API requests receive a 401 error response. This prevents external tools, scripts, or bots from accessing your content through API endpoints like `/wp-json/wp/v2/posts`. Authenticated requests from logged-in users continue to work normally.

= How does auto-logout work? =

The plugin records a timestamp each time a logged-in user loads a page. On the next page load, it compares the current time against the stored timestamp. If the difference exceeds the configured idle time, the user is logged out immediately. The idle timer resets on every page load, so users who are actively browsing are never interrupted. You can set the timeout in minutes, hours, or days.

= Can I set a custom page for users to see after login? =

Yes. In the Login Redirect section of the plugin settings, enter the full URL of the page you want users to land on after logging in. This overrides the default WordPress behavior of sending users to the dashboard. Note that if a user was trying to reach a specific page before being asked to log in, they will be redirected back to that page instead of the custom redirect URL.

= Does it work with WordPress multisite? =

Yes. The plugin is fully compatible with WordPress multisite. In a multisite network, the settings are managed from the Network Admin area. You can make the entire network private, require users to be members of individual sub-sites before accessing them, and automatically assign roles to users across sub-sites when new users or new sites are created.

= Can I allow certain pages to remain public while the rest of the site is private? =

Yes, but it requires a small amount of code. Use the `aioi_allow_public_access` filter in your theme's `functions.php` file or a custom plugin. For example, to keep a page with the slug "public-info" accessible without login:

`add_filter( 'aioi_allow_public_access', function( $allow ) {
    if ( is_page( 'public-landing' ) ) {
        return true;
    }
    return $allow;
} );`

= Does it block search engines from indexing my site? =

Yes. When the private site option is enabled, the plugin overrides the robots.txt file to disallow all crawling. It also disables outgoing pingbacks and trackbacks, so your site does not announce new content to external services or ping aggregators.

= Does it work with caching plugins? =

Generally, yes. Most WordPress caching plugins bypass the cache for logged-in users and do not cache redirects, so the privacy enforcement works as expected. However, aggressive full-page caching at the server level (Varnish, Nginx FastCGI cache) may serve cached pages to unauthenticated users if not configured to respect WordPress login cookies. If you use server-level caching, make sure it bypasses the cache when WordPress login cookies are absent.

= What happens to users with no role on my site? =

On a single-site WordPress installation, users who are logged in but have no assigned role are treated as unauthorized. The plugin logs them out and displays a message explaining that they do not have permission to access the site. This prevents access by accounts that have been deactivated by removing their role rather than deleting them.

= Does it block XML-RPC access? =

Yes. When the private site option is active, the plugin completely disables XML-RPC. This prevents any remote access through the XML-RPC protocol, including third-party apps and services that use it to interact with WordPress.

= Is it compatible with custom login page plugins? =

The plugin uses WordPress's built-in `auth_redirect()` function to send unauthenticated users to the login page. Most custom login page plugins work by intercepting the standard login URL and redirecting to a custom page. Because All-In-One Intranet relies on standard WordPress authentication functions, it is generally compatible with custom login page plugins. The login redirect feature also works regardless of whether the user logs in through the default or a custom login page.

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

= 1.8.1 =
* Changed: Compatibility with WordPress 6.9.
* Fixed: Made sure the XMLRPC is also safeguarded against unauthorized access.

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

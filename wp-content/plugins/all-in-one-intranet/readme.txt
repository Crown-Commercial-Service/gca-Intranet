=== Intranet & Private Site - All-In-One Intranet ===
Contributors: slaFFik, jaredatch, smub
Tags: intranet, private site, auto logout, restrict access, multisite
Requires at least: 5.5
Requires PHP: 7.0
Tested up to: 7.0
Stable tag: 1.10.0
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Turn WordPress into a private intranet in one click. Restrict access to logged-in members, with auto-logout and login redirect, also on multisite.

== Description ==

Plenty of companies run their intranet on WordPress. The problem is that WordPress was built for public-facing sites. Making it work as a private intranet typically requires installing multiple plugins, configuring each one separately, and hoping they all play nicely together.

All-In-One Intranet turns your WordPress site into a fully private intranet from a single plugin. Enable privacy with one checkbox, set up auto-logout to protect sensitive information, configure where users land after login, and manage multisite access controls, all from one settings page.

Corporate intranet, private knowledge base, restricted client portal, internal comms hub: the privacy and access control are the same job in each case, and this plugin does that part.

= What is an Intranet? =

An intranet is a private website or network used internally by an organization. Unlike a public website, an intranet is only accessible to authorized users: typically employees, contractors, or specific team members.

Common uses for a WordPress intranet include:

* Internal company communications and announcements
* Employee handbooks, policies, and procedures
* Knowledge bases and documentation wikis
* Project collaboration and team coordination
* HR portals for onboarding and training materials
* Client portals with restricted access to project files

WordPress already has the editing interface and the user roles for all of these. What it does not have is the access control layer, which is what All-In-One Intranet adds.

= Features =

All-In-One Intranet has five features, covering what most intranets need:

= One-Click Private Site =

Enable the "Force site to be entirely private" checkbox, and your entire WordPress site becomes restricted to logged-in users only. Anyone who is not logged in gets redirected to the WordPress login page automatically.

This single setting handles multiple layers of privacy at once:

* **Page and post access** - all frontend content requires authentication
* **REST API protection** - unauthenticated REST API requests are blocked with a 401 error, so data cannot leak through the API
* **XML-RPC blocking** - XML-RPC is disabled entirely when privacy is active
* **Search engine blocking** - the robots.txt file is automatically updated to disallow all crawling, so your private content stays out of search indexes
* **Pingback suppression** - outgoing pingbacks and trackbacks are disabled so your private site does not announce itself to external services
* **Feed protection** - RSS, Atom, and comment feeds require authentication, so protected posts and comments cannot be read through `/feed/` or crafted feed URLs
* **Comment and trackback blocking** - unauthenticated visitors cannot post comments or trackbacks to protected content through `wp-comments-post.php` or `wp-trackback.php`
* **Admin endpoint gating** - the `admin-ajax.php` and `admin-post.php` handlers require a valid login, so public "nopriv" actions registered by your theme or other plugins do not run for logged-out visitors. A short allowlist keeps the handlers people need before login working: two-factor and passkey plugins, and connected site-management platforms
* **Entry-point coverage** - WordPress files that load the site outside the normal page render, such as `wp-links-opml.php` (the blogroll and OPML export) and `wp-activate.php`, are sealed so they cannot leak post content, feeds, your site title, or the WordPress version
* **Slug enumeration prevention** - WordPress's canonical redirect does not reveal the slugs of private posts to unauthenticated visitors on pretty permalinks
* **Role and membership enforcement** - the REST API and comment gates apply the same role and sub-site membership checks as the rest of the site, so a logged-in user with no role, or who is not a member of the current sub-site, is blocked there too

The plugin also watches your registration settings: if "Anyone can register" is enabled, or open registration is allowed on a multisite network, a warning appears on the settings page so you can fix it.

= Auto-Logout for Inactive Users =

Shared workstations and forgotten browser tabs are a security risk for intranets. The auto-logout feature lets you set a maximum idle time (in minutes, hours, or days) after which users are automatically logged out.

The plugin tracks when each browser session was last active. On every page load, it checks whether the configured idle time has been exceeded. If a session has been idle for too long, the user is logged out immediately and redirected back to the page they were viewing, which triggers the login wall if the site is private. Each session keeps its own timer, so staying active at your desk does not keep a forgotten login on a shared machine alive.

This protects sensitive company information without requiring users to remember to log out manually. Set it to 30 minutes for high-security environments, a few hours for typical office use, or leave it blank to disable the feature entirely.

Auto-logout counts browser sessions only. A request that authenticates without a login cookie (a site management dashboard using its own API, or a script using an application password) has no session to expire, so it is left alone.

= Custom Login Redirect =

By default, WordPress sends users to the dashboard after they log in. For an intranet, this is not useful: your team is logging in to read content, not to manage the site.

The login redirect feature lets you set any URL on your site as the post-login landing page. Point it to your company homepage, a news feed, or a team dashboard so users see relevant content right away.

This redirect only applies when users log in directly through the standard WordPress login page. A user who was redirected to the login page from a specific URL is sent back to that URL instead.

= Multisite Sub-site Privacy =

If you run a WordPress multisite network, you can require logged-in users to be members of a specific sub-site before they can view it. This is useful for organizations with multiple departments, teams, or client areas, each with their own sub-site that should only be visible to relevant people.

When a user who is logged in but not a member of the current sub-site tries to access it, they see a message listing all the sub-sites they do have access to, with clickable links to navigate there. Access to the Network Admin area is never restricted by this setting.

This option works in combination with the main privacy setting: enable private site first, then add sub-site membership requirements on top of it.

= Multisite Default Role Assignment =

Managing user access across multiple sub-sites in a WordPress network can be tedious: every new user and every new sub-site means assigning roles by hand.

The default role assignment feature automates this. Choose a role (Subscriber, Editor, Administrator, or any custom role), and the plugin handles the rest:

* When a **new user** is created, they are automatically added to every active sub-site in the network with the selected role
* When a **new sub-site** is created, all existing users are automatically added to it with the selected role

= How to Make Your WordPress Site Private =

It takes about a minute to make your WordPress site private with All-In-One Intranet:

1. Install and activate the plugin from the WordPress plugin directory
2. Go to **Settings > All-In-One Intranet** in your WordPress admin (or **Network Admin > Settings > All-In-One Intranet** for multisite)
3. Check the box labeled **"Force site to be entirely private"**
4. Click **Save Changes**

That is all it takes. Your site is now private. Any visitor who is not logged in will be redirected to the WordPress login page. The REST API, XML-RPC, and search engine indexing are all locked down automatically. If you see a warning about registration settings, follow its link to disable open registration.

= How to Set Up Auto-Logout for Inactive Users =

The auto-logout feature protects your intranet from unattended browser sessions:

1. Go to **Settings > All-In-One Intranet**
2. Find the **Auto Logout** section
3. Enter a number in the time field (e.g., 30)
4. Select the time unit from the dropdown: **Minutes**, **Hours**, or **Days**
5. Click **Save Changes**

Users who are inactive for longer than the configured period will be logged out on their next page interaction. Their activity timer resets on every page load, so active users are never interrupted. The timer belongs to the browser session, so being active in one browser does not keep the same account signed in on another machine.

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
* **REST API lockdown** - blocks unauthenticated API requests to endpoints like `/wp-json/wp/v2/posts`
* **XML-RPC disabling** - completely disables XML-RPC when privacy is active
* **No-role user handling** - on single-site installations, users who are logged in but have no assigned role are logged out and shown an error message, so an account deactivated by removing its role cannot get in
* **Registration monitoring** - warns you if WordPress is configured to allow open registration, which would undermine your private site setup
* **Nonce verification and capability checks** - settings forms use WordPress nonce validation, and settings pages require `manage_options` (single site) or `manage_network_options` (multisite)

Media uploads (images, PDFs, etc.) remain accessible to anyone who knows their direct URL. This is a limitation of how WordPress stores media files and is common to most privacy plugins. If you need to protect individual file downloads, consider a dedicated file protection plugin alongside All-In-One Intranet.

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

Two more filters cover login-screen plugins, which have to finish their authentication exchange while the visitor is still logged out. `aioi_public_actions` lists the `admin-ajax.php` / `admin-post.php` actions that may still run while the site is private, and `aioi_public_rest_routes` does the same for plugins that verify a second factor over the REST API, matching a route exactly or as a path segment prefix. Two-factor and passkey plugins are already covered out of the box, and every bundled entry applies only while the plugin it belongs to is active, so a private site never leaves an endpoint open for a plugin it does not run.

Only add authentication endpoints to either list. Anything on them can be called by logged-out visitors, so it must not return site content, and it must do its own credential or token check. Site-management platforms are the deliberate exception, because returning site content is their whole purpose. See the next section.

= Site Management Dashboard Compatibility =

Many agencies and IT teams look after every site they run from a central dashboard that handles updates and backups. Making a site private should not cut it off from that dashboard, so these connector plugins are supported out of the box, with nothing to configure:

* **[ManageWP Worker](https://wordpress.org/plugins/worker/)** - also the connector used by GoDaddy Pro
* **[MainWP Child](https://wordpress.org/plugins/mainwp-child/)**
* **[InfiniteWP Client](https://wordpress.org/plugins/iwp-client/)**
* **[WP Umbrella](https://wordpress.org/plugins/wp-health/)**
* **[WP Remote](https://wordpress.org/plugins/wpremote/)**

Each of these talks to its dashboard over its own signed protocol rather than a browser login. Site syncing, plugin and theme updates, backups, and the dashboard's one-click login into wp-admin all keep working with "Force site to be entirely private" enabled.

ManageWP, MainWP and InfiniteWP answer their dashboards from their own hooks, before the privacy gate runs, so nothing needs to be opened for them. The other two each need one narrow exemption, granted only to a request the platform itself has already vouched for. WP Umbrella works entirely through the WordPress REST API, which a private site otherwise closes, so its own namespace opens only for a request presenting WP Umbrella's credentials, and the `admin-ajax.php` calls it makes back to itself open only for a request carrying the nonce those handlers verify. WP Remote routes part of its traffic through `admin-ajax.php`, and that one action opens only once WP Remote has checked the caller's signature and registered its handler. An anonymous request to either still meets the login wall, a credentialed one still has to satisfy the platform's own checks, and every other route, action and page stays private.

Auto-logout handles those background calls too: a dashboard request is never logged out in the middle of an API call, and it does not count as the connected administrator's own activity, so frequent polling cannot hold a real person's session open past the idle limit you set.

A private site does still hide anything a service fetches anonymously from its own servers: uptime monitoring, broken-link checking, and SEO or page-speed scans. Those requests carry no login, so they get the login redirect and the dashboard usually reports the site as down or its links as broken. That applies to every external service, not only these five.

== Screenshots ==

1. All-In-One Intranet settings: make your WordPress site private, set auto-logout, and configure the login redirect.
2. Network Admin settings for a multisite intranet: network-wide privacy and per-sub-site membership controls.

== Frequently Asked Questions ==

= How do I make my WordPress site completely private? =

Install and activate the plugin, then go to Settings > All-In-One Intranet and check "Force site to be entirely private." All pages, posts, and custom content types will require login. The REST API and XML-RPC are also locked down automatically.

= Does the plugin protect uploaded media files? =

No. Media files (images, PDFs, videos, etc.) that are uploaded through WordPress remain accessible to anyone who knows the direct URL. This is because WordPress serves media files directly through your web server, bypassing PHP and plugin logic. This limitation is common to most WordPress privacy plugins. If direct media file protection is a requirement, you would need a server-level solution or a dedicated download protection plugin in addition to All-In-One Intranet.

= Does it block the WordPress REST API? =

Yes. When the private site option is enabled, unauthenticated REST API requests receive a 401 error response. This prevents external tools, scripts, or bots from accessing your content through API endpoints like `/wp-json/wp/v2/posts`. Authenticated requests from logged-in users continue to work normally. Two narrow sets of routes are exempt, and only while the plugin providing them is active: the endpoints two-factor and passkey plugins use to finish a login, and a site management platform's own API namespace for a request presenting that platform's credentials. See "For Developers" in the Description tab for how to adjust either list.

= How does auto-logout work? =

The plugin records a timestamp on the browser session each time a logged-in user loads a page. On the next page load, it compares the current time against that session's timestamp. If the difference exceeds the configured idle time, the user is logged out immediately. The idle timer resets on every page load, so users who are actively browsing are never interrupted, and each browser session keeps its own timer, so staying signed in at your desk does not keep a forgotten login on a shared machine alive. You can set the timeout in minutes, hours, or days.

Only browser sessions are subject to it. A request that authenticates without a login cookie (a site management dashboard using its own API, or a script using an application password) has no session to expire, so it is left running and it does not reset anybody's idle timer.

= Can I set a custom page for users to see after login? =

Yes. In the Login Redirect section of the plugin settings, enter the full URL of the page you want users to land on after logging in. This overrides the default WordPress behavior of sending users to the dashboard. If a user was trying to reach a specific page before being asked to log in, they will be redirected back to that page instead of the custom redirect URL.

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

= Is it compatible with two-factor authentication plugins? =

Yes. Some 2FA plugins complete part of the login exchange with a background request from the login screen, which happens before the visitor is logged in. The plugin recognizes those requests and lets them through, so the second-factor prompt appears and login can finish. Wordfence, WP 2FA, miniOrange 2-Factor, Solid Security, AIO Login, Limit Login Attempts Reloaded and Login With Ajax passkey login are covered out of the box. Login With Ajax's own AJAX login form is not: that form lives on a front-end page, which is behind the login wall on a private site anyway. Plugins that keep the whole flow on the login page itself, such as Two-Factor, CleanTalk Security and Google Authenticator, need nothing special.

Account recovery links are treated differently from login. A link that switches off a user's second factor is not needed to finish a login, so it stays behind the login wall even when the rest of that plugin is supported. miniOrange's emailed 2FA reset link is the current example. Ask an administrator to clear the second factor, or open that one endpoint yourself with the `aioi_allow_public_access` filter.

Shield Security is the exception: its 2FA step shares one general-purpose endpoint with the rest of the plugin, so opening it would also open everything else behind that endpoint. If you use Shield's 2FA on a private site, add `shield_action` yourself with the `aioi_public_actions` filter described in the Description tab.

If any other 2FA plugin reports a generic authentication error at login on a private site, its background request is being sent to the login wall. Developers can allow it with the `aioi_public_actions` or `aioi_public_rest_routes` filter, also in the Description tab.

= Is it compatible with ManageWP, MainWP, InfiniteWP, WP Umbrella, or WP Remote? =

Yes, all five, on a private site, with nothing to configure: ManageWP Worker (the same connector GoDaddy Pro uses), MainWP Child, InfiniteWP Client, WP Umbrella and WP Remote. Their dashboard requests are signed with their own keys instead of relying on a browser login, so site syncing, plugin and theme updates, backups, and the dashboard's one-click login into wp-admin keep working with "Force site to be entirely private" enabled.

ManageWP, MainWP and InfiniteWP answer their dashboards from their own hooks before the privacy gate runs, so they need no exemption at all. WP Umbrella works through the WordPress REST API and WP Remote routes part of its traffic through `admin-ajax.php`, both of which a private site closes to unauthenticated callers, so each gets one narrow exemption while its plugin is installed. Neither is a blanket hole: WP Umbrella's namespace opens only for a request that presents its credentials, and WP Remote's action only once WP Remote has verified the caller's signature itself. An anonymous request to either endpoint is still sent to the login wall, and a credentialed one still has to pass the platform's own checks. The exemption hands the decision to the platform rather than removing it.

Auto-logout understands these requests as well. A dashboard poll is never logged out in the middle of an API call, and it does not count as the connected administrator's own activity, so it cannot keep a real person signed in past the idle limit you configured.

The parts that cannot work on a private site are the ones that fetch your pages anonymously from the vendor's servers: uptime monitoring, broken-link checking, and SEO or page-speed scans. Those requests carry no login, so they receive the login redirect and the service will usually report the site as down or its links as broken. Switch those particular monitors off for private sites. There is no way to satisfy them and stay private.

= How is this different from a membership plugin? =

Membership plugins are built to sell access: they manage subscription levels, process payments, and drip-feed content to paying customers. All-In-One Intranet is built for internal, private sites where everyone who logs in is already a trusted member of your organization. It locks the entire site down to logged-in users in one click instead of gating individual posts behind a purchase or subscription tier. If you need to charge for access, use a membership plugin; if you need a private company intranet, this is the simpler fit.

= Does it work with page builders like Elementor, Beaver Builder, or Divi? =

Yes. All-In-One Intranet works at the authentication layer and does not change how your pages are built or rendered, so you can design your intranet with any page builder and the privacy enforcement still applies to the finished pages. If a builder's live preview appears to redirect to the login screen, that is expected for a logged-out request. Edit while logged in and the builder behaves normally.

= Will it slow down my site? =

No noticeable impact. The privacy check runs early on each request and is a simple logged-in or logged-out test, and the auto-logout feature reads and updates a single session record per page load. There are no external calls and no heavy database queries involved.

= Can I keep my custom-branded login page? =

Yes. The plugin relies on WordPress's standard authentication, so it does not replace or restyle your login screen. If you use a custom login page plugin, unauthenticated visitors are sent to whatever login URL WordPress is configured to use, and your branding is preserved.

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

= 1.10.0 =
* Added: Compatibility with site management platforms: ManageWP (also GoDaddy Pro), MainWP, InfiniteWP, WP Umbrella and WP Remote.
* Changed: Auto-logout now applies only to browser sessions. A request that authenticates without a login cookie has no session to expire, so it is left alone and no longer resets anybody's idle timer.
* Fixed: ManageWP - with auto-logout enabled, the Worker connector stopped answering its dashboard altogether, and its one-click login into wp-admin could log you straight back out.
* Fixed: ManageWP, MainWP and InfiniteWP - with auto-logout enabled, a connected dashboard kept resetting the idle timer of the administrator account it signs in as, so that person was never logged out for inactivity.
* Fixed: WP Umbrella - it could not reach a "Force private" site at all, because it works entirely through the REST API and met the private-site 401 before it could authenticate. Its own API namespace and the `admin-ajax.php` calls it makes to itself are now allowed through while it is installed.
* Fixed: WP Remote - it lost the part of its dashboard traffic that goes through `admin-ajax.php` on a "Force private" site. That one action is now allowed through, once WP Remote has authenticated the caller.
* Fixed: With auto-logout enabled, activity in one browser kept every other login of the same account signed in. Each browser session now keeps its own idle timer.
* Fixed: On a "Force private" site running WordPress 7.0, resetting a password failed at the last step with "Your password reset link appears to be invalid".

= 1.9.2 =
* Fixed: Nobody could log in to a "Force private" site while a two-factor authentication plugin such as Wordfence was active.
* Fixed: On multisite, creating a sub-site logged a WordPress deprecation notice.
* Fixed: On multisite with a default sub-site role set, the creator of a new sub-site was demoted from administrator to that role.
* Fixed: With auto-logout enabled, users could be logged out again the moment they passed a two-factor check if it took them too long to enter their OTP code.

= 1.9.1 =
* Fixed: Fatal error "Call to undefined function getmypid()" on hosts where the PHP `getmypid()` function is disabled.

= 1.9.0 =
* Changed: Compatibility with WordPress 7.0.
* Changed: The "Force private" auth gate now works correctly when WordPress is installed in a subdirectory.
* Changed: On activation the plugin now plants a small must-use companion (`wp-content/mu-plugins/aioi-installing-gate.php`); deactivation removes it. It closes a `/wp-activate.php` content-leak surface that the main plugin cannot reach on its own (see Fixed).
* Changed: The post-login redirect URL is now sanitized and normalized when saved: a bare path such as `dashboard` is stored as `/dashboard`, while site-relative paths and full http(s) URLs are kept as entered.
* Fixed: Fixed multiple access-control bypasses in "Force private" mode that allowed unauthenticated visitors to read protected posts and feeds via crafted URLs.
* Fixed: Closed a content-leak on "Force private" sites where unauthenticated visitors could read protected posts, feeds, and REST output through `/robots.txt` by overriding its query string (for example `/robots.txt?robots=0&feed=rss2` or `?robots=0&p=N`).
* Fixed: Fixed unauthenticated post-slug enumeration via WordPress's canonical redirect on pretty permalinks.
* Fixed: Closed an access-control bypass on "Force private" sites where unauthenticated visitors could post comments and trackbacks to protected posts via `wp-comments-post.php` and `wp-trackback.php`.
* Fixed: Closed a content-leak on single-site (and non-network-activated multisite) "Force private" installs where unauthenticated visitors could read RSS feeds and REST API output through `/wp-activate.php` (for example `?feed=rss2`, `?p=N&feed=comments-rss2`, or `?rest_route=/wp/v2/posts`). WordPress core skips loading regular plugins on `wp-activate.php` because of `WP_INSTALLING`, so the gate is now enforced from a must-use companion file.
* Fixed: Closed a content-leak on "Force private" sites where unauthenticated visitors could read the site's blogroll (the OPML links export), title, and WordPress version through `/wp-links-opml.php`, which loads WordPress without firing the normal page-render auth gate.
* Fixed: Closed an access-control bypass on "Force private" sites where unauthenticated visitors could reach the AJAX and form-handler endpoints (`/wp-admin/admin-ajax.php` and `/wp-admin/admin-post.php`). WordPress treats these as admin requests, so the normal page-render auth gate did not apply to them. Any public ("nopriv") action registered by the active theme or another plugin would run for logged-out visitors even though the site is private, potentially exposing data or triggering actions that should require a login. Both endpoints now require a valid login.
* Fixed: On "Force private" sites the REST API and comment/trackback gates now apply the same role and sub-site-membership checks as the rest of the site, so a logged-in user with no role (or who is not a member of the current sub-site) can no longer read REST API content or post comments that they would otherwise be blocked from.
* Fixed: Additional "Force private" hardening: the inactivity auto-logout now bounces through a host-validated safe redirect instead of trusting the `Host` header, the network settings save now performs an explicit capability check, and the default sub-site member role is validated against the registered roles when saved.
* Fixed: Minor robustness and standards fixes: the private-site `robots.txt` now includes a `User-agent: *` line, the plugin's PHP files guard against direct access, and corrected an internationalization issue in a registration warning notice.

= 1.8.1 =
* Changed: Compatibility with WordPress 6.9.
* Fixed: Made sure the XMLRPC is also safeguarded against unauthorized access.

= 1.8.0 =
* IMPORTANT: The minimum WordPress version is now WordPress 5.5.
* IMPORTANT: The minimum PHP version is now PHP 7.0.
* Added: Multisite-specific options: "Require logged-in users to be members of a sub-site to view it".
* Added: "Sub-site Membership" - assign a user role for newly added users.
* Changed: Compatibility with WordPress 6.6.
* Fixed: Several security-related improvements in various parts of the plugin.
* Fixed: Code style improvements.

= 1.7.1 =
* Security update and added WordPress 5.7 compatibility.

= 1.7 =
* Security update and added WordPress 5.6 compatibility.

= 1.6 =
* Security update and added WordPress 5.4 compatibility.

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

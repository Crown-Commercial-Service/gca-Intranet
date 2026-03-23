## Changelog

**Admin and Site Enhancements (ASE) v1.0.0** was released on October 17, 2022. Since then, there have been **85 _major_ releases** (e.g. 1.1.0 ) and **188 _minor_ releases** (e.g. 4.9.1), for a **total of 273 releases**.

Each **_major release_** usually corresponds with the addition of one new module/feature. Each module/feature usually is the equivalent of one (or more) single-purpose plugin. Each **_minor release_** usually contain one or more bugfix or improvements to existing modules/features.

[**Upgrade to ASE Pro**](https://www.wpase.com/chnlg-to-web). Lifetime Deal (LTD) available.

### 8.5.1 (2026.03.16) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Utilities >> Email Delivery**: SMTP password is now securely encrypted at rest (in wp_options). Props to Bekee G. for prompting this improvement.

* **[IMPROVED in Pro] Admin Interface >> Admin Menu Organizer**: Implement an option to add custom menu and submenu items. The custom menu item can use custom icon, title, link and capability required, or can be used as a separator or section header in the admin menu with no linking. Props to Kai, Leslie, Ken R., @27pixel, @firstandlast, Jarosław K., Mike D. @oxygen, Jacob N., Ramit I., Bent F. and Yaser for prompting this improvement.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: add status filter (active vs inactive) in snippets listing page.

* **[IMPROVED in Pro] Utilities >> Maintenance Mode**: allow wildcard use in excluding URLs from maintenance mode. Props to Tony S. for prompting this improvement.

* **[FIXED in Pro] Content Management >> Media Categories**: fix broken CSS in categories tree context menu causing the menu to look squished.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [38 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Ukrainian, Portuguese (Brazil), Polish, Dutch, Norwegian, Spanish, Danish.
    * ASE Pro: Updated Portuguese (Brazil), Polish, Norwegian, Hungarian, Spanish, Catalan.

### 8.5.0 (2026.03.09) - ASE Free and Pro

* **[ADDED in Pro] Content Management >> Post Type Switcher**: Easily switch the post type of one or more posts to a different post type from the classic editor, block editor, quick edit or bulk edit. Props to Bas B. Yalcin and Sarah for suggesting this addition.

* **[IMPROVED in Free and Pro] Disable Components >> Disable Smaller Components**: Added an option to disable [site admin email verification screen](https://make.wordpress.org/core/2019/10/17/wordpress-5-3-admin-email-verification-screen/) that was added since WordPress v5.3.

* **[IMPROVED in Pro] Security >> Email Address Obfuscator**: add an option to use "high-compatibility" mode which should help avoid wrongly-ordered email address when the obfuscator shortcode is used in certain widgets / components in page builders, e.g. Elementor Icon Box. Props to Kim N. and Jay T. for prompting this improvement.

* **[IMPROVED in Pro] Content Management >> Terms Order**: 
  * Enable term drag and dropp between levels. In other words, drag a parent-level term as the child of another parent and vice versa.
  * Block editor's taxonomy meta boxes now reflects the custom terms order.

* **[FIXED in Pro] Content Management >> Content Order**: terms from non-hierarchical taxonomies are now properly shown when "Show taxonomy terms" is checked. Props to Christian S. for reporting the issue.

* **[FIXED in Pro] Log In/Out | Register >> Login Page Customizer**: fixed PHP deprecation issue. Props to Bilgi for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [38 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Portuguese (Brazil), Polish, Norwegian, German, French, Dutch (Belgium), Danish, Chinese (Taiwan)
    * ASE Pro: Updated Czech, Spanish (Spain), Norwegian, Portuguese (Brazil)

### 8.4.2 (2026.03.02) - ASE Free and Pro

* **[FIXED in Free] Content Management >> Content Duplication**: fixed missing 'Duplicate' link in post actions row in the post listing screen since v8.4.1 introduced a security fix for this module. Props to st@n, @ilanaw, @michoscopic, @lineadiconfine, @dannyfoo and @webkatdesign for [reporting](https://wordpress.org/support/topic/duplicate-post-page-7-4-1-bug/) the bug.

* **[IMPROVED in Pro] Security >> Two-Factor Authentication (2FA)**: Grace period notice that urges users with the relevant roles to set up 2FA is now excluded from being hidden by the Hide Admin Notices module. This ensures that those users see it when they log in.

* **[FIXED in Pro] Plugin Activation**: fixed a fatal error that occurs in a certain scenario during plugin activation. Props to Alistair W. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Media Categories**: fixed a PHP warning that occurs in a certain scenario.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed a fatal error that occurs when trying to edit a Bricks template for a taxonomy archive when a custom field group is attached to that taxonomy and no specific taxonomy term has been selected to preview the archive in the builder. Props to David W. for reporting the issue in detail (with error stack trace) and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Content Order**: Fixed a bug that affects non-hierarchical post types causing duplicating menu_order in pairs of posts, posts occasionally / randomly being nested as child post during drag and dropping, and drag-and-dropping not working reliably when dragging a bit too far to the right hand side in the 'Order' page. Props to Christian S. for reporting the issue in detail, with screen recordings, and facilitating the troubleshooting process further.

* **[FIXED in Pro] Utilities >> File Manager**: fixed a fatal error that occurs in a scenario where mime_content_type() is not available in PHP because magic module is disabled. Props to Bill J. for reporting the issue in detail.

* **[FIXED in Pro] Utilities >> Local User Avatar**: fixed an issue with `get_avatar_url()` override always returning URL for the `thumbnail` size of the avatar and ignoring size parameter. This for example, can break Bricks builder's `{author_avatar:512}` dynamic tag. Props to Martin Ž. for reporting the issue in detail.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [38 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Vietnamese, Portuguese (Brazil), Polish, Persian, Norwegian, Arabic.
    * ASE Pro: Updated Spanish (Spain), Catalan, Hungarian, Vietnamese, Norwegian, Portuguese (Brazil), Danish.

### 8.4.1 (2026.02.23) - ASE Free and Pro

* **[SECURITY FIX in Free] Content Management >> Content Duplication**: fixed "Broken Access Control" vulnerability with CVSS severity "Medium (5.4)"" that could cause "Information Disclosure" as responsiblly disclosed by security researcher Jakub Herman via Pathstack. In practical terms, this for example, can allow a logged-in user with contributor role to duplicate (and then view) password-protected content created by an administrator via the admin bar duplication link of the password-protected post in the frontend.

* **[IMPROVED and FIXED in Pro] Utilities >> Form Builder**: 
  * **Added bulk export / import for forms** via an "Export / Import Forms" button in the forms listing page, which links to the Form Builder section in ASE settings "Export | Import" section. You can choose to export all forms, or manually select which forms to export.
  * **Added bulk export / import for style templates** via an "Export / Import Style Templates" button in the forms listing page, which links to the Form Builder section in ASE settings "Export | Import" section. You can choose to export all style templates, or manually select which style templates to export.
  * **Added bulk export for form entries** via an "Export Entries" button in the forms listing page, which links to the Form Builder section in ASE settings "Export | Import" section. You can choose to export entries from a particular form, and customize what entry data will be exported, set the output format (CSV, Excel or JSON) and set a data range to filter by. Props to Uli L., Richard L. and Dave P. for prompting this improvement.
  * **Security hardening for the upload field** by removing trust in client-sent constraints and enforcing server-side file policy.
  * **Stop recording IP address** during form submissions.
  * **Fixed custom CSS classes not being output** for "Display" field types, e.g. HTML field. Props to Yan for reporting the issue.

* **[FIXED in Free and Pro] Content Management >> Content Order**: fixed layout issue in the content order page caused by CSS being overridden by the MetForm Pro plugin's CSS. Props to @once-master for prompting this fix.

* **[FIXED in Pro] Security >> CAPTCHA Protectino >> ALTCHA**: fixed a JS error in Firefox browser related to `@wordpress/interactivity` when an ALTCHA field is included in a form via the Form Builder module. Props to Yan for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [38 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Serbian, Turkish, Spanish (Spain), Portuguese (Brazil), Polish, Persian, Norwegian, Dutch, Danish, Azerbaijani, Albanian
    * ASE Pro: Updated Norwegian. Completed Serbian (special props to [Igor E.](https://wordpress.org/support/users/igorel/)).

### 8.4.0 (2026.02.16) - ASE Free and Pro

* **[ADDED in Pro] Security >> Two-Factor Authentication**: Add an extra verification step during login for some or all user roles with grace period settings. Supported methods are authenticator app (TOTP), recovery codes and email. Compatible with the Change Login URL, Limit Login Attempts and CAPTCHA Protection modules. Props to Sebastian, Laurent F., Jonathan, Carlos d.A. Silvan L., Luethi, Ramyt I., Ammar, Marcel S., Drijen S., Stefan B., Timothy L. and Cory V. for prompting this addition.

* **[FIXED in Free and Pro] Disable Components >> Disable Gutenberg**: fixed a PHP fatal error that occurs in a certain scenario. Props to [@d79](https://wordpress.org/support/users/d79/) for [reporting](https://wordpress.org/support/topic/bug-disable-gutenberg-%e2%86%92-fatal-error/) this in detail with the error entry log.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [38 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Danish, Spanish (Chile), Russian, Portuguese (Brazil), Polish, Persian, German, Dutch (Netherlands), Dutch (Belgium)
    * ASE Pro: updated Danish, Portuguese (Brazil)

### 8.3.2 (2026.02.09) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Security >> Limit Login Attempts**:
  * ASE Free: when an IP address is locked out because it's exceeded the allowed failed login attempts amount, there will be a "Release Lock" button shown in a new "Actions" column in the "Failed login attempts" list within the Limit Login Attempts module. This will easily remove the IP address from lockout and allow the user on that IP address to attempt another set of login attempts. Props to [Michael K.](https://wordpress.org/support/users/michoscopic/) for [prompting](https://wordpress.org/support/topic/any-way-to-unblock-user-after-limit-login-attempts/) this improvement.
  * ASE Pro: in addition to the "Release Lock" button, there will also be a "Whitelist" button that will automatically copy the IP address to the "Never block the following IP addresses" section.

* **[IMPROVED in Free and Pro] Content Management >> SVG Upload**: when an SVG has the width and height attributes at 100%, the width and height is now going to be detected from the viewBox attribute and properly saved in the attachment metadata (width, height, dimensions).
  
* **[IMPROVED and FIXED in Pro] Custom Code >> Code Snippets Manager**: 
  * To date, each revision is saved as a new file in the snippets folder. Going forward snippet creation and update will trigger a process to ensure only one file exists for each snippet and snippet deletion will remove snippet revision files, while ensuring the revisions feature will continue to work. Props to Claudio P. for prompting this improvement.
  * Fixed an issue in a certain scenario where snippets filtering and search are not working properly. Props to Oliver S. for reporting this.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: added `m.d.Y` format, e.g. `12.31.2025` for the date field's output format. This format is commonly used in Germany. Props to Oliver Z. for prompting this improvement.

* **[IMPROVED in Pro] Utilities >> Form Builder**: the date picker now shows the year as a dropdown, making it easier to select a particular year. Props to Richard L. for prompting this improvement. Also improved the UI to remove extra white space on the right hand side of the day numerals.

* **[IMPROVED in Pro] Admin Interface >> Hide Admin Bar**: add a new option to "always show the admin bar for administrators on the backend", which is useful for scenarios when an administrator user is also assigned other roles for which the admin bar is being hidden on the backend. Props to André C. for prompting this improvement via a comprehensive feedback (setup, observed behaviour, expected behaviour) and useful screenshots.

* **[IMPROVED in Pro] Utilities >> Form Builder**:
  * Improve mechanism to prevent file of the same name being overwritten via the file upload field. Props to Ingo R. for prompting this improvement.
  * Add a way to choose webhook payload structure so it is more compatible with various requirements. Props to Ingo R. for prompting this imprvement.

* **[FIXED in Pro] Admin Interface >> Admin Logo**: fixed an issue when certain types of SVGs are used for admin menu logo, there are extra-wide spacing at the top and bottom of the logo image. Props to Henry R. for reporting this issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [38 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free - Update Ukrainian, Spanish (Spain), Portuguese (Brazil), Polish, Norwegian, French, Dutch, Arabic.
    * ASE Pro - Update Norwegian, Portuguese (Brazil), Polish.

### 8.3.1 (2026.02.02) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Hide Admin Notices**: notice/ad from BdThemes Element Pack Pro plugin is now properly moved to the notices panel. Props to Alexander P. for reporting the issue.

* **[IMPROVED in Pro] Content Management >>  External Permalinks**: add an option to enable the module "only on", "except on" or "on all" post types. Props to Martin S. for prompting this improvement.

* **[IMPROVED in Pro] Content Management >> Media Categories**: add an option to disable the media categories features for some or all non-administrator user roles with access to the media library, i.e. user roles with `upload_files` capability.

* **[IMPROVED in Pro] Admin Interface >> Admin Logo**: add an option to replace the home icon in the admin bar with the site logo set in "Settings >> General". Props to Martin S. for prompting this improvement.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Gropus**: 
  - add a top sticky bar with title and publish / update button when the custom field group creation / edit screen is scrolled down.
  - in the custom field groups listing page, for the Placement colum, labels are now added before the raw values, e.g. 'post' becomes "Post (post)".
  
* **[IMPROVED in Pro] Optimizations >> Image Upload Control**: when "Only apply resize and conversion to media library and block editor uploads" is checked/enabled, only uploads done directly in the media library or within the context of creating or editing pages and posts will go through conversion (to JPG or WebP). This provides better isolation, so that plugins that incorporates the block editor in creating something, e.g. MailPoet creating newsletter in wp-admin (not frontend form), will no longer have image uploads automatically converting to JPG / WebP, where conversion to WebP may result in blank image in some email clients. Props to Lee B. for prompting this improvement.

* **[FIXED and IMPROVED in Pro] Admin Interface >> Clean Up Admin Bar**: 
  * fixed an issue where admin bar menu item from the Paymattic plugin is not being properly detected and listed for removal. Props to Maik E. for reporting the issue
  * add a button to clear and rescan the list of extra admin bar elements that may have piled up over time and no longer reflects the current state of the admin bar.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fix category filter not working in the snippets listing page. Props to Mirko S. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [38 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free - Updated Spanish (Spain), Portuguese (Brazil), Polish, French, Dutch,.
    * ASE Pro - Updated Polish.

### 8.3.0 (2026.01.26) - ASE Free and Pro

* **[ADDED in Free and Pro] Content Management >> Media Files Visibility Control**: in ASE Free, this module will limit media library in a way that only administrators can see all media files. Non-administrator users will only see media files they uploaded themselves in the media library and in the modal window to add/insert media. ASE Pro adds an option to specify which non-administrator user roles will be limited to only seeing media files they uploaded themselves. 

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**:
  * In the Placement tab, post types now shows the label as well. e.g. 'post' now becomes 'Posts (post)'.
  * In the Placement tab, user roles now shows the label as well. e.g. 'administrator' now becomes 'Administrator (administrator)'.
  * Fixed PHP warnings related to custom field group creation / update process.

* **[IMPROVED in Pro] Utilities >> File Manager**:
  * Add the option to set FM_READ_ONLY constant to true in wp-config.php if you want to activate read-only mode, i.e. only Open and Download actions are available.
  * Add a note in the module to quickly explain about enabling the read-only mode by setting FM_READ_ONLY, DISALLOW_FILE_MODS or DISALLOW_FILE_EDIT to true in wp-config.php.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: 
  * add an "Export / Import Snippets" button in the snippets listing page and an "Export / Import Snippets" submenu item inside the "Code Snippets" admim menu. Both link to the snippets export / import section in the main ASE settings page, at the bottom. Props to Henry R. for prompting this improvement.
  * for CSS snippets, there's a new option to load the snippet within the block editor. It's located under the "Frontend >> Type of page" conditional. Props to Emmanuel A. for prompting this improvement.
  * add a note in the module about manually activating safe mode to regain access to wp-admin when a PHP snippet triggers a pervasive fatal error.

* **[IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: 
  * When all active columns are set to auto width, horizontal scrolling will automatically be disabled in the list table of posts. This ensures that the righ-border-line for the first column will not be displayed in such a scenario.
  * Add an option to make the column header row sticky (on top) on scroll.
  * Add an option to set the number of (first) columns to freeze when horizontal scrolling is enabled.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [38 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free - Updated Vietnamese, Russian, Portuguese (Brazil), Polish, Norwegian, Dutch (Belgian), Dutch, Bengali (Bangladesh)
    * ASE Pro - Indonesian, Vietnamese, Polish, Norwegian, Portuguese (Brazil).

### 8.2.3 (2026.01.19) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Admin Menu Organizer**: added an option to make the "Collapse Menu" toggler sticky at the bottom of the admin menu. Props to [@outdoorsdev1](https://wordpress.org/support/users/outdoorsdev1/) for [suggesting this](https://wordpress.org/support/topic/request-make-expand-collapse-menu-item-in-wordpress-admin-navigation-sticky/) and sharing the code snippets this improvement is based upon.

* **[IMPROVED in Pro] Utilities >> File Manager**:
  * When DISALLOW_FILE_EDIT is set to true, only 'Open' (read-only) and 'Download' actions are available within the plugins and themes folder. When DISALLOW_FILE_MODS is set to true, only 'Open' (read-only) and 'Download' actions are available across all folders and files. Props to Rob M. for prompting this improvement.
  * 'Open' is now properly hidden in the right-click context menu for non-text files.
  * Main folders and files list has been made more compact and styling polished to make it more useful (more items in view) and more comfortable (less busy look).
  * Fix alphabetical sort of sub-folders on clicking a parent folder in the folder tree navigation.
  * Right click to paste into a folder now works properly.

* **[IMPROVED in Pro] Disable Components >> Disable Comments**: when comments are disabled for all post types, the admin bar counter/link and the admin menu item for comments will automatically be removed as well. Props to Kenneth S. and other users for propmpting this imprvement.

* **[IMPROVED in Pro] Content Management >> Content Order**: add an option to show featured image thumbnails in the Order page. Props to Hans R. for suggesting this improvement.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Group**: 
  * added an option to show sequence number for repeater field row label. This is useful for when you have a lot of rows in your repeater field and would like to keep track of the sequence / position of each row. Props to Igor K. for prompting this improvement.
  * fixed PHP fatal error that occurs in a certain scenario. Props to Stefan for reporting the issue with the debug/error log entry.

* **[IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: 
  * Add a new 'Template' column within the 'Default' category for the Pages (page) post type. This will show the template in use by a page, whether in the context of a block theme, or a classic theme. Props to Hans R. for prompting this improvement.
  * Make the first column sticky when horizontal scrolling is enabled.
  * If a plugin registers a sortable column, it is now recognized as a column that can be used as the default sort.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [38 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free - Updated Portuguese (Brazil), Polish, Persian, Danish.
    * ASE Pro - Updated Portuguese (Brazil).

### 8.2.2 (2026.01.12) - ASE Free and Pro

* **[IMPROVED in Pro] Utilities >> File Manager**:
  * 'Download' a file previous would open the file URL in a new browser tab for several file types. It will now properly initiate a download.
  * When a file is selected via it's checkbox, 'Open' and 'Download' buttons will now be shown.
  * Sync the order of actions between the toolbar buttons and right-click context menu.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: when creating a PHP snippet, 'Hook' is now selected by default for "Where to execute / insert".

* **[IMPROVED in Pro] Optimization >> Image Upload Control**: add an option to limit resize and conversion only to media library and block editor uploads. This may be useful for a scenario where a frontend form has an upload field that was resizing and converting image files being uploaded, and that behaviour is not desired. Props to Stijn V. for prompting this improvement.

* **[FIXED and IMPROVED in Pro] Content Management >> Media Categories**: 
  * Fixed layout shift issue in the list view of the media library caused by the categories tree being loaded into view a bit late.
  * After drag-and-drop operation to a category, the page no longer reloads and the counter for the category will be updated live.
  * Category tree state preservation across page reloads is now more robust, making use of browser localStorage.
  * Uploading multiple files to a specific category or sub-category now properly shows a placeholder with progress bar for each file.
  * Categorizing via bulk select and drag-and-dropping selection to a category is now more stable, preserving the media grid in view (with infinite scrolling enabled) and keeping the bulk select mode active. This makes it easier to perform sequential categorization operations. Props to Ramyt I. for prompting this improvement.

* **[FIXED in Pro] Utilities >> Local User Avatar**: fixed a typo in javascript code causing issue with updating another, non-admin user's avatar. Props to RJ Ramey for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [38 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free - Added Azerbaijani, props to [Saeid BGrn](https://profiles.wordpress.org/saeead/), and Dutch (Belgium), props to [Pieterjan D.](https://profiles.wordpress.org/nekojonez/). Updated Spanish (Spain), Portuguese (Brazil), Polish, Persian, Norwegian, Indonesian, German (Formal), Dutch, Arabic.
    * ASE Pro - Updated Indonesian.

### 8.2.1 (2026.01.06) - ASE Free and Pro

* **[IMPROVED in Pro] Admin Interface >> Admin Logo**: admin logo is now loaded in a way that prevents layout shift in the admin menu panel.

* **[IMPROVED in Pro] Custom Content Types, Code Snippets Manager, Form Builder**: top level admin menu icons added by these modules are now rendered with the correct admin color scheme's color on page load, no longer briefly rendered with black color.

* **[IMPROVED in Free and Pro] Admin Interface >> Admin Menu Organizer**: when at least one menu item is hidden until toggled, the "Show Less" menu item is no longer briefly shown on admin page load.

* **[IMPROVED in Free and Pro] Optimization >> Image Upload Control**: prevent an image (JPG, PNG or WebP) with dimension smaller than max width / max height set in the module, from being re-saved, and thus, prevent it from being re-compressed upon upload. Props to Monika W. for prompting this improvement.

* **[FIXED in Pro] Content Management >> Content Order**: fixed a fatal error that occurs in a certain scenario. Props to John S. for reporting the issue in detail, copying the exact error message, which helped with troubleshooting.

### 8.2.0 (2025.01.05) - ASE Free and Pro

* **[ADDED in Pro] Utilities >> File Manager**: A comprehensive file manager for WordPress with folder tree navigation, file and folder operations, and code editing capabilities.

* **[FIXED in Free and Pro] Admin Interface >> Enhance List Tables**: fix an issue where the Title column is no longer sortable when the Last Modified column is shown. Props to [@pierre1wp](https://wordpress.org/support/users/pierre1wp/) for spotting and [reporting](https://wordpress.org/support/topic/ase-disabled-the-ability-to-sort-posts-by-title/) this.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: added an option to use wpLink modal (that allows for manual URL insertion, or search from existing posts) to render the hyperlink field input UI. Props to Mohammed Z. for prompting this improvement.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: 
  * Fixed column for an ASE repeater field showing empty value for time and datetime sub-fields.
  * Fixed PHP deprecation notice that occurs when trying to render repeater sub-fields with `null` value.
  
* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fixed a javascript error that occurs when viewing wp-admin in a language that uses multi-bytes characters, e.g. Japanese. The error prevents the option panel to always hide menu items by user roles to properly show up and function. Props to Taro O. for reporting the issue in detail and with relevant screenshots.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [36 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free - Updated Spanish (Spain), Chinese (Taiwan), Greek.
    * ASE Pro - Completed Greek. Special props for [Stelio C.](https://profiles.wordpress.org/scprojectsgr/)!

### 8.1.4 (2025.12.29) - ASE Free and Pro

* **[IMPROVED in Free and Pro]** Prevent FOUC (Flash of Unstyled Content) in the admin (side) menu when either the Custom Content Types, Admin Menu Organizer or Admin Logo modules is active. Props to Jake H. for prompting this improvement.

* **[FIXED in Free and Pro] Admin Interface >> Wider Admin Menu**: fixed admin menu overlap with several of FluentCart's admin pages. Props to Richard B. for reporting the issue.

* **[FIXED in Pro] Security >> Email Address Obfuscator**: prevent removal of strings in post content that looks like an email address but is not, e.g. some-@-image.jpg, important@document.pdf, etc.. Props to Joe S. for prompting this fix.

* **[FIXED in Free and Pro] Utilities >> Multiple User Roles**: fix PHP warning when an admin is viewing another user's profile screen. Props to Bas B. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [36 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free - Updated Persian, Romanian, Hungarian, Turkish, Portuguese (Brazil), Polish, Japanese
    * ASE Pro - Added Persion (partial). Updated Catalan, Greek, Hungarian.

### 8.1.3 (2025.12.22) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Disable Components >> Disable Gutenberg**: fixed an issue where 'global-styles-inline-css' internal CSS is still being output despite checking "Also disable frontend block styles / CSS files for the selected post types.". Props to Mike D. for reporting the issue, facilitating the troubleshooting process, providing analysis of why it occurs in certain scenarios (e.g. Bricks builder), and proposing the fix for it!

* **[FIXED in Free and Pro] Content Management >> External Permalinks**: Fixed a PHP warning. Props to Tanja K. for reporting it with the debug log entry.

* **[FIXED in Free and Pro] Log In/Out | Register >> Redirect After Login / Logout**: fixed an issue where if the target has query parameters, e.g. /account/?tab=profile, the redirect will append a slash at the end, e.g. /account/?tab=profile/, which might break the functionality dependent on the query parameter value. Props to [@robo1999](https://wordpress.org/support/users/robo1999/) for [reporting this](https://wordpress.org/support/topic/redirect-on-login-trailing-slash-problem/).

* **[FIXED in Free and Pro] Admin Interface >> Enhance List Tables >> Last Modified Column**: make sure the date time is using the timezone set in "Settings >> General". Props to [Mikka](https://wordpress.org/support/users/miikkamakela/) for [suggesting](https://wordpress.org/support/topic/timezone-for-last-modified/) this improvement.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [36 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Danish, Polish, Portuguese (Brazil)
    * ASE Pro: updated Greek

### 8.1.2 (2025.12.15) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Hide Admin Notices**: notices on Meta Box plugin's admin screens are now properly hidden in the notices panel.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**:
  * Public-facing snippet category archive pages have been disabled as they do not serve any purpose. Props to Martin S. for prompting this improvement.
  * XML sitemap for the snippet category archive pages have been removed as they do not serve any purpose. Props to Robert F. for prompting this improvement.
  * Added an option to disable the Code Snippets Editor custom user role. Props to David M. and Martin S. for prompting this improvement.
  
* **[IMPROVED in Pro] Security >> Email Address Obfuscator**: add a new 'mailto' option for the 'link' parameter, that will output the plain `mailto:obfuscated@email.address` string, which may be useful for scnearios where you need this added to a block editor or page builder widget/element/component that accepts a shortcode for the URL. Props to Ramyt I. for prompting this improvement.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [36 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Swedish, Portuguese (Brazil), Polish, Dutch, Norwegian, Korean, Italian, German (Formal)
    * ASE Pro: updated Chinese (China), Portuguese (Brazil), Norwegian, Hungarian, Greek

### 8.1.1 (2025.12.08) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Utilities >> Password Protection**: added a note about caching being disabled when this module is enabled. Props to Ralf L. for prompting this improvement.

* **[IMPROVED and FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: 
  * the relationship field is now supported in Elementor integration, in a similar way repeater field is already integrated. You can use the Loop Grid widget to select the relationship field as data source, and a Loot Item template to display dynamic data, including ASE custom fields, for each related post. Props to Ian B. for prompting this improvement.
  * fixed an issue where taxonomy term custom fields are not displaying their values in Bricks builder Query Loop (for terms). Props to Michael S. for reporting the issue in great detail (also with a screen recording) and facilitating the troubleshooting process.

* **[FIXED in Free and Pro] ASE Settings**: in a rare scenario, ASE settings page is not properly loaded because of a Javascript error. This fix adds a simple check to prevent the error from happening. Props to Connor B. for reporting this and facilitating the troubleshooting process.

* **[FIXED in Free and Pro] Utilities >> Email Delivery**: fixed an issue with [changes in WP v6.9](https://make.wordpress.org/core/2025/11/18/more-reliable-email-in-wordpress-6-9/) to ensure the `Envelope-From`, `Return-Path` is properly set via `$phpmailer->Sender` and `$phpmailer->ReturnPath`. Props to Pavlov P. for reporting the issue and doing the research towards the fix, and @denisgomesfranco for [reporting the issue](https://wordpress.org/support/topic/wordpress-6-9-changed-things-on-wp_mail-and-mail-delivery-is-causing-errors/) separately.

* **[FIXED in Pro] Utilities >> Redirect Manager**: fixed a bug where redirecting to an external URL, e.g. https://www.google.com, is not working. Props to Mathijs v.d.B. for reporting the issue in detail and helping to test the fix.

* **[FIXED in Pro] Utilities >> Form Builder**: 
  * fixed raw HTMl being shown in entry listing's "Preview" column, in entry view screen's field labels, and in "Form Settings >> Entries >> Content for the Preview Column" dropdown options.
  * fixed "Screen Options >> Pagination" not working properly in the forms listing page.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [36 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Spanish (Spain), Spanish (Chile), Portuguese (Brazil), Polish, Norwegian, Korean, Italian, Dutch, Chinese (China)
    * ASE Pro: updated Greek, Chinese (China), Italian, Polish, Portuguese (Brazil)

### 8.1.0 (2025.12.01) - ASE Free and Pro

* **[ADDED in Pro] Utilities >> Redirect Manager**: Easily manage various types of redirects with support for wildcards and regular expressions (regex).

* **[SECURITY FIX in Free and Pro] Content Management >> Media Replacement**: fix for broken access control vulnerability that allows an authenticated user with author role to delete arbitrary media files owned by any user, including administrators. Props to @daroo, a security researcher responsibly disclosing this via Pathstack.

* **[IMPROVED in Free and Pro] Content Management >> Media Replacement**: non-image attachments that's recently been replaced will now have timestamps added in the URL for certain scenarios. Props to Simone S. for prompting this improvement.

* **[IMPROVED in Pro] Utilities >> Form Builder**: HTML are now allowed in field labels and descriptions, as well as in radio / checkbox / image select fields labels/options and matrix fields row/column labels. This can be handy for example, when adding the link tag &lt;a&gt; to your privacy policy page in a checkbox label. Props to Peter D. and Oliver S. for prompting this improvement.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [36 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Portuguese (Brazil), Polish, Norwegian, Korean, Dutch, Danish.
    * ASE Pro: updated Greek, Danish, Polish.

### 8.0.8 (2025.11.24) - ASE Free and Pro

* **[FIXED in Free and Pro] Admin Interface >> Admin Menu Organizer**: fixed an issue where backslash were added before a single quote mark when it's present in a menu title. This occurs upon saving changes. Props to Stéphane N. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Utilities >> Email Delivery**: fixed missing slash (/) in email log's attachment link, between the site domain and the 'wp-content' string, e.g. https://wwww.domain.domwp-content/uploads/the-attachment-file.pdf. Props to Florian D. for reporting the issue with a clearly annotated screenshot.

* **[IMPROVED in Pro] Utilities >> Form Builder**: 
  * Improve compatibility with WordPress installation using SQLite, e.g. WordPress Studio local installation.
  * "Screen Options" is now hidden when viewing a single form entry.
  * Upgrade the "Form Builder" block from API v1 to v3. Props to Fahd M. for prompting this improvement.
  * Change "Form Builder" block to use the same icon as the "Forms" admin menu.
  
* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: add an option to select which snippets to export: all snippets / only active snippets / by snippets type / by categories / manual selection. Please go to the main ASE settings page and click "Export | Import" link in the bottom-right part of the page to see the changes. Props to Jim R. for prompting this improvement.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [36 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: ASE Free: updated Turkish, Spanish, Portuguese (Brazil), Polish, Norwegian, Indonesian, Dutch, Bengali (Bangladesh), Albanian.

### 8.0.7 (2025.11.17) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Hide Admin Notices**: notice from the Shortpixel plugin is now properly hidden as well.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed a fatal error that occurs in a certain scenario. Props to Jannick N. for reporting the issue complete with the error stack trace and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed an issue where the 'Instructions' field is not shown immediately when adding a new field in a custom field group. Props to Niels S. for investigating and reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [36 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Portuguese (Portugal), Portuguese (Brazil), Polish, Norwegian, French, Dutch.
    * ASE Pro: update Greek, Hungarian.

### 8.0.6 (2025.11.10) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Change Login URL**: login attempts from Paid Membership Pro's login form will now, always be allowed, whether the form is embedded in a page, or in a pop-up / modal window. Props to Bent F. for prompting this improvement.

* **[IMPROVED in Pro] Admin Interface >> Hide Admin Notices**: add the option to also hide the 'Notices' menu in the admin bar for non-administrators. This will effectively hide all notices for non-admins. Props to Theodore for prompting this improvement.

* **[IMPROVED in Pro] Content Management >> Content Order**:
  * The "Hide child posts" checkbox will now only show up on hierarchical post types order pages
  * When "Hide child posts" is checked, the "Has child posts" status will be shown next to the title of parent posts

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [35 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Turkish, Spanish, German (Formal), Portuguese (Brazil), Polish, Norwegian, Dutch.
    * ASE Pro: Updated Portuguese (Brazil), Norwegian, Danish.

### 8.0.5 (2025.11.03) - ASE Free and Pro

* **[FIXED in Free and Pro] Content Management >> Media Replacement**: fixed a fatal error that occurs in a certain scenario. Props to [@zadine](https://wordpress.org/support/users/zadine/) for [reporting the issue](https://wordpress.org/support/topic/fatal-error-4934/) complete with the debug.log entry details, including the stack trace.

* **[FIXED in Pro] Admin Interface >> Clean Up Admin Bar**: fixed an issue with Fluent Forms admin bar menu item not being hideable, i.e. not available as a choice to be hidden. Props to Frank K. for reporting the issue.

* **[FIXED in Pro] Security >> CAPTCHA Protection**: fixed a PHP warning that occurs in a certain scenario. Props to @betaplus for reporting the issue with the error/debug log entry.

* **[FIXED in Pro] Content Management >> Media Categories**: Fixed a fatal error that occurs in a certain scenario when the [gallery] shortcode is in use (by a user, or via another plugin). Also fixed a related PHP warning. Props to Rauf K. for reporting the issue and suggesting the code fix!

* **[IMPROVED in Pro] Content Management >> Content Order**: Added the ability to reorder and change the parent of child posts. Props to Hui C., Kees, Ole, Marco M. and Ian H. for prompting this improvement.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [35 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Ukrainian, Portuguese (Brazil), Polish, Norwegian, Korean, Japanese, French, Dutch, Danish, Chinese (Taiwan), Arabic
    * ASE Pro: Updated Danish, Hungarian, Portuguese (Brazil)

### 8.0.4 (2025.10.27) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Utilities >> Multiple User Roles**: the 'Administrator' role checkbox will now be disabled to prevent accidentally removing this role and cause the user to lose the admin role without an easy way to restore it. Props to [Edward B.](https://wordpress.org/support/users/edviser/) for [prompting](https://wordpress.org/support/topic/how-to-regain-admin-access-when-misusing-multiple-userroles-feature/) this improvement.

* **[FIXED in Free and Pro] Disable Components >> Disable REST API**: 
  * ASE Free and Pro: fixed an issue where The Events Calendar ajax-based navigation of the frontend calendar broke, because it's REST API route is disabled. The route has been automatically excluded from being disabled, i.e. automatically whitelisted. Props to Michael D. for reporting the issue.
  * ASE Pro: fixed an issue with how excluded routes are not being properly handled and excluded due to a flawed logic in the code. Props to Eric C. for reporting the issue and suggesting a code fix for it.
  
* **[IMPROVED in Pro] Utilities >> Form Builder**: added feature to export and import forms. This is done via the 'Settinsg' tab. Once exported, on the target site, create a blank form and go to the 'Settings' tab to import the form. Props to Marcellus J. Hoang H., netcit, Bo M. and John E. for suggesting this improvement.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [35 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Slovak, Portuguese (Brazil), Polish, Japanese, Dutch, Danish, Chinese (Taiwan), Arabic.
    * ASE Pro: Updated Portuguese (Brazil), Hungarian. 

### 8.0.3 (2025.10.20) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Utilities >> View Admin as Role**: add an additional note to create a backup admin user before using this module. This backup admin user is useful when for whatever reason and in very rare occasions, the primary admin user is not able to return / login as an admin.

* **[FIXED in Pro] Disable Components >> Disable REST API**: fixed an issue where the /wp-json route is still publicly accessible. Props to Pavan K. for reporting the issue.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: 
  * fix the link for "Manage Columns" button in the "All Posts" listing page.
  * fix PHP warning that occurs in a certain scenario

* **[FIXED in Pro] Utilities >> Display System Summary**: additional fix for a fatal error that occurs in a certain scenario when trying to calculate the size of a folder/file in a must-use plugin. Props to someone from creatives.hu for reporting this related, follow up issue and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [35 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Spanish (Spain), Spanish (Chile), Portuguese (Brazil), Polish, Norwegian, Dutch, Chinese (Taiwan)
    * ASE Pro: Added Bulgarian (partial).

### 8.0.2 (2025.10.13) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Disable Components >> Disable Comments**: only post types that has support for comments will be listed as options now. Props to [DJABHipHop](https://wordpress.org/support/users/pressthemes1/) for [prompting](https://wordpress.org/support/topic/suggestion-for-the-disable-comments-feature/) this improvement.

* **[IMPROVED in Pro] Disable Components >> Disable REST API**: improved the logic to always enable certain endpoints, e.g. /wp-json/wc/store/v1/cart, especially for authenticated user roles which have not been explicitly been allowed access in the module settings. Special props to Eric C. for spotting this unobvious oversight, reporting it in detail and with clarity, and on top of that, providing the code fix.

* **[FIXED in Pro] Utilities >> Display System Summary**: fixed a fatal error that occurs in a certain scenario when trying to calculate the size of a folder/file in a must-use plugin. Props to someone from creatives.hu for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fixed an issue where the AMO settings page is not working properly when a certain custom menu item is manually added via a PHP code snippet. Props to Theodore for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [35 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Portuguese (Brazil), Polish, Norwegian, Korean, Japanese, German, Danish.
    * ASE Pro: Updated Danish, Norwegian, Hungarian, Portuguese (Brazil).

### 8.0.1 (2025.10.06) - ASE Free and Pro

* **[FIXED in Free and Pro] Content Management >> Content Duplication**: 
  * fixed a fatal error that occurs in a specific scenario. Props to [@afrigols](https://wordpress.org/support/users/afrigols/) for [reporting](https://wordpress.org/support/topic/php-fatal-error-in-the-latest-plugin-update-v8-0-0/) the issue.
  * improved fix of missing admin bar duplication link in the frontend view of posts.

* **[IMPROVED and FIXED in Pro] Admin Interface >> Admin Menu Organizer**: 
  * improved reset logic to also work when no settings has been saved.
  * prevent several of [MainWP Dashboard](https://wordpress.org/plugins/mainwp/) and [Smash Balloon Social Post Feed](https://wordpress.org/plugins/custom-facebook-feed/) submenu items from showing up blank in the AMO settings page.
  * fixed an edge case where hidden menu item toggle (Show All/Less) was not shown in a rare scenario for users with multiple roles.

* **[FIXED in Pro] Security >> CAPTCHA Protection**: fixed a CSS issue with ALTCHA, where the checkbox label turned white and unreadable. Props to Shawn B. for reporting the issue.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: 
  * fixed a fatal error when `update_cf()` is invoked. Props to Pierre A. for reporting the issue.
  * fixed PHP warning when rendering relationship field in a certain scenario in Bricks. Props to Tadea T. for reporting the issue with the relevant error/debug log entry.

* **[FIXED in Pro] Utilities >> Form Builder**: fixed a typo in the UI (English). Props to Morten E.L. for catching and reporting it.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [35 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Added Bulgarian. Updated Russian, Portuguese (Portugal), Portuguese (Brazil), Polish, Norwegian, French, Dutch, Danish, Bengali (Bangladesh), Arabic.
    * ASE Pro: Updated Danish.

### 8.0.0 (2025.09.29) - ASE Free and Pro

* **[ADDED in Free and Pro] Disable Components >> Disable Embeds**: Prevent your site content from being embedded on other sites. Prevent embedding of non-whitelisted sites in your site. Disable all, related JavaScript. Removes support for the embed block.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fixed PHP warning that occurs in a certain scenario. Props to Andrew A. for reporting the issue and suggesting the fix.

* **[FIXED in Pro] Content Management >> Content Duplication**: fixed a bug where the duplication link is not properly showing up in the admin bar on the frontend when viewing single pages / posts /CPT post, and when viewing the homepage that uses a specific page. Props to Lee B. for prompting this fix.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [34 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Serbian, Portuguese (Brazil), Polish, Norwegian, Korean, Japanese, Danish.

### 7.9.11 (2025.09.22) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Content Management >> Content Duplication**: re-enable duplication of Elementor templates and landing pages as data for both post types are confirmed to be stored only in the default wp_posts and wp_postmeta tables. Props to Marcel N. and Celeste W.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed sorting based on the "Modified" date time / column not properly working in the snippets listing page. Props to Oliver S. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed a bug where update_cf() doesn't work when a field in a custom field group has an empty 'Name'. Props to @compwj for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [34 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Spanish (Chile), Portuguese (Brazil), Polish, Norwegian, German (Formal), Danish.
    * ASE Pro: updated Arabic.

### 7.9.10 (2025.09.15) - ASE Free and Pro

* **[FIXED in Free and Pro] Admin Interface >> Wider Admin Menu**: fixed the admin menu overlapping Formidable Form plugin's admin pages. Props to Stijn V. for reporting the issue.

* **[IMPROVED in Pro] Utilities >> Form Builder**: matrix fields are now responsive (mobile-friendly). Props to Uli L. for prompting this improvement.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fix an issue where the "is not / not any of" operator in an AND/OR conditional reverts back to "is / one of" upon saving. Props to Dexx W. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [34 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Portuguese (Brazil), Polish, Norwegian, German, Danish.
    * ASE Pro: updated German.

### 7.9.9 (2025.09.08) - ASE Free and Pro

* **[FIXED in Free and Pro] Disable Components >> Disable Smaller Components >> Disable shortlink tag in head**: fixed priority for `remove_action()` for the `template_redirect` hook, so it is properly removed. Props to [@robertmichalski](https://wordpress.org/support/users/robertmichalski/) for [reporting the issue](https://wordpress.org/support/topic/removing-shortlink-header-fails/) and providing the fix.

* **[FIXED in Pro] Admin Interface >> Show Custom Taxonomy Filters**: fixed an issue where frontend 404 page is not properly being displayed when additional filters for non-hierarchical taxonomies are set to be shown and the permalink structure is set to /%category%/%postname%/. Props to John C. for reporting the issue and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [34 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Spanish (Spain), Portuguese (Brazil), Polish, Norwegian, Danish, Arabic

### 7.9.8 (2025.09.01) - ASE Free and Pro

* **[SECURITY FIX in Free and Pro] Content Management >> SVG Upload**: fix Stored XSS vulnerability when a user with media upload privilege uploads a malicioius SVG via xmlrpc.php. Props to Willow, a security researcher from WPScan and Jetpack for responsibly disclosing the vulnerability.

* **[IMPROVED in Free and Pro] Log In/Out and Register >> Change Login URL**: legitimate cURL requests to /wp-admin/admin-post.php is now allowed when Change Login URL is enabled. This is sometimes needed to utilize the admin_post_nopriv_action hook. Props to Maxime for prompting this improvement.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [34 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Portuguese (Brazil), Polish, Norwegian, German (Formal), French, Danish

### 7.9.7 (2025.08.25) - ASE Free and Pro

* **[FIXED in Free and Pro] Admin Interface >> Admin Menu Organizer**: fixed PHP notice "\_load_textdomain_just_in_time was called incorrectly". Props to Matteo for reporting the issue.

* **[FIXED in Pro] Content Management >> Custom Content Types**: fixed PHP warning and error that occurs in a certain scenario when viewing the Custom Post Types listing page. Props to Kol N. for reporting the issue and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [34 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Vietnamese, Slovak, Portuguese (Brazil), Polish, Norwegian, Dutch, Danish.
    * ASE Pro: Updated Hungarian.

### 7.9.6 (2025.08.18) - ASE Free and Pro

* **[FIXED in Free and Pro] Admin Interface >> Admin Menu Organizer**: fix PHP warnings that occurs when using "wp list post" command via WP CLI. Props to [Apostolos P.T.](https://wordpress.org/support/users/monotropos/) for [reporting](https://wordpress.org/support/topic/2-warnings-when-getting-page-from-cli/) this issue.

* **[CHANGED in Pro] Custom Code >> Code Snippets Manager**: The snippet description field/editor is now placed below the code editor, so you can immediately work on the code upon page load / saving changes. This also fixes an issue where the snippet description field very occasionally shows a blank white box rendering it unusable. Props to Kilian H. for prompting this change.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [34 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Russian, Portuguese (Brazil), Polish, Norwegian, Japanese, German (formal), Dutch, Bengali.

### 7.9.5 (2025.08.11) - ASE Free and Pro

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: fixed notices still showing in Dokan plugin's dashboard. Props to [@kam420](https://wordpress.org/support/users/kam420/) for [reporting this](https://wordpress.org/support/topic/admin-notice-is-working-yet-after-hiding-from-ase-setting/) with screenshots.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: 
  * **fixed PHP fatal error** that occurs in a certain scenario involving newly added separators. Props to Christian S. for reporting the issue by including the error log entry and facilitating the troubleshooting process.
  * **fixed PHP deprecation notices** related to hiding menu and sub-menu items until toggled. Props to Christian S. for reporting the issue by including the error log entry.

* **[IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: make the featured image column semi-sortable. When sorted ascendingly, only posts with a featured image will be shown. When sorting descendingly, only posts without a featured image will be shown. This should be useful enough to get a list of posts with no featured image set yet. Props to Robert H. for prompting this improvement.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [34 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Turkish, Spanish, Russian, Romanian, Portuguese (Brazil), Polish, Norwegian, Italian, Indonesian, German (Formal), Dutch.
    * ASE Pro: Updated Portuguese (Brazil).

### 7.9.4 (2025.08.04) - ASE Free and Pro

* **[FIXED in Free] Utilities >> Maintenance Mode**: fixed PHP warning as [reported](https://wordpress.org/support/topic/undefined-variable-custom_head_code-in/) by [@jacekjacek](https://wordpress.org/support/users/jacekjacek/).

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed an issue where in certain scenarios the Elementor repeater field integration is not shwoing the repeater sub-fields in the loop item template. Props to Muhammad Q. for reporting the issue in detail and facilitating the troubleshooting process.

* **[IMPROVED in Free and Pro] Disable Components >> Disable Gutenberg**: 
  * automatically exclude post types that are not exposed in the REST API from the module settings, as these post types would not have the block editor enabled.
  * remove inapplicatble post types registered by WordPress, ASE, ACF, Elementor, Breakdance, Oxygen Classic and Bricks from module settings.

* **[IMPROVED in Free and Pro] Log In/Out | Register >> Log In/Out Menu**: 
  * Free and Pro: strings in this module are now internationalized, i.e. ready for translations. Props to Markus F. for prompting this improvement.
  * Pro: added options to set custom "Log In" and "Log Out" menu labels. Props to Markus F. for prompting this improvement.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [34 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Added Bengali (Bangladesh, completed) with special props to [Ruhani R.](https://profiles.wordpress.org/ruhanirabin/) for speedily completing this translation. Updated German, Polish and Portuguese (Brazil).
    * ASE Pro: Added Bengali (Bangladesh, partial). Updated Spanish and Japanese.

### 7.9.3 (2025.07.28) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Disable Components >> Disable Gutenberg**: improve code to remove frontend Gutenbeg assets to account for edge cases. Props to Mike D. for prompting the improvement.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: added shortcode sub-type for text field and implement shortcode output for this sub-type in Oxygen, Breakdance and Elementor. Props to Radoslaw B. for prompting this improvement.

* **[FIXED in Pro] Utilities >> Form Builder**: fixed a PHP warning error related to the display of name field in the entries list page. Props to Radoslaw B. for reporting the issue in detail, including the error log entry and a screenshot.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [33 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Slovak, Portuguese (Brazil), Polish, Japanese, 
    * ASE Pro: updated German and Japanese.

### 7.9.2 (2025.07.21) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Change Login URL**: when in Settings >> General, "Membership: Anyone can register" is checked, opening /wp-signup.php will no longer redirect to the registration form. The custom login slug is required in the URL, i.e. /wp-signup.php?custom_login_slug, to be able to access the registration form.

* **[IMPROVED in Pro] Utilities >> Form Builder**: Improved accessibility of the file upload field. Make sure all input elements in this field have corresponding labels. Props to Claudio P. for prompting this improvement.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fixed an issue where the 'Hide' options were not being properly shown in full or were toggleable correctly when wp-admin is set to display in non-English language and the Independent Analytics plugin is active. Props to François A. for reporting the issue in detail and facilitating the troubleshooting process.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed an issue where on importing PHP snippets, backslashes were unnecessarily removed, e.g. from regex patterns, resulting in broken, non-working snippets. Props to Oliver Z. for reporting the issue in detail and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [33 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Portuguese (Brazil), Chinese (China), Catalan.
    * ASE Pro: Completed Spanish (100%). Special props to Lluis H. for a great effort in completing the translation.
    
### 7.9.1 (2025.07.14) - ASE Free and Pro

* **[IMPROVED in Pro] Content Management >> Content Order**: the "show excerpt" toggle will now use an actual excerpt if already set, instead of generating one from the post content. Props to @ricjoh for prompting this improvement.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed a PHP warning causing internal server (500) error in Breakdance builder. Props to Henry R. for reporting the issue with the error log entry.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [33 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Spanish, Russian, Portuguese (Brazil), Polish, Norwegian, Italian, Indonesian, French, Dutch, Danish.
    * ASE Pro: updated Norwegian and Spanish.

### 7.9.0 (2025.07.07) - ASE Free and Pro

* **[ADDED in Free and Pro] Disable Components >> Disable Author Archvies**: Return 404 (Not Found) error when trying to load author archives. Remove or disable links to author archives. Remove author archives from the sitemap.

* **[IMPROVED in Pro] Content Management >> Media Categories**: add an option to set the width of the categories panel, or to hide it. Props to Darren L. and Satoshi F. for prompting this improvement.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed an issue with ASE Repeater only showing the first 10 rows of data in Elementor Loop Grid. Props to Pierre A. for reporting the issue and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [33 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Vietnamese, Russian, Portuguese (Brazil), Polish, German, Danish, Catalan
    * ASE Pro: updated Spanish.

### 7.8.18 (2025.06.30) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Custom Code >> Insert &lt;head&gt;, &lt;body&gt; and &lt;footer&gt; Code**: add an option to disable removal of backslashes from frontend code output. This would be useful if your code contains, for example, some sort of regex pattern. Props to [@rjwilt](https://wordpress.org/support/users/rjwilt/) for [prompting](https://wordpress.org/support/topic/custom-code-backslash-in-javascript-code-removed/) this improvement.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: when a PHP fatal error occur, the module will now catch more type of errors and properly record it in the PHP snippet edit screen. Props to Jim R. for reporting a related issue in great detail and facilitating the troubleshooting process.

* **[IMPROVED in Pro] Content Management >> Content Order**: the 'Order' button on posts lists table will now be visible for editors as well. Props to Markus R. for prompting this improvement.

* **[IMPROVED in Pro] Optimizations >> Image Upload Control**: add an option to disable conversion to JPG / WebP, but still allow to resize image dimension and disable generation of intermediary sizes. Props to Himanshu A. for prompting this improvement.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: 
  * **Fixed the rendering of date, time and datetime fields when the admin is in a non-English language**. This sometimes resulted in a date or time column returning empty values. Props to Killian H. for reporting the issue and facilitating the troubleshooting process.
  * **Fixed a JS error** that occurs when a custom field column's title is a number. This prevented the admin columns manager screen from being usable / functional, e.g. when managing the Courses post type (WPLMS) admin columns screen. Props to Stefanie N. for reporting this issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: link button in the WYSIWYG field inside a CFG placed on taxonomy terms will now work properly.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [33 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: ASE Free: update Portuguese (Brazil), Polish, Norwegian, Japanese, Indonesian, French, Arabic
    * ASE Pro: update Spanish (Spain), Hungarian

### 7.8.17 (2025.06.23) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Change Login URL**: make login failure process compatible with Bedrock installation where WP core is installed in a sub-directory (/wp/). Props to [@shadyshane](https://wordpress.org/support/users/shadyshane/) for [prompting](https://wordpress.org/support/topic/wp-bedrock-support-for-custom-login-url/) this improvement.

* **[FIXED in Pro] Utilities >> Form Builder**:
  * Fixed an issue where if checkbox and radio fields are marked as required in sites still on PHP 7.4, the form will fail submitting even if the checkbox and/or radio fields have been checked / selected. Props to Pierro B. and Leon S. for reporting the issue in detail, and especially for Leon who discovered that it only happened on sites still on PHP 7.4.
  * Fixed an issue where if choice and matrix fields labels contain an apostrophe, e.g. I'm satisfied with the customer service, the submission data gets malformed.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [33 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Vietnamese, Portuguese (Brazil), Polish, Norwegian, Japanese, Indonesian, Dutch.
    * ASE Pro: updated Hungarian, Vietnamese.

### 7.8.16 (2025.06.16) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Content Management >> Media Replacement**: added an option to disable adding timestamp URL parameter for newly replaced media item, for busting browser cache. If you disable it, you'll need to manually do a force reload of the browser tab to see the new image. Props to [@alriksson](https://wordpress.org/support/users/alriksson/) and [@antoinelrx](https://wordpress.org/support/users/antoinelrx/) for prompting this improvement [here](https://wordpress.org/support/topic/media-replacement-query-string/) and [here](https://wordpress.org/support/topic/media-replace-issue-url-parameter/).

* **[IMPROVED in Free and Pro] Log In/Out & Registration >> Redirect After Login/Logout**: will now work with redirection to a URL that contains an anchor (#anchor). Props to Uli L. for prompting this improvement.

* **[IMPROVED in Pro] Admin Interface >> Admin Logo**: the admin bar logo will now be shown in mobile view/screen. Props to Daniel A. for prompting this improvement.

* **[FIXED in Pro] Content Management >> Custom Content Types**: 
  * in Elementer integration for ASE repeater, when configuring the Loop Grid widget to query repeater field data, there is now an **option to "Query Current Post Only"** which will ensure only reepater data from the current post in view will be shown. Props to Pierre A. for reporting the issue and facilitating the troubleshooting process.
  * fix for when custom fields that are placed on posts **no longer display their value on taxonomy term archive** since v7.8.12. Props to Mirko S. for reporting the issue with a screencast to illustrate the issue in detail and facilitating the troubleshooting process.
  * **fix PHP E_ERROR** that occurs in a certain scenario. Props to Wilbert S. for reporting the issue along with the error stack trace.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [33 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: jpdated Vietnamese, Spanish (Spain), Slovak, Russian, Portuguese (Brazil), Polish, Norwegian, Korean, Indonesian, Dutch, Danish
    * ASE Pro: updated Spanish, Hungarian, Vietnamese

### 7.8.15 (2025.06.10) - ASE Free and Pro

* **[IMPROVED and FIXED in Free and Pro] Log In/Out & Register >> Change Login URL**: 
  * add an **option to allow login from additional login URLs**, e.g. created by e-commerce plugins or using page builders.
  * fixed an issue where **password-protected pages/posts gets redirected to /not_found/** upon entering the correct password. Props to Emile V., @jeromebxl and Emmanuel A. for [reporting the issue](https://wordpress.org/support/topic/problem-with-a-password-protected-page/).

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: non-administrator will no longer see the "Code Snippets" menu item in the admin (side) menu. Props to Harry F. for prompting this improvement.

### 7.8.14 (2025.06.09) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Change Login URL**: will now also check if the login request is coming from cURL and properly handles it. Props to Emmanuel A. for prompting this improvement.

* **[FIXED in Free and Pro] Content Management >> SVG Upload**: updated SVG sanitizer library to the latest version to resolve PHP 8.4 deprecation issue as reported by Keith H.

* **[FIXED in Pro] Content Management >> Media Categories**: 
  * fixed a JS error that occurs in post edit screen when the Meta Box plugin is active and shoing custom fields meta boxes. Props to Tanja K. for reporting the issue in detail and facilitating the troubleshooting process.
  * uploading multiple files in the root of the media library grid view will now show the progress indicator again. Props to admin_t for prompting this fix.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed PHP deprecation warning in sites running PHP 8.4. Props to Keith H. for reporting the issue.

* **[FIXED in Pro] Content Management >> Media Replacement**: fixed an issue where uploading a new image during the replacement process would then reload the page taking you out of the replacement flow. Props to John B. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [33 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Turkish, Portuguese (Brazil), Polish, Norwegian, Dutch, Chinese (Taiwan).
    * ASE Pro: updated Spanish (Spain), Norwegian, Catalan. Special props to [@lluishospital](https://profiles.wordpress.org/lluishospital/) for completing the Catalan translation!

### 7.8.13 (2025.06.02) - ASE Free and Pro

* **[SECURITY FIX in Free and Pro] Custom Code >> Insert &lt;head&gt;, &lt;body&gt; and &lt;footer&gt; Code** fixed Stored XSS vulnerability responsibly disclosed by Dmitrii Ignatyev from CleanTalk Inc.

* **[IMPROVED in Pro] Utilities >> Form Builder**: the associated attachment ID will now be removed when the email header image URL is deleted. This will ensure the header image will no longer be part of the email sent out from the form builder. Props to Lucas C. for reporting the issue in great detail.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: 
  * **Added a mechanism to ensure that PHP snippets will have the opening <?php tag** upon publishing or updating a PHP snippet. Props to Jim W. for prompting this improvement.
  * **Permanently deleted snippet will now have the snippet file deleted as well**. Props to Jose K.N. for prompting this improvement.

* **[FIXED in Pro] Security >> Email Address Obfuscator**: fixed an issue where the obfuscation shortcode is showing instead of the obfuscated email address when an Elementor Icon List widget contains an email address text. Props to Wence W. for reporting the issue in detail and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Media Categories**: fixed an issue where only the first image is being uploaded when trying to upload several images into a category in the grid view. Props to Sanjeev S. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [33 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Vietnamese, Slovak, Portuguese (Brazil), Polish, Norwegian, Indonesian, German, Dutch, Catalan, Arabic, Albanian.
    * ASE Pro: Added Catalan. Special props to [@lluishospital](https://profiles.wordpress.org/lluishospital/) for pushing ahead with the translation!

### 7.8.12 (2025.05.26) - ASE Free and Pro

* **[IMPROVED in Pro] Content Management >> Custom Content Types**:
  * **Implement custom field groups for taxonomy terms**. You can now attach custom fields directly on taxonomy terms. This is also integrated with Oxygen Classic, Bricks, Breakdance and Elementor when you create templates for the taxonomy (term) archive. Props to Hadar B., Yannick, Gabriel, Ayman A., Aleš S., Zubair, Ivar S., Ahmed E., Henry R. for prompting this improvement and help with early testing.
  * **Added warning message** in the custom field groups listing when no placement rules have been set for a custom field group.

* **[IMPROVED in Free and Pro] Admin Interface >> Hide Admin Bar**: add an option to always show the admin bar in the frontend. This is useful for when an admin has multiple roles and the admin bar is set to be hidden for those other role(s). Props to [@croberta84](https://wordpress.org/support/users/croberta84/) for [prompting](https://wordpress.org/support/topic/multiple-role-admin-bar-issue/) this improvement.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [33 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Added Catalan. Updated Ukrainian, Spanish (Spain), Slovak, Portuguese (Brazil), Polish, Norwegian, Japanese and Indonesian.

### 7.8.11 (2025.05.19) - ASE Free and Pro

* **[SECURITY FIX in Free] ASE Settings**: fix CSRF vulnerability responsibly disclosed by Dmitriy Ignatyev, a penetration tester from CleanTalk Inc. This has to do with several AJAX requests related to support/upgrade nudges.

* **[IMPROVED in Pro] Utilities >> Form Builder**: 
  * **Added an option to send form submission data to webhooks**. Props to Niesl S., Mathijs v.d.B., Eric L. and Jakub X. for suggesting this feature.
  * **Improved accessibility of hidden field labels** by modifying the CSS to no longer use `display: none`. Props to Jakub X. for suggesting the improvement.
  * **Improved accessibility** by properly adding hidden labels and the relevant `for` attributes for name, address, likert/matrix scale, matrix of uniform dropdowns and matrix of variable dropdowns fields. Props to Jakub X. for propmpting this improvement.

* **[IMPROVED in Pro] Content Management >> Custom Content Types**: improved description for the Menu Title field in Options Page creation / edit screen. Props to Jim R. for prompting this improvement. 

* **[FIXED in Pro] Content Management >> Media Replacmeent**: fixed an issue where the media replacement mechanism is broken in the grid view of the media library after v7.8.7 release. Props to Matthias S. for reporting the issue.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fixed a PHP warning when the Featured Image columns is being displayed. Props to Ian F. for reporting the issue.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed a PHP warning that occurs in a certain scenario. Props to Dominik K. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [32 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Vietnamese, Spanish, Slovak, Russian, Portuguese (Brazil), Polish, Norwegian, Indonesian, German, Dutch, Danish, Chinese (Taiwan), French.
    * ASE Pro: updated Hungarian.

### 7.8.10.1 (2025.05.12) - ASE Pro

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed PHP fatal error on sites with PHP 7.4 that occurs since the addition of the PHP MobileDetect library in v7.8.10. Props to Pierro B., Lee H. and Courtney V. for immediately eporting the issue.

### 7.8.10 (2025.05.12) - ASE Free and Pro

* **[IMPROVED, CHANGED and FIXED in Pro] Content Management >> Custom Content Types**:
  * **Added Datetime field** and integrate with Oxygen Classic, Bricks, Breakdance and Elementor. Props to Pradnya T.P. for suggesting this improvement.
  * **Changed date picker library** froom bootstrap-datepicker to flatpickr. This is the same library used for the new datetime field, so, it streamlines assets requirements and UI/X.
  * **Fixed date field display inaccuracy** in Bricks builder when the WP site language is set to non-English. Props to Daniel B. for reporting the issue with a screencast, which helped identify the root cause and the fix.
  
* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: added "device type" conditional for CSS/JS/HTML snippets, which allows you to choose on which device(s) to load a snippet in, i.e. desktop / tablet / mobile. Props to Sreelal G. for prompting this improvement.

* **[IMPROVED in Pro] Utilities >> Form Builder**: improve accessibility by adding label for attributes for the email, website/URL, phone, text, textarea, number, range slider, spinner, star, NPS, dropdown, checkboxes, radio buttons, image select, date and time fields. This should also help improve Pagespeed Insights' Accessibility score for pages that has an ASE form in it. Props to Jakub X. for prompting this improvement.

* **[IMPROVED in Free and Pro] Admin Interface >> Enhance List Tables & Admin Columns Manager**: the featured image column will now have the post thumbnail linking to the post edit screen. Props to Domenico M. for suggesting this.

* **[IMPROVED in Free and Pro] Admin Interface >> Hide Admin Bar**: add a notice in the module settings that says "The settings above will override the ‘Toolbar’ settings in the user profile edit screen.". This should help clear up confusion on which settings will take precedence when the Hide Admin Bar module is enabled. Props to [Matt H.](https://wordpress.org/support/users/matthodder/) for [reporting the issue](https://wordpress.org/support/topic/hide-admin-bar-is-forcing-admin-bar-for-unchecked-roles/).

* **[FIXED in Free and Pro] Admin Interface >> Wider Admin Menu**: fixed an issue where Gravity Forms form edit screen's layout was shifted partially to the right. Props to Marv D. for repoerting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Content Duplication**: fixed a PHP fatal error that occurs in a certain scenario when ASE custom field group is attached to a post. Props to Wilbert S. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [32 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Vietnamese, Portuguese (Brazil), Polish, Norwegian, Indonesian, French, Dutch.
    * ASE Pro: Danish, Czech, Norwegian, Hungarian

### 7.8.9 (2025.05.05) - ASE Free and Pro

* **[IMPROVED and FIXED in Pro] Content Management >> Content Order**: 
  * When enabling content ordering for ASE code snippets, the 'View' button will no longer be shown as there is no frontend view for code snipepts, so, clicking on it would've shown a 404 error page. Props to Jim W. for prompting this change.
  * Fix PHP warning in a certain scenario involving 404 error pages. Pros to David B. for reporting the issue.

* **[IMPROVED and FIXED in Pro] Custom Code >> Code Snippets Manager**: 
  * **Added option to select code editor theme between dark and light**. Props to Jim W. for suggesting this improvement.
  * **Trashed snippets will now be deactivated properly**. Props to Jim W. for suggesting this improvement.
  * **Fixed snippet filtering not working when CSS type is selceted**. Props to Jim W. for reporting the issue.
  
* **[FIXED in Free and Pro] Log In/Out & Register >> Redirect After Login**: fix a redirect issue when the target URL is an HTML page with .html extension. THe slash (/) suffix will no longer be appended, which will break the URL / redirect. Props to [@hyzwar](https://wordpress.org/support/users/hyzwar/) for [reporting it](https://wordpress.org/support/topic/redirect-after-login-static-html-issue/).

* **[FIXED in Free and Pro] Optimizations >> Image Upload Control**: fix PHP warnings that occurs in a certain scenario. Props to [@johnwebman](https://wordpress.org/support/users/johnwebman/) for [reporting the issue](https://wordpress.org/support/topic/multiple-warnings-in-error-log/) complete with the error log entries.

* **[FIXED in Free and Pro] Admin Interface >> Enhance List Tables**: fixed an issue where showing the excerpt column can break the layout of WooCommerce products list (or any list) when the excerpt contains some HTML that gets improperly truncated. Props to @adminfongyuan for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [31 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Vietnamese, Spanish (Spain), Spanish (Chile), Portuguese (Brazil), Polish, Indonesian, Dutch, Chinese (Taiwan).
    * ASE Pro: updated Slovak, Hungarian, Romanian, Polish, Vietnamese.

### 7.8.8 (2025.04.28) - ASE Free and Pro

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: 
  * Implemented **code snippets export and import** via ASE settings page's "Export | Import" panel. Props to Philippe G., Tanguy, Nicolas M., Dennis M., Jeremy L., Yasuyoshi T., Daniel, Simon and Hoang Hxn for suggesting this improvement.
  * Fixed an issue where PHP snippets that contains certain **code was being truncated upon saving**. Props to Markus F. for reporting the issue and facilitating the troubleshooting process.
  * Fixed an issue where PHP snippets that contains certain **code was being malformed upon saving**, causing the snippet to churn out an error. Props to Emile V. for reporting the issue and facilitating the troubleshooting process.
  * Fixed an issue where **snippet description editor is not loaded properly in the snippet edit screen**. Props to Jim R. for reporting the issue and facilitating the troubleshooting process.
  * Fixed a **JS error when editing PHP snippet**.
  * Fixed **PHP warning** that occurs when saving a JS snippet with certain options

* **[IMPROVED and FIXED in Free and Pro] ASE Settings**: 
  * ASE Free and Pro: **Implement modules live search**, so you can more easily and quickly get to the module settings you need. Props to [wpman](https://wordpress.org/support/topic/feature-request-search-in-ase/), Todd, Max Z. and Nathan I. for prompting this improvement.
  * ASE Pro: Fixed an issue where **settings can not be imported when Admin Menu Organizer module is not enabled**. Props to Hoang Hxn. for reporting the issue.
  * ASE Pro: Fixed an issue where **translations are not properly loaded when the Form Builder module is enabled**. Props to Hoang Hxn. for reporting the issue.

* **[IMPROVED in Pro] Disable Components >> CAPTCHA Protection >> Comment Form**: make sure CAPTCHA assets are not loaded when commenting is allowed on an individual post while disabled for the entire post type via the Disable Comments module.

* **[CHANGED in Free and Pro] Translations**: make sure that translations are always loaded from the included .mo files. Props to Hoang Hxn for prompting this improvement.

* **[IMPROVED and FIXED in Pro] Utilities >> Form Builder**: 
  * Added **custom CSS for email templates**. Props to Mathijs v.d.B. for prompting this improvement.
  * Fixed an issue where **form preview are devoid of any styling** after v7.8.7 release. Props to John E. for prompting the fix.
  * Fixed an issue where **form inserted with the form block** in the block editor is devoid of any styles. Props to John E. for prompting this fix.
  * Added **new error message for the upload field when the PHP `tmpfile()` function is disabled / not available**, which will cause uploads to fail. Props to Eric L. for propmting this improvement.
  * **Fixed PHP warnings** when sending test emails.
  
* **[IMPROVED in Pro] Content Management >> Custom Content Types**: added a warning message when rendering the `[post_cf_form]` shortcode in edit mode but no post ID has been set.

* **[FIXED in Free and Pro] Log In/Out & Register >> Change Login URL**: fixed a conflict with Gravity Forms causing the file upload field, when set to upload multiple files, to fail with a JS error. Props to Henry R. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [31 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Vietnamese, Ukrainian, Spanish (Chile), Spanish (Spain), Slovak, Portuguese (Brazil), Polish, Norwegian, Indonesian, Dutch, Chinese (Taiwan).
    * ASE Pro: updated Romanian, Portuguese (Brazil), Polish, Vietnamese.

### 7.8.7 (2025.04.21) - ASE Free and Pro

* **[SECURITY FIX in Free and Pro] Custom Code >> Insert &lt;head&gt;, &lt;body&gt; and &lt;footer&gt; Code** fixed Stored XSS vulnerability reported by @ha1s3nb3rgg.

* **[IMPROVED in Free and Pro] Utilities >> Password Protection**:
  * ASE Pro: **Color of the "View Content" button** will now follow the one set in the Login Page Customizer module. Props to Sébastien B. for propmpting this improvement.
  * ASE Pro: **Added advanced options section to set custom label for password field and submit button**. Props to Sébastien B. for propmpting this improvement.
  * ASE Free and Pro: **Error messages are now internationalized**, i.e. translatable.  Props to Sébastien B. for propmpting this improvement.
  
* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**:
  * Date field format will now be honored when displaying a date field in Breakdance builder. Props to Radoslaw B. for the detailed reporting of the issue which includes environment data, current vs expected behaviour, technical details and steps to reproduce the issue.
  * "Line Break" label when a line break field is inside a repeater will no longer show up in the edit screen.
  * The 'Instructions' option when editing a "Line Break" field will no longer be displayed.
  
* **[IMPROVED in Pro] Utilities >> Maintenance Mode**: added an option to insert custom &lt;head&gt; code, which is useful, for example, to load custom web fonts, e.g. Google Fonts. The font can then be used in the Custom CSS section for styling the text on the maintenance page. Props to Bent F. for prompting this improvement.

* **[IMPROVED in Pro] Content Management >> Media Categories**: the categories tree state are now saved and will be shown upon reopening the media library or after drag-and-dropping media items to a category / sub-category. Props to Jeroen, Nicola and Ramyt I. for prompting this improvement.

* **[IMPROVED in Pro] Utilities >> Form Builder**: form scripts and styles will now only be loaded on pages where a form is being shown. Props to Mathijs v.d.B. for prompting this improvement.

* **[FIXED in Free and Pro] Log In/Out & Register >> Change Login URL**: fixed a bug where uploading a file on the frontend in Gravity Forms by a non-logged-in user fails due to recent security fix in Change Login URL. Props to [Bernd](https://wordpress.org/support/users/eide/) for [reporting the issue](https://wordpress.org/support/topic/gravitforms-logic-flawed-in-prevent_redirect_to_custom_login_url/) with sufficient technical details.

* **[FIXED in Free and Pro] ASE Settings Export / Import** will now include the settings for the Admin Menu Organizer module after recent change where the module settings has moved into it's own admin page. Props to Hoang Hxn for reporting the issue.
  
* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fixed an issue where sortable Meta Box columns were not shown as sortable due to [recent changes](https://metabox.io/meta-box-aio-includes-framework/) in how Meta Box AIO started including the Meta Box framework. Props to David D. for reporting the issue and facilitating troubleshooting.

* **[FIXED in Pro] Security >> Email Address Obfuscator**: fixed an issue where email address was not automatically obfuscated in an Elementor text widget. Props to Linda H. for reporting the issue.

* **[FIXED in Pro] Utilities >> Display System Summary**: added potential fix to prevent error when calculating varioius folder sizes. Props to @jh for reporting the issue and facilitating troubleshooting.

* **[FIXED in Pro] Admin Interface >> Show Custom Taxonomy Filters**: fix an issue where search is broken for posts while filter(s) for non-hierarchical taxonomies are enabled. Props Grzegorz K. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [31 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Vietnamese, Spanish (Spain), Portuguese (Brazil), Polish, Norwegian, German, Dutch, Danish.
    * ASE Pro: Updated Vietnamese, Hungarian.

### 7.8.6 (2025.04.14) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Enhance List Tables**: the Last Modified column will now use the date and time format set in Settings >> General. Props to @st3phan5 for [suggesting this](https://wordpress.org/support/topic/date-format-for-last-modified/).

* **[IMPROVED and FIXED in Pro] Content Management >> Custom Content Types**: 
  * **added integration of the repeater field into Elementor Pro**. This is done via the Loop Builder widget and the Loop Item template. [View documentation](https://www.wpase.com/documentation/custom-field-types/#elementor) for more details. Please test and open a support ticket if you see any bugs / issues.
  * improve the [[cf]] shortcode for gallery field when using the **grid output so each thumbnail now links to the full size image URL**. Props to Synapse A.I. S.L. for propmting this improvement.
  * fixed an issue introduced in v7.8.5 where the **WYSIWYG field can not be resized** when editing a post. Props to Stach R. for reporting the issue.

* **[FIXED in Free and Pro] Log In/Out & Register >> Change Login URL**: fix PHP warning as [reported by @sgiberson](https://wordpress.org/support/topic/undefined-variable-custom_login_slug/).

* **[FIXED in Pro] Admin Interface >> Clean Up Admin Bar**: fixed PHP deprecation notices. Props to Pierre G. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [31 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: Updated Vietnamese, Spanish (Spain), Portuguese (Brazil), Polish, Norwegian, German, Dutch, Danish.
    * ASE Pro: Updated Vietnamese, Hungarian.

### 7.8.5 (2025.04.07) - ASE Free and Pro

* **[IMPROVED in Pro] Security >> CAPTCHA Protection**: add an Advanced Options section for ALTCHA where you can set custom text for the different steps in the verification process. This can be useful if you need to quickly translate the default text. Props to @betaplus, Linda H. and Marcellus J. for prompting this improvement.

* **[IMPROVED and FIXED in Pro] Utilities >> Form Builder**: 
  * **Added an option to hide the form after successful submission**. This can be configured under form Settings >> Confirmation >> Confirmation Type >> Success Message. Props to Ignazio D.M. for prompting this improvement.
  * **Fixed layout issues on style template editor**. Fixed style editor not being full-width and the Publish button being contained in wide white box.

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Change Login URL**: added handling for an issue when Gravity Forms is active and someone tries to open a URL that contains the `gf_page` parameter, e.g. to preview a form, while not being logged-in, it would then redirect to the custom login URL. That will now be prevented and the user will be redirected to the homepage instead. Props to Coan Z. for reporting this.
  
* **[CHANGED in Free and Pro] Admin Interface >> Admin Menu Organizer**: moved the admin menu settings to a separate Admin Menu page/item under Settings. This will help prevent an issue where saving changes to ASE settings failed as [reported by @BooMeranGz](https://wordpress.org/support/topic/apache-mod_security-waf-problems/). This also improves the usability of the menu organizer for sites with lots of menu items, especially when drag-and-dropping menu item to reposition it.

* **[FIXED in Pro] Log In/Out & Register >> Last Login Column**: fixed conflict with WPGridBuilder causing user filtering to not work properly in WPGridBuilder. Props to Stuart McK. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Managmenent >> Media Categories**: 
  * fixed an issue where a PHP notice, "Function \_load_textdomain_just_in_time was called incorrectly", was being triggerred in a certain scenario. Props to Wilbert S. for reporting the issue and facilitating the troubleshooting process.
  * fixed a fatal error that occurs in a certain scenario when Breakdance is active and a logged-in user is trying to view the frontend. Props to Darren L. for reporting it and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [31 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Vietnamese, Ukrainian, Spansih (Spain), Portuguese (Brazil), Polish, Indonesian, Hungarian, German, Dutch
    * ASE Pro: updated Vietnamese. Special kudos to [Hoang Hxn](https://profiles.wordpress.org/nguyenquanghoang/) for the huge work of translating the new Form Builder module!

### 7.8.4 (2025.03.31) - ASE Free and Pro

* **[IMPROVED and FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: 
  * **Added frontend submission form via shortcode**. [Documentation](https://www.wpase.com/documentation/custom-field-types/#functions-shortcodes) has been added. Please test and open a support ticket if you find any issue / bug.
  * **Added `insert_post_cf()` to create a new post and set custom field values**. [Documentation](https://www.wpase.com/documentation/custom-field-types/#functions-shortcodes) has been added.
  * **Enable updating repeater field value with `update_cf()`**. [Documentation](https://www.wpase.com/documentation/custom-field-types/#functions-shortcodes) has been added.
  * **Improved `update_cf()` function to accommodate more data formats** for various field types, including ability to use externally hosted files/images for the file and gallery fields. More info in the [documentation](https://www.wpase.com/documentation/custom-field-types/#functions-shortcodes). As of this v7.8.4, this can be considered stable enough for production sites. That said, please open a support ticket if you discover any issues / bugs.
  * **Added Output Format option for Date, Time, Radio, Select and Checkbox fields**. This can be overridden by manually setting an output format in `get_cf()`, `the_cf()` or the `[cf]` shortcode.
  * **`get_cf()` in 'default' output mode** will now properly return the frontend display format set in the settings for Date, Time, Radio, Select and Checkbox fields, including when these fields are sub-fields of a repeater field. Props to Ingo R. for prompting this improvement.
  * **Added mininum height to WYSIWYG field** to fix the occasional rendering issue where a click was needed to trigger the field to be at it's proper height.

* **[IMPROVED in Pro] Utilities >> Form Builder**:
  * **Removed "Hide label" and "Required" checkboxex from Hidden fields** as they are not relevant for this field type.
  * **Removed superfluous < character and improve layout of description in the Default Value option**.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fixed PHP warning that occurs in a certain scenario.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed PHP warning that occurs in a certain scenario.

* **[FIXED in Pro] Content Management >> Media Categories**: fixed layout of the filter bar items in media frame/popup in certain scenario, so they are aligned horizontally and not vertically.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [31 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Spanish, Portuguese (Brazil), Dutch, Chinese (Taiwan)
    * ASE Pro: Hungarian, Polish

### 7.8.2 (2025.03.03) - ASE Free and Pro

* **[ADDED in Free and Pro] Disable Components >> Disable Smaller Components**: added an option to disable the Application Passwords feature in WordPress. Props to [DJABHipHop](https://wordpress.org/support/users/pressthemes1/) for [suggesting this](https://wordpress.org/support/topic/add-options-to-disable-wp_is_application_passwords_available/).

* **[IMPROVED in Pro] Optimizations >> Image Upload Control**: add an option to disable generation of intermediate sizes. Props to Wilbert S., [@visedfaq](https://wordpress.org/support/topic/feature-suggestion-remove-image-sizes/), [@bradhazel](https://wordpress.org/support/topic/feature-request-i-have-two/), Stijn V., Jake H. and [@luislu](https://wordpress.org/support/topic/add-function-to-disable-thumbnail-size/) for suggesting this feature.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: 
  * **Added a new field type: time**. This has been integrated with Oxygen (classic), Bricks, Breakdance and Elementor. [Documentation](https://www.wpase.com/documentation/custom-field-types/) has also been updated. Props to Jim R. and Rlaf L. for prompting this imprvement.
  * **Improved color field**. Color picker now supports transparency, which outputs the color in RGBA format, e.g. rgba(108, 19, 135, 0.9). Non-transparent color will be in HEX format, e.g. #333333.
  * **Improved UI for relationship field**. Posts are now categorized under the post type label, and the post type label preppended on the selected posts. This is especially useful if you have multiple post types enabled. Props to @yankiara for suggesting this improvement.
  * **Improved UI for term field**. Terms are now categorized under the taxonomy label, and the taxonomy label preppended on the selected terms.
  * **Improved UI for user field**. Will now include display name and email address. This makes it easier to search / filter users.

* **[CHANGED and IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: 
  * **Added rendering of column values for the new time field in ASE** custom field groups.
  * Added "!important" for the "overflow-y:hidden" attribute for the #list-table-wrapper div, to **better guarantee horizontal scrolling will work** when other CSS rules try to override this attribute.

* **[FIXED and IMPROVED in Pro] Utilities >> Form Builder**:
  * **Added option to not save form submissions** in the database. This may be useful for scenarios where privacy is of a concern. Props to Marcellus J. for suggesting this.
  * **Added option to set which field of each form to use for previewing entries** on the Entries listing page. This should make the Entries page more useful.
  * **Added #page_url tag as possible default value of hidden field** which will be replaced by the permalink of the page where the form is being displayed on. This is useful for when you need to track where form submission is coming from. Props to Ralf L. for suggesting this improvement.
  * **Fixed an issue where Screen Options panel is gone** from all admin pages when Form Builder is enabled. Props to Susanne R. for reporting the issue.
  
* **[FIXED in Pro] Security >> CAPTCHA Protection**: fixed PHP warning related to displaying CAPTCHA widgets on the comment form as reported by @betaplus.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fixed a PHP fatal error when clicking on Yootheme menu icon, which uncommonly, links to an AJAX call URL. Props to Àlex M. for reporting the error with the complete error stack trace and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Content Order**: fixed a PHP error that breaks AJAX-filtering using JetSmartFilters. Props to Stijn V. for reporting the issue and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [31 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Ukrainian, Spanish (Spain), Spanish (Chile), Portuguese (Brazil), Polish, Norwegian, Dutch
    * ASE Pro: updated Arabic, Norwegian, Hungarian, Polish, Romanian, Portuguese (Brazil). Polish, Portuguese (Brazil) and Romanian are 100% translated. Special kudos to [Jarosław K.]((https://profiles.wordpress.org/kosmity/)), [Valdemir M.](https://profiles.wordpress.org/valdemirmaran/) and [Catalin T.](https://profiles.wordpress.org/catalinx777/) for the huge work of translating the new Form Builder module!

### 7.8.1.1 (2025.03.10) - ASE Pro

* **[FIXED in Pro] Content Management >> Content Order**: 
  * fixed a bug introduced in v7.8.1 where all post types on the frontend are ordered by menu_order when the "Use custom order on frontend query..." checkbox are checked, disregarding the post types selection in the module settings. Props to Mitchel v.E. for reporting the issue.
  * fixed PHP warning related to applying custom ordering on frontend next/previous post navigation. Props to Richard B. for reporting the error with the complete error log entry.
  
### 7.8.1 (2025.03.03) - ASE Free and Pro

* **[IMPROVED in Pro] Security >> CAPTCHA Protection**: when CAPTCHA Protection is enabled on WooCommerce login form, also enable on the login form in WooCommerce checkout page. Ensure that after successful login, the customer/user is redirected back to the checkout page. This will override the settings added to the Redirect After Login module. Props to @betaplus for prompting this improvement.

* **[IMPROVED in Free and Pro] Disable Components >> Disable REST API**: 
  * ASE Pro: **CAPTCHA Protection >> ALTCHA widget** now works properly when Disable REST API is turned on. Props to Ivan C.d.A. for reporting the issue.
  * ASE Free and Pro: **Contact Form 7** now works properlywhen Disable REST API is turned on. Props to Ivan C.d.A. for reporting the issue.
  * ASE Pro: **Added an option to exclude certain routes** from being disabled.
  
* **[IMPROVED in Pro] Log In/Out | Register >> Login Page Customizer**: 
  * Added a link to the [haikei app](https://haikei.app/generators/) when choosing 'Pattern' as the login page background. This is where you can **generate your own pattern** with a color scheme that match the branding of the site you are working on. Props to Wence W. for prompting this improvement.
  * **The link to the privacy policy page** will now have the same color as registration and password reset links. Props to Wence W. for prompting this improvement.
  * **Added an option to choose login button color**. Button hover color and button text color will automatically adjust to the chosen color. You can always customize in detail with the Custom CSS option. Props to Wence W. for prompting this improvement.
  
* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fix PHP warning in a certain scenario. Props to Dave C. for reporting the issue in detail and even suggesting the code fix as well, which has been tested and implemented.

* **[FIXED in Pro] Content Management >> Content Order**: fix an issue where custom order is not being applied on taxonomy term archive page using the (WP) core Query Loop block. Props to Greg F. for reporting the issue in detail and working together to find a fix for it. Much appreciated!

* **[FIXED and IMPROVED in Pro] Utilities >> Form Builder**:
  * Add a Plain (no header image) email template. Props to Mathijs v.d.B. for suggesting this.
  * Made default field input background color white
  * Email notifications and auto-responder: ensure field tags work, i.e. properly replaced with the corresponding field value on email delivery. This also fixes the issue where auto-responder were not being delivered properly.
  * Dropdown field: fixed builder preview for dropdown field becoming empty when adding a new option
  * Address field: change zip subfield to be of the type text (previously it was number) to accomodate for various types of zip codes around the world
  * Website / URL field: improve URL validation method
  * Upload field: modification to frontend styles
  * Choice field presets: update to options for the Importance preset for better clarity / less ambiguity
  * Matrix of Uniform Dropdown field: fix an issue where the first option is not being shown in the dropddown.
  * Choice and Matrix fields: the options are now immediately draggable upon adding the field.
  * Textarea field: line breaks are now preserved in email notification and entry preview

* **[TRANSLATION in Free and Pro]** ASE is now being translated into [31 languages](https://translate.wpase.com/):
  * **Added new/improved translation** for:
    * ASE Free: updated Vietnamese, Spanish, Portuguese (Brazil), Polish, Norwegian, German, Dutch, Chinese (Taiwan)
    * ASE Pro: added Turkish (partial). Updated Czech, Vietnamese, Romanian, Portuguese (Brazil).

### 7.8.0 (2025.03.03) - ASE Free and Pro

* **[ADDED in Pro] Utilities >> Form Builder**: Enable the creation of various types of forms on the frontend to collect information from site visitors or users or members. This module should be stable enough but please perform some tests before using it on production sites or mission critical tasks. If you find any bugs / issues, please open a support ticket for it.

* **[IMPROVED in Free and Pro] Security >> Limit Login Attempts**: improved mechanism to validate IP addresses. Props to Ivan C.d.A. and [@denisgomesfranco](https://wordpress.org/support/topic/seemingly-weird-behavior-with-limit-login-attempts/) for prompting this improvement.

* **[FIXED in Free and Pro] Log In/Out | Register >> Login ID Type**: when login ID type is set to "email address only", logging in with an email address that contains an uppercase does not work. This is fixed now. Props to Martin H. for noticing the issue and reporting it.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fix a fatal error in a certain scenario when displaying the default Date column. Props to Alberto P. for reporting the issue with the error stack trace.

* **[FIXED in Pro] Content Management >> Content Order**: fix PHP warning that occurs in a certain scenario. Props to Dominik K., Simon S., Benjamin O., Sander d.V., betaplus, William L., @mmjaeger and Rick H.,  for reporting the issue along with the relevant error/debug log entry.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 31 languages:
  * **Added new/improved translation** for:
    * Free: Updated Spanish, Portuguese (Brazil), Polish and Norwegian
    * Pro: Updated Polish, Portuguese (Brazil), Romanian

### 7.7.0 (2025.02.24) - ASE Free and Pro

* **[ADDED in Pro] Security >> CAPTCHA Protection**: add CAPTCHA protection to WordPress default login, password reset, registration and comment forms and WooCommerce login, password reset and registration forms. Support [ALTCHA](https://altcha.org/) self-hosted version (GDPR-compliant, open source, free), Google reCaptcha v2 and v3, and Cloudflare Turnstile.

* **[IMPROVED in Free and Pro] Admin Interface >> Hide Admin Notices**: fix for notices still showing on Hash Form form builder screen.

* **[IMPROVED in Pro] Utilities >> Maintenance Mode**: added an option to exclude certain URLs from the maintenance mode. This could be useful if you have a custom login page created outside of ASE. Props to Miriam M. and Julian S. for prompting this improvement.

* **[IMPROVED amd FOXED in Pro] Admin Interface >> Admin Columns Manager**: 
  * **improved mechanism to load original column title** when an applicable column is set as such.
  * **fixed a conflict with PublishPress Revisions** plugin causing fatal error when viewing the revisions queue page. Props to Adam for reporting the issue with a screenshot and the error stack trace, which helped in quickly resolving the issue.
  
* **[IMPROVED in Pro] Log In/Out | Register >> Login Page Customizer**:
  * Changed logo image options to site icon, media library (image) or external (image) URL. Should work with your existing settings.
  * Added automatic image dimension detection and image ratio preserver when using media library image. Props to Max Z. for prompting this improvement.
  * Added links to external tools to get image dimension and calculate smaller dimension when using external image URL.

* **[IMPROVED in Pro] Optimizations >> Image Upload Control**: add an option to set the JPG quality during conversion/upload of BMP, PNG and JPG files. Props to John D. for prompting this improvement.

* **[FIXED in Free and Pro] Security >> Limit Login Attempts**: fix IPv6 addresses not being properly detected. Props to Mathijs v.d.B. and Jon B. for reporting the issue.

* **[FIXED in Pro] Content Management >> Content Order**: fixed an issue where adjacent posts navigation (Previous / Next) is not being displayed when the Content Order module is enabled for post types that does not include the post type where such navigation is added to. Props to Glenn W. for reporting the issue in detail and facilitated the troubleshooting process.

* **[FIXED in Pro] Admin Interface >> Admin Logo**: fix admin bar logo link not linking to the homepage as it should when viewed in the backend / wp-admin. Props to Max Z. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: update Norwegian, Polish, Dutch, Portuguese (Brazil), Chinese (Taiwan), Indonesian
    * Pro: update Indonesian, Hungarian, Polish

### 7.6.11 (2025.02.17) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Hide Admin Notices**: the notices counter in the admin bar will now be updated as you clear notices from inside the notices drawer. There will be a slight delay, about 2 seconds, to compensate for the various ways different plugins clear their notices and how long that might take. Props to Elmira T. for prompting this improvement.

* **[IMPROVED and FIXED in Pro] Admin Interface >> Admin Columns Manager**: 
  * add an option to **use a column as the default sort (ascending / descending)**, so that when visiting the View All Posts/Pages/CPT posts, the list table will be sorted accordingly. Props to Justin B., Matt B., Lynette C. and Patrick K. for prompting this improvement. Your patience is also appreciated.
  * add an option to **disable horizontal scrolling**, which might be useful in certain scnearios where another plugin is not expecting the `.wp-list-table` to be wrapped in a `div`. Props to Klemen T. for reporting a related issue that prompted this improvement.
  * enable **custom date time format for the default 'Date' column**. Props to Julian W. for prompting this improvement.
  * fixed **WPML's language column not showing the correct language flag** when switching language in the admin bar. Props to S. H. for reporting the issue in detail and facilitating the troubleshooting process.

* **[FIXED in Free and Pro] Admin Interface >> Various Admin UI Enhancements >> Display Active Plugins First**: fix a PHP error in a certain scenario. Props to betaplus for reporting the issue along with the error call stack, which helps with troubleshooting.

* **[FIXED in Free and Pro] Disable Components >> Disable Gutenberg**: fix for frontend block styles / CSS files not being properly disabled. Props to Stijn V. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Utilities >> Email Delivery**: fix an issue that may cause fatal error in some scenario when trying to log email deliveries. Props to Martin H. for reporting the issue in details (with an error stack trace).

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fix an issue where in Gravity Forms main dashboard, menu item(s) that are set to always be hidden for all user roles are not being properly hidden. Props to Robert D. for reporting the issue in detail and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: update Norwegian, Polish, Dutch, Portuguese (Brazil), Chinese (Taiwan), Indonesian
    * Pro: update Indonesian, Hungarian, Polish

### 7.6.10 (2025.02.10) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Content Management >> Content Order**: 
  * **when WPML plugin is active**, the content ordering page will now only show posts from the chosen language. Props to Stijn V. for prompting this improvement.
  * **added 'Order' button in posts list tables** of post types where Content Order module is enabled for. Useful for when the 'Order' submenu can not be added to the parent menu that contains the link to the post type list table, e.g. LearnDash courses.

* **[IMPROVED in Pro] Security >> Email Address Obfuscator**: added a 'text' parameter to the obfuscator shortcode that allows for showing a custom text instead of the human-readable email address. This, combined with the 'link' and 'class' parameter, allows for showing the obfuscated and linked email address as, for example, a "Contact Us" button. Props to Antoine L. for suggesting this improvement.

* **[IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: improv rendering of the post list table when this module is enabled. Props to Jake K. for prompting this improvement.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fix styling issues when select2 JS and CSS files are also loaded by LearnDash and Hash Form plugins on the snippet creation and editing screens.

* **[SECURITY FIX in Free and Pro]:
  * **Security >> Limit Login Attempts**: fix Bypass via IP Spoofing vulnerability as responsibly disclosed by Bob, a security researcher for WPScan and Jetpack.
  * **Utilities >> Password Protection**: fix Password Protection Bypass vulnerability as responsibly disclosed by Bob, a security researcher for WPScan and Jetpack.  

* **[CHANGED in Free] Security >> Limit Login Attempts**: IP detection from a preferred header, e.g. `HTTP_X_FORWARDED_FOR`, is now also available in the free version of ASE.

* **[FIXED in Free and Pro] Content Management >> External Permalinks**: fixed a compatibility issue that causes BeTheme template builder failing to load on singular template of post types that has External Permalinks enabled for. Props to PJ for reporting the issue and facilitating the troubleshooting process.

* **[CHANGED in Pro] Admin Interface >> Admin Logo**: Admin bar logo link will no longer open in new browser tab by default. Props to Henry R. for suggesting this change.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fix an issue where in a particular scenario, code snippets are not being properly stored as files to load/execute from. Props to Jhay B. for reporting the issue and facilitating the troubleshooting.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: update Norwegian, French, Polish, Dutch, Portuguese (Brazil), Persian
    * Pro: update Polish, Vietnamese, Portuguese (Brazil)

### 7.6.9 (2025.02.03) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Security >> Disable XML-RPC**: added additional things being disabled related to XML-RPC, namely, the 'X-Pingback' header in HTTP response headers, closing pings and pre-emptively remove several XML-RPC methods. Props to David M. for prompting this improvement.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: added the missing Pages as part of selection in the "Single Page/Post/CPT" conditional for loading CSS/JS/HTML snippets on the frontend. Props to Killian H. for reporting the issue.

* **[FIXED in Free and Pro] Utilities >> View Admin as Role**: fix PHP fatal error in a certain scenario. Props to Dale R. for reporting the issue in detail along with the error log entry.

* **[FIXED in Free and Pro] Content Management >> Media Replacement**: 
  - added mechanism to prevent browser cache busting URL parameter being added twice when performing replacements.
  - fix an issue where in the grid view, media replacement is not working properly in Firefox / Zen browsers. Props to Justin for reporting the issue in detail and facilitating the troubleshooting process.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fix date column showing 1 day difference with frontend displayed date. Props to Jonathan J. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fix an issue where submenu item's Hide / Options is not toggling the sub-options when clicked. Props to Christian for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Content Order**: fix an issue with LearnDash where enabling content ordering for Courses will not add the 'Order' submenu item. Props to Bas B. for reporting the issue and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: update Norwegian, Dutch, Polish, Portuguese (Brazil) and Slovak
    * Pro: updated Slovak

### 7.6.8 (2025.01.27) - ASE Free and Pro

* **[IMPROVED in Pro] Email Delivery >> Email Log**: make sure info on attachments in email delivery is properly logged, and also displayed when viewing the details of entries in the email log.

* **[FIXED in Free and Pro] Optimizations >> Image Upload Control**: fix PHP warning/error that occurs in a certain scenario. Props to [DJABHipHop](https://wordpress.org/support/users/pressthemes1/) for [reporting this](https://wordpress.org/support/topic/undefined-variable-converted_to_jpgplugin-admin-and-site-enhancements-ase/) with the error log entry.

* **[FIXED in Free and Pro] Admin Interface >> Various Admin UI Enhancements >> Display Active Plugins First**: fix PHP warnings in PHP 8.3. Props to Bjorn S. for reporting this.

* **[FIXED in Free and Pro] Security >> Email Address Obfuscator**: fix an issue where the obfuscated email address where there are hyphens in the domain name, will show a word in the domain name being reversed when viewed in iOS Safari browser. Props to Thorsten S. for reporting the issue.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: 
  * **fix layout issue** on frontend conditional's select2 input when a second select2 CSS is loaded by another plugin.
  * **fix for missing Code Snippets menu** in a certain scenario (roots.io Trellis setup). Props to Tim P. for reporting the issue in detail and facilitating the troubleshooting process.
  * **fix for "Single page/post/CPT" conditional** not properly loading the searchable list and allowing for selection of post(s). Props to Kilian H. for reporting the issue.
  
* **[FIXED in Pro] Pro version activation** when the free version is still active will now correctly show a message with a link back to the plugins page on a sub-folder WP install. Props to Florian G. for reporting the issue, where the link was missing the /sub-folder/ in the link.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: updated French, Hungarian, Albanian, Swedish, Vietnamese, Chinese (Taiwan)
    * Pro: updated Polish, Portuguese (Brazil), Vietnamese

### 7.6.7.1 (2025.01.20) - ASE Free and Pro

* **[FIXED in Free and Pro] fixed a fatal error** introduced in v7.6.7. Props to [@cck23](https://wordpress.org/support/users/cck23/) and [@aguilar1181](https://wordpress.org/support/users/aguilar1181/) for quickly [reporting it](https://wordpress.org/support/topic/ase7-6-7-error/).

### 7.6.7 (2025.01.20) - ASE Free and Pro

* **[IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: add support for **Meta Box Lite** plugin.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: add conditional logic to set on which part of the frontend CSS / JS / HTML snippets should be loaded. Conditionals include type of page, post type, single page/post/CPT, URL, taxonomy, taxonomy term, login status and user role.

* **[FIXED in Free and Pro] Content Management >> Open All External Links in New Tab**: fix an issue where links added via Elementor are not being opened in new tabs. Props to Stijn V. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: updated Turkish, Spanish (Spain), Norwegian, Dutch, Polish, Portuguese (Brazil), Russian
    * Pro: updated Portuguese (Brazil)

### 7.6.6 (2025.01.13) - ASE Free and Pro

* **[IMPROVED in Pro] Admin Interface >> Admin Logo**: 
  * add option to show the **admin menu logo in the backend** and show the **admin bar logo in the frontend**. Props to Max Z. for suggesting this.
  * add option to link the admin bar logo to the dashboard when it is shown on the frontend. Props to Max Z. for suggesting this.

* **[IMPROVED and FIXED in Pro] Custom Code >> Code Snippets Manager**: 
  * add option to **execute PHP snippet via a secure URL**.
  * add option to **set the load / execution priority / order** for all snippet types.
  * improved reliability of mechanism to record error when creating / updating PHP snippets
  * fixed fatal error when creating non-PHP snippet in a certain scenario

* **[IMPROVED in Pro] Admin Interface >> Various Admin UI Enhancements**: add an option to open all admin page links in new tab. Useful for people who prefer this workflow by default, instead of pressing the Ctrl or Command button while clicking on a link. Props to Rick A. for suggesting this improvement.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: fixed notices being displayed on Pods plugin's admin screen.

* **[FIXED in Pro] Utilities >> Display System Summary**: fix fatal error in a certain scenario when trying to load the dashboard widget. This has to do with a symlinked directory present in the WP root folder that links to a destination not defined in open_basedir. Props to Rado R. for reporting the issue in details and facilitating the troubleshooting process.

* **[FIXED in Pro] ASE Settings >> Export | Import**: fixed a bug where export was not working as expected. Props to David R. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: updated Norwegian, Portuguese (Brazil), Albanian
    * Pro: updated Czech, Spanish (Spain), French, Hungarian, Norwegian, Dutch, Polish, Portuguese (Brazil), Vietnamese

### 7.6.5 (2025.01.06) - ASE Free and Pro

* **[CHANGED in Pro] ASE Settings >> Import | Export** is now **available on all Pro plans** (SOLO, TEAM, AGENCY).

* **[FIXED in Free and Pro] Utilities >> View Admin as Role**: fixed PHP warning that occurs in a certain scnenario.

* **[FIXED and IMPROVED in Pro] Custom Code >> Code Snippets Manager**: 
  * add option to **execute PHP snippets on page load (always) or on demand (manually)**. Props to Theo v.d.S. for the prompting this improvement.
  * add option to **execute PHP snippets everywhere, only in the admin or only on the frontend**. Props to Gergo F. for prompting this improvement.
  * add option to **execute PHP snippets via shortcode**. Also add the shortcode in the snippets list's "Options" column.
  * **fixed PHP warning** introduced in v7.6.4. Props to Yoshihiro T., Mark K. and jman for reporting the issue with the relevant error/debug log entries.
  
* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fix an issue where if ACF select and radio fields are set to return an array, the admin columns for them are not showing any value. Props to A. Remut for reporting the issue.

* **[FIXED in Pro] Content Management >> Media Categories**: fixed a fatal error that occurs in a certain scenario when opening the "Appearances >> Menus" page. Props to Ivar S. for reporting the issue along with the debug/error log entries and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: updated Portuguese (Brazil), Portuguese (Portugal), Russian, Ukrainian.
    * Pro: updated Polish, Portuguese (Brazil), Vietnamese

### 7.6.4 (2024.12.30) - ASE Free and Pro

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: add an option for PHP snippets to choose which hook to execute each snippet at. You can choose between `plugins_loaded` (default), `after_setup_theme`, `init`, `wp_loaded` or `wp`. Props to Morten P. and Lee B. for prompting this improvement.

* **[CHANGED in Pro] Admin Interface >> Admin Columns Manager**: terms list in a taxonomy columns now links to the filtered list of posts with that term, just like clicking a category on the Posts list. Props to Stijn V. for prompting this change.

* **[FIXED in Free and Pro] Admin Interface >> SVG Upload**: fix fatal error and deprecation notice when the [Enhanced Responsive Images](https://wordpress.org/plugins/auto-sizes/) plugin is active. Props to [Mike B.] for [reporting this](https://wordpress.org/support/topic/fatal-error-svg-with-7-6-2/) and to [Sunny](https://wordpress.org/support/users/frdmsun/) for providing a critical piece of info that led to this fix.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed an issue where the Snippet Categories column is blank. Props to Stijn V. for reporting the issue.

* **[YEAR END SALE]** [**Get 20% discount**](https://www.wpase.com/chnlg-to-web) by the end of the year on new Pro license purchase and upgrades.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: updated Arabic, Spanish (Spain), Korean, Dutch, Polish, Portuguese (Brazil), Vietnamese, Chinese (Taiwan)
    * Pro: updated Arabic, Portuguese (Brazil), Vietnamese
    
### 7.6.3 (2024.12.16) - ASE Free and Pro

* **[SECURITY FIX in Free and Pro]**:
  * **Utilities >> Email Delivery**: fix Broken Access Control vulnerability related to the process of sending a test email. Props to Rafie Muhammad (Patchstack) for the responsible disclosure.
  * **Utilities >> View Admin as Role**: fix Privilege Escalation vulnerability that happens on a rare scenario. Props to Rafie Muhammad (Patchstack) for the responsible disclosure.

* **[IMPROVED in Free and Pro] Admin Interface >> Clean Up Admin Bar**: add an option to remove the home icon and site name. Props to Max Z. for prompting this improvement.

* **[IMPROVED and FIXED in Fre and Pro] Utilities >> Email Delivery**: 
  * Free: fix an issue where failed delivery during a send test just continues to show the spinning "Sending test email..." message. It should now properly update to "Oops, something went wrong" message. 
  * Pro: fix an issue where failed delivery were not properly logged as such in the email log in certain scenarios.
  * Pro: add an option to disable authentication which is needed in some scenarios, e.g. delivery via Google Workspace's without 2FA and using the `smtp-relay.gmail.com` host without credentials. Props to @boomerangz for suggesting this improvement.

* **[FIXED in Free and Pro] Content Management >> Media Replacement**: fix an issue where replacing non-image attachments, e.g. MP4 videos, might cause display issue on the attachment edit screen or when editing/viewing posts using displaying that attachment. Props to [@alriksson](https://wordpress.org/support/users/alriksson/) for [reporting the issue](https://wordpress.org/support/topic/media-replacement-query-string/).

* **[FIXED in Pro] Admin Interface >> Admin Logo**: fix misformatted CSS for styling the admin bar logo. Props to @nassukesso for reporting this.
  
* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fix an issue where the left-most, default checkbox column is displayed wider than it should when Simple Custom Post Order plugin is active. Props to Valentin J. for reporting the issue and collaborating with a thorough investigation on their part as well.

* **[FIXED in Pro] Content Management >> Content Order**: 
  * fix an issue where querying and displaying the posts on the ordering page produces a fatal error in some scenarios. Props to Valentin J. for reporting the issue with great detail (error log entry + screenshot), which helped with the troubleshooting process.
  * exclude CPTs from WordPres core, ASE, WooCommerce, Breakdance, Bricks and Elementor from the list of non-hierarchical CPTs to enable content ordering for.

* **[YEAR END SALE]** [**Get 20% discount**](https://www.wpase.com/chnlg-to-web) by the end of the year on new Pro license purchase and upgrades.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: updated Hungarian, Indonesian, Chinese (Taiwan)
    * Pro: updated Norwegian
  
### 7.6.2.1 (2024.12.09) - ASE Pro

* **[FIXED in Pro]** fixed a fatal error introduced in v7.6.2 in certain scenarios. Props to Marco M.J. for reporting the issue.

### 7.6.2 (2024.12.09) - ASE Free and Pro

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: text field now supports oEmbed output when the "URL / oEmbed" sub-type is selected and the value of the field is a URL from one of the [supported oEmbed providers](https://developer.wordpress.org/reference/hooks/oembed_providers/), e.g. YouTube. [Documentation for the text field](https://www.wpase.com/documentation/custom-field-types/#text) has been udpated to include this output type. Props to Konstantinos K. for suggesting this improvement.

* **[IMPROVED in Pro] Email Delivery**: add an option to store 10, 25 and 50 emails in the email log. Props to Sarah A. for the suggestion.

* **[CHANGED in Free and Pro] Content Management >> Content Order**: the required user capability to be able to order content has been changed from `edit_pages` to `edit_others_posts`. This preserve how things already work, i.e. administrators and editors can perform content ordering, but allows for scenarios where an editor / an editor-level custom role does not have the `edit_pages` capability but needs the ability to perform content ordering. Props to Uli L. for suggesting this change.

* **[FIXED in Free and Pro] ASE settings**: fix an issue where the settings page is broken when the [Assets Cleanup](https://wordpress.org/plugins/wp-asset-clean-up/) plugin is active. Props to George N. for reporting the issue with a screencast for clarity and assisting with troubleshooting.

* **[FIXED in Pro] Log In/Out | Regisger >> Login Page Customizer**: fixed an issue where page background color and logo sizing is being / can be overridden by CSS from theme child. Props to Christian for reporting the issue.

* **[FIXED in Pro] Admin Interface >> Enhance List Tables**: fix PHP warning in certain scenario when Last Modifield column is being shown. Props to R. József G. for reporting the issue.

* **[FIXED in Pro] Admin Interface >> Admin Logo**: fixed a layout issue where admin menu logo still shows and overlaps wp-admin content area at certain screen width range. Props to Andresen for reporting the issue.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fix an issue causing the Posts >> Tags submenu item not hideable for any user roles. Props to Antonio A. for reporting the issue with a screenshot for clarity.

* **[FIXED in Pro] Utilities >> Display System Summary**: fixed fatal error when open_base_dir restriction is in effect for a particular file path when trying to calculate the various main folder sizes. Props to Christian for reporting the issue with the full error log entry and facilitating the troubleshooting process.

* **[YEAR END SALE]** [**Get 20% discount**](https://www.wpase.com/chnlg-to-web) by the end of the year on new Pro license purchase and upgrades.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: updated Indonesian, Albanian, Chinese (Taiwan)
    * Pro: Indonesian, Czech, Polish, Portuguese (Brazil), Vietnamese
  
### 7.6.1 (2024.12.09) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Log In/Out | Register >> Change Login URL**: improved code to account for login attempts done via a POST request not originating from the custom login URL/page. Props to ken0429ng for reporting the issue and facilitating the troubleshooting process.

* **[CHANGED in Free and Pro] Optimizations >> Image Upload Control**: changed the max height value from 3,840 pixels to 10,000 pixels to account for use cases where tall screenshots of a page is being uploaded. Props to [@jessejfisher](https://wordpress.org/support/users/jessejfisher/) for [reporting this](https://wordpress.org/support/topic/saving-validation-issue/).

* **[FIXED in Pro] Content Mangement >> Custom Content Types >> Options Pages**: fixed an issue where a custom field in a newly created custom field group for an options page can not be displayed in Bricks and Elementor. They were shown in the custom field selection menu, but the value was not displayed on the preview and frontend. Props to Aleš S. and Ian W. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Utilities >> Display System Summary**: fixed fatal error that occurs when the file path is too long. Props to Maziar E.S. for reporting the issue in details with the error log entry and the WordPress environment details.

* **[FIXED in Pro] Content Mangement >> Custom Content Types >> Custom Field Group**: `get_cf()` will now use GMT/UTC timezone when outputting a date field to avoid issues where the output has a one-day difference for sites configured to use certain timezones, e.g. an American timezone. Props to Jonathan J. for reporting the issue and providing screenshots as well as the WordPress environment info to help with the troubleshooting.

* **[YEAR END SALE]** [**Get 20% discount**](https://www.wpase.com/chnlg-to-web) by the end of the year on new Pro license purchase and upgrades.

* **[TRANSLATION in Free and Pro]** Added Spanish (Chile) and completed Chinese (Taiwan) for ASE Free. ASE is now being translated into 29 languages:
  * **Added new/improved translation** for:
    * Free: added Spanish (Chile), updated Spanish (Spain), Norwegian, Dutch, Polish, Portuguese (Brazil), Serbian, Swedish, Vietnamese, Chinese (Taiwan)

### 7.6.0 (2024.11.18) - ASE Free and Pro

* **[CHANGED in Free and Pro] ASE Settings**: the "Log In | Log Out" category has been renamed to "Log In/Out | Register".

* **[ADDED in Free and Pro] Log In/Out | Register >> Registration Date Column**: implement the ability to show registration date column in the users list table. Pro version makes the column sortable. Props to Bas B. for suggesting this feature.

* **[IMPROVED in Free and Pro] Custom Code >> Manage robots.txt**: 
  * Update robots.txt online validation tools. Now uses websiteplanet.com and seositecheckup.tools. Props to David S. for reporting that one of the previous validators was no longer available.
  * Make sure robots.txt content being set in ASE is honored in the actual output on certain scenarios

* **[YEAR END SALE]** [**Get 20% discount**](https://www.wpase.com/chnlg-to-web) by the end of the year on new Pro license purchase and upgrades.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 28 languages:
  * **Added new/improved translation** for:
    * Free: Spanish, Albanian, Chinese (Taiwan).
    * Pro: updated Slovak.
  
### 7.5.4 (2024.11.25) - ASE Free and Pro

* **[FIXED in Free and Pro] Log In/Out | Register >> Site Identity on Login Page / Login Page Customizer**: fix login logo reverting to default WP logo on WP v6.7 instead of using the site icon. Props to [@kimu](https://wordpress.org/support/users/kimu/), [Greg M.](https://wordpress.org/support/users/gregmount/), Aleš S. and [@havidz](https://wordpress.org/support/users/havidz/) for reporting the issue [here](https://wordpress.org/support/topic/site-identity-on-login-page-does-not-seem-to-work-anymore/) and [here](https://wordpress.org/support/topic/after-update-my-logo-on-login-page-is-lost/).

* **[FIXED in Free and Pro] Disable Components >> Disable Smaller Components >> Disable plugin and theme editor**: fix fatal error that occurs in certain scenario. Props to Darren L. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 28 languages:
  * **Added new/improved translation** for:
    * Free: Spanish, Albanian, Chinese (Taiwan).
    * Pro: updated Slovak.

### 7.5.3 (2024.11.18) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Utilities >> Maintenance Mode**: make sure the site icon is included in the maintenance page. This should ensure browser tabs also displays the site icon. Props to [@tanasi](https://wordpress.org/support/users/tanasi/) for [reporting](https://wordpress.org/support/topic/favicon-missing-when-plugin-active/#post-18017882) the issue.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: 
  * Fixed fatal error in a certain scenario. Props to Francois G. for reporting the issue.
  * Fixed number of views being duplicated in the Views column of posts listing tables from Independent Analytics plugin. Props to Andreas K. for reporting the issue.
  
* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Group**:
  * **Fixed Oxygen builder integration** issue where the ASE Field selection button is not shown, or it's shown but not all ASE fields were listed upon clicking it.
  * **Add grid view option for gallery field** in Oxygen builder.

* **[YEAR END SALE]** [**Get 20% discount**](https://www.wpase.com/chnlg-to-web) by the end of the year on new Pro license purchase and upgrades.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 28 languages:
  * **Added new/improved translation** for:
    * Free: updated Arabic, Polish, Slovak, Albanian.
    * Pro: updated Arabic, Hungarian.

### 7.5.2 (2024.11.11) - ASE Free and Pro

* **[IMPROVED in Free] Log In/Out & Register >> Change Login URL**: failed login on non-default login form, e.g. WooCommerce account login page, will now redirect back to that custom login form / page and no longer redirect to the custom login URL set in ASE. Props to [@vanektomas](https://wordpress.org/support/users/vanektomas/) for [reporting this](https://wordpress.org/support/topic/i-found-a-bug-11/).

* **[IMPROVED and FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: 
  * [IMPROVED] The gallery field type now has an option to **display the gallery in grid mode** using the native [gallery] shortcode. Props to Han L. for suggesting this and providing the code snippet to base it upon.
  * [FIXED] Display of **WSYIWYG field on the frontend will now properly render paragraph breaks** via get_cf() and also in Oxygen and Breakdance. Props to Gabriel A. and Matija Z. for reporting the issue and facilitating the troubleshooting process.

* **[SECURITY/FIXED in Free and Pro] Content Management >> SVG Upload**: fixed a Stored XSS security issue responsiblly disclosed by Francesco Carlucci via Wordfence. This allowed adding/uploading malicious SVG image via the /media endpoint in the REST API. Sanitization has now been added in that upload route.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: fixed an issue where notices are not hidden on WS Form edit screen. Props to Solomon A. for reporting the issue.

* **[FIXED in Free and Pro] Optimizations >> Image Upload Control**: fixed fatal error in a rare scenario. Props to Darren L. for reporting the issue and helping with the troubleshooting process.

* **[FIXED in Pro] Utilities >> Email Delivery**: fixed entries order and sorting issue in the email delivery log. Props to Mathijs V.D.B for reporting the issue.

* **[YEAR END SALE]** [**Get 20% discount**](https://www.wpase.com/chnlg-to-web) by the end of the year on new Pro license purchase and upgrades.

* **[TRANSLATION in Free and Pro]** ASE is now being translated into 28 languages:
  * **Added new/improved translation** for:
    * Free: updated Spanish, Korean, Norwegian, Dutch, Portuguese (Brazil), Swedish, Ukrainian, Vietnamese.
    * Pro: updated Portuguese (Brazil), Vietnamese

### 7.5.1 (2024.11.04) - ASE Free and Pro

* **[IMPROVED in Pro] Admin Interface >> Various Admin UI Enhancements**: added an option to add username to the body class. Useful for when you need to modify the admin area only for certain user(s).

* **[IMPROVED in Pro] Content Management >> Public Preview for Drafts**: added public preview button/link in gutenberg editor.

* **[IMPROVED in Pro] Log In/Out & Register >> Login Page Customizer**: added an option to load an external CSS using the full URL of the CSS file. Props to Yann S. for prompting this improvement.

* **[IMPROVED in Free and Pro] ASE Settings**: number input fields now has appropriate min / max values. e.g. Revisions Control module's revision limit has a minimum value of 1 and maximum value of 100.

* **[FIXED in Free and Pro] Utilities >> Multiple User Roles**: fix PHP warning. Props to Bob G. for reporting the issue.

* **[FIXED in Free and Pro] Admin Interface >> Disable Dashboard Widgets**: fix PHP warning. Props to Richard E. for reporting the issue.

* **[YEAR END SALE]** [**Get 20% discount**](https://www.wpase.com/chnlg-to-web) by the end of the year on new Pro license purchase and upgrades.

* **[TRANSLATION in Free and Pro]** Added translation for Russian (partial). ASE is now being translated into 28 languages:
  * **Added new/improved translation** for:
    * Free: Updated Spanish, Korean, Norwegian, Dutch, polish, Brazilian Portuguese, Portuguese (Portugal), Russian, Slovak.
    * Pro: Updated Spanish, Norwegian, Brazilian Portugal, Slovak, Vietnamese.

### 7.5.0 (2024.10.28) - ASE Free and Pro

* **[ADDED in Pro] Content Management >> Public Preview for Drafts**: Enable public preview for draft posts from some or all public post types. Props to Jan K. for suggesting this.

* **[IMPROVED in Pro] Content Management >> Terms Order**: will now also allow ordering hierarchical taxonomies of non-public post types. Props to Matthias E. for prompting this change.

* **[IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: taxonomy columns are now sortable by default. To apply this change, please visit the Manage Columns page for each post type and then view the posts list table/page. Props to Satoshi F. for suggesting this improvement.

* **[FIXED in Free and Pro] Security >> Limit Login Attempts**: fix PHP fatal error and warnings in a certain scenario. Props to Oliver S. for reporting it.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: fix an issue where notices are not hidden on [WP All Export](https://wordpress.org/plugins/wp-all-export/) admin page. Props to [@tomislo](https://wordpress.org/support/users/tomislo/) for [reporting the issue](https://wordpress.org/support/topic/hide-admin-notices-2/).

* **[FIXED in Free and Pro] Optimizations >> Image Upload Control**: fix an issue where upon upload, image orientation changes, e.g. portrait to landspace. Props to Aleš S. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Custom Content Types**: fix an issue where custom REST API base for CPT and custom taxonomies were not working. Props to Gabriel A. for reporting the issue with a clear screencast to illustrate it.

* **[FIXED in Pro] Content Management >> Media Replacement**: fix JS error in the media library grid view preventing media replacement to be initiated in a certain scenario.

### 7.4.8 (2024.10.21) - ASE Free and Pro

* **[IMPROVED in Pro] Admin Interface >> Various Admin UI Enhancements**: added a module to add user role slug(s) to admin &lt;body&gt; classes. Useful for when you need to modify the admin area only for certain user roles. Props to Henry R. for prompting this improvement.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: added a couple of layout field types to more flexibly organize fields in a field group for data entry. Props to Christian G. for suggesting this improvement.
  * **Heading field**: useful for grouping together several fields. Props to Christian G. for suggesting this improvement.
  * **Line break field**: useful for breaking the flow of fields in a custom field group. The next field after the line break, will move to a new line.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: fix an issue where notices are not hidden on [WP All Import](https://wordpress.org/plugins/wp-all-import/) admin page. Props to [@tomislo](https://wordpress.org/support/users/tomislo/) for [reporting the issue](https://wordpress.org/support/topic/hide-admin-notices-2/).

* **[FIXED in Free and Pro] Disable Components >> Disable Gutenberg**: fix a layout issue on the classic editor UI due to a bug in Safari 18. Props to [@pressthemes1](https://wordpress.org/support/users/pressthemes1/) and Gabriel A. for reporting this.

* **[FIXED in Free and Pro] Disable Components >> Disable Smaller Components >> Disable plugin and theme editor**: fix PHP warninng. Props to [@pressthemes1](https://wordpress.org/support/users/pressthemes1/) and [@dsnger](https://wordpress.org/support/users/dsnger/) for [reporting the issue](https://wordpress.org/support/topic/undefined-array-key-disallow_file_edit/).

* **[FIXED in Pree and Pro] ASE Settings**: fixed an issue where the settings page has a JS error rendering it unusable when WPML and WMPL String Translation plugins are active. Props to Stijn V. for reporting the issue and facilitating troubleshooting.

* **[FIXED in ASE Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fix PHP warning.

* **[TRANSLATION in Free and Pro]** Added translation for Persian. ASE is now being translated into 27 languages:
  * **Added new/improved translation** for:
    * Free: Updated Spanish, French, Polish, Albanian. Added Persian.
    * Pro: Updated Slovak.
  
### 7.4.7 (2024.10.14) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Admin Menu Organizer**: 
  * [ASE Free] Fix PHP warning. Props to [@mkautsarjuhari](https://wordpress.org/support/users/mkautsarjuhari/) for [reporting this].
  * [ASE Pro] Specifically for the Dashboard menu item, the always hide for "all roles" radio choice will no longer be available. This prevents the scenario where all site admins are blocked from accessing the Dashboard page, creating potential confusion that the admin/site is broken. Props to A. Remut for prompting this improvement. 

* **[IMPROVED and FIXED in Free and Pro] Disable Components >> Disable Smaller Components >> Disable Plugin and Theme Editor**: 
  * Improve code logic to minimize the need to update wp-config.php.
  * Fix PHP warning. Props to Geert L. and @pressthemes1 for reporting the issue.

* **[IMPROVED and FIXED in Pro] Admin Interface >> Admin Columns Manager**: 
  * Added **several date time format options**. Props to Henry R. for the suggestion.
  * Enable **displaying sub-field of ACF group field** in a separate column. Props to Stanisław R. and Aseloka S. for suggesting this feature.
  * Make sure the default Last Modified and Published columns, as well as the Last Modified column from Enhanced List Tables module respects the date time format chosen. Props to Ivar S. for reporting the issue and facilitating the troubleshooting process.
  * Fixed deprecation notices in some scenarios.
  
* **[IMPROVED in Pro] Log In/Out & Register >> Change Login URL**: improved mechanism to prevent failed login on custom login forms, e..g WooCommerce, JetFormBuilder, from redirecting to wp-login.php. Props to Jose K.N. for prompting the improvement.

* **[IMPROVED in Pro] Log In/Out & Register >> Login Page Customizer**: you can now use external image URL for logo image and background image. Props to Nadja V.M. for prompting this improvement.

* **[IMPROVED in Pro] Utilities >> Maintenance Mode**: you can now use an external image URL for the background image. Props to Nadja V.M. for prompting this improvement.
* **[IMPROVED in Pro] Utilities >> Search Engine Visibility Status**: improve code logic to only perform checks when in wp-admin. Props to Uli L. for prompting the improvement.

* **[FIXED in Pro] Utilities >> Email Delivery**: fixed sorting issue with the email log, where the latest log entry are not displayed right away, and pagination is not showing entries in a sequential manner. Props to Nadja V.M. for reporting the issue.

* **[FIXED in Pro] Optimizations >> Image Upload Control**: fixed an issue with converting PNG to WebP in a scenario where `imagecreatefrompng()` is not available. Props to Bojan K. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Admin Interface >> Enhance List Table**: fixed PHP warning for Last Modified column.

* **[TRANSLATION in Free and Pro]** Added translation for Turkish. ASE is now being translated into 26 languages:
  * **Added new/improved translation** for:
    * Free: Danish, Italian, Dutch, Polish, Brazilian Portuguese, Ukrainian, Vietnamese, Chinese (Taiwan)
    * Pro: Hungarian, Brazilian Portuguese, Vietnamese
  
### 7.4.6 (2024.10.07) - ASE Free and Pro

* **[IMPROVED in PRO] Log In/Out & Register >> Change Login URL**: now respects the recently added feature to Redirect After Login module which allows for separate redirection URL for each user role. With this improvement, if a user role is already logged-in, going to the custom login URL will properly redirect to the URL set in Redirect After Login module for that role, no longer redirecting to /wp-admin/ (the Dashboard). An improvement was also implemented to handle the scenario where the user has multiple user roles. Props to Wence W. for reporting the issue and facilitating the troubleshooting.

* **[FIXED in PRO] Admin Interface >> Admin Columns Manager**: improved mechanism to detect and categorize custom fields in a post type, ensuring they are handled properly based on their handler (ASE, ACF, Meta Box or plain custom field). Props to Stanislaw R. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Free and Pro] Admin Interface >> Disable Dashboard Widgets**: fix for certain dashboard widgets not being listed by this module, thus not possible to disable them, e.g. HappyAddons for Elementor. Props to MIRAJA Design for reporting this.

* **[FIXED in Free and Pro] Admin Interface >> Admin Menu Organizer**: fix admin menu scrolling issue when opening WPIDE plugin's admin page. Props to Francois G. for reporting the issue.

* **[FIXED in Free and Pro] Disable Components >> Disable Smaller Components >> Disable Plugin and Theme Editor**: add a mechanism to check if wp-config.php is writeable. If wp-config.php is not writeable, a warning message will now be displayed in the module description. This will also prevent fatal error on sites where wp-config.php is not writeable. Props to @cvladan, @aguilar1181, @pressthemes1 for [reporting this](https://wordpress.org/support/topic/fatal-error-crash/).

* **[TRANSLATION in Free and Pro]** Added translation for Turkish. ASE is now being translated into 26 languages:
  * **Added new/improved translation** for:
    * Free: Updated Dutch, Polish, Portuguese (Brazil), Serbian, Vietnamese. Added Turkish.

### 7.4.5 (2024.09.30) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Disable Components >> Disable Smaller Components**: added an option to disable plugin and theme editor. Props to Michael S. and [@pressthemes1](https://wordpress.org/support/users/pressthemes1/) for [suggesting this](https://wordpress.org/support/topic/disable-plugin-theme-editor/).

* **[FIXED in Pro] Content Management >> Media Categories**: fix filter bar getting too tall. Now will be the same height as the category action bar.

* **[TRANSLATION in Free and Pro]** Added partial translation for Albanian and Chinese (Taiwan). ASE is now being translated into 25 languages:
  * **Added new/improved translation** for:
    * Free: Updated Arabic, German, Korean and Vietnamese. Added Serbian (partial) and Chinese-Taiwan (partial).
    * Pro: Updated Vietnamese. Added Arabic (complete).

### 7.4.4 (2024.09.23) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Optimization >> Image Upload Control**:
  * ASE Free and Pro: improved handling of PNG with transparency
  * ASE Pro: fix for WebP conversion for certain type of PNG with transparency. Props to Aleš S. for reporting the issue.
  
* **[IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: add an option to set custom formatting for number and date-time columns. This is applicable to default columns and custom field columns, including those by ASE, ACF, Meta Box.

* **[FIXED in Free and Pro] Log In/Out & Register >> Last Login Column**: fix login time not being properly logged. Props to [@pcamoz](https://wordpress.org/support/users/pcamoz/) for [reporting the issue](https://wordpress.org/support/topic/log-last-login-for-users/).

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fix PHP warning. Props to Simon K. for reporting the issue.

* **[FIXED in Pro] Content Management >> Media Categories**: fix CSS issue causing the search box position to shift leftward. Props to Mike D. for reporting the issue.

* **[TRANSLATION in Free and Pro]** Added partial translation for Romanian and Chinese (Taiwan). ASE is now translated into 24 languages:
  * **Added new/improved translation** for:
    * Free: Updated Arabic, German, Korean and Vietnamese. Added Serbian (partial) and Chinese-Taiwan (partial).
    * Pro: Updated Vietnamese. Added Arabic (complete).
  
### 7.4.3 (2024.09.23) - ASE Pro

* **[FIXED in Pro] Disable Components >> Disable REST API**: fix an issue, where in some scenarios, wp-admin and/or the ASE settings page was not accessible when this module was turned on. Props to Stijn V. and Matt B. for quickly reporting the issue and facilitating the troubleshooting process.

### 7.4.2 (2024.09.16) - ASE Free and Pro

* **[IMPROVED in Pro] Content Management >> Content Order**: adjacent posts (next and previous posts) are now correctly reflecting the custom order. Props to Antoine L. for suggesting this improvement.

* **[FIXED in Free and Pro] Log In/Out & Register >> Change Login URL**: fix an issue where visiting the custom login URL while logged-in returns a 404 Not Found error. Props to Nadja v.M. for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now translated into 22 languages:
  * **Added new/improved translation** for:
    * Free: Spanish, Indonesian, Norwegian, Dutch, Polish, Brazilian Portuguese, Ukrainian, Vietnamese
    * Pro: Vietnamese
  
### 7.4.1 (2024.09.10) - ASE Pro

* **[FIXED in Pro] Disable Components >> Disable REST API**: fix an issue where updating to v7.4.0 causes the REST API to be inaccessible for all authenticated / logged-in user roles. This for example, caused users not being able to load the Elementor editor. Props to Ron R. for quickly reporting the issue.

### 7.4.0 (2024.09.09) - ASE Free and Pro

* **[ADDED in Free and Pro] Admin Interface >> Enhance List Tables**: 
  * Add an option to show a sortable, last modified column. Props to [@nirmithamw](https://wordpress.org/support/users/nirmithamw/) for [suggesting this](https://wordpress.org/support/topic/feature-request-post-notes-last-modified/).
  * Add an option to hide the date column. Maybe useful if you're showing the last modified column already.
  
* **[IMPROVED in Pro] Disable Components >> Disable REST API**: add an option to disable REST API access for some or all authenticated / logged-in user role(). May be useful if you have a membership or ecommerce site and would like to limit access to the REST API for admins only. Props to Yurkee for suggesting this improvement.
  
* **[FIXED in Free and Pro] Disable Components >> Disable Gutenberg**: fix an issue where frontend gutenberg CSS assets and styles were not properly removed in some scenarios when using Bricks builder / themne, even though it's been set to be disabled in ASE settings. Props to [@thekendog](https://wordpress.org/support/users/thekendog/) for [reporting this](https://wordpress.org/support/topic/dequeue-wp-block-library-css/).
  
* **[FIXED in Pro] Admin Interface >> Admin Logo**: fix styling issue when admin bar logo is enabled and shown in the admin bar on the frontend. Props to [@lulech23](https://wordpress.org/support/users/lulech23/) for reporting this.

* **[TRANSLATION in Free and Pro]** ASE is now translated into 22 languages:
  * **Added new/improved translation** for:
    * Free: Spanish, Indonesian, Dutch, Polish, Brazilian Portuguese, Slovak, Vietnamese
    * Pro: Hungarian, Norwegian, Vietnamese, Romanian

### 7.3.3 (2024.09.02) - ASE Free and Pro

* **[ADDED in Free and Pro] Disable Components >> Disable Smaller Components**: added an option to remove generator tag that contains WordPress version number in RSS feed. Props to [Emmanue ATSÉ](https://wordpress.org/support/users/eatse/) for [suggesting this](https://wordpress.org/support/topic/disable-version-number-version-still-present-in-rss-feed/).

* **[IMPROVED in Pro] Log In/Out & Register >> Redirect After Login and Logout**: add option to redirect to separate URL for each user role.

* **[FIXED in Free and Pro] Log In/Out & Register >> Change Login URL**: fix PHP warning as [reported](https://wordpress.org/support/topic/php-warning-undefined-variable-4/#post-17984197) by [@dakotadevelopers](https://wordpress.org/support/users/dakotadevelopers/) and Thibaut V.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: some notices were not properly hidden on Wordfence plugin's dashboard, which is now fixed and will be properly hidden in the notices panel. Props to [@tomislo](https://wordpress.org/support/users/tomislo/) for [reporting the issue](https://wordpress.org/support/topic/hide-admin-notices-2/).

* **[FIXED in Pro] Admin Interface >> Admin Logo**: when admin logo is shown in the admin menu and the menu is collapsed, the logo will now be hidden instead of shown in the original size, overflowing the collapsed menu. Props to @myleslasco for reporting the issue.

* **[TRANSLATION in Free and Pro]** ASE is now translated into 22 languages:
  * **Added new/improved translation** for:
    * Free: Hungarian, Indonesian, Slovak, Swedish, Vietnamese
    * Pro: Italian, Vietnamese

### 7.3.2 (2024.08.26) - ASE Free and Pro

* **[IMPROVED in Pro] Security >> Email Address Obfuscator**: add option to only auto-obfuscate email addresses in post content for site visitors, not for logged-in users. This is useful for when you need to enable users to export post content that contains email addresses. Props to Wence W. for suggesting this improvement.

* **[FIXED in Free and Pro] Security >> Limit Login Attempts**: fix PHP warning. Props to [@malaga16](https://wordpress.org/support/topic/some-php-warning-in-debug-log/) for [reporting this](https://wordpress.org/support/topic/some-php-warning-in-debug-log/).

* **[TRANSLATION in Free and Pro]** ASE is now translated into 22 languages:
  * **Added new/improved translation** for:
    * Free: Arabic, Italian, Polish
    * Pro: Italian, Norwegian

### 7.3.1 (2024.08.19) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Clean Up Admin Bar**: when 'Howdy' is removed, the account menu item will now be positioned towards the right-most part of the admin bar. Props to Basil B. and Tony B. for reporting the issue.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: properly hide notices on user deletion confirmation screen. Props to [@tomislo](https://wordpress.org/support/users/tomislo/) for [reporting the issue](https://wordpress.org/support/topic/hide-admin-notices-2/).

* **[FIXED in Free and Pro] Content Management >> Content Order**: fix an issue where checks on non-public post types were not saved in the module settings. Props to Robert G. for reporting the issue.

* **[FIXED in Free and Pro] Utilities >> Password Protection**: fix an issue for WordPress subfolder install where entering the correct password does not work, i.e. does not remove password protection allowing to see the page content. Props to Manny C. and Markus F. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Free and Pro] Security >> Email Address Obfuscator**: fix an issue where the obfuscated email address is not being output in human-readable form when the shortcode for it is used inside an ACF options page (WYSIWYG Editor) and rendered via a Bricks builder template. Props to Aleš S. for reporting the issue and facilitatiing the troubleshooting process.

* **[FIXED in Free and Pro] Content Management >> Content Order**: fixed a bug where in a certain scenario, not all posts from a non-hierarchical post type is shown in the post ordering screen. Props to Henry R. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Utilities >> Search Engine Visibility Status**: fix an issue where on certain scenarios, search engine indexing was being disabled unintentionally. Props to Val J. and Andreas K. for reporting the issue and facilitating the troubleshooting process.

* **[TRANSLATION in Free and Pro]** Added Indonesian and Romanian translation. ASE is now translated into 22 languages:
  * **Added new/improved translation** for:
    * Completed for ASE Free: Indonesian, Romanian
    * Completed for ASE Pro: Indonesian
    * Updated for ASE Free: Spanish, Norwegian, Dutch, Polish, Brazilian Portuguese, Ukrainian, Vietnamese
    * Updated for ASE Pro: Norwegian

### 7.3.0 (2024.08.12) - ASE Free and Pro

* **[NEW in Free and Pro] Disable Components >> Disable Smaller Components >> Disable Lazy Load**: Disable lazy loading of images that was natively added since WordPress v5.5. Props to [@boxhamster](https://wordpress.org/support/users/boxhamster/) and [@masvil](https://wordpress.org/support/users/masvil/) for [suggesting this](https://wordpress.org/support/topic/feature-request-disable-wp-core-lazy-loading/).

* **[IMPROVED in Free and Pro] Content Management >> SVG Upload**: will now properly display SVGs uploaded using another plugin, e.g. SVG Support. Props to [@tomislo](https://wordpress.org/support/users/tomislo/) and [@ingarb](https://wordpress.org/support/users/ingarb/) for [reporting the issue](https://wordpress.org/support/topic/svg-issues-4/#post-17933277) which prompted this improvement.

* **[IMPROVED in Free and Pro] Content Management >> Content Order**: will now work with non-public post types as well. Props to Robert G. for prompting this improvement.

* **[IMPROVED in Pro] Log In/Out & Register >> Login Page Customizer**: enable use of the site icon as the login page logo. Props to Ron R. for suggesting this improvement.

* **[CHANGED in Pro] ASE Settings Export**: the "live site URL" field value will be emptied when exporting ASE settings. This will prevent "Reading >> Search engine visibility >> Discourage search engines from indexing this site" from being auto-checked, since the site the settings is being imported into will likely have a different URL than the original live site URL. Props to Val J. for prompting this change.

* **[FIXED in Pro] Content Management >> Media Categories**: improve the fix in in v7.2.1 for layout issue as reported by Marcellus J. Will now cover more scenarios.

* **[TRANSLATION in Free and Pro]** ASE is now translated into 20 languages:
  * **Added new/improved translation** for:
    * ASE Free: Arabic, Dutch, Brazilian Portuguese, Slovak, Swedish, Vietnamese, Hungarian
    * ASE Pro: Danish, Brazilian Portuguese, Slovak, Vietnamese, German (formal), Hungarian

### 7.2.1 (2024.08.05) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Utilities >> Password Protection**: password-protected pages will now be marked with `noindex` to prevent "duplicate – from user not as canonical set up" warning in SEO reports, e.g. by google. Props to Christian S. for [suggesting this](https://wordpress.org/support/topic/how-to-set-passwort-protection-page-noindex/).

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: the number field now supports decimal values. Props to Philippe G. for prompting the improvement.

* **[IMPROVED in Pro] Admin Interface >> Admin Logo**: enable the use of an image URL hosted on another site as the logo image. Props to Bayley S. for suggesting this improvement.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: fix for notices coming from "Stock Sync with Google Sheet for WooCommerce" and "Stock Sync with Google Sheet for WooCommerce Ultimate" plugins not being hidden. Props to Manish S. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Media Categories**: 
  * **fix a conflict with WP to Buffer plugin** causing the plugins list page to show hidden modals. Props to Riza A. for reporting the issue in detail.
  * **fix CSS layout issue with WP 6.6** and greater when viewing the media library. This happens when both "Clean Up Admin Bar >> Remove the Help tab and drawer" and "Enhance List Tables >> Show file size column in media library" are checked. Props to Marcellus J. for reporting the issue.
  
* **[TRANSLATION in Free and Pro]** ASE is now translated into 20 languages:
  * **Added new/improved translation** for:
    * ASE Free: Hungarian, Korean, Brazilian Portuguese, Slovak, Vietnamese
    * ASE Pro: Hungarian, Brazilian Portuguese, Slovak, Vietnamese, Chinese

### 7.2.0 (2024.07.29) - ASE Free and Pro

* **[NEW in Pro] Admin Interface >> Admin Logo**: add custom logo to the admin dashboard. You can choose to show it on the admin bar, or at the top of the admin menu. Props to Rino D.B. and Matt B. for prompting the addition of this new module.

* **[FIXED in Free and Pro] Admin Interface >> Clean Up Admin Bar**: fixed 'Howdy' no longer being hidden since WP v6.6. Props to [@wesleypeace](https://wordpress.org/support/users/wesleypeace/), [@lookazd](https://wordpress.org/support/users/lookazd/), [@colourstone](https://wordpress.org/support/users/colourstone/), [@verysiberian](https://wordpress.org/support/users/verysiberian/) and Marco M.J. for [reporting the issue](https://wordpress.org/support/topic/hide-howdy-not-working/).

* **[FIXED in Free and Pro] Log In/Out & Register >> Change Login URL**: fixed a compatibility issue with User Switching plugin, preventing it to perform user switching. Props to George N. for reporting the issue.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fix several PHP warnings. Props to Marco for reporting the issue.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed PHP warning and deprecation notices in certain scenarios. Props to Leigh H. for reporting the issue in great details and facilitating the troubleshooting process.
  
* **[TRANSLATION in Free and Pro]** ASE is now translated into 20 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/), [denisgomesfranco](https://profiles.wordpress.org/denisgomesfranco/), [Denison C.](https://profiles.wordpress.org/denisoncarlos/) and [@ofmarconi](https://profiles.wordpress.org/ofmarconi/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **French**: ASE Free and Pro (completed). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Polish**: ASE Free and Pro (completed). Props to [@kosmity](https://profiles.wordpress.org/kosmity/) and [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Norwegian**: ASE Free and Pro (completed). Props to [Alf O.F.](https://profiles.wordpress.org/skoen/).
  * **Czech**: ASE Free and Pro (completed). Props to Jan S.
  * **Spanish**: ASE Free and Pro (completed). Props to [@marcorubiol](https://profiles.wordpress.org/marcorubiol/).
  * **Slovak**: ASE Free and Pro (completed). Props to [Dominik K.](https://profiles.wordpress.org/dominokozmali/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Arabic**: ASE Free (completed) | ASE Pro (partial). Props to [Mohammed J.](https://profiles.wordpress.org/ih4xz/).
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 7.1.5 (2024.07.22) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Hide Admin Notices**: the Notices admin bar menu item will now be hidden by default and only shown when there are notices on the page. Previously, it was shown briefly and disappear when there are no notices on the page. Props to Stijn V. for suggesting the improvement.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: the WYSIWYG field will now include ordered and unordered list buttons and be more similar to WP classic editor. Props to Daniel and Michael R. for prompting the improvement.

* **[CHANGED in Pro] Content Management >> Custom Content Types >> Custom Taxonomies**: the default 'Uncategorized' term will no longer be created by default. If you've created custom taxonomies prior to ASE Pro v7.1.5 and want to remove that term, please follow [this documentation](https://www.wpase.com/documentation/how-to-remove-uncategorized-taxonomy-term/). Props to Hadar B., Di C., Ole P. for prompting this change.

* **[FIXED in Free and Pro] Security >> Limit Login Attempts**: fixed PHP notices that appears in a certain scenario. Props to Oliver S. for reporting this with a copy of the relevant PHP/error/debug log entries.

* **[FIXED in Free and Pro] Utilities >> Redirect 404**: fixed an issue where ASE's redirection overrides those set by SEOPress Pro. Props to Markus B. for reporting the issue with a screencast and facilitating the troubleshooting process.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: 
  * **Fixed submenu items with blank titles** that appears in certain scenarios / for certain plugin's submenu items. Props to Marvin A. for reporting this.
  * **Fixed submenu items not being always hidden** in certain scenarios / for certain plugin's submenu items. Props Marvin A. for reporting this and facilitating troubleshooting.
  
* **[TRANSLATION in Free and Pro]** ASE is now translated into 20 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/), [denisgomesfranco](https://profiles.wordpress.org/denisgomesfranco/), [Denison C.](https://profiles.wordpress.org/denisoncarlos/) and [@ofmarconi](https://profiles.wordpress.org/ofmarconi/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **French**: ASE Free and Pro (completed). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Polish**: ASE Free and Pro (completed). Props to [@kosmity](https://profiles.wordpress.org/kosmity/) and [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Norwegian**: ASE Free and Pro (completed). Props to [Alf O.F.](https://profiles.wordpress.org/skoen/).
  * **Czech**: ASE Free and Pro (completed). Props to Jan S.
  * **Spanish**: ASE Free and Pro (completed). Props to [@marcorubiol](https://profiles.wordpress.org/marcorubiol/).
  * **Slovak**: ASE Free and Pro (completed). Props to [Dominik K.](https://profiles.wordpress.org/dominokozmali/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Arabic**: ASE Free (completed) | ASE Pro (partial). Props to [Mohammed J.](https://profiles.wordpress.org/ih4xz/).
  * **Translation updates**: added new/improved translation for:
    * ASE Free: Italian, Dutch, Brazilian Portuguese, Portugal Portuguese, Vietnamese
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 7.1.4 (2024.07.15) - ASE Free and Pro

* **[MILESTONE] ASE Free just reached [100,000 active installs](https://wordpress.org/plugins/admin-site-enhancements/advanced/)!** If you've been enjoying using ASE on your site(s), please consider writing [a quick 5-star review](https://wordpress.org/plugins/admin-site-enhancements/#reviews) or share about ASE on your WordPress-related facebook group(s). You can also [provide your feedback](https://wordpress.org/support/plugin/admin-site-enhancements/) on how ASE can be improved further. Thank you!

* **[IMPROVED in Pro] Utilities >> Search Engines Visibility Status**: will now automatically strip the trailing slash in the live / production site's URL added in the module's settings. This prevents "Settings >> Reading >> Discourage search engines from indexing this site" from being auto re-cchecked on the live / production site, when a trailing slash is included in the module's settings. Props to Henry R. for testing and reporting the issue in detail.

* **[IMPROVED in Pro] Content Management >> Media Categories**: fixed an issue where the Screen Options tab is not clickable. Props to Steven Y. for reporting the issue and suggesting a fix for it.

* **[IMPROVED in Pro] Utilities >> Email Delivery >> Email Delivery Log**: 
  * Make the Resend button / feature available for entries with 'Successful' status as well. Props to Mathijs V.D.B. for the suggestion.
  * Added feature to **delete individual log entries** and to **clear the whole log**. Props to Francois G. for suggesting this feature.

* **[FIXED in Free and Pro] ASE Settings**: layout fixes for RTL languages, e.g. Arabic.
  
* **[TRANSLATION in Free and Pro]** Added Slovak translation. ASE is now translated into 20 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/), [denisgomesfranco](https://profiles.wordpress.org/denisgomesfranco/), [Denison C.](https://profiles.wordpress.org/denisoncarlos/) and [@ofmarconi](https://profiles.wordpress.org/ofmarconi/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **French**: ASE Free and Pro (completed). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Polish**: ASE Free and Pro (completed). Props to [@kosmity](https://profiles.wordpress.org/kosmity/) and [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Norwegian**: ASE Free and Pro (completed). Props to [Alf O.F.](https://profiles.wordpress.org/skoen/).
  * **Czech**: ASE Free and Pro (completed). Props to Jan S.
  * **Spanish**: ASE Free and Pro (completed). Props to [@marcorubiol](https://profiles.wordpress.org/marcorubiol/).
  * **Slovak**: ASE Free and Pro (completed). Props to [Dominik K.](https://profiles.wordpress.org/dominokozmali/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Arabic**: ASE Free (completed) | ASE Pro (partial). Props to [Mohammed J.](https://profiles.wordpress.org/ih4xz/).
  * **Translation updates**: added new/improved translation for:
    * ASE Free: Arabic, Italian, Norwegian, Dutch
    * ASE Pro: Vietnamese, Slovak
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 7.1.3 (2024.07.08) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Show Custom Taxonomy Filters**: when set, will now use `$taxonomy->labels->all_items` instead of "All {post type plural label)" where "All" is hardcoded, albeit still internationalized. Props to [@jpagano](https://wordpress.org/support/users/jpagano/) for [suggesting this](https://wordpress.org/support/topic/use-taxonomy-labels-all_items-in-the-custom-taxonomy-filter-all-option/).

* **[IMPROVED in Pro] Utilities >> Email Delivery >> Email Delivery Log** added a feature to resend emails that failed during delivery attempt. You can also specify a different destination email, e.g. useful when, for example, there's a typo in the original destination email. Props to Mathijs V.D.B. for suggesting the improvement.

* **[IMPROVED in Pro] Log In/Out & Register >> Login Page Customizer**: if numeric values are entered in the logo width and height settings, the 'px' unit will be automatically added upon saving changes. This will prevent the logo from showing up in the default 84x84 pixels. Props to Matt B. for prompting the improvement.
  
* **[TRANSLATION in Free and Pro]** Added Arabic translation. ASE is now translated into 19 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/), [denisgomesfranco](https://profiles.wordpress.org/denisgomesfranco/), [Denison C.](https://profiles.wordpress.org/denisoncarlos/) and [@ofmarconi](https://profiles.wordpress.org/ofmarconi/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **French**: ASE Free and Pro (completed). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Polish**: ASE Free and Pro (completed). Props to [@kosmity](https://profiles.wordpress.org/kosmity/) and [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Norwegian**: ASE Free and Pro (completed). Props to [Alf O.F.](https://profiles.wordpress.org/skoen/).
  * **Czech**: ASE Free and Pro (completed). Props to Jan S.
  * **Spanish**: ASE Free and Pro (completed). Props to [@marcorubiol](https://profiles.wordpress.org/marcorubiol/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Arabic**: ASE Free (completed) | ASE Pro (partial). Props to [Mohammed J.](https://profiles.wordpress.org/ih4xz/).
  * **Translation updates**: added new/improved translation for:
    * ASE Free: Vietnamese, Ukrainian, German, Brazilian Portuguese
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 7.1.2 (2024.07.01) - ASE Free and Pro

* **[IMPROVED in Pro] Utilities >> Email Delivery**: add option to set Bcc email address(es). Props to Gerold H. for suggesting this.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: add description on finding the Code Snippets menu item once the module is enabled.

* **[CHANGED in Free and Pro] ASE Settings**: ASE will explicitlly autoload two of the options it creates in wp_options in anticipation of upcoming [changes in WP v6.6](https://make.wordpress.org/core/2024/06/18/options-api-disabling-autoload-for-large-options/).

* **[IMPROVED in Pro] Log In/Out & Register >> Change Login URL**: when failing to login on WooCommerce My Account page, you'll no longer be redirected to the custom login URL, thus revealing it's location, but will remain on the My Account page with an error message shown there. Props to Gustavo F. for reporting the issue in great detail.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: fixed briefly flashing notices issue that crept into v7.1.1 release. Props to Justin for noticing and taking the time to open a support ticket to quickly report it.

* **[FIXED in Free and Pro] Admin Interface >> Admin Menu Organizer**: fixed fatal error in a certain scenario. Props to Ralf L. for reporting the issue and proposing the code fix.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: 
  * fixed an issue where on a fresh install, the **first custom field being added and saved would go missing** upon adding and saving a second one in a custom field group. Props to Michael S. for finding the bug and reporting the steps to re-create the bug reliably, which helps in troubleshooting it.
  * fixed an issue with **Breakdance integration** where using the **Post Content** element in a Single Post template causes HTTP 500 error. Props to Alexandre M. for performing a thorough test to confirm the bug and recording a detailed screencast to prove it. Props to Darren L. and Henry R. for reporting a similar issue and helping with troubleshooting and testing the fix. If you've been using Template Content Area, you can now switch to Post Content if you need to.
  
* **[TRANSLATION in Free and Pro]** ASE is now translated into 18 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/), [denisgomesfranco](https://profiles.wordpress.org/denisgomesfranco/), [Denison C.](https://profiles.wordpress.org/denisoncarlos/) and [@ofmarconi](https://profiles.wordpress.org/ofmarconi/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **French**: ASE Free and Pro (completed). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Polish**: ASE Free and Pro (completed). Props to [@kosmity](https://profiles.wordpress.org/kosmity/) and [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Norwegian**: ASE Free and Pro (completed). Props to [Alf O.F.](https://profiles.wordpress.org/skoen/).
  * **Czech**: ASE Free and Pro (completed). Props to Jan S.
  * **Spanish**: ASE Free and Pro (completed). Props to [@marcorubiol](https://profiles.wordpress.org/marcorubiol/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Translation updates**: added new/improved translation for:
    * ASE Free: Hungarian, Dutch, Swedish, Ukrainian, Chinese
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 7.1.1 (2024.06.24) - ASE Free and Pro

* **[IMPROVED in Pro] Utilities >> Email Delivery**: add option to set the number of most recent email delivery log entries to keep. This should prevent database bloat when logging is enabled.

* **[IMPROVED in Pro] Utilities >> Maintenance Mode**: add option to allow frontend access for non-administrator role(s). Props to Jan W. for suggesting the improvement.

* **[IMPROVED in Pro] Disable Components >> Disable Gutenberg & Disable Comments**: add an option to "Disable on all post types". Props to PJ for suggesting the improvement.

* **[CHANGED in Pro] Log In/Out & Register >> Site Identity on the Login Page**: this module is now disabled in the Pro version as it's features are included in the Login Page Customizer module.

* **[FIXED / IMPROVED in Pro] Admin Interface >> Admin Menu Organizer**: 
  * fixed a JS error preventing the module to work properly when WP Activity Log plugin is active. Props to @admin_truemarket for reporting the issue.
  * replaced triangle HTML symbol with SVG icon to prevent styling from being affected by emoji script.
  
* **[FIXED in Free and Pro] Log In/Out & Register >> Change Login URL**: trying to open /wp-admin/profile.php while not being logged-in will no longer redirect to the custom login URL. Props to Orlando M. for reporting the issue.

* **[FIXED in Free and Pro] Content Management >> AVIF Upload**: fix broken links in the module description. Props to [@jlop77](https://wordpress.org/support/users/jlop77/) for [reporting](https://wordpress.org/support/topic/%e2%98%a0%ef%b8%8f-is-this-a-security-bug-it-redirects-me-to-a-strange-page/) the issue.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**:
  * Fix an issue where WPML WooCommerce Multilingual & Multicurrency plugin's Setup Wizard was hidden. Props to [@ignasr](https://wordpress.org/support/users/ignasr/) for [reporting](https://wordpress.org/support/topic/incompatability-with-wpml/) the issue.
  * Fix an issue where notices are not being hidden in FunnelKit Funnel Builder plugin's Licenses screen. Props to Simon L. for reporting this.
  
* **[FIXED in Free and Pro] Content Management >> SVG Upload**: fixed a plugin conflict issue with SVG Block plugin, and in general, with any other plugin that uses the \enshrined\svgSanitize\Sanitizer class in their code. Props to [@jlop77](https://wordpress.org/support/users/jlop77/) for [reporting](https://wordpress.org/support/topic/svg-issues-3/) the issue and narrowing it down to a conflict with SVG Block plugin and even reported the incidence on [their support forum](https://wordpress.org/support/topic/conflict-with-ase-plugin/). So very helpful!

* **[TRANSLATION]** Spanish translation added (complete)! ASE is now translated into 18 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/), [denisgomesfranco](https://profiles.wordpress.org/denisgomesfranco/), [Denison C.](https://profiles.wordpress.org/denisoncarlos/) and [@ofmarconi](https://profiles.wordpress.org/ofmarconi/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **French**: ASE Free and Pro (completed). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Polish**: ASE Free and Pro (completed). Props to [@kosmity](https://profiles.wordpress.org/kosmity/) and [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Norwegian**: ASE Free and Pro (completed). Props to [Alf O.F.](https://profiles.wordpress.org/skoen/).
  * **Czech**: ASE Free and Pro (completed). Props to Jan S.
  * **Spanish**: ASE Free and Pro (completed). Props to [@marcorubiol](https://profiles.wordpress.org/marcorubiol/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Translation updates**: added new/improved translation for:
    * ASE Free: Spanish, French, Hungarian, Italian, Norwegian, Dutch, Polish, Brazilian Portuguese, Ukraininan and Vietnamese
    * ASE Pro: Spanish, Hungarian, Brazilian Portuguese
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 7.1.0 (2024.06.17) - ASE Free and Pro

* **[IMPROVED in Pro] Utilities >> Email Delivery**: added an option to enable logging of email deliveries. Props to Francois G. and Jonathan L. for suggesting this.

* **[IMPROVED in Pro] Log In/Out & Register >> Login Page Customizer**: added an option to hide the "Remember Me" checkbox. Props to Markus R. for the suggestion.

* **[FIXED in Free] Utilities >> Maintenance Mode**: fix missing background image/pattern after update to v7.0.3. Props to [@arj968653](https://wordpress.org/support/users/arj968653/) for [reporting this](https://wordpress.org/support/topic/maintenance-mode-background-not-shown/) in detail.

* **[FIXED in Free] Security >> Email Address Obfuscator**: fix obfuscated email address not properly inlined in certain scenarios, e.g. used inside Elementor Text Editor widget. Props to [@lostguybrazil](https://wordpress.org/support/users/lostguybrazil/) for [reporting this](https://wordpress.org/support/topic/email-address-obfuscator-email-always-on-its-own-line/) with detailed explanation, screenshots and also proposing a working solution!... which was applied on this fix.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fixed fatal error in a particular scenario involving the RWMB_Core class. Props to Kenneth S. for reporting the issue complete with the error log details.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fixed an issue where JS error occurs when certain plugins uses a star icon in it's subemnu title.

* **[TRANSLATION]** ASE is now translated into 17 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **French**: ASE Free and Pro (completed). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Polish**: ASE Free and Pro (completed). Props to [@kosmity](https://profiles.wordpress.org/kosmity/) and [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Norwegian**: ASE Free and Pro (completed). Props to [Alf O.F.](https://profiles.wordpress.org/skoen/).
  * **Czech**: ASE Free and Pro (completed). Props to Jan S..
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Translation updates**: added new/improved translation for:
    * ASE Free: Danish, Hungarian, Italian, Norwegian, Dutch, Swedish
    * ASE Pro: Chinese, Czech, Danish
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 7.0.3 (2024.06.10) - ASE Free and Pro

* **[IMPROVED in Pro] Utilities >> Maintenance Mode**: 
  * Added an option to **use an existing page** created with the block / classic editor or with a page builder as the maintenance page. Props to Ignazio D.M., [@pyoil](https://wordpress.org/support/users/pyoil/), Michael M., Antoine L. and Melissa I. for suggesting this feature. 
  * Added **pattern as background** option like in the Login Page Customizer module.

* **[IMPROVED in Pro] Utilities >> Password Protection**: automatically applies design elements from the Login Page Customizer module when it's enabled. This includes the login form background and the page background.

* **[IMPROVED in Pro] Log In/Out & Register >> Login Page Customizer**: make subfields line up on the same row on the Login Page Background section when 'Custom' is selected. Make two more strings translatable. Props to Hoang N.Q. for pointing them out.

* **[IMPROVED in Free and Pro] Log In | Log Oot >> Change Login URL**: when logged-in, opening default /wp-login.php will now redirect to /wp-admin/ (Dashboard).

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fixed an issue where new separators could not be added in certain scenarios. Props to Mike D. for reporting the issue. Also fixed an issue where the organizer is broken when [WP 2FA](https://wordpress.org/plugins/wp-2fa/) plugin is active. Props to Batist L. for reporting the issue.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed an issue where repeated entries mentioning this module were created in WooCommerce 'plugin-woocommerce' error log. Props to Thanos K., Susanne R., KathArt I., Anthony V., Morten P., Gerhard R. and Paul O. for reporting the issue.

* **[FIXED in Free and Pro] ASE Settings**: 
  * **Fixed an incompatibility** with [WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels](https://wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce/) that was breaking their admin pages and features. Props to [@jtphelan](https://wordpress.org/support/users/jtphelan/) for reporting the issue [here](https://wordpress.org/support/topic/breaks-bulk-pdf-creation-of-woocommerce-pdf-invoices/) and [@webvizionph](https://wordpress.org/support/users/webvizionph/) for reporting it [here](https://wordpress.org/support/topic/incompatible-with-webtoffees-woocommerce-pdf-invoices-packing-slips/).
  * **Fixed PHP warning**. Props to [@mdeg79](https://wordpress.org/support/users/mdeg79/) for [reporting the issue](https://wordpress.org/support/topic/changing-the-login-url-breaks-functionality-on-the-front-end/page/2/#post-17807687).

* **[TRANSLATION]** Norwegian translation added (complete). ASE is now translated into 16 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **French**: ASE Free and Pro (completed). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Polish**: ASE Free and Pro (completed). Props to [@kosmity](https://profiles.wordpress.org/kosmity/) and [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Norwegian**: ASE Free and Pro (completed). Props to [Alf O.F.](https://profiles.wordpress.org/skoen/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Translation updates**: added new/improved translation for:
    * ASE Free: Danish, Hungarian, Italian, Dutch, Polish, Swedish, Ukrainian, Vietnamese, French.
    * ASE Pro: Hungarian, Italian, Dutch, Vietnamese, French.
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.
  
### 7.0.2 (2024.06.03) - ASE Free and Pro

* **[ADDED in Pro] Admin Interface >> Various Admin UI Enhancements**:
  * **Preserve Taxonomy Hierarchy**: Preserve the visual hierarchy of taxonomy terms checklist in the classic editor.
  * **Enable Dashboard Columns Settings**: Enable manual settings of dashboard columns layout in Screen Options. You can choose between 1 to 4 columns. 

* **[CHANGED in Free and Pro] Content Management >> Media Library Infinite Scrolling**: moved inside Admin Interface >> Various Admin UI Enhancements module.

* **[CHANGED in Free and Pro] Admin Interface >> Display Active Plugins First**: moved inside Admin Interface >> Various Admin UI Enhancements module.

* **[IMPROVED in Pro] Content Management >> Content Duplication**: enable duplication on non-public post types. Props to Matt B. for suggesting the improvement.

* **[FIXED in Free and Pro] ASE Settings**: fixed an issue that affects several number input fields, where if nothing is typed in, the default value as shown in the input placeholder is not properly saved. Props to [@inacodeweb](https://wordpress.org/support/users/inacodeweb/) for [reporting this issue](https://wordpress.org/support/topic/revisions-missing-in-astra-theme/) in the Revisions Control module. Also added missing placeholders for several number input fields.

* **[FIXED in Free and Pro] Content Management >> Media Replacement**: fixed a fatal error that happens in a certain scenario during replacement of a WebP image. Props to [@tormodg](https://wordpress.org/support/users/tormodg/) for [reporting the issue](https://wordpress.org/support/topic/trying-to-replace-webp-image-causes-fatal-crash/) with the details of the error.

* **[FIXED in Free and Pro] Log In/Out & Register >> Change Login URL**: 
  * **Comment moderation links** in email notification will now redirect to the login page instead of the `not_found` URL when the user is not already logged-in. Props to Mathijs V.D.B. for reporting the issue. 
  * **The "Login" link** in the password reset flow will now link to the custom login URL. Props to Sebastian A. for reporting the issue.

* **[FIXED in Pro] Log In/Out & Register >> Login Page Customizer**: fixed error message's font color in dark mode on the login form background. Props to Stewart R. for reporting the issue. Also fixed text color and spacing issues in other notices / messages.

* **[FIXED in Free and Pro] Admin Interface >> Wider Admin Menu**: fixed an issue where on Elementor settings, the header is covered by the admin menu. Props to Stijn for reporting the issue and sharing the CSS fix.

* **[FIXED in Free and Pro] Admin Interface >> Admin Menu Organizer**: fixed fatal error that occurs in certain scenario. This is related to changing the 'Posts' menu item's title. Props to [Goran](https://wordpress.org/support/users/goran63/) for [reporting this](https://wordpress.org/support/topic/crash-site-when-submitting-settings/).

* **[FIXED in Pro] Content Management >> Media Categories**: 
  - Fixed minor styling issues on the media frame in the frontend view, e.g. page builder edit mode. 
  - Fixed styling issue of the categories tree

* **[TRANSLATION]** Polish translation fully completed. ASE is now translated into 15 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **French**: ASE Free and Pro (completed). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Polish**: ASE Free and Pro (completed). Props to [@kosmity](https://profiles.wordpress.org/kosmity/) and [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Translation updates**: added new/improved translation for:
    * ASE Free: Hungarian, Italian, Swedish, Vietnamese, Polish
    * ASE Pro: German, Hungarian, Italian, Dutch, Vietnamese, Polish
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 7.0.1 (2024.05.27) - ASE Pro

* **[FIXED in PRO] Log In/Out & Register >> Login Page Customizer**: fixed responsive styling. Props to Stewart R. for reporting the issue. Also removed customization to body tag from the interim login modal window.

### 7.0.0 (2024.05.27) - ASE Free and Pro

* **[NEW in PRO] Log In/Out & Register >> Login Page Customizer**: added new module to easily customize the design of the login page. Props to Nami, Max Z., Vijayanand and John S. for suggesting this feature.

* **[IMPROVED in Pro] Content Management >> Content Order**: media library items can now be custom ordered. Props to Ric M. for suggesting the improvement amd sharing their use case. Note: the custom order can only be previewed in the list view of the media library, and not in the grid view.

* **[IMPROVED in Free and Pro] Optimizations >> Image Upload Control**: added a way to detect an invalid image that may cause errors. Props to Alberto O. Jr. for reporting the issue when importing Blocksy Child Starter theme that contains some demo content with an invalid image.

* **[CHANGED in Free and Pro] Content Management >> AVIF Upload**: this has been moved from a Pro module into a free module as WP core increases support for handling AVIF images. This module may be phased out in the future.

* **[FIXED in Free and Pro] Admin Interface >> Admin Menu Organizer**: 
  * Fixed a PHP warning. Props to Tomas R., Francoies G., Susanne R., David D., Richard E., Benjamin O., Justin K. and Cameron C. for reporting it. 
  * Fixed an edge case where custom menu order is not being respected because a theme was not returning `true` when hooking into `custom_menu_order hook`. Props to Paul B. for reporting the issue.
  * Fixed an issue where admin menu is cut-off at the bottom after clicking Show All, which may happen if there are a lot of menu items, e.g. from plugins. Props to Nguyen Q.H. for reporting the issue with a quick screencast.

* **[FIXED in Pro] Content Management >> Custom Content Types**: fixed a conflict with WP Grid Builder causing it's settings pages to break. Props to Darren L. for reporting the issue and bridging troubleshooting with WPGB support.

* **[TRANSLATION]** French translation completed. ASE is now translated into 15 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **French**: ASE Free and Pro (completed). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Polish**: ASE Free (completed) | ASE Pro (partial). Props to [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Translation updates**: added new/improved translation for Danish, Hungarian, Italian, Dutch, Swedish, Vietnamese, Hungarian, Italian and French.
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 6.9.13.2 (2024.05.27) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Change Login URL**: add trailing slash suffix in the custom login URL input field, and make sure if a trailing slash is added in the input, it will be stripped. Props to Wence W. for reporting the issue with the trailing slash that prompted this improvement.

* **[FIXED in Pro] Utilities >> Display System Summary**: fixed fatal error when trying to get the total size of the wp-content folder in certain scenarios. Props to Marcellus J. for reporting the issue and facilitating the troubleshooting process.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fixed an issue where the option to hide submenu items did not appear on click of the 'Hide' checkbox. Props to Geoff L. for reporting the issue when The Events Calendar Pro is active, showing it's submenu items. Unfortunately, this fix introduces a breaking change, so, you may need to re-do the hide settings for your submenu items done with v6.9.13. Please check on your site(s).

### 6.9.13.1 (2024.05.20) - ASE Free

* **[FIXED] Admin Interface >> Admin Menu Organizer**: fixed fatal error when installing / updating to v6.9.13. Props to [@timbre-design](https://wordpress.org/support/users/timbre-design/) and Kenneth L. for [reporting the issue](https://wordpress.org/support/topic/fatal-error-4684/).

### 6.9.13 (2024.05.20) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Change Login URL**: will now respect the Redirect After Login settings when a logged-in user is trying to open the custom login URL, i.e. will no longer redirect to the Dashboard /wp-admin/ and instead to the custom redirect URL for the user role. Props to Jacob O. for suggesting this improvement.

* **[IMPROVED in Pro] Admin Interface >> Admin Menu Organizer**: implemented the option to add new separators. Props to Claudio P., Gabriel A. and Brandon Z. for suggesting this feature. Also implemented the option to hide submenu items. Props to Bengt R., Dana S., Steven Y., Andreas A. and Bill J. for suggesting this feature.

* **[IMPROVED in Pro] Utilities >> Search Engine Visibility Status**: added an option to set the live / production site's URL, which will automatically prevent search engine visibility from being enabled on the development / staging site. Props to Gregory V. for suggesting this.

* **[IMPROVED in Pro] Security >> Limit Login Attempts || Security >> Password Protection**: added detected user IP address along with the header it was detected from in the IP whitelisting section, to make it easy to whitelist your IP address. Also added a way to detect the real IP address of users/visitors if the site is behind Cloudflare proxy. Additionally, also added a way to define the preferred header to use when detecting the IP address. Props to Sebastian A. for prompting these improvements.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: properly hide notices on WooCommerce and TranslatePress settings pages. Props to @designidit and Simon L. for reporting the issues.

* **[TRANSLATION]** French translation added. German translation completed. ASE is now translated into 15 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free and Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free and Pro (completed). Props to [@markussss](https://profiles.wordpress.org/markussss/), Bastian S. et al.
  * **Polish**: ASE Free (completed) | ASE Pro (partial). Props to [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **French**: ASE Free (completed) | ASE Pro (partial). Props to [Stéphan G.](https://profiles.wordpress.org/gongonzo/), [@jeanfrancoisdelvin](https://profiles.wordpress.org/jeanfrancoisdelvin/), [@srossignol](https://profiles.wordpress.org/srossignol/), [@lucashw](https://profiles.wordpress.org/lucashw/), [@skippy43](https://profiles.wordpress.org/skippy43/), [@anlip](https://profiles.wordpress.org/anlip/), [@agencefacton](https://profiles.wordpress.org/agencefacton/), [@injsbx](https://profiles.wordpress.org/injsbx/) et al.
  * **Translation updates**: added new/improved translation for Chinese, Vietnamese and Danish.
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 6.9.12 (2024.05.07) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Admin Menu Organizer**: if the menu title for 'Posts' has been modified, the word 'Posts' will be changed as well throughout wp-admin. Props to [@tinnyfusion](https://wordpress.org/support/users/tinnyfusion/) for [sharing the snippet](https://wordpress.org/support/topic/recent-update-renaming-posts/) this improvement is based upon.

* **[IMPROVED in Pro] Admin Interface >> Admin Menu Organizer**: the 'Contact' menu item from Contact Form 7 can now be always hidden. Props to Wence W. for reporting the issue.

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Last Login Column**: Date time format now uses the one set in Settings >> General. In ASE Pro, the last login column is now sortable.

* **[IMPROVED in Pro] Utilities >> Display System Summary**: added info on database, site, wp-content, plugins, themes and upload folders sizes. Props to [@unrealnfs](https://wordpress.org/support/users/unrealnfs/) for [suggesting this](https://wordpress.org/support/topic/request-dashboard-widget-plugin-my-simple-space/).

* **[IMPROVED in Pro] Utilities >> Email Delivery**: added the option to specify a custom reply-to name and email address. Props to @designidit for suggesting this.

* **[TRANSLATION]** Korean translation has been completed. ASE has now been translated into 14 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Korean**: ASE Free (completed) | ASE Pro (completed). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **Polish**: ASE Free (completed) | ASE Pro (in progress). Props to [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **German (Formal)**: ASE Free (completed) | ASE Pro (partial). Props to [@markussss](https://profiles.wordpress.org/markussss/) et al.
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.
  
### 6.9.11 (2024.05.07) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Admin Menu Organizer**: allow renaming the Posts menu item. Some projects require renaming this to News, Articles, etc. Props to Simone S. for the sharing the use case and suggesting this.

* **[IMPROVED in Pro] Optimizations >> Image Upload Control**: added GD library WebP conversion fallback for when Imagick is the active editor but has no WebP support. Props to Aleš S. for reporting the issue that prompted the improvement.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: improved protection against XSS (cross-site scripting) by escaping unescaped variables.

* **[CHANGED in Pro] Admin Interface >> Admin Columns Manager**: disable formatting that adds thousands separator (comma) to columns for number fields. Also made columns for ASE radio and number fields sortable.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Options Pages**: fixed an issue in Bricks builder query loop where querying for repeater field's sub-fields returned nothing. Props to Jacob O. for reporthing the issue.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fixed an issue where default and custom field columns are not able to be sorted properly despite being marked as sortable. Props to Sebastian A. and Uli L. for reporting the issue.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed deprecation notices on custom field group editing screen in PHP 8.2.

* **[TRANSLATION]** 2 new languages have been added. ASE has now been translated into 14 languages:
  * **Chinese (China)**: ASE Free and Pro (completed). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/).
  * **Hungarian**: ASE Free and Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (completed). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free and Pro (completed). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Polish**: ASE Free (completed) | ASE Pro (in progress). Props to [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).
  * **Korean**: ASE Free (completed) | ASE Pro (partial). Props to [@chazmlab](https://profiles.wordpress.org/chazmlab/).
  * **German (Formal)**: ASE Free (completed) | ASE Pro (partial). Props to [@markussss](https://profiles.wordpress.org/markussss/) et al.
  * **More strings** have been internationalized. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings, if you havent' done so already.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.

### 6.9.10.1 (2024.05.01) - ASE Pro

* **[FIXED in Pro] Optimizations >> Image Upload Control**: fixed an issue with WebP upload as reported by Konstantin T, where the resulting upload is a broken image. Also made sure WebP upload will be resized to the maximum dimension defined in the settings.

### 6.9.10 (2024.04.30) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Disable Dashboard Widgets**: added an option to disable the Welcome to WordPress widget/panel. Props to [@tinnyfusion](https://wordpress.org/support/users/tinnyfusion/) for [suggesting this](https://wordpress.org/support/topic/feature-request-remove-welcome-panel/) and providing the snippet to base this on.

* **[IMPROVED in Pro] Admin Interface >> Show Custom Taxonomy Filters**: this has been disabled for ASE's Media Categories to prevent showing the filter twice on the list view of the media library.

* **[IMPROVED in Pro] Optimizations >> Image Upload Control**: WebP conversion now works on sites where the active editor for media handling is WP_Image_Editor_Imagick. Props to Konstantin T. for reporting the issue which prompted this improvement.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: remove irrelevant CPTs from ASE, WooCommerce and page builders from the field group's Placement >> Post Types drop down list, and sort the list alphabetically.

* **[IMPROVED in Pro] Admin Interface >> Admin Menu Organizer**: when trying to hide a menu item with the "Always hide for user role(s) >> all roles except" option for a certain role, e.g. Administrator, it will not work correctly if a user has that certain role and another role (e.g. Web Designer), which can be assigned via the Utilities >> Multiple User Roles module. Previously, the menu item will also be hidden for users with multiple roles that includes that certain role. This release fixes that issue, i.e. a user with multiple roles will now see the correct behaviour. Props to Ingo R. for reporting the issue in detail and also posting the code fix!

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed an issue in WYSIWYG field where inserting an image with caption will output the plain shortcode in the editor with the image placed within the shortcode. Props to Jacob O. for reporting the issue.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Taxonomies**: fixed PHP warning in custom taxonomies list table.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fixed empty value being returned for ASE hyperlink field in certain scenarios.

* **[TRANSLATION]** ASE has now been translated into 12 languages:
  * **Chinese (China)**: ASE Free and Pro (**completed**). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Portuguese (Brazil)**: ASE Free and ASE Pro (**completed**). Props to [Dennis F.](https://profiles.wordpress.org/dnn/).
  * **Hungarian**: ASE Free and Pro (**completed**). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Vietnamese**: ASE Free and Pro (**completed**). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Italian**: ASE Free and Pro (**completed**). Props to [Fabio P.](https://profiles.wordpress.org/fabioperri/) et. al.
  * **Dutch**: ASE Free (completed) | ASE Pro (partial). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) and [Peter S.](https://profiles.wordpress.org/psmits1567/) et al.
  * **Polish**: ASE Free (completed) | ASE Pro (partial). Props to [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Swedish**: ASE Free (completed) | ASE Pro (partial). Props to [Robert M.](https://profiles.wordpress.org/robertmichalski/) and [Tor-Bjorn F.](https://profiles.wordpress.org/tobifjellner/).
  * **Danish**: ASE Free (completed) | ASE Pro (partial). Props to [Morten E.L.](https://profiles.wordpress.org/ellegaarddk/), [Helgi P.](https://profiles.wordpress.org/helgipetersen/) and [Kurt M.A.](https://profiles.wordpress.org/moskjaer/).

  * **More strings** have been internationalized. Props to [@cooper08](https://wordpress.org/support/topic/missing-translations-notices-show-all-less/) and [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/) for pointing them out. @Translators, please visit the respective project pages for the Free and Pro versions to translate the new strings.
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.
  * Note: simply change the language in your user profile editing screen to see the translations in action.

### 6.9.9 (2024.04.23) - ASE Free and Pro

* **[TRANSLATION]** ASE has now been translated into the following languages:
  * **Chinese (China)**: ASE Free (completed) | ASE Pro (partial). Props to [@bricksvip](https://profiles.wordpress.org/bricksvip/).
  * **Dutch**: ASE Free (completed) | ASE Pro (partial). Props to [Toine R](https://profiles.wordpress.org/toineenzo/) et al.
  * **Hungarian**: ASE Free (completed) | ASE Pro (completed). Props to [R. József G.](https://profiles.wordpress.org/radicsjg/).
  * **Polish**: ASE Free (completed) | ASE Pro (partial). Props to [Dariusz Z.](https://profiles.wordpress.org/dariobros/).
  * **Portuguese (Brazil)**: ASE Free (completed) | ASE Pro (completed). Props to [Dennis F.](https://profiles.wordpress.org/dnn/).
  * **Portuguese (Portugal)**: ASE Free (completed) | ASE Pro (partial). Props to [Ricardo C.](https://profiles.wordpress.org/madebyuh/).
  * **Ukrainian**: ASE Free (completed) | ASE Pro (partial). Props to [Irina](https://profiles.wordpress.org/irinashl/).
  * **Urdu**: ASE Free (completed) | ASE Pro (partial). Props to [Ayyaz A.](https://profiles.wordpress.org/ayyazahmad/).
  * **Vietnamese**: ASE Free (completed) | ASE Pro (completed). Props to [Hoang N.Q.](https://profiles.wordpress.org/nguyenquanghoang/).
  * **Interested to help translate or improve the translation?** Please go to [https://translate.wpase.com](https://translate.wpase.com) for more info.
  
* **[IMPROVED in Free and Pro] Content Management >> Content Duplication**: will properly detect if a 'product' post type is registered by WooCommerce or not. If it's registered by WooCommerce, content duplication will use WooCommerce's native feature. If it's not registered by WooCommerce, e.g. by ACF, then ASE's content duplication feature will be used. Props to [@rikhen](https://wordpress.org/support/users/rikhen/) for [reporting the issue](https://wordpress.org/support/topic/content-duplication-not-always-working-for-cpts/) which prompted the improvement.

### 6.9.8 (2024.04.15) - ASE Free and Pro

* **[TRANSLATION for Free and Pro]** ASE Free is now available in Dutch (props to Toine R. et al.) and Ukrainian (props to Irina), and ASE Pro is available in Hungarian (props to R. József G.) and Brazilian Portuguese (props to Dennis F.)! For ASE Free, once you switch your language to Nederland or Українська in your profile settings, please go to Dashboard >> Updates >> Translation and click on "Update translation" to download the translation from wordpress.org. Pro version already includes the translation. Interested to have ASE in your language(s)? Please go to [https://translate.wpase.com ](https://translate.wpase.com)on how to get involved. Thanks!

* **[INTERNATIONALIZATION]** More strings have been internationalized, so they are available for translation into various languages.

* **[CHANGED in Free]** Remove the nudge to donate in support of the development for the free version of ASE. This has been replaced with a simple ask to share about ASE with your communities, which is also a way to provide support. Overall, the support nudge at the top of ASE settings page has been simplified. So, if you find ASE has been useful on your sites and in your workflow, hopefully you will consider adding [a nice review](https://wordpress.org/plugins/admin-site-enhancements/#reviews), providing feedback, sharing about ASE or helping with translation efforts. Please do understand that by now, probably more than 300 hours have been spent on developing the free version of ASE over the course of one and a half year.

* **[FIXED in Pro] Content Management >> Content Duplication**: fixed a bug where all user roles were selected after saving changes to ASE settings, despite unchecking several user roles before saving. Props to David M. for spotting and reporting the issue.

* **[FIXED in Pro] Content Management >> Media Categories**: fixed CSS so filter bar items in the list view does not spill over to the second line.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: properly output "Hide until toggle" text without raw PHP code.

### 6.9.7 (2024.04.07) - ASE Free and Pro

* **[TRANSLATE ASE] Are you interested to see ASE in your language?**: ASE has been fully internationalized and ready for localization / translation. If you're interested to get involved, please head over to [https://translate.wpase.com](https://translate.wpase.com/) for more info. Props to [Toine R.](https://wordpress.org/support/users/toineenzo/) for leading the Dutch translation efforts and [@radicsjg](https://profiles.wordpress.org/radicsjg/) for the Hungarian translation.

* **[IMPROVED in Pro] Content Management >> Content Duplication**: added an option to choose on which post type(s) duplication is enabled. Props to Paul R. for prompting the improvement after reporting seeing double 'Duplicate' link on the ads listing of Advanced Ads plugin, which has it's own 'Duplicate' feature for their ads.

* **[IMPROVED in Pro] Utilities >> Redirect 404**: added an option to redirect to a custom URL instead of the homepage.

* **[CHANGED in Free and Pro] Utilities >> Search Engines Visibility Status**: Removed visibility status notice. Admin bar status should be enough. Props to Marco R. for prompting the change.

* **[CHANGED and IMPROVED in Free and Pro] Admin Interface >> Show Custom Taxonomy Filter**: this has been taken out of Enhance List Tables module and is now it's own module. In ASE Pro, it's also possible to show additional filter(s) for non-hierarchical taxonomies, e.g. Post Tags.

* **[CHANGED in Free] ASE Settings**: removed sponsorship ask/nudge. Simplify this nudge into an ask to add a review or share about ASE.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Options Pages**: fixed an issue where WYSIWYG field that is part of an options page will output the wrong content when rendered inside Breakdance builder's header. Props to Darren L. for reporting the issue with screencasts and facilitating troubleshooting further.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: Fixed PHP warning in WYSIWYG field. Fixed true/false field not showing SVG icon in Elementor.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fix for Elementor Templates >> Types column having duplicate values. Props to Steven Y. for reporting the issue.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fix activate / deactivate toggle on snippets listing page not working properly, i.e. returning 'yes' / 'no' page instead of actually toggling and stays on the listing page. Props to Darren L. for reporting the issue.

* **[FIXED in Pro] Admin Interface >> Admin Menu Organizer**: fixed PHP deprecation notice.

### 6.9.6.2 (2024.04.01) - ASE Pro

* **[FIXED in Pro] Content Management >> Content Order**: fixed PHP warnings as reported by Yoshihiro T. and a developer at mez#####e.co.

### 6.9.6.1 (2024.04.01) - ASE Pro

* **[FIXED in Pro] Content Management >> Custom Content Types >> Options Pages**: fixed an issue where getting an option page's option value in the frontend returns empty value. Props to Darren for reporting this with a short but clear screencast that illustrates the issue in Breakdance builder.

### 6.9.6 (2024.04.01) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Redirect After Login & Logout**: make it possible to redirect to the homepage by leaving the redirection slug input blank. Props to [@mateuszkalamarz](https://wordpress.org/support/users/mateuszkalamarz/) for [reporting the issue](https://wordpress.org/support/topic/redirect-after-login-doesnt-allow-front-page/) and prompting the improvement.

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Change Login URL**: properly redirect /wp-login (without .php) to the /not_found/ URL. It was showing the login form on some scenarios. Props to Artur M. for reporting this and help with troubleshooting.

* **[IMPROVED in Pro] Content Management >> Content Order**: it's now possible to enable custom ordering for post types that are not hierarchical and don't support page attributes, e.g. posts and WooCommerce products. Props to Kay L., Eirini Z. and Hampus E. for suggesting this improvement.

* **[IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: Post Parent and Menu Order are now included in Default columns for all post types.

* **[FIXED in Free and Pro] Disable Components >> Disable Gutenberg**: fixed an issue where the "Add Form" button from Gravity Forms is not present next to "Add Media" button in the post/page edit screen when gutenberg / block editor is disabled for a post type. Props to [Kazam Creative](https://wordpress.org/support/users/goldenagemedia/) for [reporting the issue](https://wordpress.org/support/topic/bug-add-shortcode-to-visual-editor-buttons-disappear/).

* **[FIXED in Pro] Content Management >> Custom Content Types**: in some scenarios, the WYSIWYG field is not responsive, and thus, not usable when editing a post using that field type with the block editor. This is now fixed. Props to Philipp Z. for reporting the issue and facilitating the troubleshooting.

* **[FIXED in Free and Pro] ASE Settings**: fixed PHP warning reported by [@osblaga](https://wordpress.org/support/users/osblaga/) in [this thread](https://wordpress.org/support/topic/error-when-activating-wp-ase-plugin/).

### 6.9.5 (2024.03.25) - ASE Free and Pro

* **[IMPROVED in Free and Pro] Admin Interface >> Enhance List Tables**: added the option to show file size column in the media library list view. Props to [@tinnyfusion](https://wordpress.org/support/users/tinnyfusion/) for [suggesting this](https://wordpress.org/support/topic/add-file-size-column-to-media-library/) and providing the code snippet to base this upon.

* **[IMPROVED in Free and Pro] Security >> Limit Login Attempts**: change input type for the limit numbers from text to number.

* **[IMPROVED in Free and Pro] Improve security**: properly escape unescaped $variables on ASE settings page and in various modules.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: when disabling safe mode via the admin bar icon/toggle fails, a message is now shown that links to the [documentation](https://www.wpase.com/documentation/code-snippets-manager/) on how to disable it manually via wp-config.php. Props to Nelson T. for prompting the improvement.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: you can now choose default value(s) for radio, select and checkbox fields. Props to Max F. for pointing out this missing option.

* **[IMPROVED in Pro] Disable Components >> Disable Gutenberg & Disable Comments**: added additional option to "Disable only on" or "Disable except on" the selected post types. This should make it easier to include or exclude only certain post types despite new post types being registered on the site. i.e. no need to continually check newly added post types to disable Gutenberg / comments on.

* **[IMPROVED in Pro] Content Management >> Media Categories**: css adjustment to fix toolbar items spilling over to the second line in the grid view on certain scenarios.

* **[IMPROVED in Free and Pro] Increase code modularity** across all modules. One is by switching from autoloading vendor libraries using Composer to loading them when the corresponding modules are enabled. The other by breaking down module category classes, e.g. class-content-management.php, that has grown large as new features and modules are added to each category, into smaller ones, e.g. class-content-duplication.php.

* **[CHANGED in Free and Pro] Disable Components >> Disable Block-based Widgets Settings Screen**: is now moved under Disable Smaller Components module.

* **[CHANGED in Pro] Content Management >> Terms Order**: custom terms ordering was previously limited to users with 'manage_options' capability, e.g. administrator, and now is available for users with 'manage_categories' capability, e.g. administrator, editor, shop_manager. Props to Marco R. for the feedback.

* **[FIXED in Free and Pro] Security >> Limit Login Attempts**: Fixed an issue where sorting by date-time (Last Attempt On) in the failed login attempts log table did not work as expected. Going forward, the log table will by default be sorted by Last Attempt On in descending order. Props to a user whose name escapes memory at the moment!

* **[FIXED in Free and Pro] ASE Settings**: fixed an issue where TinyMCE Visual editor is not responsive / working in Firefox browser. e.g. in Custom Admin Footer Text and Maintenance Mode modules. Props to Michael S. for reporting the issue. Replaced Text tab/editor with a code button to perform raw HTML editing.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed an issue where TinyMCE Visual editor for snippet description is not responsive / working in Firefox browser. Props to Michael S. for hinting at the issue. Replaced Text tab/editor with a code button to perform raw HTML editing.

* **[FIXED in Free and Pro] Admin Interface >> Admin Menu Organizer**: fixed a PHP warning that occurs in certain scenarios.

* **[FIXED in Pro] Utilities >> Maintenance Mode**: fixed an issue where background image is not properly loaded certain scenarios.

### 6.9.4 (2024.03.18) - ASE Free and Pro

* **[IMPROVED in Pro] Content Management >> Media Categories**: when in grid view, parent categories are now properly assigned when uploading new media files to a sub-category view, i.e. after clicking on a sub-category in the categories tree. Props to Alin T. for suggesting the improvement. Also simplified the UI by removing the "Media Categories" title above the categories tree.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: "This is a required field" validation now works properly for File, Radio and Checkbox fields, while removing it from the True False field, as an empty checkbox is a valid value as well. Props to Michael S. for testing and reporting this.

* **[IMPROVED in Pro] Utilities >> Maintenance Mode**: add an option to set the title of the maintenance page, which is visible in browser tab title. Props to Gálik J. for the suggestion. Also enabled wpautop() for the output of heading and description. i.e. line breaks now appear on the maintenance page.

* **[IMPROVED in Free and Pro] Disable Components >> Disable Smaller Components >> Disable Emoji Support**: will also now disable conversion of text emojis into picture emojis, a.k.a. smilies. Props to [@ofmarconi](https://wordpress.org/support/users/ofmarconi/) for [reporting the issue](https://wordpress.org/support/topic/even-with-the-option-to-disable-emoji-it-continues-to-be-changed-on-the-front-en/) on their Elementor site.

* **[IMPROVED in Pro] Admin Interface >> Custom Admin Footer Text**: added "Add Media" and "Visual | Text" switcher.

* **[FIXED in Free and Pro] ASE Settings**: fixed an issue in some scenarios, of not being able to enter any value in the TinyMCE Visual editor. Props to Nelson T. who first reported the issue in the Custom Admin Footer Text module and being patient and supportive throughout the troubleshooting process. Additional props to Gustavo F. for reporting the same issue in the Maintenance Mode module and facilitating the troubleshooting process as well.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed an issue in some scenarios, of not being able to enter any value in the snippet description's TinyMCE Visual editor. Props to Michael S. for reporting the issue.

* **[FIXED in Free and Pro] Log In/Out & Register >> Change Login URL**: fixed an issue where on some circumstances, after a successful login, redirection to the default / custom 404 page instead of the admin dashboard occurs. Props to Ignazio D.M. for reporting the issue.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed an issue with Elementor integration where Loop Grid widget is not showing the correct set of CPT content. Props to Thomas B. for reporting the issue in great detail (problematic URLs and annotated screenshots) and facilitating the troubleshooting process.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed a couple of deprecation notices when in PHP v8.2 for creating dynamic property.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed an issue where custom field group's extra settings were not properly being saved.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: fixed missing settings panels in Loco Translate, e.g. Add New Language panel, as they are using divs with .notice class, which were hidden inside the Notices panel in the admin bar. These panels are now excluded from being hidden. Props to [@inboundbe](https://wordpress.org/support/users/inboundbe/) and [@kirollosa](https://wordpress.org/support/users/kirollosa/) for [reporting the issue](https://wordpress.org/support/topic/issue-with-loco-translate-2/).

* **[FIXED in Free and Pro] Content Mangement >> Media Replacement**: fixed an issue where on certain scenarios, when editing a page / post / CPT with the block editor, there were media frame layout issues when trying to change the featured image. Props to Philipp Z. for reporting the issue and facilitating troubleshooting, which includes recording a 43 seconds screencast of the issue.

* **[FIXED in Free and Pro] Optimizations >> Revisions Control**: fixed an issue when entering 0 revisions as the limit, after saving changes, it will revert back to the default 10 revisions. Props to [@pressthemes1](https://wordpress.org/support/users/pressthemes1/) for [reporting the issue](https://wordpress.org/support/topic/all-setting-revisions-control-to-0/).

### 6.9.3 (2024.03.12) - ASE Free and Pro

* **[IMPROVED in Pro] Utilities >> Maintenance Mode**: enable WYSIWYG editor for heading and description, which allows for links, some custom HTML and custom styling if you need it. Also added ability to add custom CSS along with addition of a page overlay div element, which you can style to overlay the background image, e.g. make it darker or brighter to increase contrast with the maintenance message.

* **[IMPROVED in Pro] Content Management >> Content Duplication**: added duplication link in the publishing section of post edit screen, both in the classic editor and block editor. Also added an option to choose on which locations to show the duplication link: list view post action row / admin bar / edit screen publish section.

* **[FIXED in Free and Pro] ASE Settings**: prevent JS error when iThemes Security Pro / Solid Security Pro plugin is active.

* **[FIXED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed an issue where returning the value of a custom field inside a GenerateBlocks Query Loop returns an empty value after update to v6.9.1. Props to Arne O. for reporting the issue in details and patiently facilitating the troubleshooting process. This also fixed a similar issue with showing custom field values in Breakdance Post Loop Builder. Props to Philipp Z. for reporting it.

* **[CHANGED in Pro] Content Management >> AVIF Upload**: remove link to avif.io, which is no longer online. This module will likely be removed once WP v6.5 is released and is widely adopted, as built-in AVIF support will be [included in WP core](https://make.wordpress.org/core/2024/02/23/wordpress-6-5-adds-avif-support/).

### 6.9.2 (2024.03.07) - ASE Free and Pro

* **[CHANGED in Pro] Content Management >> Custom Content Types**: custom field groups and options pages creation is now enabled by default when Custom Content Types module is enabled. Also updated module description and settings to reflect this.

* **[FIXED in Free and Pro] Optimizations >> Image Upload Control**: added checks to prevent PHP fatal error and warnings when uploading non-image files and WebP conversion is enabled. Props to Peter J. and Gilang R. for reporting the fatal error issue.

* **[FIXED in Free and Pro] Security >> Limit Login Attemtps**: fixed an issue where under certain conditions, the login page would endlessly reload making it impossible to login. Props to [eangulus](https://wordpress.org/support/users/eangulus/), [andreawriessnegger](https://wordpress.org/support/users/andreawriessnegger/), [eangulus](https://wordpress.org/support/users/eangulus/), Max F. and Eric for reporting this patiently and in great detail in the wp.org [support forum](https://wordpress.org/support/topic/something-not-right-with-limit-login-attempts/) and via email.

### 6.9.1 (2024.02.29) - ASE Free and Pro

* **[IMPROVED in Pro] Content Management >> Custom Content Types**: added the ability to create Options Pages, which for example, you can use to allow clients to easily edit parts of the website, e.g. office address, organization mission statement, etc. This has also been integrated with Oxygen, Bricks, Breakdance and Elementor, i.e. you can see fields from options pages showing up as dynamic data source.

* **[IMPROVED in Pro] Optimizations >> Image Upload Control**: in previous version, BMP, PNG and JPG images will be converted first to WebP before being resized  to the specified. Now, uploads will be resized first before conversion to WebP. This will result in even smaller file size while maintaining image quality. Props to Adryan for the astute observation and detailed reporting of the issue.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: the WYSIWYG field now has a 'code' button to show the HTML version of content being added / copy-pasted. Useful for cleaning up from unwanted code, e.g. CSS classes, weird formatting, etc. Props to Michael S. for suggesting something similar.

* **[IMPROVED in Pro] Custom Code >> Code Snippets Manager**: snippet description editor now has the Visual-Text tabs. Useful for cleaning up description from unwanted code, e.g. CSS classes, weird formatting, etc, when the description is copy-pasted from elsewhere. Props to Michael S. for suggesting this.

* **[IMPROVED in Free and Pro] Log In/Out & Register >> Change Login URL**: will now correctly output an error message on failed login: "Error: Invalid username/email or incorrect password.". This is a custom message that does not give away valuable info to potential hackers. e.g. does not indicate that the username is correct but password is wrong. Props to Sven K. for reporting the issue.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed missing "Snippet Categories" sub-menu item after the last release. Props to Herbert S. for reporting the issue.

* **[FIXED in Free] Utilities >> Maintenance Mode**: properly hide the 'Image' and 'Color' background options in the free version. Props to Michael I. for reporting the issue.

* **[FIXED in Pro] Content Management >> Custom Content Type**: custom taxonomy key / slug was limited to 20 characters despite the UI / description saying it's limited to 32 characters, per the hard limit set by [register_taxonomy()(https://developer.wordpress.org/reference/functions/register_taxonomy/). Props to Michael S. for reporting the issue.

### 6.9.0.1 (2024.02.26) - ASE Pro

* **[FIXED in Pro] Content Management >> Content Duplication**: fixed a bug that can cause PHP warning or fatal error. Props to Benjamin P., Elon R., and Yoshihiro T. for reporting the issue immediately after the release of v6.9.0.

### 6.9.0 (2024.02.26) - ASE Free and Pro

* **[NEW in Free and Pro] Admin Interface >> Custom Admin Footer Text**: Customize the text you see on the footer of wp-admin pages.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: added 4 **new field types: number, radio, checkbox and (image) gallery**. All of them works with the repeater field and have been integrated with Bricks, Breakdance, Oxygen and Elementor, and also fully supported in the Admin Columns Manager module.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Field Groups**: added option to choose preview size for file (image) field type. You can choose between thumbnail (cropped/square) or medium (uncropped). Some users prefer the cropped version as it's smaller file size, while some other users prefer the uncropped version which for example, shows brand logos nicely.

* **[IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: list table is now responsive, i.e. looks and works nicely on mobile. Props to Pablo R. for reporting the issue when vieweing on mobile.

* **[IMPROVED in Pro] Log In/Out & Register >> Change Login URL**: added option to choose which URL to redirect to when visitors try to access default WP login URLs/slugs (/wp-admin/, /admin/, /wp-login.php, /login/). Props to Igor P. for the suggestion.

* **[IMPROVED in Pro] Content Management >> Content Duplication**: added option to choose which user role(s) the duplication feature is available for. Props to Igor P. for the suggestion.

* **[IMPROVED in Pro] Content Management >> Custom Content Types >> Custom Post Types**: small usability tweak. Add a close icon in the icon picker of the custom post type creation / edit screen. Props to Steven Y. for the suggestion.

### 6.8.3.1 (2024.02.20) - ASE Pro

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fixed an issue where column widths are not properly applied.

### 6.8.3 (2024.02.20) - ASE Free and Pro

* **[NEW in Pro] Content Management >> Custom Content Types**: added **integration of ASE custom fields as Elementor builder's dynamic data provider** All field types, except for repeater fields, can be displayed using the Text Editor widget. It's also possible to use compatible field types as data sources for URLs, files/media, colors, numbers, image and video.

* **[IMPROVED in Free and Pro] Admin Interface >> Hide Admin Notices**: mouse cursor now changes to a pointer (hand icon pointing upwards) when hovering on the 'Notices' menu in the admin bar. Props to @cvladan for [suggesting this](https://github.com/qriouslad/admin-site-enhancements/issues/10).

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed PHP warning on login page. Props to Sunny L., Linda L. and Francois G. for reporting the issue along with the full error entry that helped with troubleshooting.

* **[FIXED in Free and Pro] ASE Settings Page**: fixed incompatibility with US Weather Widget plugin (a plugin that's last updated 6 years ago) causing the settings page to load partially and become non-functional. Props to James B for reporting the issue.

### 6.8.2 (2024.02.16) - ASE Free and Pro

* **[IMPROVEMENT in Free and Pro] Content Management >> Content Duplication**: added an option to choose where to redirect after performing duplication of a post. Choices are the edit screen of the new/duplicate post, or the list view for the post type, e.v. View all posts. Props to [@americancreativeconsulting](https://wordpress.org/support/users/americancreativeconsulting/) for [suggesting](https://wordpress.org/support/topic/feature-request-duplicate-page-without-opening-to-it/) this improvement.

* **[FIXED in Free and Pro] Content Management >> Media Replacement**: fixed an issue where media replace is not working in non-English wp-admin. Also fixed an issue where the "Drop files to upload" blue overlay is not closing after drag-and-dropping a file in the media replacement modal window. Props to Andi P. for reporting these issues in details and with screencasts and also facilitating troubleshooting further.

* **[FIXED in Free and Pro] Log In/Out & Register >> Last Login Column**: fixed PHP warning when logging out of temporary account created with [Temporary Login Without Password](https://wordpress.org/plugins/temporary-login-without-password/) plugin. Props to Alex S. for reporting the issue.

* **[FIXED in Pro] Terms Order**: fixed PHP notice and warning on certain scenarios. Props to Brian N. for reporting the issue with a copy of the error log entry, which helped with troubleshooting.

* **[FIXED in Pro] Custom Code >> Code Snippets Manager**: fixed an issue where users are not able to logout in certain scenarios when Code Snippets Manager is active. Props to HMDIA for reporting the issue and assisting with troubleshooting.


### 6.8.0 (2024.02.13) - ASE Free and Pro

* **[NEW in Free and Pro] Log In Log Out >> Login ID Type**: Restrict login ID to username or email address only.

* **[IMPROVED in Free and Pro] Custom Code >> Custom Admin CSS**: change the hook in use to print custom CSS on page from 'admin_enqueue_scripts' to 'admin_print_footer_scripts'. This increases the chance that the custom CSS will override previously declared CSS.

* **[IMPROVED in Free and Pro] Utilities >> Display System Summary**: added server IP address. Props to Koen A. for the suggestion.

* **[IMPROVED in Free and Pro] Security >> Limit Login Attempts**: improve detection of user's IP address, especially for cases where the user is behind a proxy server. Prevents locking out all users from that proxy server when one is being locked out. Props to Gunnar A. for detecting and reporting the issue and suggesting a solution to help fix that.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fixed an issue where columns of certain post type listings are showing empty cells / values. This was originally reported in detail with GeoDirectory's Packages listing by Martin K., which also helped with the troubleshooting.

* **[FIXED in Free and Pro] ASE settings page**: fixed plugin conflict between ASE and WordPress Mentions Légales plugin causing ASE settings page to be blank and dysfunctional. Props to Gilbert G. for reporting and helping with troubleshooting.

* **[FIXED in Pro] Admin Interface >> Admin Columns Manager**: fixed PHP fatal error when there's a product that does not have an SKU and the Products column is shown in WooCommerce Orders page. Props to Jacob E. for reporting the issue and helping with troubleshooting.

* **[FIXED in Free and Pro] Content Management >> Content Duplication**: fixed PHP warnings as reported by [@webvizionph](https://wordpress.org/support/users/webvizionph/) in details [here](https://wordpress.org/support/topic/warning-attempt-to-read-property-post_type-on-null-in-home/).

* **[FIXED in Pro] Content Management >> Media Replacement**: fixed an issue where the "Select New Media File" button does not work in the grid view of the media library when viewing a media item and trying to perform media replacement. Props to Stefan P. for reporting the issue.

* **[FIXED in Pro] Content Management >> Media Replacement**: fixed an issue where the new image (for replacement) is not being shown in the frontend view of the site and in the block editor's Featured Image section. Props to David H. for their investigative work detailing the issues and the scenarios under which they appear, and also for helping with the troubleshooting process.

### 6.7.0 (2024.02.07) - ASE Free and Pro

* **[NEW in Free and Pro] Admin Interface >> Display Active Plugins First**: new module to display active / activated plugins at the top of the Installed Plugins list. Useful when your site has many deactivated plugins for testing or development purposes.

* **[IMPROVED in Pro] Content Management >> Custom Content Types**: added **integration of ASE custom fields as Breakdance builder's dynamic data provider** with full support for the repeater field. All field types can be output using a simple text element in Breakdance, and it's also possible to output compatible field types as URLs and be used in the image and video elements for further customization. Relationship field support in Post Loop Builder requires intervention from Breakdance team, and ASE has initiated the process towards that end.

* **[IMPROVED in Pro] Admin Interface >> Admin Columns Manager**: added a column containing list of products being ordered for WooCommerce Orders listing page. Props to Jacob E. for the suggestion.

* **[FIXED in Free and Pro] Admin Interface >> Hide Admin Notices**: will now only be active in wp-admin pages and not on the frontend. Changed the hook used to load inline CSS styles that was causing the PHP warning issue of "headers already sent" when viewing the Customizer, as reported by [@socialsparkmedia](https://wordpress.org/support/users/socialsparkmedia/) and [@elonreynolds](https://wordpress.org/support/users/elonreynolds/) [here](https://wordpress.org/support/topic/php-warning-using-divi-theme-with-ase-pro-6-5-0/) and [here](https://wordpress.org/support/topic/warning-message-when-opening-customizer/). Additionally, when Breakdance builder is actively managing a post/page, a similar error was also reported and has also been fixed. Props to Val J. for reporting this issue in detail and facilitating troubleshooting.

* **[FIXED in Free and Pro] Content Management >> Media Replacement**: fixed a bug that occurs when an image of a different (mime) type than the image to replace is being used for the replacement. A check has been added that will output a warning when that happens, and will disable the Perform Replacement button until an image of the same type is selected. Props to David H. and Andi P. for reporting issues related to this bug, and help with providing detailed information to help with troubleshooting.

* **[FIXED in Free and Pro] Utilities >> Display System Summary**: fixed PHP warning when there's an error getting database information via mysqli_connect(). Props to Val J. for reporting the issue and helping with troubleshooting.

### 6.6.0 (2024.01.30) - ASE Free and Pro

* **[NEW][Free][Pro] Utilities >> Search Engines Visibility Status**: new module to show admin bar status and admin notice when search engines are set to be discouraged from indexing the site, which is set through a "Search engine visibility" checkbox in Settings >> Reading. Props to David S. for suggesting the feature and providing the code snippet (generated with ChatGPT Plus) that this module was based on.

* **[FIXED][Free][Pro] Security >> Limit Login Attempts**: fixed an issue where lockout is not effecive after reaching the limit of failed login attempts and being shown the lockout screen. Reloading the lockout screen would simply show the login form again, even when lockout period is not over yet. This happens only when using default login URL at /wp-login.php. Props to [@dywoo02](https://wordpress.org/support/users/dywoo02/) for [reporting this](https://wordpress.org/support/topic/limit-login-attempts-limit-login-attempts-and-correct-access-data/) and doing a thorough investigative work. That gave a clue and help for looking at the right places in the code to fix the issue.

* **[FIXED][Free][Pro] Log In/Out & Register >> Change Login URL**: undo a change in v6.5.1 that was returning the custom login URL for wp_login_url(). This caused common login URLs like /admin, /wp-admin and /login to redirect to the custom login URL, thus making it pointless to have a custom login URL. With this reversion, those common long URLs will redirect to /not_found/ 404 error page. Props to Hayato for reporting this issue after updating to v6.5.1.

* **[FIXED][Pro] Local User Avatar**: fixed PHP warning when opening the block editor and in other scenarios. Props to Rio M. and Brian W. for reporting the issue.

### 6.5.1 (2024.01.29) - ASE Free and Pro

* **[IMPROVED][Pro] Content Management >> Custom Content Types**: added **integration of ASE custom fields as Oxygen and Bricks builder's dynamic data provider**. The repeater and relationship fields are fully supported in Bricks builder's Query Loop. Repeater field suport in Oxygen builder's Repeater module requires implementation from within Oxygen builder's plugin, so, if you'd like to see that happen, please comment / vote / request it at [this Github issue](https://github.com/soflyy/oxygen-bugs-and-features/issues/3499), which is their official feature request channel.

* **[IMPROVED][Free][Pro] Admin Interface >> Clean Up Admin Bar**: when 'Howdy' is hidden and "Avatar Display" is disabled in Settings >> Discussion, the profile menu will also hide the default user avatar and the profile dropdown will no longer show empty space where the avatar was shown before. Props to Stijn V. for reporting the issue and providing the one-line code fix for it.

* **[IMPROVED][Free][Pro] Log In/Out & Register >> Change Login URL**: after resetting password, the "Log in" link in "Your password has been reset. Log in" will now link to the custom login URL. More generally, wp_login_url() will now return the custom login URL. Props to [@timbre-design](https://wordpress.org/support/topic/change-login-url-disables-reset-password-function/) for reporting the issue.

* **[FIXED][Free][Pro] Log In/Out & Register >> Change Login URL**: when a user is logged-in and attempt to visit the custom login URL, the user was being logged out and redirected to the login page. Now, the user be redirected to /wp-admin/ and will stay logged-in. Props to [@boah123](https://wordpress.org/support/users/boah123/) for [reporting the issue](https://wordpress.org/support/topic/change-login-url-disables-remember-me/) with detailed steps to reproduce it.

* **[FIXED][Pro] Utilities >> Local User Avatar**: fixed fatal error in certain scenario when $user object is not being returned.

* **[FIXED][Pro] Admin Interface >> Admin Menu Organizer**: fixed an issue where /wp-admin/ (Dashboard) is not accessible upon successful login when this module is active. Props to John M. for reporting the issue and help with narrowing down the probably cause.

* **[FIXED][Pro] Admin Interface >> Admin Columns Manager**: fixed an issue with SureMembers Access Groups listing page not showing the group title and action links when Admin Columns Manager is active. Props to Volker D. for reporting the issue and facilitating troubleshooting.

* **[FIXED][Pro] Admin Interface >> Admin Columns Manager**: nothing is listed in the Custom Fields section when organizing columns for WooCommerce Orders (shop_order), including custom fields from ACF. Props to Jacob E. for reporting the issue and providing additional information to help troubleshoot the issue.

### 6.5.0 (2024.01.22) - ASE Free and Pro

* **[NEW][Free][Pro] Content Management >> Media Library Infinite Scrolling**: Re-enable infinite scrolling in the grid view of the media library. Useful for scrolling through a large library. Props to [@benbaudart](https://wordpress.org/support/users/benbaudart/) for [suggesting this](https://wordpress.org/support/topic/feature-request-infinite-scroll-in-media-library/).

* **[IMPROVED][Pro] Admin Interface >> Admin Columns Manager**: make WP core, ASE, ACF and Meta Box columns with values that are sortable to be automatically sortable, i.e. can be sorted ASC / DESC on clicking the column header. **To enable this, please open the admin columns manager for the post types you have, and click Save Changes**. Props to Julian S. and Uli L for suggesting a feature in this area.

* **[IMPROVED][Free][Pro] Admin Interface >> Hide Admin Notices**: will now properly handle hiding notices on GenerateBlocks settings page. Props to [@tpfoster](https://wordpress.org/support/users/tpfoster/) for [reporting this](https://wordpress.org/support/topic/hide-admin-notices-doesnt-work-on-every-page/) in great detail, which helped with troubleshooting.

* **[IMPROVED][Pro] Utilities >> Local User Avatar**: make sure local avatar is used for [get_avatar_url()](https://developer.wordpress.org/reference/functions/get_avatar_url/). Props to Sunny T. for reporting that Cwicly Image block does not load local avatar when "Dynamic Data >> WordPress >> Author Profile Picture" is selected as the data source, @ardyan for reporting gravatar image was still loaded on their site.

* **[FIXED][Pro] Admin Interface >> Admin Columns Manager**: fixed an issue where Screen Options toggle was not properly toggling RankMath's SEO Title and SEO Desc columns. Props to Sunny T. for reporting the issue.

* **[FIXED][Pro] Admin Interface >> Admin Columns Manager**: fixed missing column values from EDD Orders page. Props to Deborah S. for reporting the issue and helping with troubleshooting.

* **[FIXED][Free][Pro] Optimizations >> Heartbeat Control**: fixed PHP warnign. Props to Maek M. for reporting the issue along with the error log entry.

* **[FIXED][Free][Pro] Disable Components >> Disable Comments**: fixed PHP warning issue. Props to @ken0429ng for reporting the issue.

* **[FIXED][Free][Pro] Fatal error on site migration**: fixed a fatal error that occurred after migrating a site with ASE configured. Props to Bart van O. and JW for reporting the issue and helping with troubleshooting.

### 6.4.0 (2024.01.15) - ASE Free and Pro

* **[NEW][Pro] Content Management >> Terms Order**: Enable custom ordering of terms from various "hierarchical" taxonomies.

* **[IMPROVED][Pro] Admin Interface >> Admin Columns Manager**: allow extra columns, e.g. from plugins like WPML and Yoast SEO, to use their original title, which sometimes are icons, e.g. language/country flag to signify language, or traffic light to signify SEO score. Props to Sebastian A. for reporting the issue with WPML language column needing to use the original title, which is the language/country flag, different for each enabled languages.

* **[IMPROVED[Pro] Admin Interface >> Admin Columns Manager**: added support for wp_block post type used for all user-created block patterns since WP v6.3. It will allow showing the sync status column added in the 'wp_pattern_sync_status' post meta. Props to Francois G. for suggesting this.

* **[FIXED][Free][Pro] Admin Interface >> Hde Admin Notices**: fixed another PHP warning.

* **[FIXED][Free][Pro] Settings page**: fixed plugin conflicts causing ASE settings page to go blank, i.e. does not work properly. This is usually caused by other plugins loading scripts on ASE settings page that causes some JS error. Two such plugins if active will no longer cause the issue.

* **[FIXED][Free][Pro] Utilities >> Image Sizes Panel**: fixed fatal error in a certain scenario when GamiPress plugin is enabled. Props to Peter J. for reporting the issue complete with the relevant error log entry, which helped with troubleshooting.

* **[FIXED][Free][Pro] Security >> Email Address Obfuscator**: fixed PHP warning on scenarios where user agent is not detectable.

* **[FIXED][Pro] Content Management >> Custom Content Types**: fixed PHP warning during sanitization of checkbox fields during CPT creation / editing. Also fixed PHP warning when trying to detect post type label for the admin column in the Custom Post Types listing page.

### 6.3.2 (2024.01.10) - ASE Free and Pro

* **[FIXED][Free][Pro] Admin Interface >> Hde Admin Notices**: fixed PHP warning. Props to [@cck23](https://wordpress.org/support/users/cck23/) and [@joeysander](https://wordpress.org/support/users/joeysander/) for [reporting this](https://wordpress.org/support/topic/warning-appears-when-updating-ase-to-6-3-1/).

* **[FIXED] Admin Interface >> Wider Admin Menu**: fixed layout issue in WooCommerce products listing page when viewed on mobile. Props to [@inboundbe](https://wordpress.org/support/users/inboundbe/) for [reporting this](https://wordpress.org/support/topic/wider-admin-menu-conflicts-on-mobile/), and with a screenshot.

### 6.3.1 (2024.01.10) - ASE Free and Pro

* **[IMPROVED][Free][Pro] Disable Components >> Disable Comments**: now will also block comment attempts via XML-RPC and REST API. Props to @jmentalist and Anders for reporting the issue and helping with troubleshooting.

* **[IMPROVED][Pro] Admin Interface >> Admin Menu Organizer**: add "Reset Menu" link at the bottom of the organizer to reset menu order, titles and hidden status. Props to Francois G. for suggesting this.

* **[IMPROVED][Pro] Custom Code >> Code Snippets Manager**: add snippet categories to help organize your code snippets. Props to George N. for the feature suggestion.

* **[IMPROVED][Free][Pro] Admin Interface >> Hde Admin Notices**: fixed an issue where notices are not moved inside the notices panel after the last release. Pro version adds an option to also hide admin notices for non-administrators.

* **[FIXED][Free][Pro] Security >> Limit Login Attempts**: fixed PHP warning errors that occurs in certain scenarios.

* **[FIXED][Free][Pro] Padding issue around "Media (attachment)"** checkbox in module options.

### 6.3.0 (2024.01.07) - ASE Free and Pro

* **[NEW][Free][Pro] Utilities >> Image Sizes Panel**: New module to display a panel showing and linking to all available sizes when viewing an image in the media library. Especially useful to quickly get the URL of a particular image size. Pro version adds a convenient button to copy the image URL on click.

* **[IMPROVED][Free][Pro] Content Management >> Content Duplication**: After clicking the duplicate link, redirects will now go to the edit screen of the duplicate content. Add admin bar link to duplicate content on the edit screen (wp-admin) and singular view (frontend).

* **[IMPROVED][Free][Pro] Admin Interface >> Hide Admin Notices**: Prevent hidden notices from being moved into the notices panel and be made visible. Useful, for example, when plugins add hidden notices for showing action progress or errors when needed.

* **[IMPROVED][Pro] Custom Code >> Code Snippets Manager**: added support for SCSS in CSS snippets. Props to Benjamin P. for the suggestion.

* **[IMPROVED][Pro] Content Management >> Media Categories**: make the categories tree scrollable when there are many categories / sub-categories to make drag-and-dropping onto the tree more convenient. Props to Alin T. for suggesting the improvement.

* **[IMPROVED][Pro] Content Management >> Media Categories**: when you assign a media item to a sub-category, it will automatically be assigned to the parent category and the counter on the parent category will be incremented as well. Props to Alin T. for suggesting the improvement.

* **[IMPROVED][Pro] Security >> Email Address Obfuscator**: fix for auto-obfuscation when email address is inside an HTML tag like &lt;div&gt; or &lt;span&gt;. Props to Julian S. for noticing the issue in Elementor Icon List widget, reporting in details with a screencast, testing further to ensure the_content filter applies there, and finally even suggesting code fixes including the improved regex pattern! Thank you!

### 6.2.7 (2024.01.01) - ASE Free and Pro

* **[IMPROVED][Pro] Admin Interface >> Admin Columns Manager**: columns for post ID, menu order, publish date and last modified date are now sortable by default. Props to Hayato for suggesting the Last Modified Date column to be made sortable, which prompted the improvement.

* **[IMPROVED][Free][Pro] Disable Components >> Disable Comments:** Improve logic to ensure comment section is properly hidden, preventing empty gaps from appearing on the frontend. This was observed in GeneratePress theme. Props to Julian for reporting the issue with a detailed screencast / video recording.

* **[IMPROVED][Pro] Content Management >> Custom Content Types**: "Show in REST" is enabled by default during creation of new custom taxonomy. This help ensure that custom taxonomies being created will work in the block editor right away. Props to Arne O. for hinting about the issue in a support ticket.

* **[IMPROVED][Pro] Content Management >> Custom Content Types**: improve module description to emphasize that custom field groups are currently (only) supported for post types.

* **[CHANGED][Free][Pro] Optimizations >> Revisions Control**: exclude bricks_template CPT from the list of post types to enable revisions control for. Bricks handle this via a custom constant as explained at [this article](https://academy.bricksbuilder.io/article/revisions/). So, usage of a PHP code snippet via the Code Snippets Manager module is more appropriate to control revisions on Bricks templates. Props to Kenneth S. for reporting the issue in the first place.

* **[FIXED][Pro] Admin Interface >> Admin Columns Manager**: fix for column header not being hidden when the corresponding Screen Options toggle is unchecked on the post listing table page. Props to David M. and Sunny T. for reporting the issue.

* **[FIXED][Pro] Content Management >> Custom Content Types**: when there are multiple repeater fields for a custom field group, and in the post editing UI, fixed an issue with using the + sign on a repeater field row that may cause sub-fields from another repeater to be added instead. Props to Benjamin P. for reporting the issue in details (with screenshots and highlights added), which helped with replicating the issue and finding the cause and fix.

* **[FIXED][Free][Pro] Admin Interface >> Enhance List Tables >> Show additional filter(s)**: fixed PHP warning for undefined taxonomy-slug array key. Props to Christine M. for reporting the issue.

* **[FIXED][PRO] Content Management >> Custom Content Types >> Custom Field Groups**: fixed link to documentation on a field group edit screen's 'Tips' meta box.

* **[FIXED][Pro] Content Mangement >> Content Order**: fixed an issue where secondary queries in GenerateBlocks Query Loop block were not able to be sorted as intended. Props to Arne O. for reporting the issue in detail and helping with troubleshooting.

### 6.2.6.1 (2023.12.20) - ASE Pro

* **[FIXED][PRO] Admin Interface >> Admin Columns Manager**: fixed fatal error for sites where the NumberFormatter class is not present. Props to David M. for reporting.

### 6.2.6 (2023.12.20) - ASE Free and Pro

* **[ADDED][Pro] Admin Interface >> Admin Columns Manager**: added support for ACF and Meta Box custom fields. This includes ACF repeater and flexible content fields as well as Meta Box cloneable group field.

* **[FIXED][Free][Pro] Left-side footer area in wp-admin** will no longer be blank. Props to C.J. Ezell for reporting the issue.

* **[FIXED][Free] Fixed PHP warning errors** around the sponsorship nudge. Props to [@banijadev](https://wordpress.org/support/users/banijadev/) for [reporting](https://wordpress.org/support/topic/undefined-array-key-have_sponsored/) the issue.

### 6.2.5 (2023.12.12) - ASE Free and Pro

* **[ADDED][Free][Pro] Utilities >> Email Delivery**: added the option to bypass SSL certificate verification. While this would be insecure if mail is delivered across the internet, it could help in certain local and/or containerized WordPress scenarios. Props to Thijs E. for suggesting this feature and providing the code snippet for it.

* **[IMPROVED][Free][Pro] Log In/Out & Register >> Change Login URL**: fixed account registration URL going to the /not_found/ 404 page. Improved overall handling of login, registration and password reset flows. Props to [@tedocweb](https://wordpress.org/support/users/tedocweb/) for [reporting the issue](https://wordpress.org/support/topic/change-login-url-15/) and prompting the improvement.

* **[FIXED][Free][Pro] Admin Interface >> Wider Admin Menu**: fix for when the admin menu is shown on a block editor screen (non-fullwidth), it would overlap part of the block editor. Props to Adrien R. for reporting the issue and pointing to the CSS fix.

* **[FIXED][Free][Pro] Admin Interface >> Enhance List Tables**: fixed PHP warning on the media library list view when "Show additional filter(s) for hierarchical, custom taxonomies" is enabled.

### 6.2.4 (2023.12.08) - ASE Free and Pro

* **[IMPROVED][Pro] Security >> Email Address Obfuscator**: added custom subject line for the obfuscate shortcode when mailto linking is enabled. Shortcode example added in module description. Props to Paul R. for the suggestion. Also improved mechanism to auto-obfuscate email addresses in post content so it does not interfere with manually added obfuscation shortcode, which contains an email address in it.

* **[IMPROVED][Free][Pro] Content Management >> Content Duplication**: exclude addition of 'Duplicate' link for WooCommerce products as there's already a native 'Duplicate' link from WooCommerce. Props to Claudio P. for reporting the issue.

* **[FIXED][Free][Pro] Log In/Out & Register >> Change Login URL**: fixed an issue where custom login URL redirects to 404 when the site has WPML configured to apply the directory pattern for the main site language. Props to Marcellus J. for reporting the issue.

* **[FIXED][Pro] Optimizations >> Image Upload Control**: fixed an issue where if the PNGs being uploaded are 'paletter' images, the resulting WebP files are blank. Props to Marcellus J. for reporting the issue.

* **[FIXED][Free][Pro] Admin Interface >> Enhance List Tables**: fixed PHP deprecated error, "Constant FILTER_SANITIZE_STRING is deprecated", when "Show additional filter(s) for hierarchical, custom taxonomies" is enabled. Thanks to [Alex @justsmilepeople](https://wordpress.org/support/users/justsmilepeople/) for [reporting this](https://wordpress.org/support/topic/filter_sanitize_string-is-deprecated/).

* **[FIXED][Pro] Custom Code >> Code Snippets Manager**: fixed PHP warning in WP subfolder installs due to non-dynamic definition of a folder in /wp-content/uploads/, which is used to store snippet files for execution. Props to Phil K. for discovering the issue and suggesting the fix.

### 6.2.3 (2023.12.07) - ASE Free and Pro

* **[ADDED][Pro] Security >> Limit Login Attempts >> IP Whitelist**: this should also be useful to unblock a user. Props to Vijayanand V. for the suggestion.

* **[ADDED][Pro] Utilities >> Password Protection**: added IP whitelisting and bypass via URL parameter. Useful for your team to easily view a dev site or providing easy access to clients when reviewing a dev site. Please see module description for details. Props to Alf O.F. for the suggestion to add IP whitelisting.

* **[FIXED][Free][Pro] Disable Components >> Disable Smaller Components >> Disable Emoji Support**: fixed PHP fatal error for an edge case where DNS prefetch of emoji URLs does not return a string. Props to [@tomhung](https://wordpress.org/support/users/tomhung/) for [reporting and providing](https://wordpress.org/support/topic/uncaught-typeerror-strpos/) the code fix.

* **[FIXED][Free][Pro] ASE settings page**: fixed an issue where for some screen size, the main settings section is overlapping with the WP side menu. Props to [@dvaer](https://wordpress.org/support/users/dvaer/) for [reporting this](https://wordpress.org/support/topic/wider-admin-menu-causes-overlap-of-ase-interface/) and also providing the CSS fix for it.

### 6.2.2.1 (2023.12.05) - ASE Pro

* **[IMPROVED][Pro] Utilities >> Maintenance Mode**: improve loading of dependencies on media library and color picker assets.

### 6.2.2 (2023.12.05) - ASE Free and Pro

* **[ADDED][Pro] Utilities >> Maintenance Mode**: add an option to use an image as the page background. Props to Vijayanand V. for the suggestion. Using a solid color is also possible now. Please expect to see this module improved further in future releases.

* **[IMPROVED][PRO] Admin Interface >> Clean Up Admin Bar**: changed algorithm to detect newly added admin bar items, e.g. from theme, plugins or snippets, both on the backend and also the frontend. Props to Jan K. for suggesting the improvement and providing tips on how to achieve it.

* **[IMPROVED][Pro] Optimizations >> Image Upload Control**: transparent PNG will now be converted to transparent WebP. Props to Laurent F. for the suggestion.

* **[IMPROVED][Pro] Content Management >> Custom Content Types**: added description on where to find links to create CPTs, custom taxonomies and custom field groups, which are under the Settings menu.

* **[IMPROVED][Free][Pro] Disable Components >> Disable XML-RPC**: improve code to ensure link to xmlrpc script in &lt;head&gt; is properly removed. Props to Dorel Y. for reporting the issue and suggesting a snippet to do that.

* **[FIXED][Pro] Missing 'Configure' link** in plugin action links on the Pro version, which may make it difficult for users to find the settings page for ASE if they've never used the free version before.

### 6.2.1 (2023.12.01) - ASE Free and Pro

* **[ADDED][Pro] Security >> Obfuscate Email Address**: added the option to automatically obfuscate email addresses inside post content. Props to Dorel Y. for the suggestion.

* **[IMPROVED][Free][Pro] Content Management >> Content Order**: for enabled post types, newly created posts will now be placed at the bottom of the order. This removes the need to manually order after post creation. Props to Bengt R. and Darius for identifying the issue with new posts ordering and suggesting the improvement.

* **[IMPROVED][Pro] Admin Interface >> Admin Columns Manager**: added Post Parent and Menu Order columns in the Default (columns) section for hierarchical post types or those supporting page attributes.

* **[IMPROVED][Pro] Content Management >> Custom Content Types >> Custom Field Groups**: tidy up the appearance of TinyMCE toolbar in the WYSIWYG custom field input.

* **[IMPROVED][Free][Pro] Disable Components >> Disable Comments**: this should be more reliable now with improved application of the comments_array filter hook for post types where comments are being disabled. Props to Dorel Y. for reporting the issue with a theme used on their site.

* **[GRAMMAR][Free][Pro] Admin Interface - Enhance List Tables**: remove unneeded dots from the end of the list items. Props to Sridhar K. for spotting that.

* **[FIXED][Free][Pro] Optimizations >> Heartbeat Control**: fixed a PHP Warning when wp-cron.php is triggered. Props to Avi R. and Christian G. for reporting the issue.

* **[FIXED][Free][Pro] Admin Interface >> Admin Menu Organizer**: certain plugins, e.g. GigPress and MemberPress, has parent menu items that were not able to be organized by the AMO module. This should now be fixed. Props to Nadja V.M. and Dana S. for reporting the issue.

* **[FIXED][Pro] Content Management >> Custom Content Types >> Custom Field Groups**: fixed a missing JS file error when using the block editor and having a WYSIWYG custom field loaded.

### 6.2.0 (2023.11.27) - ASE Free and Pro

* **[ADDED][Pro] Utilities >> Local User Avatar**: New module to enable usage of any image from WordPress Media Library as user avatars. Props to Florian B. for the suggestion.

* **[IMPROVED][Free][Pro] Security >> Email Address Obfuscator**: improve module description with clearer examples of how to use the shortcode. Props to jh@ for the suggestion.

* **[IMPROVED][Pro] Content Management >> Custom Content Types >> Custom Field Groups**: image field input will now render the 'medium' size image as a preview in the post edit screen, preventing cropping of images that is better shown uncropped, e.g. brand logo. Props to Simon for providing detailed feedback on this.

* **[FIXED][Pro] Admin Interface >> Admin Columns Manager**: prevent duplication of WooCommerce taxonomy terms in the product categories and product tags columns. Props to Philippe G. and Florian B. for reporting the issue.

* **[FIXED][Free][Pro] Optimizations >> Image Upload Control**: fixed an issue where subsequently uploading files with the same filename will result in the last upload overwriting the earlier uploads, i.e. all uploads ended up with the same filename. This includes scenarios when such images are copy-pasted into the block editor. Props to Manu H. reporting the issue in detail and help with ongoing troubleshooting.

* **[FIXED][Pro] Optimizations >> Image Upload Control**: in some scenarios, the default WebP conversion quality of 82 is not automatically applied upon enabling it. It should now be properly applied. Props to Matija S. for spotting the issue.

### 6.1.3 (2023.11.23) - ASE Free and Pro

* **[ADDED][Pro] Admin Interface >> Hide Admin Bar**: added a Pro feature to (also) hide the admin bar on the backend for some user roles, which maybe desirable for certain types of membership sites.

* **[IMPROVED][Free][Pro] Utilities >> View Admin as Role**: added a floating button to the right-bottom corner of wp-admin pages when viewing as a non-admin user role. In addition to the switcher on the admin bar, this should make it easier to switch back to the administrator role.

* **[IMPROVED][Free][Pro] Utilities >> View Admin as Role**: add a warning for sites that has Ninja Firewall active to uncheck "Block attempts to gain administrative privileges" when this module is enabled to prevent being locked out of the admin account. Props to C de Groot for reporting the issue and suggesting to add a warning.

* **[IMPROVED][Pro] Admin Interface >> Admin Columns Manager**: the "Manage Columns" button will now be visible only to administrators. Thanks to Claudio P. for reporting the issue where non-admins can see it and clicking on it produces error messages.

### 6.1.2 (2023.11.21) - ASE Pro

* **[ADDED][Pro] Custom Code >> Code Snippets Manager**: added a description field to each code snippet and a column for it in the list view. Props to Simon (and another user whom I can't figure out who, sorry!) for suggesting this.

* **[IMPROVED][Pro] Custom Code >> Code Snippets Manager**: when Content Order is enabled for code snippets, the 'Order' submenu item will properly be positioned at the end.

* **[IMPROVED][Pro] Custom Code >> Code Snippets Manager**: reduce FOUC (Flash of Unstyled Content) on the add new & edit screens of code snippets.

* **[FIXED][Pro] Admin Interface >> Admin Menu Organizer**: fixed an issue where for certain group of submenu items are not shown when this module is enabled and/or when saving changes with the module enabled. For example, this happened with WooCommerce >> Analytics and FakerPress submenu items. Props to @ubuntuproductions for reporting the issue.

### 6.1.1 (2023.11.17) - ASE Pro

* **[FIXED]Pro] Content Management >> Content Order**: fixed PHP warning for newly added option to use custom order on the frontend. Props to Thomas B. for reporting.

### 6.1.0 (2023.11.17) - ASE Free and Pro

* **[ADDED][Pro] Content Management >> Content Order**: added option to use custom order on frontend query and display of enabled post types. This is done via the pre_get_posts hook for post type archive pages and on secondary queries.

* **[ADDED][Pro] Optimizations >> Image Upload Control**: you can now set the WebP conversion quality, between 1 to 100. Default has been increased from 80 to 82. Props to Istvan for suggesting the improvement after seeing artifacts on blurry parts of certain images when converted to WebP with the default quality settings.

* **[IMPROVED][Pro] Admin Interface >> Admin Columns Manager**: newly registered custom taxonomies will now immediately show up in the Admin Columns Manager screen. Previously, you'll need to reset the columns first. Props to Simon for reporting the issue when adding a custom taxonomy with Advanced Custom Fields (ACF), leading to the improvement made.

* **[IMPROVED][Free][Pro] Admin Interface >> Admin Menu Organizer**: styling of WooCommerce separator has been improved to match the other separators.

* **[FIXED][Pro] Admin Interface >> Admin Menu Organizer**: fixed a bug where opting to always hide a menu item does not properlly work. Also added special treatment for Yoast SEO menu item that can not be always hidden due to how it is showing a special menu item for editors. Props to Chris for reporting the issue and helping with troubleshooting.

* **[FIXED][Free][Pro] Security >> Email Address Obfuscator**: further fix for Firefox browser, to correctly show the human-readable email address to site visitors. Props to Mark G. for reporting the issue (again!).

* **[FIXED][Free][Pro] Content Management >> Content Duplication**: fixed an issue with CSS variables used in the block editor not being properly duplicated, thus breaking the style of the duplicate post. Props to Tim H. for reporting the issue when working with blocks from GenerateBlocks. Props to Sami M. for reporting the issue.

* **[FIXED][Pro] Admin Interface >> Admin Columns Manager**: fixed an issue where saving changes resulted in an error in the admin-ajax.php call when 5G / 6G firewall rules is enabled in All in One Security plugin. Props to Joko Z. for reporting the issue.

* **[FIXED][Pro] Admin Interface >> Hide Admin Notices**: fixed an issue where "an unexpected network error has occurred!" notice was shown in the admin notices panel despite no apparent network error occurring. Props to Sebastian A. for reporting this.

* **[FIXED][Pro] Custom Code >> Code Snippets Manager**: fixed an issue where last modified date time is inaccurate. Props to Greg F. for reporting the issue and helping with troubleshooting.

### 6.0.8.2 (2023.11.13) - ASE Pro

* **[IMPROVED][Pro] Admin Interface >> Admin Columns Manager**: Fix issues with Rank Math SEO admin columns. Ensure they are properly manageable with ASE while retaining bulk-editability, and prevent duplicate values from being shown. Props to Benjamin P. for reporting the issue with detail and screenshots.

### 6.0.8.1 (2023.11.13) - ASE Free and Pro

* **[IMPROVED][Pro] Content Management >> Media Categories**: frontend CSS previously (also) loaded for public visitors will only be shown to logged-in users when they work with the media library in page builders.

* **[IMPROVED][Free][Pro] Content Management >> External Permalinks**: completely remove jQuery dependency so it no longer loads on the frontend. Previously the frontend JS was improved to no longer use jQuery, but the dependency was still there. Props to David M. for reporting the issue as he was working on his site using Bricks builder and wanted to optimize page load time.

* **[IMPROVED][Pro] Admin Interface >> Admin Columns Manager**: Further improvement to the handling of custom columns from plugins, prompted by report from Paul R. of an issue with columns from WooCommerce Product Retailers plugin.


### 6.0.8 (2023.11.12) - ASE Pro

* **[IMPROVED][Pro] Admin Interface >> Admin Columns Manager**: Improved handling for custom columns from plugins, e.g. SEO plugins like Yoast SEO, All in One SEO (AIOS), Rank Math SEO and SEOPress. There's now a dedicated "Extra" section on the admin columns management page/UI. There's also a "Reset Columns" button that will allow for restoring columns to the initial state and re-listing available default, extra and custom field columns to choose from. Props to Benjamin P. and Tim for reporting issues with Rank Math and SEOPress which prompted the improvement.

* **[FIX][Pro] Admin Interface >> Admin Menu Organizer**: Fixed an issue where submenu items could not be re-ordered. Props to Bruze Z. for reporting the issue with WooCommerce Products submenu items.

### 6.0.7 (2023.11.10) - ASE Free and Pro

* **[FIX][Free][Pro] Disable Components >> Disable Smaller Components >> Disable Dashicons CSS and JS**: Fix for scenarios where a custom login URL is set with another plugin, e.g. All in One Security (AIOS), and disabling Dashicons will mess up the styling of that login page. Thanks to Aleš for reporting the issue.

* **[FIX][Free][Pro] Security >> Obfuscate Author Slugs**: Fixed PHP warning "Undefined array key SERVER_ADDR" in certain scenarios where server IP is not detectable via that PHP server global. Thanks to Marco M. for reporting.

* **[FIX][Free][Pro] Log In/Out & Register >> Change Login URL**: Fix for password-protected pages getting redirected to the /not_found/ 404 page even after entering the correct password. Props to [@vanektomas](https://wordpress.org/support/users/vanektomas/) and [@netzzjd](https://wordpress.org/support/users/netzzjd/) for [reporting this](https://wordpress.org/support/topic/bug-in-change-login-url-if-its-enabled-and-set/).

* **[FIX][Free][Pro] Utilities >> Display System Summary**: Fixed PHP fatal error when mysqli_connect() fails to connect to the database. Thanks to Diaz X. and Simon for reporting the issue.

* **[FIX][Pro] Admin Interface >> Admin Columns Manager**: Fixed PHP Warning "non-numeric value encountered". Props to Elon R. for reporting the issue in detail.

### 6.0.6 (2023.11.05) - ASE Free and Pro

* **[ADDED][Pro] Admin Interface >> Admin Columns Manager**: Horizontal scrolling on the posts list table is automatically enabled when there are columns using custom width in pixels that have the combined width greater than the width of the list table. This is especially useful for post types with many custom fields and you'd like to show many / most of them in the list table.

* **[IMPROVED][Pro] Admin Interface >> Admin Columns Manager**: Ensure custom columns are shown correctly in the posts list table after performing Quick Edit on a post. Props to Benjamin P. for reporting the issue.

* **[FIXED][Pro] Admin Interface >> Admin Columns Manager**: Fixed several PHP Warnings when custom fields have no value assigned yet for a post.

* **[FIXED][Free][Pro] Admin Interface >> Hide Admin Notices**: Connection error notices were shown immediately after enabling this module, regardless of whether there was an actual connection error. It will now be properly hidden and only shown as needed. Props to Sebastian A. for reporting the issue.

* **[FIXED][Pro] Content Management >> Custom Content Types**: The WYSIWYG custom field will now correctly insert the media when using the Add Media button/UI.

* **[FIXED][Free][Pro] Security >> Email Address Obfuscator**: Fixed an issue with Firefox browser not correctly showing the human-readable version of the email. Props to Mark G. for reporting the issue with helpful screenshots.

### 6.0.5.1 (2023.10.31) - ASE Free and Pro

* **[FIXED][Free][Pro] Log In/Out & Register >> Change Login URL**: Fixed an issue where correct login redirects to /not_found/ after latest changes in v6.0.5. Props to Gustavo F. and Ignacio C. for reporting the issue.

### 6.0.5 (2023.10.30) - ASE Free and Pro

* **[IMPROVED][Free][Pro] Log In/Out & Register >> Change Login URL**: Redirection of /wp-login.php and /wp-admin/ to the 404 /not_found/ URL works more reliably now. Props to Gustavo F. for reporting the issue where those URLs were not properly redirecting to /not_found/ on his sites and ongoing help with troubleshooting it.

* **[FIXED][Pro] Admin Interface >> Admin Menu Organizer**: Missing submenu items should now be shown in the admin menu, as well as ASE Pro settings page's sortables. i.e. the module should more reliably show submenu items now. Props to Bruce Z for reporting this for an ACF Pro CPT menu item which had missing submenu items on his site.

* **[FIXED][Pro] Content Management >> Custom Content Types**: Fixed a PHP Warning error. Props to Sam E.B. for reporting.

### 6.0.4 (2023.10.26) - ASE Free and Pro

* **[IMPROVED][Free] Admin Interface >> Hide Admin Notices**: Removed broad-sweeping CSS rules that unintentionally affected notices that were not supposed to be hidden into the admin notices panel. Props to Ingo R. for reporting the issue with MainWP site connection error notice being hidden on page load.

* **[IMPROVED][Free] Disable Components >> Disable Smaller Components >> Disable version number**: will now only remove version number from static assets in the public view (non-logged-in) of pages. Props to Ingo R for reporting that ACSS auto-BEM feature in Bricks was missing it's button in the structure panel, and was caused by version number being removed from static assets.

* **[FIXED][Free][Pro] PHP Deprecation notice in PHP 8.1:** which showed up when `null` was unintentionally passed into `preg_replace()` during the rendering of some ASE setting fields. Props to Stewart R. for reporting the issue.

* **[FIXED][Pro] Admin Interface >> Admin Menu Organizer:** now correctly shows all submenu items. Props to Henry R. for reporting an issue where several of Elementor's 'Templates' menu's submenu items gone missing when this module was enabled.

### 6.0.3 (2023.10.18) - ASE Free and Pro

* **[FIXED][Free] Admin Interface >> Hide Admin Notices**: Plugin description and update success message will now remain visible after plugin update process is completed on the Plugins listing page.
* **[FIXED][Pro] Custom Code >> Code Snippets Manager**: CSS fixes for the activate / deactivate / publish / update box on each snippet.
* **[FIXED][Free] Utilities >> Display System Summary**: CSS fix to ensure system summary is displayed in alignment with other elements in the At a Glance widget

### 6.0.2 (2023.10.17) - ASE Pro

* **[FIXED] Admin Interface >> Admin Columns Manager**: Will now render the value of custom fields added using default WordPress UI/metabox for managing custom fields. This is rendered using [`get_post_meta()`](https://developer.wordpress.org/reference/functions/get_post_meta/). Props to Upekkha for reporting it.

### 6.0.1 (2023.10.13) - ASE Pro

* **[FIXED] Custom Code >> Code Snippets Manager**: PHP Warning: undefined variable. Props to Michael S.P. for reporting it.
* **[FIXED] Admin Interface >> Admin Columns Manager**: PHP Warning: undefined array key 0. Props to Sam for reporting it.
* **[FIXED] Content Management >> Custom Content Types**: PHP Deprecation: creation of dynamic properties. Props to Steven for reporting it.
* **[FIXED] Optimizations >> Image Upload Control**: .jpeg files will now properly be converted to .webp. Props to Julian S for reporting it.

### 6.0.0 (2023.10.11)

* **[ADDED] Log In/Out & Register >> Site Identity on Login Page**: Use the site icon and URL to replace the default WordPress logo with link to wordpress.org on the login page. Props to [@cooper08](https://wordpress.org/support/users/cooper08/) and [@julians3](https://wordpress.org/support/users/julians3/) for [suggesting this](https://wordpress.org/support/topic/change-login-wp-logo/) along with the code snippet.

* **[Pro] The Pro version of ASE is here. Lifetime Deal (LTD) is available.** Find out more at [wpase.com](https://www.wpase.com/chnlg-to-web).

### 5.8.1 (2023.10.05)

* **[FIXED] wp_die error message** when updating to v5.8.0. Thanks to [@verysiberian](https://wordpress.org/support/users/verysiberian/) for the prompt and detailed [report](https://wordpress.org/support/topic/wsod-on-all-sites-after-update/) and [@wrlkd](https://wordpress.org/support/users/wrlkd/), [@rockwildaz](https://wordpress.org/support/users/rockwildaz/) and [@amnwtritaly](https://wordpress.org/support/users/amnwtritaly/) for helping out with troubleshooting.

### 5.8.0 (2023.10.05)

* **[ADDED] Disable Components >> Disable Smaller Components >> Disable version number**: You can now hide version number which by default is part of static resource (CSS/JS) URLs in the &lt;head&gt;. Props to [@cooper08](https://wordpress.org/support/users/cooper08/) for [suggesting this](https://wordpress.org/support/topic/disable-smaller-components/) along with the code snippet.

* **[ADDED] Disable Components >> Disable Smaller Components >> Disable jQuery Migrate**: You can now disable the jQuery Migrate script from loading on the frontend. Props to [@blueoaks](https://wordpress.org/support/users/blueoaks/) for [suggesting this](https://wordpress.org/support/topic/feature-request-disable-jquery-migrate/).

* **[CHANGED] Admin Interface >> Hide Admin Notices**: This is now limited to site admins only. Editors, Authors and other user roles will see notices as usual.

* **[FIXED] Admin Interface >> Hide Admin Notices**: Notices under each plugin that has an update in the Plugins list page now shows up normally where it was previously missing/hidden when Hide Admin Notices is active. Thanks to [@venkeyaccent](https://wordpress.org/support/users/venkeyaccent/), [@dmdeck](https://wordpress.org/support/users/dmdeck/) and [@computerbuddha](https://wordpress.org/support/users/computerbuddha/) for reporting this issue [here](https://wordpress.org/support/topic/bug-in-hide-admin-notices/) and [here](https://wordpress.org/support/topic/hide-admin-notices-issue/).

* **[IMPROVED] Admin Interface >> Wider Admin Menu**: Now works when wp-admin is in Right-to-Left (RTL) languages. Thanks to [@mehdimoradi7172](https://wordpress.org/support/users/mehdimoradi7172/) for [reporting the issue](https://wordpress.org/support/topic/wider-admin-menu-rtl-issue/).

* **[IMPROVED] Log In/Out & Register >> Change Login URL**: Default lost password URL no longer accessible directly and must now include custom login slug. Props to [@banijadev](https://wordpress.org/support/users/banijadev/) for discovering the issue and [reporting it](https://wordpress.org/support/topic/add-support-hidden-lost-password-path/).

* **[IMPROVED] Content Management >> Media Replacement**: media-replace-frontend.css no longer loads for site visitors. Props to [@cvladan](https://wordpress.org/support/users/cvladan/) for discovering the issue and [reporting it](https://wordpress.org/support/topic/dont-load-media-replace-frontend-css-on-frontend/).

* **[SECURITY] Utilities >> Password Protection**: Patched a bypass vulnerability disclosed responsibly by security researcher Abu Hurayra via Patchstack, regarding the authentication cookie. It now uses wp_hash_password() and wp_check_password().

### 5.7.1 (2023.09.12)

* **[FIXED] Disable Components >> Disable Gutenberg**: Fixed PHP Warnings. Thanks to [@ysintos](https://wordpress.org/support/users/ysintos/) and [@ofmarconi](https://wordpress.org/support/users/ofmarconi/) for reporting this issue [here](https://wordpress.org/support/topic/unable-to-upload-images-after-last-update-5-7-0/) and [here](https://wordpress.org/support/topic/warning-class-disable-components-php-on-line-238/).

* **[FIXED] Custom Code >> Custom Admin / Frontend CSS**: Fix for escaped symbols being output on inlined stylesheets. Thanks to [@d4niwp](https://wordpress.org/support/users/d4niwp/) for [reporting this](https://wordpress.org/support/topic/issue-with-admin-and-frontend-css-selector-is-converted-into/).

### 5.7.0 (2023.08.30)

* **[NEW] Refreshed UI for ASE admin page.** This is largely based off of the UI/X design work generously provided by [@rinodeboer](https://wordpress.org/support/users/rinodeboer/). Thanks so much Rino!

* **[IMPROVED] Admin Interface >> Hide Admin Notices:** Handle additional notices which appears on an admin pages that have been encapsulated inside an additional div, which is sometimes done by plugins that modifies a WP core admin screen.

* **[IMPROVED] Refactored admin-page.js**, the main js file used to build out ASE's admin page. It's tidier and smaller now.

* **[CHANGED] Move Enhance List Table module inside Admin Interface tab** and reorganize modules in that tab for better logical grouping / sequencing.

### 5.6.2 (2023.08.25)

* **[FIXED] JS error in admin-page.js.** Props to [@andyguzman](https://wordpress.org/support/users/andyguzman/) for [reporting it](https://wordpress.org/support/topic/avif-jquery-breaking-the-settings-page/).


### 5.6.1 (2023.08.24)

* **[FIXED] Content Management >> Media Replacement**: Fixed PHP Warnings. Thanks to Marco for reporting this issue.

* **[IMPROVED] Admin Interface >> Hide Admin Notices**: Fixed missing commas in JS file to target certain notice divs. Notices no longer 'flash' briefly now before being hidden inside the notices panel. Thanks to [@cvladan](https://wordpress.org/support/users/cvladan/) for [suggesting the fix](https://wordpress.org/support/topic/conflict-with-another-plugin-suggestion/).

### 5.6.0 (2023.08.14)

* **[ADDED] Utilities >> Display System Summary**: Show quick summary of the system the site is running on to admins, in the "At a Glance" dashboard widget. This includes the web server software, the PHP version, and the database software. Props to Keith for suggesting this.

* **[IMPROVED] Security >> Limit Login Attempts**: Improve SQL query for creating the failed logins log table so it is more compatible with a wider range of DB setups.

### 5.5.2 (2023.08.09)

* **[IMPROVED] Disable Components >> Disable Gutenberg**: default WP post types (wp_template, wp_template_part, wp_global_styles, wp_navigation) are now removed from the Disable Gutenberg settings.

* **[IMPROVED] Admin Interface >> Admin Menu Organizer**: when a hidden menu item is selected, i.e. the admin page for it is being viewed, the menu item will now remain visible along with it's sub-menu items. This makes for a better UX overall, reducing back and forth clicks of the "Show All" toggle. Props to [@tomhung](https://wordpress.org/support/users/tomhung/) and [@dvaer](https://wordpress.org/support/users/dvaer/) for [suggesting this](https://wordpress.org/support/topic/feature-request-show-all-menu-stay-open-when-on-closed-menu-item/).

* **[IMPROVED] Disable Components >> Disable Feeds**: the /feed/ page now will properly return 403 Forbidden response. It was previously showing 500 error when a site is inspected with [Sucuri malware and security checker](https://sitecheck.sucuri.net/). The code changes for this was produced by ChatGTP via [@ofmarconi](https://wordpress.org/support/users/ofmarconi/)'s [prompt](https://wordpress.org/support/topic/sucuri-error-500-disable-feed/#post-16957833) as part of his [detailed report and investigation](https://wordpress.org/support/topic/sucuri-error-500-disable-feed/) on the issue. Thank you!

* **In-Kind Sponsorship**: If anyone has spare license for [GenerateBlocks Pro](https://generateblocks.com/pro/), [Blockstudio](https://blockstudio.dev/), [Lazy Blocks](https://www.lazyblocks.com/), [CubeWP](https://cubewp.com/) and/or [WS Form](https://wsform.com/) that you'd like to donate to my test site for ASE, please [get in touch](https://bowo.io). Thank you!

### 5.5.1 (2023.08.07)

* **[FIXED] Admin Interface >> Admin Menu Organizer:** some plugins register their menu item with a super-late priority, e.g. 10000, so, ASE can not organize them properly. Changes have been made to accommodate such scenario. Props to [@gd4web](https://github.com/gd4web) for [reporting this](https://wordpress.org/support/topic/features-request-and-issue-reporting/) in detail.

* **[FIXED] Content Management >> Content Order:** Fix for PHP errors that appear if a post type has been de-registered while content ordering is still enabled for it in ASE.

* **[FIXED] Security >> Limit Login Attempts:** Fixed an issue for when 'sql_require_primary_key' is set in the DB, e.g. managed MySQL DB in DigitalOccean, ASE would churn out "Attempt to create or modify table without primary key" error. Props to [Greg Mount](https://github.com/gd4web) for [reporting this](https://wordpress.org/support/topic/attempt-to-create-or-modify-table-without-primary-key/) in great detail and suggesting the fix.

* **[IMPROVED] Disable Components >> Disable Comments:** Add filter to prevent anonymous comment via XML-RPC. Props to [@bzosel](https://github.com/bzosel) for [reporting an issue with disabling comment](https://wordpress.org/support/topic/comment-was-added-although-comments-disabled/) that prompted this improvement.

* **[NEW MONTHLY SPONSOR] Thank you @maeonian for being a new monthly sponsor at USD 2 / month!** ASE is now at 6 of 10 of the monthly sponsors goal. It doesn't take much to sponsor ASE for the time and effort savings you may have gained by using it on your sites. You can [be one](https://github.com/sponsors/qriouslad) today!

### 5.5.0 (2023.08.04)

* **[ADDED] Security >> Email Address Obfuscator**. Obfuscate email address to prevent spam bots from harvesting them, but make it readable like a regular email address for human visitors. Props to [@nassukesso](https://github.com/nassukesso) for [suggesting this feature](https://wordpress.org/support/topic/email-address-obfuscating/).

* **[FIXED] In some scenarios, ASE settings page only shows the category tabs while not showing any of the actual modules settings**. Props to John B. for reporting this and facilitating troubleshooting. He also kindly provided licenses to premium plugins on my test site, that will help with ASE development and maintenance in the future. If you have spare licenses for [~~Elementor Pro~~](https://elementor.com/pro/), [~~Spectra Pro~~](https://wpspectra.com/pro/), [~~Bricks~~](https://bricksbuilder.io/), [~~Cwicly~~](https://cwicly.com/) and/or [~~JetEngine~~](https://crocoblock.com/plugins/jetengine/) that you'd like to donate to my test site, please [get in touch](https://bowo.io).

### 5.4.1 (2023.07.31)

* **[Fixed] Security >> Limit Login Attempts**. Fixed an error on sites with older version of MySQL / MariaDB that caused the failure of DB table creation to be used to log failed login attempts. Props to [Ken Sim](https://wordpress.org/support/users/kwsim539/) for [reporting this](https://github.com/qriouslad/admin-site-enhancements/issues/4) in great detail, which made it much easier to fix. Ken has also kindly became a [monthly sponsor](https://github.com/sponsors/qriouslad) of my work. Thanks Ken! I'm now at 5 of my initial goal of [getting 10 monthly sponsors](https://github.com/sponsors/qriouslad). 🙂

* **[IMPROVED] Content Management >> Enhance List Tables >> Show featured image column:** will now replace the default product thumbnail column on WooCommerce product listing page, and no longer show both columns. Props to [@studio84digital](https://wordpress.org/support/users/studio84digital/) for [reporting it](https://wordpress.org/support/topic/show-featured-image-column-woocommerce/) on the [support forum](https://wordpress.org/support/plugin/admin-site-enhancements/).

### 5.4.0 (2023.07.25)

* **[ADDED] Content Management >> Allow Custom Navigation Menu Items to Open in New Tab**. Allow custom navigation menu items to have links that open in new browser tab via target="\_blank" attribute. The rel="noopener noreferrer nofollow" attribute will also be added for enhanced security and SEO benefits. Props to [@tomhung](https://github.com/tomhung) for [suggesting this feature](https://github.com/qriouslad/admin-site-enhancements/issues/4) and providing the [code snippet](https://gitlab.com/-/snippets/2567854) to base it upon.

* Checked compatibility with WordPress v6.3 RC1

### 5.3.2 (2023.07.15)

* **[FIXED] Disable Smaller Components >> Disable the generator meta tag:** fixed PHP fatal error in some scenarios for PHP v8.0+. Props to [@swissspidy](https://github.com/swissspidy) for [reporting it](https://github.com/qriouslad/admin-site-enhancements/issues/4) on Github.

* **[IMPROVED] Content Management >> External Permalinks:** removed jQuery dependency on the front-end in public.js. Replaced script with pure JS version churn out by ChatGPT which was prompted by Marco M.J. So, yes... first AI usage in ASE code. Pretty cool. 🙂

### 5.3.1 (2023.07.14)

* **[FIXED] Admin Interface >> Disable Dashboard Widgets:** fixed PHP warning. Props to [@mohobook](https://wordpress.org/support/users/mohobook/) for [reporting it](https://wordpress.org/support/topic/code-error-media-replacement-not-working/).

* **[IMPROVED] Content Management >> Open All External Links in New Tab:** will now exclude relative URLs which points to internal URLs from being opened in a new tab. Props to [@francismacomber](https://wordpress.org/support/users/francismacomber/) for [reporting it](https://wordpress.org/support/topic/open-all-external-links-in-new-tab-misidentifies-relative-urls-as-external/).

### 5.3.0 (2023.07.08)

* **[IMPROVED] Utilities >> Email Delivery**: You can now send a test email to verify if your custom sender name/email and SMTP configuration work as intended. Props to many users for suggesting this in the support forum [here](https://wordpress.org/support/topic/request-test-email-delivery/), [here](https://wordpress.org/support/topic/test-smtp-email-delivery/), [here](https://wordpress.org/support/topic/smtp-email-delivery/), [here](https://wordpress.org/support/topic/check-smtp-by-sending-an-email/) and [here](https://wordpress.org/support/topic/please-add-a-test-option-for-smtp/).

### 5.2.11 (2023.07.05)

* **[IMPROVED] Utilities >> Password Protection** This will now also work on non-HTTPS sites. Props to Sascha for reporting the issue via the contact form at [bowo.io](https://bowo.io) and providing great detail about what was done to try and troubleshoot it, and later on found the root cause of the issue, which made fixing the issue much simpler.

### 5.2.10 (2023.06.30)

* **[IMPROVED] Utilities >> Email Delivery** Custom sender name / email can now be set and enforced independent of delivery via external SMTP service/account. Props to [@lcwilson18](https://wordpress.org/support/users/lcwilson18/) for [suggesting it](https://wordpress.org/support/topic/smtp-email-delivery/).

### 5.2.9 (2023.06.27)

* **[FIXED] Log In/Out & Register >> Redirect After Login** will now correctly redirect to an internal wp-admin page, e.g. wp-admin/edit.php?post_type=page (View All Pages). Props to [@tomhung](https://wordpress.org/support/users/tomhung/) for [reporting it](https://wordpress.org/support/topic/redirect-after-login-wont-redirect-to-internal-page/).

### 5.2.8 (2023.06.26)

* **[IMPROVED] Front-end public.js** will only be loaded if relevant modules that use it are enabled. Props to Martin M. for reporting the issue via the contact form at [bowo.io](https://bowo.io) and Maxime D. via Facebook message.

### 5.2.7 (2023.06.26)

* **[FIXED] Custom Code**: Possible fix for CodeMirror (code editor) JS error caused by jQuery not being explicitly set as a dependency. When this happens, ASE's admin page is rendered blank / useless. Props to [@sarah-haruel](https://wordpress.org/support/users/sarah-haruel/) for [reporting it](https://wordpress.org/support/topic/admin-and-site-enhancements-ase-disabled-when-slideshow-se-activated/).
* **[IMPROVED] Log In/Out & Register >> Change Login URL**: Improve module description to avoid confusion with renaming the entire /wp-admin/ as opposed to renaming just the login URL. Props to [Maz Ziebell](https://wordpress.org/support/users/max-ziebell/) for the suggestion.
* **[FIXED] Disable Components >> Disable Gutenberg**: Fixed PHP warning when saving a CPT post where gutenberg has been disabled. Props to [@gregmount](https://wordpress.org/support/users/gregmount/) for [reporting it](https://wordpress.org/support/topic/disable-gutenberg-throws-php-warning/).
* **[ADDED] A dismissible Sponsorship / Rating / Feedback nudge/notice** has been added to ASE's admin page (only), which will appear after every 10 consecutive clicks of the "Save changes" button. This is a way to try and gain additional support for ASE's ongoing development and maintenance. I hope you don't mind. 🙂

### 5.2.6 (2023.06.24)

* **[FIXED] Security >> Limit Login Attempts**: Fixed PHP warning issue. Props to [@tomo55555](https://wordpress.org/support/users/tomo55555/) for [reporting it](https://wordpress.org/support/topic/warning-error-reported/).

### 5.2.5 (2023.06.22)

* **[FIXED] Utilities >> Password Protection**: Fixed the issue that causes the password protection form/page to have no styling after v5.2.4 patch release. Props to [@gregmount](https://wordpress.org/support/users/gregmount/) for [reporting it](https://wordpress.org/support/topic/v5-2-4-breaks-styling-on-password-protection/).

### 5.2.4 (2023.06.21)

* **[FIXED] Disable Components >> Disable Dashicons**: Fixed PHP warning when executing WP CLI commands. Props to [@tomhung](https://wordpress.org/support/users/tomhung/) for [reporting it](https://wordpress.org/support/topic/php-warning-strpos-empty-needle-2/).

* **[RESOLVED] Unable to Save Changes in ASE**: This is caused by plugin conflict which happens when [WP STAGING - Backup Duplicator & Migration](https://wordpress.org/plugins/wp-staging/) is active. Props to [@kilimats](https://wordpress.org/support/users/kilimats/) for [reporting it](https://wordpress.org/support/topic/doesnt-save-14/) and to [@allbutone](https://wordpress.org/support/users/allbutone/) for liasing with the developer of WP Staging to [fix things on their end](https://wordpress.org/support/topic/plugin-conflict-prevents-saving/).

### 5.2.3 (2023.06.18)

* **[FIXED] Optimizations >> Image Upload Control**: fixed a small code error that prevented image resizing from actually happening. Props to [@matija80](https://wordpress.org/support/users/matija80/) for [reporting it](https://wordpress.org/support/topic/image-upload-control-resizing-doesnt-work/).

* **[FIXED] Admin Interface >> Disable Dashboard Widgets**: fixed "array offset on value of type bool" error. Props to [@grizdev](https://wordpress.org/support/users/grizdev/) for [reporting it](https://wordpress.org/support/topic/disable-dashboard-widgets-produces-array-offset-on-value-of-type-bool-error/).

* **[FIXED] Admin Interface >> Wider Admin Menu**: fixed an issue where wider admin menu pushed WooCommerce page header content to shift right and be cut-off from the screen. Props to [@malaga16](https://wordpress.org/support/users/malaga16/) for [reporting it](https://wordpress.org/support/topic/admin-menu-sidebar-custom-with-problem/).

* **[FIXED] Utilities >> Password Protection**: fixed PHP Warning. Props to [@gregmount](https://wordpress.org/support/users/gregmount/) for [reporting it](https://wordpress.org/support/topic/php-warning-on-password-protected-login/).

### 5.2.2 (2023.06.14)

* **[FIXED] Security >> Limit Login Attempts**: fixed redirection loop that happens when custom login URL is enabled and user has reached the allowed failed login attempts count. Props to [@allbutone](https://wordpress.org/support/users/allbutone/) for [reporting it](https://wordpress.org/support/topic/error-limit-login-attempts/) and narrowing down the steps to replicate the issue reliably, which helps with troubleshooting.

### 5.2.1 (2023.06.13)

* **[FIXED] Disable Components >> Disable Comments**: Fixed an issue where a JS alert saying "Are you sure you want to do this? The comment changes you made will be lost." popups when trying to update a page / post / post type where commenting has been disabled. Props to [@dagaloni](https://wordpress.org/support/users/dagaloni/) for [thoroughly reporting it](https://wordpress.org/support/topic/are-you-sure-you-want-to-to-this-popup-for-updateting-page/) with screenshots and steps to replicate the issue.

* **[FIXED] Utilities >> View Admin as Role**: Provide a simple and secure method to regain administrator access when something goes wrong while switching to non-administrator role. This occasionally happens when the login session ended and you're being logged out by the system. Instruction on how to regain administrator access has been added to the settings section for the module. Props to [@cebuss](https://wordpress.org/support/users/cebuss/) for [thoroughly reporting it](https://wordpress.org/support/topic/feature-request-avoid-locking-people-out-of-their-site/) and providing a way to regain access via direct changes to the database.

* **[SECURITY] Utilities >> View Admin as Role**: Make sure only users performing role switching can access the role switcher admin bar menu. This ensures that non-administrator users logging in normally won't see the menu and prevent them from performing role switching.

### 5.2.0 (2023.06.10)

* **[ADDED] Admin Interface >> Wider Admin Menu**. Give the admin menu more room to better accommodate wider items. Props to [@dvaer](https://profiles.wordpress.org/dvaer/) and [@kilimats](https://wordpress.org/support/users/kilimats/) for [suggesting this](https://wordpress.org/support/topic/feature-request-wider-admin-menu/).

### 5.1.0 (2023.06.04)

* **[ADDED] Disable Components >> Disable Block-Based Widgets Settings Screen**. Restores the classic widgets settings screen when using a classic (non-block) theme. Props to [@ruralinfo](https://profiles.wordpress.org/ruralinfo/) and [@dvaer](https://profiles.wordpress.org/dvaer/) for [suggesting this](https://wordpress.org/support/topic/feature-request-disable-gutenberg-for-widget-page/).
* **[FIXED] UTILITIES >> SMTP Email Delivery**: fixed an issue where 'from' email is using WP default wordpress@site.com instead of the email defined in module settings. Props to [@scarlywebs](https://wordpress.org/support/users/scarlywebs/) for [reporting it](https://wordpress.org/support/topic/smtp-from-not-working/) and the continued help in testing.
* **[ENHANCED] UTILITIES >> SMTP Email Delivery**: added option to force the usage of the FROM name/email defined in module settings. [Suggested](https://wordpress.org/support/topic/smtp-from-not-working/#post-16762588) by [@scarlywebs](https://wordpress.org/support/users/scarlywebs/).
* **[FIXED]** Custom, right-side footer text now only shows on the plugin's main settings page.

### 5.0.2.4 (2023.06.01)

* **[FIXED] Utilities >> SMTP Email Delivery**: fixed an issue with encoding / decoding of stored password that prevented proper functioning of this module. **Please re-enter your password and save changes**.
* **[FIXED] Utilities >> Password Protection**: fixed an issue with encoding / decoding of stored password that prevented proper functioning of this module. **Please re-enter your password and save changes**.

### 5.0.2.3 (2023.05.25)

* **[FIXED] Log In/Out & Register >> Change Login URL**: URLs that start with the custom login slug no longer redirects to the login page. Props to [@netzzjd](https://profiles.wordpress.org/netzzjd/) for [reporting it](https://wordpress.org/support/topic/change-login-url-redirects/).

### 5.0.2.2 (2023.05.24)

* **[FIXED] Log In/Out & Register >> Change Login URL**: URLs for lost password flow, i.e. password reset flow, are now accessible when this module is enabled. It will no longer redirect to the not_found (404) page. Props to [@scarlywebs](https://wordpress.org/support/users/scarlywebs/) for [reporting it](https://wordpress.org/support/topic/password-reset-url-link/) and help with testing all the way.

### 5.0.2 (2023.05.09)

* **[FIXED] Utilities >> View Admin as Role**: is now only accessible / usable for administrators.
* **[ENHANCE] Admin Interface >> Admin Menu Organizer**: improved description and UI for better clarity.
* **[POLISH] General code polish** to resolve several PHP warnings.

### 5.0.1 (2023.04.29)

* **[FIXED] WordPress Newsboard link in footer** showing up on all wp-admin pages. Now will only show up on the plugin's main page.

### 5.0.0 (2023.04.29)

* **[ADDED] Content Management >> Content Order**. Enable custom ordering of various content types. Useful for displaying them in the desired order in wp-admin and on the frontend. e.g. projects, services, FAQs, team members, etc.
* **[FIXED] Utilities >> Password Protection**: Fix PHP Warning error. Props to [@cvladan](https://github.com/cvladan) for [reporting it](https://wordpress.org/support/topic/minor-php-warning/).
* **[CHANGED] Content Management >> Page and Post Duplication** module has been renamed to "Content Duplication".
* **[ADDED] Translate link** on plugin page's header and in the .org plugin description page. Please [consider helping to translate](https://translate.wordpress.org/projects/wp-plugins/admin-site-enhancements/) the plugin description (a.k.a. README). There are about 100 strings/sentences in it. This will help expand the reach of Admin and Site Enhancements into people who read/speak your language. Special props to [Angelika Reisiger
](https://profiles.wordpress.org/la-geek/), General Translation Editor for Deutsch (German) #de_DE, for tackling translation of the plugin description into [German](https://translate.wordpress.org/locale/de/default/wp-plugins/admin-site-enhancements/) and [German (formal)](https://translate.wordpress.org/locale/de/formal/wp-plugins/admin-site-enhancements/).
* **[ADDED] Link to [WordPress Newsboard](https://bowo.io/asenha-wpn-dsc)** in plugin page's footer. This is another thing I created back in 2014 and is still working to aggregate the latest news, articles, tutorials, podcasts and videos from 100+ WordPress-centric sources.

### 4.9.3 (2023.04.22)

* **[FIXED] Disable Components >> Disable REST API**: fix for error when trying to save edits in Gutenberg editor when Disable REST API module is turned on. Props to [@alexgraphicd](https://profiles.wordpress.org/alexgraphicd/) for [reporting it](https://wordpress.org/support/topic/cant-save-edits-to-posts-or-pages-when-rest-api-is-disabled/).

### 4.9.2 (2023.04.22)

* **[FIXED] Disable Components >> Disable Comments**: fix for certain scenarios where comment form is still shown despite commenting being disabled on the post type. Props to [@crazyserb](https://profiles.wordpress.org/crazyserb/) for [reporting it](https://wordpress.org/support/topic/disable-comments-functionality-doesnt-work-either/), providng temporary admin access, as well as screenshots to help with troubleshooting!

### 4.9.1 (2023.04.19)

* **[ENHANCEMENT] Admin Interface >> Hide Admin Notices**. LearnDash pages was showing up notices below header. This fix will now move them into the hidden, toggleable notices panel.

### 4.9.0 (2023.04.16)

* **[ENHANCEMENT] Content Management >> External Permalinks**. The rel="noopener noreferrer nofollow" attribute will now be added to external permalinks for enhanced security and SEO benefits. 
* **[ADDED] Content Management >> Open All External Links in New Tab**. Force all links to external sites to open in new browser tab via target="\_blank" attribute. The rel="noopener noreferrer nofollow" attribute will also be added for enhanced security and SEO benefits.

### 4.8.3 (2023.04.15)

* **[FIXED] Content Management >> Content Duplication**: fixed isses when duplicating pages built with Oxygen and Bricks builder. May also solve similar issues with other page builders. Please try this on your page builder installation and report in the forum if you're still experiencing issues.

### 4.8.2 (2023.04.03)

* **[FIXED] Admin Interface >> Admin Menu Organizer**: fix for some menu items missing / being hidden in the menu item sortables. Props to [@chrisplaneta](https://profiles.wordpress.org/chrisplaneta/) for [reporting](https://wordpress.org/support/topic/full-of-useful-features-works-without-problems/#post-16620272).

### 4.8.1 (2023.04.03)

* **[FIXED] Admin Interface >> Hide Admin Notices**: now hides notices on some plugins' pages where an extra div exist above the notice divs.
* **[FIXED] Log In/Out & Register >> Change Login URL**: fix an issue where sometimes successful login would redirect to /not_found/ URL.
* **[ENHANCEMENT] Content Management >> Media Replacement**: added browser cache busting via jQuery to ensure the new image is shown on the media/attachment edit page after performing replacement. Elsewhere on wp-admin and on the front end, a hard reload is still needed to flush out browser cache for the old image.

### 4.8.0 (2023.04.02)

* **[ADDED] Utilities >> Multiple User Roles**: Enable assignment of multiple roles during user account creation and editing. This maybe useful for working with roles not defined in WordPress core, e.g. from e-commerce or LMS plugins.


### 4.7.4 (2023.03.05)

* **[ADDED] Content Management >> Enhance List Tables >> Show ID in Action Rows**: on the list tables for pages, all post types, all taxonomies, media, users and comments. Props to [@cvladan](https://github.com/cvladan) for the [feature suggestion](https://github.com/qriouslad/admin-site-enhancements/issues/2).

### 4.7.3 (2023.03.03)

* **[FIXED] Optimizations >> Image Upload Control**: "Unexpected response" error in the media uploader when uploading non-JPG files.

### 4.7.2 (2023.02.25)

* **[FIXED] Disable Components >> Disable Smaller Components >> Disable Emojis** now properly disables emojis in admin pages.

### 4.7.1 (2023.02.24)

* **[CHANGED] Utilities >> Maintenance Mode**. When maintenance mode is enabled, an admin bar icon is shown as an indicator.
* **[CHANGED] Utilities >> Password Protection**. Change background color of admin bar status icon from green to red, to better indicate that the site is (currently) inaccessible to the general public.

### 4.7.0 (2023.02.24)

* **[ADDED] Utilities >> Maintenance Mode**. Show a customizable maintenance page on the frontend while performing a brief maintenance to your site. Logged-in administrators can still view the site as usual.
* **[CHANGED] Disable Components >> Disable Smaller Components >> Disable Dashicons CSS and JS** now excludes the login page. This ensures the login page CSS styles is correctly loaded.
* **[CHANGED] Utilities >> SMTP Email Delivery**. Slight improvement to copy/description for improved clarity.

### 4.6.0 (2023.02.22)

* **[ADDED] Utilities >> SMTP Email Delivery**. Use external SMTP service to ensure notification and transactional emails from your site are being delivered to inboxes.

### 4.5.0 (2023.02.17)

* **[ADDED] Admin Interface >> Clean Up Admin Bar >> Remove the Help tab and drawer**.
* **[ADDED] Disable Components >> Disable Smaller Components**. Prevent smaller components from running or loading. Make the site more secure and load slightly faster. This includes disabling the generator &lt;meta&gt; tag, the Windows Live Writer (WLW) manifest &lt;link&gt; tag, the Really Simple Discovery (RSD) &lt;link&gt; tag and the WordPress shortlink &lt;link&gt; tag in &lt;head&gt;, as well as disabling dashicons CSS and JS files for site visitors, and emoji support for pages, posts and custom post types.

### 4.4.0 (2023.02.05)

* **[ADDED] Custom Code >> Custom Body Class**. Add custom &lt;body&gt; class(es) on the singular view of some or all public post types. Compatible with classes already added using [Custom Body Class plugin](https://wordpress.org/plugins/wp-custom-body-class).

### 4.3.1 (2023.02.05)

* **[CHANGED] Content Management >> Media Replace** option is no longer shown on the grid view of the media library. It will only show up on the attachment edit page which can be reached from both the list view via "Edit or Replace Media" link and the grid view media frame's "Edit more details" link.

### 4.3.0 (2023.01.30)

* **[ADDED] Optimizations >> Image Upload Control**. Resize newly uploaded, large images to a smaller dimension and delete originally uploaded files. BMPs and non-transparent PNGs will be converted to JPGs and resized.
* **[CHANGED] Revisions Control** is now under the Optimizations category.
* **[CHANGED] View Admin as Role** is now under the Utilities category.
* **[CHANGED] Improve title of features** to be shorter and more consistent across categories.

### 4.2.2 (2023.01.24)

* **[FIXED] Admin Interface >> Disable Dashboard Widgets**. Fixed a fatal error issue when there's a widget with priority 'high'. Props to [@samirhp](https://profiles.wordpress.org/samirhp/) for [reporting it](https://wordpress.org/support/topic/error-with-new-update-6/).

### 4.2.1 (2023.01.23)

* **[FIXED] Admin Interface >> Disable Dashboard Widgets**. Fixed missing dashboard widgets list when first trying to activate the feature. Also fixes PHP fatal error when trying to view the dashboard when the feature is activated under such scenario. Props to [@samirhp](https://profiles.wordpress.org/samirhp/) for [reporting it](https://wordpress.org/support/topic/error-with-new-update-6/).

### 4.2.0 (2023.01.23)

* **[ADDED] Admin Interface >> Disable Dashboard Widgets**. Clean up and speed up the dashboard by completely disabling some or all widgets. Disabled widgets won't load any assets nor show up under Screen Options.

### 4.1.0 (2023.01.16)

* **[ADDED] Utilities >> Enable Password Protection**. Password-protect the entire site to hide the content from public view and search engine bots / crawlers. Logged-in administrators can still access normally.

### 4.0.1 (2023.01.12)

* **[FIXED] Content Management >> Enable SVG Upload**: Fixed a bug where uploaded, non-SVG media files does not have metadata generated when Enable SVG Upload is enabled.

### 4.0.0 (2023.01.04)

* **[ADDED] Disable Components >> Disable All Updates**. Completely disable core, theme and plugin updates and auto-updates. Will also disable update checks, notices and emails.

### 3.9.2 (2023.01.03)

* **[FIXED] Custom Code >> Manage robots.txt**. Fixed ["Undefined variable" issue](https://wordpress.org/support/topic/undefine-variable-field_option_value/) reported by [kwbrayton](https://profiles.wordpress.org/kwbrayton/). Also make this feature work on scenarios where an actual robots.txt file exists, not just the virtual one created by default, by WordPress. In such case, the robots.txt file will be renamed to robots_txt_backup_{timestamp}.txt when this feature is enabled. Vice versa, when the feature is disabled, the backup file will be renamed back to robots.txt, so it will be in use again as it were.

### 3.9.1 (2022.12.29)

* **[FIXED] Content Management >> Enable External Permalinks**. Fixed an issue where default WordPress permalink for a post loads white, empty screen when no external permalink has been set for the post.

### 3.9.0 (2022.12.29)

* **[ADDED] Content Management >> Enable External Permalinks**. Enable pages, posts and/or custom post types to have permalinks that point to external URLs. Compatible with links added using [Page Links To](https://wordpress.org/plugins/page-links-to/).

### 3.8.0 (2022.12.27)

* **[ADDED] Optimizations >> Enable Heartbeat Control**. Modify the interval of the WordPress heartbeat API or disable it on admin pages, post creation/edit screens and/or the frontend. This will help reduce CPU load on the server.

### 3.7.0 (2022.12.25)

* **[ADDED] Content Management >> Enable Revisions Control**. Prevent bloating the database by limiting the number of revisions to keep for some or all post types supporting revisions.

### 3.6.1 (2022.12.22)

* **[CHANGED] Custom Code >> Manage robots.txt**. Fixed an issue where code editor was not rendered properly upon clicking the Custom Code tab.

### 3.6.0 (2022.12.22)

* **[CHANGED] Added "Log In/Out & Register" and "Custom Code" categories**. Recategorizes relevant features under these new categories.
* **[ADDED] Log In/Out & Register >> Enable Last Login Column**. Log when users on the site last logged in and display the date and time in the users list table.

### 3.5.0 (2022.12.19)

* **[ADDED] Utilities >> Manage robots.txt**. Easily edit and validate your robots.txt content.

### 3.4.0 (2022.12.16)

* **[ADDED] Utilities >> Enable Log In/Out Menu**. Enable log in, log out and dynamic log in/out menu item for addition to any menu. Depending on the user's logged-in status, the menu item will show up, disappear or change.

### 3.3.0 (2022.12.12)

* **[ADDED] Utilities >> Insert &lt;head&gt;, &lt;body&gt; and &lt;footer&gt; Code**. Easily insert &lt;meta&gt;, &lt;link&gt;, &lt;script&gt; and &lt;style&gt; tags, Google Analytics, Tag Manager, AdSense, Ads Conversion and Optimize code, Facebook, TikTok and Twitter pixels, etc.
* **[CHANGED] Utilities >> Manage ads.txt and app-ads.txt** is now a single settings field.

### 3.2.0 (2022.12.12)

* **[ADDED] Utilities >> Manage ads.txt and app-ads.txt**: Easily edit and validate your ads.txt and app-ads.txt content. Please backup existing ads.txt and app-ads.txt content and remove those files before copying the original content into the ads.txt and app-ads.txt manager in the Utilities tab. If no ads.txt / app-ads.txt files exist in the root directory of your WordPress installation, you can immediately add content for both files.

### 3.1.0 (2022.12.11)

* **[ADDED] Content Management >> Enable Auto-Publishing of Posts with Missed Schedule**: Trigger publishing of scheduled posts of all types marked with "missed schedule", anytime the site is visited. Uses Transients API to reduce load to the DB on busy sites. Will only query the DB once every 15 minutes (at most).

### 3.0.6 (2022.12.08)

* **[CHANGED] Admin Interface >> Admin Menu Organizer**: Enqueue jQuery UI widget.min.js for WP version less than 5.6.0. This ensures the feature works for those WP versions.

### 3.0.5 (2022.12.08)

* **[CHANGED] Admin Interface >> Admin Menu Organizer**: Enqueue the minified version of jQuery UI scripts, as older versions of WP do not have the unminified version.

### 3.0.4 (2022.12.07)

* **[CHANGED] Admin Interface >> Admin Menu Organizer**: Substantially lower priorities of actions that handles rendering of custom menu order, custom menu title and hiding of menu items. This is so that menu items added late by active plugins and theme are handled properly.

### 3.0.3 (2022.12.07)

* **[CHANGED] Admin Interface >> Admin Menu Organizer**: Make sure menu item sortables reflect custom menu order saved in options, especially when this feature is re-enabled. Remove all HTMl tags and content inside them from menu item titles in the sortables.

### 3.0.2 (2022.12.07)

* **[CHANGED] Hide stubborn notices** appearing inside the admin page header (via CSS).

### 3.0.1 (2022.12.01)

* **[CHANGED] Admin Interface >> Admin Menu Organizer**: Make sure newer menu items added by newly installed plugins or activated theme are showing up towards the end of the admin menu and the menu item sortables. Comments and updates counters are now hidden. Sortables for menu separators have been faded out to make actual menu items stand out more.
* **[CHANGED] Disable Components >> Disable Comments**: Ensure trackbacks metabox is also hidden on edit screens of post types where commenting is disabled.

### 3.0.0 (2022.11.30)

* **[ADDED] Disable Components >> Disable Feeds**: Disable all RSS, Atom and RDF feeds. This includes feeds for posts, categories, tags, comments, authors and search. Also removes traces of feed URLs from &lt;head&gt;.


### 2.9.0 (2022.11.30)

* **[ADDED] Disable Components >> Disable REST API**: Disable REST API access for non-authenticated users and remove URL traces from \<head\>, HTTP headers and WP RSD endpoint.

### 2.8.3 (2022.11.29)

* **[CHANGED] Admin Interface >> Admin Menu Organizer**: Fix for missing Show All toggle in certain scenarios.

### 2.8.2 (2022.11.29)

* **[CHANGED] Admin Interface >> Admin Menu Organizer**: Allow customizing menu item title, for menu items added by plugins or the active theme, i.e. not from WordPress core.

### 2.8.1 (2022.11.28)

* **[CHANGED] Admin Interface >> Admin Menu Organizer**: Fixed the issue when menu items are no longer shown upon saving. This happened when Admin Menu Organizer was enabled and directly saved without making any changes to the menu order first. 

### 2.8.0 (2022.11.28)

* **[ADDED] Disable Components >> Disable Gutenberg**: Disable the Gutenberg block editor for some or all applicable post types. Optionally disable frontend block styles / CSS files for the selected post types.

### 2.7.0 (2022.11.27)

* **[ADDED] Disable Components >> Disable Comments**: Disable comments for some or all public post types. When disabled, existing comments will also be hidden on the frontend.
* **[CHANGED] Security >> Limit Login Attempts**: Improved styling of empty datatable, i.e. when there is no failed login attempts logged.
* **[CHANGED] Settings tab position** is now saved to browser cookie, so it persists upon saving changes or revisiting it from elsewhere in wp-admin.
* **[CHANGED] Some code refactoring** to make logical separation and categorization of features clearer. CodeMirror and DataTables assets loading are also moved

### 2.6.0 (2022.11.19)

* **[ADDED] Content Management >> Enable SVG Upload**: Allow some or all user roles to upload SVG files, which will then be sanitized to keep things secure.

### 2.5.0 (2022.11.18)

* **[ADDED] Security >> Limit Login Attempts**: Prevent brute force attacks by limiting the number of failed login attempts allowed per IP address.
* **[CHANGED] Security >> Custom Login URL** feature has been made compatible with Limit Login Attempts feature.
* **[CHANGED] Security >> Change Login URL** now works with the interim login modal window, the one that pops up when user is logged out in the background.
* **[CHANGED] Security >> Change Login URL** adds another process to ensure user is redirected to the dashboard after successful login. It was redirecting to the /not_found/ (404) page in rare occasions as [reported by @vdrover](https://wordpress.org/support/topic/change-login-url-redirects-to-not-found/).
* **[CHANGED] Utilities >> Redirect After Login** will correctly override default login redirection by Change Login URL.
* **[CHANGED] Improve code comments throughout**.

### 2.4.0 (2022.11.10)

* **[ADDED] Utilities >> Enable Custom Frontend CSS**: Add custom CSS on all frontend pages for all user roles.

### 2.3.0 (2022.11.09)

* **[ADDED] Utilities >> Enable Custom Admin CSS**: Add custom CSS on all admin pages for all user roles.

### 2.2.0 (2022.11.09)

* **[ADDED] Security >> Disable XML-RPC**: Protect your site from brute force, DOS and DDOS attacks via XML-RPC. Also disables trackbacks and pingbacks.
* **[ADDED] Expand/collapse toggle** for feature settings that takes up longer vertical space, e.g. Admin Menu Customizer.
* **[CHANGED] Refactor code around plugin settings page**. Now uses separate classes for sections and fields registration, for sanitization of field values and for rendering the fields on the admin page.

### 2.1.0 (2022.11.08)

* **[ADDED] Security >> Obfuscate Author Slugs**: Obfuscate publicly exposed author page URLs that shows the user slugs / usernames, e.g. _sitename.com/author/username1/_ into _sitename.com/author/a6r5b8ytu9gp34bv/_, and output 404 errors for the original URLs. Also obfuscates in _/wp-json/wp/v2/users/_ REST API endpoint. Props to [pull request](https://github.com/qriouslad/admin-site-enhancements/pull/1) from [Wahyu Arief @wahyuief](https://github.com/wahyuief) and [functions](https://plugins.trac.wordpress.org/browser/smart-user-slug-hider/tags/4.0.2/inc/class-smart-user-slug-hider.php) from [Smart User Slug Hider
](https://wordpress.org/plugins/smart-user-slug-hider/).

### 2.0.0 (2022.11.06)

* **[ADDED] Admin Interface >> Admin Menu Organizer**: Customize the order of the admin menu and optionally hide some items.

### 1.9.0 (2022.11.03)

* **[ADDED] Admin Interface >> Hide or Modify Elements**: Easily simplify or customize various admin UI elements, starting with the admin bar.
* **[CHANGED] Content Management >> Enhance List Tables**: this combines previously separate features related to list tables for various post types.

### 1.8.0 (2022.11.03)

* **[ADDED] Admin Interface >> View Admin as Role**: View admin pages and the site (logged-in) as one of the non-administrator user roles.

### 1.7.0 (2022.10.31)

* **[ADDED] Utilities >> Redirect 404 to Homepage**: Perform 301 (permanent) redirect to the homepage for all 404 (not found) pages.

### 1.6.0 (2022.10.31)

* **[ADDED] Utilities >> Redirect After Logout**: Set custom redirect URL for all or some user roles after logout.

### 1.5.0 (2022.10.30)

* **[ADDED] Utilities >> Redirect After Login**: Set custom redirect URL for all or some user roles after login.

### 1.4.0 (2022.10.30)

* **[ADDED] Security >> Change Login URL**: allow for setting a custom login URL to improve site security.

### 1.3.0 (2022.10.29)

* **[ADDED] Admin Interface >> Hide Admin Bar**: Hide it on the front end for all or some user roles.

### 1.2.0 (2022.10.28)

* **[ADDED] Admin Interface >> Hide Admin Notices**: Clean up admin pages by moving notices into a separate panel easily accessible via the admin bar.

### 1.1.0 (2022.10.22)

* **[ADDED] Content Management >> Enable Media Replacement**: Enable easy replacement of any type of media file with a new one while retaining the existing media ID and file name.

### 1.0.0 (2022.10.17)

* Initial stable release. 
## [3.5.0] - 2025-05-08
### Added
- Added: There is a new filter, `gal_logout_delay_timer`, to modify the auto-logout delay timer (default value is `5` seconds).

### Changed
- Compatibility with WordPress 6.8.
- Compatibility with PHP 8.
- Changed: As users may have different Google accounts, and they may not be logged in in the correct one, we now request users to confirm the correct account to use when they want to log in.

### Fixed
- Properly mention WordPress with a capital P.
- Start using where applicable secure `https://` instead of `http://` in internal and external URLs.
- A lot of code styles fixes and cleanups.
- The plugin was generating a lot of PHP Notices and Deprecation notices on PHP 8.
- Translation domain was set incorrectly in the plugin, now it's the same as the plugin slug: `googleappslogin-premium`.
- When making request to check whether there is a new plugin version available, we were not verifying the SSL certificate of wp-glogin.com domain.
- The Google Services library used inside the plugin was not working properly on PHP 8, we addressed a lot of deprecation issues.
- The uploaded Service Account JSON file was not properly deleted after its usage.

<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Satisfy the ABSPATH guard at the top of each class file.
define('ABSPATH', '/tmp/');

// WordPress time constants used by the sync class.
define('MINUTE_IN_SECONDS', 60);
define('DAY_IN_SECONDS',    86400);

WP_Mock::bootstrap();

// Load the classes under test.
require_once dirname(__DIR__) . '/library/class-gca-workday-api.php';
require_once dirname(__DIR__) . '/library/class-gca-sync-users.php';

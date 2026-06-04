<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

define('ABSPATH', '/tmp/');
define('MINUTE_IN_SECONDS', 60);
define('DAY_IN_SECONDS', 86400);

WP_Mock::bootstrap();

// Load plugin constants that library classes reference.
define('GCA_WORKFLOW_FILE',    dirname(__DIR__) . '/gca-publishing-workflow.php');
define('GCA_WORKFLOW_VERSION', '0.1');
define('GCA_WORKFLOW_DIR',     dirname(__DIR__) . '/');

// Load all classes under test.
require_once dirname(__DIR__) . '/library/class-gca-workflow-settings.php';
require_once dirname(__DIR__) . '/library/class-gca-workflow-roles.php';
require_once dirname(__DIR__) . '/library/class-gca-workflow-statuses.php';
require_once dirname(__DIR__) . '/library/class-gca-workflow-notifications.php';
require_once dirname(__DIR__) . '/library/class-gca-workflow-rejection.php';
require_once dirname(__DIR__) . '/library/class-gca-workflow-revisions.php';

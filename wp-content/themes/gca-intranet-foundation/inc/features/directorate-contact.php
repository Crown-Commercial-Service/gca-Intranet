<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Directorate Contact Popup
//
// Renders a floating contact popup in the footer for pages tagged with a
// responsible_team taxonomy term. The popup title, description, and contact
// items are configured on the term edit screen via ACF fields.
// -----------------------------------------------------------------------------

gca_register_feature_flag('directorate-contact', [
    'label'       => 'Directorate Contact Popup',
    'description' => 'Shows a contact popup in the footer for pages tagged with a responsible team term.',
    'default'     => false,
    'tags'        => ['ui', 'contact'],
]);

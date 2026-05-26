<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Team Contact Card
//
// Renders a click-to-expand contact card in the footer for pages tagged with a
// responsible_team taxonomy term. The title, description, and contact items are
// configured on the term edit screen via ACF fields.
// -----------------------------------------------------------------------------

gca_register_feature_flag('directorate-contact', [
    'label'       => 'Team Contact Card',
    'description' => 'Shows an expandable contact card in the footer for pages tagged with a responsible team term.',
    'default'     => false,
    'tags'        => ['ui', 'contact'],
]);

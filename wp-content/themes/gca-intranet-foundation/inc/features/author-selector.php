<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Author Selector
//
// Replaces the native WordPress author meta box on the blog and work_update
// post types with a searchable Select2 dropdown. Each option shows the user's
// profile image (Google SSO photo → Gravatar → WP default). All users are
// pre-loaded as inline JSON — no AJAX required.
// -----------------------------------------------------------------------------

gca_register_feature_flag('author-selector', [
    'label'       => 'Author Selector',
    'description' => 'Replaces the default WordPress author meta box on blog and work_update posts with a searchable Select2 dropdown that displays each user\'s profile image.',
    'default'     => true,
    'tags'        => ['admin', 'ui', 'authors'],
]);

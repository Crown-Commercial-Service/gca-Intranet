<?php

if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
        'page_title'  => 'Community Settings',
        'menu_title'  => 'Community Settings',
        'menu_slug'   => 'gca-community-settings',
        'capability'  => 'edit_posts',
        'redirect'    => false,
        'icon_url'    => 'dashicons-admin-generic',
        'position'    => 30
    ]);
}

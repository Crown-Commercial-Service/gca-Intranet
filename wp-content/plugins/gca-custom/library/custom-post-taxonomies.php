<?php

add_action('init', 'gca_register_my_taxonomies');

function gca_register_my_taxonomies()
{
    register_taxonomy('event_location', array('event'), array(
        'hierarchical' => true,
        'label'        => 'Event Location',
        'capabilities' => ['assign_terms' => 'edit_posts', 'edit_terms' => 'manage_categories'],
        'show_in_rest' => true
    ));

    register_taxonomy('label', array('news','post', 'blog', 'work_update'), array(
        'hierarchical' => true,
        'label'        => 'Label *strategic use case only*',
        'capabilities' => ['assign_terms' => 'edit_posts', 'edit_terms' => 'manage_categories'],
        'show_in_rest' => true
    ));

    register_taxonomy('content_type', array('post', 'page'), array(
        'hierarchical' => true,
        'label'        => 'Content Type',
        'capabilities' => ['assign_terms' => 'edit_posts', 'edit_terms' => 'manage_categories'],
        'show_in_rest' => true
    ));
    
    register_taxonomy('responsible_team', array('work_update', 'page', 'post'), array(
        'hierarchical' => true,
        'label'        => 'Responsible directorate/team',
        'capabilities' => ['assign_terms' => 'edit_posts', 'edit_terms' => 'manage_categories'],
        'show_in_rest' => true
    ));

    register_taxonomy('audience', array('post', 'page', 'event'), array(
        'hierarchical' => true,
        'label'        => 'Audience',
        'capabilities' => ['assign_terms' => 'edit_posts', 'edit_terms' => 'manage_categories'],
        'show_in_rest' => true
    ));

}

add_filter( "radio_buttons_for_taxonomies_no_term_event_location", "__return_FALSE" );
add_filter( "radio_buttons_for_taxonomies_no_term_label", "__return_FALSE" );
add_filter( "radio_buttons_for_taxonomies_no_term_content_type", "__return_FALSE" );
add_filter( "radio_buttons_for_taxonomies_no_term_responsible_team", "__return_FALSE" );
add_filter( "radio_buttons_for_taxonomies_no_term_audience", "__return_FALSE" );

// Allow Publishers to access TablePress
add_filter('tablepress_user_capability', function() {
    return 'publish_posts';
});

add_filter('pp_capabilities_roles_list', function($roles) {
    unset($roles['author']);
    unset($roles['editor']);
    unset($roles['contributor']);
    return $roles;
});
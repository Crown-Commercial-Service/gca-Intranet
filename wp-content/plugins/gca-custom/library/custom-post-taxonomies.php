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

    register_taxonomy('sub_categories', array('post', 'work_update', 'blog'), array(
        'hierarchical' => true,
        'label'        => 'Sub Categories',
        'capabilities' => ['assign_terms' => 'edit_posts', 'edit_terms' => 'manage_categories'],
        'show_in_rest' => true
    ));

    register_taxonomy('content_type', array('post', 'blog', 'work_update', 'event'), array(
        'hierarchical' => true,
        'label'        => 'Content Type',
        'capabilities' => ['assign_terms' => 'edit_posts', 'edit_terms' => 'manage_categories'],
        'show_in_rest' => true
    ));
    
    register_taxonomy('responsible_team', array('work_update'), array(
        'hierarchical' => true,
        'label'        => 'Responsible directorate/team',
        'capabilities' => ['assign_terms' => 'edit_posts', 'edit_terms' => 'manage_categories'],
        'show_in_rest' => true
    ));

    register_taxonomy('audience', array('post', 'work_update', 'blog', 'event'), array(
        'hierarchical' => true,
        'label'        => 'Audience',
        'capabilities' => ['assign_terms' => 'edit_posts', 'edit_terms' => 'manage_categories'],
        'show_in_rest' => true
    ));

}



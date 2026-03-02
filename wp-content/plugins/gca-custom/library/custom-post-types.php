<?php

add_action('init', 'gca_register_my_cpts');

function gca_register_my_cpts()
{

    $news = array(
        'labels' => array(
            'name'          => 'News',
            'singular_name' => 'News',
            'menu_name'     => 'News',
            'add_new'       => 'Add News',
            'add_new_item'  => 'Add News',
            'new_item'      => 'New News',
            'edit_item'     => 'Edit News',
            'view_item'     => 'View News',
            'all_items'     => 'All News',
        ),
        'taxonomies' => ['category'],
        "menu_icon" => "dashicons-admin-post",
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'rest_base' => 'news',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
    );

    $blog = array(
        'labels' => get_labels('Blog'),
        "menu_icon" => "dashicons-welcome-write-blog",
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'rest_base' => 'blogs',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
    );


    $event = array(
        'labels' => get_labels('event'),
        'taxonomies' => ['category'],
        "menu_icon" => "dashicons-calendar-alt",
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'rest_base' => 'events',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
    );

    $work_update = array(
        'labels' => get_labels('work update'),
        "menu_icon" => "dashicons-sort",
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'rest_base' => 'work_updates',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
    );


    $information = array(
        'labels' => get_labels('information'),
        "menu_icon" => "dashicons-welcome-learn-more",
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'rest_base' => 'information',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
    );

    register_post_type('news', $news);
    register_post_type('blog', $blog);
    register_post_type('event', $event);
    register_post_type('work_update', $work_update);
    register_post_type('information', $information);
}


function get_labels($name)
{
    $name =  ucwords($name);
    $plural = $name . 's';

    return array(
        'name'          => $plural,
        'singular_name' => $name,
        'menu_name'     => $plural,
        'add_new'       => 'Add New ' . $name,
        'add_new_item'  => 'Add New ' . $name,
        'new_item'      => 'New ' . $name,
        'edit_item'     => 'Edit ' . $name,
        'view_item'     => 'View ' . $name,
        'all_items'     => 'All ' . $plural,
    );
}

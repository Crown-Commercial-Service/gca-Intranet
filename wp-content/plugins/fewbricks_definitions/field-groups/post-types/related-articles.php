<?php
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

$location = [
    [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'news' ] ],
    [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'blog' ] ],
    [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'work_update' ] ],
    [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'event' ] ],
];

$fg = ( new fewacf\field_group( 'Related Articles', '202605200001a', $location, 100, [
    'position'                         => 'normal',
    'names_of_items_to_hide_on_screen' => [],
] ) );

$fg->add_field( new acf_fields\text( 'Heading', 'related_articles_heading', '202605200002a', [
    'instructions'  => 'Always use the title Related articles.',
    'default_value' => 'Related articles',
    'maxlength'     => 50,
] ) );

$fg->add_field( new acf_fields\relationship( 'Select specific articles', 'related_articles_posts', '202605200004a', [
    'instructions'  => 'Maximum of 3 articles. Use the filters to narrow by post type and taxonomy. If left empty, the latest posts will be shown.',
    'post_type'     => [ 'news', 'blog', 'work_update', 'event' ],
    'max'           => 3,
    'filters'       => [ 'search', 'taxonomy' ],
    'return_format' => 'object',
] ) );

$fg->register();

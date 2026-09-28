<?php

use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

$location = [
    [
        [
            'param'    => 'options_page',
            'operator' => '==',
            'value'    => 'gca-community-settings'
        ]
    ]
];

$fg = ( new fewacf\field_group( 'Guidance Boxes', '202609161224a', $location, 10 ));

$fg->add_field( new acf_fields\wysiwyg( 'Blog Listing Page Guidance', 'blog_guidance_box', '202609161224b', [
    'instructions' => 'Add guidance content to appear under the filters on the Blog listing page.',
] ) );

$fg->add_field( new acf_fields\wysiwyg( 'Social Wall Guidance', 'social_wall_guidance_box', '202609161224c', [
    'instructions' => 'Add guidance content to appear on the Social Wall page.',
] ) );

$fg->register();

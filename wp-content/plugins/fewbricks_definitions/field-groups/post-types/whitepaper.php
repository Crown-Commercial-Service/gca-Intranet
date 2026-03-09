<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;


// --- Setting up fields for the supplier custom post type ---

$location = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'whitepaper'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Whitepaper Details', '202001131107a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
        'the_content'
    ]
]));

$fg1->add_field( new acf_fields\file( 'Whitepaper', 'whitepaper_file', '202001131109a', [
    'required' => 1
] ) );

$fg1->add_field( new acf_fields\wysiwyg( 'Whitepaper Form Introduction', 'form_introduction', '202002281430a', [
    'instructions' => 'Optional text to display above the form when requesting access to the Whitepaper'
] ) );

$fg1->add_field( new acf_fields\text( 'Description', 'description', '202002050905a', [
    'instructions' => 'This hidden description is sent to Salesforce when the form is submitted.'
] ) );

$fg1->add_field( new acf_fields\text( 'Campaign code', 'campaign_code', '202002041115a', [
    'instructions' => 'An optional campaign code which will be sent to Salesforce on submission.'
] ) );

$fg1->register();


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
            'value' => 'webinar'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Webinar Details', '202001131703a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
        'the_content'
    ]
]));

$fg1->add_field( new acf_fields\wysiwyg( 'Webinar Form Introduction', 'form_introduction', '202002051740a', [
    'instructions' => 'Optional text to display above the form when requesting access to the Webinar'
] ) );

$fg1->add_field( new acf_fields\text( 'Webinar Additional text', 'additional_text', '202002051219a', [
    'instructions' => 'Optional text to display underneath the thumbnail when requesting the Webinar'
] ) );

$fg1->add_field( new acf_fields\oembed('Webinar Video', 'webinar_video', '202001150013a', [
    'required' => 1
]));

$fg1->add_field( new acf_fields\text( 'Description', 'description', '202002050905a', [
    'instructions' => 'This hidden description is sent to Salesforce when the form is submitted.'
] ) );

$fg1->add_field( new acf_fields\text( 'Campaign code', 'campaign_code', '202002041115b', [
    'instructions' => 'An optional campaign code which will be sent to Salesforce on submission.'
] ) );

$fg1->register();


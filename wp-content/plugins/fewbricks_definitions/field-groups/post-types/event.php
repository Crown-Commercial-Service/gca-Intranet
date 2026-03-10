<?php
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

$location = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'event'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Event Details', '202603101020a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
    ]
]));

$fg1->add_field( new acf_fields\date_time_picker( 'Event start date (and time)', 'start_datetime', '202603101020b', [
    'instructions' => 'The start date and time for the event',
    'display_format' => 'd-m-Y g:i a',
    'return_format' => 'd-m-Y g:i a',
    'required' => 1
] ) );

$fg1->add_field( new acf_fields\date_time_picker( 'Event end date (and time)', 'end_datetime', '202603101020c', [
    'instructions' => 'The end date and time for the event',
    'display_format' => 'd-m-Y g:i a',
    'return_format' => 'd-m-Y g:i a',
    'required' => 1
] ) );


$fg1->add_field( new acf_fields\text( 'CTA Label', 'secondary_cta_label', '202603101020d') );

$fg1->add_field( new acf_fields\text( 'CTA Destination', 'secondary_cta_destination', '202603101020e') );

$fg1->register();

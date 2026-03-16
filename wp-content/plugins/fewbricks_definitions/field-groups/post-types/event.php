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

$fg1->add_field( new acf_fields\date_picker( 'Event start date', 'start_date', '202603101020b', [
    'instructions' => 'The start date for the event',
    'display_format' => 'd-m-Y',
    'return_format' => 'd-m-Y',
    'required' => 1
] ) );

$fg1->add_field( new acf_fields\time_picker( 'Event start time', 'start_time', '202603101021c', [
    'instructions'   => 'The start time for the event (e.g. 09:30)',
    'display_format' => 'H:i',
    'return_format'  => 'H:i',
] ) );

$fg1->add_field( new acf_fields\date_time_picker( 'Event end date', 'end_date', '202603101020c', [
    'instructions' => 'The end date for the event',
    'display_format' => 'd-m-Y',
    'return_format' => 'd-m-Y',
] ) );

$fg1->add_field( new acf_fields\time_picker( 'Event end time', 'end_time', '202603131021c', [
    'instructions'   => 'The end time for the event (e.g. 14:30)',
    'display_format' => 'H:i',
    'return_format'  => 'H:i',
] ) );

$fg1->add_field( new acf_fields\text( 'CTA Label', 'secondary_cta_label', '202603101020d') );

$fg1->add_field( new acf_fields\text( 'CTA Destination', 'secondary_cta_destination', '202603101020e') );

$fg1->register();

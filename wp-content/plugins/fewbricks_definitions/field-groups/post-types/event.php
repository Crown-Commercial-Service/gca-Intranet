<?php
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;


// --- Setting up fields for the framework custom post type ---

$location = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'event'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Event Details', '202002061432a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
    ]
]));


$fg1->add_field( new acf_fields\image( 'Event image', 'image', '202002061421a', [
    'instructions' => 'This image will be shown in featured events on a landing page and as the thumbnail image on the events page. Minimum size: 160&times;80. Recommended size: 320&times;160.',
] ) );

$fg1->add_field( new acf_fields\text( 'Summary', 'event_summary', '202505191545a', [
    'instructions' => 'A few short sentences - a maximum of 180 characters.',
    'required' => 1,
    'maxlength' => 180
] ) );

$fg1->add_field( new acf_fields\wysiwyg( 'Description', 'description', '202002061420a', [
    'instructions' => 'If you add an image in this section it will show in the body copy only.',
    'required' => 1
] ) );

$fg1->add_field( new acf_fields\text( 'CTA Label', 'cta_label', '202002061456a', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\text( 'CTA Destination', 'cta_destination', '202002061456b', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\date_time_picker( 'Event start date (and time)', 'start_datetime', '202002061422a', [
    'instructions' => 'The start date and time for the event',
    'display_format' => 'd-m-Y g:i a',
    'return_format' => 'd-m-Y g:i a',
    'required' => 1
] ) );

$fg1->add_field( new acf_fields\date_time_picker( 'Event end date (and time)', 'end_datetime', '202002061423a', [
    'instructions' => 'The end date and time for the event',
    'display_format' => 'd-m-Y g:i a',
    'return_format' => 'd-m-Y g:i a',
    'required' => 1
] ) );


// Setting up fields for event location

$online = [
    [
        'field' => '202208221314b',
        'operator' => '==',
        'value' => 'Online'
    ]
];

$in_person =  [
    [
        'field' => '202208221314b',
        'operator' => '==',
        'value' => 'In Person'
    ]
];

$hybrid = [
    [
        'field' => '202208221314b',
        'operator' => '==',
        'value' => 'Online and In Person'
    ]
];


$fg2 = ( new fewacf\field_group( 'Event Location', '202208251516a', $location, 10, [
    'conditional_logic' => 1
]));

$fg2->add_field( new acf_fields\radio( 'Event Location Type', 'location_type', '202208221314b', [
    'choices' => array(
        'Online'	=> 'Online',
        'In Person'	=> 'In Person',
        'Online and In Person'	=> 'Online and In Person'
    ),
    'layout' => 'horizontal',
    'default_value' => 'online',
    'required' => 1,
] ) );

$place_name = new acf_fields\text( 'Place Name', 'place_name', '202208251556b', [
    'instructions' => '',
    'conditional_logic' => [ $in_person, $hybrid ],
    'required' => 1,
] );

$street_address = new acf_fields\text( 'Street Address', 'street_address', '202208251557b', [
    'instructions' => '',
    'conditional_logic' => [ $in_person, $hybrid ],
    'required' => 1,
] );

$address_locality = new acf_fields\text( 'Town or City', 'address_locality', '202208251558b', [
    'instructions' => '',
    'conditional_logic' => [ $in_person, $hybrid ],
    'required' => 1,
] );

$postal_code = new acf_fields\text( 'Postal Code', 'postal_code', '202208251559b', [
    'instructions' => '',
    'conditional_logic' => [ $in_person, $hybrid ],
    'required' => 1,
] );

$address_region = new acf_fields\text( 'Region', 'address_region', '202208251600b', [
    'instructions' => '',
    'conditional_logic' => [ $in_person, $hybrid ],
] );

$address_country = new acf_fields\text( 'Country', 'address_country', '202208251601b', [
    'instructions' => '',
    'conditional_logic' => [ $in_person, $hybrid ],
    'required' => 1,
] );

$fg2->add_field($place_name);
$fg2->add_field($street_address);
$fg2->add_field($address_locality);
$fg2->add_field($postal_code);
$fg2->add_field($address_region);
$fg2->add_field($address_country);

$fg2->register();


$fg1->add_field( new acf_fields\text( 'Secondary CTA Label', 'secondary_cta_label', '202002061517a', [
    'instructions' => 'A secondary CTA to display beneath the event location (appears in the sidebar on large screens)',
] ) );

$fg1->add_field( new acf_fields\text( 'CTA Destination', 'secondary_cta_destination', '202002061517b', [
    'instructions' => 'A secondary CTA to display beneath the event location (appears in the sidebar on large screens)',
] ) );

$fg1->register();

/*
description content (formattable, no limit, able to add images to the content)

event image (to be used for thumbnail too)

CTA with label and defined destination (if required)

event date / date range (required)

event start and finish time (required)

event location (free text, if required)
=
right hand box CTA with label and defined destination (if required)

Category tags (enable multiple tags)

Sector tags (enable multiple tags)

Audience tag (customer / supplier event)

Then they should be stored with the event in Wordpress
*/

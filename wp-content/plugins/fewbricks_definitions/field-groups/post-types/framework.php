<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;


// --- Setting up fields for the framework custom post type ---

$location = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'framework'
        ]
    ]
];

$notCasLocation = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'framework'
        ],
        [
            'param' => 'post_taxonomy',
            'operator' => '!=',
            'value' => 'framework_type:cas-framework'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Framework Details', '201902041237a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
        'the_content',
        'excerpt'
    ]
]));


$fg1->add_field( new acf_fields\text( 'Framework ID', 'framework_id', '201902041405a', [
    'instructions' => 'Framework ID from Salesforce',
    'maxlength' => 50,
    'required' => 1,
    'readonly' => 1
] ) );


$fg1->add_field( new acf_fields\wysiwyg( 'Summary', 'framework_summary', '201902181515a', [
    'instructions' => 'A few short sentences - a maximum of 180 characters.'
] ) );

$fg1->add_field( new acf_fields\wysiwyg('Available for', 'framework_availability', '202205101128a', [
    'instructions' => 'Include who can use this agreement. For example, central government, charities, devolved administrations or other.'
]));

$fg1->add_field( new acf_fields\wysiwyg('Updates', 'framework_updates', '201902251546a'));

$fg1->add_field( (new acf_fields\repeater('CAS Updates', 'framework_cas_updates', '202205130119a', [
    'button_label' => 'Add Update'
]))
    ->add_sub_field( new acf_fields\date_picker( 'Date', 'date', '202002061422a', [
        'instructions' => 'Date of the update',
        'display_format' => 'd-m-Y',
        'return_format' => 'd-m-Y',
        'required' => 1,
        'wrapper' => array (
            'width' => '20',
            'class' => '',
            'id' => '')
    ] ) )
    ->add_sub_field( new acf_fields\wysiwyg( 'Update', 'update', '202205130119b', [
        'required' => 1,
        'wrapper' => array (
            'width' => '80',
            'class' => '',
            'id' => '')
    ] ) )
);

$fg1->add_field( new acf_fields\wysiwyg( 'Description', 'framework_description', '201902041416a', [
    'instructions' => 'Describe what the agreement can be used for and what kind of products or services can be bought.',
] ) );

$fg1->add_field( new acf_fields\wysiwyg('When you can\'t use this agreement', 'framework_cannot_use', '202205101129a', [
    'instructions' => 'Describe what can\'t be procured through this agreement. If there is a more suitable agreement for these needs, list those.',
]));

$fg1->add_field( new acf_fields\wysiwyg( 'Benefits', 'framework_benefits', '201902041814a', [
    'instructions' => 'List the benefits of using this specific agreement (not the benefits of using CCS).',
] ) );

$fg1->add_field( new acf_fields\wysiwyg( 'How to buy', 'framework_how_to_buy', '201902041411a', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\wysiwyg( 'Information and documents for suppliers', 'framework_info_docs_for_suppliers', '201903211125a', [
    'instructions' => 'Only displayed for DPS Frameworks'
] ) );

$fg1->register();



$fg2 = ( new fewacf\field_group( 'Documents', '201902051045a', $notCasLocation, 20 ));

$fg2->add_field( new acf_fields\wysiwyg( 'Documents - Updates', 'framework_documents_updates', '201902051044a', [
    'instructions' => '',
] ) );

$fg2->add_field( (new acf_fields\repeater('Documents - Downloads', 'framework_documents', '201902051040a', [
    'button_label' => 'Add Document'
]))
    ->add_sub_field( new acf_fields\file( 'Document', 'framework_documents_document', '201902051043a' ) )
);

$fg2->register();



$fg3 = ( new fewacf\field_group( 'Keywords', '201902201440a', $location, 30 ));

$fg3->add_field( new acf_fields\textarea( 'Keywords', 'framework_keywords', '201902201448a', [
    'instructions' => 'Optionally enter some keywords (separated by comma\'s) which will be used to help ensure accurate search output (maximum combined length, 3000 characters)',
    'maxlength' => 3000
] ) );

$fg3->register();



$fg4 = ( new fewacf\field_group( 'Upcoming Deal Details', '201903081626a', $notCasLocation, 30 ));

$fg4->add_field( new acf_fields\wysiwyg( 'Upcoming Deal Details', 'framework_upcoming_deal_details', '201903081627a', [
] ) );

$fg4->register();

$fg5 = (new fewacf\field_group('Upcoming Agreement Summary', '202403081626a', $notCasLocation, 30));

$fg5->add_field(new acf_fields\wysiwyg('Upcoming Agreement Summary', 'framework_upcoming_deal_summary', '202403081627a', [
    'instructions' => 'Summary for the upcoming agreements page - a maximum of 180 characters.'
]));

$fg5->register();

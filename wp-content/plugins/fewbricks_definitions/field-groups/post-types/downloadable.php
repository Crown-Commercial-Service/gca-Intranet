<?php

use fewbricks\bricks as bricks;
use fewbricks\acf as fewacf;
use fewbricks\acf\fields as acf_fields;


// --- Setting up fields for the downloadable resources custom post type ---

$location = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'downloadable'
        ]
    ]
];

$fg1 = (new fewacf\field_group('Downloadable Resources Details', '202203293819a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
        'the_content'
    ]
]));

$fg1->add_field(new acf_fields\text('Downloadable Resource Type', 'downloadable_type', '202202255670a', [
    'default_value' => 'Downloadable Resource',
    'instructions' => 'This text appears above the main title on the gated form and specifies the type of downloadable resource. Default is Downloadable Resource.'
]));

$fg1->add_field(new acf_fields\wysiwyg('Downloadable Resources Form Introduction', 'form_introduction', '202203297695a', [
    'instructions' => 'Optional text to display above the form when requesting access to the Downloadable Resource'
]));

$fg1->add_field(new acf_fields\text('Prompt text', 'prompt_text', '202209021654a', [
    'default_value' => 'To read this Downloadable Resource, we need a few details',
    'instructions' => 'The text to be displayed just after the intro text on the gated form. Note default is: To read this Downloadable Resource, we need a few details'
]));

$fg1->add_field(new acf_fields\text('Download confirmation page title', 'download_confirmation_msg', '202202257695a', [
    'default_value' => 'You can now download this Downloadable Resource',
    'instructions' => 'Title to display on download confirmation page. Note default is: You can now download this Downloadable Resource'
]));

$fg1->add_field(new acf_fields\wysiwyg('Downloadable Resources URL', 'downloadable_resources_url', '202203301985a', [
    'instructions' => 'URL to be displayed on confirmation page (the first link)'
]));

$fg1->add_field(new acf_fields\file('Downloadable Resources File', 'downloadable_resources_file', '202203291846a', [
    'required' => 1
]));

$fg1->add_field(new acf_fields\text('Downloadable Resources File Text', 'downloadable_resources_file_text', '202203251648a'));

$fg1->add_field(new acf_fields\oembed('Embed Video', 'embed_video', '202201150013a'));

$fg1->add_field(new acf_fields\text('Description', 'description', '202203290739a', [
    'instructions' => 'This hidden description is sent to Salesforce when the form is submitted.'
]));

$fg1->add_field(new acf_fields\text('Campaign code', 'campaign_code', '202203292266a', [
    'instructions' => 'An optional campaign code which will be sent to Salesforce on submission.'
]));

$fg1->register();

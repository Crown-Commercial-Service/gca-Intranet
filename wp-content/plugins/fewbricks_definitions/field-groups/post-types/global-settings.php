<?php

use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

$location = [
    [
        [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'global_settings',
        ]
    ]
];

$fg_gs = (new fewacf\field_group('Global Settings', '202603101200a', $location, 10));

$fg_gs->add_field(new acf_fields\text('Cookies banner title', 'cookies_title', '202603101200b', [
    'instructions'  => 'The heading displayed at the top of the cookies banner.',
    'default_value' => 'Cookies on GCA',
    'required'      => 1,
]));

$fg_gs->add_field(new acf_fields\wysiwyg('Cookies banner content', 'cookies_content', '202603101200c', [
    'instructions' => 'The body text shown inside the cookies banner. Keep it concise.',
    'toolbar'      => 'basic',
    'media_upload' => 0,
]));

$fg_gs->add_field(new acf_fields\text('Cookies policy version', 'cookies_policy_version', '202603101200d', [
    'instructions'  => 'Update this value whenever the cookies policy changes (e.g. use today\'s date: 2026-03-10). Changing it forces the banner to reappear for all users.',
    'default_value' => '1.0',
    'required'      => 1,
]));

$fg_gs->register();

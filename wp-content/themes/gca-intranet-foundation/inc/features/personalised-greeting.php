<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

gca_register_feature_flag('personalised-greeting', [
    'label'       => 'Personalised Greeting',
    'description' => 'Shows a personalised time-based greeting on the homepage for logged-in users.',
    'default'     => false,
    'tags'        => ['ui', 'homepage', 'personalisation'],
]);

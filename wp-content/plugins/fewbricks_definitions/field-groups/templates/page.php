<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

// --- Setting components on default page template ---

$location = [
    [
        [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'page'
        ],
	    [
		    'param'    => 'page_template',
		    'operator' => '!=',
		    'value'    => 'page-templates/landing.php'
	    ],
	    [
		    'param'    => 'page_template',
		    'operator' => '!=',
		    'value'    => 'page-templates/products-and-services.php'
	    ],
	    [
		    'param'    => 'page_template',
		    'operator' => '!=',
		    'value'    => 'page-templates/sectors.php'
	    ]

    ],
];

$fg8 = ( new fewacf\field_group( 'Page components', '202001031015a', $location, 50, [
    'names_of_items_to_hide_on_screen' => [
    ],
	'position' => 'normal',
] ));

$fg8->add_brick((new bricks\group_page_content_default('page_components', '202001031015b')));


$fg8->register();

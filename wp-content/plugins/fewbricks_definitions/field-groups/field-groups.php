<?php

use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;



/**
 * Import field groups for post types
 */
include('post-types/framework.php');
include('post-types/cas-framework.php');
include('post-types/lot.php');
include('post-types/supplier.php');
include('post-types/whitepaper.php');
include('post-types/webinar.php');
include('post-types/event.php');
include('post-types/downloadable.php');



/**
 * Import field groups for page templates
 */
include('templates/page.php');
include('templates/post.php');
include('templates/landing.php');
include('templates/products-and-services.php');
include('templates/sectors.php');



/**
 * Import field groups for taxonomies (WIP)
 */
//include('taxonomies/pillars.php');



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
    ]
];

$fg3 = ( new fewacf\field_group( 'Keywords', '201902201440a', $location, 30 ));

$fg3->add_field( new acf_fields\textarea( 'Keywords', 'framework_keywords', '201902201448a', [
    'instructions' => 'Optionally enter some keywords (separated by comma\'s) which will be used to help ensure accurate search output (maximum combined length, 1000 character)',
    'maxlength' => 1000
] ) );

$fg3->register();

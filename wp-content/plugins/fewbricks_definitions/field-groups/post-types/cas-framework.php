<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;


// --- Setting up fields for the framework custom post type ---

$location = [
    [
        [
            'param' => 'post_taxonomy',
            'operator' => '==',
            'value' => 'framework_type:cas-framework'
        ]
    ]
];

$temp1 = ( new fewacf\field_group( 'Documents - Buyer Guide', '202206011031a', $location, 30 ));
$temp1->add_field( new acf_fields\wysiwyg( 'Documents - Buyer Guide', 'framework_customer_guide', '202206011031b' ) );
$temp1->register();

$temp2 = ( new fewacf\field_group( 'Documents - Core terms and conditions', '202206011032a', $location, 30 ));
$temp2->add_field( new acf_fields\wysiwyg( 'Documents - Core terms and conditions', 'framework_core_terms_conditions', '202206011032b' ) );
$temp2->register();

$temp3 = ( new fewacf\field_group( 'Documents - Call-off order form', '202206011033a', $location, 30 ));
$temp3->add_field( new acf_fields\wysiwyg( 'Documents - Call-off order form', 'framework_call_off_order_form', '202206011033' ) );
$temp3->register();

$temp4 = ( new fewacf\field_group( 'Documents - Joint Schedules', '202206011034a', $location, 30 ));
$temp4->add_brick((new bricks\cas_joint_schedules('framework_cas_joint_schedules', '202206011034b')));
$temp4->register();

$temp5 = ( new fewacf\field_group( 'Documents - Call Off Schedules', '202206011035a', $location, 30 ));
$temp5->add_brick((new bricks\cas_call_off_schedules('framework_cas_call_off_schedules', '202206011035b')));
$temp5->register();

$temp6 = ( new fewacf\field_group( 'Documents - Framework Schedules', '202206011036a', $location, 30 ));
$temp6->add_brick((new bricks\cas_framework_schedules('framework_cas_framework_schedules', '202206011036b')));
$temp6->register();

$temp7 = ( new fewacf\field_group( 'Documents - Templates', '202206011037a', $location, 30 ));
$temp7->add_field( new acf_fields\wysiwyg( 'Documents - Templates', 'framework_templates', '202206011037b' ) );
$temp7->register();

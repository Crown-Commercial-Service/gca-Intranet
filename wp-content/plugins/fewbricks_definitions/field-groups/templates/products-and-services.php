<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

// --- Setting components on default page template ---

$location = [
	[
		[
			'param' => 'page_template',
			'operator' => '==',
			'value' => 'page-templates/products-and-services.php'
		]
	]
];



/**
 * Define the field group
 *
 * Field groups with a lower menu_order will appear first on the edit screens (change by 10,20,30 increments to give yourself space to add)
 */
$field_group = ( new fewacf\field_group( 'Lead Text', '202002181348a', $location, 20, [
	'position' => 'acf_after_title',
	'names_of_items_to_hide_on_screen' => [
		'excerpt',
		'comments'
	]
] ));

/**
 * Define the fields
 */
$field_group->add_field( new acf_fields\text( 'Lead Text', 'page_lead_text', '202002181348b', [
	'instructions' => 'Optionally enter some lead text for the page (maximum length, 200 characters)',
	'maxlength' => 200
] ) );

/*
 * Register the field group
 */
$field_group->register();



/**
 * Define the field group
 *
 * Field groups with a lower menu_order will appear first on the edit screens (change by 10,20,30 increments to give yourself space to add)
 */
$field_group = ( new fewacf\field_group( 'Page components', '202002181348c', $location, 50, [
	'position' => 'normal',
]));

/**
 * Define the fields
 */
$field_group->add_brick((new bricks\group_page_content_default('page_components', '202002181348d')));

/*
 * Register the field group
 */
$field_group->register();

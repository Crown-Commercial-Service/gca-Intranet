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
			'value' => 'page-templates/sectors.php'
		]
	]
];



/**
 * Define the field group
 *
 * Field groups with a lower menu_order will appear first on the edit screens (change by 10,20,30 increments to give yourself space to add)
 */
$field_group = ( new fewacf\field_group( 'Lead Text', '202002181433a', $location, 20, [
	'position' => 'acf_after_title',
	'names_of_items_to_hide_on_screen' => [
	]
] ));

/**
 * Define the fields
 */
$field_group->add_field( new acf_fields\text( 'Lead Text', 'page_lead_text', '202002181433b', [
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
$field_group = ( new fewacf\field_group( 'Page components', '202002181433c', $location, 50, [
	'position' => 'normal',
]));

/**
 * Define the fields
 */
$field_group->add_brick((new bricks\group_page_content_default('page_components', '202002181433d')));

/*
 * Register the field group
 */
$field_group->register();




/**
 * Define the field group
 *
 * Field groups with a lower menu_order will appear first on the edit screens (change by 10,20,30 increments to give yourself space to add)
 */
$field_group = ( new fewacf\field_group( 'Option Cards', '1605530113a', $location, 60, [
	'position' => 'normal',
]));

/**
 * Define the fields
 */

$field_group->add_field(new acf_fields\true_false('Include Option Cards?', 'option_cards', '1605530113b', [
	'default_value' => ['value' => '1'],
]));

	/*
	* Register the field group
 */
$field_group->register();

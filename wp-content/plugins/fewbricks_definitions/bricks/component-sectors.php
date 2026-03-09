<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_sectors extends project_brick {

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Sectors';

	/**
	 * This is where all the fields for the brick will be set-
	 */
	public function set_fields() {

//		$this->add_field( new acf_fields\text( 'Heading', 'heading', '202002181326a', [
//			'instructions' => 'Keep the heading concise and under 200 characters (including spaces).',
//			'maxlength' => 200
//		] ));

		$this->add_field( (new acf_fields\repeater('Sectors', 'sectors', '202002181326b', [
			'button_label' => 'Add sector',
			'layout' => 'row',
			'max' => 8
		]) )
			->add_sub_field(new acf_fields\taxonomy('Sector', 'taxonomy', '202002171340f',[
				'taxonomy' => 'sectors',
				'field_type' => 'select',
				'return_format' => 'object',
				'multiple' => 0,
			]))
			->add_sub_field(new acf_fields\text('Title', 'title', '202002181326c'))
			->add_sub_field(new acf_fields\wysiwyg('Content', 'content', '202002181326d'))
		);

	}

	/**
	 * Function to show what Twig could do for you
	 * @return array
	 */
	protected function get_brick_html() {

		$data = [
			'heading' => $this->get_field( 'heading' ),
			'sectors' => $this->get_field( 'sectors' ),
		];

		return $data;

	}

}

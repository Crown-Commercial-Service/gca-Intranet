<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_pillars extends project_brick {

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Pillars';

	/**
	 * This is where all the fields for the brick will be set-
	 */
	public function set_fields() {

		$this->add_field( new acf_fields\text( 'Heading', 'heading', '202002171340a', [
			'instructions' => 'Keep the heading concise and under 200 characters (including spaces).',
			'maxlength' => 200
		] ));

		$this->add_field( (new acf_fields\repeater('Pillars', 'pillars', '202002171340b', [
			'button_label' => 'Add pillar',
			'layout' => 'row',
			'max' => 4
		]) )
			->add_sub_field(new acf_fields\taxonomy('Flourish', 'taxonomy', '202002171340f',[
				'taxonomy' => 'pillars',
				'field_type' => 'select',
				'return_format' => 'object',
				'multiple' => 0,
			]))
			->add_sub_field(new acf_fields\image('Icon', 'icon', '202002171340c'))
			->add_sub_field(new acf_fields\text('Title', 'title', '202002171340d'))
			->add_sub_field(new acf_fields\wysiwyg('Content', 'content', '202002171340e'))
		);

	}

	/**
	 * Function to show what Twig could do for you
	 * @return array
	 */
	protected function get_brick_html() {

//		$links = array();
//
//		while ( $this->have_rows('links') ) {
//			$this->the_row();
//
//			array_push( $links ,
//				array(
//					'html' => acf_fields\flexible_content::get_sub_field_brick_instance()->get_html()
//				)
//			);
//		}

		$data = [
			'heading' => $this->get_field( 'heading' ),
			'pillars' => $this->get_field( 'pillars' ),
		];

		return $data;

	}

}

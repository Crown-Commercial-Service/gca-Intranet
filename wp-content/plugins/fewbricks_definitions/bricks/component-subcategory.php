<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_subcategory extends project_brick {

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Subcategory';

	/**
	 * This is where all the fields for the brick will be set-
	 */
	public function set_fields() {

		$this->add_field( new acf_fields\text( 'Heading', 'heading', '202001131516a', [
			'instructions' => 'Keep the heading concise and under 200 characters (including spaces).',
			'maxlength' => 200
		] ));

		$this->add_field( new acf_fields\wysiwyg( 'Content', 'content', '202001131516b' ) );


		$this->add_field( new acf_fields\text('Link URL', 'link_url', '202002061331a', [
		    'instructions' => 'External links: use the whole URL including https://. For example https://www.pensionwise.gov.uk/en. Internal links: use the part of the link after gov.uk. For example If you want to link to www.crowncommercial.gov.uk/agreements/RM1063 you would enter /agreements/RM1063'
            ]
        ) );


        $this->add_field( new acf_fields\text('Link Text', 'link_text', '202002031711a', [
            'instructions' => 'Add text for the link. If you do not enter any text it will show \'Learn more\'.'
            ]
        ) );

	}

	/**
	 * Function to show what Twig could do for you
	 * @return array
	 */
	protected function get_brick_html() {

		$data = [
			'heading' => $this->get_field( 'heading' ),
			'content' => apply_filters( 'the_content', $this->get_field( 'content' ) ),
			'link' => $this->get_field( 'link' ),
		];

		return $data;

	}

}

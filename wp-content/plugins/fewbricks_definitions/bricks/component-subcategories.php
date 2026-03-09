<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_subcategories extends project_brick {

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Related categories';

	/**
	 * This is where all the fields for the brick will be set-
	 */
	public function set_fields() {

	    $this->add_field( new acf_fields\image( 'Optional Image Icon', 'optional_icon', '202002051757a', [
	        'instructions' => 'An optional icon to display above the heading. Ideal size: 600&times;400, ideal aspect ratio 3:2. Please do not upload large images (anything over 1000&times;1000) as this will have a negative impact on performance.',
        ]) );

		$this->add_field( new acf_fields\text( 'Heading', 'heading', '202001131517a', [
			'instructions' => 'Keep the heading concise and under 200 characters (including spaces).',
			'maxlength' => 200
		] ));

		$this->add_field( new acf_fields\wysiwyg( 'Content', 'content', '202001131517b' ) );

		$fc = new acf_fields\flexible_content( 'Links', 'links', '202001131517c', [
			'button_label' => 'Add link',
			'layout'       => 'row',
			'min'          => '',
			'max'          => 9,
		] );

		$l = new layout( '', 'link', '202001131517d' );
		$l->add_brick( new component_subcategory( 'link', '202001131517e' ) );
		$fc->add_layout( $l );

		$this->add_flexible_content( $fc );

	}

	/**
	 * Function to show what Twig could do for you
	 * @return array
	 */
	protected function get_brick_html() {

		$links = array();

		while ( $this->have_rows('links') ) {
			$this->the_row();

			array_push( $links ,
				array(
					'html' => acf_fields\flexible_content::get_sub_field_brick_instance()->get_html()
				)
			);
		}

		$data = [
			'heading' => $this->get_field( 'heading' ),
			'content' => apply_filters( 'the_content', $this->get_field( 'content' ) ),
			'links'   => $links
		];

		return $data;

	}

}

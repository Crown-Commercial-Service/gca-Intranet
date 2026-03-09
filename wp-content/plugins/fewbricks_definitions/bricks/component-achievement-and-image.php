<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class quote_and_image
 * @package fewbricks\bricks
 */
class component_achievement_and_image extends project_brick {

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Achievement and Image';

	/**
	 * This is where all the fields for the brick will be set-
	 */
	public function set_fields() {

//		$this->add_field( new acf_fields\text( 'Heading', 'heading', '202002181326a', [
//			'instructions' => 'Keep the heading concise and under 200 characters (including spaces).',
//			'maxlength' => 200
//		] ));

            
    $this->add_field(new acf_fields\image( 'Image' , 'image' , '202019031724a',[
        'instructions' => 'The height of this image is flexible. Minimum width: 618 px. Recommended size: 1236&times;818.',
        'min_width' => 618,
    ] ));

    $this->add_field(new acf_fields\wysiwyg( 'Text' , 'text' , '202019031724b', [
		'instructions' => 'You will need to keep this section short. You have a limit of 280 characters (or roughly 60 words)',
		'maxlength' => 280,
	] ));
    }


	/**
	 * Function to show what Twig could do for you
	 * @return array
	 */
	protected function get_brick_html() {

		$data = [
            'image'  => $this->get_field( 'image' ),
			'text'  => apply_filters( 'the_content' , $this->get_field( 'text' ) ),
		];

		return $data;

	}

}
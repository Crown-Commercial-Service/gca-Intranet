<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class quote_and_image
 * @package fewbricks\bricks
 */
class component_featured extends project_brick {

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Featured';

	/**
	 * This is where all the fields for the brick will be set-
	 */
	public function set_fields() {

//		$this->add_field( new acf_fields\text( 'Heading', 'heading', '202002181326a', [
//			'instructions' => 'Keep the heading concise and under 200 characters (including spaces).',
//			'maxlength' => 200
//		] ));

    
	$this->add_field(new acf_fields\wysiwyg( 'Section Heading' , 'section_heading' , '202007041328a' ));
	
	$this->add_field(new acf_fields\wysiwyg( 'Heading 1' , 'heading_1' , '202006041042a' ));
	
    $this->add_field(new acf_fields\wysiwyg( 'Body 1' , 'body_1' , '202006041054a' ));
	
	$this->add_field(new acf_fields\wysiwyg( 'Link 1 Title' , 'link_1_title' , '202007041101a' ));
	
	$this->add_field(new acf_fields\wysiwyg( 'Link 1 Destination' , 'link_1_destination' , '202007041140a' ));

	$this->add_field(new acf_fields\wysiwyg( 'Heading 2' , 'heading_2' , '202006041043a' ));
    
	$this->add_field(new acf_fields\wysiwyg( 'Body 2' , 'body_2' , '202006041054b' ));
	
    $this->add_field(new acf_fields\wysiwyg( 'Link 2 Title' , 'link_2_title' , '202007041101b' ));
    
    $this->add_field(new acf_fields\wysiwyg( 'Link 2 Destination' , 'link_2_destination' , '202007041140b' ));
	
	$this->add_field(new acf_fields\wysiwyg( 'Heading 3' , 'heading_3' , '202006041043b' ));
	
	$this->add_field(new acf_fields\wysiwyg( 'Body 3' , 'body_3' , '202006041054c' ));
	
	$this->add_field(new acf_fields\wysiwyg( 'Link 3 Title' , 'link_3_title' , '202007041101c' ));
	
    $this->add_field(new acf_fields\wysiwyg( 'Link 3 Destination' , 'link_3_destination' , '202007041140c' ));
    }


	/**
	 * Function to show what Twig could do for you
	 * @return array
	 */
	protected function get_brick_html() {

		$data = [
			'section_heading'  => apply_filters( 'the_content' , $this->get_field( 'section_heading' ) ),
            'heading_1'  => apply_filters( 'the_content' , $this->get_field( 'heading_1' ) ),
            'heading_2'  => apply_filters( 'the_content' , $this->get_field( 'heading_2' ) ),
            'heading_3'  => apply_filters( 'the_content' , $this->get_field( 'heading_3' ) ),
            'body_1'  => apply_filters( 'the_content' , $this->get_field( 'body_1' ) ),
            'body_2'  => apply_filters( 'the_content' , $this->get_field( 'body_2' ) ),
			'body_3'  => apply_filters( 'the_content' , $this->get_field( 'body_3' ) ),
			'link_1_title'  => apply_filters( 'the_content' , $this->get_field( 'link_1_title' ) ),
            'link_2_title'  => apply_filters( 'the_content' , $this->get_field( 'link_2_title' ) ),
			'link_3_title'  => apply_filters( 'the_content' , $this->get_field( 'link_3_title' ) ),
			'link_1_destination'  => apply_filters( 'the_content' , $this->get_field( 'link_1_destination' ) ),
            'link_2_destination'  => apply_filters( 'the_content' , $this->get_field( 'link_2_destination' ) ),
			'link_3_destination'  => apply_filters( 'the_content' , $this->get_field( 'link_3_destination' ) )
		];

		return $data;

	}

}
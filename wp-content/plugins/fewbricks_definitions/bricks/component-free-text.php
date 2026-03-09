<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_free_text
 * @package fewbricks\bricks
 */
class component_free_text extends project_brick
{

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Free Text';

	/**
	 * Set all the fields for the brick.
	 */
	public function set_fields()
	{
		$this->add_field(new acf_fields\wysiwyg( 'Left Heading ' , 'left_heading' , '202017031528bb' ));
        $this->add_field(new acf_fields\wysiwyg( 'Left Content' , 'left_content_new' , '202017031046b' ));
		$this->add_field( new acf_fields\text( 'Button label', 'cta_label', '202030031101a', [
			'instructions' => 'Keep the text concise and under 140 characters (including spaces) so that it can be displayed for search engine results as the meta description.',
		] ));
		$this->add_field( new acf_fields\text( 'Button destination', 'cta_destination', '202030031101b', [
			'instructions' => 'Leave the default value, `#js-contact-form` to take the user to the form section on the page.',
			'default_value' => '#js-contact-form'
		] ));
		$this->add_field(new acf_fields\wysiwyg( 'Right Heading ' , 'right_heading' , '202017031528a' ));
		$this->add_field(new acf_fields\wysiwyg( 'Right Content' , 'right_content_new' , '202017031046a' )); 
	}

	/**
	 * This function will be used in the frontend when displaying the brick.
	 * It will be called by the parents class function get_html(). See that function
	 * for info on what data you have at your disposal.
	 * @return array
	 */
	protected function get_brick_html()
	{
		// Use apply-filter on WYSIWYG fields
		$data = [
			'text'  => apply_filters( 'the_content' , $this->get_field( 'text' ) ),
			'cta_label'  => $this->get_field( 'cta_label' ),
			'cta_destination'  => $this->get_field( 'cta_destination' )
		];

		return $data;
	}

}
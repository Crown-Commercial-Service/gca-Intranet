<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_text extends project_brick
{

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Text';

	/**
	 * Set all the fields for the brick.
	 */
	public function set_fields()
	{
		$this->add_field(new acf_fields\text( 'Content' , 'text' , '202001031024a', [
			'instructions' => 'Keep the summary text concise and under 350 characters (including spaces).',
			'maxlength' => 350
		] )); 
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
			'text'  => apply_filters( 'the_content' , $this->get_field( 'text' ) )
		];

		return $data;
	}

}

<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_image extends project_brick
{

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Image';

	/**
	 * Set all the fields for the brick.
	 */
	public function set_fields()
	{
		$this->add_field(new acf_fields\image( 'Image' , 'image' , '202001031509a',[
			'instructions' => 'The height of this image is flexible. Minimum width: 618 px. Recommended size: 1236&times;818.',
			'min_width' => 618,
		] ));
		$this->add_field(new acf_fields\wysiwyg( 'Caption' , 'caption' , '202001091137a' ));
	}

	/**
	 * This function will be used in the frontend when displaying the brick.
	 * It will be called by the parents class function get_html(). See that function
	 * for info on what data you have at your disposal.
	 * @return array
	 */
	protected function get_brick_html()
	{

		$data = [
			'image'  => $this->get_field( 'image' )
		];

		return $data;
	}

}

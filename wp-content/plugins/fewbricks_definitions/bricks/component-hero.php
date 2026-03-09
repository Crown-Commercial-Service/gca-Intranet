<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_hero extends project_brick {

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Hero';

	/**
	 * This is where all the fields for the brick will be set-
	 */
	public function set_fields() {

		$this->add_field( new acf_fields\image( 'Hero Image', 'image', '202001031023a', [
			'instructions' => 'Leave blank if no hero required. Recommended size: 1122&times;517.',
			'min_width' => 1042,
			'min_height' => 480,
		] ) );

		// REVIEW MICROCOPY
		// Minimum size required is 1042 pixels wide and 480 pixels tall, however the recommended dimensions are 2084 pixels wide and 960 pixels tall (uploading at these larger dimensions will ensure that retina displays receive a high resolution image). Please note that these hero images are vertically cropped on larger screen sizes - so please try and ensure the focal point of the image is vertically in the middle (as the image is cropped vertically on the center)

		$this->add_field( new acf_fields\text( 'Heading', 'heading', '202001031023b', [
			'instructions' => 'Keep headings under 65 characters (including spaces) so that they can be displayed for search engine results.',
		] ));
		$this->add_field( new acf_fields\text( 'Button label', 'cta_label', '202001031023d', [
			'instructions' => 'Keep the text concise and under 140 characters (including spaces) so that it can be displayed for search engine results as the meta description.',
		] ));
		$this->add_field( new acf_fields\text( 'Button destination', 'cta_destination', '20200108105a', [
			'instructions' => 'Leave the default value, `#js-contact-form` to take the user to the form section on the page.',
			'default_value' => '#js-contact-form'
		] ));

	}

	/**
	 * Function to show what Twig could do for you
	 * @return array
	 */
	protected function get_brick_html() {

		$data = [
			'heading'  => $this->get_field( 'heading' ),
			'image'  => $this->get_field( 'image' ),
			'cta_label'  => $this->get_field( 'cta_label' ),
			'cta_destination'  => $this->get_field( 'cta_destination' )
		];

		return $data;

	}

}

<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_intro extends project_brick {

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Intro and description';

	/**
	 * This is where all the fields for the brick will be set-
	 */
	public function set_fields() {

		$this->add_field( new acf_fields\text( 'Heading', 'heading', '202001031433a', [
			'instructions' => 'Keep the heading concise and under 200 characters (including spaces).',
			'maxlength' => 200
		] ));

		$this->add_field( new acf_fields\wysiwyg( 'Content', 'content', '202001031433b' ) );


		$this->add_field( new acf_fields\text( 'Button label', 'cta_label', '202001031433d', [
			'instructions' => 'Keep the text concise and under 140 characters (including spaces) so that it can be displayed for search engine results as the meta description.',
			'maxlength' => 140
		] ));
		$this->add_field( new acf_fields\text( 'Button destination', 'cta_destination', '202001091136a', [
			'instructions' => 'Leave the default value, `#js-contact-form` to take the user to the form section on the page.',
			'default_value' => '#js-contact-form'
		] ));


		$this->add_field( new acf_fields\text( 'Secondary label', 'secondary_label', '202001091335a', [
			'instructions' => 'Keep the CTA concise and under 140 characters (including spaces) so that it can be displayed for search engine results as the meta description.',
			'maxlength' => 140
		] ));
		$this->add_field( new acf_fields\text( 'Secondary link', 'secondary_destination', '202001091136b', [
			'instructions' => 'Link to a page on the same domain by writing e.g. /agreements',
		] ));

		$fc = new acf_fields\flexible_content( 'Media', 'media', '2020011440a', [
			'button_label' => 'Add media to section',
			'layout'       => 'row',
			'min'          => '',
			'max'          => 1,
		] );

		$l = new layout( '', 'image', '2020011440b' );
		$l->add_brick( new component_image( 'image', '2020011440d' ) );
		$fc->add_layout( $l );

		$l = new layout( '', 'video', '2020011440c' );
		$l->add_brick( new component_video( 'video', '2020011440e' ) );
		$fc->add_layout( $l );

		$this->add_flexible_content( $fc );

		$this->add_field(new acf_fields\radio('Media and text', 'flip_media_text', '2021033004562b', [
			'instructions' => 'Select how the image and text will appear on the site',
			'choices' 	   => [
				'image_right' => 'Image right, text left (default)',
				'image_left' => 'Image left, text right'
			]
		]));

	}

	/**
	 * Function to show what Twig could do for you
	 * @return array
	 */
	protected function get_brick_html() {

		$media = array();

		 while ( $this->have_rows('media') ) {
			 $this->the_row();

			 array_push( $media ,
				 array(
					 'html' => acf_fields\flexible_content::get_sub_field_brick_instance()->get_html()
				 )
			 );
		 }

//		while ( $this->have_rows( 'media' ) ) {
//			$this->the_row();
//			$media = $this->get_child_brick_in_repeater( 'media', 'block', 'component_block' )->get_html();
//			array_push( $media, $data );
//		}

		$data = [
			'heading' => $this->get_field( 'heading' ),
			'content' => apply_filters( 'the_content', $this->get_field( 'content' ) ),
			'media'   => $media
		];

		return $data;

	}

}

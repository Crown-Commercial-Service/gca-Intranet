<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class group_post_content_default
 * @package fewbricks\bricks
 */
class group_post_content_default extends project_brick
{

	/**
	 * @var string
	 */
	protected $label = 'Post components';

	/**
	 *
	 */
	public function set_fields()
	{

		$fc = new acf_fields\flexible_content('Components', 'rows', '202501031018a', [
			'button_label'  =>  'Add component',
			'layout' => 'row'
		]);

        $l = new layout('', 'feature_news', '202002101434a');
        $l->add_brick(new component_feature_news('feature_news', '202502101434b'));
        $fc->add_layout($l);

		$this->add_flexible_content($fc);

	}

	/**
	 * @return array
	 */
	protected function get_brick_html()
	{
		$data = array( 'components' => array() );

		while ( $this->have_rows('rows') ) {
			$this->the_row();

			array_push( $data['components'] ,
				array(
					'html' => acf_fields\flexible_content::get_sub_field_brick_instance()->get_html()
				)
			);
		}

		return $data;

	}

}

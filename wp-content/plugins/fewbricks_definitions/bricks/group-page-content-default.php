<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class group_page_content_default
 * @package fewbricks\bricks
 */
class group_page_content_default extends project_brick
{

	/**
	 * @var string
	 */
	protected $label = 'Page components';

	/**
	 *
	 */
	public function set_fields()
	{

		$fc = new acf_fields\flexible_content('Components', 'rows', '202001031018a', [
			'button_label'  =>  'Add component',
			'layout' => 'row'
		]);

		$l = new layout('', 'text', '202001031019a');
		$l->add_brick(new component_text('text', '202001031019b'));
		$fc->add_layout($l);

		$l = new layout('', 'intro', '202001031449a');
		$l->add_brick(new component_intro('intro', '202001031449b'));
		$fc->add_layout($l);

        $l = new layout('', 'subcategories', '202001131522a');
        $l->add_brick(new component_subcategories('subcategories', '202001131522b'));
        $fc->add_layout($l);

        $l = new layout('', 'accordion', '202001211617a');
        $l->add_brick(new component_accordion('accordion', '202001211617b'));
        $fc->add_layout($l);

        $l = new layout('', 'feature_news', '202002101434a');
        $l->add_brick(new component_feature_news('feature_news', '202002101434b'));
        $fc->add_layout($l);

        $l = new layout('', 'feature_events', '202002131748a');
        $l->add_brick(new component_feature_events('feature_events', '202002131749a'));
        $fc->add_layout($l);

		$l = new layout('', 'pillars', '202002171346a');
		$l->add_brick(new component_pillars('pillars', '202002171346b'));
		$fc->add_layout($l);

		$l = new layout('', 'cards', '202002181327a');
		$l->add_brick(new component_card_list('cards', '202002181327b'));
		$fc->add_layout($l);

		$l = new layout('', 'sectors', '202002181437a');
		$l->add_brick(new component_sectors('sectors', '202002181437b'));
		$fc->add_layout($l);
			
		$l = new layout('', 'free_text', '202017031053a');
		$l->add_brick(new component_free_text('free_text', '202017031053a'));
		$fc->add_layout($l);
			
		$l = new layout('', 'quote_and_image', '202010031547a');
		$l->add_brick(new component_quote_and_image('quote_and_image', '202010031548b'));
		$fc->add_layout($l);

		$l = new layout('', 'achievement_and_image', '202019031723a');
		$l->add_brick(new component_achievement_and_image('achievement_and_image', '202019031723b'));
		$fc->add_layout($l);

		$l = new layout('', 'featured', '202006041022a');
		$l->add_brick(new component_featured('featured', '202006041022b'));
		$fc->add_layout($l);

		$l = new layout('', 'tabs', '202401161327a');
		$l->add_brick(new component_tabs('tabs', '202401161327b'));
		$fc->add_layout($l);

		$l = new layout('', 'apollos', '202402211347a');
		$l->add_brick(new component_apollos('apollos', '202402211347b'));
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

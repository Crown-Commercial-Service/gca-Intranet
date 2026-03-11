<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_subpage_cards
 * Displays child pages of a selected (or current) page as a card grid.
 * @package fewbricks\bricks
 */
class component_subpage_cards extends project_brick {

	/**
	 * @var string Label shown in the "Add component" editor menu.
	 */
	protected $label = 'Subpage Cards';

	public function set_fields() {

		$this->add_field( new acf_fields\text( 'Heading', 'heading', '202503100010a', [
			'instructions' => 'Optional heading displayed above the subpage cards. Keep under 200 characters.',
			'maxlength'    => 200,
		] ) );

		$this->add_field( new acf_fields\post_object( 'Parent Page', 'parent_page', '202503100010b', [
			'instructions'  => 'Select the page whose child pages will be displayed as cards. Leave empty to automatically use the current page\'s children.',
			'post_type'     => [ 'page' ],
			'allow_null'    => 1,
			'multiple'      => 0,
			'return_format' => 'id',
			'ui'            => 1,
		] ) );

	}

	/**
	 * @return array
	 */
	protected function get_brick_html() {

		$parent_id = $this->get_field( 'parent_page' ) ?: get_the_ID();

		$subpages = get_pages( [
			'parent'      => (int) $parent_id,
			'sort_column' => 'menu_order',
			'sort_order'  => 'ASC',
			'post_status' => 'publish',
		] );

		return [
			'heading'  => $this->get_field( 'heading' ),
			'subpages' => $subpages,
		];

	}

}

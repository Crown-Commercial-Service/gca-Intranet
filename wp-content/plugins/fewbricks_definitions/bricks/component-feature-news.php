<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_feature_news
 * @package fewbricks\bricks
 */
class component_feature_news extends project_brick
{

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Feature News';

    /**
     * Set all the fields for the brick.
     */
    public function set_fields()
    {
        $this->add_field(new acf_fields\text('Heading', 'heading', '202002111558a', [
            'instructions' => 'Always us the title Related articles.',
            'default_value' => 'Related articles'
        ]));

        $this->add_field(new acf_fields\taxonomy('News Type', 'news_type', '202002101444a', [
            'taxonomy' => 'category',
            'multiple' => 1,
            'field_type' => 'checkbox',
            'instructions' => 'Select a type of news to filter the results by. Defaults to all types of news (so if you select nothing, all types of news will be returned).'
        ]));

        $this->add_field(new acf_fields\text('View More Text', 'view_more_text', '202305031558a', [
            'instructions' => 'Leave it blank to use defult text: Browse all XXX news articles'
        ]));

        $this->add_field(new acf_fields\taxonomy('Products and Services', 'products_and_services', '202002101433a', [
            'taxonomy' => 'products_services',
            'multiple' => 1,
            'field_type' => 'multi_select',
            'instructions' => 'Select one or more products and services to filter the results by.'
        ]));

        $this->add_field(new acf_fields\taxonomy('Sectors', 'sectors', '202002101441a', [
            'taxonomy' => 'sectors',
            'multiple' => 1,
            'field_type' => 'multi_select',
            'instructions' => 'Select one or more sectors to filter the results by.'
        ]));

        // add_action('init', 'removing_post_tag_from_taxonomy_list');

        $this->add_field(new acf_fields\relationship( 'Select specific articles' , 'cherry_picked_articles' , '202002101443a', [
            'post_type' => 'post',
            'max' => 3,
            'filters' => array(
                0 => 'search',
                2 => 'taxonomy',
            ),
            'instructions' => 'Maximum of 3 articles. You can select specific articles to show on the page. Type the name of the article in the search box or select the pillar, category, sector or type of news to filter the list to choose from.'
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
        //$newsType            = $this->get_field( 'news_type' );
        //$productsAndServices = $this->get_field( 'products_and_services' );
        //$sectors             = $this->get_field( 'sectors' );
        //$cherryPicked        = $this->get_field('cherry_picked_articles');

        // Use apply-filter on WYSIWYG fields
        $data = [
        ];

        return $data;
    }

}

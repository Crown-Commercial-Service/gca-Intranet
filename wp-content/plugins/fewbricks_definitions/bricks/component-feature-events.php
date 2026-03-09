<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_feature_events
 * @package fewbricks\bricks
 */
class component_feature_events extends project_brick
{

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Feature Events';

    /**
     * Set all the fields for the brick.
     */
    public function set_fields()
    {
        $this->add_field(new acf_fields\text('Component Heading', 'heading', '202002111558a', [
            'instructions' => 'Always use \'Upcoming events\'',
            'default_value' => 'Upcoming events'
        ]));

        $this->add_field(new acf_fields\taxonomy('Event Categories', 'event_categories', '202002101444a', [
            'taxonomy' => 'products_services',
            'multiple' => 1,
            'field_type' => 'multi_select',
            'instructions' => 'Select an event category to filter the results by.'
        ]));

        $this->add_field(new acf_fields\taxonomy('Sectors', 'sectors', '202002101441a', [
            'taxonomy' => 'sectors',
            'multiple' => 1,
            'field_type' => 'multi_select',
            'instructions' => 'Select one or more sectors to filter the results by.'
        ]));
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

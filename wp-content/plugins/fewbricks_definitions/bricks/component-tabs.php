<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_tabs extends project_brick
{

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Tabs';

    /**
     * This is where all the fields for the brick will be set-
     */
    public function set_fields()
    {

        $this->add_field(new acf_fields\text('Heading', 'heading', '202401161326a', [
            'instructions' => 'Keep the heading concise and under 100 characters (including spaces).',
            'maxlength' => 100
        ]));

        $this->add_field((new acf_fields\repeater('Tabs', 'tabs', '202401161326b', [
                'button_label' => 'Add tab',
                'layout' => 'row',
                'max' => 4
            ]))
                ->add_sub_field(new acf_fields\text('Title', 'title', '202401161326c', [
                    'instructions' => 'Keep the title concise and under 35 characters (including spaces).',
                    'maxlength' => 35,
                    'required' => 1,
                ]))
                ->add_sub_field(new acf_fields\wysiwyg('Content', 'content', '202401161326d'))
        );
    }

    /**
     * Function to show what Twig could do for you
     * @return array
     */
    protected function get_brick_html()
    {

        $data = [
            'heading' => $this->get_field('heading'),
            'tabs' => $this->get_field('tabs'),
        ];

        return $data;
    }
}

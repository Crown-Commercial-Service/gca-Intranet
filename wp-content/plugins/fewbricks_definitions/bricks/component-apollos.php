<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_apollos extends project_brick
{

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Report Cards';

    /**
     * This is where all the fields for the brick will be set-
     */
    public function set_fields()
    {

        $this->add_field((new acf_fields\repeater('Report Cards', 'apollos', '202402211354e', [
                'button_label' => 'Add Report Card',
                'layout' => 'row',
                'max' => 6
            ]))

             ->add_sub_field(new acf_fields\text('Heading', 'heading', '202402211326a', [
            'instructions' => 'Keep the heading concise and under 50 characters (including spaces).',
            'required' => 1,
            'maxlength' => 50
             ]))
       
            ->add_sub_field(new acf_fields\text('Requirement', 'requirement', '202402211321b', [
                'instructions' => 'What was the requirement? Keep this less than 200 characters (including spaces)',
                'required' => 1,
                'maxlength' => 200
            ]))

            ->add_sub_field(new acf_fields\text('Solution', 'solution', '202402211322c', [
                'instructions' => 'What was the proposed solution? Keep this less than 200 characters (including spaces)',
                'required' => 1,
                'maxlength' => 200
            ]))

            ->add_sub_field((new acf_fields\repeater('Results', 'results', '202402211324n', [
                'button_label' => 'Add result',
                'instructions' => 'What were the outcomes? These will be displayed as bullet points.',
                'layout' => 'row',
            ]))
                ->add_sub_field(new acf_fields\text('Result', 'result', '202402211325f')))

            ->add_sub_field(new acf_fields\text('Visit Site text', 'visit_site_text', '202402211328i', [
            'instructions' => 'text of visit site link, note default is Visit Site',
            'default' => 'Visit Site'
            ]))

            ->add_sub_field(new acf_fields\text('Visit Site URL', 'visit_site_url', '202402211329j', [
            'instructions' => 'url of visit site link, if external link post full URL with https protocol',
            'default' => '/'
            ]))

            ->add_sub_field(new acf_fields\text('Learn more text', 'learn_more_text', '202402211330k', [
            'instructions' => 'text of learn more link, note default is Learn more',
            'default' => 'Learn more'
            ]))

            ->add_sub_field(new acf_fields\text('Learn more URL', 'learn_more_url', '202402211331l', [
            'instructions' => 'url of learn more link, if external link post full URL with https protocol',
            ]))
        );
      
    }

    /**
     * Function to show what Twig could do for you
     * @return array
     */
    protected function get_brick_html()
    {

        $data = [
            'apollos' => $this->get_field('apollos'),
        ];

        return $data;
    }
}

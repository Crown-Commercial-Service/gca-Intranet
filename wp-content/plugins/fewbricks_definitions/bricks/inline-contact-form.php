<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class inline_contact_form
 * @package fewbricks\bricks
 */
class inline_contact_form extends project_brick
{

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Inline Contact Form';

    /**
     * Set all the fields for the brick.
     */
    public function set_fields()
    {
        $this->add_field(new acf_fields\true_false('Show contact form?', 'show_contact_form', '202002031250a'));

        $this->add_field(new acf_fields\true_false('Show newsletter form?', 'show_newsletter_form', '202005061114a', [
            'instructions' => 'Select if specific newsletter form is to be displayed',
        ]));

        $this->add_field(new acf_fields\true_false('Hide callback option on form?', 'hide_callback_form', '202527031250a'));

        $this->add_field(new acf_fields\text('Form heading', 'form_heading', '202002031251a', [
            'conditional_logic' => [
                [
                    [
                        'field' => '202002031250a',
                        'operator' => '==',
                        'value' => '1'
                    ]
                ]
            ],
        ]));

        $this->add_field(new acf_fields\text('Form sub-heading', 'form_sub_heading', '202002031251b', [
            'conditional_logic' => [
                [
                    [
                        'field' => '202002031250a',
                        'operator' => '==',
                        'value' => '1'
                    ]
                ]
            ],
        ]));

        $this->add_field(new acf_fields\text('Message question title', 'message_question_title', '202002060930a', [
            'conditional_logic' => [
                [
                    [
                        'field' => '202002031250a',
                        'operator' => '==',
                        'value' => '1'
                    ]
                ]
            ],
        ]));

        $this->add_field(new acf_fields\text('Message question description', 'message_question_description', '202002060930b', [
            'conditional_logic' => [
                [
                    [
                        'field' => '202002031250a',
                        'operator' => '==',
                        'value' => '1'
                    ]
                ]
            ],
        ]));

        $this->add_field(new acf_fields\text('Form campaign code', 'form_campaign_code', '202002031251c', [
            'conditional_logic' => [
                [
                    [
                        'field' => '202002031250a',
                        'operator' => '==',
                        'value' => '1'
                    ]
                ]
            ],
        ]));

        $this->add_field(new acf_fields\true_false('Show "Find out more about aggregation" checkbox', 'find_out_more_aggregation', '202002271040a', [
            'instructions' => 'Should not be used in conjunction with the "Aggregation Options" list.',
        ]));

        $this->add_field(new acf_fields\true_false('Show "What areas of aggregation are you interested in"', 'show_what_areas_aggregation', '202002060930d', [
        	'instructions' => 'By enabling aggregation options on the form, the campaign form code will no longer be submitted to Salesforce.',
            'conditional_logic' => [
                [
                    [
                        'field' => '202002031250a',
                        'operator' => '==',
                        'value' => '1'
                    ]
                ]
            ],
        ]));

        $this->add_field( (new acf_fields\repeater('Aggregation Options', 'aggregation_options', '202002121515a', [
            'button_label' => 'Add Aggregation Option',
            'layout' => 'table',
            'conditional_logic' => array(
                array (
                    array (
                        'field' => '202002060930d',
                        'operator' => '!=',
                        'value' => ''
                    )
                )
            )
        ]))
            ->add_sub_field(new acf_fields\text('Name', 'name', '202002121517a'))
            ->add_sub_field(new acf_fields\text('Campign Code', 'campaign_code', '202002121517b'))
        );



    }

    /**
     * This function will be used in the frontend when displaying the brick.
     * It will be called by the parents class function get_html(). See that function
     * for info on what data you have at your disposal.
     * @return array
     */
    protected function get_brick_html()
    {
        // Use apply-filter on WYSIWYG fields
        $data = [
        ];

        return $data;
    }

}

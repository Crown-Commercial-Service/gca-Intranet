<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_accordion
 * @package fewbricks\bricks
 */
class component_accordion extends project_brick {

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Accordion';

    /**
     * This is where all the fields for the brick will be set-
     */
    public function set_fields() {

        $this->add_field( new acf_fields\text( 'Heading', 'heading', '202001211615a', [
            'instructions' => 'A heading to display above all of the accordion items. Ideally keep this heading under 65 characters (including spaces).',
            'maxlength' => 65
        ] ));

        $this->add_field(new acf_fields\true_false('Open all by default', 'open_all_by_default', '202306081530a',[
            'instructions' => 'Check if you want the accordion to be open when user landed on the page',
            'default_value' => '0',
        ]));

        $this->add_field( new acf_fields\wysiwyg( 'Introduction', 'introduction', '202001211615b', [
            'instructions' => 'Brief introductory text to dislay above '
        ] ));

        $this->add_field( (new acf_fields\repeater('Accordion Items', 'items', '202001211607a', ['button_label' => 'Add Accordion Item']) )
            ->add_sub_field(new acf_fields\text('Title', 'title', '202001211615c'))
            ->add_sub_field(new acf_fields\wysiwyg('Content', 'content', '202001211616a'))
        );

    }

}

<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_quicklinks
 * Editable Quick Links list for the homepage.
 * @package fewbricks\bricks
 */
class component_quicklinks extends project_brick
{

    protected $label = 'Quick Links';

    public function set_fields()
    {
        $this->add_field( new acf_fields\text( 'Section heading', 'title', '202604100014a', [
            'instructions'  => 'Heading shown above the quick links list.',
            'default_value' => 'Quick links',
            'maxlength'     => 80,
        ] ) );

        $this->add_field( new acf_fields\textarea( 'Section description', 'description', '202604100015a', [
            'instructions' => 'Short description shown below the heading (max 40 characters).',
            'maxlength'    => 40,
            'rows'         => 2,
            'new_lines'    => '',
        ] ) );

        $this->add_field( ( new acf_fields\repeater( 'Links', 'links', '202604100016a', [
            'instructions' => 'Add one or more quick links. Each link needs a label and a URL.',
            'button_label' => 'Add link',
            'layout'       => 'table',
        ] ) )
            ->add_sub_field( new acf_fields\text( 'Link label', 'text', '202604100017a', [
                'maxlength' => 48,
            ] ) )
            ->add_sub_field( new acf_fields\url( 'Link URL', 'url', '202604100018a' ) )
        );
    }

}

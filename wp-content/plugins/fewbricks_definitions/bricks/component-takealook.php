<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_takealook
 * Editable "Take a look" promotional card for the homepage.
 * @package fewbricks\bricks
 */
class component_takealook extends project_brick
{

    protected $label = 'Take a Look';

    public function set_fields()
    {
        $this->add_field( new acf_fields\text( 'Section heading', 'title', '202604100010a', [
            'instructions'  => 'Heading shown above the card.',
            'default_value' => 'Take a look',
            'maxlength'     => 80,
        ] ) );

        $this->add_field( new acf_fields\textarea( 'Section description', 'description', '202604100011a', [
            'instructions' => 'Short description shown below the heading (max 40 characters).',
            'maxlength'    => 40,
            'rows'         => 2,
            'new_lines'    => '',
        ] ) );

        $this->add_field( new acf_fields\text( 'Card link text', 'link_text', '202604100012a', [
            'instructions'  => 'Text displayed inside the card link (max 90 characters).',
            'default_value' => 'Learn more',
            'maxlength'     => 90,
        ] ) );

        $this->add_field( new acf_fields\url( 'Card link URL', 'link_url', '202604100013a', [
            'instructions' => 'Where the card link points to. Leave empty to show the card without a link.',
        ] ) );
    }

}

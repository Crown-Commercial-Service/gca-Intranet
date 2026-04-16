<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_homehero
 * Editable homepage hero banner: kicker text, heading, and hero image.
 * @package fewbricks\bricks
 */
class component_homehero extends project_brick
{

    protected $label = 'Homepage Hero';

    public function set_fields()
    {
        $this->add_field( new acf_fields\text( 'Kicker text', 'kicker', '202604100001a', [
            'instructions'  => 'Small text displayed above the main heading (e.g. "Welcome to our").',
            'default_value' => 'Welcome to our',
            'maxlength'     => 80,
        ] ) );

        $this->add_field( new acf_fields\text( 'Heading', 'title', '202604100002a', [
            'instructions'  => 'Main H1 heading displayed in the hero banner.',
            'default_value' => 'GCA Intranet',
            'maxlength'     => 100,
        ] ) );

        $this->add_field( new acf_fields\image( 'Hero image', 'image', '202604100003a', [
            'instructions'  => 'Decorative image shown in the right-hand side of the hero.',
            'return_format' => 'array',
            'preview_size'  => 'medium',
        ] ) );
    }

}

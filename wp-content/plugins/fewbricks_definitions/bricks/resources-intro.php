<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class resources_intro
 * @package fewbricks\bricks
 */
class resources_intro extends project_brick {

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Resources Intro';

    /**
     * This is where all the fields for the brick will be set-
     */
    public function set_fields() {

        $this->add_field( new acf_fields\text( 'Heading', 'heading', '202001101221a', [
            'instructions' => 'Keep headings under 65 characters (including spaces)',
            'maxlength' => 65
        ] ));
        $this->add_field( new acf_fields\wysiwyg( 'Introduction', 'introduction', '202001101222a' ));

    }

}

<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class downloadable_list
 * @package fewbricks\bricks
 */
class downloadable_list extends project_brick {

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Downloadable Resources List';

    /**
     * This is where all the fields for the brick will be set-
     */
    public function set_fields() {

        $this->add_field((new acf_fields\relationship('Downloadable Resources', 'downloadable_resource', '202203299533a', [
            'post_type' => 'downloadable'
        ])));

    }

}
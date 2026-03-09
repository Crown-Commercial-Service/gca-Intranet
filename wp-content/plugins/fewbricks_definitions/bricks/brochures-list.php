<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class brochures_list
 * @package fewbricks\bricks
 */
class brochures_list extends project_brick {

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Brochures List';

    /**
     * This is where all the fields for the brick will be set-
     */
    public function set_fields() {

        $this->add_field( (new acf_fields\repeater('Brochures', 'brochures_list', '202001091647a', ['button_label' => 'Add Brochure']))
            ->add_sub_field(new acf_fields\file('Brochure', 'brochure', '202001091649a', [
                'required' => 1
            ]))
        );

    }

}

<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_workupdates
 * Editable Work Updates homepage section.
 * @package fewbricks\bricks
 */
class component_workupdates extends project_brick
{

    protected $label = 'Work Updates';

    public function set_fields()
    {
        $this->add_field( new acf_fields\text( 'Section heading', 'title', '202604100019a', [
            'instructions'  => 'Heading shown above the work update cards.',
            'default_value' => 'Work updates',
            'maxlength'     => 50,
        ] ) );

        $this->add_field( new acf_fields\textarea( 'Section description', 'description', '202604100020a', [
            'instructions' => 'Short description shown below the heading (max 40 characters).',
            'maxlength'    => 40,
            'rows'         => 2,
            'new_lines'    => '',
        ] ) );

        $this->add_field( new acf_fields\number( 'Post count', 'count', '202604100021a', [
            'instructions'  => 'Number of work update posts to display.',
            'default_value' => 2,
            'min'           => 1,
            'max'           => 6,
        ] ) );

        $this->add_field( new acf_fields\text( '"See more" link text', 'see_more_text', '202604100022a', [
            'instructions'  => 'Label for the "more" link at the bottom of the section.',
            'default_value' => 'More work updates',
            'maxlength'     => 80,
        ] ) );

        $this->add_field( new acf_fields\url( '"See more" link URL', 'see_more_url', '202604100023a', [
            'instructions'  => 'URL the "more" link points to.',
            'default_value' => '/work_update/',
        ] ) );
    }

}

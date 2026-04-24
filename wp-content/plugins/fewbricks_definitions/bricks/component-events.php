<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_events
 * Editable Events homepage section.
 * @package fewbricks\bricks
 */
class component_events extends project_brick
{

    protected $label = 'Events';

    public function set_fields()
    {
        $this->add_field( new acf_fields\text( 'Section heading', 'title', '202604100029a', [
            'instructions'  => 'Heading shown above the event cards.',
            'default_value' => 'Events',
            'maxlength'     => 50,
        ] ) );

        $this->add_field( new acf_fields\textarea( 'Section description', 'description', '202604240010a', [
            'instructions' => 'Short description shown below the heading. Leave empty to hide it and remove the underline.',
            'maxlength'    => 120,
            'rows'         => 2,
            'new_lines'    => '',
        ] ) );

        $this->add_field( new acf_fields\number( 'Event count', 'count', '202604100030a', [
            'instructions'  => 'Number of upcoming events to display automatically. Ignored when specific events are chosen below.',
            'default_value' => 3,
            'min'           => 1,
            'max'           => 9,
        ] ) );

        $this->add_field( new acf_fields\relationship( 'Featured events', 'pinned_events', '202604240011a', [
            'instructions'  => 'Optionally pick specific events to display. When set, overrides the event count above and shows exactly these events.',
            'post_type'     => [ 'event' ],
            'filters'       => [ 'search' ],
            'max'           => 9,
            'return_format' => 'id',
        ] ) );

        $this->add_field( new acf_fields\text( '"See more" link text', 'see_more_text', '202604100031a', [
            'instructions'  => 'Label for the "more events" link at the bottom of the section.',
            'default_value' => 'More events',
            'maxlength'     => 80,
        ] ) );

        $this->add_field( new acf_fields\url( '"See more" link URL', 'see_more_url', '202604100032a', [
            'instructions'  => 'URL the "more" link points to.',
            'default_value' => '/event/',
        ] ) );
    }

}

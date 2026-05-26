<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class component_latestnews
 * Editable Latest News homepage section.
 * @package fewbricks\bricks
 */
class component_latestnews extends project_brick
{

    protected $label = 'Latest News';

    public function set_fields()
    {
        $this->add_field( new acf_fields\text( 'Section heading', 'title', '202604100004a', [
            'instructions'  => 'Heading shown above the news cards.',
            'default_value' => 'Latest news',
            'maxlength'     => 50,
        ] ) );

        $this->add_field( new acf_fields\textarea( 'Section description', 'description', '202604100005a', [
            'instructions' => 'Short description shown below the heading (max 40 characters).',
            'maxlength'    => 40,
            'rows'         => 2,
            'new_lines'    => '',
        ] ) );

        $this->add_field( new acf_fields\number( 'Featured post count', 'featured_count', '202604100006a', [
            'instructions'  => 'Number of large featured news items to show.',
            'default_value' => 1,
            'min'           => 1,
            'max'           => 3,
        ] ) );

        $this->add_field( new acf_fields\number( 'Secondary post count', 'secondary_count', '202604100007a', [
            'instructions'  => 'Number of smaller secondary news items to show.',
            'default_value' => 3,
            'min'           => 1,
            'max'           => 6,
        ] ) );

        $this->add_field( new acf_fields\relationship( 'Selected news articles', 'selected_posts', '202604220001a', [
            'post_type'    => [ 'news' ],
            'max'          => 9,
            'filters'      => [
                0 => 'search',
            ],
            'instructions' => 'Optional. Choose specific news articles in display order. If you leave this empty, the latest published news will be used.',
        ] ) );

        $this->add_field( new acf_fields\text( '"See more" link text', 'see_more_text', '202604100008a', [
            'instructions'  => 'Label for the "browse all" link at the bottom of the section.',
            'default_value' => 'Browse all news articles',
            'maxlength'     => 80,
        ] ) );

        $this->add_field( new acf_fields\url( '"See more" link URL', 'see_more_url', '202604100009a', [
            'instructions'  => 'URL the "browse all" link points to.',
            'default_value' => '/news/',
        ] ) );
    }

}

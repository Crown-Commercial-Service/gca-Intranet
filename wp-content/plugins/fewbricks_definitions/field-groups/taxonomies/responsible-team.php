<?php

use fewbricks\acf as fewacf;
use fewbricks\acf\fields as acf_fields;

/**
 * ACF field group for the responsible_team taxonomy.
 * Editors configure the team contact card on the term edit screen.
 * Any page tagged with the term automatically gets the card in the footer.
 */

if (!function_exists('gca_flag_enabled') || !gca_flag_enabled('directorate-contact')) {
    return;
}

$location = [[
    ['param' => 'taxonomy', 'operator' => '==', 'value' => 'responsible_team']
]];

$dc_fg = new fewacf\field_group('Team Contact Card', '202604140060a', $location, 10);

$dc_fg->add_field(new acf_fields\message('', 'dc_section_heading', '202604140060p', [
    'message'   => '<strong style="font-size:14px;">Team Contact Card</strong>',
    'esc_html'  => 0,
    'new_lines' => '',
]));

$dc_fg->add_field(new acf_fields\select('Header Icon', 'dc_header_icon_type', '202604140060n', [
    'instructions'  => 'Icon displayed in the card header and pill button.',
    'choices'       => [
        'group'      => 'Group / Team',
        'person'     => 'Person',
        'headphones' => 'Headphones (Support)',
        'chat'       => 'Chat',
    ],
    'default_value' => 'group',
    'allow_null'    => 0,
]));

$dc_fg->add_field(new acf_fields\color_picker('Header Icon Colour', 'dc_header_color', '202604140060b', [
    'instructions'  => 'Colour used for the card header icon.',
    'default_value' => '#6b7280',
]));

$dc_fg->add_field(new acf_fields\text('Card Title', 'dc_popup_title', '202604140060d', [
    'instructions' => 'Main heading shown in the contact card. Leave blank to disable the card entirely for this term.',
]));

$dc_fg->add_field(new acf_fields\textarea('Card Description', 'dc_popup_description', '202604140060e', [
    'instructions' => 'Short description shown below the heading.',
    'rows'         => 3,
    'new_lines'    => '',
]));

$dc_fg->add_field(
    (new acf_fields\repeater('Contact Items', 'dc_contact_items', '202604140060f', [
        'button_label' => 'Add Contact Item',
        'layout'       => 'row',
        'max'          => 5,
    ]))
    ->add_sub_field(new acf_fields\select('Icon', 'dc_icon_type', '202604140060g', [
        'instructions'  => 'Choose an icon for this contact method.',
        'choices'       => [
            'email'    => 'Email',
            'phone'    => 'Phone',
            'link'     => 'External Link',
            'document' => 'Document',
        ],
        'default_value' => 'email',
        'allow_null'    => 0,
    ]))
    ->add_sub_field(new acf_fields\text('Item Title', 'dc_item_title', '202604140060i', [
        'instructions' => 'Short label for this contact method (e.g. "Email", "Call (Ext. 4001)").',
        'required'     => 1,
    ]))
    ->add_sub_field(new acf_fields\text('Item Subtitle', 'dc_item_subtitle', '202604140060j', [
        'instructions' => 'The contact detail (e.g. email address, phone number).',
    ]))
    ->add_sub_field(new acf_fields\url('Item Link URL', 'dc_item_link', '202604140060k', [
        'instructions' => 'Optional URL — makes the entire row clickable. Leave blank for non-clickable items.',
    ]))
);

$dc_fg->add_field(new acf_fields\text('Footer Link Text', 'dc_footer_link_text', '202604140060l', [
    'instructions' => 'Text for the link at the bottom of the card (e.g. "View full HR Staff Directory →").',
]));

$dc_fg->add_field(new acf_fields\url('Footer Link URL', 'dc_footer_link_url', '202604140060m', [
    'instructions' => 'URL for the footer link.',
]));

$dc_fg->register();

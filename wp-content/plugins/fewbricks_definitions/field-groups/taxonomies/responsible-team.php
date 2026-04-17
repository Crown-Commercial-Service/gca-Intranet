<?php

use fewbricks\acf as fewacf;
use fewbricks\acf\fields as acf_fields;

/**
 * ACF field group for the responsible_team taxonomy.
 * Editors configure the directorate contact popup on the term edit screen.
 * Any page tagged with the term automatically gets the popup in the footer.
 */

$location = [[
    ['param' => 'taxonomy', 'operator' => '==', 'value' => 'responsible_team']
]];

$dc_fg = new fewacf\field_group('Directorate Contact Popup', '202604140060a', $location, 10);

$dc_fg->add_field(new acf_fields\color_picker('Header Background Colour', 'dc_header_color', '202604140060b', [
    'instructions'  => 'Background colour for the popup header bar.',
    'default_value' => '#3d53de',
]));

$dc_fg->add_field(new acf_fields\text('Popup Category Label', 'dc_popup_category_label', '202604140060c', [
    'instructions'  => 'Small badge label shown at the top of the popup (e.g. "DIRECTORATE CONTACT"). Leave blank to hide.',
    'default_value' => 'DIRECTORATE CONTACT',
]));

$dc_fg->add_field(new acf_fields\text('Popup Title', 'dc_popup_title', '202604140060d', [
    'instructions' => 'Main heading inside the popup. Leave blank to disable the popup entirely for this term.',
]));

$dc_fg->add_field(new acf_fields\textarea('Popup Description', 'dc_popup_description', '202604140060e', [
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
    ->add_sub_field(new acf_fields\color_picker('Icon Background Colour', 'dc_icon_bg_color', '202604140060h', [
        'instructions'  => 'Background colour for the icon circle.',
        'default_value' => '#3d53de',
    ]))
    ->add_sub_field(new acf_fields\text('Item Title', 'dc_item_title', '202604140060i', [
        'instructions' => 'Bold label for this contact method (e.g. "Email the HR Helpdesk").',
        'required'     => 1,
    ]))
    ->add_sub_field(new acf_fields\text('Item Subtitle', 'dc_item_subtitle', '202604140060j', [
        'instructions' => 'Secondary line (e.g. email address, phone number, wait time).',
    ]))
    ->add_sub_field(new acf_fields\url('Item Link URL', 'dc_item_link', '202604140060k', [
        'instructions' => 'Optional URL — makes the entire row clickable. Leave blank for non-clickable items.',
    ]))
);

$dc_fg->add_field(new acf_fields\text('Footer Link Text', 'dc_footer_link_text', '202604140060l', [
    'instructions' => 'Text for the link at the bottom of the popup (e.g. "View full HR Staff Directory →").',
]));

$dc_fg->add_field(new acf_fields\url('Footer Link URL', 'dc_footer_link_url', '202604140060m', [
    'instructions' => 'URL for the footer link.',
]));

$dc_fg->register();

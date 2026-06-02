<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Page Feedback
//
// Renders an "Is this page useful?" widget above the footer on all singular
// pages. Yes/No collect checkbox reasons; Report a Problem collects freetext.
// All three forms are created programmatically via GFAPI on first use and are
// editable from WP Admin → Forms.
// -----------------------------------------------------------------------------

gca_register_feature_flag('page-feedback', [
    'label'       => 'Page Feedback',
    'description' => 'Shows an "Is this page useful?" feedback widget above the footer on singular pages. Requires Gravity Forms.',
    'default'     => false,
    'tags'        => ['ui', 'feedback', 'gravity-forms'],
]);

if (!gca_flag_enabled('page-feedback')) {
    return;
}

add_action('init', 'gca_pf_maybe_create_forms');

add_filter('gform_pre_render', function (array $form): array {
    $yes_id = (int) get_option('gca_pf_yes_form_id', 0);
    $no_id  = (int) get_option('gca_pf_no_form_id', 0);
    if (!in_array((int) $form['id'], [$yes_id, $no_id], true) || empty($form['description'])) {
        return $form;
    }
    foreach ($form['fields'] as $field) {
        if ($field->type === 'checkbox') {
            $field->label = $form['description'];
            break;
        }
    }
    return $form;
}, 10, 1);

add_filter('gform_field_content', function (string $content, GF_Field $field, $value, $entry_id, int $form_id): string {
    $our_ids = array_filter([
        (int) get_option('gca_pf_yes_form_id', 0),
        (int) get_option('gca_pf_no_form_id', 0),
        (int) get_option('gca_pf_report_form_id', 0),
    ]);
    if (!in_array($form_id, $our_ids, true) || $field->type !== 'checkbox') {
        return $content;
    }
    return preg_replace(
        '/(<legend\b[^>]*>)(.*?)(<\/legend>)/s',
        '$1<h3 class="gca-pf-checkbox-heading">$2</h3>$3',
        $content
    );
}, 10, 5);

add_filter('gform_validation_message', function (string $message, array $form): string {
    $our_ids = array_filter([
        (int) get_option('gca_pf_yes_form_id', 0),
        (int) get_option('gca_pf_no_form_id', 0),
        (int) get_option('gca_pf_report_form_id', 0),
    ]);
    if (in_array((int) $form['id'], $our_ids, true)) {
        return '<h2 class="gform_submission_error hide_summary">There is a problem</h2>';
    }
    return $message;
}, 10, 2);

// Bump this whenever a form definition changes — triggers an auto-update of existing forms.
define('GCA_PF_FORMS_VERSION', 6);

// ---------------------------------------------------------------------------
// Gravity Forms – programmatic form creation / update
// ---------------------------------------------------------------------------

function gca_pf_maybe_create_forms(): void
{
    if (!class_exists('GFAPI')) {
        return;
    }

    $needs_update = (int) get_option('gca_pf_forms_version', 0) < GCA_PF_FORMS_VERSION;

    foreach ([
        'gca_pf_yes_form_id'    => 'gca_pf_yes_form_definition',
        'gca_pf_no_form_id'     => 'gca_pf_no_form_definition',
        'gca_pf_report_form_id' => 'gca_pf_report_form_definition',
    ] as $option => $definition_fn) {
        $stored_id = (int) get_option($option, 0);
        if ($stored_id > 0 && GFAPI::form_id_exists($stored_id)) {
            if ($needs_update) {
                $definition       = $definition_fn();
                $definition['id'] = $stored_id;
                GFAPI::update_form($definition);
            }
            continue;
        }
        $new_id = GFAPI::add_form($definition_fn());
        if (!is_wp_error($new_id)) {
            update_option($option, $new_id);
        }
    }

    if ($needs_update) {
        update_option('gca_pf_forms_version', GCA_PF_FORMS_VERSION);
    }
}

function gca_pf_confirmation(): array
{
    $id = uniqid('', false);
    return [
        $id => [
            'id'               => $id,
            'name'             => 'Default Confirmation',
            'isDefault'        => true,
            'type'             => 'message',
            'message'          => ' ',
            'url'              => '',
            'pageId'           => '',
            'queryString'      => '',
            'conditionalLogic' => '',
        ],
    ];
}

function gca_pf_yes_form_definition(): array
{
    return [
        'title'        => 'Page Feedback – Useful',
        'labelPlacement' => 'top_label',
        'button'       => ['type' => 'text', 'text' => 'Submit', 'imageUrl' => ''],
        'confirmations' => gca_pf_confirmation(),
        'notifications' => [],
        'is_active'    => '1',
        'fields'       => [
            [
                'id'           => 1,
                'type'         => 'checkbox',
                'label'        => 'Can you tell us why this page was useful?',
                'isRequired'   => true,
                'errorMessage' => 'Select at least one option',
                'choices'      => [
                    ['text' => 'I found the information I was looking for',            'value' => 'found_info',       'isSelected' => false],
                    ['text' => 'The information was clear and easy to understand',     'value' => 'clear_info',       'isSelected' => false],
                    ['text' => 'The information helped me complete what I needed to do', 'value' => 'helped_complete', 'isSelected' => false],
                ],
                'inputs' => [
                    ['id' => 1.1, 'label' => 'I found the information I was looking for',            'name' => ''],
                    ['id' => 1.2, 'label' => 'The information was clear and easy to understand',     'name' => ''],
                    ['id' => 1.3, 'label' => 'The information helped me complete what I needed to do', 'name' => ''],
                ],
            ],
            ['id' => 2, 'type' => 'hidden', 'label' => 'Page ID',        'defaultValue' => '{embed_post:ID}'],
            ['id' => 3, 'type' => 'hidden', 'label' => 'Response Type',  'defaultValue' => 'yes'],
        ],
    ];
}

function gca_pf_no_form_definition(): array
{
    return [
        'title'        => 'Page Feedback – Not Useful',
        'labelPlacement' => 'top_label',
        'button'       => ['type' => 'text', 'text' => 'Submit', 'imageUrl' => ''],
        'confirmations' => gca_pf_confirmation(),
        'notifications' => [],
        'is_active'    => '1',
        'fields'       => [
            [
                'id'           => 1,
                'type'         => 'checkbox',
                'label'        => 'Can you tell us why this page was not useful?',
                'isRequired'   => true,
                'errorMessage' => 'Select at least one option',
                'choices'      => [
                    ['text' => 'I could not find what I was looking for',   'value' => 'not_found',        'isSelected' => false],
                    ['text' => 'The information was out of date',            'value' => 'out_of_date',      'isSelected' => false],
                    ['text' => 'The information was difficult to understand', 'value' => 'hard_to_understand', 'isSelected' => false],
                ],
                'inputs' => [
                    ['id' => 1.1, 'label' => 'I could not find what I was looking for',    'name' => ''],
                    ['id' => 1.2, 'label' => 'The information was out of date',             'name' => ''],
                    ['id' => 1.3, 'label' => 'The information was difficult to understand', 'name' => ''],
                ],
            ],
            ['id' => 2, 'type' => 'hidden', 'label' => 'Page ID',       'defaultValue' => '{embed_post:ID}'],
            ['id' => 3, 'type' => 'hidden', 'label' => 'Response Type', 'defaultValue' => 'no'],
        ],
    ];
}

function gca_pf_report_form_definition(): array
{
    return [
        'title'        => 'Page Feedback – Report a Problem',
        'labelPlacement' => 'top_label',
        'button'       => ['type' => 'text', 'text' => 'Submit', 'imageUrl' => ''],
        'confirmations' => gca_pf_confirmation(),
        'notifications' => [],
        'is_active'    => '1',
        'fields'       => [
            ['id' => 1, 'type' => 'textarea', 'label' => 'What were you doing?', 'isRequired' => true, 'errorMessage' => 'Enter what you were doing'],
            ['id' => 2, 'type' => 'textarea', 'label' => 'What went wrong?',     'isRequired' => true, 'errorMessage' => 'Enter what went wrong'],
            ['id' => 3, 'type' => 'hidden',   'label' => 'Page ID',              'defaultValue' => '{embed_post:ID}'],
            ['id' => 4, 'type' => 'hidden',   'label' => 'Response Type',        'defaultValue' => 'report'],
        ],
    ];
}


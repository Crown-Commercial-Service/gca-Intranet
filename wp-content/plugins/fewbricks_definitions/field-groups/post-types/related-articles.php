<?php
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

$location = [
    [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'news' ] ],
    [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'blog' ] ],
    [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'work_update' ] ],
    [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'event' ] ],
];

$fg = ( new fewacf\field_group( 'Related Articles', '202605200001a', $location, 100, [
    'position'                         => 'normal',
    'names_of_items_to_hide_on_screen' => [],
] ) );

$fg->add_field( new acf_fields\true_false( 'Show related articles', 'related_articles_enabled', '202605200005a', [
    'instructions'  => 'Toggle to show or hide the related articles section on this post.',
    'default_value' => 1,
    'ui'            => 1,
    'ui_on_text'    => 'Visible',
    'ui_off_text'   => 'Hidden',
] ) );

$fg->add_field( new acf_fields\text( 'Heading', 'related_articles_heading', '202605200002a', [
    'instructions'  => 'Always use the title Related articles.',
    'default_value' => 'Related articles',
    'maxlength'     => 50,
] ) );

$fg->add_field( new acf_fields\select( 'Filter by taxonomy', 'related_articles_taxonomy', '202605200003a', [
    'instructions' => 'Select a taxonomy term to use as the fallback filter. If no specific articles are selected below, the 3 most recent posts with this term will be shown. Leave empty to hide the section when no articles are selected.',
    'allow_null'   => 1,
    'choices'      => [],
] ) );

$fg->add_field( new acf_fields\relationship( 'Select specific articles', 'related_articles_posts', '202605200004a', [
    'instructions'  => 'Maximum of 3 articles. Use search to find articles. If left empty, the 3 most recent articles matching the taxonomy selected above will be shown.',
    'post_type'     => [ 'news', 'blog', 'work_update', 'event' ],
    'max'           => 3,
    'filters'       => [ 'search' ],
    'return_format' => 'object',
] ) );

$fg->register();

add_filter( 'acf/fields/relationship/query/name=related_articles_posts', function ( $args, $field, $post_id ) {
    $taxonomy_value = isset( $_POST['gca_taxonomy_filter'] ) ? sanitize_text_field( $_POST['gca_taxonomy_filter'] ) : '';
    if ( ! $taxonomy_value ) {
        return $args;
    }

    $parts = explode( ':', $taxonomy_value, 2 );
    if ( count( $parts ) !== 2 ) {
        return $args;
    }

    [ $tax_slug, $term_id ] = $parts;

    $args['tax_query'] = [ [
        'taxonomy' => sanitize_key( $tax_slug ),
        'field'    => 'term_id',
        'terms'    => (int) $term_id,
    ] ];

    return $args;
}, 10, 3 );

add_filter( 'acf/load_field/name=related_articles_taxonomy', function ( $field ) {
    $field['choices'] = [];

    $taxonomies = [ 'category', 'label', 'responsible_team', 'event_location', 'audience' ];

    foreach ( $taxonomies as $tax_slug ) {
        $taxonomy = get_taxonomy( $tax_slug );
        if ( ! $taxonomy ) {
            continue;
        }

        $terms = get_terms( [ 'taxonomy' => $tax_slug, 'hide_empty' => false ] );
        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            continue;
        }

        $field['choices'][ $taxonomy->label ] = [];
        foreach ( $terms as $term ) {
            $field['choices'][ $taxonomy->label ][ $tax_slug . ':' . $term->term_id ] = $term->name;
        }
    }

    return $field;
} );

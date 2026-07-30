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
    'instructions'  => 'Toggle to show related articles. When turned on, the 3 most recent articles will show. If a taxonomy is selected, the 3 most recent within that taxonomy is shown. You can also choose specific articles to display below.',
    'default_value' => 0,
    'ui'            => 1,
    'ui_on_text'    => 'On',
    'ui_off_text'   => 'Off',
] ) );

$fg->add_field( new acf_fields\text( 'Heading', 'related_articles_heading', '202605200002a', [
    'instructions'  => 'Enter a heading for the related articles section.',
    'default_value' => 'Related articles',
    'maxlength'     => 50,
] ) );

$fg->add_field( new acf_fields\select( 'Type', 'related_articles_type', '202605200006a', [
    'instructions'  => 'Select the type of content to show as related articles. If a taxonomy is selected or specific articles are chosen below, those take priority over this setting.',
    'default_value' => 'all',
    'allow_null'    => 0,
    'choices'       => [ 'all' => 'All' ],
] ) );

$fg->add_field( new acf_fields\select( 'Filter by taxonomy', 'related_articles_taxonomy', '202605200003a', [
    'instructions' => 'Select a taxonomy to show related articles. If left empty, the 3 most recent articles will show.',
    'allow_null'   => 1,
    'choices'      => [],
] ) );

$fg->add_field( new acf_fields\relationship( 'Select specific articles', 'related_articles_posts', '202605200004a', [
    'instructions'  => 'If you want to specify which articles are shown, select a maximum of 3 articles using the search below.',
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

add_filter( 'acf/load_field/name=related_articles_type', function ( $field ) {
    $field['choices'] = [ 'all' => 'All' ];

    $post_types = [ 'news', 'blog', 'work_update', 'event' ];

    foreach ( $post_types as $post_type_slug ) {
        $post_type = get_post_type_object( $post_type_slug );
        if ( ! $post_type ) {
            continue;
        }

        $field['choices'][ $post_type_slug ] = $post_type->labels->name;
    }

    return $field;
} );

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

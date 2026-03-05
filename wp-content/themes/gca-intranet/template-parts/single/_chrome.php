<?php
/**
 * Shared single/page chrome: header, hero, breadcrumbs
 * Sets $GLOBALS['gca_is_page'] for downstream templates.
 */

get_header();

$hero_id  = (int) get_post_meta(get_the_ID(), '_gca_hero_image_id', true);
$hero_url = $hero_id ? wp_get_attachment_image_url($hero_id, 'large') : '';

$post_type = get_post_type();
$is_page   = ($post_type === 'page');

// Hero title:
// - Pages: pass '' so hero.php falls back to get_the_title()
// - CPTs: use CPT singular label (News/Blog/Event/Work update)
$hero_title = '';
if (!$is_page) {
  $pt_obj = get_post_type_object($post_type);
  if ($pt_obj && !empty($pt_obj->labels->singular_name)) {
    $hero_title = $pt_obj->labels->singular_name;
  }
}

get_template_part('template-parts/hero', null, [
  'title'     => $hero_title,
  'image_url' => $hero_url ?: '',
]);

get_template_part('template-parts/breadcrumbs');

$GLOBALS['gca_is_page'] = $is_page;
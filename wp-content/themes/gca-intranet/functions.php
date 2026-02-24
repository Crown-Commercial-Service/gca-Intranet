<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Child theme assets + GOV.UK JS init (scoped)
 */
add_action('wp_enqueue_scripts', function (): void {

  // Child theme CSS
  wp_enqueue_style(
    'gca-intranet-child',
    get_stylesheet_directory_uri() . '/style.css',
    [],
    filemtime(get_stylesheet_directory() . '/style.css')
  );

  /**
   * GOV.UK Frontend JS (scoped rollout)
   * Parent theme build outputs GOV.UK JS to:
   * /wp-content/themes/gca-intranet-foundation/assets/scripts/all.min.js
   *
   * In a child theme, get_template_directory_uri() points to the parent theme.
   */
  if (is_single()) {

    $govuk_js_rel = '/assets/scripts/all.min.js';
    $govuk_js_abs = get_template_directory() . $govuk_js_rel;
    $govuk_js_ver = file_exists($govuk_js_abs) ? (string) filemtime($govuk_js_abs) : '1.0.0';

    wp_enqueue_script(
      'gca-govuk-frontend',
      get_template_directory_uri() . $govuk_js_rel,
      [],
      $govuk_js_ver,
      true
    );

    // Initialise GOV.UK components
    wp_add_inline_script(
      'gca-govuk-frontend',
      'window.GOVUKFrontend && window.GOVUKFrontend.initAll && window.GOVUKFrontend.initAll();',
      'after'
    );
  }

}, 999);

/**
 * HERO IMAGE META (editable header/hero image, separate from featured image)
 * Stored as attachment ID in post meta: _gca_hero_image_id
 */
add_action('add_meta_boxes', function (): void {

  add_meta_box(
    'gca_hero_image',
    __('Hero header image', 'gca-intranet'),
    function ($post): void {
      $meta_key = '_gca_hero_image_id';
      $image_id = (int) get_post_meta($post->ID, $meta_key, true);

      wp_nonce_field('gca_hero_image_save', 'gca_hero_image_nonce');

      $thumb = $image_id
        ? wp_get_attachment_image($image_id, 'medium', false, ['style' => 'max-width:100%;height:auto;'])
        : '';

      echo '<p>' . esc_html__(
        'Choose an image to appear in the page header (oval). This is separate from the featured image used in the content.',
        'gca-intranet'
      ) . '</p>';

      echo '<div id="gca-hero-image-preview" style="margin:12px 0;">' . ($thumb ?: '') . '</div>';

      echo '<input type="hidden" id="gca_hero_image_id" name="gca_hero_image_id" value="' . esc_attr((string) $image_id) . '">';

      echo '<p style="display:flex;gap:8px;align-items:center;">';
      echo '<button type="button" class="button" id="gca-hero-image-select">' . esc_html__('Select image', 'gca-intranet') . '</button>';
      echo '<button type="button" class="button" id="gca-hero-image-remove" ' . ($image_id ? '' : 'disabled') . '>' . esc_html__('Remove', 'gca-intranet') . '</button>';
      echo '</p>';
    },
    ['post', 'page'],
    'side',
    'default'
  );
});

add_action('save_post', function (int $post_id): void {

  if (!isset($_POST['gca_hero_image_nonce']) || !wp_verify_nonce($_POST['gca_hero_image_nonce'], 'gca_hero_image_save')) {
    return;
  }

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return;
  }

  if (!current_user_can('edit_post', $post_id)) {
    return;
  }

  $image_id = isset($_POST['gca_hero_image_id']) ? (int) $_POST['gca_hero_image_id'] : 0;

  if ($image_id > 0) {
    update_post_meta($post_id, '_gca_hero_image_id', $image_id);
  } else {
    delete_post_meta($post_id, '_gca_hero_image_id');
  }
});

/**
 * Admin JS for the Hero header image meta box (uses WP Media Library)
 */
add_action('admin_enqueue_scripts', function (): void {

  wp_enqueue_media();

  $js = <<<'JS'
(function(){
  function ready(fn){ if(document.readyState!=='loading'){ fn(); } else { document.addEventListener('DOMContentLoaded', fn); } }
  ready(function(){
    var selectBtn = document.getElementById('gca-hero-image-select');
    var removeBtn = document.getElementById('gca-hero-image-remove');
    var input = document.getElementById('gca_hero_image_id');
    var preview = document.getElementById('gca-hero-image-preview');
    if(!selectBtn || !input || !preview) return;

    var frame;

    selectBtn.addEventListener('click', function(e){
      e.preventDefault();
      if(frame){ frame.open(); return; }

      frame = wp.media({
        title: 'Select hero header image',
        button: { text: 'Use this image' },
        multiple: false
      });

      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        input.value = attachment.id || '';
        preview.innerHTML = (attachment.sizes && attachment.sizes.medium)
          ? '<img src="'+attachment.sizes.medium.url+'" style="max-width:100%;height:auto;" />'
          : '<img src="'+attachment.url+'" style="max-width:100%;height:auto;" />';
        if(removeBtn) removeBtn.disabled = false;
      });

      frame.open();
    });

    if(removeBtn){
      removeBtn.addEventListener('click', function(e){
        e.preventDefault();
        input.value = '';
        preview.innerHTML = '';
        removeBtn.disabled = true;
      });
    }
  });
})();
JS;

  // Load after jquery-core (available in WP admin)
  wp_add_inline_script('jquery-core', $js, 'after');
});
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
    (string) filemtime(get_stylesheet_directory() . '/style.css')
  );

  /**
   * GOV.UK Frontend JS (scoped rollout)
   * Parent theme build outputs GOV.UK JS to:
   * /wp-content/themes/gca-intranet-foundation/assets/scripts/all.min.js
   *
   * In a child theme, get_template_directory_uri() points to the parent theme.
   */
  if (is_singular()) { // was is_single(); pages need this too for GOVUK components
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
    // Apply to CPTs + pages (and keep 'post' if you still use standard posts anywhere)
    ['page', 'news', 'blog', 'event', 'work_update', 'post'],
    'side',
    'default'
  );
});

add_action('save_post', function (int $post_id): void {

  if (!isset($_POST['gca_hero_image_nonce']) || !wp_verify_nonce((string) $_POST['gca_hero_image_nonce'], 'gca_hero_image_save')) {
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
 * GI-17: Two-column layout left column WYSIWYG
 * Stored in post meta: _gca_col2_wysiwyg
 * Only shown in the editor UI when template-layout-2col.php is selected
 */
add_action('add_meta_boxes', function (): void {
  $screens = ['page', 'news', 'blog', 'event', 'work_update'];

  add_meta_box(
    'gca_col2_content',
    __('Layout: left column content', 'gca-intranet'),
    'gca_render_col2_metabox',
    $screens,
    'normal',
    'high'
  );
});

function gca_render_col2_metabox(\WP_Post $post): void
{
  wp_nonce_field('gca_save_layout_col2', 'gca_layout_col2_nonce');

  $value = (string) get_post_meta($post->ID, '_gca_col2_wysiwyg', true);

  wp_editor(
    $value,
    'gca_col2_wysiwyg_editor',
    [
      'textarea_name' => 'gca_col2_wysiwyg',
      'media_buttons' => true,
      'textarea_rows' => 10,
      'tinymce'       => true,
      'quicktags'     => true,
    ]
  );

  echo '<p class="description">' . esc_html__(
    'Only used when the “Layout – 2 column” template is selected.',
    'gca-intranet'
  ) . '</p>';
}

add_action('save_post', function (int $post_id): void {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

  if (!isset($_POST['gca_layout_col2_nonce']) || !wp_verify_nonce((string) $_POST['gca_layout_col2_nonce'], 'gca_save_layout_col2')) {
    return;
  }

  if (!current_user_can('edit_post', $post_id)) return;

  if (isset($_POST['gca_col2_wysiwyg'])) {
    update_post_meta($post_id, '_gca_col2_wysiwyg', wp_kses_post((string) $_POST['gca_col2_wysiwyg']));
  }
});

/**
 * Featured image display toggle (UNIVERSAL)
 * Applies to: news, blog
 * Stored in post meta: _gca_hide_featured_image (1/0)
 *
 * This affects BOTH 1-col and 2-col templates (and any future ones).
 */
add_action('add_meta_boxes', function (): void {
  $screens = ['news', 'blog'];

  add_meta_box(
    'gca_featured_image_toggle',
    __('Featured image', 'gca-intranet'),
    function (\WP_Post $post): void {
      wp_nonce_field('gca_save_featured_image_toggle', 'gca_featured_image_toggle_nonce');

      $checked = get_post_meta($post->ID, '_gca_hide_featured_image', true) ? 'checked' : '';

      echo '<label style="display:flex;gap:8px;align-items:center;">';
      echo '<input type="checkbox" name="gca_hide_featured_image" value="1" ' . $checked . ' />';
      echo esc_html__('Hide featured image on page', 'gca-intranet');
      echo '</label>';

      echo '<p class="description" style="margin-top:8px;">' . esc_html__(
        'If checked, the featured image will not show on the page (regardless of template).',
        'gca-intranet'
      ) . '</p>';
    },
    $screens,
    'side',
    'default'
  );
});

add_action('save_post', function (int $post_id): void {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

  if (!isset($_POST['gca_featured_image_toggle_nonce']) || !wp_verify_nonce((string) $_POST['gca_featured_image_toggle_nonce'], 'gca_save_featured_image_toggle')) {
    return;
  }

  if (!current_user_can('edit_post', $post_id)) return;

  $hide = isset($_POST['gca_hide_featured_image']) ? 1 : 0;

  if ($hide) {
    update_post_meta($post_id, '_gca_hide_featured_image', 1);
  } else {
    delete_post_meta($post_id, '_gca_hide_featured_image');
  }
});

/**
 * Admin JS for:
 * - Hero header image meta box (WP Media Library)
 * - Only show GI-17 left column metabox when template-layout-2col.php is selected
 */
add_action('admin_enqueue_scripts', function (string $hook): void {

  // Only on post editor screens
  if ($hook !== 'post.php' && $hook !== 'post-new.php') {
    return;
  }

  wp_enqueue_media();

  $js = <<<'JS'
(function(){
  function ready(fn){ if(document.readyState!=='loading'){ fn(); } else { document.addEventListener('DOMContentLoaded', fn); } }

  // ----------------------------
  // Hero image metabox behaviour
  // ----------------------------
  function initHeroImage(){
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
  }

  // --------------------------------------------
  // Template switcher helpers (classic + Gutenberg)
  // --------------------------------------------
  function getTemplateValue(){
    // Classic editor uses #page_template
    var sel = document.getElementById('page_template');
    if (sel) return sel.value;

    // Some editors store it in a hidden input
    var input = document.querySelector('input[name="page_template"]');
    if (input) return input.value;

    return '';
  }

  function set2ColBoxesVisibility(){
    var template = getTemplateValue();
    var shouldShow = (template === 'template-layout-2col.php');

    // Main left column WYSIWYG
    var col2Box = document.getElementById('gca_col2_content');
    if (col2Box) col2Box.style.display = shouldShow ? '' : 'none';
  }

  ready(function(){
    initHeroImage();
    set2ColBoxesVisibility();

    // Watch for changes (classic editor)
    var sel = document.getElementById('page_template');
    if (sel) {
      sel.addEventListener('change', set2ColBoxesVisibility);
    }

    // Gutenberg: keep it simple and robust
    setInterval(set2ColBoxesVisibility, 800);
  });
})();
JS;

  wp_add_inline_script('jquery-core', $js, 'after');
});

/**
 * Homepage options (Customizer)
 * - Take a look component settings stored as theme_mods
 * - Link text is a plain-text paragraph with a hard 90 char limit (no HTML, no images)
 */
if (!function_exists('gca_sanitize_takealook_link_text')) {
  function gca_sanitize_takealook_link_text($value): string {
    $value = (string) $value;

    // Prevent HTML (and therefore images/links/etc)
    $value = wp_strip_all_tags($value);

    // Normalise whitespace and trim
    $value = preg_replace('/\s+/', ' ', $value);
    $value = trim((string) $value);

    // Hard cap to 90 characters (multibyte safe)
    if (function_exists('mb_substr')) {
      $value = (string) mb_substr($value, 0, 90);
    } else {
      $value = (string) substr($value, 0, 90);
    }

    return $value;
  }
}

/**
 * GI-101: Quick links sanitiser (plain text, hard cap)
 */
if (!function_exists('gca_sanitize_quicklink_text')) {
  function gca_sanitize_quicklink_text($value): string {
    $value = (string) $value;
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/\s+/', ' ', $value);
    $value = trim((string) $value);

    // Hard cap so it stays within the card nicely
    $max = 48;
    if (function_exists('mb_substr')) {
      $value = (string) mb_substr($value, 0, $max);
    } else {
      $value = (string) substr($value, 0, $max);
    }

    return $value;
  }
}

/**
 * Editable homepage text sanitiser (plain text, trimmed)
 * Use this for titles + descriptions to keep them consistent.
 */
if (!function_exists('gca_sanitize_home_text')) {
  function gca_sanitize_home_text($value): string {
    $value = (string) $value;
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/\s+/', ' ', $value);
    return trim((string) $value);
  }
}

/**
 * Editable homepage DESCRIPTION sanitiser (plain text, trimmed, hard 40 char cap)
 * Use this for homepage section descriptions so they stay short.
 */
if (!function_exists('gca_sanitize_home_desc_40')) {
  function gca_sanitize_home_desc_40($value): string {
    $value = (string) $value;
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/\s+/', ' ', $value);
    $value = trim((string) $value);

    $max = 40;
    if (function_exists('mb_substr')) {
      return (string) mb_substr($value, 0, $max);
    }
    return (string) substr($value, 0, $max);
  }
}

add_action('customize_register', function (\WP_Customize_Manager $wp_customize): void {

  $section = 'gca_homepage_options';

  $wp_customize->add_section($section, [
    'title'       => __('Homepage options', 'gca-intranet'),
    'priority'    => 30,
    'description' => __('Controls for homepage components.', 'gca-intranet'),
  ]);

  // ============================================================
  // Latest news (Customizer) — order: 1st on homepage
  // ============================================================

  $wp_customize->add_setting('gca_latestnews_title', [
    'default'           => __('Latest news', 'gca-intranet'),
    'sanitize_callback' => 'gca_sanitize_home_text',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_latestnews_title', [
    'type'     => 'text',
    'section'  => $section,
    'label'    => __('Latest news: title', 'gca-intranet'),
    'priority' => 10,
  ]);

  $wp_customize->add_setting('gca_latestnews_desc', [
    'default'           => "What's happening in our organisation",
    'sanitize_callback' => 'gca_sanitize_home_desc_40',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_latestnews_desc', [
    'type'        => 'textarea',
    'section'     => $section,
    'label'       => __('Latest news: description', 'gca-intranet'),
    'description' => __('Text shown under the “Latest news” heading on the homepage. Max 40 characters (longer text is truncated).', 'gca-intranet'),
    'input_attrs' => [
      'maxlength' => 40,
      'rows'      => 2,
    ],
    'priority'    => 20,
  ]);

  // ============================================================
  // GI-100: Take a look (Customizer) — order: 2nd on homepage
  // ============================================================

  $wp_customize->add_setting('gca_takealook_enabled', [
    'default'           => true,
    'sanitize_callback' => static fn ($v) => (bool) $v,
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_takealook_enabled', [
    'type'     => 'checkbox',
    'section'  => $section,
    'label'    => __('Show “Take a look” block', 'gca-intranet'),
    'priority' => 30,
  ]);

  $wp_customize->add_setting('gca_takealook_title', [
    'default'           => __('Take a look', 'gca-intranet'),
    'sanitize_callback' => 'gca_sanitize_home_text',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_takealook_title', [
    'type'     => 'text',
    'section'  => $section,
    'label'    => __('Take a look: title', 'gca-intranet'),
    'priority' => 40,
  ]);

  $wp_customize->add_setting('gca_takealook_desc', [
    'default'           => '',
    'sanitize_callback' => 'gca_sanitize_home_desc_40',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_takealook_desc', [
    'type'        => 'textarea',
    'section'     => $section,
    'label'       => __('Take a look: description', 'gca-intranet'),
    'description' => __('Optional text shown under the title. Max 40 characters (longer text is truncated).', 'gca-intranet'),
    'input_attrs' => [
      'maxlength' => 40,
      'rows'      => 2,
    ],
    'priority'    => 50,
  ]);

  $wp_customize->add_setting('gca_takealook_link_text', [
    'default'           => __('Learn more', 'gca-intranet'),
    'sanitize_callback' => 'gca_sanitize_takealook_link_text',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_takealook_link_text', [
    'type'        => 'textarea',
    'section'     => $section,
    'label'       => __('Take a look: link text', 'gca-intranet'),
    'description' => __('Plain text only. Max 90 characters. No images or HTML.', 'gca-intranet'),
    'input_attrs' => [
      'maxlength' => 90,
      'rows'      => 3,
    ],
    'priority'    => 60,
  ]);

  $wp_customize->add_setting('gca_takealook_link_url', [
    'default'           => '',
    'sanitize_callback' => 'esc_url_raw',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_takealook_link_url', [
    'type'        => 'url',
    'section'     => $section,
    'label'       => __('Take a look: link URL', 'gca-intranet'),
    'description' => __('If empty, the block renders as “not configured”.', 'gca-intranet'),
    'priority'    => 70,
  ]);

  // ============================================================
  // GI-101: Quick links (Customizer) — order: 3rd on homepage
  // ============================================================

  $wp_customize->add_setting('gca_quicklinks_enabled', [
    'default'           => true,
    'sanitize_callback' => static fn ($v) => (bool) $v,
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_quicklinks_enabled', [
    'type'     => 'checkbox',
    'section'  => $section,
    'label'    => __('Show “Quick links” block', 'gca-intranet'),
    'priority' => 80,
  ]);

  $wp_customize->add_setting('gca_quicklinks_title', [
    'default'           => __('Quick links', 'gca-intranet'),
    'sanitize_callback' => 'gca_sanitize_home_text',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_quicklinks_title', [
    'type'     => 'text',
    'section'  => $section,
    'label'    => __('Quick links: title', 'gca-intranet'),
    'priority' => 90,
  ]);

  $wp_customize->add_setting('gca_quicklinks_desc', [
    'default'           => '',
    'sanitize_callback' => 'gca_sanitize_home_desc_40',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_quicklinks_desc', [
    'type'        => 'textarea',
    'section'     => $section,
    'label'       => __('Quick links: description', 'gca-intranet'),
    'description' => __('Optional text shown under the title. Max 40 characters (longer text is truncated).', 'gca-intranet'),
    'input_attrs' => [
      'maxlength' => 40,
      'rows'      => 2,
    ],
    'priority'    => 100,
  ]);

  $priority = 110;
  for ($i = 1; $i <= 3; $i++) {

    $wp_customize->add_setting("gca_quicklinks_{$i}_text", [
      'default'           => '',
      'sanitize_callback' => 'gca_sanitize_quicklink_text',
      'transport'         => 'refresh',
    ]);

    $wp_customize->add_control("gca_quicklinks_{$i}_text", [
      'type'        => 'text',
      'section'     => $section,
      'label'       => sprintf(__('Quick link %d: text', 'gca-intranet'), $i),
      'description' => __('Plain text only.', 'gca-intranet'),
      'input_attrs' => [
        'maxlength' => 48,
      ],
      'priority'    => $priority,
    ]);

    $priority += 10;

    $wp_customize->add_setting("gca_quicklinks_{$i}_url", [
      'default'           => '',
      'sanitize_callback' => 'esc_url_raw',
      'transport'         => 'refresh',
    ]);

    $wp_customize->add_control("gca_quicklinks_{$i}_url", [
      'type'        => 'url',
      'section'     => $section,
      'label'       => sprintf(__('Quick link %d: URL', 'gca-intranet'), $i),
      'description' => __('Full URL (e.g. https://…).', 'gca-intranet'),
      'priority'    => $priority,
    ]);

    $priority += 10;
  }

  // ============================================================
  // Work updates (Customizer) — order: 4th on homepage
  // ============================================================

  $wp_customize->add_setting('gca_workupdates_title', [
    'default'           => __('Work updates', 'gca-intranet'),
    'sanitize_callback' => 'gca_sanitize_home_text',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_workupdates_title', [
    'type'     => 'text',
    'section'  => $section,
    'label'    => __('Work updates: title', 'gca-intranet'),
    'priority' => 170,
  ]);

  $wp_customize->add_setting('gca_workupdates_desc', [
    'default'           => '',
    'sanitize_callback' => 'gca_sanitize_home_desc_40',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_workupdates_desc', [
    'type'        => 'textarea',
    'section'     => $section,
    'label'       => __('Work updates: description', 'gca-intranet'),
    'description' => __('Text shown under the “Work updates” heading on the homepage. Max 40 characters (longer text is truncated).', 'gca-intranet'),
    'input_attrs' => [
      'maxlength' => 40,
      'rows'      => 2,
    ],
    'priority'    => 180,
  ]);

  // ============================================================
  // Blogs (Customizer) — order: 5th on homepage
  // ============================================================

  $wp_customize->add_setting('gca_blogs_title', [
    'default'           => __('Blogs', 'gca-intranet'),
    'sanitize_callback' => 'gca_sanitize_home_text',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_blogs_title', [
    'type'     => 'text',
    'section'  => $section,
    'label'    => __('Blogs: title', 'gca-intranet'),
    'priority' => 190,
  ]);

  $wp_customize->add_setting('gca_blogs_desc', [
    'default'           => '',
    'sanitize_callback' => 'gca_sanitize_home_desc_40',
    'transport'         => 'refresh',
  ]);

  $wp_customize->add_control('gca_blogs_desc', [
    'type'        => 'textarea',
    'section'     => $section,
    'label'       => __('Blogs: description', 'gca-intranet'),
    'description' => __('Text shown under the “Blogs” heading on the homepage. Max 40 characters (longer text is truncated).', 'gca-intranet'),
    'input_attrs' => [
      'maxlength' => 40,
      'rows'      => 2,
    ],
    'priority'    => 200,
  ]);
});

/**
 * Customizer UI: character counter for Take a look link text
 * + Quick links text counters (48 chars)
 * + Homepage description counters (40 chars)
 */
add_action('customize_controls_enqueue_scripts', function (): void {
  $js = <<<'JS'
(function(){
  function addCounter(controlId, maxDefault){
    var control = document.getElementById(controlId);
    if(!control) return;

    var input = control.querySelector('textarea, input[type="text"]');
    if(!input) return;

    var max = parseInt(input.getAttribute('maxlength') || String(maxDefault || 0), 10);
    if(!max) return;

    var counter = document.createElement('div');
    counter.style.marginTop = '6px';
    counter.style.fontSize = '12px';
    counter.style.opacity = '0.85';

    function update(){
      var val = input.value || '';
      var len = val.length;

      if(len > max){
        input.value = val.substring(0, max);
        len = max;
      }

      counter.textContent = (max - len) + ' characters remaining';
    }

    input.parentNode.appendChild(counter);
    input.addEventListener('input', update);
    update();
  }

  function init(){
    // Take a look link text (90 chars)
    addCounter('customize-control-gca_takealook_link_text', 90);

    // Quick links text fields (48 chars)
    addCounter('customize-control-gca_quicklinks_1_text', 48);
    addCounter('customize-control-gca_quicklinks_2_text', 48);
    addCounter('customize-control-gca_quicklinks_3_text', 48);

    // Homepage section descriptions (40 chars)
    addCounter('customize-control-gca_latestnews_desc', 40);
    addCounter('customize-control-gca_takealook_desc', 40);
    addCounter('customize-control-gca_quicklinks_desc', 40);
    addCounter('customize-control-gca_workupdates_desc', 40);
    addCounter('customize-control-gca_blogs_desc', 40);
  }

  document.addEventListener('DOMContentLoaded', init);
})();
JS;

  wp_add_inline_script('customize-controls', $js, 'after');
});

/**
 * Admin shortcut: Appearance → Homepage options
 * Sends editors straight to the Customizer section for the homepage blocks.
 */
add_action('admin_menu', function (): void {
  add_theme_page(
    __('Homepage options', 'gca-intranet'),
    __('Homepage options', 'gca-intranet'),
    'edit_theme_options',
    'gca-homepage-options',
    function (): void {
      $url = add_query_arg(
        [
          'autofocus[section]' => 'gca_homepage_options',
          'return'             => urlencode(admin_url('themes.php?page=gca-homepage-options')),
        ],
        admin_url('customize.php')
      );

      wp_safe_redirect($url);
      exit;
    }
  );
});
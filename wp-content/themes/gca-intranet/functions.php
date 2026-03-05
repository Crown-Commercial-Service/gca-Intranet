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

/**
 * GI-100: Take a look component fields on the homepage (WYSIWYG)
 * Stores HTML in: _gca_take_a_look_content
 *
 * NOTE:
 * We keep this metabox for now (backwards compatible), but the homepage
 * template is now driven by Customizer settings (theme_mods).
 */
add_action('add_meta_boxes', function (): void {

  add_meta_box(
    'gca_take_a_look',
    __('Homepage: Take a look', 'gca-intranet'),
    'gca_render_take_a_look_metabox',
    'page',
    'normal',
    'high'
  );
});

function gca_render_take_a_look_metabox(\WP_Post $post): void
{
  // Only show/use on the page set as "Front page" in Settings → Reading
  $front_page_id = (int) get_option('page_on_front');

  if ($front_page_id !== (int) $post->ID) {
    echo '<p>' . esc_html__('This box is only used on the Front page (Settings → Reading).', 'gca-intranet') . '</p>';
    return;
  }

  wp_nonce_field('gca_take_a_look_save', 'gca_take_a_look_nonce');

  $title   = (string) get_post_meta($post->ID, '_gca_take_a_look_title', true);
  $desc    = (string) get_post_meta($post->ID, '_gca_take_a_look_desc', true);
  $content = (string) get_post_meta($post->ID, '_gca_take_a_look_content', true);
  ?>
  <p>
    <label for="gca_take_a_look_title"><strong><?php esc_html_e('Title', 'gca-intranet'); ?></strong></label><br>
    <input
      type="text"
      id="gca_take_a_look_title"
      name="gca_take_a_look_title"
      class="widefat"
      value="<?php echo esc_attr($title); ?>"
      placeholder="<?php echo esc_attr__('Take a look', 'gca-intranet'); ?>"
    >
  </p>

  <p>
    <label for="gca_take_a_look_desc"><strong><?php esc_html_e('Description', 'gca-intranet'); ?></strong></label><br>
    <textarea
      id="gca_take_a_look_desc"
      name="gca_take_a_look_desc"
      class="widefat"
      rows="3"
      placeholder="<?php echo esc_attr__('Short description under the title', 'gca-intranet'); ?>"
    ><?php echo esc_textarea($desc); ?></textarea>
  </p>

  <p>
    <strong><?php esc_html_e('Box content', 'gca-intranet'); ?></strong><br>
    <span class="description">
      <?php esc_html_e('Add text, links, and images. Everything renders inside the green box.', 'gca-intranet'); ?>
    </span>
  </p>

  <?php
  wp_editor(
    $content,
    'gca_take_a_look_content',
    [
      'textarea_name' => 'gca_take_a_look_content',
      'textarea_rows' => 6,
      'media_buttons' => true,
      'teeny'         => false,
      'quicktags'     => true,
    ]
  );
}

add_action('save_post_page', function (int $post_id): void {

  // Autosave / permissions
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return;
  }

  if (!current_user_can('edit_post', $post_id)) {
    return;
  }

  // Only save for the page set as Front page
  $front_page_id = (int) get_option('page_on_front');
  if ($front_page_id !== (int) $post_id) {
    return;
  }

  // Nonce
  if (!isset($_POST['gca_take_a_look_nonce']) || !wp_verify_nonce((string) $_POST['gca_take_a_look_nonce'], 'gca_take_a_look_save')) {
    return;
  }

  $title = isset($_POST['gca_take_a_look_title'])
    ? sanitize_text_field((string) $_POST['gca_take_a_look_title'])
    : '';

  $desc = isset($_POST['gca_take_a_look_desc'])
    ? sanitize_textarea_field((string) $_POST['gca_take_a_look_desc'])
    : '';

  $content = isset($_POST['gca_take_a_look_content'])
    ? wp_kses_post((string) $_POST['gca_take_a_look_content'])
    : '';

  update_post_meta($post_id, '_gca_take_a_look_title', $title);
  update_post_meta($post_id, '_gca_take_a_look_desc', $desc);
  update_post_meta($post_id, '_gca_take_a_look_content', $content);
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
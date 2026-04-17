<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('gca_render_term_tree')) {

    function gca_render_term_tree($taxonomy, $terms, $selected, $level = 0)
    {
        foreach ($terms as $term) {

            $checked = in_array($term->slug, $selected) ? 'checked' : '';
            $input_id = $taxonomy . '-' . $term->slug;

            $indent_class = 'gca-filter-indent-' . $level;

            ?>
            <div class="govuk-checkboxes__item govuk-checkboxes__item--small <?php echo esc_attr($indent_class); ?>">

                <input
                    class="govuk-checkboxes__input govuk-checkboxes__input--small"
                    id="<?php echo esc_attr($input_id); ?>"
                    name="<?php echo esc_attr($taxonomy); ?>[]"
                    type="checkbox"
                    value="<?php echo esc_attr($term->slug); ?>"
                    <?php echo $checked; ?>
                    onchange="this.form.submit();"
                >

                <label
                    class="govuk-label govuk-checkboxes__label"
                    for="<?php echo esc_attr($input_id); ?>"
                >
                    <?php if ($level === 0) : ?>
                        <?php echo esc_html($term->name); ?>
                    <?php else : ?>
                        <?php echo esc_html($term->name); ?>
                    <?php endif; ?>
                </label>

            </div>
            <?php

            $children = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => true,
                'parent'     => $term->term_id,
            ]);

            if (!empty($children) && !is_wp_error($children)) {
                gca_render_term_tree($taxonomy, $children, $selected, $level + 1);
            }
        }
    }
}
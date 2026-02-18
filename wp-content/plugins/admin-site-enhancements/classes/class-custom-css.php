<?php

namespace ASENHA\Classes;

/**
 * Class for Custom Admin and Frontend CSS modules
 *
 * @since 6.9.5
 */
class Custom_Css {

    /**
     * Enqueue custom admin CSS
     * Consider using https://github.com/Cerdic/CSSTidy in the future
     *
     * @since 2.3.0
     */
    public function custom_admin_css() {

        $options = get_option( ASENHA_SLUG_U, array() );
        $custom_admin_css = $options['custom_admin_css'];

        ?>
        <style type="text/css">
            <?php echo wp_strip_all_tags( $custom_admin_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </style>
        <?php

    }

    /**
     * Enqueue custom frontend CSS
     * Consider using https://github.com/Cerdic/CSSTidy in the future
     *
     * @since 2.3.0
     */
    public function custom_frontend_css() {

        $options = get_option( ASENHA_SLUG_U, array() );
        $custom_frontend_css = $options['custom_frontend_css'];

        ?>
        <style type="text/css">
            <?php echo wp_strip_all_tags( $custom_frontend_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </style>
        <?php

    }

}
<?php

namespace ASENHA\Classes;

/**
 * Class for Disable Embeds module. 
 * Ported from the Disable Embeds plugin v1.5.0 by Pascal Birchler
 * @link https://wordpress.org/plugins/disable-embeds/
 * 
 * @since 8.0.0
 */
class Disable_Embeds {

    /**
     * Disable embeds on init.
     *
     * - Removes the needed query vars.
     * - Disables oEmbed discovery.
     * - Completely removes the related JavaScript.
     * - Disables the core-embed/wordpress block type (WordPress 5.0+)
     *
     * @link https://plugins.trac.wordpress.org/browser/disable-embeds/tags/1.5.0/disable-embeds.php#L23
     * @since 8.0.0
     */
    public function disable_embeds_init() {
        /* @var WP $wp */
        global $wp;

        // Remove the embed query var.
        $wp->public_query_vars = array_diff( $wp->public_query_vars, array(
            'embed',
        ) );

        // Remove the oembed/1.0/embed REST route.
        add_filter( 'rest_endpoints', [ $this, 'disable_embeds_remove_embed_endpoint'] );

        // Disable handling of internal embeds in oembed/1.0/proxy REST route.
        add_filter( 'oembed_response_data', [ $this, 'disable_embeds_filter_oembed_response_data' ] );

        // Turn off oEmbed auto discovery.
        add_filter( 'embed_oembed_discover', '__return_false' );

        // Don't filter oEmbed results.
        remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

        // Remove oEmbed discovery links.
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

        // Remove oEmbed-specific JavaScript from the front-end and back-end.
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
        add_filter( 'tiny_mce_plugins', [ $this, 'disable_embeds_tiny_mce_plugin' ] );

        // Remove all embeds rewrite rules.
        add_filter( 'rewrite_rules_array', [ $this, 'disable_embeds_rewrites' ] );

        // Remove filter of the oEmbed result before any HTTP requests are made.
        remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );

        // Load block editor JavaScript.
        add_action( 'enqueue_block_editor_assets', [ $this, 'disable_embeds_enqueue_block_editor_assets' ] );

        // Remove wp-embed dependency of wp-edit-post script handle.
        add_action( 'wp_default_scripts', [ $this, 'disable_embeds_remove_script_dependencies' ] );
    }

    /**
     * Removes the oembed/1.0/embed REST route.
     * 
     * @link https://plugins.trac.wordpress.org/browser/disable-embeds/tags/1.5.0/disable-embeds.php#L128
     * @since 8.0.0
     *
     * @param array $endpoints Registered REST API endpoints.
     * @return array Filtered REST API endpoints.
     */
    public function disable_embeds_remove_embed_endpoint( $endpoints ) {
        unset( $endpoints['/oembed/1.0/embed'] );

        return $endpoints;
    }

    /**
     * Disables sending internal oEmbed response data in proxy endpoint.
     *
     * @link https://plugins.trac.wordpress.org/browser/disable-embeds/tags/1.5.0/disable-embeds.php#L142
     * @since 8.0.0
     *
     * @param array $data The response data.
     * @return array|false Response data or false if in a REST API context.
     */
    public function disable_embeds_filter_oembed_response_data( $data ) {
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return false;
        }

        return $data;
    }

    /**
     * Removes the 'wpembed' TinyMCE plugin.
     *
     * @link https://plugins.trac.wordpress.org/browser/disable-embeds/tags/1.5.0/disable-embeds.php#L74
     * @since 8.0.0
     *
     * @param array $plugins List of TinyMCE plugins.
     * @return array The modified list.
     */
    public function disable_embeds_tiny_mce_plugin( $plugins ) {
        return array_diff( $plugins, array( 'wpembed' ) );
    }

    /**
     * Remove all rewrite rules related to embeds.
     *
     * @link https://plugins.trac.wordpress.org/browser/disable-embeds/tags/1.5.0/disable-embeds.php#L86
     * @since 8.0.0
     *
     * @param array $rules WordPress rewrite rules.
     * @return array Rewrite rules without embeds rules.
     */
    public function disable_embeds_rewrites( $rules ) {
        foreach ( $rules as $rule => $rewrite ) {
            if ( false !== strpos( $rewrite, 'embed=true' ) ) {
                unset( $rules[ $rule ] );
            }
        }

        return $rules;
    }

    /**
     * Enqueues JavaScript for the block editor.
     * 
     * @link https://plugins.trac.wordpress.org/browser/disable-embeds/tags/1.5.0/disable-embeds.php#L157
     * @since 8.0.0
     *
     * This is used to unregister the `core-embed/wordpress` block type.
     */
    public function disable_embeds_enqueue_block_editor_assets() {
        $asset_file = ASENHA_PATH . 'includes/disable-embeds/build/index.asset.php';

        $asset = is_readable( $asset_file ) ? require $asset_file : [];

        $asset['dependencies'] = isset( $asset['dependencies'] ) ? $asset['dependencies'] : [];
        $asset['version'] = isset( $asset['version'] ) ? $asset['version'] : '';

        wp_enqueue_script(
            'disable-embeds',
            plugins_url( 'includes/disable-embeds/build/index.js', __DIR__ ),
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }

    /**
     * Removes wp-embed dependency of core packages.
     *
     * @link https://plugins.trac.wordpress.org/browser/disable-embeds/tags/1.5.0/disable-embeds.php#L180
     * @since 8.0.0
     *
     * @param WP_Scripts $scripts WP_Scripts instance, passed by reference.
     */
    public function disable_embeds_remove_script_dependencies( $scripts ) {
        if ( ! empty( $scripts->registered['wp-edit-post'] ) ) {
            $scripts->registered['wp-edit-post']->deps = array_diff(
                $scripts->registered['wp-edit-post']->deps,
                array( 'wp-embed' )
            );
        }
    }
}
<?php

namespace ASENHA\Classes;

/**
 * Class for Open All External Links in New Tab module
 *
 * @since 6.9.5
 */
class Open_External_Links_In_New_Tab {
    /**
     * Parse links in content to add target="_blank" rel="noopener noreferrer nofollow" attributes
     *
     * @since 4.9.0
     * @param string $content HTML content after the_content.
     * @return string
     */
    public function add_target_and_rel_atts_to_content_links( $content ) {
        if ( empty( $content ) ) {
            return $content;
        }
        $exclude_new_tab_rules = array();
        $exclude_nofollow_rules = array();
        // regex pattern for "a href"
        $regexp = "<a\\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>";
        if ( !preg_match_all(
            "/{$regexp}/siU",
            $content,
            $matches,
            PREG_SET_ORDER
        ) ) {
            return $content;
        }
        foreach ( $matches as $match ) {
            $original_tag = $match[0];
            // e.g. <a title="Link Title" href="http://www.example.com/sit-quaerat">
            $tag = $match[0];
            // Same value as $original_tag but for further processing
            $url = $match[2];
            // e.g. http://www.example.com/sit-quaerat
            if ( false !== strpos( $url, get_site_url() ) ) {
                // Internal link. Do nothing.
                continue;
            }
            if ( false === strpos( $url, 'http' ) ) {
                // Relative link to internal URL. Do nothing.
                continue;
            }
            // External link. Let's do something.
            $omit_new_tab = !empty( $exclude_new_tab_rules ) && self::url_host_matches_any_rule( $url, $exclude_new_tab_rules );
            $omit_nofollow = !empty( $exclude_nofollow_rules ) && self::url_host_matches_any_rule( $url, $exclude_nofollow_rules );
            if ( false === $omit_new_tab ) {
                // Regex pattern for target="_blank|parent|self|top"
                $pattern = '/target\\s*=\\s*"\\s*_(blank|parent|self|top)\\s*"/';
                if ( 0 === preg_match( $pattern, $tag ) ) {
                    // Replace closing > with ' target="_blank">'.
                    $tag = substr_replace( $tag, ' target="_blank">', -1 );
                }
            }
            // If there's no 'rel' attribute in $tag, add rel.
            $pattern = '/rel\\s*=\\s*\\"[a-zA-Z0-9_\\s]*\\"/';
            if ( 0 === preg_match( $pattern, $tag ) ) {
                if ( false === $omit_nofollow ) {
                    $tag = substr_replace( $tag, ' rel="noopener noreferrer nofollow">', -1 );
                } else {
                    $tag = substr_replace( $tag, ' rel="noopener noreferrer">', -1 );
                }
            } elseif ( false === $omit_nofollow ) {
                // replace rel="noopener" with rel="noopener noreferrer nofollow".
                if ( false !== strpos( $tag, 'noopener' ) && false === strpos( $tag, 'noreferrer' ) && false === strpos( $tag, 'nofollow' ) ) {
                    $tag = str_replace( 'noopener', 'noopener noreferrer nofollow', $tag );
                }
            } else {
                // Extend existing rel with noreferrer when nofollow must not be added.
                if ( false !== strpos( $tag, 'noopener' ) && false === strpos( $tag, 'noreferrer' ) && false === strpos( $tag, 'nofollow' ) ) {
                    $tag = str_replace( 'noopener', 'noopener noreferrer', $tag );
                }
            }
            // Replace original a href tag with one containing target and rel attributes above.
            $content = str_replace( $original_tag, $tag, $content );
        }
        return $content;
    }

    /**
     * Normalize textarea lines to lowercase trimmed host suffix rules (Pro caller only).
     * Option strings are sanitized with sanitize_textarea_field(); keep newline separation here.
     *
     * @param string $textarea Saved option string.
     * @return string[] Non-empty lowercase rules.
     */
    private static function parse_domain_rules_from_textarea( $textarea ) {
        if ( !is_string( $textarea ) || '' === $textarea ) {
            return array();
        }
        $lines = preg_split( '/\\r\\n|\\r|\\n/', $textarea );
        $rules = array();
        foreach ( $lines as $line ) {
            $line = strtolower( trim( $line ) );
            if ( '' !== $line ) {
                $rules[] = $line;
            }
        }
        return $rules;
    }

    /**
     * Whether a URL's host matches any suffix rule (exact host or subdomain of rule).
     * Administrators should avoid excessively short suffix rules (e.g. TLD-only); matching is naive suffix-based.
     *
     * @param string             $url   Full absolute URL including scheme.
     * @param array<int, string> $rules Lowercased rules from textarea.
     * @return bool
     */
    private static function url_host_matches_any_rule( $url, array $rules ) {
        if ( '' === $url || empty( $rules ) ) {
            return false;
        }
        $hostname = wp_parse_url( $url, PHP_URL_HOST );
        if ( empty( $hostname ) || !is_string( $hostname ) ) {
            return false;
        }
        $hostname_lc = strtolower( $hostname );
        foreach ( $rules as $rule ) {
            if ( self::hostname_matches_rule_lowercase( $hostname_lc, $rule ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Core host vs rule comparison (both arguments lowercased).
     *
     * @param string $hostname_lc Parsed link host from wp_parse_url, lowercased.
     * @param string $rule_lc     Trimmed textarea line, lowercased.
     * @return bool
     */
    private static function hostname_matches_rule_lowercase( $hostname_lc, $rule_lc ) {
        if ( '' === $hostname_lc || '' === $rule_lc ) {
            return false;
        }
        if ( $hostname_lc === $rule_lc ) {
            return true;
        }
        $suffix = '.' . $rule_lc;
        if ( strlen( $hostname_lc ) <= strlen( $rule_lc ) ) {
            return false;
        }
        return substr( $hostname_lc, -strlen( $suffix ) ) === $suffix;
    }

}

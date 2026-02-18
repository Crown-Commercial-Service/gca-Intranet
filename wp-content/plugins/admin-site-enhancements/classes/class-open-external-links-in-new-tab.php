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
     */
    public function add_target_and_rel_atts_to_content_links( $content ) {
        if ( ! empty( $content ) ) {

            // regex pattern for "a href"
            $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>";

            if ( preg_match_all( "/$regexp/siU", $content, $matches, PREG_SET_ORDER ) ) {

                // $matches might contain parts of $content that has links (a href)
                preg_match_all( "/$regexp/siU", $content, $matches, PREG_SET_ORDER );
                
                if ( is_array( $matches ) ) {                   
                    $i = 0;

                    foreach ( $matches as $match ) {

                        $original_tag = $match[0]; // e.g. <a title="Link Title" href="http://www.example.com/sit-quaerat">
                        $tag = $match[0]; // Same value as $original_tag but for further processing
                        $url = $match[2]; // e.g. http://www.example.com/sit-quaerat
                        
                        if ( false !== strpos( $url, get_site_url() ) ) {
                            // Internal link. Do nothing.
                        } elseif ( false === strpos( $url, 'http' ) ) {
                            // Relative link to internal URL. Do nothing.
                        } else {
                            // External link. Let's do something.
                            // Regex pattern for target="_blank|parent|self|top"
                            $pattern = '/target\s*=\s*"\s*_(blank|parent|self|top)\s*"/';
                            // If there's no 'target="_blank|parent|self|top"' in $tag, add target="blank"
                            if ( 0 === preg_match( $pattern, $tag ) ) {
                                // Replace closing > with ' target="_blank">'
                                $tag = substr_replace( $tag, ' target="_blank">', -1 );
                            }                           

                            // If there's no 'rel' attribute in $tag, add rel="noopener noreferrer nofollow"
                            $pattern = '/rel\s*=\s*\"[a-zA-Z0-9_\s]*\"/';
                            if ( 0 === preg_match( $pattern, $tag ) ) {
                                // Replace closing > with ' rel="noopener noreferrer nofollow">'
                                $tag = substr_replace( $tag, ' rel="noopener noreferrer nofollow">', -1 );
                            } else {
                                // replace rel="noopener" with rel="noopener noreferrer nofollow"
                                if ( false !== strpos( $tag, 'noopener' ) 
                                    && false === strpos( $tag, 'noreferrer' ) 
                                    && false === strpos( $tag, 'nofollow' ) 
                                    ) {
                                    $tag = str_replace( 'noopener', 'noopener noreferrer nofollow', $tag );
                                }
                            }
                            
                            // Replace original a href tag with one containing target and rel attributes above
                            $content = str_replace( $original_tag, $tag, $content );
                        }
                        $i++;
                    }
                }
            }
        }

        return $content;
    }
        
}
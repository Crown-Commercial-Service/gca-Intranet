<?php

namespace ASENHA\Classes;

/**
 * Class for Disable XML-RPC module
 *
 * @since 6.9.5
 */
class Disable_XML_RPC {

    /**
     * Remove XML RPC link in head
     * 
     * @since 6.2.2
     */
    public function remove_xmlrpc_link() {
        remove_action('wp_head', 'rsd_link');
    }
    
    /**
     * Remove XML-RPC methods
     * 
     * @link https://plugins.trac.wordpress.org/browser/stop-xml-rpc-attacks/trunk/stop-xml-rpc-attacks.php
     * @since 7.6.9
     */
    public function remove_xmlrpc_methods( $methods ) {
        unset($methods['system.multicall']);
        unset($methods['system.listMethods']);
        unset($methods['system.getCapabilities']);
        unset($methods['pingback.extensions.getPingbacks']);
        unset($methods['pingback.ping']);
        return $methods;        
    }

    /**
     * Disable the XML-RPC component
     *
     * @since 2.2.0
     */
    public function maybe_disable_xmlrpc( $data ) {

        // http_response_code(403);
        header('HTTP/1.1 403 Forbidden');
        exit('You don\'t have permission to access this file.');

    }
    
    /**
     * Hide xmlrpc.php in HTTP response headers
     * 
     * @link https://wordpress.stackexchange.com/a/219185
     */
    public function hide_xmlrpc_in_http_response_headers( $headers ) {
        unset( $headers['X-Pingback'] );
        return $headers;
    }
    
}
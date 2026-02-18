<?php

namespace ASENHA\Classes;

/**
 * Class for Change Login URL module
 *
 * @since 6.9.5
 */
class Change_Login_URL {
    /**
     * Redirect to valid login URL when custom login slug is part of the request URL
     *
     * @link https://plugins.trac.wordpress.org/browser/admin-login-url-change/trunk/admin-login-url-change.php#L134
     * @since 1.4.0
     */
    public function redirect_on_custom_login_url() {
        $options = get_option( ASENHA_SLUG_U );
        $custom_login_slug = $options['custom_login_slug'];
        $url_input = sanitize_text_field( $_SERVER['REQUEST_URI'] );
        // Make sure $url_input ends with /
        if ( false !== strpos( $url_input, $custom_login_slug ) ) {
            if ( substr( $url_input, -1 ) != '/' ) {
                $url_input = $url_input . '/';
            }
        }
        // If URL contains the custom login slug, redirect to the dashboard
        if ( false !== strpos( $url_input, '/' . $custom_login_slug . '/' ) ) {
            if ( is_user_logged_in() ) {
                if ( array_key_exists( 'redirect_after_login', $options ) && $options['redirect_after_login'] ) {
                    $redirect_after_login = new Redirect_After_Login();
                    $redirect_after_login_type = ( isset( $options['redirect_after_login_type'] ) ? $options['redirect_after_login_type'] : 'single_url' );
                    // Does the user have roles data in array form?
                    $user = wp_get_current_user();
                    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
                        $current_user_roles = $user->roles;
                        // sort by value in descending order, so roles with custom redirection enabled comes first
                    }
                    if ( 'single_url' == $redirect_after_login_type && array_key_exists( 'redirect_after_login_for', $options ) && !empty( $options['redirect_after_login_for'] ) ) {
                        $redirect_after_login_to_slug_raw = ( isset( $options['redirect_after_login_to_slug'] ) ? $options['redirect_after_login_to_slug'] : '' );
                        $relative_path = $redirect_after_login->get_redirect_relative_path( $redirect_after_login_to_slug_raw );
                        $redirect_after_login_for = $options['redirect_after_login_for'];
                        if ( isset( $redirect_after_login_for ) && count( $redirect_after_login_for ) > 0 ) {
                            // Assemble single-dimensional array of roles for which custom URL redirection should happen
                            $roles_for_custom_redirect = array();
                            foreach ( $redirect_after_login_for as $role_slug => $custom_redirect ) {
                                if ( $custom_redirect ) {
                                    $roles_for_custom_redirect[] = $role_slug;
                                }
                            }
                            // Set custom redirect URL for roles set in the settings. Otherwise, leave redirect URL to the default, i.e. admin dashboard.
                            foreach ( $current_user_roles as $role ) {
                                if ( in_array( $role, $roles_for_custom_redirect ) ) {
                                    if ( isset( $_GET['action'] ) ) {
                                        // User Switching plugin
                                        if ( 'switch_to_user' == $_GET['action'] || 'switch_to_olduser' == $_GET['action'] ) {
                                            return;
                                            // This ensures user switching proceeds
                                        } else {
                                            wp_safe_redirect( home_url( $relative_path ) );
                                            exit;
                                        }
                                    } else {
                                        // Redirect to custom redirect slug
                                        wp_safe_redirect( home_url( $relative_path ) );
                                        exit;
                                    }
                                } else {
                                    if ( isset( $_GET['action'] ) ) {
                                        // User Switching plugin
                                        if ( 'switch_to_user' == $_GET['action'] || 'switch_to_olduser' == $_GET['action'] ) {
                                            return;
                                            // This ensures user switching proceeds
                                        } else {
                                            // Redirect to dashboard
                                            wp_safe_redirect( get_admin_url() );
                                            exit;
                                        }
                                    } else {
                                        // Redirect to dashboard
                                        wp_safe_redirect( get_admin_url() );
                                        exit;
                                    }
                                }
                            }
                        } else {
                            if ( isset( $_GET['action'] ) && ('switch_to_user' == $_GET['action'] || 'switch_to_olduser' == $_GET['action']) ) {
                                return;
                                // This ensures user switching proceeds
                            }
                        }
                    } else {
                        if ( 'separate_urls' == $redirect_after_login_type && array_key_exists( 'redirect_after_login_for_separate_role', $options ) && !empty( $options['redirect_after_login_for_separate_role'] ) ) {
                            // Redirect to dashboard
                            wp_safe_redirect( get_admin_url() );
                        } else {
                            // Redirect to dashboard
                            wp_safe_redirect( get_admin_url() );
                            exit;
                        }
                    }
                } else {
                    if ( isset( $_GET['action'] ) ) {
                        // User Switching plugin
                        if ( 'switch_to_user' == $_GET['action'] || 'switch_to_olduser' == $_GET['action'] ) {
                            return;
                            // This ensures user switching proceeds
                        } else {
                            // Redirect to dashboard
                            wp_safe_redirect( get_admin_url() );
                            exit;
                        }
                    } else {
                        // Redirect to dashboard
                        wp_safe_redirect( get_admin_url() );
                        exit;
                    }
                }
            } else {
                // Redirect to the login URL with custom login slug in the query parameters
                wp_safe_redirect( site_url( '/wp-login.php?' . $custom_login_slug . '&redirect=false' ) );
                exit;
            }
        }
    }

    /**
     * Prevent redirect to custom login URL when Gravity Forms is active, and non-logged-in user opens a page with ?gf_page URL string
     * 
     * @since 7.8.5
     */
    public function prevent_redirect_to_custom_login_url() {
        $url_input = sanitize_text_field( $_SERVER['REQUEST_URI'] );
        $options = get_option( ASENHA_SLUG_U );
        $custom_login_slug = $options['custom_login_slug'];
        // Make sure $url_input ends with /
        if ( false !== strpos( $url_input, $custom_login_slug ) ) {
            if ( substr( $url_input, -1 ) != '/' ) {
                $url_input = $url_input . '/';
            }
        }
        if ( false === strpos( $url_input, '/' . $custom_login_slug . '/' ) && 'GET' === $_SERVER['REQUEST_METHOD'] && isset( $_GET['gf_page'] ) && !is_user_logged_in() && !wp_doing_ajax() ) {
            wp_safe_redirect( site_url() );
            exit;
        }
    }

    /**
     * Customize login URL returned when calling wp_login_url(). Add the custom login slug.
     * 
     * @since 5.8.0
     */
    public function customize_login_url( $login_url, $redirect, $force_reauth ) {
        $options = get_option( ASENHA_SLUG_U );
        $custom_login_slug = $options['custom_login_slug'];
        $login_url = home_url( '/' . $custom_login_slug . '/' );
        if ( !empty( $redirect ) ) {
            $login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
        }
        if ( $force_reauth ) {
            $login_url = add_query_arg( 'reauth', '1', $login_url );
        }
        return $login_url;
    }

    /**
     * Customize lost password URL. Add the custom login slug.
     * 
     * @since 5.8.0
     */
    public function customize_lost_password_url( $lostpassword_url ) {
        $options = get_option( ASENHA_SLUG_U );
        $custom_login_slug = $options['custom_login_slug'];
        // return home_url( '/wp-login.php?backend&action=lostpassword' );
        return $lostpassword_url . '&' . $custom_login_slug;
    }

    /**
     * Customize registration URL. Add the custom login slug.
     * 
     * @since 6.2.5
     */
    public function customize_register_url( $registration_url ) {
        $options = get_option( ASENHA_SLUG_U );
        $custom_login_slug = $options['custom_login_slug'];
        // return home_url( '/wp-login.php?action=register&custom_login_slug' );
        return $registration_url . '&' . $custom_login_slug;
    }

    /**
     * Redirect to /not_found when login URL does not contain the custom login slug
     * This will redirect /wp-login.php and /wp-admin/ to /not_found/
     *
     * @link https://plugins.trac.wordpress.org/browser/admin-login-url-change/trunk/admin-login-url-change.php#L121
     * @since 1.4.0
     */
    public function redirect_on_default_login_urls() {
        global $interim_login;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }
        if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
            return;
        }
        $options = get_option( ASENHA_SLUG_U );
        $custom_login_slug = $options['custom_login_slug'];
        // e.g. backend
        $custom_login_whitelist_raw = ( isset( $options['custom_login_whitelist'] ) ? explode( PHP_EOL, $options['custom_login_whitelist'] ) : array() );
        $custom_login_whitelist = array();
        if ( !empty( $custom_login_whitelist_raw ) ) {
            foreach ( $custom_login_whitelist_raw as $path ) {
                $custom_login_whitelist[] = trim( $path );
            }
        }
        $url_input = sanitize_text_field( $_SERVER['REQUEST_URI'] );
        // e.g. /wp-admin/index.php?page=page-slug
        $url_input_parts = explode( '/', $url_input );
        $redirect_slug = 'not_found';
        if ( isset( $_POST['log'] ) && !empty( $_POST['log'] ) && isset( $_POST['pwd'] ) && !empty( $_POST['pwd'] ) ) {
            // When logging-in
            $http_referrer = ( isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_url( $_SERVER['HTTP_REFERER'] ) : '' );
            $http_referrer_no_protocol = str_replace( array('https://', 'http://'), '', $http_referrer );
            $http_referrer_parts = explode( '/', $http_referrer_no_protocol );
            $http_user_agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' );
            if ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', get_option( 'active_plugins', array() ) ) && isset( $_POST['pmpro_login_form_used'] ) && '1' == $_POST['pmpro_login_form_used'] ) {
                // Do nothing. i.e. do not redirect to /not_found/
                // This is a login attempt from Paid Membership Pro login form, which may include a modal/pop-up form.
            } elseif ( !empty( $http_referrer ) && false === strpos( $http_referrer, get_site_url() ) ) {
                // The referer URL does not contain the site's URL. This is an attempt to do a login POST from an external URL / illegitimate method. Let's redirect that.
                wp_safe_redirect( home_url( $redirect_slug . '/' ), 302 );
                exit;
            } elseif ( !empty( $http_user_agent ) && preg_match( '/^(curl|wget)/i', $http_user_agent ) ) {
                // The post request is coming from a cURL or Wget request, let's redirect that.
                wp_safe_redirect( home_url( $redirect_slug . '/' ), 302 );
                exit;
            } elseif ( empty( $http_referrer ) ) {
                // The login request does not have HTTP_REFERER info. e.g. coming from cURL but with a user agent set to a browser's.
                // Let's redirect that
                wp_safe_redirect( home_url( $redirect_slug . '/' ), 302 );
                exit;
            } elseif ( !empty( $http_referrer ) && false === strpos( $http_referrer, $custom_login_slug ) ) {
                // The referrer URL does not contain the custom login slug. Could be an attempt to login via cURL POST.
                if ( isset( $http_referrer_parts[1] ) && in_array( $http_referrer_parts[1], $custom_login_whitelist ) ) {
                    // Do nothing. i.e. do not redirect to /not_found/ as this contains a URL keyword that's been exlucded from redirection
                } else {
                    wp_safe_redirect( home_url( $redirect_slug . '/' ), 302 );
                    exit;
                }
            } else {
                // Do nothing. i.e. do not redirect to /not_found/ as this contains a valin login POST request
                // upon successful login, redirection to logged-in view of /wp-admin/ happens.
                // Without this condition, login attempt will redirect to /not_found/
            }
        } elseif ( isset( $_POST['post_password'] ) && !empty( $_POST['post_password'] ) ) {
            // When entering password for a password-protected post/page
            // Do nothing. i.e. do not redirect to /not_found/
        } elseif ( is_user_logged_in() ) {
            // Do nothing user is already logged-in
            // Redirect to /wp-admin/ (Dashboard) when accessing /wp-login.php without any $_POST data
            if ( isset( $url_input_parts[1] ) && 'wp-login.php' == $url_input_parts[1] && empty( $_POST ) ) {
                wp_safe_redirect( admin_url(), 302 );
                exit;
            }
        } elseif ( !is_user_logged_in() ) {
            // WHen trying to access /wp-signup.php without the ?custom_login_slug, redirect to the redriect_slug
            if ( isset( $url_input_parts[1] ) && 'wp-signup.php' == $url_input_parts[1] && false === strpos( $url_input, $custom_login_slug ) ) {
                // Redirect to /not_found/
                wp_safe_redirect( home_url( $redirect_slug . '/' ), 302 );
                exit;
            } elseif ( false !== strpos( $url_input, 'wp-admin/admin-post.php' ) ) {
                // Do nothing. i.e. do not redirect to /not_found/
            } elseif ( isset( $url_input_parts[1] ) && in_array( $url_input_parts[1], array(
                'admin',
                'wp-admin',
                'login',
                'wp-login',
                'wp-login.php',
                'login.php'
            ) ) && (!isset( $url_input_parts[2] ) || isset( $url_input_parts[2] ) && empty( $url_input_parts[2] ) || isset( $url_input_parts[2] ) && false !== strpos( $url_input_parts[2], '.php' )) ) {
                // Redirect to /not_found/ or custom redirect slug
                wp_safe_redirect( home_url( $redirect_slug . '/' ), 302 );
                exit;
            } elseif ( false !== strpos( $url_input, 'wp-login.php' ) ) {
                if ( isset( $_GET['action'] ) && ('logout' == $_GET['action'] || 'rp' == $_GET['action'] || 'resetpass' == $_GET['action']) || isset( $_GET['checkemail'] ) && ('confirm' == $_GET['checkemail'] || 'registered' == $_GET['checkemail']) || isset( $_GET['interim-login'] ) && '1' == $_GET['interim-login'] || 'success' == $interim_login || isset( $_GET['redirect_to'] ) && isset( $_GET['reauth'] ) && false !== strpos( $url_input, 'comment' ) ) {
                    // When we're logging out, inside the reset password flow, inside the registration flow or within the interim login flow
                    // e.g. https://www.example.com/wp-login.php?action=logout&_wpnonce=49bb818269
                    // e.g. https://www.example.com/wp-login.php?action=rp --> reset password
                    // e.g. https://www.example.com/wp-login.php?action=resetpass --> reset password
                    // e.g. https://www.example.com/wp-login.php?checkmail=confirm --> reset password
                    // e.g. https://www.example.com/wp-login.php?checkmail=registered --> register account
                    // e.g. https://www.example.com/wp-login.php?interim-login=1&wp_lang=en_US
                    // e.g. https://www.example.com/wp-admin/comment.php?action=approve&c=14#wpbody-content --> https://www.example.com/wp-login.php?redirect_to=https%3A%2F%2Fwww.example.com%2Fwp-admin%2Fcomment.php%3Faction%3Dapprove%26c%3D14&reauth=1#wpbody-content --> comment approve
                    // Do nothing.. proceed...
                } elseif ( isset( $_GET['action'] ) && ('lostpassword' == $_GET['action'] || 'register' == $_GET['action']) ) {
                    // When resetting password or registering an account
                    if ( isset( $_POST['user_login'] ) ) {
                        // Sending the form to reset password or register an account...
                        // Do nothing.. proceed with password reset or account registration
                    } else {
                        // When landing on the password reset or registration form
                        // ...and custom login slug is not in the URL
                        if ( false === strpos( $url_input, $custom_login_slug ) ) {
                            // Redirect to /not_found/
                            wp_safe_redirect( home_url( $redirect_slug . '/' ), 302 );
                            exit;
                        }
                        // or, custom login slug is in the url
                        // e.g. https://www.example.com/wp-login.php?action=lostpassword&customloginslug
                        // e.g. https://www.example.com/wp-login.php?action=register&customloginslug
                        // Do nothing... allow reset password or registration
                    }
                } elseif ( isset( $_GET['action'] ) && 'validate_2fa' == $_GET['action'] ) {
                    // When performing two-factor authentication
                    // Do nothing. Do not redirect. Allow login.
                } elseif ( false === strpos( $url_input, $custom_login_slug ) ) {
                    // When landing on the login form /wp-login.php
                    // ...and custom login slug is not in the URL
                    // Redirect to /not_found/
                    wp_safe_redirect( home_url( $redirect_slug . '/' ), 302 );
                    exit;
                } elseif ( false !== strpos( $url_input, $custom_login_slug ) ) {
                    // When landing on the login form /wp-login.php
                    // ...and custom login slug is in the URL
                    // e.g. https://www.example.com/wp-login.php?customloginslug&redirect=false
                    // Do nothing. Do not redirect. Allow login.
                } else {
                }
            } else {
            }
        } else {
        }
    }

    /**
     * Redirect to custom login URL on failed login
     *
     * @link https://plugins.trac.wordpress.org/browser/admin-login-url-change/trunk/admin-login-url-change.php#L148
     * @since 1.4.0
     */
    public function redirect_to_custom_login_url_on_login_fail() {
        global $asenha_limit_login;
        $options = get_option( ASENHA_SLUG_U );
        $custom_login_slug = $options['custom_login_slug'];
        if ( isset( $asenha_limit_login ) && is_array( $asenha_limit_login ) && $asenha_limit_login['within_lockout_period'] ) {
            // Do nothing. This prevents redirection loop.
        } else {
            $should_redirect = true;
            // Prevent redirection to wp-login.php if the login process is initiated by a custom login form, e.g. WooCommerce, JetFormBuilder
            // i.e. the POST request will not contain WP login process defaults as follows
            if ( !isset( $_POST['log'] ) && !isset( $_POST['pwd'] ) && !isset( $_POST['wp-submit'] ) && !isset( $_POST['testcookie'] ) ) {
                $should_redirect = false;
            }
            if ( $should_redirect ) {
                // Append 'failed_login=true' so we can output custom error message above the login form
                wp_safe_redirect( site_url( 'wp-login.php?' . $custom_login_slug . '&redirect=false&failed_login=true' ) );
                exit;
            }
        }
    }

    /**
     * Add login error message on top of the login form. 
     * Only shown if there's a failed_login URL parameter, and Limit Login Attempts module is not enabled. 
     * If LLA module is enabled, the same custom login error message is handled there.
     *
     * @since 6.9.1
     */
    public function add_failed_login_message( $message ) {
        global $asenha_limit_login;
        if ( isset( $_REQUEST['failed_login'] ) && $_REQUEST['failed_login'] == 'true' ) {
            if ( is_null( $asenha_limit_login ) ) {
                $message = '<div id="login_error" class="notice notice-error"><b>' . __( 'Error:', 'admin-site-enhancements' ) . '</b> ' . __( 'Invalid username/email or incorrect password.', 'admin-site-enhancements' ) . '</div>';
            }
        }
        return $message;
    }

    /**
     * Redirect to custom login URL on successful logout
     *
     * @link https://plugins.trac.wordpress.org/browser/admin-login-url-change/trunk/admin-login-url-change.php#L148
     * @since 1.4.0
     */
    public function redirect_to_custom_login_url_on_logout_success() {
        $options = get_option( ASENHA_SLUG_U );
        $custom_login_slug = $options['custom_login_slug'];
        // Redirect to the login URL with custom login slug in it
        wp_safe_redirect( home_url( 'wp-login.php?' . $custom_login_slug . '&redirect=false' ) );
        exit;
    }

    /**
     * Customize logout URL by adding the custom login slug to it
     * 
     * @since 7.0.2.3
     */
    public function customize_logout_url( $logout_url, $redirect ) {
        $options = get_option( ASENHA_SLUG_U );
        $custom_login_slug = $options['custom_login_slug'];
        if ( !empty( $redirect ) ) {
            $logout_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $logout_url );
        }
        $logout_url .= '&' . $custom_login_slug;
        return $logout_url;
    }

}

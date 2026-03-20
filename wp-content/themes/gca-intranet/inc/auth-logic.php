<?php
/**
 * GCA INTRANET - STABLE AUTH LOGIC (Reverted & Branded)
 */

// GLOBAL DETECTION
$is_backdoor = (strpos($_SERVER['REQUEST_URI'], 'gcawebadmin') !== false);

// THE CONTEXTUAL LIE (Makes inputs appear for backdoor)
add_filter('option_galogin_premium', function($options) {
    if (strpos($_SERVER['REQUEST_URI'], 'gcawebadmin') !== false && is_array($options)) {
        $options['ga_disablewplogin'] = false;
        $options['ga_hidewplogin'] = false;
    }
    return $options;
}, 999);

// THE GATE-CRASHER (Emergency Auth Bypass)
add_filter('authenticate', function($user, $username, $password) {
    if (strpos($_SERVER['REQUEST_URI'], 'gcawebadmin') === false || $user instanceof WP_User || empty($username)) {
        return $user;
    }
    remove_all_filters('authenticate');
    return wp_authenticate_username_password(null, $username, $password);
}, 1, 3);

// THE ROUTING
add_action('init', function() use ($is_backdoor) {
    if ($is_backdoor) {
        global $action; $action = 'login';
        add_filter('login_body_class', fn($c) => array_merge($c, ['gca-backdoor-page']));
        require_once ABSPATH . 'wp-login.php';
        exit;
    }
});

// INJECT BRANDING (Logo + Badge)
add_filter('login_message', function($message) use ($is_backdoor) {
    if (!$is_backdoor) {
        return '
        <div class="landing-header">
            <span class="gca-logo">
                  <img src="' . get_template_directory_uri() . '/assets/img/Government-Commercial-Agency-black-linear.png" alt="GCA Logo" />
                </span>
            <span class="intranet-badge">Intranet</span>
        </div>';
    }

    return '<p style="border: 1px solid #1d70b8; border-left: 4px solid #1d70b8; padding:15px; background:#fff7f7; border-radius: 10px;">This administrator access route is for emergency use only. Password recovery is not available here. If access is required, retrieve the current credentials via AWS Parameter Store using the agreed platform support process.</p>';
});

// THE STYLES (Your Landing Page CSS)
//@todo use the actual stylesheet. For some reason the lading-page.css in assets/dist is not working on ECS.
//Looks like it's not building it as part of npm, but local works fine
add_action('login_head', function() use ($is_backdoor) { ?>
    <style>
        body.login {
        background-color: #ffffff !important;
        display: flex;
        align-items: baseline;
        justify-content: center;
        min-height: 100vh;
        border-top: 8px solid #568FFB !important;
        font-family: "GDS Transport", arial, sans-serif;
        }
        body.login h1 {
        display: none !important;
        }
        body.login #login {
        width: 100% !important;
        max-width: 350px !important;
        margin: 0 !important;
        }
        body.login #login form {
        background: #ffffff !important;
        border-radius: 10px !important;
        border: none !important;
        padding: 40px 24px;
        box-shadow: none !important;
        }
        body.login #login form .forgetmenot {
        padding-bottom: 10px;
        }
        body.login .message, body.login .notice, body.login .success {
        border: 1px solid #72aee6;
        border-left: 4px solid #72aee6;
        }
        body.login #nav, body.login #backtoblog, body.login .galogin-or {
        display: none !important;
        }
        body.login .landing-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin-bottom: 40px;
        }
        body.login .gca-logo-text {
        font-size: 28px;
        font-weight: bold;
        color: #0b0c0c;
        }
        body.login .intranet-badge {
        background-color: #8BA9FE;
        color: #0b0c0c;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 19px;
        font-weight: 400;
        }
        body.login .gca-logo img {
        width: 350px;
        }
        body.login #loginform p.galogin a {
        display: flex !important;
        align-items: center;
        justify-content: center;
        color: #0b0c0c !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        border: 1px solid #0b0c0c !important;
        border-radius: 8px !important;
        width: 85%;
        margin: 0 auto;
        }
        body.login #loginform p.galogin a::before {
        content: "";
        display: inline-block;
        width: 20px;
        height: 20px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path fill="%23EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="%234285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="%23FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="%2334A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>');
        background-repeat: no-repeat;
        background-size: contain;
        margin-right: 12px;
        }
        body.login .google-apps-header .inner {
        box-shadow: none;
        height: 100%;
        }
        body.login .google-apps-header .inner span {
        font-weight: 600;
        color: #000;
        }
        body.login .google-apps-header .icon {
        display: none !important;
        }
        body.login:not(.gca-backdoor-page) #loginform p:not(.galogin),
        body.login:not(.gca-backdoor-page) #loginform .user-pass-wrap,
        body.login:not(.gca-backdoor-page) #loginform .submit {
        display: none !important;
        }
        body.login.gca-backdoor-page .galogin,
        body.login.gca-backdoor-page .galogin-or {
        display: none !important;
        }
        body.login.gca-backdoor-page form {
        padding: 40px !important;
        max-width: 400px !important;
        }
        body.login.gca-backdoor-page label {
        font-weight: bold;
        }
        body.login.gca-backdoor-page input.input {
        border: 2px solid #0b0c0c !important;
        }
        body.login.gca-backdoor-page #wp-submit {
        background: #1d70b8 !important;
        width: 100%;
        border: none;
        font-weight: bold;
        margin-top: 10px;
        }
        /*# sourceMappingURL=landing-page.css.map */
    </style>
    <?php
});

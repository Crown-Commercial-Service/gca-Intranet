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
add_action('login_head', function() use ($is_backdoor) {
    echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/assets/dist/landing-page.css">';
});

<?php

/**
 * GCA INTRANET - AUTHENTICATION & LANDING PAGE LOGIC
 */

/**
 * THE BACKDOOR ROUTING
 * Tells WordPress that /gcawebadmin is a valid path.
 */
add_action('init', function() {
    if (trim($_SERVER['REQUEST_URI'], '/') === 'gcawebadmin') {
        if (!defined('GCA_IS_BACKDOOR')) define('GCA_IS_BACKDOOR', true);

        global $user_login, $error, $action;
        $action = 'login';

        // 1. Add the custom emergency notice box
        add_filter('login_message', function() {
            return '
            <div class="gca-emergency-notice" style="border-left: 4px solid #1d70b8; padding: 15px; background: #fff; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #0b0c0c;">
                    This administrator access route is for emergency use only. Password recovery is not available here.
                    If access is required, retrieve the current credentials via AWS Parameter Store using the agreed platform support process.
                </p>
            </div>';
        });

        // 2. Hide Google SSO elements and "Lost your password" specifically here
        add_action('login_head', function() {
            echo '
            <style>
                #loginform > p:first-of-type, /* Hides "Sign in with Google" if injected inside form */
                .galogin, .galogin-or, #nav { display: none !important; }
                .login h1 a { background-size: contain !important; width: 100% !important; }
                #login { padding-top: 40px !important; }
            </style>';
        });

        require_once ABSPATH . 'wp-login.php';
        exit;
    }
});

/**
 * THE UI LOGIN HIJACK LOGIC
 */
add_action('login_init', function() {
    // A. HANDLE GOOGLE BUTTON REDIRECT
    if (isset($_GET['gaalogin']) && $_GET['gaalogin'] == '1') {
        $settings = get_option('galogin_premium');
        $id = $settings['ga_clientid'] ?? '';

        if ($id) {
            $auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
                'response_type' => 'code',
                'client_id'     => $id,
                'redirect_uri'  => wp_login_url(),
                'scope'         => 'openid email profile',
                'access_type'   => 'online',
                'prompt'        => 'select_account',
                'state'         => mt_rand() . '|' . ($_REQUEST['redirect_to'] ?? home_url())
            ]);
            wp_redirect($auth_url);
            exit;
        }
    }

    // THE UI SWITCH
    // Hijack the UI ONLY if we are NOT on the /gcawebadmin backdoor
    // and NOT submitting a login form (POST).
    if (!defined('GCA_IS_BACKDOOR') && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_start();
        add_action('login_header', function() {
            ob_end_clean();
            echo gca_get_custom_landing_page_html();
            exit;
        }, -1);
    }
});

// ASSET WHITELIST: Prevent "Force Login" plugins from blocking CSS/Images
add_filter('all_in_one_intranet_skip_login', function($skip) {
    $uri = $_SERVER['REQUEST_URI'];
    if (preg_match('/\.(css|js|png|jpg|jpeg|svg|ico)$/i', $uri) || strpos($uri, 'wp-login.php') !== false) {
        return true;
    }
    return $skip;
});

// THE LANDING PAGE HTML
function gca_get_custom_landing_page_html() {
    $google_url = add_query_arg([
        'gaalogin'    => '1',
        'redirect_to' => $_REQUEST['redirect_to'] ?? home_url()
    ], wp_login_url());

    ob_start(); ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Sign in - Government Commercial Agency</title>
        <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/dist/gca-theme.css">
    </head>
    <body class="landing-page-container">
        <div class="landing-card">
            <div class="landing-header">
                <span class="gca-logo">
                  <img src="<?php echo get_template_directory_uri(); ?>/assets/img/Government-Commercial-Agency-black-linear.png" alt="GCA Logo" />
                </span>
                <span class="intranet-badge">Intranet</span>
            </div>
            <div class="login-section">
                <a href="<?php echo esc_url($google_url); ?>" class="google-signin-button">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="display: block;">
                      <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                      <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
                      <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                      <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                    </svg>
                    <span>Sign in with Google</span>
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php return ob_get_clean();
}

/**
 * GCA INTRANET - AUTHENTICATION & LANDING PAGE LOGIC END
 */

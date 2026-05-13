<?php
/**
 * Plugin Name: Block Executable Uploads
 * Description: Prevents upload of executable and dangerous file types via WordPress.
 *              Must-use plugin — loaded automatically, cannot be deactivated.
 * Version:     1.0.0
 */

// Remove dangerous extensions from the WordPress allowed MIME types list.
add_filter('upload_mimes', function (array $mimes): array {
    $dangerous = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7',
        'sh', 'py', 'pl', 'cgi', 'exe', 'bat', 'cmd', 'ps1',
    ];

    foreach ($dangerous as $ext) {
        unset($mimes[$ext]);
    }

    return $mimes;
});

// Hard-block uploads by extension even when MIME type spoofing is attempted.
add_filter('wp_check_filetype_and_ext', function (array $data, string $file, string $filename): array {
    $dangerous = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7',
        'sh', 'py', 'pl', 'cgi', 'exe', 'bat', 'cmd', 'ps1',
    ];

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (in_array($ext, $dangerous, true)) {
        $data['ext']  = false;
        $data['type'] = false;
    }

    return $data;
}, 10, 3);

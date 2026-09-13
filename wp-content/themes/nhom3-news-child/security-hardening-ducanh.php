<?php
/**
 * Module Bảo mật & Gia cố hệ thống - Tuần 3
 * Tác giả: Đức Anh (Security Lead)
 * Chức năng: Chặn truy cập trực tiếp file nhạy cảm và vô hiệu hóa trình sửa code (Theme/Plugin Editor)
 */

// 1. Chặn thực thi trực tiếp file PHP nếu không qua WordPress Core
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// 2. Chặn truy cập các tệp tin nhạy cảm ở cấp độ ứng dụng WordPress (PHP Hook 403)
add_action('init', 'ducanh_block_sensitive_files_access');
function ducanh_block_sensitive_files_access() {
    if (isset($_SERVER['REQUEST_URI'])) {
        $blocked_files = [
            'readme.html',
            'license.txt',
            'wp-config.php',
            'debug.log',
            'xmlrpc.php'
        ];
        
        $request_uri = strtolower($_SERVER['REQUEST_URI']);
        foreach ($blocked_files as $file) {
            if (strpos($request_uri, $file) !== false) {
                status_header(403);
                wp_die(
                    '<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập tệp tin hệ thống này (Bảo vệ bởi Module Security - Đức Anh).</p>',
                    'Truy cập bị từ chối',
                    ['response' => 403]
                );
            }
        }
    }
}

// 3. Tự động ghi rule bảo vệ vào file .htaccess nếu chạy máy chủ Apache
add_action('admin_init', 'ducanh_write_htaccess_protection');
function ducanh_write_htaccess_protection() {
    $htaccess_path = ABSPATH . '.htaccess';

    $rules = "\n# BEGIN Block Sensitive Files - Duc Anh Security\n"
           . "<FilesMatch \"^(wp-config\\.php|\\.htaccess|\\.htpasswd|readme\\.html|license\\.txt|debug\\.log)\">\n"
           . "    Order Allow,Deny\n"
           . "    Deny from all\n"
           . "</FilesMatch>\n"
           . "# END Block Sensitive Files - Duc Anh Security\n";

    if (file_exists($htaccess_path) && is_writable($htaccess_path)) {
        $current_content = file_get_contents($htaccess_path);
        if (strpos($current_content, '# BEGIN Block Sensitive Files - Duc Anh Security') === false) {
            file_put_contents($htaccess_path, $rules, FILE_APPEND);
        }
    }
}

// 4. Vô hiệu hóa và gỡ hoàn toàn menu Theme/Plugin File Editor khỏi trang Dashboard
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

add_action('admin_menu', 'ducanh_remove_file_editors_menu', 999);
function ducanh_remove_file_editors_menu() {
    remove_submenu_page('themes.php', 'theme-editor.php');
    remove_submenu_page('plugins.php', 'plugin-editor.php');
}
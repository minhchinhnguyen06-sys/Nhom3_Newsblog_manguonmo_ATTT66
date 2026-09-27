<?php
/**
 * File functions.php tổng hợp của Nhóm 3 - Child Theme
 */

// ==========================================
// 1. Code chức năng của Bạn 1 
// ==========================================
function nhom3_secure_shortcode($atts) { 
    $clean_text = sanitize_text_field( isset($atts['text']) ? $atts['text'] : 'Chao mung den voi Nhom 3' );
    return $clean_text;
} 
add_shortcode('nhom3_chao', 'nhom3_secure_shortcode');
// Bạn 1 hoặc Bạn 4 thêm đoạn này vào functions.php để nhúng JS
function them_js_dong_thongbao() {
    ?>
    <script>
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('thongbao-dong')) {
            e.target.closest('.thongbao').style.display = 'none';
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'them_js_dong_thongbao');


// ==========================================
// 2. Code chức năng của Bạn 2 
// ==========================================
function xu_ly_dau_vao_thongbao($atts, $content) {
    
    // Giá trị mặc định nếu người dùng không truyền type
    $atts = shortcode_atts([
        'type' => 'info',
    ], $atts);

    // ----- VALIDATION: chỉ cho phép 3 loại type hợp lệ -----
    $danh_sach_type_hop_le = ['info', 'canhbao', 'thanhcong'];
    $type = in_array($atts['type'], $danh_sach_type_hop_le) ? $atts['type'] : 'info';

    // ----- SANITIZATION: làm sạch dữ liệu -----
    $type = sanitize_text_field($type);          // loại bỏ thẻ HTML/script lẫn trong type
    $noi_dung = sanitize_text_field($content);    // loại bỏ thẻ HTML/script trong nội dung

    // Trả về mảng dữ liệu đã sạch cho Bạn 3 dùng
    return [
        'type' => $type,
        'noi_dung' => $noi_dung,
    ];
}

// ==========================================
// 3. Code chức năng của Bạn 3
// ==========================================
function hien_thi_thongbao($atts, $content = null) {

    // ----- BƯỚC 1: Nhận dữ liệu đã lọc từ hàm của Bạn 2 -----
    $du_lieu_sach = xu_ly_dau_vao_thongbao($atts, $content);
    $type     = $du_lieu_sach['type'];
    $noi_dung = $du_lieu_sach['noi_dung'];

    // ----- BƯỚC 2: Escaping - chống XSS khi xuất ra màn hình -----
    $type_an_toan = esc_attr($type);
    $noi_dung_an_toan = esc_html($noi_dung);

    // ----- BƯỚC 3: Xuất HTML ra -----
    $html  = '<div class="thongbao thongbao-' . $type_an_toan . '">';
    $html .= '<span class="thongbao-icon"></span>';
    $html .= '<span class="thongbao-noidung">' . $noi_dung_an_toan . '</span>';
    $html .= '<button class="thongbao-dong">&times;</button>';
    $html .= '</div>';

    return $html;
}
add_shortcode('thongbao', 'hien_thi_thongbao');


// ==========================================
// 4. Code chức năng của Bạn 5 
// ==========================================
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

?>

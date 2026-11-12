<?php
/**
 * Nhom 3 security-conscious Child Theme enhancements.
 * WordPress Core remains responsible for authentication and passwords.
 * The Nhom 3 Design Polish plugin is responsible for public layouts.
 */
if (!defined('ABSPATH')) { exit; }

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('nh3c-child', get_stylesheet_uri(), [], '3.1.0');
}, 30);

/**
 * NHOM 3 - merged contributions, preserving original function names.
 * Ban 1: shortcode welcome + JavaScript dismiss notice.
 * Ban 2: sanitize/validate notice input.
 * Ban 3: escape notice output.
 * Ban 5: hardening functions are further below.
 */
function nhom3_secure_shortcode($atts = []) {
    $atts = shortcode_atts(['text' => 'Chào mừng đến với Nhóm 3'], is_array($atts) ? $atts : [], 'nhom3_chao');
    $clean_text = sanitize_text_field((string) $atts['text']);
    return esc_html($clean_text);
}
add_shortcode('nhom3_chao', 'nhom3_secure_shortcode');

function them_js_dong_thongbao() {
    // Original close-button behavior, with a guard for missing containers.
    ?>
    <script>
    document.addEventListener('click', function (e) {
        var target = e.target.closest ? e.target.closest('.thongbao-dong') : null;
        if (!target) return;
        var notice = target.closest('.thongbao');
        if (notice) notice.remove();
    });
    </script>
    <?php
}
add_action('wp_footer', 'them_js_dong_thongbao', 50);

function xu_ly_dau_vao_thongbao($atts, $content) {
    $atts = shortcode_atts(['type' => 'info'], is_array($atts) ? $atts : [], 'thongbao');
    $type = sanitize_key((string) $atts['type']);
    $danh_sach_type_hop_le = ['info', 'canhbao', 'thanhcong'];
    if (!in_array($type, $danh_sach_type_hop_le, true)) $type = 'info';
    $noi_dung = sanitize_text_field((string) ($content ?? ''));
    return ['type' => $type, 'noi_dung' => $noi_dung];
}

function hien_thi_thongbao($atts = [], $content = null) {
    $du_lieu_sach = xu_ly_dau_vao_thongbao($atts, $content);
    $type_an_toan = esc_attr($du_lieu_sach['type']);
    $noi_dung_an_toan = esc_html($du_lieu_sach['noi_dung']);
    $html  = '<div class="thongbao thongbao-' . $type_an_toan . '" role="status">';
    $html .= '<span class="thongbao-icon" aria-hidden="true"></span>';
    $html .= '<span class="thongbao-noidung">' . $noi_dung_an_toan . '</span>';
    $html .= '<button type="button" class="thongbao-dong" aria-label="Đóng thông báo">&times;</button>';
    $html .= '</div>';
    return $html;
}
add_shortcode('thongbao', 'hien_thi_thongbao');

/** Restrict built-in theme/plugin editors; not a substitute for server hardening. */
if (!defined('DISALLOW_FILE_EDIT')) { define('DISALLOW_FILE_EDIT', true); }

/** Disable XML-RPC methods if the project does not use XML-RPC integrations.
 * Web server should ALSO deny /xmlrpc.php at HTTP level after verifying integrations.
 */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_headers', function ($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});

/**
 * Ban 5 - security implementation. WordPress PHP cannot enforce access to static
 * files served directly by nginx. Actual server-level deny rules are supplied in
 * NGINX-SECURITY-EXAMPLE.conf and must be tested/applied manually by an admin.
 */
function ducanh_block_sensitive_files_access() {
    // Only inspect WordPress-routed requests, never claim this secures static files.
    $uri = isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI'])
        ? wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '';
    if (!is_string($uri)) return;
    $path = strtolower(trim($uri, '/'));
    if ($path === 'xmlrpc.php') {
        status_header(403);
        wp_die(esc_html__('Truy cập XML-RPC bị từ chối.', 'nhom3-news-child'), '', ['response' => 403]);
    }
}
// xmlrpc_enabled filter below is the primary WordPress-level method guard.
// Intentionally NOT registered on init: XML-RPC may handle early or outside that hook.

function ducanh_write_htaccess_protection() {
    // No automatic file writes. Returns a documented example for Apache only.
    return "# Apache 2.4 example - apply manually only on Apache\\n"
        . "<FilesMatch \\\"^(wp-config\\\\.php|\\\\.htaccess|\\\\.htpasswd|readme\\\\.html|license\\\\.txt|debug\\\\.log)$\\\">\\n"
        . "Require all denied\\n</FilesMatch>\\n";
}

function ducanh_remove_file_editors_menu() {
    if (!is_admin()) return;
    remove_submenu_page('themes.php', 'theme-editor.php');
    remove_submenu_page('plugins.php', 'plugin-editor.php');
}
add_action('admin_menu', 'ducanh_remove_file_editors_menu', 999);

/** A simple front-end submission requiring existing WordPress login. */
function nh3c_submission_form() {
    if (!is_user_logged_in()) {
        return '<p>' . esc_html__('Bạn cần đăng nhập trước khi gửi bài.', 'nhom3-news-child') . ' <a href="' . esc_url(wp_login_url(get_permalink())) . '">' . esc_html__('Đăng nhập', 'nhom3-news-child') . '</a></p>';
    }
    if (!current_user_can('edit_posts')) {
        return '<p>' . esc_html__('Tài khoản chưa có quyền gửi bài. Vui lòng liên hệ quản trị viên.', 'nhom3-news-child') . '</p>';
    }
    $result = isset($_GET['nh3c_status']) && is_string($_GET['nh3c_status']) ? sanitize_key(wp_unslash($_GET['nh3c_status'])) : '';
    ob_start();
    if ($result === 'sent') echo '<div class="nh3c-notice nh3c-notice--success" role="status">' . esc_html__('Đã gửi bài, đang chờ quản trị viên duyệt.', 'nhom3-news-child') . '</div>';
    if ($result === 'error') echo '<div class="nh3c-notice nh3c-notice--warning" role="alert">' . esc_html__('Bài viết không hợp lệ hoặc không thể lưu. Vui lòng kiểm tra lại.', 'nhom3-news-child') . '</div>';
    ?>
    <form class="nh3c-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <h2><?php echo esc_html__('Gửi bài viết', 'nhom3-news-child'); ?></h2>
        <p><?php echo esc_html__('Bài viết của bạn sẽ được lưu chờ duyệt, không công khai ngay.', 'nhom3-news-child'); ?></p>
        <input type="hidden" name="action" value="nh3c_submit_article">
        <?php wp_nonce_field('nh3c_submit_article', 'nh3c_nonce'); ?>
        <label><?php echo esc_html__('Tiêu đề', 'nhom3-news-child'); ?><input type="text" name="nh3c_title" maxlength="180" required></label>
        <label><?php echo esc_html__('Chuyên mục', 'nhom3-news-child'); ?>
        <?php wp_dropdown_categories(['taxonomy' => 'category', 'name' => 'nh3c_category', 'id' => 'nh3c_category', 'show_option_none' => '-- Chọn chuyên mục --', 'option_none_value' => 0, 'hide_empty' => false, 'required' => true]); ?>
        </label>
        <label><?php echo esc_html__('Nội dung', 'nhom3-news-child'); ?><textarea name="nh3c_content" minlength="50" maxlength="40000" rows="12" required></textarea></label>
        <button type="submit"><?php echo esc_html__('Gửi để xét duyệt', 'nhom3-news-child'); ?></button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('nhom3_submit_post', 'nh3c_submission_form');

/** Nonce + capability + input validation + bounded payload + pending status. */
add_action('admin_post_nh3c_submit_article', function () {
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        wp_die(esc_html__('Không có quyền gửi bài.', 'nhom3-news-child'), '', ['response' => 403]);
    }
    check_admin_referer('nh3c_submit_article', 'nh3c_nonce');

    $title = isset($_POST['nh3c_title']) && is_string($_POST['nh3c_title']) ? sanitize_text_field(wp_unslash($_POST['nh3c_title'])) : '';
    $content = isset($_POST['nh3c_content']) && is_string($_POST['nh3c_content']) ? wp_kses_post(wp_unslash($_POST['nh3c_content'])) : '';
    $cat = isset($_POST['nh3c_category']) ? absint($_POST['nh3c_category']) : 0;
    $valid = $title !== '' && mb_strlen($title) <= 180 && mb_strlen(wp_strip_all_tags($content)) >= 50 && mb_strlen($content) <= 40000 && $cat && term_exists($cat, 'category');

    $referer = wp_get_referer();
    $return_url = $referer ? wp_validate_redirect($referer, home_url('/')) : home_url('/');
    $return_url = remove_query_arg('nh3c_status', $return_url);
    if (!$valid) { wp_safe_redirect(add_query_arg('nh3c_status', 'error', $return_url)); exit; }

    // User-level short submission cooldown to discourage accidental duplicates/spam.
    $lock_key = 'nh3c_submit_' . get_current_user_id();
    if (get_transient($lock_key)) {
        wp_safe_redirect(add_query_arg('nh3c_status', 'error', $return_url)); exit;
    }
    set_transient($lock_key, 1, 20);
    $post_id = wp_insert_post([
        'post_type' => 'post',
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => 'pending',
        'post_author' => get_current_user_id(),
        'post_category' => [(int) $cat],
        'comment_status' => 'closed',
    ], true);
    if (is_wp_error($post_id)) delete_transient($lock_key);
    wp_safe_redirect(add_query_arg('nh3c_status', is_wp_error($post_id) ? 'error' : 'sent', $return_url));
    exit;
});

/** Backward-compatible shortcodes. Design Polish plugin owns the homepage display. */
add_shortcode('nhom3_home', function () {
    if (function_exists('nh3dp_t')) return ''; // The plugin's front-page template already renders the homepage.
    $query = new WP_Query(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 9]);
    ob_start();
    echo '<section class="nh3c-form"><h2>' . esc_html__('Bài viết mới nhất', 'nhom3-news-child') . '</h2>';
    while ($query->have_posts()) {
        $query->the_post();
        echo '<p><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></p>';
    }
    wp_reset_postdata();
    echo '</section>';
    return ob_get_clean();
});

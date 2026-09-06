<?php 
function nhom3_secure_shortcode($atts) { 
    $clean_text = sanitize_text_field( isset($atts['text']) ? $atts['text'] : 'Chao mung den voi Nhom 3' ); 
} 
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

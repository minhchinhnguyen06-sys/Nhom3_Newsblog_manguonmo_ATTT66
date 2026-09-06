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

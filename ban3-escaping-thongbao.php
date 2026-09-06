<?php

// PHẦN BẠN 3 (Trang): Hiển thị Shortcode + Escaping chống XSS

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
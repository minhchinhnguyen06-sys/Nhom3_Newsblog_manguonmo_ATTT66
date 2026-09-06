<?php 
function nhom3_secure_shortcode($atts) { 
    $clean_text = sanitize_text_field( isset($atts['text']) ? $atts['text'] : 'Chao mung den voi Nhom 3' ); 
} 

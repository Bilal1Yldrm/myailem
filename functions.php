<?php

// Güvenlik için doğrudan erişimi engelle
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Stil dosyası ekleme
function myailem_aileler_enqueue_styles() {
    wp_enqueue_style( 'myailem-aileler-style', plugin_dir_url( __FILE__ ) . '../css/myailem-aileler.css' );
    // Bootstrap CSS'i ekle
    wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' );

    // Bootstrap Icons CSS'i ekle
    wp_enqueue_style( 'bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css' );
}

// JavaScript dosyası ekleme
function myailem_aileler_enqueue_scripts() {
    wp_enqueue_script( 'myailem-aileler-script', plugin_dir_url( __FILE__ ) . '../js/myailem-aileler.js', array( 'jquery', 'bootstrap' ), null, true );

    // WordPress'e JavaScript değişkenlerini aktar
    wp_localize_script( 'myailem-aileler-script', 'mAilemAileler', array(
        'searchPersonUrl' => esc_url( home_url( '/search_person/' ) ),
        'searchPersonNonce' => wp_create_nonce( 'search_person' ),
    ) );

    // Bootstrap JavaScript'i ekle (jQuery'ye bağımlı)
    wp_enqueue_script( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), null, true );
}

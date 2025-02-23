<?php
/**
 * Plugin Name: Myailem Aileler Modülü
 * Description: Aileler ile ilgili işlemleri yönetir.
 */

// Güvenlik için doğrudan erişimi engelle
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include dosyalarını dahil et// bu aile bilgilerini içeriyor// alt dizinde
require_once plugin_dir_path( __FILE__ ) . 'include/shortcodes.php';
require_once plugin_dir_path( __FILE__ ) . 'include/functions.php';  

/**
 * Menüye "Aileler" bağlantısı ekler.
 *
 * @param string $items Menü öğelerinin HTML'i.
 * @param object $args  Menü argümanları.
 * @return string Güncellenmiş menü öğelerinin HTML'i.
 */
function myailem_aileler_menu_item( $items, $args ) {
    // Aileler sayfasının ID'sini alın
    $aileler_sayfasi_id = get_option( 'myailem_aileler_sayfasi_id' );

    // Eğer Aileler sayfası varsa ve ID geçerliyse
    if ( $aileler_sayfasi_id ) {
        // Sayfa URL'sini alın
        $aileler_sayfasi_url = get_permalink( $aileler_sayfasi_id );

        // Eğer URL varsa
        if ( $aileler_sayfasi_url ) {
            // Menü öğesini oluşturun
            $menu_item = '<li class="fs-5"><a href="' . esc_url( $aileler_sayfasi_url ) . '"><i class="fas fa-users"></i> ' . esc_html__( 'Aileler', 'myailem' ) . '</a></li>';
            // Menü öğesini mevcut öğelerin sonuna ekleyin
            $items .= $menu_item;
        }
    }
    return $items;
}
// Kısa kodu kaydet
add_shortcode( 'myailem_aileler', 'myailem_aileler_shortcode' );

//Stilleri ekle
add_action( 'wp_enqueue_scripts', 'myailem_aileler_enqueue_styles' );

//JavaScript'i ekle
add_action( 'wp_enqueue_scripts', 'myailem_aileler_enqueue_scripts' );

//Menuye Aileler'i ekle
add_filter( 'wp_nav_menu_items', 'myailem_aileler_menu_item', 10, 2 );
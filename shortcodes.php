<?php

// Güvenlik için doğrudan erişimi engelle
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Kısa kod tanımlama (shortcode)
function myailem_aileler_shortcode() {
    ob_start(); // Çıktı tamponlamasını başlat

    // WordPress veritabanı global değişkenini kullan
    global $wpdb;

    // Tablo adını tanımla
    $table_name = $wpdb->prefix . 'aile_bilgileri';

    // Arama sorgusu
    $search_query = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
    $comprehensive = isset( $_GET['comprehensive'] ) ? filter_var( $_GET['comprehensive'], FILTER_VALIDATE_BOOLEAN ) : false;

    // Sayfalama
    $current_page = max( 1, get_query_var('paged') ); // 'paged' parametresi
    $per_page = 10; // Sayfa başına aile sayısı
    $offset = ( $current_page - 1 ) * $per_page;

    // SQL sorgusu oluştur
    $sql = "SELECT * FROM $table_name";

    // Arama sorgusu ekle
    if ( ! empty( $search_query ) ) {
        $search_term = esc_sql( $search_query );
        if ( $comprehensive ) {
            $sql .= " WHERE aile_adi LIKE '%$search_term%' OR yeri LIKE '%$search_term%' OR notu LIKE '%$search_term%'";
        } else {
            $sql .= " WHERE aile_adi LIKE '%$search_term%'";
        }
    }

    // Sıralama ekle
    $sql .= " ORDER BY aile_adi ASC";

    // Toplam aile sayısını bul
    $total_sql = "SELECT COUNT(*) FROM $table_name";
    if ( ! empty( $search_query ) ) {
        $search_term = esc_sql( $search_query );
        if ( $comprehensive ) {
            $total_sql .= " WHERE aile_adi LIKE '%$search_term%' OR yeri LIKE '%$search_term%' OR notu LIKE '%$search_term%'";
        } else {
            $total_sql .= " WHERE aile_adi LIKE '%$search_term%'";
        }
    }
    $total_aileler = $wpdb->get_var( $total_sql );

    // Sayfalama için LIMIT ve OFFSET ekle
    $sql .= " LIMIT $per_page OFFSET $offset";

    // Aileleri veritabanından çek
    $aileler = $wpdb->get_results( $sql );

    // Toplam sayfa sayısını hesapla
    $num_pages = ceil( $total_aileler / $per_page );

    // Aileler modülünün içeriğini burada oluşturun
    echo '<div class="container">';
    echo '<div class="d-flex justify-content-between align-items-center mb-4">';
    echo '<!-- Sol taraf: Başlık -->';
    echo '<div class="d-flex align-items-center gap-3">';
    echo '<h1 class="mb-0" style="font-size: calc(1em + 1pt);">Aile Listesi</h1>';
    echo '</div>';
    echo '<!-- Sağ taraf: Arama formu  -->';
    echo '<div class="d-flex align-items-center gap-3">';
    echo '<!-- Arama Formu -->';
    echo '<form method="get" class="d-flex align-items-center gap-2">';
    echo '<input type="text" name="search" class="form-control" placeholder="Aile Adı, Yer ve Not\'da ara..." value="' . esc_attr( $search_query ) . '">';
    echo '<div class="form-check d-flex align-items-center">';
    echo '<input class="form-check-input" type="checkbox" name="comprehensive" id="comprehensiveSearch" ' . ( $comprehensive ? 'checked' : '' ) . '>';
    echo '<label for="comprehensiveSearch" class="form-check-label ms-2">Kapsamlı Ara</label>';
    echo '</div>';
    echo '<button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>';
    echo '</form>';
    echo '</div>';
    echo '</div>';

    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-hover">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Aile Adı</th>';
    echo '<th class="text-end">İşlemler</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    if ( $aileler ) {
        foreach ( $aileler as $aile ) {
            echo '<tr>';
            echo '<td>';
            echo '<a href="#" class="text-primary text-decoration-none" data-bs-toggle="modal" data-bs-target="#aileModal' . $aile->aile_id . '">' . esc_html( $aile->aile_adi ) . '</a>';
            echo '<!-- Modal -->';
            echo '<div class="modal fade" id="aileModal' . $aile->aile_id . '" tabindex="-1" aria-hidden="true">';
            echo '<div class="modal-dialog">';
            echo '<div class="modal-content">';
            echo '<div class="modal-header">';
            echo '<h5 class="modal-title">' . esc_html( $aile->aile_adi ) . '</h5>';
            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '</div>';
            echo '<div class="modal-body">';
            echo '<p><strong>Not:</strong> ' . ( ! empty( $aile->notu ) ? wp_kses_post( $aile->notu ) : 'Bulunmuyor' ) . '</p>';
            echo '</div>';
            echo '<div class="modal-footer">';
            echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</td>';
            echo '<td class="text-end">';

            // Yetkilendirme kontrolü
            if ( current_user_can( 'manage_options' ) ) { // Örneğin, admin yetkisi olanlar
                if ( ! empty( $aile->tree_sirasi ) ) {
                    echo '<form method="post" action="' . esc_url( home_url( '/search_person/' ) ) . '">'; // 'search_person' sayfanızın URL'sini doğru ayarlayın
                    wp_nonce_field( 'search_person', 'search_person_nonce' ); // Güvenlik için nonce alanı ekleyin
                    echo '<input type="hidden" name="q" value="' . esc_attr( $aile->tree_sirasi ) . '">';
                    echo '<button type="submit" class="btn btn-sm ' . ( $aile->yayinda ? 'btn-info' : 'btn-outline-secondary' ) . '"><i class="bi bi-diagram-3"></i> Ağaç Görünümü</button>';
                    echo '</form>';
                }
            } else {
                if ( ! empty( $aile->tree_sirasi ) ) {
                    echo '<button type="button" class="btn btn-sm tree-view-button ' . ( $aile->yayinda ? 'btn-info' : 'btn-outline-secondary' ) . '" data-tree-id="' . esc_attr( $aile->tree_sirasi ) . '" data-yayin-durumu="' . esc_attr( $aile->yayinda ) . '"><i class="bi bi-diagram-3"></i> Ağaç Görünümü</button>';
                }
            }

            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="2" class="text-center">Henüz kayıtlı aile bulunmamaktadır.</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    // Sayfalama
    if ( $num_pages > 1 ) {
        echo '<nav aria-label="Sayfa navigasyonu">';
        echo '<ul class="pagination justify-content-center">';

        // İlk Sayfa ve Önceki Sayfa
        if ( $current_page > 1 ) {
            $first_page_url = get_pagenum_link(1);
            $prev_page_url = get_pagenum_link($current_page - 1);

            echo '<li class="page-item"><a class="page-link" href="' . $first_page_url . '" aria-label="İlk"><span aria-hidden="true">««</span></a></li>';
            echo '<li class="page-item"><a class="page-link" href="' . $prev_page_url . '" aria-label="Önceki"><span aria-hidden="true">«</span></a></li>';
        } else {
            echo '<li class="page-item disabled"><span class="page-link">««</span></li>';
            echo '<li class="page-item disabled"><span class="page-link">«</span></li>';
        }

        // Aktif Sayfa
        echo '<li class="page-item active"><span class="page-link">' . $current_page . ' / ' . $num_pages . '</span></li>';

        // Sonraki Sayfa ve Son Sayfa
        if ( $current_page < $num_pages ) {
            $next_page_url = get_pagenum_link($current_page + 1);
            $last_page_url = get_pagenum_link($num_pages);

            echo '<li class="page-item"><a class="page-link" href="' . $next_page_url . '" aria-label="Sonraki"><span aria-hidden="true">»</span></a></li>';
            echo '<li class="page-item"><a class="page-link" href="' . $last_page_url . '" aria-label="Son"><span aria-hidden="true">»»</span></a></li>';
        } else {
            echo '<li class="page-item disabled"><span class="page-link">»</span></li>';
            echo '<li class="page-item disabled"><span class="page-link">»»</span></li>';
        }

        echo '</ul>';
        echo '</nav>';
    }

    echo '</div>';

    return ob_get_clean(); // Tampondaki içeriği al ve temizle
}

// Kısa kodu kaydet
add_shortcode( 'myailem_aileler', 'myailem_aileler_shortcode' );
?>
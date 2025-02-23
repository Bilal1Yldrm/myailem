<?php
/**
 * Plugin Name: Myailem Plugin
 * Plugin URI: https://www.aileagaclari.net
 * Description: Aile ağacı ve giriş sistemlerini içeren WordPress eklentisi.
 * Version: 1.0.0
 * Author: Bilal Yıldırım
 * Author URI: https://www.aileagaclari.net
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: myailem
 * Note: Bu eklenti, aile ağacı ve giriş sistemlerini içerir. Birincil Menude MyAilem_Nav_Menu ayarından menüyü ekleyebilirsiniz.
 * Domain Path: /languages/
 */
namespace Myailem; // Ana namespace'i koruyun

use Myailem\Tree\DatabaseHandler; // Sınıfı kullanmadan önce import edin

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Doğrudan erişimi engelle
}

// Eklenti dizin ve URL tanımları (Tüm alt modüller için geçerli olacak şekilde)
define( 'MYAILEM_DIR', plugin_dir_path( __FILE__ ) );
define( 'MYAILEM_URL', plugin_dir_url( __FILE__ ) );
define( 'MYAILEM_TREE_DIR', MYAILEM_DIR . 'myailem-tree/' ); // Ağaç modülü dizini
define( 'MYAILEM_TREE_URL', MYAILEM_URL . 'myailem-tree/' );   // Ağaç modülü URL'si
define( 'MYAILEM_AILELER_DIR', MYAILEM_DIR . 'myailem-aileler/' ); // Aileler modülü dizini
define( 'MYAILEM_AILELER_URL', MYAILEM_URL . 'myailem-aileler/' );   // Aileler modülü URL'si

// Sabitler
define( 'MYAILEM_KAYIT_FORM_SHORTCODE', '[myailem_kayit_form]' );
define( 'MYAILEM_GIRIS_FORM_SHORTCODE', '[myailem_login_kodu]' );
define( 'MYAILEM_GEDCOM_FORM_SHORTCODE', '[myailem_gedcom_form]' );
define( 'MYAILEM_SIFREMI_UNUTTUM_SHORTCODE', '[myailem_sifremi_unuttum]' ); // Yeni sabit
define( 'MYAILEM_AILELER_SHORTCODE', '[myailem_aileler]' );  // Aileler kısa kodu
define( 'MYAILEM_AILELER_YONETIM_SHORTCODE', '[myailem_aileler_yonetim]' ); // Aileler Yönetim kısa kodu
define( 'MYAILEM_SIIRLERIM_SHORTCODE', '[myailem_siirlerim]' );
define( 'MYAILEM_SIIRLERIM_YONETIM_SHORTCODE', '[myailem_siirlerim_yonetim]' );
define( 'MYAILEM_HIKAYELERIM_SHORTCODE', '[myailem_hikayelerim]' );
define( 'MYAILEM_HIKAYELERIM_YONETIM_SHORTCODE', '[myailem_hikayelerim_yonetim]' );
define( 'MYAILEM_BELGELER_SHORTCODE', '[myailem_belgeler]' );
define( 'MYAILEM_BELGELER_YONETIM_SHORTCODE', '[myailem_belgeler_yonetim]' );

// functions.php dosyasını dahil et
require_once plugin_dir_path( __FILE__ ) . 'myailem-tree/include/functions.php';

// shortcodes.php dosyasını dahil et
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php';

/**
 * Myailem Plugin Sınıfı
 *
 * Eklenti temel işlevlerini ve modül yüklemelerini yönetir.
 */
class Myailem_Plugin {

    use ShortcodeTrait;

    /**
     * Eklenti başlatıldığında çalışacak fonksiyonlar
     */
    public function __construct() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        register_activation_hook( __FILE__, array( $this, 'myailem_plugin_activate' ) );
        register_deactivation_hook( __FILE__,  array( $this, 'myailem_plugin_deactivate' ) );
        add_filter( 'template_include', array( $this, 'myailem_home_page_template' ) ); // template_include filtresi burada eklendi.
        $this->load_modules();
    }

    /**
     * Eklenti text domain'ini yükler.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'myailem', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Ana sayfa şablonunu değiştirme fonksiyonu.
     *
     * @param string $template Mevcut şablon yolu.
     * @return string Güncellenmiş şablon yolu.
     */
    function myailem_home_page_template( $template ) {
        if ( is_front_page() && is_home() ) {
            $new_template = plugin_dir_path( __FILE__ ) . 'templates/myailem-ana-sayfa.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }
        return $template;
    }

    /**
     * Alt modülleri yükler
     */
    private function load_modules() {
        require_once MYAILEM_DIR . 'myailem-login/myailem-login.php';
        require_once MYAILEM_DIR . 'myailem-login/myailem-kayit.php';
        require_once MYAILEM_TREE_DIR . 'myailem-tree.php';
        require_once MYAILEM_DIR . 'myailem-login/myailem-sifremi-unuttum.php'; // Yeni modül
        require_once MYAILEM_AILELER_DIR . 'myailem-aileler.php';  // Aileler modülü
        require_once MYAILEM_AILELER_DIR . 'myailem-aileler-yonetim.php'; // Aileler Yönetim modülü
        require_once MYAILEM_DIR . 'myailem-siirlerim/myailem-siirlerim.php';
        require_once MYAILEM_DIR . 'myailem-hikayelerim/myailem-hikayelerim.php';
        require_once MYAILEM_DIR . 'myailem-belgeler/myailem-belgeler.php';
    }

    /**
     * Eklenti için admin menüsüne bir menü öğesi ekler.
     */
    public function add_admin_menu() {
        add_menu_page(
            esc_html__('Myailem Ayarları', 'myailem'),        // Sayfa başlığı
            esc_html__('Myailem', 'myailem'),             // Menü başlığı
            'manage_options',        // Yetki düzeyi
            'myailem-settings',      // Menü slug
            array($this, 'myailem_settings_page'), // Menü oluşturma fonksiyonu
            'dashicons-admin-generic',   // İkon
            60                      // Menü pozisyonu
        );
    }

    /**
     * Eklenti ayarlar sayfası içeriği
     */
    public function myailem_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Myailem Ayarları', 'myailem'); ?></h1>
            <p><?php esc_html_e('Myailem eklentisinin ayarları buradan yapılabilir.', 'myailem'); ?></p>
            <a href="<?php echo get_permalink( get_option( 'myailem_aileler_sayfasi_id' ) ); ?>" class="button-primary"><?php esc_html_e( 'Aileler Sayfasına Git', 'myailem' ); ?></a>
        </div>
        <?php
    }

    /**
     * Eklenti etkinleştirildiğinde çalışacak fonksiyonlar
     */
    public function myailem_plugin_activate() {
        \Myailem\myailem_plugin_activate_operations();
        $this->myailem_create_nav_menu();
    }

    /**
     * Eklenti devre dışı bırakıldığında çalışacak fonksiyonlar
     */
    public function myailem_plugin_deactivate() {
        require_once MYAILEM_TREE_DIR . 'ged/DatabaseHandler.php';
        $database_handler = new DatabaseHandler();
        $database_handler->delete_tables();
    }

    /**
     * MyAilem_Nav_Menu adında bir menü oluşturur ve birincil menü konumuna atar.
     */
    public function myailem_create_nav_menu() {
        // Menü mevcut değilse oluştur
        $menu_name = 'MyAilem_Nav_Menu';
        $menu_exists = wp_get_nav_menu_object($menu_name);

        if (!$menu_exists) {
            $menu_id = wp_create_nav_menu($menu_name);

             // Menü konumunu ayarla
            $locations = get_theme_mod('nav_menu_locations');
            $locations['primary'] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
    }
}

function myailem_plugin_activate_operations() {
    require_once MYAILEM_TREE_DIR . 'ged/DatabaseHandler.php';
    $database_handler = new DatabaseHandler();
    $database_handler->create_tables();
    $myailem_plugin = new Myailem_Plugin();
    $myailem_plugin->myailem_ged_donustur_sayfasi_olustur();
    $myailem_plugin->myailem_kayit_sayfasi_olustur();
    $myailem_plugin->myailem_giris_sayfasi_olustur();
    $myailem_plugin->myailem_sifremi_unuttum_sayfasi_olustur();
    $myailem_plugin->myailem_aileler_sayfasi_olustur();
    $myailem_plugin->myailem_aileler_yonetim_sayfasi_olustur();
    $myailem_plugin->myailem_siirlerim_sayfasi_olustur();
    $myailem_plugin->myailem_siirlerim_yonetim_sayfasi_olustur();
    $myailem_plugin->myailem_hikayelerim_sayfasi_olustur();
    $myailem_plugin->myailem_hikayelerim_yonetim_sayfasi_olustur();
    $myailem_plugin->myailem_belgeler_sayfasi_olustur();
    $myailem_plugin->myailem_belgeler_yonetim_sayfasi_olustur();
}

// Eklenti nesnesini oluştur
$myailem_plugin = new Myailem_Plugin();
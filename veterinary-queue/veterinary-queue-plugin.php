<?php
/**
 * Plugin Name: Tierklinik-Warteschlange
 * Plugin URI: https://example.com/tierklinik-warteschlange
 * Description: Ein Plugin zur Verwaltung der Warteschlange einer Tierklinik mit TV-Ansicht.
 * Version: 1.0.0
 * Author: Ihr Name
 * Author URI: https://example.com
 * Text Domain: tierklinik-warteschlange
 * Domain Path: /languages
 */

// Direkten Zugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}

// Plugin-Hauptklasse
class TierklinikWarteschlange {
    // Plugin-Version
    private $version = '1.0.0';
    
    // Konstruktor
    public function __construct() {
        // Plugin-Konstanten definieren
        $this->define_constants();
        
        // Hooks registrieren
        $this->register_hooks();
        
        // Plugin-Komponenten initialisieren
        $this->init_components();
    }
    
    // Plugin-Konstanten definieren
    private function define_constants() {
        define('TIERKLINIK_WARTESCHLANGE_VERSION', $this->version);
        define('TIERKLINIK_WARTESCHLANGE_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('TIERKLINIK_WARTESCHLANGE_PLUGIN_URL', plugin_dir_url(__FILE__));
    }
    
    // Hooks registrieren
    private function register_hooks() {
        // Aktivierung und Deaktivierung
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialisierung
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_shortcodes'));
        
        // Admin-Bereich
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Frontend
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
    }
    
    // Plugin-Komponenten initialisieren
    private function init_components() {
        // REST API initialisieren
        require_once TIERKLINIK_WARTESCHLANGE_PLUGIN_DIR . 'includes/class-tierklinik-warteschlange-rest-api.php';
        new TierklinikWarteschlangeRestAPI();
    }
    
    // Bei Plugin-Aktivierung
    public function activate() {
        // Erstelle Warteschlangen-Ansicht-Seite
        $page_exists = get_page_by_path('tierklinik-warteschlange-tv');
        
        if (!$page_exists) {
            $page = array(
                'post_title'    => 'Tierklinik-Warteschlange TV',
                'post_content'  => '[tierklinik_warteschlange_tv]',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => 'tierklinik-warteschlange-tv'
            );
            
            wp_insert_post($page);
        }
        
        // Erstelle Warteschlangen-Eingabe-Seite
        $page_exists = get_page_by_path('tierklinik-warteschlange-eingabe');
        
        if (!$page_exists) {
            $page = array(
                'post_title'    => 'Tierklinik-Warteschlange Eingabe',
                'post_content'  => '[tierklinik_warteschlange_eingabe]',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => 'tierklinik-warteschlange-eingabe'
            );
            
            wp_insert_post($page);
        }
        
        // Registriere den Post-Type für die Warteschlange
        $this->register_post_type();
        
        // Spüle die Permalink-Struktur durch
        flush_rewrite_rules();
    }
    
    // Bei Plugin-Deaktivierung
    public function deactivate() {
        // Spüle die Permalink-Struktur durch
        flush_rewrite_rules();
    }
    
    // Custom Post Type für Tiere in der Warteschlange registrieren
    public function register_post_type() {
        $labels = array(
            'name'               => __('Tiere in der Warteschlange', 'tierklinik-warteschlange'),
            'singular_name'      => __('Tier in der Warteschlange', 'tierklinik-warteschlange'),
            'menu_name'          => __('Warteschlange', 'tierklinik-warteschlange'),
            'name_admin_bar'     => __('Tier in der Warteschlange', 'tierklinik-warteschlange'),
            'add_new'            => __('Neues Tier hinzufügen', 'tierklinik-warteschlange'),
            'add_new_item'       => __('Neues Tier hinzufügen', 'tierklinik-warteschlange'),
            'new_item'           => __('Neues Tier', 'tierklinik-warteschlange'),
            'edit_item'          => __('Tier bearbeiten', 'tierklinik-warteschlange'),
            'view_item'          => __('Tier ansehen', 'tierklinik-warteschlange'),
            'all_items'          => __('Alle Tiere', 'tierklinik-warteschlange'),
            'search_items'       => __('Tiere suchen', 'tierklinik-warteschlange'),
            'not_found'          => __('Keine Tiere gefunden', 'tierklinik-warteschlange'),
            'not_found_in_trash' => __('Keine Tiere im Papierkorb gefunden', 'tierklinik-warteschlange')
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-pets',
            'supports'           => array('title')
        );
        
        register_post_type('tierklinik_tier', $args);
    }
    
    // Shortcodes registrieren
    public function register_shortcodes() {
        add_shortcode('tierklinik_warteschlange_eingabe', array($this, 'render_eingabe_shortcode'));
        add_shortcode('tierklinik_warteschlange_tv', array($this, 'render_tv_shortcode'));
    }
    
    // Shortcode für die Eingabemaske rendern
    public function render_eingabe_shortcode() {
        ob_start();
        include TIERKLINIK_WARTESCHLANGE_PLUGIN_DIR . 'templates/eingabe-template.php';
        return ob_get_clean();
    }
    
    // Shortcode für die TV-Ansicht rendern
    public function render_tv_shortcode() {
        ob_start();
        include TIERKLINIK_WARTESCHLANGE_PLUGIN_DIR . 'templates/tv-template.php';
        return ob_get_clean();
    }
    
    // Admin-Menü hinzufügen
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=tierklinik_tier',
            __('Einstellungen', 'tierklinik-warteschlange'),
            __('Einstellungen', 'tierklinik-warteschlange'),
            'manage_options',
            'tierklinik-warteschlange-settings',
            array($this, 'render_settings_page')
        );
    }
    
    // Admin-Einstellungsseite rendern
    public function render_settings_page() {
        include TIERKLINIK_WARTESCHLANGE_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    // Admin-Scripts laden
    public function admin_scripts($hook) {
        // Nur auf Plugin-Seiten laden
        if (strpos($hook, 'tierklinik-warteschlange') === false && 
            strpos($hook, 'post_type=tierklinik_tier') === false) {
            return;
        }
        
        // Admin-CSS laden
        wp_enqueue_style(
            'tierklinik-warteschlange-admin-style',
            TIERKLINIK_WARTESCHLANGE_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            $this->version
        );
        
        // Admin-JS laden
        wp_enqueue_script(
            'tierklinik-warteschlange-admin-script',
            TIERKLINIK_WARTESCHLANGE_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // Localize Script mit AJAX-URL
        wp_localize_script(
            'tierklinik-warteschlange-admin-script',
            'tierklinik_warteschlange_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('tierklinik_warteschlange_nonce')
            )
        );
    }
    
    // Frontend-Scripts laden
    public function frontend_scripts() {
        // Font Awesome laden
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
            array(),
            '6.0.0-beta3'
        );
        
        // Hauptstil laden
        wp_enqueue_style(
            'tierklinik-warteschlange-style',
            TIERKLINIK_WARTESCHLANGE_PLUGIN_URL . 'assets/css/style.css',
            array(),
            $this->version
        );
        
        // Hauptscript laden
        wp_enqueue_script(
            'tierklinik-warteschlange-script',
            TIERKLINIK_WARTESCHLANGE_PLUGIN_URL . 'assets/js/script.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // Localize Script mit AJAX-URL
        wp_localize_script(
            'tierklinik-warteschlange-script',
            'tierklinik_warteschlange',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'rest_url' => rest_url('tierklinik-warteschlange/v1'),
                'nonce'    => wp_create_nonce('wp_rest')
            )
        );
    }
}

// Plugin-Instanz erstellen
new TierklinikWarteschlange();

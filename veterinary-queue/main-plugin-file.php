<?php
/**
 * Plugin Name: Veterinary Queue System
 * Description: Ein Warteschlangen-System für Tierarztpraxen und Tierkliniken
 * Version: 1.0.0
 * Author: Ihr Name
 * Author URI: https://ihre-website.de
 * Text Domain: veterinary-queue
 */

// Direkten Zugriff verbieten
if (!defined('ABSPATH')) {
    exit;
}

// Plugin-Konstanten definieren
define('VETERINARY_QUEUE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VETERINARY_QUEUE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VETERINARY_QUEUE_VERSION', '1.0.0');

// Klassen einbinden
require_once VETERINARY_QUEUE_PLUGIN_DIR . 'database-class.php';
require_once VETERINARY_QUEUE_PLUGIN_DIR . 'rest-api-class.php';

/**
 * Hauptklasse des Plugins
 */
class Veterinary_Queue_Plugin {
    private $db;
    private $rest_api;
    
    /**
     * Konstruktor
     */
    public function __construct() {
        // Datenbank-Instanz erstellen
        $this->db = new Veterinary_Queue_Database();
        
        // REST-API-Instanz erstellen
        $this->rest_api = new Veterinary_Queue_REST_API($this->db);
        
        // Aktivierungs-Hook
        register_activation_hook(__FILE__, [$this, 'activate_plugin']);
        
        // WordPress-Hooks
        add_action('init', [$this, 'load_textdomain']);
        add_action('init', [$this, 'register_shortcodes']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }
    
    /**
     * Plugin aktivieren
     */
    public function activate_plugin() {
        // Tabellen erstellen
        $this->db->create_tables();
        
        // Spülen der Rewrite-Rules
        flush_rewrite_rules();
    }
    
    /**
     * Textdomain laden
     */
    public function load_textdomain() {
        load_plugin_textdomain('veterinary-queue', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Shortcodes registrieren
     */
    public function register_shortcodes() {
        add_shortcode('veterinary_queue_input', [$this, 'input_shortcode']);
        add_shortcode('veterinary_queue_tv', [$this, 'tv_shortcode']);
    }
    
    /**
     * Scripts und Styles laden
     */
    public function enqueue_scripts() {
        // Plugin-Styles
        wp_enqueue_style(
            'veterinary-queue-styles',
            VETERINARY_QUEUE_PLUGIN_URL . 'styles.css',
            [],
            VETERINARY_QUEUE_VERSION
        );
        
        // Plugin-Scripts
        wp_enqueue_script(
            'veterinary-queue-scripts',
            VETERINARY_QUEUE_PLUGIN_URL . 'script-js.js',
            ['jquery'],
            VETERINARY_QUEUE_VERSION,
            true
        );
        
        // Daten an JavaScript übergeben
        wp_localize_script(
            'veterinary-queue-scripts',
            'veterinary_queue',
            [
                'rest_url' => rest_url(),
                'nonce' => wp_create_nonce('wp_rest')
            ]
        );
    }
    
    /**
     * Admin-Menü hinzufügen
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Tierklinik Warteschlange', 'veterinary-queue'),
            __('Warteschlange', 'veterinary-queue'),
            'edit_posts',
            'veterinary-queue',
            [$this, 'admin_page'],
            'dashicons-clipboard',
            30
        );
    }
    
    /**
     * Admin-Seite anzeigen
     */
    public function admin_page() {
        include VETERINARY_QUEUE_PLUGIN_DIR . 'eingabe-template.php';
    }
    
    /**
     * Shortcode für die Eingabemaske
     */
    public function input_shortcode() {
        ob_start();
        include VETERINARY_QUEUE_PLUGIN_DIR . 'eingabe-template.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode für die TV-Ansicht
     */
    public function tv_shortcode() {
        ob_start();
        include VETERINARY_QUEUE_PLUGIN_DIR . 'tv-template.php';
        return ob_get_clean();
    }
}

// Plugin starten
$veterinary_queue_plugin = new Veterinary_Queue_Plugin();

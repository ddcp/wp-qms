<?php
/**
 * Klasse für Datenbankoperationen des Tierklinik-Warteschlangen-Plugins
 */
class Veterinary_Queue_Database {
    private $table_name;
    private $treatments_table_name;
    
    /**
     * Konstruktor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'veterinary_queue';
        $this->treatments_table_name = $wpdb->prefix . 'veterinary_treatments';
    }
    
    /**
     * Plugin-Tabellen erstellen
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Queue-Tabelle
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            animal_name varchar(255) NOT NULL,
            animal_type varchar(255) NOT NULL,
            owner_name varchar(255) NOT NULL,
            arrival_time bigint(20) NOT NULL,
            priority int(1) NOT NULL DEFAULT 3,
            notes text,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Treatments-Tabelle
        $sql2 = "CREATE TABLE {$this->treatments_table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            animal_name varchar(255) NOT NULL,
            animal_type varchar(255) NOT NULL,
            owner_name varchar(255) NOT NULL,
            arrival_time bigint(20) NOT NULL,
            treatment_start_time bigint(20) NOT NULL,
            priority int(1) NOT NULL DEFAULT 3,
            wait_time bigint(20) NOT NULL,
            notes text,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql2);
    }
    
    /**
     * Alle Warteschlangen-Einträge abrufen
     */
    public function get_queue() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY 
             CASE priority
                WHEN 1 THEN 1   -- Hohe Priorität
                WHEN 2 THEN 2   -- Mittlere Priorität
                WHEN 3 THEN 3   -- Niedrige Priorität
             END,
             arrival_time ASC"
        );
        
        return $results ?: [];
    }
    
    /**
     * Statistiken abrufen
     */
    public function get_statistics() {
        global $wpdb;
        
        // Anzahl der wartenden Tiere
        $waiting_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        // Anzahl der behandelten Tiere heute
        $today_start = strtotime('today midnight');
        $treatment_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->treatments_table_name} WHERE treatment_start_time >= %d",
            $today_start
        ));
        
        // Durchschnittliche Wartezeit (der behandelten Tiere heute)
        $avg_wait_time = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(wait_time) FROM {$this->treatments_table_name} WHERE treatment_start_time >= %d",
            $today_start
        ));
        
        return [
            'waiting_count' => (int) $waiting_count,
            'treatment_count' => (int) $treatment_count,
            'avg_wait_time' => $avg_wait_time ? (int) $avg_wait_time : 0
        ];
    }
    
    /**
     * Tier zur Warteschlange hinzufügen
     */
    public function add_to_queue($animal_data) {
        global $wpdb;
        
        $data = [
            'animal_name' => sanitize_text_field($animal_data['animal_name']),
            'animal_type' => sanitize_text_field($animal_data['animal_type']),
            'owner_name' => sanitize_text_field($animal_data['owner_name']),
            'arrival_time' => time(),
            'priority' => intval($animal_data['priority']),
            'notes' => sanitize_textarea_field($animal_data['notes'])
        ];
        
        $wpdb->insert($this->table_name, $data);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Tier aus der Warteschlange entfernen
     */
    public function remove_from_queue($id) {
        global $wpdb;
        
        return $wpdb->delete($this->table_name, ['id' => $id], ['%d']);
    }
    
    /**
     * Tier als "in Behandlung" markieren
     */
    public function start_treatment($id) {
        global $wpdb;
        
        // Tier aus der Warteschlange holen
        $animal = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
        
        if (!$animal) {
            return false;
        }
        
        // Behandlung starten und Wartezeit berechnen
        $now = time();
        $wait_time = $now - $animal->arrival_time;
        
        // In die Behandlungstabelle einfügen
        $wpdb->insert(
            $this->treatments_table_name,
            [
                'animal_name' => $animal->animal_name,
                'animal_type' => $animal->animal_type,
                'owner_name' => $animal->owner_name,
                'arrival_time' => $animal->arrival_time,
                'treatment_start_time' => $now,
                'priority' => $animal->priority,
                'wait_time' => $wait_time,
                'notes' => $animal->notes
            ]
        );
        
        // Aus der Warteschlange entfernen
        $this->remove_from_queue($id);
        
        return true;
    }
    
    /**
     * Warteschlange zurücksetzen
     */
    public function reset_queue() {
        global $wpdb;
        
        return $wpdb->query("TRUNCATE TABLE {$this->table_name}");
    }
    
    /**
     * Einzelnen Warteschlangeneintrag abrufen
     */
    public function get_queue_item($id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
    }
}

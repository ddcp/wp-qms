<?php
/**
 * REST-API-Klasse für das Tierklinik-Warteschlangen-Plugin
 */
class Veterinary_Queue_REST_API {
    private $namespace = 'veterinary-queue/v1';
    private $db;
    
    /**
     * Konstruktor
     */
    public function __construct($db) {
        $this->db = $db;
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * API-Routen registrieren
     */
    public function register_routes() {
        // Warteschlange abrufen
        register_rest_route($this->namespace, '/queue', [
            'methods' => 'GET',
            'callback' => [$this, 'get_queue'],
            'permission_callback' => [$this, 'check_permission']
        ]);
        
        // Tier zur Warteschlange hinzufügen
        register_rest_route($this->namespace, '/queue', [
            'methods' => 'POST',
            'callback' => [$this, 'add_to_queue'],
            'permission_callback' => [$this, 'check_permission']
        ]);
        
        // Tier aus der Warteschlange entfernen
        register_rest_route($this->namespace, '/queue/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'remove_from_queue'],
            'permission_callback' => [$this, 'check_permission']
        ]);
        
        // Behandlung starten
        register_rest_route($this->namespace, '/queue/(?P<id>\d+)/treatment', [
            'methods' => 'POST',
            'callback' => [$this, 'start_treatment'],
            'permission_callback' => [$this, 'check_permission']
        ]);
        
        // Warteschlange zurücksetzen
        register_rest_route($this->namespace, '/queue/reset', [
            'methods' => 'POST',
            'callback' => [$this, 'reset_queue'],
            'permission_callback' => [$this, 'check_permission']
        ]);
    }
    
    /**
     * Berechtigungsprüfung
     */
    public function check_permission() {
        return current_user_can('edit_posts');
    }
    
    /**
     * Warteschlange und Statistiken abrufen
     */
    public function get_queue() {
        $queue = $this->db->get_queue();
        $stats = $this->db->get_statistics();
        
        return [
            'queue' => $queue,
            'stats' => $stats
        ];
    }
    
    /**
     * Tier zur Warteschlange hinzufügen
     */
    public function add_to_queue($request) {
        $params = $request->get_params();
        
        $required_fields = ['animal_name', 'animal_type', 'owner_name'];
        foreach ($required_fields as $field) {
            if (empty($params[$field])) {
                return new WP_Error(
                    'missing_field',
                    sprintf(__('Feld "%s" ist erforderlich', 'veterinary-queue'), $field),
                    ['status' => 400]
                );
            }
        }
        
        $animal_data = [
            'animal_name' => $params['animal_name'],
            'animal_type' => $params['animal_type'],
            'owner_name' => $params['owner_name'],
            'priority' => isset($params['priority']) ? intval($params['priority']) : 3,
            'notes' => isset($params['notes']) ? $params['notes'] : ''
        ];
        
        $id = $this->db->add_to_queue($animal_data);
        
        if (!$id) {
            return new WP_Error(
                'insert_failed',
                __('Fehler beim Hinzufügen des Tieres zur Warteschlange', 'veterinary-queue'),
                ['status' => 500]
            );
        }
        
        // Tier mit ID zurückgeben
        $new_animal = $this->db->get_queue_item($id);
        
        return rest_ensure_response($new_animal);
    }
    
    /**
     * Tier aus der Warteschlange entfernen
     */
    public function remove_from_queue($request) {
        $id = $request->get_param('id');
        
        $item = $this->db->get_queue_item($id);
        if (!$item) {
            return new WP_Error(
                'not_found',
                __('Tier nicht gefunden', 'veterinary-queue'),
                ['status' => 404]
            );
        }
        
        $result = $this->db->remove_from_queue($id);
        
        if (!$result) {
            return new WP_Error(
                'delete_failed',
                __('Fehler beim Entfernen des Tieres aus der Warteschlange', 'veterinary-queue'),
                ['status' => 500]
            );
        }
        
        return rest_ensure_response(['success' => true]);
    }
    
    /**
     * Behandlung starten
     */
    public function start_treatment($request) {
        $id = $request->get_param('id');
        
        $item = $this->db->get_queue_item($id);
        if (!$item) {
            return new WP_Error(
                'not_found',
                __('Tier nicht gefunden', 'veterinary-queue'),
                ['status' => 404]
            );
        }
        
        $result = $this->db->start_treatment($id);
        
        if (!$result) {
            return new WP_Error(
                'treatment_failed',
                __('Fehler beim Starten der Behandlung', 'veterinary-queue'),
                ['status' => 500]
            );
        }
        
        return rest_ensure_response(['success' => true]);
    }
    
    /**
     * Warteschlange zurücksetzen
     */
    public function reset_queue() {
        $result = $this->db->reset_queue();
        
        if ($result === false) {
            return new WP_Error(
                'reset_failed',
                __('Fehler beim Zurücksetzen der Warteschlange', 'veterinary-queue'),
                ['status' => 500]
            );
        }
        
        return rest_ensure_response(['success' => true]);
    }
}

<?php
/**
 * Template-Datei für die Eingabemaske des Tierklinik-Warteschlangen-Plugins
 */

// Direkten Zugriff verbieten
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="veterinary-queue-container">
    <h1><?php _e('Tierklinik Warteschlange', 'veterinary-queue'); ?></h1>
    
    <div class="input-form">
        <h2><?php _e('Neues Tier hinzufügen', 'veterinary-queue'); ?></h2>
        
        <form id="add-animal-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="animal-name"><?php _e('Name des Tieres', 'veterinary-queue'); ?> *</label>
                    <input type="text" id="animal-name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="animal-type"><?php _e('Tierart', 'veterinary-queue'); ?> *</label>
                    <select id="animal-type" class="form-control" required>
                        <option value="Hund"><?php _e('Hund', 'veterinary-queue'); ?></option>
                        <option value="Katze"><?php _e('Katze', 'veterinary-queue'); ?></option>
                        <option value="Vogel"><?php _e('Vogel', 'veterinary-queue'); ?></option>
                        <option value="Kaninchen"><?php _e('Kaninchen', 'veterinary-queue'); ?></option>
                        <option value="Meerschweinchen"><?php _e('Meerschweinchen', 'veterinary-queue'); ?></option>
                        <option value="Hamster"><?php _e('Hamster', 'veterinary-queue'); ?></option>
                        <option value="Reptil"><?php _e('Reptil', 'veterinary-queue'); ?></option>
                        <option value="Pferd"><?php _e('Pferd', 'veterinary-queue'); ?></option>
                        <option value="Anderes"><?php _e('Anderes', 'veterinary-queue'); ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="owner-name"><?php _e('Name des Besitzers', 'veterinary-queue'); ?> *</label>
                    <input type="text" id="owner-name" class="form-control" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="priority"><?php _e('Priorität', 'veterinary-queue'); ?></label>
                    <select id="priority" class="form-control">
                        <option value="3"><?php _e('Niedrig', 'veterinary-queue'); ?></option>
                        <option value="2" selected><?php _e('Mittel', 'veterinary-queue'); ?></option>
                        <option value="1"><?php _e('Hoch', 'veterinary-queue'); ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes"><?php _e('Anmerkungen', 'veterinary-queue'); ?></label>
                    <textarea id="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <button type="submit" class="btn"><?php _e('Zur Warteschlange hinzufügen', 'veterinary-queue'); ?></button>
            </div>
        </form>
    </div>
    
    <div class="queue-container">
        <div class="queue-header">
            <h2><?php _e('Aktuelle Warteschlange', 'veterinary-queue'); ?></h2>
            
            <div class="queue-actions">
                <button id="refresh-btn" class="btn"><?php _e('Aktualisieren', 'veterinary-queue'); ?></button>
                <button id="reset-btn" class="btn"><?php _e('Zurücksetzen', 'veterinary-queue'); ?></button>
            </div>
        </div>
        
        <div class="queue-stats">
            <div class="stat-item">
                <div class="stat-value" id="waiting-count">0</div>
                <div class="stat-label"><?php _e('Wartend', 'veterinary-queue'); ?></div>
            </div>
            
            <div class="stat-item">
                <div class="stat-value" id="avg-wait-time">0 Min</div>
                <div class="stat-label"><?php _e('Ø Wartezeit', 'veterinary-queue'); ?></div>
            </div>
            
            <div class="stat-item">
                <div class="stat-value" id="treatment-count">0</div>
                <div class="stat-label"><?php _e('Behandelt heute', 'veterinary-queue'); ?></div>
            </div>
        </div>
        
        <table class="queue-table">
            <thead>
                <tr>
                    <th><?php _e('Name', 'veterinary-queue'); ?></th>
                    <th><?php _e('Tierart', 'veterinary-queue'); ?></th>
                    <th><?php _e('Besitzer', 'veterinary-queue'); ?></th>
                    <th><?php _e('Wartezeit', 'veterinary-queue'); ?></th>
                    <th><?php _e('Priorität', 'veterinary-queue'); ?></th>
                    <th><?php _e('Anmerkungen', 'veterinary-queue'); ?></th>
                    <th><?php _e('Aktionen', 'veterinary-queue'); ?></th>
                </tr>
            </thead>
            <tbody id="queue-body">
                <tr>
                    <td colspan="7"><?php _e('Laden...', 'veterinary-queue'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

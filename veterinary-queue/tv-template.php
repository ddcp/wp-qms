<?php
/**
 * Template-Datei für die TV-Ansicht des Tierklinik-Warteschlangen-Plugins
 */

// Direkten Zugriff verbieten
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="veterinary-tv-container">
    <div class="tv-screen">
        <div class="tv-header">
            <h1 class="tv-title"><?php _e('Tierklinik Warteschlange', 'veterinary-queue'); ?></h1>
            <div class="tv-subtitle"><?php _e('Bitte haben Sie etwas Geduld, Sie werden aufgerufen', 'veterinary-queue'); ?></div>
        </div>
        
        <div class="tv-stats">
            <div class="tv-stat-item">
                <div class="tv-stat-value" id="waiting-count">0</div>
                <div class="tv-stat-label"><?php _e('Tiere im Wartezimmer', 'veterinary-queue'); ?></div>
            </div>
            
            <div class="tv-stat-item">
                <div class="tv-stat-value" id="avg-wait-time">0 Min</div>
                <div class="tv-stat-label"><?php _e('Durchschnittliche Wartezeit', 'veterinary-queue'); ?></div>
            </div>
            
            <div class="tv-stat-item">
                <div class="tv-stat-value" id="treatment-count">0</div>
                <div class="tv-stat-label"><?php _e('Heute bereits behandelt', 'veterinary-queue'); ?></div>
            </div>
        </div>
        
        <table class="tv-queue-table">
            <thead>
                <tr>
                    <th><?php _e('Name', 'veterinary-queue'); ?></th>
                    <th><?php _e('Tierart', 'veterinary-queue'); ?></th>
                    <th><?php _e('Besitzer', 'veterinary-queue'); ?></th>
                    <th><?php _e('Wartezeit', 'veterinary-queue'); ?></th>
                </tr>
            </thead>
            <tbody id="queue-body">
                <tr>
                    <td colspan="4"><?php _e('Laden...', 'veterinary-queue'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

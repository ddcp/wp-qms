/**
 * JavaScript für die Tierklinik-Warteschlange Eingabemaske
 */
jQuery(document).ready(function($) {
    // DOM-Elemente
    const addAnimalForm = document.getElementById('add-animal-form');
    const animalNameInput = document.getElementById('animal-name');
    const queueBody = document.getElementById('queue-body');
    const refreshBtn = document.getElementById('refresh-btn');
    const resetBtn = document.getElementById('reset-btn');
    const waitingCountElement = document.getElementById('waiting-count');
    const avgWaitTimeElement = document.getElementById('avg-wait-time');
    const treatmentCountElement = document.getElementById('treatment-count');
    
    // Aktualisiere die Warteschlange beim Laden der Seite
    refreshQueue();
    
    // Formular-Event-Listener
    if (addAnimalForm) {
        addAnimalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Formular-Daten sammeln
            const animalName = animalNameInput.value.trim();
            const animalType = document.getElementById('animal-type').value;
            const ownerName = document.getElementById('owner-name').value.trim();
            const priority = document.getElementById('priority').value;
            const notes = document.getElementById('notes').value.trim();
            
            // Validierung
            if (!animalName || !ownerName) {
                alert('Bitte geben Sie den Namen des Tieres und des Besitzers ein.');
                return;
            }
            
            // Daten zum Server senden
            $.ajax({
                url: veterinary_queue.rest_url + 'veterinary-queue/v1/queue',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', veterinary_queue.nonce);
                },
                data: {
                    animal_name: animalName,
                    animal_type: animalType,
                    owner_name: ownerName,
                    priority: priority,
                    notes: notes
                },
                success: function(response) {
                    // Formular zurücksetzen und Warteschlange aktualisieren
                    addAnimalForm.reset();
                    refreshQueue();
                },
                error: function(error) {
                    console.error('Fehler beim Hinzufügen des Tieres:', error);
                    alert('Es gab einen Fehler beim Hinzufügen des Tieres. Bitte versuchen Sie es erneut.');
                }
            });
        });
    }
    
    // Event-Listener für Refresh-Button
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            refreshQueue();
        });
    }
    
    // Event-Listener für Reset-Button
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (confirm('Sind Sie sicher, dass Sie die gesamte Warteschlange zurücksetzen möchten? Dieser Vorgang kann nicht rückgängig gemacht werden.')) {
                resetQueue();
            }
        });
    }
    
    // Funktion zum Aktualisieren der Warteschlange
    function refreshQueue() {
        $.ajax({
            url: veterinary_queue.rest_url + 'veterinary-queue/v1/queue',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', veterinary_queue.nonce);
            },
            success: function(data) {
                updateQueueUI(data);
            },
            error: function(error) {
                console.error('Fehler beim Abrufen der Warteschlange:', error);
            }
        });
    }
    
    // Funktion zum Zurücksetzen der Warteschlange
    function resetQueue() {
        $.ajax({
            url: veterinary_queue.rest_url + 'veterinary-queue/v1/queue/reset',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', veterinary_queue.nonce);
            },
            success: function() {
                refreshQueue();
            },
            error: function(error) {
                console.error('Fehler beim Zurücksetzen der Warteschlange:', error);
                alert('Es gab einen Fehler beim Zurücksetzen der Warteschlange.');
            }
        });
    }
    
    // Funktion zum Aktualisieren der UI mit Warteschlangendaten
    function updateQueueUI(data) {
        if (!queueBody) return;
        
        // Warteschlangeneinträge leeren
        queueBody.innerHTML = '';
        
        // Statistiken aktualisieren
        if (waitingCountElement) waitingCountElement.textContent = data.stats.waiting_count || 0;
        if (avgWaitTimeElement) avgWaitTimeElement.textContent = formatTime(data.stats.avg_wait_time || 0);
        if (treatmentCountElement) treatmentCountElement.textContent = data.stats.treatment_count || 0;
        
        // Keine Tiere in der Warteschlange?
        if (!data.queue || data.queue.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="7">Keine Tiere in der Warteschlange</td>';
            queueBody.appendChild(emptyRow);
            return;
        }
        
        // Warteschlangeneinträge hinzufügen
        data.queue.forEach(function(item) {
            const row = document.createElement('tr');
            row.classList.add(getPriorityClass(item.priority));
            
            // Berechne Wartezeit
            const waitTime = calculateWaitTime(item.arrival_time);
            
            row.innerHTML = `
                <td>${item.animal_name}</td>
                <td>${item.animal_type}</td>
                <td>${item.owner_name}</td>
                <td>${formatTime(waitTime)}</td>
                <td>${getPriorityLabel(item.priority)}</td>
                <td>${item.notes || '-'}</td>
                <td>
                    <button class="action-btn start-treatment" data-id="${item.id}">Behandlung starten</button>
                    <button class="action-btn remove-item" data-id="${item.id}">Entfernen</button>
                </td>
            `;
            
            queueBody.appendChild(row);
        });
        
        // Event-Listener für Aktionsbuttons
        setupActionButtons();
    }
    
    // Funktion zum Einrichten der Aktionsbuttons
    function setupActionButtons() {
        // Behandlung starten Buttons
        document.querySelectorAll('.start-treatment').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                
                $.ajax({
                    url: veterinary_queue.rest_url + 'veterinary-queue/v1/queue/' + itemId + '/treatment',
                    method: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', veterinary_queue.nonce);
                    },
                    success: function() {
                        refreshQueue();
                    },
                    error: function(error) {
                        console.error('Fehler beim Starten der Behandlung:', error);
                        alert('Es gab einen Fehler beim Starten der Behandlung.');
                    }
                });
            });
        });
        
        // Entfernen Buttons
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                
                if (confirm('Sind Sie sicher, dass Sie dieses Tier aus der Warteschlange entfernen möchten?')) {
                    $.ajax({
                        url: veterinary_queue.rest_url + 'veterinary-queue/v1/queue/' + itemId,
                        method: 'DELETE',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', veterinary_queue.nonce);
                        },
                        success: function() {
                            refreshQueue();
                        },
                        error: function(error) {
                            console.error('Fehler beim Entfernen des Eintrags:', error);
                            alert('Es gab einen Fehler beim Entfernen des Tieres aus der Warteschlange.');
                        }
                    });
                }
            });
        });
    }
    
    // Hilfsfunktionen
    function calculateWaitTime(arrivalTime) {
        const now = Math.floor(Date.now() / 1000);
        const arrival = parseInt(arrivalTime);
        return now - arrival; // Sekunden
    }
    
    function formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        
        if (hours > 0) {
            return `${hours} Std ${minutes} Min`;
        } else {
            return `${minutes} Min`;
        }
    }
    
    function getPriorityClass(priority) {
        switch (parseInt(priority)) {
            case 1: return 'priority-high';
            case 2: return 'priority-medium';
            case 3: return 'priority-low';
            default: return '';
        }
    }
    
    function getPriorityLabel(priority) {
        switch (parseInt(priority)) {
            case 1: return 'Hoch';
            case 2: return 'Mittel';
            case 3: return 'Niedrig';
            default: return 'Normal';
        }
    }
    
    // Auto-Refresh alle 30 Sekunden
    setInterval(refreshQueue, 30000);
});

# WP VQS - Simple Veterinary Queue System
Queue Management System Wordpress Plugin

# Veterinary Queue System

A WordPress plugin for managing veterinary clinic waiting queues. This system enables veterinary practices and clinics to efficiently manage their patient queue and display it on a TV screen in the waiting room.

## Features

- **Input Form**: Register new patients (animal name, species, owner, priority)
- **Queue Display**: Clear overview of all waiting animals
- **TV View**: Optimized display for waiting room screens
- **Priority System**: Urgent cases can be prioritized
- **Statistics**: Number of waiting and treated animals, average waiting time
- **AJAX Updates**: Automatic display refresh without manual page reload

## Installation

1. Upload the plugin folder to your WordPress plugins directory (`wp-content/plugins/`)
2. Activate the plugin in the WordPress admin panel
3. Use the shortcodes or admin menu to access the features

## Usage

### Admin Panel
After activation, a new menu item "Warteschlange" appears in the WordPress admin. Use this to manage the queue.

### Shortcodes
- `[veterinary_queue_input]` - Shows the input form for new patients
- `[veterinary_queue_tv]` - Shows the TV view for waiting room displays

## Technical Details

- WordPress REST API for data operations
- AJAX for real-time updates
- Custom database tables for optimal performance
- Responsive design for various screen sizes

## System Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL/MariaDB
- JavaScript-enabled browser

## License

GPL v2 or later

## Development

Contributions are welcome! Please create issues/PRs for bug reports, feature requests, or pull requests.

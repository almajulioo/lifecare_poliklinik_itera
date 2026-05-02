import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Import offline & notification management
 */
import './offline-detector.js';
import './notification-manager.js';
import './offline-queue.js';
import './offline-history.js';
import './notification-scheduler.js';
import './medication-modal.js';

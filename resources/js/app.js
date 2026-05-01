import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Service Worker Registration
 * Register service worker untuk offline support & notifications
 * 
 * ⚠️ IMPORTANT: Service worker must be in public/ folder and served directly
 * Path is /service-worker.js (not built by Vite)
 */
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js', {
      scope: '/',
    })
      .then((registration) => {
        console.log('[App] Service Worker registered successfully:', registration);
        
        // Check for updates periodically
        setInterval(() => {
          registration.update().catch((err) => {
            console.warn('[App] Error checking for SW updates:', err);
          });
        }, 60000); // Check every 60 seconds
        
        // Handle new service worker waiting
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              // New SW ready, notify user
              console.log('[App] New version available');
              // Can show notification here to user
            }
          });
        });
      })
      .catch((err) => {
        console.error('[App] Service Worker registration failed:', err);
      });
  });
}

/**
 * Import offline & notification management
 */
import './offline-detector.js';
import './notification-manager.js';
import './offline-queue.js';
import './offline-history.js';
import './notification-scheduler.js';
import './medication-modal.js';

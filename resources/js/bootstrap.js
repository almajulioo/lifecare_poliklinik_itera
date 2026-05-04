import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Register Service Worker for Offline Support
 */
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then((registration) => {
        console.log('[App] Service Worker registered:', registration);
        
        // Check for updates periodically
        setInterval(() => {
          registration.update().catch((err) => {
            console.warn('[App] Service Worker update check failed:', err);
          });
        }, 60000); // Check every minute
      })
      .catch((error) => {
        console.error('[App] Service Worker registration failed:', error);
      });
  });
}

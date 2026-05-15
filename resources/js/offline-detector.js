/**
 * LifeCare+ Offline Detector
 * Detects online/offline status dan update UI
 */

class OfflineDetector {
  constructor() {
    try {
      this.isOnline = navigator.onLine;
      this.listeners = [];
      this.setupEventListeners();
      
      // Defensive check for initUI
      if (typeof this.initUI === 'function') {
        this.initUI();
      }
      console.log('[Offline Detector] Initialized. Currently', this.isOnline ? 'ONLINE' : 'OFFLINE');
    } catch (err) {
      console.error('[Offline Detector] Constructor error:', err);
    }
  }

  setupEventListeners() {
    window.addEventListener('online', () => this.handleOnline());
    window.addEventListener('offline', () => this.handleOffline());
    
    // Also check connection periodically for reliability
    setInterval(() => {
      const actualStatus = navigator.onLine;
      if (actualStatus !== this.isOnline) {
        console.log('[Offline Detector] Status changed to:', actualStatus ? 'ONLINE' : 'OFFLINE');
        this.isOnline = actualStatus;
        this.updateUI();
        if (actualStatus) {
          this.handleOnline();
        } else {
          this.handleOffline();
        }
      }
    }, 5000); // Check every 5 seconds
  }

  initUI() {
    // Initialize UI elements
    this.updateUI();
  }

  handleOnline() {
    console.log('[Offline Detector] Going online');
    this.isOnline = true;
    this.updateUI();
    this.notifyListeners('online');
    
    // Trigger sync when coming back online
  //   if ('serviceWorker' in navigator && 'SyncManager' in window) {
  //     navigator.serviceWorker.ready.then((registration) => {
  //       registration.sync.register('sync-logs').catch((err) => {
  //         console.warn('[Offline Detector] Could not register sync:', err);
  //       });
  //     });
  //   }
  }

  handleOffline() {
    console.log('[Offline Detector] Going offline');
    this.isOnline = false;
    this.updateUI();
    this.notifyListeners('offline');
  }

  updateUI() {
    try {
      // Update indicator element if exists
      const indicator = document.getElementById('offline-indicator');
      if (indicator) {
        if (this.isOnline) {
          indicator.style.display = 'none';
        } else {
          indicator.style.display = 'block';
        }
      }

      // Update body data attribute
      document.body.dataset.offline = !this.isOnline;
      
      // Show/hide offline warning
      this.updateOfflineWarning();
    } catch (err) {
      console.warn('[Offline Detector] Error updating UI:', err);
    }
  }

  updateOfflineWarning() {
    try {
      let warning = document.getElementById('offline-warning');
      
      if (!this.isOnline) {
        if (!warning) {
          warning = document.createElement('div');
          warning.id = 'offline-warning';
          warning.className = 'fixed top-0 left-0 right-0 bg-yellow-50 border-b border-yellow-200 text-yellow-700 p-3 text-sm z-50';
          warning.innerHTML = `
            <div class="flex items-center gap-2 max-w-4xl mx-auto">
              <span class="text-lg">📡</span>
              <span><strong>Offline Mode:</strong> Anda sedang offline. Data akan disinkronkan otomatis saat kembali online.</span>
            </div>
          `;
          document.body.insertBefore(warning, document.body.firstChild);
        }
      } else {
        if (warning) {
          warning.style.opacity = '0';
          warning.style.transition = 'opacity 0.3s ease-out';
          setTimeout(() => warning.remove(), 300);
        }
      }
    } catch (err) {
      console.warn('[Offline Detector] Error updating offline warning:', err);
    }
  }

  notifyListeners(status) {
    this.listeners.forEach((listener) => {
      try {
        listener(status);
      } catch (err) {
        console.warn('[Offline Detector] Error notifying listener:', err);
      }
    });
  }

  addListener(callback) {
    if (typeof callback === 'function') {
      this.listeners.push(callback);
    }
  }

  removeListener(callback) {
    this.listeners = this.listeners.filter((listener) => listener !== callback);
  }

  static getInstance() {
    try {
      if (!window.__OfflineDetectorInstance) {
        window.__OfflineDetectorInstance = new OfflineDetector();
      }
      return window.__OfflineDetectorInstance;
    } catch (err) {
      console.error('[Offline Detector] Error in getInstance:', err);
      return null;
    }
  }
}

// Initialize when ready with error handling
try {
  OfflineDetector.getInstance();
} catch (err) {
  console.error('[Offline Detector] Error during initialization:', err);
}

// Export for use
window.OfflineDetector = OfflineDetector;


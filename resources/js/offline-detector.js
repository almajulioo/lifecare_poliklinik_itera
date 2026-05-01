/**
 * LifeCare+ Offline Detector
 * Detects online/offline status dan update UI
 */

class OfflineDetector {
  constructor() {
    this.isOnline = navigator.onLine;
    this.listeners = [];
    this.setupEventListeners();
    this.initUI();
    console.log('[Offline Detector] Initialized. Currently', this.isOnline ? 'ONLINE' : 'OFFLINE');
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

  handleOnline() {
    console.log('[Offline Detector] Going online');
    this.isOnline = true;
    this.updateUI();
    this.notifyListeners('online');
    
    // Trigger sync when coming back online
    if ('serviceWorker' in navigator && 'SyncManager' in window) {
      navigator.serviceWorker.ready.then((registration) => {
        registration.sync.register('sync-logs').catch((err) => {
          console.warn('[Offline Detector] Could not register sync:', err);
        });
      });
    }
  }

  handleOffline() {
    console.log('[Offline Detector] Going offline');
    this.isOnline = false;
    this.updateUI();
    this.notifyListeners('offline');
  }

  updateUI() {
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
  }

  updateOfflineWarning() {
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
  }

  subscribe(listener) {
    this.listeners.push(listener);
    return () => {
      this.listeners = this.listeners.filter((l) => l !== listener);
    };
  }

  notifyListeners(status) {
    this.listeners.forEach((listener) => listener(status));
  }

  static getInstance() {
    if (!window.__OfflineDetectorInstance) {
      window.__OfflineDetectorInstance = new OfflineDetector();
    }
    return window.__OfflineDetectorInstance;
  }
}

// Initialize when DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    OfflineDetector.getInstance();
  });
} else {
  OfflineDetector.getInstance();
}

// Export for use in other scripts
window.OfflineDetector = OfflineDetector;

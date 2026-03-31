/**
 * LifeCare+ Notification Manager
 * Handles browser notifications, permissions, and preferences
 */

class NotificationManager {
  constructor() {
    this.isSupported = 'Notification' in window && 'serviceWorker' in navigator;
    this.permission = this.getPermission();
    this.preferences = this.loadPreferences();
    this.initUI();
  }

  getPermission() {
    if (!this.isSupported) return 'denied';
    return Notification.permission;
  }

  loadPreferences() {
    const stored = localStorage.getItem('notification_preferences');
    
    if (stored) {
      return JSON.parse(stored);
    }
    
    // Default preferences
    const defaults = {
      enabled: true,
      sound: true,
      reminderAdvance: 15, // minutes before scheduled time
      doNotDisturb: null, // { start: '22:00', end: '07:00' }
      lastRequestTime: null,
    };
    
    localStorage.setItem('notification_preferences', JSON.stringify(defaults));
    return defaults;
  }

  savePreferences(prefs) {
    this.preferences = { ...this.preferences, ...prefs };
    localStorage.setItem('notification_preferences', JSON.stringify(this.preferences));
  }

  initUI() {
    // Auto-request permission on first visit (user can dismiss)
    if (this.permission === 'default') {
      this.requestPermissionOnce();
    }
    
    // Listen for service worker messages
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.addEventListener('message', (event) => {
        const { type, payload } = event.data;
        
        if (type === 'SHOW_NOTIFICATION') {
          this.handleNotificationFromServiceWorker(payload);
        }
      });
    }
  }

  requestPermissionOnce() {
    const lastRequestTime = this.preferences.lastRequestTime;
    const now = Date.now();
    
    // Only ask once per session, or after 7 days
    if (lastRequestTime && (now - lastRequestTime) < 7 * 24 * 60 * 60 * 1000) {
      return;
    }
    
    this.requestPermission();
  }

  requestPermission() {
    if (!this.isSupported) {
      console.warn('[Notification Manager] Notifications not supported');
      return Promise.reject();
    }

    if (this.permission === 'granted') {
      return Promise.resolve();
    }

    if (this.permission === 'denied') {
      console.warn('[Notification Manager] User denied notifications');
      return Promise.reject();
    }

    // Request permission
    return Notification.requestPermission().then((permission) => {
      this.permission = permission;
      
      if (permission === 'granted') {
        console.log('[Notification Manager] Permission granted');
        this.savePreferences({ lastRequestTime: Date.now() });
        
        // Show test notification
        this.showNotification('LifeCare+ Activated! 💊', {
          body: 'Notifikasi pengingat minum obat sudah aktif. Anda akan menerima pengingat sesuai jadwal yang telah ditentukan.',
          icon: '/images/app-icon.png',
          badge: '/images/badge.png',
          tag: 'test',
        });
        
        return true;
      }
      
      return false;
    });
  }

  showNotification(title, options = {}) {
    if (!this.isSupported || !this.preferences.enabled) {
      return;
    }

    if (this.permission !== 'granted') {
      console.warn('[Notification Manager] No permission to show notifications');
      return;
    }

    // Check do-not-disturb
    if (this.isInDoNotDisturb()) {
      console.log('[Notification Manager] In do-not-disturb time, skipping notification');
      return;
    }

    // Show via service worker if available
    if (navigator.serviceWorker?.controller) {
      navigator.serviceWorker.controller.postMessage({
        type: 'SHOW_NOTIFICATION',
        payload: { title, options },
      });
    } else {
      // Fallback: show via Notification API directly
      new Notification(title, options);
    }

    // Play sound if enabled
    if (this.preferences.sound) {
      this.playNotificationSound();
    }
  }

  playNotificationSound() {
    try {
      // Try to play notification sound
      const audio = new Audio('/audio/notification.mp3');
      audio.volume = 0.7;
      audio.play().catch((err) => {
        console.warn('[Notification Manager] Could not play sound:', err);
      });
    } catch (err) {
      console.warn('[Notification Manager] Error playing sound:', err);
    }
  }

  isInDoNotDisturb() {
    if (!this.preferences.doNotDisturb) return false;

    const { start, end } = this.preferences.doNotDisturb;
    const now = new Date();
    const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

    // Simple check (doesn't handle midnight wrap-around perfectly)
    return currentTime >= start && currentTime < end;
  }

  handleNotificationFromServiceWorker(payload) {
    // Handle clicks or interactions with notifications
    console.log('[Notification Manager] Notification from SW:', payload);
  }

  setPreference(key, value) {
    this.savePreferences({ [key]: value });
  }

  getPreference(key) {
    return this.preferences[key];
  }

  static getInstance() {
    if (!window.__NotificationManagerInstance) {
      window.__NotificationManagerInstance = new NotificationManager();
    }
    return window.__NotificationManagerInstance;
  }
}

// Initialize when ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    NotificationManager.getInstance();
  });
} else {
  NotificationManager.getInstance();
}

// Export for use
window.NotificationManager = NotificationManager;

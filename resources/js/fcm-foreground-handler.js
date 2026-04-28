/**
 * FCM Foreground Message Handler
 * Handles incoming Firebase Cloud Messaging notifications when app is active
 */

import { initializeApp } from 'firebase/app';
import { getMessaging, onMessage } from 'firebase/messaging';

// Firebase configuration (should match service worker config)
const firebaseConfig = {
  apiKey: "AIzaSyC4l8cv22eHJrBX4ezcJGVl0CSzgvoJnvA",
  authDomain: "lifecare-poliklinik-itera.firebaseapp.com",
  projectId: "lifecare-poliklinik-itera",
  storageBucket: "lifecare-poliklinik-itera.firebasestorage.app",
  messagingSenderId: "885870142104",
  appId: "1:885870142104:web:5e94de5f1f00672828a6ed",
  measurementId: "G-R784D7MWEF"
};

class FcmForegroundHandler {
  constructor() {
    this.initialized = false;
    this.init();
  }

  init() {
    try {
      // Initialize Firebase only if config is valid
      if (firebaseConfig.apiKey === 'YOUR_API_KEY') {
        console.warn('[FCM Handler] Firebase config not set');
        return;
      }

      // Initialize Firebase app
      const app = initializeApp(firebaseConfig);
      
      // Get FCM messaging instance
      const messaging = getMessaging(app);
      this.initialized = true;

      // Handle foreground messages
      onMessage(messaging, (payload) => {
        console.log('[FCM Handler] Received foreground message:', payload);
        
        // Handle both notification and data messages
        const title = payload.notification?.title || payload.data?.title || 'LifeCare+ Notification';
        const options = {
          body: payload.notification?.body || payload.data?.body || '',
          icon: payload.notification?.icon || '/favicon.ico',
          badge: '/images/badge.png',
          tag: payload.data?.tag || 'medication-reminder',
          requireInteraction: payload.data?.requireInteraction === 'true',
          data: payload.data || {},
        };

        // Show notification
        this.showNotification(title, options, payload);
        
        // Additional actions based on notification type
        if (payload.data?.type === 'medication-reminder') {
          this.handleMedicationReminder(payload);
        } else if (payload.data?.type === 'second-reminder') {
          this.handleSecondReminder(payload);
        }
      });

      console.log('[FCM Handler] Initialized successfully');
    } catch (error) {
      console.error('[FCM Handler] Initialization error:', error);
    }
  }

  showNotification(title, options, payload) {
    // Check if Notification API is supported
    if (!('Notification' in window)) {
      console.log('[FCM Handler] Notifications not supported');
      return;
    }

    // Only show if permission is granted
    if (Notification.permission !== 'granted') {
      console.log('[FCM Handler] Notification permission not granted');
      return;
    }

    // Show notification with click handler
    const notification = new Notification(title, options);
    
    notification.addEventListener('click', () => {
      // Close notification
      notification.close();
      
      // Focus window or open app
      if (window.clients) {
        window.clients.matchAll({ type: 'window' }).then(clientList => {
          for (let client of clientList) {
            if (client.url === '/' && 'focus' in client) {
              client.focus();
              return;
            }
          }
          if (window.open) {
            window.open('/dashboard', '_self');
          }
        });
      } else {
        window.focus();
        window.location.href = '/dashboard';
      }
      
      // Send action to backend
      if (payload.data?.scheduleId) {
        this.sendNotificationAction('clicked', payload.data.scheduleId);
      }
    });

    notification.addEventListener('close', () => {
      // Send action to backend
      if (payload.data?.scheduleId) {
        this.sendNotificationAction('dismissed', payload.data.scheduleId);
      }
    });
  }

  handleMedicationReminder(payload) {
    console.log('[FCM Handler] Handling medication reminder:', payload.data);
    
    // Optionally: reload dashboard or trigger update
    if (document.location.pathname === '/app/dashboard') {
      // Trigger dashboard refresh
      window.dispatchEvent(new CustomEvent('fcm-medication-reminder', {
        detail: payload.data
      }));
    }
  }

  handleSecondReminder(payload) {
    console.log('[FCM Handler] Handling second reminder:', payload.data);
    
    // Second reminders usually need more attention
    // Could trigger a modal or more persistent notification
    if (document.location.pathname === '/app/dashboard') {
      window.dispatchEvent(new CustomEvent('fcm-second-reminder', {
        detail: payload.data
      }));
    }
  }

  sendNotificationAction(action, scheduleId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!csrfToken) {
      console.error('[FCM Handler] CSRF token not found');
      return;
    }

    fetch('/api/notification-action', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        action: action,
        medication_schedule_id: scheduleId,
        timestamp: new Date().toISOString()
      })
    })
    .then(response => response.json())
    .then(data => {
      console.log('[FCM Handler] Action sent successfully:', data);
    })
    .catch(error => {
      console.error('[FCM Handler] Error sending action:', error);
    });
  }
}

// Initialize on load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    new FcmForegroundHandler();
  });
} else {
  new FcmForegroundHandler();
}

export default FcmForegroundHandler;

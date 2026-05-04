/**
 * LifeCare+ Service Worker
 * Handles offline caching, background sync, and notifications
 */

const CACHE_NAME = 'lifecare-v1';
const API_CACHE = 'lifecare-api-v1';
const RUNTIME_CACHE = 'lifecare-runtime-v1';

// Assets yang harus selalu tersedia (cache critical)
const CRITICAL_ASSETS = [
  '/',
  '/manifest.json',
  '/offline.html', // fallback page (akan dibuat nanti)
];

// API endpoints yang di-cache
const CACHEABLE_APIs = [
  /\/api\/medications\//,
  /\/api\/compliance\//,
  /\/api\/schedules\//,
];

/**
 * Install Event: Cache critical assets
 */
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[Service Worker] Caching critical assets');
      // Jangan throw error if offline - hanya cache yang bisa diakses
      return cache.addAll(CRITICAL_ASSETS).catch((err) => {
        console.warn('[Service Worker] Could not cache all critical assets:', err);
      });
    })
  );
  
  // Force new version to activate immediately
  self.skipWaiting();
});

/**
 * Activate Event: Clean up old caches
 */
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => {
            // Delete old versions
            return (
              cacheName !== CACHE_NAME &&
              cacheName !== API_CACHE &&
              cacheName !== RUNTIME_CACHE
            );
          })
          .map((cacheName) => {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          })
      );
    })
  );
  
  // Initialize IndexedDB stores
  initializeIndexedDB();
  
  // Claim all clients immediately
  return self.clients.claim();
});

/**
 * Fetch Event: Network-first strategy for API, Cache-first for assets
 */
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip foreign URLs
  if (url.origin !== location.origin) {
    return;
  }
  
  // API Requests: Network-first, fallback to cache
  if (url.pathname.startsWith('/api/')) {
    return event.respondWith(networkFirstStrategy(request));
  }
  
  // Static Assets: Cache-first, fallback to network
  if (isAsset(url.pathname)) {
    return event.respondWith(cacheFirstStrategy(request));
  }
  
  // HTML Pages: Network-first, fallback to cache
  if (request.headers.get('accept')?.includes('text/html')) {
    return event.respondWith(networkFirstStrategy(request));
  }
});

/**
 * Network-First Strategy
 * Try network first, if fails use cache
 */
function networkFirstStrategy(request) {
  return fetch(request)
    .then((response) => {
      // Cache successful responses
      if (response.ok) {
        const clone = response.clone();
        
        // Determine which cache to use
        const cacheName = request.url.includes('/api/') ? API_CACHE : RUNTIME_CACHE;
        
        caches.open(cacheName).then((cache) => {
          cache.put(request, clone);
        });
      }
      
      return response;
    })
    .catch(() => {
      // Fall back to cache on network error
      return caches.match(request).then((cachedResponse) => {
        if (cachedResponse) {
          // Add offline indicator to response
          return cachedResponse;
        }
        
        // Fallback for HTML pages
        if (request.headers.get('accept')?.includes('text/html')) {
          return caches.match('/offline.html');
        }
        
        // Generic offline response
        return new Response('Offline - Data not available', {
          status: 503,
          statusText: 'Service Unavailable',
        });
      });
    });
}

/**
 * Cache-First Strategy
 * Use cache first, if not found fetch from network
 */
function cacheFirstStrategy(request) {
  return caches.match(request).then((cachedResponse) => {
    if (cachedResponse) {
      // Return cached version
      return cachedResponse;
    }
    
    // Not in cache, fetch from network
    return fetch(request)
      .then((response) => {
        // Cache the response
        const clone = response.clone();
        caches.open(RUNTIME_CACHE).then((cache) => {
          cache.put(request, clone);
        });
        
        return response;
      })
      .catch(() => {
        // Network failed and not in cache
        return new Response('Asset not available offline', {
          status: 503,
          statusText: 'Service Unavailable',
        });
      });
  });
}

/**
 * Helper: Check if URL is asset (CSS, JS, images, fonts)
 */
function isAsset(pathname) {
  return /\.(css|js|jpg|jpeg|png|gif|svg|woff|woff2|ttf|eot|ico)$/.test(
    pathname
  );
}

/**
 * Message Handler: Handle messages from clients
 */
self.addEventListener('message', (event) => {
  const { type, payload } = event.data;
  
  console.log('[Service Worker] Received message:', type, payload);
  
  switch (type) {
    case 'SKIP_WAITING':
      console.log('[Service Worker] SKIP_WAITING triggered');
      self.skipWaiting();
      break;
      
    case 'GET_PENDING_LOGS':
      getPendingLogs(event);
      break;
      
    case 'SYNC_LOGS':
      syncPendingLogs(event);
      break;
      
    case 'CLEAR_CACHE':
      clearAllCaches(event);
      break;
      
    default:
      console.log('[Service Worker] Unknown message type:', type);
  }
});

/**
 * Background Sync: Sync offline logs when online
 * (Fallback dengan polling jika browser tidak support Background Sync API)
 */
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-logs') {
    event.waitUntil(syncPendingLogs());
  }
  
  if (event.tag === 'sync-notifications') {
    event.waitUntil(syncNotificationTimes());
  }
});

/**
 * Periodic Background Sync: Check for notifications every 5 minutes
 * Note: This requires browser support and user permission
 */
self.addEventListener('periodicsync', (event) => {
  if (event.tag === 'check-notifications') {
    event.waitUntil(checkNotifications());
  }
  
  if (event.tag === 'check-second-reminders') {
    event.waitUntil(checkSecondReminders());
  }
});

/**
 * Check Notifications and Send If Time Matches
 */
async function checkNotifications() {
  try {
    console.log('[Service Worker] Checking notifications...');
    await processExpiredSnoozes();
    
    let notifications = null;

    // Try to fetch from server first
    try {
      const response = await fetch('/api/notification-times', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      
      if (response.ok) {
        const data = await response.json();
        notifications = data.today || [];
        
        // Cache the notifications in IndexedDB for offline access
        await storeInIndexedDB('notification_times', {
          id: 'today-notifications',
          data: notifications,
          timestamp: new Date().toISOString(),
        });
        
        console.log('[Service Worker] Fetched fresh notifications from server');
      } else {
        console.warn('[Service Worker] Failed to fetch from server, trying cache...');
        notifications = null;
      }
    } catch (networkError) {
      console.warn('[Service Worker] Network error, trying IndexedDB cache...', networkError.message);
      notifications = null;
    }

    // Fallback: Load from IndexedDB if fetch failed or offline
    if (!notifications) {
      try {
        const cachedData = await getFromIndexedDB('notification_times', 'today-notifications');
        if (cachedData && cachedData.data) {
          notifications = cachedData.data;
          console.log('[Service Worker] Loaded notifications from IndexedDB cache (OFFLINE MODE)');
        }
      } catch (cacheError) {
        console.warn('[Service Worker] Could not load from cache:', cacheError.message);
        notifications = null;
      }
    }

    // No data available (online or offline)
    if (!notifications || notifications.length === 0) {
      console.warn('[Service Worker] No notifications available');
      return;
    }
    
    // Get current time
    const now = new Date();
    const currentTime = now.getHours().toString().padStart(2, '0') + ':' +
                       now.getMinutes().toString().padStart(2, '0');
    
    console.log(`[Service Worker] Current time: ${currentTime}, checking ${notifications.length} notifications`);
    
    // Check each notification
    for (const notif of notifications) {
      if (notif.time === currentTime && !notif.already_taken) {
        // Check if this medication is snoozed
        const isSnoozed = await checkIfSnoozed(notif.id);
        
        if (isSnoozed) {
          console.log(`[Service Worker] Medication ${notif.id} is snoozed, skipping notification`);
          continue;
        }
        
        // Time to notify!
        self.registration.showNotification('💊 Waktu Minum Obat', {
          body: `${notif.medicine_name} (${notif.medicine_dose})`,
          icon: '💊',
          badge: '💊',
          tag: `medication-${notif.id}`,
          requireInteraction: true,
          actions: [
            { action: 'confirm', title: 'Saya sudah minum ✓' },
            { action: 'snooze', title: 'Tunda 5 menit' },
          ],
          // Embed data untuk digunakan saat notification di-click
          data: {
            id: notif.id,
            medicine_name: notif.medicine_name,
            medicine_dose: notif.medicine_dose,
            medicine_unit: notif.medicine_unit || '',
            time: notif.time,
            route: '/app/dashboard',
          },
        });
        
        // Mark as notified (hanya jika online)
        if (navigator.onLine) {
          await markNotificationSent(notif.id);
        } else {
          console.log('[Service Worker] Offline - notification sent locally, will sync later');
        }
      }
    }
  } catch (error) {
    console.error('[Service Worker] Error checking notifications:', error);
  }
}

/**
 * Sync Notification Times from Server
 */
async function syncNotificationTimes() {
  try {
    console.log('[Service Worker] Syncing notification times...');
    
    const response = await fetch('/api/notification-times', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    
    if (!response.ok) throw new Error('Failed to sync notification times');
    
    const data = await response.json();
    
    // Store in IndexedDB for offline access
    await storeInIndexedDB('notification_times', {
      id: 'today-notifications',
      data: data.today,
      timestamp: new Date().toISOString(),
    });
    
    console.log('[Service Worker] Notification times synced');
  } catch (error) {
    console.error('[Service Worker] Error syncing notification times:', error);
  }
}

/**
 * Check Second Reminders and Send If Time Matches
 * Only send second reminders for medications that haven't been confirmed after first reminder
 * Works OFFLINE using cached data from last sync
 */
async function checkSecondReminders() {
  try {
    console.log('[Service Worker] Checking second reminders...');
    
    let secondReminders = null;

    // Try to fetch from server first
    try {
      const response = await fetch('/api/second-reminders', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      
      if (response.ok) {
        const data = await response.json();
        secondReminders = data.second_reminders || [];
        
        // Cache the second reminders in IndexedDB for offline access
        await storeInIndexedDB('notification_times', {
          id: 'second-reminders',
          data: secondReminders,
          timestamp: new Date().toISOString(),
        });
        
        console.log('[Service Worker] Fetched fresh second reminders from server');
      } else {
        console.warn('[Service Worker] Failed to fetch second reminders from server, trying cache...');
        secondReminders = null;
      }
    } catch (networkError) {
      console.warn('[Service Worker] Network error checking second reminders, trying IndexedDB cache...', networkError.message);
      secondReminders = null;
    }

    // Fallback: Load from IndexedDB if fetch failed or offline
    if (!secondReminders) {
      try {
        const cachedData = await getFromIndexedDB('notification_times', 'second-reminders');
        if (cachedData && cachedData.data) {
          secondReminders = cachedData.data;
          console.log('[Service Worker] Loaded second reminders from IndexedDB cache (OFFLINE MODE)');
        }
      } catch (cacheError) {
        console.warn('[Service Worker] Could not load second reminders from cache:', cacheError.message);
        secondReminders = null;
      }
    }

    // No data available
    if (!secondReminders || secondReminders.length === 0) {
      console.log('[Service Worker] No pending second reminders');
      return;
    }
    
    console.log(`[Service Worker] Found ${secondReminders.length} pending second reminders`);
    
    // Show notifications for each pending second reminder
    for (const reminder of secondReminders) {
      // Show second reminder notification
      self.registration.showNotification('💊 Pengingat Kedua - Minum Obat', {
        body: `${reminder.medicine_name} (${reminder.medicine_dose}) - Jangan lupa minum obat Anda!`,
        icon: '⏰',
        badge: '⏰',
        tag: `medication-reminder-2-${reminder.medication_schedule_id}`,
        requireInteraction: true,
        actions: [
          { action: 'confirm', title: 'Saya sudah minum ✓' },
          { action: 'snooze', title: 'Tunda 5 menit' },
        ],
        // Embed data untuk digunakan saat notification di-click
        data: {
          id: reminder.medication_schedule_id,
          notification_log_id: reminder.notification_log_id,
          medicine_name: reminder.medicine_name,
          medicine_dose: reminder.medicine_dose,
          time: reminder.time,
          reminder_type: 'second',
          route: '/app/dashboard',
        },
      });
      
      // Mark second reminder as sent (hanya jika online)
      if (navigator.onLine) {
        await markSecondReminderSent(reminder.notification_log_id);
      } else {
        console.log('[Service Worker] Offline - second reminder sent locally, will sync when online');
      }
    }
  } catch (error) {
    console.error('[Service Worker] Error checking second reminders:', error);
  }
}

/**
 * Mark second reminder as sent
 */
async function markSecondReminderSent(notificationLogId) {
  try {
    await fetch('/api/second-reminder-sent', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        notification_log_id: notificationLogId,
        notification_type: 'browser',
      }),
    });
  } catch (error) {
    console.error('[Service Worker] Error marking second reminder sent:', error);
  }
}

/**
 * Mark notification as sent
 */
async function markNotificationSent(scheduleId) {
  try {
    await fetch('/api/notification-sent', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        medication_schedule_id: scheduleId,
        scheduled_time: new Date().toISOString(),
        notification_type: 'browser',
      }),
    });
  } catch (error) {
    console.error('[Service Worker] Error marking notification sent:', error);
  }
}

/**
 * Sync Pending Logs to Server
 */
async function syncPendingLogs(event = null) {
  try {
    // Get pending logs dari index
    const pendingLogsJson = await getFromIndexedDB('offline_queue', 'pending_logs');
    const pendingLogs = pendingLogsJson ? JSON.parse(pendingLogsJson) : [];
    
    if (pendingLogs.length === 0) {
      console.log('[Service Worker] No pending logs to sync');
      return;
    }
    
    console.log('[Service Worker] Syncing', pendingLogs.length, 'logs');
    
    // Send to server
    const response = await fetch('/api/sync-logs', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ logs: pendingLogs }),
    });
    
    if (!response.ok) {
      throw new Error(`Sync failed: ${response.statusText}`);
    }
    
    // Clear pending logs on success
    await clearFromIndexedDB('offline_queue', 'pending_logs');
    
    // Notify all clients
    notifyClients({
      type: 'SYNC_COMPLETE',
      payload: { count: pendingLogs.length, success: true },
    });
    
    console.log('[Service Worker] Sync successful!');
  } catch (error) {
    console.error('[Service Worker] Sync failed:', error);
    
    // Notify clients about failure
    notifyClients({
      type: 'SYNC_COMPLETE',
      payload: { success: false, error: error.message },
    });
  }
}

/**
 * Get Pending Logs from IndexedDB
 */
async function getPendingLogs(event) {
  try {
    const pendingLogsJson = await getFromIndexedDB('offline_queue', 'pending_logs');
    const pendingLogs = pendingLogsJson ? JSON.parse(pendingLogsJson) : [];
    
    event.ports[0].postMessage({
      type: 'PENDING_LOGS',
      payload: pendingLogs,
    });
  } catch (error) {
    event.ports[0].postMessage({
      type: 'ERROR',
      payload: error.message,
    });
  }
}

/**
 * Clear All Caches
 */
async function clearAllCaches(event) {
  try {
    const cacheNames = await caches.keys();
    await Promise.all(cacheNames.map((name) => caches.delete(name)));
    
    if (event) {
      event.ports[0].postMessage({
        type: 'CACHE_CLEARED',
        payload: { caches_deleted: cacheNames.length },
      });
    }
  } catch (error) {
    console.error('[Service Worker] Error clearing caches:', error);
  }
}

/**
 * Simple IndexedDB helpers (without library)
 */
function getFromIndexedDB(storeName, key) {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('lifecare_db', 1);
    
    request.onsuccess = () => {
      const db = request.result;
      const transaction = db.transaction([storeName], 'readonly');
      const store = transaction.objectStore(storeName);
      const getRequest = store.get(key);
      
      getRequest.onsuccess = () => resolve(getRequest.result?.value);
      getRequest.onerror = () => reject(getRequest.error);
    };
    
    request.onerror = () => reject(request.error);
  });
}

function clearFromIndexedDB(storeName, key) {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('lifecare_db', 1);
    
    request.onsuccess = () => {
      const db = request.result;
      const transaction = db.transaction([storeName], 'readwrite');
      const store = transaction.objectStore(storeName);
      const deleteRequest = store.delete(key);
      
      deleteRequest.onsuccess = () => resolve();
      deleteRequest.onerror = () => reject(deleteRequest.error);
    };
    
    request.onerror = () => reject(request.error);
  });
}

function storeInIndexedDB(storeName, data) {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('lifecare_db', 1);
    
    request.onsuccess = () => {
      const db = request.result;
      const transaction = db.transaction([storeName], 'readwrite');
      const store = transaction.objectStore(storeName);
      const putRequest = store.put(data);
      
      putRequest.onsuccess = () => resolve();
      putRequest.onerror = () => reject(putRequest.error);
    };
    
    request.onerror = () => reject(request.error);
  });
}

/**
 * Notify all clients
 */
function notifyClients(message) {
  self.clients.matchAll().then((clients) => {
    clients.forEach((client) => {
      client.postMessage(message);
    });
  });
}

/**
 * Notification Click Handler: Handle notification actions & clicks
 */
self.addEventListener('notificationclick', (event) => {
  const notification = event.notification;
  const notificationData = notification.data;

  console.log('[Service Worker] Notification clicked:', event.action);
  console.log('[Service Worker] Notification data:', notificationData);

  event.notification.close();

  // Handle action buttons
  if (event.action === 'confirm') {
    console.log('[Service Worker] Action: confirm medication taken');
    // Confirm medication taken
    event.waitUntil(confirmMedicationTaken(notificationData.id));
  } else if (event.action === 'snooze') {
    console.log('[Service Worker] Action: snooze medication');
    // Snooze for 15 minutes
    event.waitUntil(snoozeMedication(notificationData));
  } else {
    console.log('[Service Worker] Action: default click - open app');
    // Default click: open app with modal
    event.waitUntil(
      clients.matchAll({ type: 'window' }).then((clientList) => {
        console.log('[Service Worker] Found', clientList.length, 'client windows');
        
        // Check if app is already open
        for (let i = 0; i < clientList.length; i++) {
          const client = clientList[i];
          const clientPath = new URL(client.url).pathname;
          const targetRoute = notificationData.route || '/app/dashboard';
          
          console.log('[Service Worker] Checking client:', clientPath, 'vs', targetRoute);
          
          if (clientPath.includes('dashboard') && 'focus' in client) {
            // App sudah open, focus dan send message untuk show modal
            console.log('[Service Worker] Focusing existing client window');
            client.focus();
            client.postMessage({
              type: 'SHOW_MEDICATION_MODAL',
              payload: {
                id: notificationData.id,
                medicine_name: notificationData.medicine_name,
                medicine_dose: notificationData.medicine_dose,
                medicine_unit: notificationData.medicine_unit,
                time: notificationData.time,
              },
            });
            console.log('[Service Worker] Posted modal message to client');
            return client;
          }
        }
        
        // App belum open, buka baru dengan query param
        console.log('[Service Worker] Opening new window');
        const routeWithParam = (notificationData.route || '/app/dashboard') + '?medication_id=' + notificationData.id;
        console.log('[Service Worker] Opening route:', routeWithParam);
        return clients.openWindow(routeWithParam);
      })
    );
  }
});

/**
 * Notification Close Handler: Track if user dismissed
 */
self.addEventListener('notificationclose', (event) => {
  console.log('[Service Worker] Notification closed (dismissed):', event.notification.data.id);
  
  // Optionally: track user dismissed notification
  // await logNotificationDismissed(event.notification.data.id);
});

/**
 * Confirm Medication Taken via Notification
 */
async function confirmMedicationTaken(scheduleId) {
  try {
    const response = await fetch('/api/medication-taken', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        medication_schedule_id: scheduleId,
        taken_at: new Date().toISOString(),
        source: 'browser_notification',
      }),
    });

    if (response.ok) {
      notifyClients({
        type: 'MEDICATION_CONFIRMED',
        payload: { schedule_id: scheduleId },
      });
      
      // Show confirmation notification
      self.registration.showNotification('✓ Obat Diminum', {
        body: 'Data minum obat Anda sudah tercatat.',
        icon: '✓',
        badge: '✓',
        tag: 'medication-confirmed',
      });
    }
  } catch (error) {
    console.error('[Service Worker] Error confirming medication:', error);
  }
}

/**
 * Snooze Medication Reminder
 */
async function snoozeMedication(notificationData) {
  try {
    const scheduleId = notificationData.id;
    const response = await fetch('/api/medication-snooze', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        medication_schedule_id: scheduleId,
        snooze_minutes: 5,
      }),
    });

    if (response.ok) {
      // Store full notification payload so it can be shown again after snooze expires
      const snoozeData = await response.json();
      const snoozeUntil = new Date(snoozeData.snoozed_until);
      
      await storeInIndexedDB('snoozes', {
        id: `snooze_${scheduleId}`,
        schedule_id: scheduleId,
        snoozed_until: snoozeUntil.getTime(),
        title: '💊 Waktu Minum Obat',
        body: `${notificationData.medicine_name} (${notificationData.medicine_dose})`,
        icon: '💊',
        badge: '💊',
        tag: `medication-${scheduleId}`,
        route: notificationData.route || '/app/dashboard',
        medicine_name: notificationData.medicine_name,
        medicine_dose: notificationData.medicine_dose,
        medicine_unit: notificationData.medicine_unit || '',
        time: notificationData.time,
      });
    }
  } catch (error) {
    console.error('[Service Worker] Error snoozing medication:', error);
  }
}

/**
 * Re-show reminders whose snooze time has expired.
 */
async function processExpiredSnoozes() {
  try {
    const request = indexedDB.open('lifecare_db', 1);

    request.onsuccess = async () => {
      const db = request.result;
      const transaction = db.transaction(['snoozes'], 'readonly');
      const store = transaction.objectStore('snoozes');
      const getAllRequest = store.getAll();

      getAllRequest.onsuccess = async () => {
        const snoozes = getAllRequest.result || [];
        const now = Date.now();

        for (const snooze of snoozes) {
          if (!snooze?.snoozed_until || now < snooze.snoozed_until) {
            continue;
          }

          self.registration.showNotification(snooze.title || '💊 Waktu Minum Obat', {
            body: snooze.body || 'Waktunya minum obat Anda.',
            icon: snooze.icon || '💊',
            badge: snooze.badge || '💊',
            tag: snooze.tag || `medication-${snooze.schedule_id}`,
            requireInteraction: true,
            actions: [
              { action: 'confirm', title: 'Saya sudah minum ✓' },
              { action: 'snooze', title: 'Tunda 5 menit' },
            ],
            data: {
              id: snooze.schedule_id,
              medicine_name: snooze.medicine_name,
              medicine_dose: snooze.medicine_dose,
              medicine_unit: snooze.medicine_unit || '',
              time: snooze.time,
              route: snooze.route || '/app/dashboard',
            },
          });

          await clearFromIndexedDB('snoozes', snooze.id);
        }
      };

      getAllRequest.onerror = () => {
        console.error('[Service Worker] Error reading snoozed items:', getAllRequest.error);
      };
    };

    request.onerror = () => {
      console.error('[Service Worker] Error opening IndexedDB for snooze processing:', request.error);
    };
  } catch (error) {
    console.error('[Service Worker] Error processing expired snoozes:', error);
  }
}

/**
 * Check if medication is snoozed
 */
async function checkIfSnoozed(scheduleId) {
  try {
    const snoozeData = await getFromIndexedDB('snoozes', `snooze_${scheduleId}`);
    
    if (!snoozeData) return false;
    
    const snoozeUntil = snoozeData.snoozed_until;
    const now = Date.now();
    
    if (now < snoozeUntil) {
      // Still snoozed
      return true;
    } else {
      // Snooze expired, clean up
      await clearFromIndexedDB('snoozes', `snooze_${scheduleId}`);
      return false;
    }
  } catch (error) {
    console.error('[Service Worker] Error checking snooze status:', error);
    return false;
  }
}

/**
 * Initialize IndexedDB stores (snoozes, notification_times, offline_queue)
 */
function initializeIndexedDB() {
  try {
    const request = indexedDB.open('lifecare_db', 1);

    request.onerror = () => {
      console.error('[Service Worker] Failed to open IndexedDB:', request.error);
    };

    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      console.log('[Service Worker] Upgrading IndexedDB...');

      // Create stores if they don't exist
      if (!db.objectStoreNames.contains('snoozes')) {
        db.createObjectStore('snoozes', { keyPath: 'id' });
        console.log('[Service Worker] Created snoozes store');
      }

      if (!db.objectStoreNames.contains('notification_times')) {
        db.createObjectStore('notification_times', { keyPath: 'id' });
        console.log('[Service Worker] Created notification_times store');
      }

      if (!db.objectStoreNames.contains('offline_queue')) {
        db.createObjectStore('offline_queue', { keyPath: 'id' });
        console.log('[Service Worker] Created offline_queue store');
      }
    };

    request.onsuccess = () => {
      console.log('[Service Worker] IndexedDB ready');
      request.result.close();
    };
  } catch (error) {
    console.error('[Service Worker] Error initializing IndexedDB:', error);
  }
}

console.log('[Service Worker] Loaded and ready');

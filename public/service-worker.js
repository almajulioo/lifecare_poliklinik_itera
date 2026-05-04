/**
 * LifeCare+ Service Worker
 * Mengelola cache offline, sinkronisasi latar belakang, dan notifikasi
 */

const CACHE_NAME = 'lifecare-v1';
const API_CACHE = 'lifecare-api-v1';
const RUNTIME_CACHE = 'lifecare-runtime-v1';

// Assets penting yang harus selalu di-cache
const CRITICAL_ASSETS = [
  '/',
  '/manifest.json',
  '/offline.html', // fallback page (akan dibuat nanti)
];

// Endpoint API yang dapat di-cache
const CACHEABLE_APIs = [
  /\/api\/medications\//,
  /\/api\/compliance\//,
  /\/api\/schedules\//,
];

/**
 * Event Install: Cache asset penting
 */
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Memasang...');
  
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[Service Worker] Cache asset penting');
      // Jangan error jika offline - cache yang bisa diakses saja
      return cache.addAll(CRITICAL_ASSETS).catch((err) => {
        console.warn('[Service Worker] Gagal cache semua asset:', err);
      });
    })
  );
  
  // Aktifkan versi baru segera
  self.skipWaiting();
});

/**
 * Event Aktifkan: Bersihkan cache lama
 */
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Mengaktifkan...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => {
            // Hapus versi lama
            return (
              cacheName !== CACHE_NAME &&
              cacheName !== API_CACHE &&
              cacheName !== RUNTIME_CACHE
            );
          })
          .map((cacheName) => {
            console.log('[Service Worker] Hapus cache lama:', cacheName);
            return caches.delete(cacheName);
          })
      );
    })
  );
  
  // Inisialisasi store IndexedDB
  initializeIndexedDB();
  
  // Ambil semua klien segera
  return self.clients.claim();
});

/**
 * Event Fetch: Prioritas jaringan untuk API, cache untuk asset
 */
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Lewati request non-GET
  if (request.method !== 'GET') {
    return;
  }
  
  // Lewati URL asing
  if (url.origin !== location.origin) {
    return;
  }
  
  // API: Prioritas jaringan, fallback ke cache
  if (url.pathname.startsWith('/api/')) {
    return event.respondWith(networkFirstStrategy(request));
  }
  
  // Asset Statis: Prioritas cache, fallback ke jaringan
  if (isAsset(url.pathname)) {
    return event.respondWith(cacheFirstStrategy(request));
  }
  
  // Halaman HTML: Prioritas jaringan, fallback ke cache
  if (request.headers.get('accept')?.includes('text/html')) {
    return event.respondWith(networkFirstStrategy(request));
  }
});

/**
 * Strategi Prioritas Jaringan
 * Coba jaringan dulu, jika gagal gunakan cache
 */
function networkFirstStrategy(request) {
  return fetch(request)
    .then((response) => {
      // Cache response sukses
      if (response.ok) {
        const clone = response.clone();
        
        // Tentukan cache yang digunakan
        const cacheName = request.url.includes('/api/') ? API_CACHE : RUNTIME_CACHE;
        
        caches.open(cacheName).then((cache) => {
          cache.put(request, clone);
        });
      }
      
      return response;
    })
    .catch(() => {
      // Fallback ke cache saat error jaringan
      return caches.match(request).then((cachedResponse) => {
        if (cachedResponse) {
          // Return respons cache
          return cachedResponse;
        }
        
        // Fallback untuk halaman HTML
        if (request.headers.get('accept')?.includes('text/html')) {
          return caches.match('/offline.html');
        }
        
        // Respons offline generik
        return new Response('Offline - Data not available', {
          status: 503,
          statusText: 'Service Unavailable',
        });
      });
    });
}

/**
 * Strategi Prioritas Cache
 * Gunakan cache dulu, jika tidak ada ambil dari jaringan
 */
function cacheFirstStrategy(request) {
  return caches.match(request).then((cachedResponse) => {
    if (cachedResponse) {
      // Return cache yang ada
      return cachedResponse;
    }
    
    // Tidak ada di cache, ambil dari jaringan
    return fetch(request)
      .then((response) => {
        // Cache respons
        const clone = response.clone();
        caches.open(RUNTIME_CACHE).then((cache) => {
          cache.put(request, clone);
        });
        
        return response;
      })
      .catch(() => {
        // Jaringan gagal dan tidak ada di cache
        return new Response('Asset not available offline', {
          status: 503,
          statusText: 'Service Unavailable',
        });
      });
  });
}

/**
 * Helper: Cek jika URL adalah asset (CSS, JS, gambar, font)
 */
function isAsset(pathname) {
  return /\.(css|js|jpg|jpeg|png|gif|svg|woff|woff2|ttf|eot|ico)$/.test(
    pathname
  );
}

/**
 * Handler Pesan: Tangani pesan dari klien
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
 * Sinkronisasi Latar Belakang: Sinkronisasi log offline saat online
 * (Polling jika browser tidak support Background Sync API)
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
 * Sinkronisasi Periodik: Check notifikasi setiap 5 menit
 * Note: Butuh support browser dan izin user
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
 * Cek Notifikasi dan Kirim Jika Waktu Cocok
 */
async function checkNotifications() {
  try {
    console.log('[Service Worker] Cek notifikasi...');
    await processExpiredSnoozes();
    
    let notifications = null;

    // Coba ambil dari server dulu
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
        
        // Cache notifikasi di IndexedDB untuk akses offline
        await storeInIndexedDB('notification_times', {
          id: 'today-notifications',
          data: notifications,
          timestamp: new Date().toISOString(),
        });
        
        console.log('[Service Worker] Ambil notifikasi segar dari server');
      } else {
        console.warn('[Service Worker] Gagal ambil server, coba cache...');
        notifications = null;
      }
    } catch (networkError) {
      console.warn('[Service Worker] Error jaringan, coba cache IndexedDB...', networkError.message);
      notifications = null;
    }

    // Fallback: Ambil dari IndexedDB jika fetch gagal atau offline
    if (!notifications) {
      try {
        const cachedData = await getFromIndexedDB('notification_times', 'today-notifications');
        if (cachedData && cachedData.data) {
          notifications = cachedData.data;
          console.log('[Service Worker] Ambil notifikasi dari cache (MODE OFFLINE)');
        }
      } catch (cacheError) {
        console.warn('[Service Worker] Gagal ambil cache:', cacheError.message);
        notifications = null;
      }
    }

    // Tidak ada data
    if (!notifications || notifications.length === 0) {
      console.warn('[Service Worker] Tidak ada notifikasi');
      return;
    }
    
    // Ambil waktu sekarang
    const now = new Date();
    const currentTime = now.getHours().toString().padStart(2, '0') + ':' +
                       now.getMinutes().toString().padStart(2, '0');
    
    console.log(`[Service Worker] Waktu sekarang: ${currentTime}, cek ${notifications.length} notifikasi`);
    
    // Cek setiap notifikasi
    for (const notif of notifications) {
      if (notif.time === currentTime && !notif.already_taken) {
        // Cek jika obat sedang snooze
        const isSnoozed = await checkIfSnoozed(notif.id);
        
        if (isSnoozed) {
          console.log(`[Service Worker] Obat ${notif.id} sedang snooze, skip notifikasi`);
          continue;
        }
        
        // Waktu beri notifikasi!
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
        
        // Tandai sebagai dikirim (hanya saat online)
        if (navigator.onLine) {
          await markNotificationSent(notif.id);
        } else {
          console.log('[Service Worker] Offline - notifikasi dikirim lokal, sync nanti');
        }
      }
    }
  } catch (error) {
    console.error('[Service Worker] Error checking notifications:', error);
  }
}

/**
 * Sinkronisasi Waktu Notifikasi dari Server
 */
async function syncNotificationTimes() {
  try {
    console.log('[Service Worker] Sinkronisasi waktu notifikasi...');
    
    const response = await fetch('/api/notification-times', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    
    if (!response.ok) throw new Error('Gagal sinkronisasi waktu notifikasi');
    
    const data = await response.json();
    
    // Simpan di IndexedDB untuk akses offline
    await storeInIndexedDB('notification_times', {
      id: 'today-notifications',
      data: data.today,
      timestamp: new Date().toISOString(),
    });
    
    console.log('[Service Worker] Waktu notifikasi tersinkronisasi');
  } catch (error) {
    console.error('[Service Worker] Error syncing notification times:', error);
  }
}

/**
 * Cek Pengingat Kedua dan Kirim Jika Waktu Cocok
 * Hanya kirim pengingat kedua untuk obat yang belum dikonfirmasi
 * Bekerja OFFLINE menggunakan data cache
 */
async function checkSecondReminders() {
  try {
    console.log('[Service Worker] Cek pengingat kedua...');
    
    let secondReminders = null;

    // Coba ambil dari server dulu
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
        
        // Cache pengingat kedua di IndexedDB untuk akses offline
        await storeInIndexedDB('notification_times', {
          id: 'second-reminders',
          data: secondReminders,
          timestamp: new Date().toISOString(),
        });
        
        console.log('[Service Worker] Ambil pengingat kedua segar dari server');
      } else {
        console.warn('[Service Worker] Gagal ambil pengingat server, coba cache...');
        secondReminders = null;
      }
    } catch (networkError) {
      console.warn('[Service Worker] Error jaringan cek pengingat, coba cache...', networkError.message);
      secondReminders = null;
    }

    // Fallback: Ambil dari IndexedDB jika gagal atau offline
    if (!secondReminders) {
      try {
        const cachedData = await getFromIndexedDB('notification_times', 'second-reminders');
        if (cachedData && cachedData.data) {
          secondReminders = cachedData.data;
          console.log('[Service Worker] Ambil pengingat kedua dari cache (MODE OFFLINE)');
        }
      } catch (cacheError) {
        console.warn('[Service Worker] Gagal ambil pengingat dari cache:', cacheError.message);
        secondReminders = null;
      }
    }

    // Tidak ada data
    if (!secondReminders || secondReminders.length === 0) {
      console.log('[Service Worker] Tidak ada pengingat kedua yang tertunda');
      return;
    }
    
    console.log(`[Service Worker] Ditemukan ${secondReminders.length} pengingat kedua tertunda`);
    
    // Tampilkan notifikasi untuk setiap pengingat kedua
    for (const reminder of secondReminders) {
      // Tampilkan notifikasi pengingat kedua
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
      
      // Tandai pengingat kedua dikirim (hanya saat online)
      if (navigator.onLine) {
        await markSecondReminderSent(reminder.notification_log_id);
      } else {
        console.log('[Service Worker] Offline - pengingat kedua dikirim lokal, sync nanti');
      }
    }
  } catch (error) {
    console.error('[Service Worker] Error checking second reminders:', error);
  }
}

/**
 * Tandai pengingat kedua sebagai dikirim
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
 * Tandai notifikasi sebagai dikirim
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
 * Sinkronisasi Log Tertunda ke Server
 */
async function syncPendingLogs(event = null) {
  try {
    // Ambil log tertunda dari IndexedDB
    const pendingLogsJson = await getFromIndexedDB('offline_queue', 'pending_logs');
    const pendingLogs = pendingLogsJson ? JSON.parse(pendingLogsJson) : [];
    
    if (pendingLogs.length === 0) {
      console.log('[Service Worker] Tidak ada log tertunda');
      return;
    }
    
    console.log('[Service Worker] Sinkronisasi', pendingLogs.length, 'log');
    
    // Kirim ke server
    const response = await fetch('/api/sync-logs', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ logs: pendingLogs }),
    });
    
    if (!response.ok) {
      throw new Error(`Sinkronisasi gagal: ${response.statusText}`);
    }
    
    // Hapus log tertunda saat sukses
    await clearFromIndexedDB('offline_queue', 'pending_logs');
    
    // Beritahu semua klien
    notifyClients({
      type: 'SYNC_COMPLETE',
      payload: { count: pendingLogs.length, success: true },
    });
    
    console.log('[Service Worker] Sinkronisasi sukses!');
  } catch (error) {
    console.error('[Service Worker] Sinkronisasi gagal:', error);
    
    // Beritahu klien tentang kegagalan
    notifyClients({
      type: 'SYNC_COMPLETE',
      payload: { success: false, error: error.message },
    });
  }
}

/**
 * Ambil Log Tertunda dari IndexedDB
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
 * Hapus Semua Cache
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
 * Helper IndexedDB Sederhana
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
 * Beritahu semua klien
 */
function notifyClients(message) {
  self.clients.matchAll().then((clients) => {
    clients.forEach((client) => {
      client.postMessage(message);
    });
  });
}

/**
 * Handler Klik Notifikasi: Tangani aksi dan klik notifikasi
 */
self.addEventListener('notificationclick', (event) => {
  const notification = event.notification;
  const notificationData = notification.data;

  console.log('[Service Worker] Notification clicked:', event.action);
  console.log('[Service Worker] Notification data:', notificationData);

  event.notification.close();

  // Tangani tombol aksi
  if (event.action === 'confirm') {
    console.log('[Service Worker] Aksi: konfirmasi obat sudah diminum');
    // Konfirmasi obat sudah diminum
    event.waitUntil(confirmMedicationTaken(notificationData.id));
  } else if (event.action === 'snooze') {
    console.log('[Service Worker] Aksi: snooze obat');
    // Snooze 5 menit
    event.waitUntil(snoozeMedication(notificationData));
  } else {
    console.log('[Service Worker] Aksi: buka app');
    // Klik default: buka app
    event.waitUntil(
      clients.matchAll({ type: 'window' }).then((clientList) => {
        console.log('[Service Worker] Ditemukan', clientList.length, 'window klien');
        
        // Cek jika app sudah buka
        for (let i = 0; i < clientList.length; i++) {
          const client = clientList[i];
          const clientPath = new URL(client.url).pathname;
          const targetRoute = notificationData.route || '/app/dashboard';
          
          console.log('[Service Worker] Cek klien:', clientPath, 'vs', targetRoute);
          
          if (clientPath.includes('dashboard') && 'focus' in client) {
            // App sudah buka, fokus dan kirim pesan untuk modal
            console.log('[Service Worker] Fokus window klien yang ada');
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
            console.log('[Service Worker] Kirim pesan modal ke klien');
            return client;
          }
        }
        
        // App belum buka, buka window baru
        console.log('[Service Worker] Buka window baru');
        const routeWithParam = (notificationData.route || '/app/dashboard') + '?medication_id=' + notificationData.id;
        console.log('[Service Worker] Buka rute:', routeWithParam);
        return clients.openWindow(routeWithParam);
      })
    );
  }
});

/**
 * Handler Tutup Notifikasi: Catat jika user tutup
 */
self.addEventListener('notificationclose', (event) => {
  console.log('[Service Worker] Notifikasi ditutup:', event.notification.data.id);
  
  // Opsional: catat user tutup notifikasi
  // await logNotificationDismissed(event.notification.data.id);
});

/**
 * Konfirmasi Obat Sudah Diminum via Notifikasi
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
      
      // Tampilkan notifikasi konfirmasi
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
 * Snooze Pengingat Obat
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
      // Simpan notifikasi lengkap untuk ditampilkan lagi setelah snooze berakhir
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
 * Tampilkan ulang pengingat yang snooze-nya berakhir
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
        console.error('[Service Worker] Error baca item snooze:', getAllRequest.error);
      };
    };

    request.onerror = () => {
      console.error('[Service Worker] Error buka IndexedDB snooze:', request.error);
    };
  } catch (error) {
    console.error('[Service Worker] Error processing expired snoozes:', error);
  }
}

/**
 * Cek jika obat sedang snooze
 */
async function checkIfSnoozed(scheduleId) {
  try {
    const snoozeData = await getFromIndexedDB('snoozes', `snooze_${scheduleId}`);
    
    if (!snoozeData) return false;
    
    const snoozeUntil = snoozeData.snoozed_until;
    const now = Date.now();
    
    if (now < snoozeUntil) {
      // Masih snooze
      return true;
    } else {
      // Snooze berakhir, bersihkan
      await clearFromIndexedDB('snoozes', `snooze_${scheduleId}`);
      return false;
    }
  } catch (error) {
    console.error('[Service Worker] Error checking snooze status:', error);
    return false;
  }
}

/**
 * Inisialisasi store IndexedDB
 */
function initializeIndexedDB() {
  try {
    const request = indexedDB.open('lifecare_db', 1);

    request.onerror = () => {
      console.error('[Service Worker] Gagal buka IndexedDB:', request.error);
    };

    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      console.log('[Service Worker] Upgrade IndexedDB...');

      // Buat store jika belum ada
      if (!db.objectStoreNames.contains('snoozes')) {
        db.createObjectStore('snoozes', { keyPath: 'id' });
        console.log('[Service Worker] Buat store snoozes');
      }

      if (!db.objectStoreNames.contains('notification_times')) {
        db.createObjectStore('notification_times', { keyPath: 'id' });
        console.log('[Service Worker] Buat store notification_times');
      }

      if (!db.objectStoreNames.contains('offline_queue')) {
        db.createObjectStore('offline_queue', { keyPath: 'id' });
        console.log('[Service Worker] Buat store offline_queue');
      }
    };

    request.onsuccess = () => {
      console.log('[Service Worker] IndexedDB siap');
      request.result.close();
    };
  } catch (error) {
    console.error('[Service Worker] Error inisialisasi IndexedDB:', error);
  }
}

console.log('[Service Worker] Dimuat dan siap');

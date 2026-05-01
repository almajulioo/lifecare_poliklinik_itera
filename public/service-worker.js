/**
 * LifeCare+ Service Worker
 * Handles offline caching, background sync, and notifications
 * 
 * ⚠️ IMPORTANT: This file must be in public/ folder to be served correctly
 * It is NOT bundled by Vite
 */

const CACHE_NAME = 'lifecare-v1';
const API_CACHE = 'lifecare-api-v1';
const RUNTIME_CACHE = 'lifecare-runtime-v1';

// Assets yang harus selalu tersedia (cache critical)
// Include dashboard path so it can be accessed offline after first load
const CRITICAL_ASSETS = [
  '/',
  '/app/dashboard',
  '/manifest.json',
  '/offline.html',
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
      console.log('[Service Worker] Caching critical assets:', CRITICAL_ASSETS);
      
      // Cache assets one by one to handle failures individually
      return Promise.all(
        CRITICAL_ASSETS.map((asset) =>
          cache.add(asset).catch((err) => {
            console.warn(`[Service Worker] Failed to cache ${asset}:`, err);
          })
        )
      );
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
  
  // Static Assets (CSS, JS, images, fonts): Cache-first, fallback to network
  if (isAsset(url.pathname)) {
    return event.respondWith(cacheFirstStrategy(request));
  }
  
  // HTML Pages (including /app/dashboard): Network-first, fallback to cache
  if (request.headers.get('accept')?.includes('text/html')) {
    return event.respondWith(networkFirstStrategy(request));
  }
});

/**
 * Network-First Strategy
 * Try network first, if fails use cache
 * Good for HTML pages and API that change often
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
    .catch((error) => {
      console.warn('[Service Worker] Network request failed for:', request.url, error);
      
      // Fall back to cache on network error
      return caches.match(request).then((cachedResponse) => {
        if (cachedResponse) {
          console.log('[Service Worker] Using cached response for:', request.url);
          return cachedResponse;
        }
        
        // Fallback for HTML pages - show offline page
        if (request.headers.get('accept')?.includes('text/html')) {
          console.log('[Service Worker] Showing offline page for:', request.url);
          return caches.match('/offline.html').catch(() => {
            return new Response(getOfflineHTML(), {
              headers: { 'Content-Type': 'text/html' },
            });
          });
        }
        
        // Generic offline response for other resources
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
 * Good for static assets like CSS, JS, images
 */
function cacheFirstStrategy(request) {
  return caches.match(request).then((cachedResponse) => {
    if (cachedResponse) {
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
 * Helper: Check if URL is static asset
 */
function isAsset(pathname) {
  return /\.(css|js|jpg|jpeg|png|gif|svg|woff|woff2|ttf|eot|ico|webp)$/.test(
    pathname.toLowerCase()
  );
}

/**
 * Fallback offline HTML (in case /offline.html not in cache)
 */
function getOfflineHTML() {
  return `
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Offline - LifeCare+</title>
      <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          min-height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 20px;
        }
        .offline-container {
          background: white;
          border-radius: 16px;
          padding: 40px 20px;
          max-width: 400px;
          text-align: center;
          box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        h1 { color: #333; font-size: 24px; margin-bottom: 10px; }
        .message { color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 30px; }
        .info-box {
          background: #f0f7ff;
          border-left: 4px solid #667eea;
          padding: 15px;
          text-align: left;
          margin-bottom: 20px;
          border-radius: 8px;
        }
        .retry-btn {
          background: #667eea;
          color: white;
          border: none;
          padding: 12px 24px;
          border-radius: 8px;
          font-size: 16px;
          cursor: pointer;
          margin-top: 20px;
        }
        .retry-btn:hover { background: #5568d3; }
      </style>
    </head>
    <body>
      <div class="offline-container">
        <h1>📡 Offline Mode</h1>
        <p class="message">
          Anda sedang offline. Data akan disinkronkan otomatis saat kembali online.
        </p>
        <div class="info-box">
          <strong>Apa yang bisa Anda lakukan:</strong>
          <ul style="text-align: left; margin-top: 10px;">
            <li>✓ Lihat jadwal yang sudah pernah dimuat</li>
            <li>✓ Tandai obat yang sudah diminum</li>
            <li>✓ Data akan tersimpan dan tersinkronkan</li>
          </ul>
        </div>
        <button class="retry-btn" onclick="location.reload()">🔄 Coba Muat Ulang</button>
      </div>
    </body>
    </html>
  `;
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
 * Background Sync
 */
self.addEventListener('sync', (event) => {
  console.log('[Service Worker] Background sync event:', event.tag);
  
  if (event.tag === 'sync-logs') {
    event.waitUntil(syncPendingLogs());
  }
  
  if (event.tag === 'sync-notifications') {
    event.waitUntil(syncNotificationTimes());
  }
});

/**
 * Periodic Background Sync
 */
self.addEventListener('periodicsync', (event) => {
  console.log('[Service Worker] Periodic sync event:', event.tag);
  
  if (event.tag === 'check-notifications') {
    event.waitUntil(checkNotifications());
  }
  
  if (event.tag === 'check-second-reminders') {
    event.waitUntil(checkSecondReminders());
  }
});

/**
 * Placeholder functions - implement as needed
 */
async function getPendingLogs(event) {
  console.log('[Service Worker] getPendingLogs called');
  // Implementation here
}

async function syncPendingLogs(event) {
  console.log('[Service Worker] syncPendingLogs called');
  // Implementation here
}

async function clearAllCaches(event) {
  console.log('[Service Worker] clearAllCaches called');
  const cacheNames = await caches.keys();
  await Promise.all(cacheNames.map(cacheName => caches.delete(cacheName)));
}

async function syncNotificationTimes() {
  console.log('[Service Worker] syncNotificationTimes called');
  // Implementation here
}

async function checkNotifications() {
  console.log('[Service Worker] checkNotifications called');
  // Implementation here
}

async function checkSecondReminders() {
  console.log('[Service Worker] checkSecondReminders called');
  // Implementation here
}

function initializeIndexedDB() {
  console.log('[Service Worker] Initializing IndexedDB');
  // IndexedDB setup if needed
}

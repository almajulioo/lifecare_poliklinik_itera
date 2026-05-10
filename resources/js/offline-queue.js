/**
 * LifeCare+ Offline Queue - Enhanced
 * Manages queuing of actions (medication confirmations) when offline
 * Features:
 * - Conflict detection (duplicate confirmations)
 * - Retry logic with exponential backoff
 * - Offline history tracking
 * - Better error messages
 */

class OfflineQueue {
  constructor() {
    this.isInitialized = false;
    this.pendingItems = [];
    this.syncHistory = [];
    this.retryConfig = {
      maxRetries: 3,
      baseDelay: 1000, // 1 second
      maxDelay: 30000,  // 30 seconds
    };
    this.init();
  }

  async init() {
    // Initialize IndexedDB
    this.db = await this.initIndexedDB();
    this.isInitialized = true;
    
    // Load pending items and history from DB
    await this.loadPendingItems();
    await this.loadSyncHistory();
    
    // Listen for offline/online events
    window.addEventListener('online', () => this.onOnline());
    
    // Listen for service worker messages
    // if ('serviceWorker' in navigator) {
    //   navigator.serviceWorker.addEventListener('message', (event) => {
    //     const { type, payload } = event.data;
        
    //     if (type === 'SYNC_COMPLETE') {
    //       this.handleSyncComplete(payload);
    //     }
    //   });
    // }
    
    console.log('[Offline Queue] Initialized with retry config:', this.retryConfig);
  }

  async initIndexedDB() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open('lifecare_db', 2);
      
      request.onerror = () => reject(request.error);
      request.onsuccess = () => {
        const db = request.result;
        resolve(db);
      };
      
      request.onupgradeneeded = (event) => {
        const db = event.target.result;
        
        // Create object stores if they don't exist
        if (!db.objectStoreNames.contains('offline_queue')) {
          const queueStore = db.createObjectStore('offline_queue', { keyPath: 'offlineId' });
          queueStore.createIndex('synced', 'synced', { unique: false });
          queueStore.createIndex('type', 'type', { unique: false });
          queueStore.createIndex('queuedAt', 'queuedAt', { unique: false });
        }
        
        if (!db.objectStoreNames.contains('sync_data')) {
          const syncStore = db.createObjectStore('sync_data', { keyPath: 'key' });
          syncStore.add({ key: 'pending_count', value: 0 });
        }

        // New store for sync history
        if (!db.objectStoreNames.contains('sync_history')) {
          const historyStore = db.createObjectStore('sync_history', { keyPath: 'id', autoIncrement: true });
          historyStore.createIndex('timestamp', 'timestamp', { unique: false });
          historyStore.createIndex('status', 'status', { unique: false });
        }
      };
    });
  }

  async addToQueue(item) {
    if (!this.isInitialized) {
      console.warn('[Offline Queue] Not yet initialized');
      return null;
    }

    // Check for conflicts BEFORE adding
    const conflict = await this.detectConflict(item);
    if (conflict) {
      console.warn('[Offline Queue] Conflict detected:', conflict);
      
      // Show user warning
      this.showConflictWarning(conflict);
      
      // Still queue it but mark as conflicted
      item.hasConflict = true;
      item.conflictWith = conflict;
    }

    // Generate offline ID if not present
    if (!item.offlineId) {
      item.offlineId = `offline-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    }

    // Add metadata
    item.queuedAt = new Date().toISOString();
    item.synced = false;
    item.retryCount = 0;
    item.lastError = null;
    item.lastRetryAt = null;

    // Save to IndexedDB
    try {
      await this.saveToIndexedDB(item);
      this.pendingItems.push(item);
      
      console.log('[Offline Queue] Item added to queue:', item);
      
      // Update UI
      this.updateQueueIndicator();
      
      // Broadcast to all tabs/windows via localStorage
      this.broadcastQueueUpdate();
      
      return item.offlineId;
    } catch (err) {
      console.error('[Offline Queue] Error adding to queue:', err);
      return null;
    }
  }

  /**
   * Detect conflicts: Same medication_schedule_id within 1 hour offline
   */
  async detectConflict(newItem) {
    if (newItem.type !== 'medication_log') {
      return null; // Only check medication logs
    }

    const now = Date.now();
    const oneHourAgo = now - (60 * 60 * 1000);

    for (const item of this.pendingItems) {
      if (item.type !== 'medication_log') continue;
      if (item.data?.medication_schedule_id !== newItem.data?.medication_schedule_id) continue;
      
      const itemTime = new Date(item.queuedAt).getTime();
      
      // Check if within last hour
      if (itemTime >= oneHourAgo && itemTime <= now) {
        return {
          type: 'duplicate_confirmation',
          message: `Anda sudah konfirmasi obat ini pada ${new Date(item.queuedAt).toLocaleTimeString()}`,
          existingItem: item.offlineId,
          newItem: newItem.offlineId,
        };
      }
    }

    return null;
  }

  showConflictWarning(conflict) {
    const warning = document.createElement('div');
    warning.className = 'fixed top-20 left-4 right-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded';
    warning.innerHTML = `
      <div class="flex">
        <div class="flex-shrink-0">⚠️</div>
        <div class="ml-3">
          <p class="text-sm font-medium text-yellow-800">${conflict.message}</p>
        </div>
      </div>
    `;
    
    document.body.appendChild(warning);
    
    setTimeout(() => {
      warning.remove();
    }, 5000);
  }

  async saveToIndexedDB(item) {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(['offline_queue'], 'readwrite');
      const store = transaction.objectStore('offline_queue');
      const request = store.put(item);
      
      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    });
  }

  async loadPendingItems() {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(['offline_queue'], 'readonly');
      const store = transaction.objectStore('offline_queue');
      const request = store.getAll();
      
      request.onsuccess = () => {
        this.pendingItems = request.result.filter((item) => !item.synced);
        console.log('[Offline Queue] Loaded', this.pendingItems.length, 'pending items');
        this.updateQueueIndicator();
        resolve();
      };
      
      request.onerror = () => reject(request.error);
    });
  }

  async loadSyncHistory() {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(['sync_history'], 'readonly');
      const store = transaction.objectStore('sync_history');
      const index = store.index('timestamp');
      
      // Get last 50 history entries
      const range = IDBKeyRange.lowerBound(Date.now() - (24 * 60 * 60 * 1000)); // Last 24 hours
      const request = index.getAll(range);
      
      request.onsuccess = () => {
        this.syncHistory = request.result || [];
        console.log('[Offline Queue] Loaded', this.syncHistory.length, 'sync history entries');
        resolve();
      };
      
      request.onerror = () => {
        // Ignore error if history store doesn't exist yet
        console.warn('[Offline Queue] Could not load sync history');
        resolve();
      };
    });
  }

  async addToSyncHistory(record) {
    try {
      const transaction = this.db.transaction(['sync_history'], 'readwrite');
      const store = transaction.objectStore('sync_history');
      
      return new Promise((resolve, reject) => {
        const request = store.add({
          ...record,
          timestamp: Date.now(),
        });
        
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
      });
    } catch (err) {
      console.warn('[Offline Queue] Error adding to sync history:', err);
    }
  }

  async sync() {
    if (this.pendingItems.length === 0) {
      console.log('[Offline Queue] Nothing to sync');
      return;
    }

    console.log('[Offline Queue] Syncing', this.pendingItems.length, 'items...');
    
    try {
      // Group items by type (medication logs, etc)
      const itemsByType = this.groupByType(this.pendingItems);
      
      // Send to server with retry logic
      for (const [type, items] of Object.entries(itemsByType)) {
        await this.syncItemsWithRetry(type, items, 0);
      }
      
      console.log('[Offline Queue] Sync complete');
    } catch (err) {
      console.error('[Offline Queue] Sync failed:', err);
    }
  }

  /**
   * Sync with retry logic and exponential backoff
   */
  async syncItemsWithRetry(type, items, retryAttempt = 0) {
    try {
      const result = await this.syncItemsByType(type, items);
      
      // Mark successfully synced items
      for (const item of items) {
        if (result.synced?.includes(item.offlineId)) {
          await this.markAsSynced(item.offlineId);
          
          // Add to history
          await this.addToSyncHistory({
            item_id: item.offlineId,
            type: 'medication_log',
            status: 'success',
            attempt: retryAttempt + 1,
          });
        } else if (result.conflicts?.includes(item.offlineId)) {
          // Conflict on server (already taken), mark as synced anyway
          await this.markAsSynced(item.offlineId);
          
          await this.addToSyncHistory({
            item_id: item.offlineId,
            type: 'medication_log',
            status: 'conflict_resolved',
            attempt: retryAttempt + 1,
          });
        } else if (result.errors?.[item.offlineId]) {
          // Retry logic
          throw new Error(`Item ${item.offlineId}: ${result.errors[item.offlineId]}`);
        }
      }
    } catch (error) {
      // Check if should retry
      if (retryAttempt < this.retryConfig.maxRetries) {
        const delay = Math.min(
          this.retryConfig.baseDelay * Math.pow(2, retryAttempt),
          this.retryConfig.maxDelay
        );
        
        console.log(`[Offline Queue] Retry attempt ${retryAttempt + 1}/${this.retryConfig.maxRetries} after ${delay}ms`);
        
        // Update items with retry info
        for (const item of items) {
          await this.updateItemRetry(item.offlineId, retryAttempt + 1, error.message);
        }
        
        // Add to history
        await this.addToSyncHistory({
          type: type,
          status: 'retry',
          attempt: retryAttempt + 1,
          error: error.message,
        });
        
        // Wait then retry
        await new Promise(resolve => setTimeout(resolve, delay));
        return this.syncItemsWithRetry(type, items, retryAttempt + 1);
      } else {
        // Max retries exceeded
        console.error('[Offline Queue] Max retries exceeded:', error);
        
        for (const item of items) {
          await this.updateItemRetry(item.offlineId, retryAttempt, error.message);
        }
        
        await this.addToSyncHistory({
          type: type,
          status: 'failed',
          attempt: retryAttempt,
          error: error.message,
        });
        
        // Show error to user
        this.showSyncError(error, items.length);
      }
    }
  }

  async updateItemRetry(offlineId, retryCount, lastError) {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(['offline_queue'], 'readwrite');
      const store = transaction.objectStore('offline_queue');
      const request = store.get(offlineId);
      
      request.onsuccess = () => {
        const item = request.result;
        if (item) {
          item.retryCount = retryCount;
          item.lastError = lastError;
          item.lastRetryAt = new Date().toISOString();
          store.put(item);
        }
      };
      
      request.onerror = () => reject(request.error);
      transaction.oncomplete = () => resolve();
    });
  }

  showSyncError(error, itemCount) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'fixed bottom-32 left-4 right-4 bg-red-50 border-l-4 border-red-400 p-4 rounded';
    errorDiv.innerHTML = `
      <div class="flex">
        <div class="flex-shrink-0">❌</div>
        <div class="ml-3">
          <p class="text-sm font-medium text-red-800">Gagal sync ${itemCount} item(s)</p>
          <p class="text-xs text-red-700 mt-1">${error.message}</p>
          <p class="text-xs text-red-600 mt-2">Akan coba lagi saat online</p>
        </div>
      </div>
    `;
    
    document.body.appendChild(errorDiv);
    
    setTimeout(() => {
      errorDiv.remove();
    }, 8000);
  }

  groupByType(items) {
    return items.reduce((acc, item) => {
      const type = item.type || 'default';
      if (!acc[type]) acc[type] = [];
      acc[type].push(item);
      return acc;
    }, {});
  }

  async syncItemsByType(type, items) {
    if (type === 'medication_log') {
      return this.syncMedicationLogs(items);
    }
    // Add more types as needed
  }

  async syncMedicationLogs(items) {
    const response = await fetch('/api/sync-medication-logs', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ logs: items }),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const result = await response.json();

    // Result should contain:
    // {
    //   success: true,
    //   synced: [offline-ids],
    //   conflicts: [offline-ids],  // Already taken on server
    //   errors: { 'offline-id': 'error message' }
    // }

    return {
      synced: result.synced || [],
      conflicts: result.conflicts || [],
      errors: result.errors || {},
    };
  }

  async markAsSynced(offlineId) {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(['offline_queue'], 'readwrite');
      const store = transaction.objectStore('offline_queue');
      const request = store.get(offlineId);
      
      request.onsuccess = () => {
        const item = request.result;
        if (item) {
          item.synced = true;
          item.syncedAt = new Date().toISOString();
          store.put(item);
        }
      };
      
      request.onerror = () => reject(request.error);
      
      transaction.oncomplete = () => {
        // Remove from pending
        this.pendingItems = this.pendingItems.filter((item) => item.offlineId !== offlineId);
        this.updateQueueIndicator();
        resolve();
      };
    });
  }

  onOnline() {
    console.log('[Offline Queue] Online - attempting sync');
    this.sync();
  }

  handleSyncComplete(payload) {
    console.log('[Offline Queue] Sync complete:', payload);
    
    if (payload.success) {
      this.loadPendingItems();
      this.broadcastQueueUpdate();
    }
  }

  updateQueueIndicator() {
    // Update UI indicator if exists
    const indicator = document.getElementById('offline-queue-indicator');
    const count = this.pendingItems.length;
    
    if (indicator) {
      if (count > 0) {
        indicator.style.display = 'flex';
        indicator.querySelector('[data-count]')?.setAttribute('data-count', count);
      } else {
        indicator.style.display = 'none';
      }
    }
  }

  broadcastQueueUpdate() {
    // Broadcast to other tabs/windows
    localStorage.setItem('offline_queue_update', JSON.stringify({
      timestamp: Date.now(),
      count: this.pendingItems.length,
    }));
  }

  getPendingCount() {
    return this.pendingItems.length;
  }

  getPendingItems() {
    return [...this.pendingItems];
  }

  static getInstance() {
    if (!window.__OfflineQueueInstance) {
      window.__OfflineQueueInstance = new OfflineQueue();
    }
    return window.__OfflineQueueInstance;
  }
}

// Initialize
const offlineQueue = OfflineQueue.getInstance();

// Export for global access
window.OfflineQueue = OfflineQueue;
window.offlineQueue = offlineQueue;

/**
 * Offline History Manager
 * Tracks and manages local history of medication confirmations
 * Useful for debugging and showing user sync status
 */

class OfflineHistoryManager {
  constructor() {
    this.db = null;
    this.init();
  }

  async init() {
    this.db = await this.getDB();
    console.log('[OfflineHistory] Initialized');
  }

  async getDB() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open('lifecare_db', 2);
      
      request.onerror = () => reject(request.error);
      request.onsuccess = () => resolve(request.result);
      
      request.onupgradeneeded = (event) => {
        const db = event.target.result;
        
        if (!db.objectStoreNames.contains('offline_history')) {
          const historyStore = db.createObjectStore('offline_history', { keyPath: 'id', autoIncrement: true });
          historyStore.createIndex('timestamp', 'timestamp', { unique: false });
          historyStore.createIndex('type', 'type', { unique: false });
          historyStore.createIndex('status', 'status', { unique: false });
        }
      };
    });
  }

  /**
   * Add history entry
   */
  async addEntry(entry) {
    if (!this.db) return;

    try {
      const transaction = this.db.transaction(['offline_history'], 'readwrite');
      const store = transaction.objectStore('offline_history');
      
      return new Promise((resolve, reject) => {
        const request = store.add({
          ...entry,
          timestamp: Date.now(),
        });
        
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
      });
    } catch (err) {
      console.warn('[OfflineHistory] Error adding entry:', err);
    }
  }

  /**
   * Get history entries
   */
  async getEntries(filters = {}) {
    if (!this.db) return [];

    try {
      const transaction = this.db.transaction(['offline_history'], 'readonly');
      const store = transaction.objectStore('offline_history');
      
      return new Promise((resolve, reject) => {
        let request;
        
        if (filters.type) {
          const index = store.index('type');
          request = index.getAll(filters.type);
        } else {
          request = store.getAll();
        }
        
        request.onsuccess = () => {
          let results = request.result || [];
          
          // Apply filters
          if (filters.status) {
            results = results.filter(r => r.status === filters.status);
          }
          
          if (filters.hoursAgo) {
            const cutoff = Date.now() - (filters.hoursAgo * 60 * 60 * 1000);
            results = results.filter(r => r.timestamp >= cutoff);
          }
          
          // Sort by timestamp desc
          results.sort((a, b) => b.timestamp - a.timestamp);
          
          resolve(results);
        };
        
        request.onerror = () => reject(request.error);
      });
    } catch (err) {
      console.error('[OfflineHistory] Error getting entries:', err);
      return [];
    }
  }

  /**
   * Get summary stats
   */
  async getSummary() {
    if (!this.db) return {};

    try {
      const entries = await this.getEntries();
      
      const summary = {
        total: entries.length,
        synced: entries.filter(e => e.status === 'success').length,
        failed: entries.filter(e => e.status === 'failed').length,
        pending: entries.filter(e => e.status === 'pending').length,
        conflicts: entries.filter(e => e.status === 'conflict_resolved').length,
      };
      
      return summary;
    } catch (err) {
      console.error('[OfflineHistory] Error getting summary:', err);
      return {};
    }
  }

  /**
   * Clear old history (older than X days)
   */
  async clearOldHistory(days = 30) {
    if (!this.db) return;

    try {
      const transaction = this.db.transaction(['offline_history'], 'readwrite');
      const store = transaction.objectStore('offline_history');
      const index = store.index('timestamp');
      
      const cutoffTime = Date.now() - (days * 24 * 60 * 60 * 1000);
      const range = IDBKeyRange.upperBound(cutoffTime);
      
      return new Promise((resolve, reject) => {
        const request = index.openCursor(range);
        
        request.onsuccess = (event) => {
          const cursor = event.target.result;
          if (cursor) {
            store.delete(cursor.primaryKey);
            cursor.continue();
          } else {
            resolve();
          }
        };
        
        request.onerror = () => reject(request.error);
      });
    } catch (err) {
      console.error('[OfflineHistory] Error clearing old history:', err);
    }
  }

  static getInstance() {
    if (!window.__OfflineHistoryInstance) {
      window.__OfflineHistoryInstance = new OfflineHistoryManager();
    }
    return window.__OfflineHistoryInstance;
  }
}

// Initialize
const offlineHistoryManager = OfflineHistoryManager.getInstance();

// Export for global access
window.OfflineHistoryManager = OfflineHistoryManager;
window.offlineHistoryManager = offlineHistoryManager;

/**
 * Notification Scheduler
 * 
 * Schedules and manages medication reminder notifications based on schedule times
 * Handles do-not-disturb, snoozed items, and user preferences
 */

class NotificationScheduler {
    constructor() {
        this.scheduledNotifications = new Map();
        this.preferences = null;
        this.notificationTimes = [];
        this.checkInterval = null;
        this.isOnline = navigator.onLine;

        // Listen for online/offline
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.syncNotificationState();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
        });

        // Initialize on load
        this.init();
    }

    /**
     * Initialize scheduler
     */
    async init() {
        console.log('[NotificationScheduler] Initializing...');
        
        try {
            // Load preferences from API
            await this.loadPreferences();
            
            // Load today's notification times
            await this.loadNotificationTimes();
            
            // Start checking for time matches every minute
            this.startScheduleCheck();
            
            console.log('[NotificationScheduler] Ready');
        } catch (error) {
            console.error('[NotificationScheduler] Init error:', error);
        }
    }

    /**
     * Load user notification preferences
     */
    async loadPreferences() {
        try {
            const response = await fetch('/api/notification-preferences');
            if (!response.ok) throw new Error('Failed to load preferences');
            
            const data = await response.json();
            this.preferences = data.preferences;
            
            // Also store in localStorage for offline access
            localStorage.setItem('notification_preferences', JSON.stringify(this.preferences));
            
            console.log('[NotificationScheduler] Preferences loaded:', this.preferences);
        } catch (error) {
            console.error('[NotificationScheduler] Error loading preferences:', error);
            
            // Try to load from localStorage
            const stored = localStorage.getItem('notification_preferences');
            if (stored) {
                this.preferences = JSON.parse(stored);
                console.log('[NotificationScheduler] Using stored preferences');
            } else {
                // Use defaults
                this.preferences = {
                    enabled: true,
                    dnd_start: '22:00',
                    dnd_end: '08:00',
                    sound_enabled: true,
                    advance_minutes: 0,
                    vibration_enabled: true,
                };
            }
        }
    }

    /**
     * Load today's notification times from API
     */
    async loadNotificationTimes() {
        try {
            const response = await fetch('/api/notification-times', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({}),
            });

            if (!response.ok) throw new Error('Failed to load notification times');
            
            const data = await response.json();
            this.notificationTimes = data.today || [];
            
            console.log(`[NotificationScheduler] Loaded ${this.notificationTimes.length} notifications for today`);
            
            // Store in IndexedDB for offline access
            this.storeNotificationTimesOffline(this.notificationTimes);
        } catch (error) {
            console.error('[NotificationScheduler] Error loading notification times:', error);
            
            // Try to load from IndexedDB
            const stored = await this.loadNotificationTimesOffline();
            this.notificationTimes = stored;
            console.log('[NotificationScheduler] Using offline notification times');
        }
    }

    /**
     * Store notification times in IndexedDB for offline access
     */
    async storeNotificationTimesOffline(times) {
        try {
            const db = await this.getDB();
            const tx = db.transaction('notification_times', 'readwrite');
            const store = tx.objectStore('notification_times');
            
            // Clear old entries for today
            await store.delete(`today-${new Date().toDateString()}`);
            
            // Store new entries
            await store.add({
                id: `today-${new Date().toDateString()}`,
                date: new Date().toDateString(),
                times: times,
            });
        } catch (error) {
            console.error('[NotificationScheduler] Error storing notification times:', error);
        }
    }

    /**
     * Load notification times from IndexedDB
     */
    async loadNotificationTimesOffline() {
        try {
            const db = await this.getDB();
            const tx = db.transaction('notification_times', 'readonly');
            const store = tx.objectStore('notification_times');
            
            const key = `today-${new Date().toDateString()}`;
            const entry = await store.get(key);
            
            return entry?.times || [];
        } catch (error) {
            console.error('[NotificationScheduler] Error loading offline notification times:', error);
            return [];
        }
    }

    /**
     * Get or create IndexedDB instance
     */
    async getDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open('lifecare_notifications', 1);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
            
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                if (!db.objectStoreNames.contains('notification_times')) {
                    db.createObjectStore('notification_times', { keyPath: 'id' });
                }
                
                if (!db.objectStoreNames.contains('snoozed_items')) {
                    db.createObjectStore('snoozed_items', { keyPath: 'id' });
                }
            };
        });
    }

    /**
     * Start checking for notification times
     * Runs every minute to check if it's time to notify
     */
    startScheduleCheck() {
        if (this.checkInterval) clearInterval(this.checkInterval);
        
        // Check immediately
        this.checkAndNotify();
        
        // Then check every minute
        this.checkInterval = setInterval(() => {
            this.checkAndNotify();
        }, 60000); // 60 seconds
    }

    /**
     * Check current time and send notifications if match
     */
    async checkAndNotify() {
        if (!this.preferences?.enabled) {
            console.log('[NotificationScheduler] Notifications disabled');
            return;
        }

        const now = new Date();
        const currentTime = now.getHours().toString().padStart(2, '0') + ':' +
                           now.getMinutes().toString().padStart(2, '0');

        console.log(`[NotificationScheduler] Checking at ${currentTime}...`);

        // Check if in do-not-disturb window
        if (this.isInDoNotDisturbWindow(currentTime)) {
            console.log('[NotificationScheduler] In do-not-disturb window, skipping notifications');
            return;
        }

        // Check each notification time
        for (const notif of this.notificationTimes) {
            const advanceTime = this.subtractMinutes(notif.time, this.preferences.advance_minutes);
            
            if (advanceTime === currentTime && !this.isAlreadyNotified(notif.id)) {
                console.log('[NotificationScheduler] Time to notify:', notif.medicine_name);
                
                try {
                    // Check if should notify (API check)
                    const shouldNotify = await this.shouldNotify();
                    
                    if (shouldNotify) {
                        // Show notification
                        await this.showNotification(notif);
                        
                        // Mark as notified
                        await this.markAsNotified(notif);
                    }
                } catch (error) {
                    console.error('[NotificationScheduler] Error showing notification:', error);
                }
            }
        }

        // Check for snoozed items that should re-notify
        await this.checkSnoozedItems(currentTime);
        
        // Check for second reminders (only if online)
        if (this.isOnline) {
            await this.checkAndNotifySecondReminders();
        }
    }

    /**
     * Check if in do-not-disturb window
     */
    isInDoNotDisturbWindow(currentTime) {
        const { dnd_start, dnd_end } = this.preferences;

        if (dnd_start > dnd_end) {
            // Overnight range (e.g., 22:00 - 08:00)
            return currentTime >= dnd_start || currentTime < dnd_end;
        } else {
            // Same-day range
            return currentTime >= dnd_start && currentTime < dnd_end;
        }
    }

    /**
     * Subtract minutes from time string (HH:MM)
     */
    subtractMinutes(timeStr, minutes) {
        const [hours, mins] = timeStr.split(':').map(Number);
        let totalMinutes = hours * 60 + mins - minutes;

        if (totalMinutes < 0) {
            totalMinutes += 24 * 60; // Previous day
        }

        const newHours = Math.floor(totalMinutes / 60) % 24;
        const newMins = totalMinutes % 60;

        return newHours.toString().padStart(2, '0') + ':' +
               newMins.toString().padStart(2, '0');
    }

    /**
     * Check if already notified today
     */
    isAlreadyNotified(scheduleId) {
        return this.scheduledNotifications.has(`${scheduleId}-${new Date().toDateString()}`);
    }

    /**
     * Mark notification as sent
     */
    async markAsNotified(notif) {
        const key = `${notif.id}-${new Date().toDateString()}`;
        this.scheduledNotifications.set(key, {
            scheduled_time: new Date().toIso8601String(),
            sent_at: new Date().toIso8601String(),
        });

        // Also mark on server if online
        if (this.isOnline) {
            try {
                await fetch('/api/notification-sent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        medication_schedule_id: notif.id,
                        scheduled_time: new Date().toIso8601String(),
                        notification_type: this.preferences.sound_enabled ? 'both' : 'browser',
                    }),
                });
            } catch (error) {
                console.error('[NotificationScheduler] Error marking notification sent:', error);
            }
        }
    }

    /**
     * Show notification to user
     */
    async showNotification(notif) {
        try {
            // Use notification manager if available
            if (window.notificationManager) {
                window.notificationManager.show({
                    title: '💊 Waktu Minum Obat',
                    body: `${notif.medicine_name} (${notif.medicine_dose})`,
                    icon: '💊',
                    badge: '💊',
                    tag: `medication-${notif.id}`,
                    requireInteraction: true,
                    actions: [
                        { action: 'confirm', title: 'Saya sudah minum ✓' },
                        { action: 'snooze', title: 'Tunda 15 menit' },
                        { action: 'dismiss', title: 'Tutup' },
                    ],
                });

                // Play sound if enabled
                if (this.preferences.sound_enabled) {
                    window.notificationManager.playSound();
                }

                // Vibration if enabled
                if (this.preferences.vibration_enabled && 'vibrate' in navigator) {
                    navigator.vibrate([200, 100, 200, 100, 200]); // Pattern: 200ms, 100ms pause, etc.
                }
            }
        } catch (error) {
            console.error('[NotificationScheduler] Error showing notification:', error);
        }
    }

    /**
     * Check if should notify (server-side validation)
     */
    async shouldNotify() {
        if (!this.isOnline) {
            return true; // Offline, assume should notify
        }

        try {
            const response = await fetch('/api/should-notify');
            if (!response.ok) return true;
            
            const data = await response.json();
            return data.should_notify;
        } catch (error) {
            console.error('[NotificationScheduler] Error checking should-notify:', error);
            return true;
        }
    }

    /**
     * Check snoozed items and re-notify if snooze time passed
     */
    async checkSnoozedItems(currentTime) {
        try {
            const db = await this.getDB();
            const tx = db.transaction('snoozed_items', 'readonly');
            const store = tx.objectStore('snoozed_items');
            const allSnoozed = await store.getAll();

            const now = new Date();

            for (const item of allSnoozed) {
                const snoozeUntil = new Date(item.snooze_until);
                
                if (now >= snoozeUntil) {
                    // Snooze time passed, remove from snoozed
                    const deleteTx = db.transaction('snoozed_items', 'readwrite');
                    await deleteTx.objectStore('snoozed_items').delete(item.id);
                }
            }
        } catch (error) {
            console.error('[NotificationScheduler] Error checking snoozed items:', error);
        }
    }

    /**
     * Check for and send second reminders
     * Medications that haven't been confirmed after first reminder
     */
    async checkAndNotifySecondReminders() {
        try {
            console.log('[NotificationScheduler] Checking for second reminders...');
            
            const response = await fetch('/api/second-reminders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({}),
            });
            
            if (!response.ok) {
                console.warn('[NotificationScheduler] Failed to fetch second reminders');
                return;
            }
            
            const data = await response.json();
            const secondReminders = data.second_reminders || [];
            
            console.log(`[NotificationScheduler] Found ${secondReminders.length} second reminders`);
            
            // Show notifications for each second reminder
            for (const reminder of secondReminders) {
                // Check if in do-not-disturb window
                if (this.isInDoNotDisturbWindow(new Date().getHours().toString().padStart(2, '0') + ':' + new Date().getMinutes().toString().padStart(2, '0'))) {
                    console.log('[NotificationScheduler] In do-not-disturb, skipping second reminder');
                    continue;
                }
                
                try {
                    // Show second reminder notification
                    if (window.notificationManager) {
                        window.notificationManager.show({
                            title: '💊 Pengingat Kedua - Minum Obat',
                            body: `${reminder.medicine_name} (${reminder.medicine_dose}) - Jangan lupa minum obat Anda!`,
                            icon: '⏰',
                            badge: '⏰',
                            tag: `medication-reminder-2-${reminder.medication_schedule_id}`,
                            requireInteraction: true,
                            actions: [
                                { action: 'confirm', title: 'Saya sudah minum ✓' },
                                { action: 'snooze', title: 'Tunda 15 menit' },
                            ],
                        });

                        // Play sound if enabled
                        if (this.preferences.sound_enabled) {
                            window.notificationManager.playSound();
                        }

                        // Vibration if enabled
                        if (this.preferences.vibration_enabled && 'vibrate' in navigator) {
                            navigator.vibrate([200, 100, 200, 100, 200]);
                        }
                    }
                    
                    // Mark second reminder as sent
                    await this.markSecondReminderSent(reminder.notification_log_id);
                    
                    console.log('[NotificationScheduler] Second reminder sent for:', reminder.medicine_name);
                } catch (error) {
                    console.error('[NotificationScheduler] Error showing second reminder:', error);
                }
            }
        } catch (error) {
            console.error('[NotificationScheduler] Error checking second reminders:', error);
        }
    }

    /**
     * Mark second reminder as sent
     */
    async markSecondReminderSent(notificationLogId) {
        try {
            await fetch('/api/second-reminder-sent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    notification_log_id: notificationLogId,
                    notification_type: this.preferences.sound_enabled ? 'both' : 'browser',
                }),
            });
        } catch (error) {
            console.error('[NotificationScheduler] Error marking second reminder sent:', error);
        }
    }

    /**
     * Snooze notification
     */
    async snoozeNotification(scheduleId, minutes = 15) {
        try {
            // Mark on server if online
            if (this.isOnline) {
                await fetch('/api/snooze-notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        medication_schedule_id: scheduleId,
                        snooze_minutes: minutes,
                    }),
                });
            }

            // Store in local snoozed items
            const db = await this.getDB();
            const tx = db.transaction('snoozed_items', 'readwrite');
            const store = tx.objectStore('snoozed_items');
            
            const snoozeUntil = new Date();
            snoozeUntil.setMinutes(snoozeUntil.getMinutes() + minutes);

            await store.add({
                id: `${scheduleId}-${new Date().getTime()}`,
                schedule_id: scheduleId,
                snooze_until: snoozeUntil.toIso8601String(),
            });

            console.log(`[NotificationScheduler] Snoozed for ${minutes} minutes`);
        } catch (error) {
            console.error('[NotificationScheduler] Error snoozing notification:', error);
        }
    }

    /**
     * Dismiss notification
     */
    async dismissNotification(scheduleId) {
        try {
            if (this.isOnline) {
                await fetch('/api/dismiss-notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        medication_schedule_id: scheduleId,
                    }),
                });
            }

            console.log('[NotificationScheduler] Notification dismissed');
        } catch (error) {
            console.error('[NotificationScheduler] Error dismissing notification:', error);
        }
    }

    /**
     * Sync notification state when coming online
     */
    async syncNotificationState() {
        console.log('[NotificationScheduler] Syncing notification state');
        
        // Reload notification times
        await this.loadNotificationTimes();
        
        // Reload preferences
        await this.loadPreferences();
    }

    /**
     * Stop scheduler
     */
    stop() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
        console.log('[NotificationScheduler] Stopped');
    }
}

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.notificationScheduler = new NotificationScheduler();
    });
} else {
    window.notificationScheduler = new NotificationScheduler();
}

// Extend Date for toIso8601String if not available
if (!Date.prototype.toIso8601String) {
    Date.prototype.toIso8601String = function() {
        return this.toISOString();
    };
}

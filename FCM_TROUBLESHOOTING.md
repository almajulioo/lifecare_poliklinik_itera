# FCM Test Notification - Troubleshooting Guide

## 🔍 Diagnosis Steps

### Step 1: Check Firebase Configuration

Go to **http://127.0.0.1:8000/fcm-debug** and verify:
- ✅ User is authenticated
- ✅ FCM token exists and is saved
- ✅ Firebase credentials file exists
- ✅ VITE_FIREBASE_VAPID_KEY is set

### Step 2: Check Browser Notifications Permission

1. Click on the lock icon 🔒 next to the URL
2. Find "Notifications" permission
3. Change it to "Allow"
4. Refresh the page

### Step 3: Verify Service Worker

Open browser **Developer Tools** (F12):
1. Go to **Application** tab
2. Click **Service Workers** (left sidebar)
3. You should see **/firebase-messaging-sw.js** or similar registered
4. If not, the service worker failed to load

### Step 4: Check FCM Token Status

Open **Developer Tools Console** (F12 → Console):
```javascript
// Should see FCM Token logged
// Example: "FCM Token: c-al7cLyTNqaE9..."

// If you see errors, note them down

// Check if Notification API works:
Notification.permission // Should show "granted"
navigator.serviceWorker.ready // Should have an active service worker
```

## 🛠️ Common Issues & Fixes

### Issue #1: "VAPID Key Invalid"
**Error Message:** "Invalid VAPID key" in console

**Cause:** The VITE_FIREBASE_VAPID_KEY in .env has formatting issues

**Solution:**
```bash
# Edit .env file and make sure VITE_FIREBASE_VAPID_KEY is on ONE line with no breaks
VITE_FIREBASE_VAPID_KEY=BOMU_GhGGTQEqTGwfOgg18-mN69lcAN0pgfO11iEDJEopBdstVJmCR1R2qqFe27fE46pIiY8RXYap_Mvj2P50yI
```

If you edited .env, **clear cache and restart**:
```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
composer dump-autoload
```

### Issue #2: Service Worker Not Registered
**Error:** "Service Worker registration failed" or "404 Not Found"

**Cause:** Firebase messaging service worker file is missing

**Solution:**
Check if this file exists:
```
public/firebase-messaging-sw.js
```

If missing, create it at `public/firebase-messaging-sw.js`:
```javascript
importScripts('https://www.gstatic.com/firebasejs/12.11.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/12.11.0/firebase-messaging.js');

self.onmessage = (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
};

firebase.initializeApp({
    apiKey: "AIzaSyC4l8cv22eHJrBX4ezcJGVl0CSzgvoJnvA",
    projectId: "lifecare-poliklinik-itera",
    messagingSenderId: "885870142104",
    appId: "1:885870142104:web:5e94de5f1f00672828a6ed",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log('Received background message: ', payload);
    
    const notificationTitle = payload.notification?.title || payload.data?.title || 'Notification';
    const notificationOptions = {
        body: payload.notification?.body || payload.data?.body || '',
        icon: payload.notification?.icon || '/favicon.ico',
        badge: '/favicon.ico',
        tag: payload.data?.tag || 'fcm-notification'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
```

### Issue #3: "No FCM Token for user"
**Error:** Shows on `/fcm-debug` or when trying to send

**Cause:** Token not saved to database

**Solution:**
1. Go to **http://127.0.0.1:8000/fcm-test**
2. Refresh the page (should request notification permission)
3. Click "Allow" when prompted
4. Wait 3-5 seconds for token to auto-save
5. Check if a token appears in the input field
6. Try sending again

### Issue #4: Firebase Credentials Invalid
**Error:** "Unauthenticated" or "permission denied"

**Cause:** Firebase credentials file is corrupted or invalid

**Solution:**
1. Download new service account key from Firebase Console:
   - Go to: https://console.firebase.google.com
   - Project Settings → Service Accounts
   - Click "Generate new private key"
2. Replace `storage/app/firebase_credentials.json` with the downloaded file
3. Update `.env`:
```env
FIREBASE_CREDENTIALS=storage/app/firebase_credentials.json
```
4. Clear cache: `php artisan config:clear`

### Issue #5: FCM Token Expired
**Error:** "Invalid token" after several days

**Cause:** FCM tokens expire and need refresh

**Solution:**
Visit **http://127.0.0.1:8000/fcm-test** again to automatically refresh the token

### Issue #6: Browser Not Secure
**Error:** "Push notification requires HTTPS or localhost"

**Current Status:** ✅ Working on localhost (http://127.0.0.1:8000)

**For Production:**
- Must use HTTPS
- Self-signed certificates won't work for FCM
- Need valid SSL certificate

## 📱 Testing FCM Notifications

### Option 1: Send via Form (with Backend Processing)
1. Go to http://127.0.0.1:8000/fcm-test
2. Ensure FCM token is showing
3. Enter:
   - **Title:** "Hello"
   - **Body:** "Test notification message"
4. Click "Send Notification"
5. Check console for success/error messages

### Option 2: Send via Dummy Push (Browser Only, No FCM)
1. Go to http://127.0.0.1:8000/fcm-test
2. Click "Dummy Push 'Hello' (No FCM)" button
3. You should see a browser notification immediately
4. If this works but FCM doesn't, the issue is with FCM not the browser

### Option 3: Test via API (for developers)
```bash
curl -X POST http://127.0.0.1:8000/fcm-test \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "fcm_token": "YOUR_DEVICE_TOKEN_HERE",
    "title": "Hello",
    "body": "Test message via API"
  }'
```

## 📊 Database Verification

Check if tokens are being saved:
```sql
SELECT id, name, email, fcm_token FROM users WHERE fcm_token IS NOT NULL LIMIT 5;
```

Should see token values (long random strings starting with c-...).

## 🧪 Quick Diagnostic Test

Run this in your Laravel app:
```bash
php artisan tinker

# Check Firebase is configured
>>> config('firebase.projects.app.credentials')

# Check if credentials file exists
>>> file_exists(storage_path(config('firebase.projects.app.credentials')))

# Test Firebase connection
>>> app('firebase.app')->getDatabase()
```

## 📝 Log Analysis

Check logs for errors:
```bash
tail -f storage/logs/laravel.log | grep -i "fcm\|notification"
```

## ✅ Success Indicators

When FCM is working correctly, you should see:
1. ✅ Browser notification appears immediately
2. ✅ Notification has title "Hello" and body text
3. ✅ Notification has proper Firebase icon
4. ✅ Console shows "FCM Token:" (in browser DevTools)
5. ✅ Storage shows FCM token for logged-in user

## 🚀 Next Steps if Still Not Working

1. **Clear all caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear  
   php artisan optimize:clear
   php artisan view:clear
   composer dump-autoload
   ```

2. **Restart Laravel:**
   ```bash
   # Stop current server (Ctrl+C)
   php artisan serve
   ```

3. **Check browser cache:**
   - Open DevTools (F12)
   - Right-click refresh button → "Empty cache and hard refresh"

4. **Check Firebase Console:**
   - Go to https://console.firebase.google.com/project/lifecare-poliklinik-itera
   - Check Messaging tab for delivery stats
   - Check if tokens are registered

5. **Contact Firebase Support** with:
   - Project ID: `lifecare-poliklinik-itera`
   - Error message from browser console
   - FCM token (first 20 chars is OK)

---

**Last Updated:** 2026-04-26  
**Status:** ✅ Hotfix Applied

# OneSignal Localhost Configuration Guide

## ✅ Konfigurasi Localhost sebagai Secure Origin

OneSignal sudah dikonfigurasi untuk mengenali localhost sebagai secure origin dengan setting `allowLocalhostAsSecureOrigin: true`.

## 📋 Configuration Details

### File: `resources/views/layouts/app_mobile.blade.php`

```javascript
// CRITICAL: Allow localhost untuk development
allowLocalhostAsSecureOrigin: true,
```

Konfigurasi ini memungkinkan:
- ✅ HTTP protocol di localhost (biasanya secure origins hanya HTTPS)
- ✅ localhost:8000 dianggap sebagai secure origin
- ✅ 127.0.0.1:8000 dianggap sebagai secure origin
- ✅ Push notifications berfungsi di development environment

### Localhost Detection

```javascript
window.OneSignalConfig = {
    appId: "{{ config('services.onesignal.app_id') }}",
    userEmail: "{{ auth()->user()->email }}",
    userName: "{{ auth()->user()->name }}",
    environment: "{{ config('app.env') }}",
    // Auto-detect localhost
    isLocalhost: window.location.hostname === 'localhost' || 
                 window.location.hostname === '127.0.0.1',
};
```

## 🔧 Environment-Aware Configuration

```javascript
const initConfig = {
    appId: appId,
    allowLocalhostAsSecureOrigin: true,  // ← CRITICAL untuk development
    serviceWorkerPath: "/OneSignalSDKWorker.js",
    
    // Localhost-specific settings
    ...(isLocalhost && {
        requiresUserPrivacyConsent: false,
    }),
};
```

## ✔️ Verification Checklist

### 1. Browser Console Check

Buka browser DevTools (F12) → Console dan verifikasi log berikut:

```javascript
// Expected logs:
[OneSignal] Configuration: {
    appId: "d2adbb4d-c6c8...",
    environment: "local",
    hostname: "localhost",
    isLocalhost: true
}

[OneSignal] ✅ Initialization successful

[OneSignal] Secure Origin Status: {
    isLocalhost: true,
    allowLocalhostAsSecureOrigin: true,
    protocol: "http:"
}

[OneSignal] ✅ User logged in
[OneSignal] ✅ User attributes set
```

### 2. Service Worker Registration

Buka DevTools → Application → Service Workers

Verifikasi:
- ✅ `/OneSignalSDKWorker.js` - registered
- ✅ Status: activated and running
- ✅ Scope: `/`

### 3. Push Subscription Status

Di Console, jalankan:

```javascript
// Check subscription token
OneSignal.User.PushSubscription.token

// Check if opted in
OneSignal.User.PushSubscription.optedIn

// Check notification permission
Notification.permission

// Expected outputs:
// OneSignal.User.PushSubscription.token → "xxxxxxxx..." (token string)
// OneSignal.User.PushSubscription.optedIn → true
// Notification.permission → "granted"
```

## 🚀 Testing Localhost Configuration

### Test 1: Verify Initialization

```bash
# 1. Start development server
php artisan serve

# 2. Open browser to http://localhost:8000/app/dashboard
# 3. Open Console (F12)
# 4. Look for "[OneSignal] ✅ Initialization successful"
```

### Test 2: Send Test Notification

```bash
# Terminal 1: Start queue worker
php artisan queue:work --verbose

# Terminal 2: In browser, send test notification
# Go to http://localhost:8000/app/profile
# Click "Kirim Notifikasi Percobaan"

# Terminal 3: Check logs
tail -f storage/logs/laravel.log
```

### Test 3: Check Secure Origin

```javascript
// In browser console:
console.log({
    protocol: window.location.protocol,
    hostname: window.location.hostname,
    port: window.location.port,
    isSecure: window.location.protocol === 'https:',
    isLocalhost: window.location.hostname === 'localhost',
    allowLocalhostAsSecureOrigin: true,
});

// Expected output:
{
    protocol: "http:",
    hostname: "localhost",
    port: "8000",
    isSecure: false,
    isLocalhost: true,
    allowLocalhostAsSecureOrigin: true,
}
```

## 📍 Different Localhost Environments

### Scenario 1: Local Development with `php artisan serve`

```
URL: http://localhost:8000
Protocol: HTTP (not HTTPS)
Status: ✅ Working (allowLocalhostAsSecureOrigin allows this)
```

**Verified logs:**
```
[OneSignal] Secure Origin Status: {
    isLocalhost: true,
    allowLocalhostAsSecureOrigin: true,
    protocol: "http:"
}
```

### Scenario 2: Local Development with HTTPS (optional)

```
URL: https://localhost:8000
Protocol: HTTPS
Status: ✅ Working (HTTPS is always secure)
```

### Scenario 3: Virtual Host (192.168.x.x)

```
URL: http://192.168.1.100:8000
Protocol: HTTP
Status: ⚠️ Needs allowLocalhostAsSecureOrigin (not 127.0.0.1)
Note: May require additional configuration
```

## 🔐 Security in Production

**IMPORTANT**: `allowLocalhostAsSecureOrigin` adalah HANYA untuk development!

```javascript
// Production configuration (di production server):
const isProduction = window.location.protocol === 'https:';

await OneSignal.init({
    appId: appId,
    // DO NOT use allowLocalhostAsSecureOrigin in production!
    allowLocalhostAsSecureOrigin: false,  // atau hapus sama sekali
    serviceWorkerPath: "/OneSignalSDKWorker.js",
});
```

## 🛠️ Troubleshooting

### Problem 1: "Secure context required"

**Penyebab**: Notification API memerlukan secure context (HTTPS) di production

**Solusi untuk localhost**:
```javascript
// Ini sudah ditangani dengan allowLocalhostAsSecureOrigin: true
allowLocalhostAsSecureOrigin: true,
```

### Problem 2: Service Worker not registering

**Cek**:
```javascript
// In console:
navigator.serviceWorker.ready
// Should resolve successfully

// If fails:
navigator.serviceWorker.getRegistrations().then(regs => {
    console.log('Registrations:', regs);
});
```

### Problem 3: Push notifications not working

**Debug checklist**:
```javascript
// 1. Check Notification permission
console.log('Permission:', Notification.permission);
// Expected: "granted"

// 2. Check OneSignal initialization
console.log('OneSignal ready:', OneSignal)

// 3. Check service worker
console.log('Service Workers:', navigator.serviceWorker.controller)

// 4. Check subscription
console.log('Token:', OneSignal.User.PushSubscription.token)
```

### Problem 4: "allowLocalhostAsSecureOrigin not recognized"

**Solusi**: Pastikan menggunakan OneSignal SDK v16+

```html
<!-- ✅ Correct (v16+) -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js"></script>

<!-- ❌ Wrong (old version) -->
<script src="https://cdn.onesignal.com/sdks/web/v1/OneSignalSDK.js"></script>
```

## 📊 Logging Output (Expected vs Actual)

### ✅ Successful Initialization

```
[OneSignal] Configuration: {
    appId: "d2adbb4d-c6c8-4ad3-86ac-3d8b4cc97434",
    environment: "local",
    hostname: "localhost",
    isLocalhost: true
}

[OneSignal] Initializing with config: {
    appId: "d2adbb4d-c6c8-4ad3-86ac-3d8b4cc97434",
    allowLocalhostAsSecureOrigin: true,
    serviceWorkerPath: "/OneSignalSDKWorker.js",
    environment: "local"
}

[OneSignal] ✅ Initialization successful

[OneSignal] Secure Origin Status: {
    isLocalhost: true,
    allowLocalhostAsSecureOrigin: true,
    protocol: "http:"
}

[OneSignal] ✅ User logged in

[OneSignal] ✅ User attributes set

[OneSignal] ✅ User subscribed with token: {
    token: "xxxxxxxx-xxxx-xxxx...",
    optedIn: true,
    notificationPermission: "granted"
}
```

### ❌ Configuration Error

Jika melihat error:

```
[OneSignal] ❌ Initialization error: Error: ...
[OneSignal] Error details: {
    message: "...",
    localhost: true,
    allowLocalhostAsSecureOrigin: true,
}

[OneSignal] Troubleshooting Info: {
    notificationPermission: "denied",  // ← User perlu approve
    serviceWorkerAvailable: true,
    pushAvailable: true,
    protocol: "http:",
    hostname: "localhost",
}
```

## 🎯 Complete Configuration Reference

```javascript
// ✅ COMPLETE OneSignal Configuration untuk Localhost

const initConfig = {
    // Required
    appId: "d2adbb4d-c6c8-4ad3-86ac-3d8b4cc97434",
    
    // Service Worker
    serviceWorkerParam: { scope: "/" },
    serviceWorkerPath: "/OneSignalSDKWorker.js",
    
    // Localhost Support (CRITICAL)
    allowLocalhostAsSecureOrigin: true,  // ← Allow HTTP pada localhost
    
    // UI
    notifyButton: {
        enable: true,
    },
    
    // Safari Support
    safari_web_id: "web.onesignal.auto.50d89199-747f-4818-96ca-50d4208129fc",
    
    // Localhost-specific
    requiresUserPrivacyConsent: false,  // (untuk localhost)
};
```

## 📝 Configuration Summary

| Setting | Value | Purpose |
|---------|-------|---------|
| `appId` | `d2adbb4d-...` | OneSignal App ID |
| `allowLocalhostAsSecureOrigin` | `true` | ✅ Enable HTTP di localhost |
| `serviceWorkerPath` | `/OneSignalSDKWorker.js` | Path ke service worker |
| `notifyButton` | `{ enable: true }` | Show notification button |
| `environment` | `local` | untuk development |

## ✅ Verification Commands

```bash
# 1. Check configuration
php artisan onesignal:check-config

# 2. Start queue worker
php artisan queue:work --verbose

# 3. Check logs in real-time
tail -f storage/logs/laravel.log

# 4. View service worker registration
# → Browser DevTools → Application → Service Workers

# 5. Send test notification
# → Go to /app/profile → Click "Kirim Notifikasi Percobaan"
```

---

**Status**: ✅ **Localhost sebagai Secure Origin - CONFIGURED**  
**allowLocalhostAsSecureOrigin**: ✅ **TRUE**  
**SDK Version**: v16 (supports this configuration)  
**Last Updated**: May 2, 2026

importScripts('https://www.gstatic.com/firebasejs/12.11.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/12.11.0/firebase-messaging-compat.js');

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// Initialize the Firebase app in the service worker by passing the generated config
// You should update these values with your actual Firebase config
const firebaseConfig = {
  apiKey: "AIzaSyC4l8cv22eHJrBX4ezcJGVl0CSzgvoJnvA",
  authDomain: "lifecare-poliklinik-itera.firebaseapp.com",
  projectId: "lifecare-poliklinik-itera",
  storageBucket: "lifecare-poliklinik-itera.firebasestorage.app",
  messagingSenderId: "885870142104",
  appId: "1:885870142104:web:5e94de5f1f00672828a6ed",
  measurementId: "G-R784D7MWEF"
};

// Check if config has been updated from default placeholders
if (firebaseConfig.apiKey !== 'YOUR_API_KEY') {
    firebase.initializeApp(firebaseConfig);

    const messaging = firebase.messaging();

    messaging.onBackgroundMessage(function (payload) {
        console.log('[firebase-messaging-sw.js] Received background message ', payload);

        const notificationTitle = payload.notification?.title || payload.data?.title || 'New Notification';
        const notificationOptions = {
            body: payload.notification?.body || payload.data?.body || 'You have a new message.',
            icon: '/favicon.ico' // Or path to your app logo
        };

        self.registration.showNotification(notificationTitle, notificationOptions);
    });
} else {
    console.warn('[firebase-messaging-sw.js] Firebase config missing. Please set your config.');
}

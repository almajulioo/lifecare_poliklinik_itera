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
    apiKey: "AIzaSyC3sDrzPHUrTTISZBz1Rq-eO_CR12Ecyhc",
    authDomain: "lifecare-4accb.firebaseapp.com",
    projectId: "lifecare-4accb",
    storageBucket: "lifecare-4accb.firebasestorage.app",
    messagingSenderId: "159130285382",
    appId: "1:159130285382:web:e04a71e99f949663356147",
    measurementId: "G-91JY50NGSG"
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

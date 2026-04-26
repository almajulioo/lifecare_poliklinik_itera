<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FCM Test Dashboard | Lifecare Poliklinik</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #f6f8fb 0%, #e9effd 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
        }

        .btn-gradient {
            background: linear-gradient(90deg, #4F46E5 0%, #7C3AED 100%);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
    </style>
</head>

<body class="py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">FCM Test Dashboard</h1>
            <p class="text-gray-600">Integrate and test Firebase Cloud Messaging effortlessly.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-lg animate-pulse"
                id="alert-success">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg" id="alert-error">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Form Section -->
            <div class="glass-card p-8 rounded-2xl">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    Send Push Notification
                </h2>
                <form action="{{ route('fcm.test.send') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target FCM Token</label>
                            <input type="text" name="fcm_token" required
                                value="{{ old('fcm_token', $currentUserToken) }}"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                                placeholder="Paste device token here...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="title" required value="Lifecare Alert"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message Body</label>
                            <textarea name="body" required rows="3"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">Halo! Ini adalah notifikasi uji coba dari Lifecare Poliklinik Itera.</textarea>
                        </div>
                        <button type="submit" class="w-full py-3 px-4 text-white font-semibold rounded-lg btn-gradient">
                            Send Notification
                        </button>
                        <button type="button" id="dummy-push-btn"
                            class="w-full py-3 px-4 border border-indigo-200 text-indigo-700 font-semibold rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors">
                            Dummy Push "Hello" (No FCM)
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Section -->
            <div class="glass-card p-8 rounded-2xl">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    FCM Guide
                </h2>
                <div class="text-sm text-gray-600 space-y-4">
                    <p>To test notifications, follow these steps:</p>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Ensure <code>FIREBASE_CREDENTIALS</code> is set in your <code>.env</code>.</li>
                        <li>Set <code>VITE_FIREBASE_VAPID_KEY</code> in <code>.env</code> with your Firebase Web Push certificate key pair public key.</li>
                        <li>The credential file must be a valid Service Account JSON from Firebase.</li>
                        <li>Obtain a device token from your client application (Web, Android, or iOS).</li>
                        <li>Paste the token on the left form and hit send.</li>
                    </ul>
                    <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100 mt-4">
                        <p class="font-medium text-indigo-800 mb-1">Config Status:</p>
                        <code class="text-xs break-all">
                            FIREBASE_CREDENTIALS: {{ env('FIREBASE_CREDENTIALS', 'Not Set') }}
                        </code>
                        <br>
                        <code class="text-xs break-all">
                            VITE_FIREBASE_VAPID_KEY: {{ env('VITE_FIREBASE_VAPID_KEY') ? 'Set' : 'Not Set' }}
                        </code>
                    </div>
                </div>
            </div>
        </div>

        @if($users->count() > 0)
            <div class="mt-8 glass-card p-8 rounded-2xl">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    Users with Tokens
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                                <th class="pb-3 px-4">Name</th>
                                <th class="pb-3 px-4">Email</th>
                                <th class="pb-3 px-4">Token Snippet</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @foreach($users as $user)
                                <tr class="border-b border-gray-50 hover:bg-white/50 transition-colors">
                                    <td class="py-3 px-4 font-medium">{{ $user->name }}</td>
                                    <td class="py-3 px-4">{{ $user->email }}</td>
                                    <td class="py-3 px-4 text-xs font-mono text-gray-500">
                                        {{ Str::limit($user->fcm_token, 30) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        ['alert-success', 'alert-error'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                setTimeout(() => {
                    el.style.opacity = '0';
                    el.style.transition = 'opacity 1s ease';
                    setTimeout(() => el.remove(), 1000);
                }, 5000);
            }
        });

        const showDummyNotification = async () => {
            if (!("Notification" in window)) {
                alert("Browser ini tidak mendukung Notification API.");
                return;
            }

            let permission = Notification.permission;
            if (permission === "default") {
                permission = await Notification.requestPermission();
            }

            if (permission !== "granted") {
                alert("Izin notifikasi belum diberikan.");
                return;
            }

            const title = "Hello";
            const options = {
                body: "Ini dummy push notification tanpa FCM.",
                icon: "/favicon.ico",
                tag: "dummy-hello",
            };

            if ("serviceWorker" in navigator) {
                const registration = await navigator.serviceWorker.getRegistration();
                if (registration) {
                    registration.showNotification(title, options);
                    return;
                }
            }

            new Notification(title, options);
        };

        const dummyPushButton = document.getElementById("dummy-push-btn");
        if (dummyPushButton) {
            dummyPushButton.addEventListener("click", () => {
                showDummyNotification().catch((err) => {
                    console.error("Dummy notification failed:", err);
                });
            });
        }
    </script>
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/12.11.0/firebase-app.js";
        import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.11.0/firebase-messaging.js";

        const firebaseConfig = {
            apiKey: "AIzaSyC4l8cv22eHJrBX4ezcJGVl0CSzgvoJnvA",
            authDomain: "lifecare-poliklinik-itera.firebaseapp.com",
            projectId: "lifecare-poliklinik-itera",
            storageBucket: "lifecare-poliklinik-itera.firebasestorage.app",
            messagingSenderId: "885870142104",
            appId: "1:885870142104:web:5e94de5f1f00672828a6ed",
            measurementId: "G-R784D7MWEF"
        };
        const vapidKey = "{{ env('VITE_FIREBASE_VAPID_KEY', '') }}".trim();

        const isValidWebPushVapidKey = (key) => {
            // Firebase expects URL-safe base64 public key (typically ~87 chars for 65-byte key).
            return Boolean(key) && /^[A-Za-z0-9_-]{80,120}$/.test(key);
        };

        const canUseWebPush = () => {
            const isLocalhost = location.hostname === "localhost" || location.hostname === "127.0.0.1";
            return window.isSecureContext || isLocalhost;
        };

        const waitForServiceWorkerActivation = async (registration) => {
            if (registration.active) {
                return registration;
            }

            const worker = registration.installing || registration.waiting;
            if (!worker) {
                await navigator.serviceWorker.ready;
                return registration;
            }

            await new Promise((resolve, reject) => {
                const timeout = setTimeout(() => {
                    reject(new Error("Timed out waiting for service worker activation."));
                }, 10000);

                worker.addEventListener("statechange", () => {
                    if (worker.state === "activated") {
                        clearTimeout(timeout);
                        resolve();
                    }
                });
            });

            return registration;
        };

        const ensurePushReadyRegistration = async (registration, scope) => {
            if (registration && registration.pushManager) {
                return registration;
            }

            // Retry from browser registry in case registration object is stale.
            const resolved = await navigator.serviceWorker.getRegistration(scope);
            if (resolved && resolved.pushManager) {
                return resolved;
            }

            throw new Error("FCM service worker is not ready (missing pushManager).");
        };

        const registerMessagingServiceWorker = async () => {
            if (!("serviceWorker" in navigator)) {
                throw new Error("Service Worker is not supported by this browser.");
            }

            // Use dedicated scope to avoid clashing with app SW at "/".
            const messagingScope = "/firebase-cloud-messaging-push-scope";
            const registration = await navigator.serviceWorker.register("/firebase-messaging-sw.js", {
                scope: messagingScope,
            });

            const activeRegistration = await waitForServiceWorkerActivation(registration);
            return ensurePushReadyRegistration(activeRegistration, messagingScope);
        };

        try {
            // Only initialize if config is somewhat valid (not default placeholders)
            if (firebaseConfig.apiKey !== 'YOUR_API_KEY') {
                const app = initializeApp(firebaseConfig);
                const messaging = getMessaging(app);

                // Request permission and get token
                (async () => {
                    if (!canUseWebPush()) {
                        console.error("Push notification requires HTTPS or localhost.");
                        return;
                    }

                    const permission = await Notification.requestPermission();
                    if (permission !== "granted") {
                        console.warn("Notification permission was not granted.");
                        return;
                    }

                    if (!isValidWebPushVapidKey(vapidKey)) {
                        console.error(
                            "Missing/invalid VAPID key. Set VITE_FIREBASE_VAPID_KEY in .env and reload page."
                        );
                        return;
                    }

                    try {
                        const swRegistration = await registerMessagingServiceWorker();
                        const currentToken = await getToken(messaging, {
                            vapidKey,
                            serviceWorkerRegistration: swRegistration,
                        });

                        if (currentToken) {
                            console.log("FCM Token:", currentToken);
                            const tokenInput = document.querySelector('input[name="fcm_token"]');
                            if (tokenInput && !tokenInput.value) {
                                tokenInput.value = currentToken;
                            }

                            // Save token to server for logged in user
                            @auth
                                fetch("{{ route('fcm.test.save-token') }}", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                    },
                                    body: JSON.stringify({ fcm_token: currentToken })
                                }).then(res => res.json()).then(data => {
                                    console.log('Token sync status:', data.message);
                                }).catch(err => console.error('Error syncing token:', err));
                            @endauth
                        } else {
                            console.warn("No registration token available.");
                        }
                    } catch (err) {
                        console.error("Error retrieving token.", err);
                    }
                })();

                // Listen for foreground messages
                onMessage(messaging, (payload) => {
                    console.log('Message received in foreground. ', payload);
                    const title = payload.notification?.title || payload.data?.title || 'New Notification';
                    const body = payload.notification?.body || payload.data?.body || 'You have a new message.';
                    alert(`🔔 ${title}\n\n${body}`);
                });
            } else {
                console.warn('Firebase config missing. Please set VITE_FIREBASE_* variables in .env');
            }
        } catch (e) {
            console.error('Firebase initialization error:', e);
        }
    </script>
</body>

</html>

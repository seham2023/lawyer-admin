/**
 * Firebase Messaging Service Worker
 * Handles background push notifications for incoming calls and messages
 * This runs even when the dashboard tab is closed or inactive
 */

importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

// Firebase configuration (from project qanon-d7e42)
// NOTE: To get the Web appId, go to Firebase Console → Project Settings → 
// Add app → Web → copy the config. The apiKey below works for all platforms.
const firebaseConfig = {
    apiKey: "AIzaSyD2aJjlUIF_QSl9TX-UlHMCxB5-owN7H84",
    authDomain: "qanon-d7e42.firebaseapp.com",
    projectId: "qanon-d7e42",
    storageBucket: "qanon-d7e42.firebasestorage.app",
    messagingSenderId: "355532099922",
    appId: "1:355532099922:web:XXXXXXXXXXXXXXXX",  // TODO: Add your Firebase Web App ID here
};

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

/**
 * Handle background messages (when tab is not focused)
 */
messaging.onBackgroundMessage((payload) => {
    console.log('[SW] Background message received:', payload);

    const data = payload.data || {};
    const notifPayload = payload.notification || {};
    
    let title, body, icon, tag, requireInteraction;

    if (data.key === 'newCall' || data.type === 'newCall' || data.status === 'newCall') {
        // ═══════════════════════════════════════
        // INCOMING CALL notification
        // ═══════════════════════════════════════
        const callerName = data.userName || data.title || notifPayload.title || 'مستخدم';
        const callType = data.callType || 'audio';
        const typeLabel = callType === 'video' ? 'مكالمة فيديو' : 'مكالمة صوتية';

        title = `📞 ${callerName}`;
        body = `${typeLabel} واردة — اضغط للرد`;
        icon = data.image || '/images/legal/logo.png';
        tag = `call-${data.room_id || data.conversationId || 'incoming'}`;
        requireInteraction = true;

    } else if (data.key === 'missedCall' || data.type === 'missedCall' || data.status === 'cancel') {
        // ═══════════════════════════════════════
        // MISSED CALL notification
        // ═══════════════════════════════════════
        const callerName = data.userName || data.title || notifPayload.title || 'مستخدم';
        title = `📵 مكالمة فائتة`;
        body = `${callerName} حاول الاتصال بك`;
        icon = data.image || '/images/legal/logo.png';
        tag = `missed-${data.room_id || 'call'}`;
        requireInteraction = false;

    } else if (data.type === 'new_message') {
        // ═══════════════════════════════════════
        // NEW MESSAGE notification
        // ═══════════════════════════════════════
        const senderName = data.sender_name || data.title || notifPayload.title || 'رسالة جديدة';
        const messagePreview = data.message_ar || data.message_en || notifPayload.body || 'رسالة جديدة';

        title = `💬 ${senderName}`;
        body = messagePreview;
        icon = '/images/legal/logo.png';
        tag = `msg-${data.room_id || 'new'}`;
        requireInteraction = false;

    } else {
        // ═══════════════════════════════════════
        // GENERIC notification
        // ═══════════════════════════════════════
        title = notifPayload.title || data.title || 'القسطاس';
        body = notifPayload.body || data.body || '';
        icon = '/images/legal/logo.png';
        tag = 'generic';
        requireInteraction = false;
    }

    return self.registration.showNotification(title, {
        body: body,
        icon: icon,
        tag: tag,
        requireInteraction: requireInteraction,
        data: data,
        vibrate: requireInteraction ? [200, 100, 200, 100, 200] : [100],
        actions: requireInteraction ? [
            { action: 'accept', title: 'قبول' },
            { action: 'reject', title: 'رفض' },
        ] : [],
    });
});

/**
 * Handle notification click
 */
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked:', event.action, event.notification.data);

    event.notification.close();

    const data = event.notification.data || {};

    // Focus existing dashboard window or open new one
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Try to find an existing dashboard window
            for (const client of windowClients) {
                if (client.url.includes('/admin') && 'focus' in client) {
                    // Post message to the existing window
                    client.postMessage({
                        type: 'CALL_NOTIFICATION_CLICK',
                        action: event.action,
                        data: data,
                    });
                    return client.focus();
                }
            }

            // No existing window — open new one
            const url = data.room_id
                ? `/admin/messages?room=${data.room_id}`
                : '/admin';
            return clients.openWindow(url);
        })
    );
});

/**
 * Handle messages from the main page
 */
self.addEventListener('message', (event) => {
    console.log('[SW] Message from page:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

/**
 * Socket.IO Client Wrapper for Dashboard
 * SPA-safe: survives Filament page navigations
 * Bridges all socket events → Livewire dispatch
 */

class SocketClient {
    constructor(url, userId) {
        this.url = url;
        this.userId = userId;
        this.socket = null;
        this.connected = false;
        this.currentRoomId = null;
        this.messageCallbacks = [];
        this.callCallbacks = [];
        this._reconnectAttempts = 0;
        this._pendingEmits = [];
    }

    _timestamp() {
        return new Date().toISOString();
    }

    _debug(label, data = undefined) {
        const prefix = `[SocketClient][${this._timestamp()}] ${label}`;
        if (data === undefined) {
            console.log(prefix);
            return;
        }
        console.log(prefix, data);
    }

    /**
     * Connect to Socket.IO server
     */
    connect() {
        if (this.connected && this.socket) {
            this._debug('Already connected');
            return;
        }

        this._debug('Connecting', { url: this.url, userId: this.userId });

        this.socket = io(this.url, {
            transports: ['websocket'],
            upgrade: false,
            reconnection: true,
            reconnectionDelay: 1000,
            reconnectionDelayMax: 10000,
            reconnectionAttempts: Infinity,
            timeout: 15000,
            rejectUnauthorized: false,
        });

        this.socket.on('connect', () => {
            this._debug('Connected', {
                socketId: this.socket.id,
                pendingEmits: this._pendingEmits.length,
            });
            this.connected = true;
            this._reconnectAttempts = 0;
            this._flushPendingEmits();

            const payload = {
                user_id: this.userId,
                userId: this.userId,
                id: this.userId,
                platform: 'dashboard'
            };

            // Register for call routing + chat routing
            this.socket.emit('dashboardConnect', payload);
            this.socket.emit('registerUser', payload);
            this.socket.emit('register', payload);
            this.socket.emit('online', payload);
            this.socket.emit('userOnline', payload);

            // Re-join room if we were in one
            if (this.currentRoomId) {
                this._joinRoomInternal(this.currentRoomId);
            }

            // Update presence
            this._updatePresence('online');
        });

        this.socket.on('disconnect', (reason) => {
            this._debug('Disconnected', {
                reason: reason,
                socketId: this.socket?.id ?? null,
                roomId: this.currentRoomId,
            });
            this.connected = false;
        });

        this.socket.on('reconnect', (attemptNumber) => {
            this._debug('Reconnected', {
                attemptNumber: attemptNumber,
                socketId: this.socket?.id ?? null,
            });
        });

        this.socket.on('connect_error', (error) => {
            this._reconnectAttempts++;
            console.error(`[SocketClient][${this._timestamp()}] Connection error`, {
                message: error.message,
                description: error.description ?? null,
                type: error.type ?? null,
                attempts: this._reconnectAttempts,
            });
        });

        // ═══════════════════════════════════════════════
        // CHAT EVENTS → Bridge to Livewire
        // ═══════════════════════════════════════════════

        this.socket.on('newMessage', (data) => {
            this._debug('newMessage', data);

            // 1. Notify any registered callbacks (legacy support)
            this.messageCallbacks.forEach(cb => cb(data));

            // 2. Bridge to Livewire (SPA-safe — survives navigation)
            if (window.Livewire) {
                window.Livewire.dispatch('socket-new-message', { data: data });
            }

            // 3. Browser notification if tab is not focused
            if (document.hidden) {
                this._showMessageNotification(data);
            }
        });

        this.socket.on('typingIndicator', (data) => {
            if (window.Livewire) {
                window.Livewire.dispatch('socket-typing', { data: data });
            }
        });

        // ═══════════════════════════════════════════════
        // CALL EVENTS → Bridge to Livewire
        // ═══════════════════════════════════════════════

        this.socket.on('incomingCall', (data) => {
            this._debug('incomingCall', data);
            this.callCallbacks.forEach(cb => cb(data));

            // Bridge to Livewire CallNotification component
            if (window.Livewire) {
                window.Livewire.dispatch('incoming-call', { callData: data });
            }

            // Always show browser notification for calls (even if tab is focused)
            this._showCallNotification(data);
        });

        this.socket.on('callAccepted', (data) => {
            this._debug('callAccepted', data);
            this.callCallbacks.forEach(cb => cb({ ...data, status: 'accepted' }));

            if (window.Livewire) {
                window.Livewire.dispatch('socket-call-accepted', { data: data });
            }
        });

        this.socket.on('callRejected', (data) => {
            this._debug('callRejected', data);
            this.callCallbacks.forEach(cb => cb({ ...data, status: 'rejected' }));

            if (window.Livewire) {
                window.Livewire.dispatch('socket-call-rejected', { data: data });
            }
        });

        this.socket.on('callEnded', (data) => {
            this._debug('callEnded', data);
            this.callCallbacks.forEach(cb => cb({ ...data, status: 'ended' }));

            if (window.Livewire) {
                window.Livewire.dispatch('call-ended-remote', { callData: data });
            }
        });

        this.socket.on('callTimeout', (data) => {
            this._debug('callTimeout', data);
            if (window.Livewire) {
                window.Livewire.dispatch('call-ended-remote', { callData: { ...data, reason: 'timeout' } });
            }
        });

        return this.socket;
    }

    _emitOrQueue(eventName, payload) {
        if (this.socket && this.connected) {
            this._debug('Emitting event', {
                eventName: eventName,
                socketId: this.socket.id,
                payload: payload,
            });
            this.socket.emit(eventName, payload);
            return true;
        }

        console.warn(`[SocketClient][${this._timestamp()}] Queueing event while disconnected`, {
            eventName: eventName,
            payload: payload,
            roomId: this.currentRoomId,
        });
        this._pendingEmits.push({ eventName, payload });
        return false;
    }

    _flushPendingEmits() {
        if (!this.socket || !this.connected || this._pendingEmits.length === 0) {
            return;
        }

        const queued = [...this._pendingEmits];
        this._pendingEmits = [];

        queued.forEach(({ eventName, payload }) => {
            this._debug('Flushing queued event', {
                eventName: eventName,
                socketId: this.socket.id,
                payload: payload,
            });
            this.socket.emit(eventName, payload);
        });
    }

    // ═══════════════════════════════════════════════
    // ROOM MANAGEMENT
    // ═══════════════════════════════════════════════

    joinRoom(roomId) {
        this.currentRoomId = roomId;
        if (this.connected && this.socket) {
            this._joinRoomInternal(roomId);
        }
    }

    _joinRoomInternal(roomId) {
        this.socket.emit('adduser', {
            user_id: this.userId,
            room_id: roomId
        });
        this.socket.emit('joinRoom', {
            user_id: this.userId,
            room_id: roomId
        });
        this._debug('Joined room', { roomId: roomId, socketId: this.socket?.id ?? null });
    }

    leaveRoom() {
        if (this.socket && this.connected) {
            this.socket.emit('exitChat');
        }
        this.currentRoomId = null;
    }

    // ═══════════════════════════════════════════════
    // MESSAGING
    // ═══════════════════════════════════════════════

    sendMessage(data) {
        return this._emitOrQueue('sendMessage', {
            room_id: data.room_id,
            sender_id: data.sender_id || this.userId,
            receiver_id: data.receiver_id,
            content: data.content,
            type: data.type || 'text',
            duration: data.duration || null
        });
    }

    emitRelayMessage(data) {
        this._debug('emitRelayMessage called', data);
        return this._emitOrQueue('relayMessage', data);
    }

    // ═══════════════════════════════════════════════
    // TYPING INDICATORS
    // ═══════════════════════════════════════════════

    emitTyping(roomId, receiverId) {
        if (!this.socket || !this.connected) return;
        this.socket.emit('userTyping', {
            user_id: this.userId,
            room_id: roomId,
            receiver_id: receiverId
        });
    }

    emitStoppedTyping(roomId, receiverId) {
        if (!this.socket || !this.connected) return;
        this.socket.emit('userStoppedTyping', {
            user_id: this.userId,
            room_id: roomId,
            receiver_id: receiverId
        });
    }

    // ═══════════════════════════════════════════════
    // CALL SIGNALING
    // ═══════════════════════════════════════════════

    emitInitiateCall(data) {
        this._debug('emitInitiateCall called', data);
        return this._emitOrQueue('initiateCall', data);
    }

    emitAcceptCall(data) {
        this._debug('emitAcceptCall called', data);
        return this._emitOrQueue('acceptCall', data);
    }

    emitRejectCall(data) {
        this._debug('emitRejectCall called', data);
        return this._emitOrQueue('rejectCall', data);
    }

    emitCancelCall(data) {
        this._debug('emitCancelCall called', data);
        return this._emitOrQueue('cancelCall', data);
    }

    emitEndCall(data) {
        this._debug('emitEndCall called', data);
        return this._emitOrQueue('endCall', data);
    }

    // ═══════════════════════════════════════════════
    // CALLBACKS (Legacy support)
    // ═══════════════════════════════════════════════

    onMessage(callback) {
        this.messageCallbacks.push(callback);
    }

    onCall(callback) {
        this.callCallbacks.push(callback);
    }

    // ═══════════════════════════════════════════════
    // BROWSER NOTIFICATIONS
    // ═══════════════════════════════════════════════

    _showMessageNotification(data) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;

        try {
            const title = data.sender_name || 'رسالة جديدة';
            const body = data.type === 'text'
                ? data.content
                : (data.type === 'image' ? '📷 صورة' : data.type === 'sound' ? '🎤 رسالة صوتية' : '📎 ملف');

            new Notification(title, {
                body: body,
                icon: '/images/legal/logo.png',
                tag: `msg-${data.room_id}-${Date.now()}`,
                requireInteraction: false,
            });
        } catch (e) {
            console.log('[SocketClient] Notification failed:', e);
        }
    }

    _showCallNotification(data) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;

        try {
            const callerName = data.caller_name || data.callerName || 'مستخدم';
            const callType = data.call_type || data.callType || 'audio';
            const typeLabel = callType === 'video' ? 'مكالمة فيديو' : 'مكالمة صوتية';

            const notification = new Notification(`${callerName} - ${typeLabel}`, {
                body: `${typeLabel} واردة من ${callerName}`,
                icon: '/images/legal/logo.png',
                tag: `call-${data.room_id || data.roomId}`,
                requireInteraction: true,
            });

            notification.onclick = () => {
                window.focus();
                notification.close();
            };
        } catch (e) {
            console.log('[SocketClient] Call notification failed:', e);
        }
    }

    // ═══════════════════════════════════════════════
    // PRESENCE
    // ═══════════════════════════════════════════════

    _updatePresence(status) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) return;

        fetch('/api/chat/presence', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content
            },
            body: JSON.stringify({ status: status, platform: 'dashboard' })
        }).catch(err => console.log('[SocketClient] Presence update failed:', err.message));
    }

    // ═══════════════════════════════════════════════
    // DISCONNECT
    // ═══════════════════════════════════════════════

    disconnect() {
        if (this.socket) {
            this._updatePresence('offline');
            this.socket.disconnect();
            this.connected = false;
        }
    }

    isConnected() {
        return this.connected && this.socket && this.socket.connected;
    }
}

// ═══════════════════════════════════════════════════════════
// AUTO-INITIALIZE (runs once, survives Filament SPA navigation)
// ═══════════════════════════════════════════════════════════

window.SocketClient = SocketClient;

// Prevent double-initialization in SPA
if (!window.__socketInitialized) {
    window.__socketInitialized = true;

    document.addEventListener('DOMContentLoaded', () => {
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        const socketUrl = document.querySelector('meta[name="socket-url"]')?.content || 'https://qestass.com:4888';

        console.log(`[SocketClient][${new Date().toISOString()}] ════════════════════════════════`);
        console.log(`[SocketClient][${new Date().toISOString()}] Initializing...`);
        console.log(`[SocketClient][${new Date().toISOString()}] User ID:`, userId);
        console.log(`[SocketClient][${new Date().toISOString()}] Socket URL:`, socketUrl);
        console.log(`[SocketClient][${new Date().toISOString()}] ════════════════════════════════`);

        if (userId) {
            window.socket = new SocketClient(socketUrl, parseInt(userId));
            window.socket.connect();

            // Request notification permission
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    console.log('[SocketClient] Notification permission:', permission);
                });
            }

            // ═══════════════════════════════════════════════
            // Register Service Worker for background push
            // ═══════════════════════════════════════════════
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                    .then((registration) => {
                        console.log('[SocketClient] Service Worker registered:', registration.scope);
                    })
                    .catch((err) => {
                        console.log('[SocketClient] Service Worker registration failed:', err);
                    });

                // Listen for messages FROM the service worker (e.g., notification click)
                navigator.serviceWorker.addEventListener('message', (event) => {
                    console.log('[SocketClient] Message from SW:', event.data);
                    if (event.data?.type === 'CALL_NOTIFICATION_CLICK') {
                        // Focus the window and show the call modal
                        window.focus();
                        if (event.data.data && window.Livewire) {
                            window.Livewire.dispatch('incoming-call', { callData: event.data.data });
                        }
                    }
                });
            }

            // Graceful disconnect on page unload
            window.addEventListener('beforeunload', () => {
                window.socket.disconnect();
            });
        } else {
            console.warn('[SocketClient] No user ID found — not connecting');
        }
    });
}

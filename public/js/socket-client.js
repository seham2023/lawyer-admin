/**
 * Socket.IO Client Wrapper for Dashboard
 * Handles real-time communication between dashboard and Socket.IO server
 */

class SocketClient {
    constructor(url, userId) {
        this.url = url;
        this.userId = userId;
        this.socket = null;
        this.connected = false;
        this.messageCallbacks = [];
        this.callCallbacks = [];
    }

    /**
     * Connect to Socket.IO server
     */
    connect() {
        if (this.connected) {
            console.log('Already connected to Socket.IO');
            return;
        }

        this.socket = io(this.url, {
            transports: ['websocket', 'polling'],
            reconnection: true,
            reconnectionDelay: 1000,
            reconnectionAttempts: 5
        });

        this.socket.on('connect', () => {
            console.log('Connected to Socket.IO server');
            this.connected = true;
            
            // Emit dashboard connection event
            this.socket.emit('dashboardConnect', {
                user_id: this.userId,
                platform: 'dashboard'
            });

            // Update presence to online
            this.updatePresence('online');
        });

        this.socket.on('disconnect', () => {
            console.log('Disconnected from Socket.IO server');
            this.connected = false;
        });

        this.socket.on('connect_error', (error) => {
            console.error('Socket.IO connection error:', error);
        });

        // Listen for new messages
        this.socket.on('newMessage', (data) => {
            console.log('New message received:', data);
            this.messageCallbacks.forEach(callback => callback(data));
            
            // Show browser notification if page is not focused
            if (document.hidden) {
                this.showNotification(data);
            }
            
            // Play sound
            this.playSound('notification');
        });

        // Listen for incoming calls
        this.socket.on('call', (data) => {
            console.log('Incoming call:', data);
            this.callCallbacks.forEach(callback => callback(data));
            
            if (data.status === 'start') {
                this.playSound('call');
                this.showCallNotification(data);
            }
        });

        return this.socket;
    }

    /**
     * Join a specific room
     */
    joinRoom(roomId) {
        if (!this.socket) {
            console.error('Socket not connected');
            return;
        }

        this.socket.emit('adduser', {
            user_id: this.userId,
            room_id: roomId
        });

        console.log(`Joined room: ${roomId}`);
    }

    /**
     * Send a message
     */
    sendMessage(data) {
        if (!this.socket) {
            console.error('Socket not connected');
            return;
        }

        this.socket.emit('sendMessage', {
            room_id: data.room_id,
            sender_id: data.sender_id,
            receiver_id: data.receiver_id,
            content: data.content,
            type: data.type || 'text',
            duration: data.duration || null
        });
    }

    /**
     * Emit typing indicator
     */
    emitTyping(roomId, receiverId) {
        if (!this.socket) return;

        this.socket.emit('userTyping', {
            user_id: this.userId,
            room_id: roomId,
            receiver_id: receiverId
        });
    }

    /**
     * Emit stopped typing
     */
    emitStoppedTyping(roomId, receiverId) {
        if (!this.socket) return;

        this.socket.emit('userStoppedTyping', {
            user_id: this.userId,
            room_id: roomId,
            receiver_id: receiverId
        });
    }

    /**
     * Update user presence
     */
    updatePresence(status) {
        fetch('/api/chat/presence', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                status: status,
                platform: 'dashboard'
            })
        }).catch(error => console.error('Failed to update presence:', error));
    }

    /**
     * Listen for messages
     */
    onMessage(callback) {
        this.messageCallbacks.push(callback);
    }

    /**
     * Listen for calls
     */
    onCall(callback) {
        this.callCallbacks.push(callback);
    }

    /**
     * Show browser notification
     */
    showNotification(data) {
        if (!('Notification' in window)) {
            return;
        }

        if (Notification.permission === 'granted') {
            new Notification(data.sender_name || 'New Message', {
                body: data.type === 'text' ? data.content : `Sent a ${data.type}`,
                icon: '/images/logo.png',
                tag: `message-${data.room_id}`,
                requireInteraction: false
            });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission();
        }
    }

    /**
     * Show call notification
     */
    showCallNotification(data) {
        if (!('Notification' in window)) {
            return;
        }

        if (Notification.permission === 'granted') {
            new Notification(`${data.userName || 'Someone'} is calling`, {
                body: `Incoming ${data.callType} call`,
                icon: '/images/logo.png',
                tag: `call-${data.room_id}`,
                requireInteraction: true
            });
        }
    }

    /**
     * Play sound
     */
    playSound(type) {
        const audio = new Audio(`/sounds/${type}.mp3`);
        audio.volume = 0.5;
        audio.play().catch(error => console.log('Could not play sound:', error));
    }

    /**
     * Disconnect from server
     */
    disconnect() {
        if (this.socket) {
            this.updatePresence('offline');
            this.socket.disconnect();
            this.connected = false;
        }
    }
}

// Initialize global socket instance
window.SocketClient = SocketClient;

// Auto-connect when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const userId = document.querySelector('meta[name="user-id"]')?.content;
    const socketUrl = document.querySelector('meta[name="socket-url"]')?.content || 'https://qestass.com:4888';

    if (userId) {
        window.socket = new SocketClient(socketUrl, userId);
        window.socket.connect();

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Update presence to offline when page unloads
        window.addEventListener('beforeunload', () => {
            window.socket.disconnect();
        });
    }
});

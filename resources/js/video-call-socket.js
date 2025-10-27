/**
 * Video Call Socket.IO Handler for Web Dashboard
 * Manages real-time video call notifications and signaling
 */

import io from 'socket.io-client';

class VideoCallSocket {
    constructor(serverUrl = null) {
        this.serverUrl = serverUrl || `${window.location.protocol}//${window.location.hostname}:4722`;
        this.socket = null;
        this.userId = null;
        this.listeners = {};
    }

    /**
     * Connect to Socket.IO server
     */
    connect(userId) {
        this.userId = userId;

        this.socket = io(this.serverUrl, {
            reconnection: true,
            reconnectionDelay: 1000,
            reconnectionDelayMax: 5000,
            reconnectionAttempts: 5,
            transports: ['websocket', 'polling'],
        });

        this.socket.on('connect', () => {
            console.log('Connected to video call server');
            this.registerWebClient(userId);
            this.emit('connected');
        });

        this.socket.on('disconnect', () => {
            console.log('Disconnected from video call server');
            this.emit('disconnected');
        });

        this.socket.on('incomingCall', (data) => {
            console.log('Incoming call:', data);
            this.emit('incomingCall', data);
        });

        this.socket.on('callAnswered', (data) => {
            console.log('Call answered:', data);
            this.emit('callAnswered', data);
        });

        this.socket.on('callEnded', (data) => {
            console.log('Call ended:', data);
            this.emit('callEnded', data);
        });

        this.socket.on('callDeclined', (data) => {
            console.log('Call declined:', data);
            this.emit('callDeclined', data);
        });

        // Chat events
        this.socket.on('messageReceived', (data) => {
            console.log('Message received:', data);
            this.emit('messageReceived', data);
        });

        this.socket.on('typingIndicator', (data) => {
            console.log('User typing:', data);
            this.emit('typingIndicator', data);
        });

        this.socket.on('chatHistory', (data) => {
            console.log('Chat history:', data);
            this.emit('chatHistory', data);
        });

        this.socket.on('error', (error) => {
            console.error('Socket error:', error);
            this.emit('error', error);
        });
    }

    /**
     * Register web client with server
     */
    registerWebClient(userId) {
        if (this.socket) {
            this.socket.emit('registerWebClient', {
                userId: userId,
                timestamp: new Date(),
            });
        }
    }

    /**
     * Disconnect from Socket.IO server
     */
    disconnect() {
        if (this.socket) {
            this.socket.disconnect();
        }
    }

    /**
     * Send call status update
     */
    sendCallStatus(callData, status) {
        if (this.socket) {
            this.socket.emit('callStatus', {
                ...callData,
                status: status,
                timestamp: new Date(),
            });
        }
    }

    /**
     * Answer incoming call
     */
    answerCall(callId) {
        this.sendCallStatus({ callId }, 'answer');
    }

    /**
     * Decline incoming call
     */
    declineCall(callId) {
        this.sendCallStatus({ callId }, 'decline');
    }

    /**
     * End active call
     */
    endCall(callId) {
        this.sendCallStatus({ callId }, 'end');
    }

    /**
     * Send text message during call
     */
    sendMessage(callId, message, senderName = null) {
        if (this.socket) {
            this.socket.emit('sendMessage', {
                callId: callId,
                message: message,
                senderId: this.userId,
                senderName: senderName,
                timestamp: new Date(),
            });
        }
    }

    /**
     * Send typing indicator
     */
    sendTypingIndicator(callId, isTyping = true) {
        if (this.socket) {
            this.socket.emit('typingIndicator', {
                callId: callId,
                userId: this.userId,
                isTyping: isTyping,
                timestamp: new Date(),
            });
        }
    }

    /**
     * Request chat history for a call
     */
    requestChatHistory(callId) {
        if (this.socket) {
            this.socket.emit('getChatHistory', {
                callId: callId,
                userId: this.userId,
            });
        }
    }

    /**
     * Register event listener
     */
    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    }

    /**
     * Emit event to listeners
     */
    emit(event, data = null) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(callback => {
                callback(data);
            });
        }
    }

    /**
     * Remove event listener
     */
    off(event, callback) {
        if (this.listeners[event]) {
            this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
        }
    }

    /**
     * Check if connected
     */
    isConnected() {
        return this.socket && this.socket.connected;
    }
}

// Export singleton instance
export default new VideoCallSocket();


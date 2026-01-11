# 🏗️ Chat System Technical Architecture

## Overview

This document provides detailed technical architecture for implementing real-time text, audio, and video chat in the lawyer dashboard using Socket.IO and TokBox.

---

## 🎯 System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Client (Browser)                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Filament   │  │  Socket.IO   │  │    TokBox    │      │
│  │   + Livewire │  │    Client    │  │    Client    │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
└─────────┼──────────────────┼──────────────────┼─────────────┘
          │                  │                  │
          │ HTTP/HTTPS       │ WebSocket        │ WebRTC
          │                  │                  │
┌─────────▼──────────────────▼──────────────────▼─────────────┐
│                     Load Balancer (Nginx)                    │
└─────────┬──────────────────┬──────────────────┬─────────────┘
          │                  │                  │
┌─────────▼──────────┐ ┌─────▼──────────┐ ┌────▼─────────────┐
│  Laravel Backend   │ │  Socket.IO     │ │  TokBox Media    │
│  (API + Web)       │ │  Server        │ │  Server (Cloud)  │
│                    │ │  (Node.js)     │ │                  │
│  ┌──────────────┐  │ │                │ │                  │
│  │ Controllers  │  │ │  ┌──────────┐  │ │                  │
│  │ Services     │  │ │  │ Handlers │  │ │                  │
│  │ Models       │  │ │  │ Auth     │  │ │                  │
│  └──────┬───────┘  │ │  └────┬─────┘  │ │                  │
└─────────┼──────────┘ └───────┼────────┘ └──────────────────┘
          │                    │
          │                    │
┌─────────▼────────────────────▼─────────┐
│         Database (MySQL/PostgreSQL)     │
│  ┌──────────────────────────────────┐  │
│  │ conversations                    │  │
│  │ conversation_participants        │  │
│  │ messages                         │  │
│  │ video_calls                      │  │
│  └──────────────────────────────────┘  │
└─────────────────────────────────────────┘
          │
┌─────────▼─────────┐
│  Redis (Cache)    │
│  - Sessions       │
│  - Typing Status  │
│  - Online Users   │
└───────────────────┘
```

---

## 📊 Database Schema

### 1. conversations

```sql
CREATE TABLE conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('direct', 'group') NOT NULL DEFAULT 'direct',
    name VARCHAR(255) NULL COMMENT 'Group chat name',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_type (type),
    INDEX idx_created_by (created_by)
);
```

### 2. conversation_participants

```sql
CREATE TABLE conversation_participants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_read_at TIMESTAMP NULL,
    is_muted BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant (conversation_id, user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_conversation_id (conversation_id)
);
```

### 3. messages

```sql
CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    message_type ENUM('text', 'audio', 'video', 'file', 'image') NOT NULL DEFAULT 'text',
    content TEXT NULL COMMENT 'Text message content',
    file_path VARCHAR(500) NULL COMMENT 'Path to uploaded file',
    file_name VARCHAR(255) NULL,
    file_size INT UNSIGNED NULL COMMENT 'File size in bytes',
    duration INT UNSIGNED NULL COMMENT 'Audio/video duration in seconds',
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_created_at (created_at),
    INDEX idx_is_read (is_read)
);
```

### 4. video_calls

```sql
CREATE TABLE video_calls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    session_id VARCHAR(255) NOT NULL COMMENT 'TokBox session ID',
    token TEXT NOT NULL COMMENT 'TokBox token',
    started_by BIGINT UNSIGNED NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    duration INT UNSIGNED NULL COMMENT 'Call duration in seconds',
    status ENUM('initiated', 'ongoing', 'ended', 'missed') NOT NULL DEFAULT 'initiated',

    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (started_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
);
```

---

## 🔌 API Endpoints

### Conversations

#### GET /api/conversations

**Description**: List all conversations for authenticated user
**Response**:

```json
{
    "data": [
        {
            "id": 1,
            "type": "direct",
            "name": null,
            "participants": [
                {
                    "id": 1,
                    "name": "John Doe",
                    "avatar": "https://..."
                }
            ],
            "last_message": {
                "id": 100,
                "content": "Hello",
                "created_at": "2026-01-06T10:00:00Z"
            },
            "unread_count": 5
        }
    ]
}
```

#### POST /api/conversations

**Description**: Create new conversation
**Request**:

```json
{
    "type": "direct",
    "participant_ids": [2, 3]
}
```

#### GET /api/conversations/{id}

**Description**: Get conversation details

#### DELETE /api/conversations/{id}

**Description**: Delete conversation

---

### Messages

#### GET /api/conversations/{id}/messages

**Description**: Get messages for conversation
**Query Parameters**:

-   `page` (int): Page number
-   `per_page` (int): Messages per page (default: 50)

**Response**:

```json
{
    "data": [
        {
            "id": 1,
            "sender_id": 1,
            "sender": {
                "id": 1,
                "name": "John Doe",
                "avatar": "https://..."
            },
            "message_type": "text",
            "content": "Hello, how are you?",
            "is_read": false,
            "created_at": "2026-01-06T10:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 100
    }
}
```

#### POST /api/conversations/{id}/messages

**Description**: Send message
**Request**:

```json
{
    "message_type": "text",
    "content": "Hello!"
}
```

#### POST /api/conversations/{id}/messages/upload

**Description**: Upload file/media
**Request**: Multipart form data

-   `file`: File to upload
-   `type`: 'image', 'audio', 'video', 'file'

#### POST /api/messages/{id}/read

**Description**: Mark message as read

---

### Video Calls

#### POST /api/conversations/{id}/video-call/initiate

**Description**: Initiate video call
**Response**:

```json
{
    "call_id": 1,
    "session_id": "1_MX40...",
    "token": "T1==cGFydG5lcl9pZD...",
    "api_key": "47112345"
}
```

#### POST /api/video-call/{id}/end

**Description**: End video call

---

## 🔄 Socket.IO Events

### Client → Server Events

#### `join_conversation`

**Payload**:

```json
{
    "conversation_id": 1
}
```

#### `leave_conversation`

**Payload**:

```json
{
    "conversation_id": 1
}
```

#### `send_message`

**Payload**:

```json
{
    "conversation_id": 1,
    "message_type": "text",
    "content": "Hello!"
}
```

#### `typing_start`

**Payload**:

```json
{
    "conversation_id": 1
}
```

#### `typing_stop`

**Payload**:

```json
{
    "conversation_id": 1
}
```

#### `mark_read`

**Payload**:

```json
{
    "message_id": 100
}
```

#### `initiate_call`

**Payload**:

```json
{
    "conversation_id": 1,
    "call_type": "video"
}
```

#### `end_call`

**Payload**:

```json
{
    "call_id": 1
}
```

---

### Server → Client Events

#### `new_message`

**Payload**:

```json
{
    "id": 100,
    "conversation_id": 1,
    "sender_id": 2,
    "sender": {
        "id": 2,
        "name": "Jane Doe",
        "avatar": "https://..."
    },
    "message_type": "text",
    "content": "Hi there!",
    "created_at": "2026-01-06T10:00:00Z"
}
```

#### `message_sent`

**Payload**:

```json
{
    "temp_id": "temp_123",
    "message": {
        /* message object */
    }
}
```

#### `user_typing`

**Payload**:

```json
{
    "conversation_id": 1,
    "user_id": 2,
    "user_name": "Jane Doe"
}
```

#### `user_stopped_typing`

**Payload**:

```json
{
    "conversation_id": 1,
    "user_id": 2
}
```

#### `message_read`

**Payload**:

```json
{
    "message_id": 100,
    "read_by": 2,
    "read_at": "2026-01-06T10:05:00Z"
}
```

#### `call_initiated`

**Payload**:

```json
{
    "call_id": 1,
    "conversation_id": 1,
    "session_id": "1_MX40...",
    "token": "T1==cGFydG5lcl9pZD...",
    "api_key": "47112345",
    "started_by": {
        "id": 2,
        "name": "Jane Doe"
    }
}
```

#### `call_ended`

**Payload**:

```json
{
    "call_id": 1,
    "duration": 300
}
```

#### `user_joined`

**Payload**:

```json
{
    "conversation_id": 1,
    "user": {
        "id": 3,
        "name": "Bob Smith"
    }
}
```

#### `user_left`

**Payload**:

```json
{
    "conversation_id": 1,
    "user_id": 3
}
```

---

## 🔐 Authentication Flow

### 1. User Login

```
User → Laravel → Generate Sanctum Token → Return to Client
```

### 2. Socket.IO Connection

```javascript
const socket = io("http://localhost:3000", {
    auth: {
        token: "sanctum_token_here",
    },
});
```

### 3. Server-side Token Validation

```javascript
io.use(async (socket, next) => {
    const token = socket.handshake.auth.token;

    // Validate token with Laravel API
    const response = await fetch("http://laravel-api/api/user", {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    });

    if (response.ok) {
        const user = await response.json();
        socket.user = user;
        next();
    } else {
        next(new Error("Authentication failed"));
    }
});
```

---

## 📁 File Structure

### Laravel

```
app/
├── Models/
│   ├── Conversation.php
│   ├── ConversationParticipant.php
│   ├── Message.php
│   └── VideoCall.php
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── ConversationController.php
│           ├── MessageController.php
│           └── VideoCallController.php
├── Services/
│   ├── ChatService.php
│   └── TokBoxService.php
├── Events/
│   ├── MessageSent.php
│   └── CallInitiated.php
└── Filament/
    ├── Pages/
    │   └── Chat.php
    └── Livewire/
        ├── ChatConversationList.php
        ├── ChatMessageList.php
        ├── ChatMessageInput.php
        └── VideoCallModal.php

database/
└── migrations/
    ├── *_create_conversations_table.php
    ├── *_create_conversation_participants_table.php
    ├── *_create_messages_table.php
    └── *_create_video_calls_table.php

resources/
└── views/
    ├── filament/
    │   └── pages/
    │       └── chat.blade.php
    └── livewire/
        ├── chat-conversation-list.blade.php
        ├── chat-message-list.blade.php
        ├── chat-message-input.blade.php
        └── video-call-modal.blade.php
```

### Node.js Socket.IO Server

```
backend/
└── socket-server/
    ├── server.js
    ├── package.json
    ├── .env
    ├── middleware/
    │   └── auth.js
    ├── handlers/
    │   ├── messageHandler.js
    │   ├── callHandler.js
    │   └── typingHandler.js
    └── utils/
        ├── logger.js
        └── tokbox.js
```

---

## 🎨 Frontend Components

### Chat Page Layout

```blade
<div class="chat-container">
    <!-- Sidebar: Conversation List -->
    <div class="conversations-sidebar">
        @livewire('chat-conversation-list')
    </div>

    <!-- Main: Message Area -->
    <div class="messages-area">
        <!-- Header -->
        <div class="chat-header">
            <div class="participant-info">...</div>
            <div class="actions">
                <button wire:click="initiateVideoCall">
                    Video Call
                </button>
            </div>
        </div>

        <!-- Messages -->
        <div class="messages-list">
            @livewire('chat-message-list', ['conversationId' => $selectedConversation])
        </div>

        <!-- Input -->
        <div class="message-input">
            @livewire('chat-message-input', ['conversationId' => $selectedConversation])
        </div>
    </div>
</div>

<!-- Video Call Modal -->
@livewire('video-call-modal')
```

---

## 🔊 Audio Recording (Voice Notes)

### Browser MediaRecorder API

```javascript
let mediaRecorder;
let audioChunks = [];

async function startRecording() {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    mediaRecorder = new MediaRecorder(stream);

    mediaRecorder.ondataavailable = (event) => {
        audioChunks.push(event.data);
    };

    mediaRecorder.onstop = async () => {
        const audioBlob = new Blob(audioChunks, { type: "audio/webm" });
        await uploadAudio(audioBlob);
        audioChunks = [];
    };

    mediaRecorder.start();
}

function stopRecording() {
    mediaRecorder.stop();
}

async function uploadAudio(blob) {
    const formData = new FormData();
    formData.append("file", blob, "voice-note.webm");
    formData.append("type", "audio");

    const response = await fetch(
        `/api/conversations/${conversationId}/messages/upload`,
        {
            method: "POST",
            headers: {
                Authorization: `Bearer ${token}`,
            },
            body: formData,
        }
    );

    const message = await response.json();
    // Emit via Socket.IO
    socket.emit("send_message", message);
}
```

---

## 📹 TokBox Video Integration

### Initialize Session

```javascript
const apiKey = "47112345";
const sessionId = "1_MX40...";
const token = "T1==cGFydG5lcl9pZD...";

// Initialize session
const session = OT.initSession(apiKey, sessionId);

// Subscribe to streams
session.on("streamCreated", (event) => {
    session.subscribe(event.stream, "subscriber", {
        insertMode: "append",
        width: "100%",
        height: "100%",
    });
});

// Connect to session
session.connect(token, (error) => {
    if (!error) {
        // Publish own stream
        const publisher = OT.initPublisher("publisher", {
            insertMode: "append",
            width: "100%",
            height: "100%",
        });

        session.publish(publisher);
    }
});
```

### End Call

```javascript
function endCall() {
    session.disconnect();
    // Notify server
    fetch(`/api/video-call/${callId}/end`, {
        method: "POST",
        headers: {
            Authorization: `Bearer ${token}`,
        },
    });
}
```

---

## 🚀 Deployment Considerations

### Socket.IO Server Deployment

-   Use PM2 for process management
-   Enable clustering for scalability
-   Use Redis adapter for multi-server setup
-   Configure Nginx as reverse proxy

### Example PM2 Configuration

```javascript
// ecosystem.config.js
module.exports = {
    apps: [
        {
            name: "socket-server",
            script: "./server.js",
            instances: "max",
            exec_mode: "cluster",
            env: {
                NODE_ENV: "production",
                PORT: 3000,
            },
        },
    ],
};
```

### Nginx Configuration

```nginx
upstream socket_io {
    ip_hash;
    server 127.0.0.1:3000;
    server 127.0.0.1:3001;
}

server {
    listen 80;
    server_name chat.example.com;

    location / {
        proxy_pass http://socket_io;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
    }
}
```

---

## 📊 Performance Optimization

### Database Indexing

-   Index `conversation_id` in messages table
-   Index `sender_id` in messages table
-   Index `created_at` for message pagination
-   Composite index on `(conversation_id, created_at)`

### Caching Strategy

-   Cache conversation list in Redis
-   Cache unread message counts
-   Cache online user status
-   TTL: 5-10 minutes

### Message Pagination

-   Load 50 messages per page
-   Implement infinite scroll
-   Use cursor-based pagination for better performance

---

## 🔔 Notification Strategy

### Browser Notifications

```javascript
// Request permission
Notification.requestPermission();

// Show notification
socket.on("new_message", (message) => {
    if (Notification.permission === "granted") {
        new Notification("New Message", {
            body: message.content,
            icon: message.sender.avatar,
        });
    }
});
```

### Sound Alerts

```javascript
const notificationSound = new Audio("/sounds/notification.mp3");

socket.on("new_message", () => {
    notificationSound.play();
});
```

---

**Last Updated**: 2026-01-06
**Version**: 1.0

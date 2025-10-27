# Text Chat Implementation for Video Calls

## Overview

This implementation adds real-time text chat functionality to video calls using Socket.IO. Lawyers and clients can exchange messages during active calls with typing indicators and message history.

## Features

✅ **Real-time messaging** via Socket.IO
✅ **Typing indicators** to show when someone is typing
✅ **Message history** stored in database
✅ **Persistent storage** of all messages
✅ **Message deletion** for users
✅ **Pagination** for chat history
✅ **Responsive UI** with dark mode support
✅ **Auto-scroll** to latest messages

## Architecture

```
Client (Web/Mobile)
    ↓
Socket.IO Event: sendMessage
    ↓
Node.js Server (web-socket-handler.js)
    ↓
├─→ Store in callChats (memory)
├─→ Broadcast to other participant
└─→ Save to database via API
    ↓
Database (call_messages table)
```

## Database Schema

### call_messages Table
```sql
CREATE TABLE call_messages (
    id BIGINT PRIMARY KEY,
    call_id BIGINT NOT NULL,
    sender_id BIGINT NOT NULL,
    message LONGTEXT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (call_id) REFERENCES video_calls(id),
    FOREIGN KEY (sender_id) REFERENCES users(id),
    INDEX (call_id),
    INDEX (sender_id),
    INDEX (created_at)
);
```

## Files Created

### Backend
- `app/Models/CallMessage.php` - Message model
- `app/Http/Controllers/CallMessageController.php` - API controller
- `app/Livewire/CallChatInterface.php` - Chat component
- `database/migrations/2025_10_27_000001_create_call_messages_table.php` - Migration
- `backend/nodejs/web-socket-handler.js` - Updated with chat functions

### Frontend
- `resources/js/video-call-socket.js` - Updated with chat methods
- `resources/views/livewire/call-chat-interface.blade.php` - Chat UI

### Routes
- `routes/api.php` - Updated with chat endpoints

## API Endpoints

### Send Message
```http
POST /api/call-messages/send
Content-Type: application/json

{
    "call_id": 1,
    "message": "Hello, can you hear me?"
}
```

**Response:**
```json
{
    "success": true,
    "message": {
        "id": 1,
        "sender_id": 1,
        "sender_name": "John Doe",
        "sender_avatar": "https://...",
        "message": "Hello, can you hear me?",
        "created_at": "14:30"
    }
}
```

### Get Chat History
```http
GET /api/call-messages/history?call_id=1&limit=50
```

**Response:**
```json
{
    "success": true,
    "messages": [
        {
            "id": 1,
            "sender_id": 1,
            "sender_name": "John Doe",
            "sender_avatar": "https://...",
            "message": "Hello!",
            "created_at": "14:30",
            "is_own": true
        }
    ],
    "count": 1
}
```

### Get Messages (Paginated)
```http
GET /api/call-messages?call_id=1&page=1&per_page=20
```

### Delete Message
```http
DELETE /api/call-messages/{messageId}
```

## Socket.IO Events

### Client → Server
```javascript
// Send message
socket.emit('sendMessage', {
    callId: 1,
    message: 'Hello!',
    senderId: 1,
    senderName: 'John Doe',
    timestamp: new Date()
});

// Send typing indicator
socket.emit('typingIndicator', {
    callId: 1,
    userId: 1,
    isTyping: true,
    timestamp: new Date()
});

// Request chat history
socket.emit('getChatHistory', {
    callId: 1,
    userId: 1
});
```

### Server → Client
```javascript
// Receive message
socket.on('messageReceived', (data) => {
    console.log('New message:', data);
});

// Typing indicator
socket.on('typingIndicator', (data) => {
    console.log('User typing:', data);
});

// Chat history
socket.on('chatHistory', (data) => {
    console.log('Chat history:', data);
});
```

## Usage in Livewire

### Basic Usage
```blade
<livewire:call-chat-interface :callId="$callId" />
```

### In Video Call Component
```blade
<div class="grid grid-cols-3 gap-4 h-screen">
    <!-- Video -->
    <div class="col-span-2">
        <livewire:video-call-interface :callId="$callId" />
    </div>
    
    <!-- Chat -->
    <div class="col-span-1">
        <livewire:call-chat-interface :callId="$callId" />
    </div>
</div>
```

## JavaScript Integration

### Using Socket.IO Client
```javascript
import videoCallSocket from '/resources/js/video-call-socket.js';

// Connect
videoCallSocket.connect(userId);

// Send message
videoCallSocket.sendMessage(callId, 'Hello!', 'John Doe');

// Listen for messages
videoCallSocket.on('messageReceived', (data) => {
    console.log('Message:', data.message);
});

// Send typing indicator
videoCallSocket.sendTypingIndicator(callId, true);

// Request chat history
videoCallSocket.requestChatHistory(callId);
```

## Node.js Server Integration

### Update server.js
```javascript
const webSocketHandler = require('./web-socket-handler');

io.on('connection', (socket) => {
    // Handle message
    socket.on('sendMessage', (data) => {
        const participantIds = [data.senderId, data.receiverId];
        webSocketHandler.broadcastMessage(data.callId, data, participantIds);
    });

    // Handle typing indicator
    socket.on('typingIndicator', (data) => {
        const participantIds = [data.senderId, data.receiverId];
        webSocketHandler.broadcastTypingIndicator(data.callId, data, participantIds);
    });

    // Handle chat history request
    socket.on('getChatHistory', (data) => {
        const history = webSocketHandler.getChatHistory(data.callId);
        socket.emit('chatHistory', {
            callId: data.callId,
            messages: history
        });
    });
});
```

## Installation Steps

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Update Node.js Server
Follow the integration steps above to add chat event handlers.

### 3. Add to Filament Page
```blade
<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2">
        <livewire:video-call-interface :callId="$callId" />
    </div>
    <div class="col-span-1">
        <livewire:call-chat-interface :callId="$callId" />
    </div>
</div>
```

## Features Explained

### Real-time Messaging
Messages are sent via Socket.IO and stored in the database for persistence.

### Typing Indicators
Shows when the other participant is typing. Automatically stops after 3 seconds of inactivity.

### Message History
All messages are stored in the database and can be retrieved with pagination.

### Auto-scroll
Chat automatically scrolls to the latest message when new messages arrive.

### Message Deletion
Users can delete their own messages. Deleted messages are removed from the database.

## Troubleshooting

### Messages Not Appearing
- Check Socket.IO connection in browser console
- Verify Node.js server is running
- Check database for stored messages

### Typing Indicator Not Working
- Verify Socket.IO events are being emitted
- Check Node.js server logs
- Ensure user IDs are correct

### Chat History Not Loading
- Check database for messages
- Verify API endpoint is accessible
- Check browser console for errors

## Performance Considerations

### Database Indexes
The migration includes indexes on:
- `call_id` - for fast message retrieval per call
- `sender_id` - for filtering messages by sender
- `created_at` - for sorting messages by time

### Message Pagination
Use pagination to avoid loading too many messages at once:
```php
$messages = CallMessage::where('call_id', $callId)
    ->paginate(20);
```

### Memory Management
The Node.js server stores chat in memory (`callChats`). Consider clearing old chats:
```javascript
// Clear chat history after call ends
webSocketHandler.clearChatHistory(callId);
```

## Security

### Authorization
All API endpoints verify that the user is part of the call before allowing access.

### Message Validation
Messages are validated for length (max 5000 characters) and content.

### User Verification
Only the message sender can delete their own messages.

## Future Enhancements

- [ ] Message reactions/emojis
- [ ] File sharing during calls
- [ ] Message search
- [ ] Message pinning
- [ ] Read receipts
- [ ] Message encryption
- [ ] Voice messages
- [ ] Message editing


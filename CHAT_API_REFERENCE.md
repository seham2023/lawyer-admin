# Chat API Reference

## Base URL
```
https://your-domain.com/api
```

## Authentication
All endpoints require Laravel Sanctum authentication:
```
Authorization: Bearer YOUR_TOKEN
```

## Endpoints

### 1. Send Message

**Endpoint:** `POST /call-messages/send`

**Description:** Send a text message during a video call

**Request Body:**
```json
{
    "call_id": 1,
    "message": "Hello, can you hear me?"
}
```

**Parameters:**
- `call_id` (required, integer) - ID of the video call
- `message` (required, string, max 5000) - Message content

**Response (200 OK):**
```json
{
    "success": true,
    "message": {
        "id": 1,
        "sender_id": 1,
        "sender_name": "John Doe",
        "sender_avatar": "https://example.com/avatar.jpg",
        "message": "Hello, can you hear me?",
        "created_at": "14:30"
    }
}
```

**Error Responses:**
- `403 Unauthorized` - User is not part of this call
- `422 Unprocessable Entity` - Validation error

---

### 2. Get Chat History

**Endpoint:** `GET /call-messages/history`

**Description:** Get all messages for a specific call

**Query Parameters:**
- `call_id` (required, integer) - ID of the video call
- `limit` (optional, integer, default 50, max 100) - Number of messages to retrieve

**Example:**
```
GET /call-messages/history?call_id=1&limit=50
```

**Response (200 OK):**
```json
{
    "success": true,
    "messages": [
        {
            "id": 1,
            "sender_id": 1,
            "sender_name": "John Doe",
            "sender_avatar": "https://example.com/avatar.jpg",
            "message": "Hello!",
            "created_at": "14:30",
            "is_own": true
        },
        {
            "id": 2,
            "sender_id": 2,
            "sender_name": "Jane Smith",
            "sender_avatar": "https://example.com/avatar2.jpg",
            "message": "Hi there!",
            "created_at": "14:31",
            "is_own": false
        }
    ],
    "count": 2
}
```

**Error Responses:**
- `403 Unauthorized` - User is not part of this call
- `404 Not Found` - Call not found

---

### 3. Get Messages (Paginated)

**Endpoint:** `GET /call-messages`

**Description:** Get paginated messages for a call

**Query Parameters:**
- `call_id` (required, integer) - ID of the video call
- `page` (optional, integer, default 1) - Page number
- `per_page` (optional, integer, default 20, max 100) - Messages per page

**Example:**
```
GET /call-messages?call_id=1&page=1&per_page=20
```

**Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "call_id": 1,
            "sender_id": 1,
            "message": "Hello!",
            "created_at": "2025-10-27T14:30:00Z",
            "updated_at": "2025-10-27T14:30:00Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 50,
        "last_page": 3
    }
}
```

---

### 4. Delete Message

**Endpoint:** `DELETE /call-messages/{messageId}`

**Description:** Delete a message (only sender can delete)

**URL Parameters:**
- `messageId` (required, integer) - ID of the message to delete

**Example:**
```
DELETE /call-messages/1
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Message deleted"
}
```

**Error Responses:**
- `403 Unauthorized` - User is not the message sender
- `404 Not Found` - Message not found

---

## Socket.IO Events

### Client → Server

#### Send Message
```javascript
socket.emit('sendMessage', {
    callId: 1,
    message: 'Hello!',
    senderId: 1,
    senderName: 'John Doe',
    timestamp: new Date()
});
```

#### Send Typing Indicator
```javascript
socket.emit('typingIndicator', {
    callId: 1,
    userId: 1,
    isTyping: true,
    timestamp: new Date()
});
```

#### Request Chat History
```javascript
socket.emit('getChatHistory', {
    callId: 1,
    userId: 1
});
```

### Server → Client

#### Message Received
```javascript
socket.on('messageReceived', (data) => {
    console.log('New message:', {
        callId: data.callId,
        senderId: data.senderId,
        senderName: data.senderName,
        message: data.message,
        timestamp: data.timestamp
    });
});
```

#### Typing Indicator
```javascript
socket.on('typingIndicator', (data) => {
    console.log('User typing:', {
        callId: data.callId,
        userId: data.userId,
        isTyping: data.isTyping
    });
});
```

#### Chat History
```javascript
socket.on('chatHistory', (data) => {
    console.log('Chat history:', {
        callId: data.callId,
        messages: data.messages
    });
});
```

---

## Usage Examples

### JavaScript/Vue
```javascript
import videoCallSocket from '/resources/js/video-call-socket.js';

// Connect
videoCallSocket.connect(userId);

// Send message
videoCallSocket.sendMessage(callId, 'Hello!', 'John Doe');

// Listen for messages
videoCallSocket.on('messageReceived', (data) => {
    console.log('Message from', data.senderName, ':', data.message);
});

// Send typing indicator
videoCallSocket.sendTypingIndicator(callId, true);

// Stop typing
videoCallSocket.sendTypingIndicator(callId, false);

// Request history
videoCallSocket.requestChatHistory(callId);
```

### PHP/Laravel
```php
use App\Models\CallMessage;

// Get messages for a call
$messages = CallMessage::where('call_id', $callId)
    ->with('sender')
    ->orderBy('created_at', 'asc')
    ->get();

// Send message
$message = CallMessage::create([
    'call_id' => $callId,
    'sender_id' => auth()->id(),
    'message' => 'Hello!',
]);

// Delete message
$message->delete();
```

### cURL
```bash
# Send message
curl -X POST https://your-domain.com/api/call-messages/send \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "call_id": 1,
    "message": "Hello!"
  }'

# Get chat history
curl -X GET "https://your-domain.com/api/call-messages/history?call_id=1&limit=50" \
  -H "Authorization: Bearer TOKEN"

# Delete message
curl -X DELETE https://your-domain.com/api/call-messages/1 \
  -H "Authorization: Bearer TOKEN"
```

---

## Rate Limiting

- **Send Message:** 10 messages per minute per user
- **Get History:** 30 requests per minute per user
- **Delete Message:** 20 requests per minute per user

---

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 400 | Bad Request | Invalid request parameters |
| 403 | Unauthorized | User not authorized for this action |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Server Error | Internal server error |

---

## Best Practices

1. **Validate Input** - Always validate message content on client side
2. **Handle Errors** - Implement proper error handling for failed requests
3. **Pagination** - Use pagination for large chat histories
4. **Typing Indicators** - Debounce typing indicators to reduce server load
5. **Message Limits** - Enforce message length limits (max 5000 chars)
6. **Timestamps** - Always include timestamps for message ordering
7. **User Verification** - Verify user is part of the call before sending messages

---

## Webhooks (Future)

Future versions may support webhooks for:
- New message notifications
- Call ended events
- User typing events


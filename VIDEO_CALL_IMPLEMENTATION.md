# Video Call Implementation for Filament Dashboard

This document describes the implementation of video calling functionality in the Filament dashboard, allowing lawyers to receive and answer calls from both mobile clients and the web dashboard.

## Overview

The video call system integrates:
- **Laravel Backend**: API endpoints for call management
- **Filament Dashboard**: Web interface for lawyers
- **Node.js Server**: OpenTok session management and Socket.IO signaling
- **Mobile App**: Existing Android client
- **OpenTok**: Video/audio streaming

## Architecture

```
Mobile Client (Android)
    ↓
Node.js Server (OpenTok + Socket.IO)
    ↓
├─→ Mobile Client (FCM Notifications)
└─→ Web Dashboard (Socket.IO Events)
    ↓
Laravel API
    ↓
Database (VideoCall Model)
```

## Database Schema

### VideoCall Model
- `id`: Primary key
- `caller_id`: User initiating the call
- `receiver_id`: User receiving the call
- `case_record_id`: Associated case (optional)
- `session_id`: OpenTok session ID
- `token`: OpenTok token
- `api_key`: OpenTok API key
- `status`: pending, active, ended, missed, declined
- `call_type`: audio or video
- `started_at`: Call start timestamp
- `answered_at`: Call answer timestamp
- `ended_at`: Call end timestamp
- `duration`: Call duration in seconds
- `answered_on_web`: Boolean flag for web answer
- `answered_on_mobile`: Boolean flag for mobile answer

## API Endpoints

### Create Video Session
```
POST /api/video-calls/create-session
{
    "receiver_id": 2,
    "case_record_id": 1,
    "call_type": "video"
}
```

### Answer Call
```
POST /api/video-calls/answer
{
    "call_id": 1,
    "answered_on_web": true
}
```

### End Call
```
POST /api/video-calls/end
{
    "call_id": 1
}
```

### Decline Call
```
POST /api/video-calls/decline
{
    "call_id": 1
}
```

### Get Pending Calls
```
GET /api/video-calls/pending
```

### Get Call History
```
GET /api/video-calls/history
```

## Filament Pages

### VideoCalls Page
- Location: `/admin/video-calls`
- Shows pending, active, and historical calls
- Provides UI for answering/declining/ending calls

## Socket.IO Events

### Client → Server
- `registerWebClient`: Register web client connection
- `callStatus`: Send call status updates

### Server → Client
- `incomingCall`: Incoming call notification
- `callAnswered`: Call answered notification
- `callEnded`: Call ended notification
- `callDeclined`: Call declined notification

## Configuration

Add to `.env`:
```
OPENTOK_NODE_SERVER_URL=https://your-domain.com:4722
OPENTOK_API_KEY=your_api_key
OPENTOK_API_SECRET=your_api_secret
```

## Installation Steps

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Update Node.js Server
Integrate the web socket handler into your Node.js server:
```javascript
const webSocketHandler = require('./web-socket-handler');

// In Socket.IO connection handler
socket.on('registerWebClient', (data) => {
    webSocketHandler.registerWebClient(socket, data.userId);
});

socket.on('disconnect', () => {
    webSocketHandler.unregisterWebClient(socket.userId);
});
```

### 3. Install JavaScript Dependencies
```bash
npm install socket.io-client
```

### 4. Include Socket.IO Script
Add to your Filament layout or use Vite:
```blade
<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
```

### 5. Initialize Socket Connection
In your Filament page or component:
```javascript
import videoCallSocket from '/resources/js/video-call-socket.js';

// Connect when page loads
videoCallSocket.connect(userId);

// Listen for incoming calls
videoCallSocket.on('incomingCall', (data) => {
    // Show incoming call notification
});
```

## Usage Flow

### Incoming Call (Mobile → Web)
1. Mobile client initiates call via `createSessionToken` API
2. Node.js creates OpenTok session
3. Node.js sends FCM notification to mobile receiver
4. Node.js sends Socket.IO event to web dashboard
5. Web dashboard shows incoming call notification
6. Lawyer clicks "Answer" button
7. Web dashboard connects to OpenTok session
8. Video/audio stream established

### Answering Call on Web
1. Lawyer sees incoming call notification
2. Clicks "Answer" button
3. `answerCall` API endpoint called
4. VideoCall record updated with `answered_on_web = true`
5. Livewire component initializes OpenTok publisher/subscriber
6. Video call interface displayed

### Ending Call
1. Either party clicks "End Call"
2. `endCall` API endpoint called
3. VideoCall record updated with `status = ended` and duration
4. OpenTok session disconnected
5. Socket.IO notification sent to other party
6. Call history updated

## Files Created/Modified

### New Files
- `app/Models/VideoCall.php` - Video call model
- `app/Http/Controllers/VideoCallController.php` - API controller
- `app/Filament/Pages/VideoCalls.php` - Filament page
- `app/Livewire/VideoCallInterface.php` - Livewire component
- `routes/api.php` - API routes
- `database/migrations/2025_10_27_000000_create_video_calls_table.php` - Database migration
- `resources/views/filament/pages/video-calls.blade.php` - Filament view
- `resources/views/livewire/video-call-interface.blade.php` - Livewire view
- `resources/js/video-call-socket.js` - Socket.IO client
- `backend/nodejs/web-socket-handler.js` - Node.js socket handler

### Modified Files
- `bootstrap/app.php` - Added API routes
- `config/services.php` - Added OpenTok configuration

## Testing

### Test Incoming Call
1. Use mobile app to call a lawyer
2. Check if notification appears on web dashboard
3. Click "Answer" to accept call
4. Verify video/audio stream

### Test Call Decline
1. Receive incoming call on web
2. Click "Decline"
3. Verify call status changes to "declined"

### Test Call History
1. Complete a call
2. Navigate to `/admin/video-calls`
3. Verify call appears in history with correct duration

## Troubleshooting

### Socket.IO Connection Issues
- Verify Node.js server is running on port 4722
- Check CORS configuration in Node.js server
- Ensure SSL certificates are valid

### OpenTok Session Issues
- Verify API key and secret in configuration
- Check OpenTok account quota
- Ensure session creation endpoint is accessible

### Video/Audio Not Working
- Check browser permissions for camera/microphone
- Verify OpenTok SDK is loaded
- Check browser console for errors

## Future Enhancements

- [ ] Call recording
- [ ] Screen sharing
- [ ] Call transfer
- [ ] Conference calls
- [ ] Call scheduling
- [ ] Call analytics
- [ ] Call quality monitoring


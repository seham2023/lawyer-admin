# Video Call Implementation - Quick Start Guide

Get the video calling system up and running in 5 minutes!

## Prerequisites Checklist

- [ ] Laravel application running
- [ ] Node.js server running on port 4722
- [ ] OpenTok API credentials (apiKey, apiSecret)
- [ ] MySQL database configured
- [ ] Filament admin panel installed

## Step 1: Database Setup (1 minute)

```bash
# Run migrations to create video_calls table
php artisan migrate
```

**Verify**: Check database for `video_calls` table
```bash
mysql> SHOW TABLES LIKE 'video_calls';
```

## Step 2: Environment Configuration (1 minute)

Add to `.env`:
```env
OPENTOK_NODE_SERVER_URL=https://your-domain.com:4722
OPENTOK_API_KEY=your_opentok_api_key
OPENTOK_API_SECRET=your_opentok_api_secret
```

## Step 3: Node.js Server Integration (2 minutes)

1. Copy `backend/nodejs/web-socket-handler.js` to your Node.js project
2. Add to `backend/nodejs/server.js`:

```javascript
// At top with requires
const webSocketHandler = require('./web-socket-handler');

// In Socket.IO connection handler
socket.on('registerWebClient', (data) => {
    webSocketHandler.registerWebClient(socket, data.userId);
    socket.userId = data.userId;
});

socket.on('disconnect', function () {
    if (socket.userId) {
        webSocketHandler.unregisterWebClient(socket.userId);
    }
});
```

3. Update the 'call' event handler to include:
```javascript
// Send to web dashboard
webSocketHandler.sendIncomingCallToWeb(data.receiverId, {
    callId: data.conversationId,
    senderId: data.senderId,
    callerName: data.callerName,
    type: data.type,
    sessionId: data.sessionId,
    token: data.token,
    apiKey: data.apiKey,
});
```

See `NODEJS_INTEGRATION_GUIDE.md` for complete integration.

## Step 4: Install Dependencies (1 minute)

```bash
npm install socket.io-client
```

## Step 5: Access Video Calls Page

1. Start Laravel: `php artisan serve`
2. Start Node.js: `cd backend/nodejs && node server.js`
3. Navigate to: `http://localhost:8000/admin/video-calls`
4. Login with lawyer credentials

## Testing the Implementation

### Quick Test: Incoming Call
1. Open dashboard in browser
2. From mobile app, call the lawyer
3. Verify notification appears on dashboard
4. Click "Answer" to accept

### Quick Test: Call History
1. Complete a call
2. Refresh dashboard
3. Scroll to "Call History" section
4. Verify call appears with correct status and duration

## File Locations

| File | Purpose |
|------|---------|
| `app/Models/VideoCall.php` | Video call model |
| `app/Http/Controllers/VideoCallController.php` | API controller |
| `app/Filament/Pages/VideoCalls.php` | Dashboard page |
| `app/Livewire/VideoCallInterface.php` | Video call component |
| `routes/api.php` | API routes |
| `database/migrations/2025_10_27_000000_create_video_calls_table.php` | Database migration |
| `resources/views/filament/pages/video-calls.blade.php` | Dashboard view |
| `resources/views/livewire/video-call-interface.blade.php` | Video interface |
| `resources/js/video-call-socket.js` | Socket.IO client |
| `backend/nodejs/web-socket-handler.js` | Node.js handler |

## API Endpoints

```bash
# Create video session
POST /api/video-calls/create-session
{
    "receiver_id": 2,
    "case_record_id": 1,
    "call_type": "video"
}

# Answer call
POST /api/video-calls/answer
{
    "call_id": 1,
    "answered_on_web": true
}

# End call
POST /api/video-calls/end
{
    "call_id": 1
}

# Get pending calls
GET /api/video-calls/pending

# Get call history
GET /api/video-calls/history
```

## Troubleshooting

### Issue: No incoming call notification
**Solution**: 
- Check Node.js server is running: `curl https://localhost:4722`
- Check browser console for Socket.IO errors
- Verify CORS configuration includes your domain

### Issue: Video/audio not working
**Solution**:
- Check browser permissions for camera/microphone
- Verify OpenTok SDK is loaded
- Check browser console for errors
- Verify OpenTok credentials are correct

### Issue: Database migration fails
**Solution**:
- Check MySQL is running
- Verify database credentials in `.env`
- Run: `php artisan migrate:refresh` (for development only)

## Next Steps

1. **Read full documentation**: See `VIDEO_CALL_IMPLEMENTATION.md`
2. **Run comprehensive tests**: See `TESTING_GUIDE.md`
3. **Deploy to production**: Ensure SSL certificates are valid
4. **Monitor performance**: Check Node.js and Laravel logs

## Support Files

- `VIDEO_CALL_IMPLEMENTATION.md` - Complete implementation details
- `NODEJS_INTEGRATION_GUIDE.md` - Node.js server integration
- `TESTING_GUIDE.md` - Comprehensive testing procedures
- `IMPLEMENTATION_SUMMARY.md` - Project summary

## Success Indicators

✅ Dashboard page loads at `/admin/video-calls`
✅ Incoming calls appear as notifications
✅ Can answer/decline calls
✅ Video/audio streams work
✅ Call history shows completed calls
✅ Call duration is accurate

---

**Ready to test?** Start with the Quick Test section above!


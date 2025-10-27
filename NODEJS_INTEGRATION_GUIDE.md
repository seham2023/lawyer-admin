# Node.js Server Integration Guide

This guide explains how to integrate the web socket handler into your existing Node.js server to support video calls from the Filament dashboard.

## Step 1: Add Web Socket Handler

The `web-socket-handler.js` file has been created at `backend/nodejs/web-socket-handler.js`. This module manages web client connections and sends call notifications.

## Step 2: Update server.js

Add the following to your `backend/nodejs/server.js`:

### At the top with other requires:
```javascript
const webSocketHandler = require('./web-socket-handler');
```

### In the Socket.IO connection handler (around line 83):
```javascript
io.on('connection', function (socket) {
    console.log("welcome socket test new server");
    
    // Register web client
    socket.on('registerWebClient', (data) => {
        webSocketHandler.registerWebClient(socket, data.userId);
        socket.userId = data.userId;
    });
    
    // Handle web client disconnect
    socket.on('disconnect', function () {
        console.log("disconnect");
        if (socket.userId) {
            webSocketHandler.unregisterWebClient(socket.userId);
        }
        // ... existing disconnect code ...
    });
    
    // ... rest of existing code ...
});
```

### Update the 'call' event handler (around line 357):

Replace the existing call handler with:
```javascript
socket.on('call', data => {
    console.log("call", data);
    
    let time = new Date();
    var config = {}, i18n = "";
    let devices = [], bodyNotify = "";
    
    if (data.status == "start") {
        let day = time.getDate();
        let month = time.getMonth() + 1;
        let year = time.getFullYear();
        let date = day + "/" + month + "/" + year;
        data.month = month;
        data.day = day;
        data.year = year;
        data.timeAdd = new Date();
        data.answerSender = true;
        
        if (!videosCallArray.includes(data.conversationId.toString())) {
            videosCallArray.push(data.conversationId.toString());
        }
        socket.videoCallId = data.conversationId.toString();

        update(data.senderId, 'users', {'connected': false});
        
        get(data.receiverId, 'devices', 'user_id').then(function (rows) {
            console.log('send notification new call');
            console.log(rows);
            sendNotification(rows, data, 'start', 'newCall');
            
            // NEW: Send to web dashboard
            webSocketHandler.sendIncomingCallToWeb(data.receiverId, {
                callId: data.conversationId,
                senderId: data.senderId,
                callerName: data.callerName,
                callerAvatar: data.callerAvatar,
                type: data.type,
                sessionId: data.sessionId,
                token: data.token,
                apiKey: data.apiKey,
            });
        });
    } else if (data.status == "answer") {
        update(data.conversationId, 'rooms', {'call_status': 'answer'});
        
        // NEW: Notify web dashboard
        webSocketHandler.sendCallAnsweredToWeb(data.senderId, {
            callId: data.conversationId,
            answeredBy: data.receiverId,
            answeredOnWeb: data.answeredOnWeb || false,
        });
    } else {
        console.log("answerSecond", data);
        if (data.answerSecond == false) {
            get(data.receiverId, 'devices', 'user_id').then(function (rows) {
                sendNotification(rows, data, 'cancel', 'missedCall');
            });
        }
        if (videosCallArray.includes(socket.videoCallId)) {
            videosCallArray.pop(socket.videoCallId);
        }
        update(data.senderId, 'users', {'connected': true});
        update(data.receiverId, 'users', {'connected': true});
        update(data.conversationId, 'rooms', {'call_status': '"end"'});
        
        // NEW: Notify web dashboard
        webSocketHandler.sendCallEndedToWeb(data.senderId, {
            callId: data.conversationId,
            endedBy: data.senderId,
            duration: data.duration,
        });
        webSocketHandler.sendCallEndedToWeb(data.receiverId, {
            callId: data.conversationId,
            endedBy: data.senderId,
            duration: data.duration,
        });
    }
});
```

## Step 3: Update CORS Configuration

Ensure your Socket.IO CORS configuration includes the web dashboard domain:

```javascript
const io = socketIO(httpsServer, {
  cors: {
    origin: [
        "https://qestass.com",
        "https://your-dashboard-domain.com",
        "http://127.0.0.1:5500"
    ],
    methods: ["GET", "POST"],
    credentials: true
  }
});
```

## Step 4: Test the Integration

### Test Web Client Registration
1. Open browser console on dashboard
2. Check if Socket.IO connects to port 4722
3. Verify `registerWebClient` event is sent

### Test Incoming Call Notification
1. Make a call from mobile app
2. Check Node.js console for web socket events
3. Verify notification appears on web dashboard

### Test Call Status Updates
1. Answer call on web dashboard
2. Check if `callAnswered` event is received
3. Verify call status updates in database

## Debugging

### Enable Socket.IO Debug Logging
```javascript
const io = socketIO(httpsServer, {
    // ... other options
    debug: true,
});
```

### Check Connected Web Clients
Add a debug endpoint:
```javascript
app.get('/api/debug/web-clients', (req, res) => {
    res.json({
        connectedClients: Object.keys(webSocketHandler.webClients),
        count: Object.keys(webSocketHandler.webClients).length,
    });
});
```

### Monitor Socket Events
```javascript
socket.on('registerWebClient', (data) => {
    console.log('Web client registered:', data);
    console.log('Current web clients:', Object.keys(webSocketHandler.webClients));
});
```

## Common Issues

### Web Client Not Receiving Notifications
- Check if Socket.IO connection is established
- Verify user ID is correctly registered
- Check browser console for errors
- Ensure Node.js server is running

### CORS Errors
- Add dashboard domain to CORS origins
- Verify SSL certificates are valid
- Check firewall rules for port 4722

### Connection Timeout
- Increase reconnection attempts in client
- Check Node.js server logs
- Verify network connectivity


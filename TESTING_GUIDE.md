# Video Call Testing Guide

This guide provides comprehensive testing procedures for the video call implementation.

## Prerequisites

- Laravel application running
- Node.js server running on port 4722
- Mobile app installed and configured
- Lawyer account in Filament dashboard
- Client account in mobile app
- Valid OpenTok credentials

## Test Environment Setup

### 1. Database Setup
```bash
php artisan migrate
```

### 2. Start Services
```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Node.js
cd backend/nodejs
node server.js

# Terminal 3: Vite (if using)
npm run dev
```

### 3. Access Dashboard
- Navigate to `http://localhost:8000/admin`
- Login with lawyer credentials
- Go to Video Calls page

## Test Cases

### Test 1: Incoming Call Notification

**Objective**: Verify that incoming calls from mobile appear on web dashboard

**Steps**:
1. Open Filament dashboard in browser
2. Open mobile app on another device
3. From mobile app, initiate a call to the lawyer
4. Observe dashboard for incoming call notification

**Expected Result**:
- Incoming call notification appears on dashboard
- Shows caller name and avatar
- Displays "Answer" and "Decline" buttons
- Call status is "pending"

**Verification**:
```bash
# Check database
SELECT * FROM video_calls WHERE status = 'pending';
```

### Test 2: Answer Call on Web

**Objective**: Verify that lawyer can answer calls on web dashboard

**Steps**:
1. Receive incoming call on dashboard (Test 1)
2. Click "Answer" button
3. Observe video/audio connection

**Expected Result**:
- Call status changes to "active"
- Video call interface appears
- Local and remote video streams visible
- Audio connection established
- `answered_on_web` flag set to true

**Verification**:
```bash
SELECT * FROM video_calls WHERE id = 1;
# Should show: status='active', answered_on_web=1, answered_at=NOW()
```

### Test 3: Decline Call on Web

**Objective**: Verify that lawyer can decline calls on web dashboard

**Steps**:
1. Receive incoming call on dashboard
2. Click "Decline" button
3. Observe call status change

**Expected Result**:
- Call status changes to "declined"
- Notification disappears from dashboard
- Mobile app receives decline notification
- Call appears in history with "declined" status

**Verification**:
```bash
SELECT * FROM video_calls WHERE id = 1;
# Should show: status='declined', ended_at=NOW()
```

### Test 4: End Active Call

**Objective**: Verify that active calls can be ended

**Steps**:
1. Answer a call on web dashboard (Test 2)
2. Click "End Call" button
3. Observe call termination

**Expected Result**:
- Call status changes to "ended"
- Video/audio streams disconnected
- Duration calculated and saved
- Both parties notified
- Call appears in history

**Verification**:
```bash
SELECT * FROM video_calls WHERE id = 1;
# Should show: status='ended', ended_at=NOW(), duration > 0
```

### Test 5: Call History

**Objective**: Verify that call history is properly recorded

**Steps**:
1. Complete multiple calls (answer, decline, end)
2. Navigate to Video Calls page
3. Scroll to Call History section

**Expected Result**:
- All completed calls appear in history
- Shows contact name and avatar
- Displays call type (audio/video)
- Shows call status (ended, declined, missed)
- Shows call duration
- Shows call date/time

**Verification**:
```bash
SELECT * FROM video_calls 
WHERE status IN ('ended', 'declined', 'missed')
ORDER BY created_at DESC;
```

### Test 6: Mute/Unmute Audio

**Objective**: Verify audio control during call

**Steps**:
1. Answer a call on web dashboard
2. Click mute button
3. Verify audio is muted
4. Click unmute button
5. Verify audio is restored

**Expected Result**:
- Mute button changes color (red when muted)
- Audio stream is muted
- Other party cannot hear audio
- Unmute restores audio

### Test 7: Toggle Video

**Objective**: Verify video control during call

**Steps**:
1. Answer a video call on web dashboard
2. Click video toggle button
3. Verify video is disabled
4. Click video toggle button again
5. Verify video is restored

**Expected Result**:
- Video button changes color (red when off)
- Video stream is disabled
- Other party sees black screen
- Toggle restores video

### Test 8: Simultaneous Mobile and Web Connection

**Objective**: Verify that lawyer can receive calls on both mobile and web

**Steps**:
1. Login to mobile app as lawyer
2. Open web dashboard as lawyer
3. From client mobile app, call the lawyer
4. Observe notifications on both devices

**Expected Result**:
- Mobile receives FCM notification
- Web dashboard receives Socket.IO notification
- Lawyer can answer on either device
- Only one connection active at a time

### Test 9: Call Duration Accuracy

**Objective**: Verify that call duration is accurately recorded

**Steps**:
1. Answer a call on web dashboard
2. Wait for 30 seconds
3. End the call
4. Check call history

**Expected Result**:
- Duration shows approximately 30 seconds
- Duration format is HH:MM:SS
- Duration saved in database

**Verification**:
```bash
SELECT id, duration, ended_at, started_at,
       TIMESTAMPDIFF(SECOND, started_at, ended_at) as calculated_duration
FROM video_calls 
WHERE status = 'ended'
ORDER BY created_at DESC LIMIT 1;
```

### Test 10: Error Handling

**Objective**: Verify proper error handling

**Steps**:
1. Disconnect Node.js server
2. Try to initiate a call
3. Observe error message
4. Restart Node.js server
5. Retry call

**Expected Result**:
- Clear error message displayed
- Application doesn't crash
- Can retry after service restoration

## Performance Testing

### Load Test: Multiple Concurrent Calls
```bash
# Simulate multiple calls
for i in {1..10}; do
  curl -X POST http://localhost:8000/api/video-calls/create-session \
    -H "Authorization: Bearer TOKEN" \
    -d "receiver_id=2&call_type=video"
done
```

### Database Query Performance
```bash
# Check query performance
EXPLAIN SELECT * FROM video_calls 
WHERE receiver_id = 2 AND status = 'pending';
```

## Browser Compatibility Testing

Test on:
- Chrome/Chromium (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Security Testing

### Test 1: Unauthorized Access
```bash
# Try to answer someone else's call
curl -X POST http://localhost:8000/api/video-calls/answer \
  -H "Authorization: Bearer WRONG_TOKEN" \
  -d "call_id=1"
```

**Expected**: 403 Forbidden

### Test 2: Invalid Call ID
```bash
curl -X POST http://localhost:8000/api/video-calls/answer \
  -H "Authorization: Bearer TOKEN" \
  -d "call_id=99999"
```

**Expected**: 404 Not Found

## Troubleshooting

### Issue: No incoming call notification
- Check Node.js server logs
- Verify Socket.IO connection in browser console
- Check CORS configuration
- Verify user ID is correct

### Issue: Video/audio not working
- Check browser permissions
- Verify OpenTok SDK loaded
- Check browser console for errors
- Verify OpenTok credentials

### Issue: Call duration incorrect
- Check server time synchronization
- Verify database timestamps
- Check timezone configuration

## Cleanup

After testing:
```bash
# Clear test data
DELETE FROM video_calls WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY);
```


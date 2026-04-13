# Real-Time Chat & Calls — Problems & Solutions

> Date: 2026-04-13  
> Projects: `qestassflutter` (Mobile) · `lawyer-filamnt` (Dashboard) · `nodejs-v4` (Backend)

---

## Problem 1: Chat — Mobile → Dashboard not real-time

**Symptom:** Messages sent from mobile only appear on dashboard after manual refresh. Dashboard → Mobile works fine.

**Root Cause:** The chat Blade template (`chat-interface.blade.php`) bound socket listeners directly via `window.socket.socket.on('newMessage', ...)` inside a Livewire `@script` block. Filament runs in **SPA mode** — when navigating away and back, the `@script` block re-executes but the old listener is gone and the new one may bind before the socket is ready. The `wire:poll.5s` fallback masked the issue by refreshing every 5 seconds.

**Fix:**  
- Rewrote `socket-client.js` to relay ALL socket events to Livewire via `Livewire.dispatch('socket-new-message', ...)` — this is **SPA-safe** and survives page navigation.
- Chat Blade now listens via `Livewire.on('socket-new-message')` instead of direct socket binding.
- Removed `wire:poll.5s`.

**Files changed:**
- `public/js/socket-client.js` — complete rewrite
- `resources/views/livewire/chat-interface.blade.php` — new listener approach

---

## Problem 2: Calls — Dashboard → Mobile never arrives

**Symptom:** When lawyer starts a call from dashboard, it shows "waiting for partner" but the mobile user never gets an incoming call notification.

**Root Cause:** **Dual token generation.** The dashboard's PHP `AgoraService` generated one set of Agora tokens, then sent them via socket to Node.js. But Node.js `call-controller.js` **generated its own separate tokens** for the receiver. Result: caller and receiver had tokens from different authorities → Agora channel join failed silently.

**Fix:**  
- Dashboard now calls Node.js `GET /api/createSessionToken` (same endpoint Flutter uses) to get the caller's token.
- Node.js generates the receiver's token during `initiateCall` socket event.
- **Single authority** for all Agora tokens = Node.js server.

**Files changed:**
- `app/Livewire/ChatInterface.php` — uses Node.js HTTP endpoint instead of PHP AgoraService
- `controllers/call-controller.js` — unified token flow

---

## Problem 3: No multi-device call sync

**Symptom:** When a call comes in, only one device rings. Accepting on one device doesn't stop ringing on others. No "accepted on another device" behavior like Facebook/Messenger.

**Root Cause:** The call controller already broadcast `incomingCall` to all receiver sockets, but there was no logic to notify OTHER devices when one device accepts/rejects.

**Fix:**  
- `handleAcceptCall` now emits `callEnded` with reason `"Accepted on another device"` to all receiver sockets **except** the one that accepted.
- `handleRejectCall` does the same for rejection.
- Dashboard `CallNotification.php` now has `#[On('socket-call-accepted')]` and `#[On('socket-call-rejected')]` handlers that auto-dismiss the call modal.

**Files changed:**
- `controllers/call-controller.js` — multi-device accept/reject sync
- `app/Livewire/CallNotification.php` — new Livewire event handlers
- `resources/views/livewire/call-notification.blade.php` — auto-dismiss on remote events

---

## Problem 4: No background call notifications on Dashboard

**Symptom:** If the dashboard browser tab is inactive or closed, incoming calls are silently lost. Unlike Facebook which shows browser notifications even when the tab is not focused.

**Root Cause:** No Service Worker existed. The dashboard relied entirely on the Socket.IO connection which only works while the tab is active.

**Fix:**  
- Created `firebase-messaging-sw.js` — a Service Worker that receives FCM web push notifications even when the tab is closed.
- Shows native browser notifications for incoming calls (with accept/reject buttons), missed calls, and new messages.
- `socket-client.js` registers the Service Worker on page load and listens for notification clicks to focus the dashboard window.
- Added `POST /api/registerWebDevice` endpoint on Node.js to store the dashboard's FCM web push token alongside mobile device tokens.

**Files created/changed:**
- `public/firebase-messaging-sw.js` — NEW, Service Worker
- `public/js/socket-client.js` — Service Worker registration
- `routes/api-routes.js` — new `/api/registerWebDevice` endpoint

> ⚠️ **Requires:** Add a Web app in Firebase Console and set the `appId` in `firebase-messaging-sw.js` line 22.

---

## Problem 5: Calls hang forever if not answered

**Symptom:** If the receiver doesn't answer, the call stays in "ringing" state indefinitely. No missed call notification is sent.

**Root Cause:** No timeout mechanism existed in `call-controller.js`.

**Fix:**  
- Added 60-second auto-timeout in `handleInitiateCall()`.
- On timeout: caller gets `callTimeout` event, receiver gets `callEnded`, missed call push notification is sent.
- Timeout is properly cleared on accept, reject, cancel, or end.

**Files changed:**
- `controllers/call-controller.js` — timeout timer logic

---

## Problem 6: Fragile socket bridge in AdminPanelProvider

**Symptom:** The `AdminPanelProvider.php` had a `setInterval` that polled for `window.socket` availability every 500ms to attach call listeners. This was unreliable and sometimes failed.

**Root Cause:** The socket bridge was implemented as inline JS in a Blade render hook, which competed with the socket-client.js initialization timing.

**Fix:**  
- Removed the inline JS bridge entirely from `AdminPanelProvider.php`.
- `socket-client.js` now handles ALL event relaying to Livewire natively — no external bridge needed.

**Files changed:**
- `app/Providers/Filament/AdminPanelProvider.php` — removed JS bridge code

---

## Summary of All Changed Files

| File | Project | Action |
|------|---------|--------|
| `public/js/socket-client.js` | Dashboard | Rewritten — SPA-safe, Livewire bridge |
| `app/Livewire/ChatInterface.php` | Dashboard | Uses Node.js for tokens |
| `resources/views/livewire/chat-interface.blade.php` | Dashboard | SPA-safe socket listeners |
| `app/Livewire/CallNotification.php` | Dashboard | Multi-device sync handlers |
| `resources/views/livewire/call-notification.blade.php` | Dashboard | New accept/reject flow |
| `app/Providers/Filament/AdminPanelProvider.php` | Dashboard | Removed JS bridge |
| `public/firebase-messaging-sw.js` | Dashboard | **NEW** — background push |
| `controllers/call-controller.js` | Node.js | Timeout + multi-device sync |
| `routes/api-routes.js` | Node.js | Web device registration |

**Flutter — No changes needed.** It already uses the correct socket events and token endpoint.

---

## Deployment Checklist

- [ ] Deploy dashboard files (PHP + Blade + JS)
- [ ] Run `php artisan view:clear` on dashboard server
- [ ] Deploy Node.js files (`call-controller.js`, `api-routes.js`)
- [ ] Restart Node.js server (`pm2 restart` or equivalent)
- [ ] Add Web app in Firebase Console → set `appId` in `firebase-messaging-sw.js`
- [ ] Test: send message mobile → dashboard (should appear instantly)
- [ ] Test: call from dashboard → mobile (should ring on mobile)
- [ ] Test: call from mobile → dashboard (should show notification)
- [ ] Test: accept on one device → other device stops ringing
- [ ] Test: let call timeout (60s) → missed call notification appears



I traced the current flow across the three projects and implemented the highest-confidence fixes.

The main root causes were:

InvalidToken: the backend was forwarding the receiver’s Agora token back to the caller on callAccepted. I fixed that in call-controller.js by storing the caller token at initiateCall time and returning the correct caller token on accept.
Duplicate ringing / duplicate accept state: Flutter’s retry loop was re-sending initiateCall every 2 seconds, and Node treated each retry as a brand-new invite. I added active-call dedupe/throttling in that same file, so repeated retries for the same room no longer create fresh incoming-call bursts.
Dashboard-to-mobile chat not real-time: the dashboard was relying on Laravel→Node HTTP relay after DB save. I switched the dashboard to relay the already-persisted message through the browser’s live socket instead, via new relayMessage handling in socket-handlers.js, chat-handlers.js, ChatInterface.php, and chat-interface.blade.php.
Dashboard socket instability and duplicate JS listeners: I made the dashboard socket client websocket-only with queued emits in socket-client.js, and I hardened the call notification bindings in call-notification.blade.php so SPA navigation doesn’t stack duplicate listeners and duplicate ringtone playback.
I also added better Agora page logging in video-call.blade.php so the next round of debugging has immediate signal if media still fails.
I verified syntax with php -l on the edited Livewire PHP files and node -c on the edited Node/dashboard JS files that can be syntax-checked locally. I did not run full end-to-end call/chat tests here, so the next step is deploy both lawyer-filamnt and nodejs-v4, then test in this order:

Dashboard → mobile message should appear instantly.
Mobile → Filament call should ring once, accept once, and stop ringing on other devices.
After Filament accepts a mobile-originated call, the mobile caller should no longer get AgoraRtcException / InvalidToken.
Dashboard → mobile call should now survive brief socket reconnects instead of silently failing.

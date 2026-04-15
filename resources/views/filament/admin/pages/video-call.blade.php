<x-filament-panels::page class="!p-0 !max-w-none">
    <div class="relative w-full h-screen bg-gray-900">
        {{-- Video Containers --}}
        <div id="videos" class="relative w-full h-full">
            {{-- Subscriber (Remote Video) - Full Screen --}}
            <div id="subscriber" class="w-full h-full"></div>

            {{-- Publisher (Local Video) - Picture in Picture --}}
            <div id="publisher"
                class="absolute bottom-6 right-6 w-64 h-48 rounded-lg overflow-hidden shadow-2xl border-2 border-white">
            </div>
        </div>

        {{-- Call Controls --}}
        <div
            class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex items-center gap-4 bg-gray-800/90 backdrop-blur-sm px-6 py-4 rounded-full shadow-xl">
            {{-- Mute Audio --}}
            <button id="muteAudio" class="p-4 bg-gray-700 hover:bg-gray-600 text-white rounded-full transition"
                title="Mute/Unmute">
                <x-heroicon-o-microphone class="w-6 h-6" />
            </button>

            {{-- Toggle Video (only for video calls) --}}
            @if($callType === 'video')
                <button id="toggleVideo" class="p-4 bg-gray-700 hover:bg-gray-600 text-white rounded-full transition"
                    title="Camera On/Off">
                    <x-heroicon-o-video-camera class="w-6 h-6" />
                </button>
            @endif

            {{-- End Call --}}
            <button id="endCall" class="p-4 bg-red-600 hover:bg-red-700 text-white rounded-full transition"
                title="End Call">
                <x-heroicon-o-phone-x-mark class="w-6 h-6" />
            </button>
        </div>

        {{-- Call Status --}}
        <div id="callStatus"
            class="absolute top-6 left-1/2 transform -translate-x-1/2 bg-gray-800/90 backdrop-blur-sm px-6 py-3 rounded-full text-white font-medium">
            Connecting...
        </div>
    </div>

    {{-- Agora SDK --}}
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.20.0.js"></script>

    <script>
        // Agora Configuration
        const appId = '{{ $apiKey }}'; // Using apiKey logic as appId mapping
        const channelName = '{{ $sessionId }}'; // sessionId mapped to channelName
        const token = '{{ $token }}';
        const callType = '{{ $callType }}';
        const roomId = {{ $roomId ?? 0 }};
        const callerId = {{ $callerId ?? 0 }};
        const receiverId = {{ $receiverId ?? 0 }};
        const role = @js($role ?? '');

        let client;
        let localAudioTrack;
        let localVideoTrack;
        let audioEnabled = true;
        let videoEnabled = true;
        const uid = {{ $uid ?? 0 }};
        let remoteJoined = false;
        let callTerminationSent = false;
        let socketHandlersAttached = false;

        console.log('[VideoCall] Initializing page', {
            appId: appId,
            channelName: channelName,
            callType: callType,
            uid: uid,
            roomId: roomId,
            callerId: callerId,
            receiverId: receiverId,
            role: role,
            hasToken: Boolean(token),
        });

        function updateCallStatus(status) {
            document.getElementById('callStatus').textContent = status;
        }

        function closeWindowSoon(delay = 1500) {
            window.setTimeout(() => window.close(), delay);
        }

        function buildSignalPayload() {
            return {
                caller_id: callerId,
                callerId: callerId,
                receiver_id: receiverId,
                receiverId: receiverId,
                room_id: roomId,
                roomId: roomId,
            };
        }

        function emitSignal(eventName, payload) {
            if (!window.socket) {
                console.warn('[VideoCall] Socket client is not available for', eventName);
                return false;
            }

            const methodMap = {
                cancelCall: 'emitCancelCall',
                endCall: 'emitEndCall',
                rejectCall: 'emitRejectCall',
            };

            const method = methodMap[eventName];

            if (method && typeof window.socket[method] === 'function') {
                window.socket[method](payload);
                return true;
            }

            if (window.socket.socket) {
                window.socket.socket.emit(eventName, payload);
                return true;
            }

            return false;
        }

        function notifyCallTermination(source = 'manual') {
            if (callTerminationSent || !roomId || !callerId || !receiverId) {
                return;
            }

            const payload = buildSignalPayload();
            let signalName = 'endCall';

            if (!remoteJoined && role === 'caller') {
                signalName = 'cancelCall';
            }

            callTerminationSent = emitSignal(signalName, payload);
            console.log('[VideoCall] Sent signaling on close', { source, signalName, payload, callTerminationSent });
        }

        function handleRemoteSignal(status, data) {
            const dataRoomId = Number(data?.room_id ?? data?.roomId ?? 0);

            if (!dataRoomId || dataRoomId !== roomId) {
                return;
            }

            callTerminationSent = true;
            console.log('[VideoCall] Remote signaling event', { status, data });

            if (status === 'rejected') {
                updateCallStatus('Call rejected');
                closeWindowSoon();
                return;
            }

            if (status === 'timeout') {
                updateCallStatus('No answer');
                closeWindowSoon();
                return;
            }

            if (status === 'ended') {
                updateCallStatus('Call ended');
                closeWindowSoon();
            }
        }

        function attachSocketHandlers() {
            if (socketHandlersAttached || !window.socket) {
                return socketHandlersAttached;
            }

            socketHandlersAttached = true;

            if (typeof window.socket.onCall === 'function') {
                window.socket.onCall((data) => {
                    const status = data?.status;

                    if (status === 'rejected') {
                        handleRemoteSignal('rejected', data);
                    } else if (status === 'ended') {
                        handleRemoteSignal('ended', data);
                    }
                });
            } else if (window.socket.socket) {
                window.socket.socket.on('callRejected', (data) => handleRemoteSignal('rejected', data));
                window.socket.socket.on('callEnded', (data) => handleRemoteSignal('ended', data));
            }

            if (window.socket.socket) {
                window.socket.socket.on('callTimeout', (data) => handleRemoteSignal('timeout', data));
            }

            console.log('[VideoCall] Socket handlers attached');
            return true;
        }

        function waitForSocketHandlers(attempt = 0) {
            if (attachSocketHandlers()) {
                return;
            }

            if (attempt >= 20) {
                console.warn('[VideoCall] Timed out waiting for socket client in popup');
                return;
            }

            window.setTimeout(() => waitForSocketHandlers(attempt + 1), 500);
        }

        async function initializeSession() {
            console.log('[VideoCall] Creating Agora client...');
            client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

            // Subscribe to remote stream
            client.on("user-published", async (user, mediaType) => {
                console.log('[VideoCall] user-published', { uid: user.uid, mediaType: mediaType });
                await client.subscribe(user, mediaType);
                remoteJoined = true;
                updateCallStatus('Connected');

                if (mediaType === "video") {
                    const remoteVideoTrack = user.videoTrack;
                    const subscriberDiv = document.getElementById('subscriber');
                    remoteVideoTrack.play(subscriberDiv);
                }
                if (mediaType === "audio") {
                    const remoteAudioTrack = user.audioTrack;
                    remoteAudioTrack.play();
                }
            });

            client.on("user-unpublished", (user) => {
                console.log('[VideoCall] user-unpublished', { uid: user.uid });
                remoteJoined = false;
                updateCallStatus('Participant left');
            });

            client.on("user-left", (user) => {
                console.log('[VideoCall] user-left', { uid: user.uid });
                remoteJoined = false;
                updateCallStatus('Call ended');
                closeWindowSoon(2000);
            });

            // Connect to Session
            try {
                // Join channel using the authenticated user's ID
                await client.join(appId, channelName, token, uid);
                console.log('[VideoCall] Joined Agora channel successfully');
                updateCallStatus('Waiting for other participant...');

                // Publish local tracks
                if (callType === 'video') {
                    [localAudioTrack, localVideoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();
                    console.log('[VideoCall] Local audio/video tracks created');
                    localVideoTrack.play(document.getElementById('publisher'));
                    await client.publish([localAudioTrack, localVideoTrack]);
                } else {
                    localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
                    console.log('[VideoCall] Local audio track created');
                    await client.publish([localAudioTrack]);
                }

                console.log('[VideoCall] Local tracks published');
            } catch (error) {
                console.error('[VideoCall] Error connecting to Agora:', error);
                updateCallStatus('Connection failed: ' + error.message);
            }
        }

        // Mute/Unmute Audio
        document.getElementById('muteAudio').addEventListener('click', () => {
            audioEnabled = !audioEnabled;
            if (localAudioTrack) {
                localAudioTrack.setMuted(!audioEnabled);
            }

            const btn = document.getElementById('muteAudio');
            if (audioEnabled) {
                btn.classList.remove('bg-red-600');
                btn.classList.add('bg-gray-700');
            } else {
                btn.classList.remove('bg-gray-700');
                btn.classList.add('bg-red-600');
            }
        });

        // Toggle Video (only for video calls)
        @if($callType === 'video')
            document.getElementById('toggleVideo').addEventListener('click', () => {
                videoEnabled = !videoEnabled;
                if (localVideoTrack) {
                    localVideoTrack.setMuted(!videoEnabled);
                }

                const btn = document.getElementById('toggleVideo');
                if (videoEnabled) {
                    btn.classList.remove('bg-red-600');
                    btn.classList.add('bg-gray-700');
                } else {
                    btn.classList.remove('bg-gray-700');
                    btn.classList.add('bg-red-600');
                }
            });
        @endif

        // End Call
        document.getElementById('endCall').addEventListener('click', async () => {
            notifyCallTermination('end-button');
            if (localAudioTrack) localAudioTrack.close();
            if (localVideoTrack) localVideoTrack.close();
            if (client) await client.leave();

            updateCallStatus('Call ended');
            closeWindowSoon(1000);
        });

        // Initialize on page load
        waitForSocketHandlers();

        if (channelName && token && appId) {
            initializeSession();
        } else {
            console.error('[VideoCall] Invalid call parameters', { appId, channelName, tokenPresent: Boolean(token), uid });
            updateCallStatus('Invalid call parameters');
        }

        // Cleanup on window close
        window.addEventListener('beforeunload', () => {
            notifyCallTermination('beforeunload');
            if (localAudioTrack) localAudioTrack.close();
            if (localVideoTrack) localVideoTrack.close();
        });
    </script>
</x-filament-panels::page>

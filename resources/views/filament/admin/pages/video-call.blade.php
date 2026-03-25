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

        let client;
        let localAudioTrack;
        let localVideoTrack;
        let audioEnabled = true;
        let videoEnabled = true;

        async function initializeSession() {
            client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

            // Subscribe to remote stream
            client.on("user-published", async (user, mediaType) => {
                await client.subscribe(user, mediaType);
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
                updateCallStatus('Participant left');
            });

            client.on("user-left", (user) => {
                updateCallStatus('Call ended');
                setTimeout(() => window.close(), 2000);
            });

            // Connect to Session
            try {
                // Join channel using the authenticated user's ID
                const uid = {{ $uid ?? 0 }};
                await client.join(appId, channelName, token, uid);
                updateCallStatus('Waiting for other participant...');

                // Publish local tracks
                if (callType === 'video') {
                    [localAudioTrack, localVideoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();
                    localVideoTrack.play(document.getElementById('publisher'));
                    await client.publish([localAudioTrack, localVideoTrack]);
                } else {
                    localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
                    await client.publish([localAudioTrack]);
                }
            } catch (error) {
                console.error('Error connecting to Agora:', error);
                updateCallStatus('Connection failed: ' + error.message);
            }
        }

        // Update Call Status
        function updateCallStatus(status) {
            document.getElementById('callStatus').textContent = status;
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
            if (localAudioTrack) localAudioTrack.close();
            if (localVideoTrack) localVideoTrack.close();
            if (client) await client.leave();

            updateCallStatus('Call ended');
            setTimeout(() => window.close(), 1000);
        });

        // Initialize on page load
        if (channelName && token && appId) {
            initializeSession();
        } else {
            updateCallStatus('Invalid call parameters');
        }

        // Cleanup on window close
        window.addEventListener('beforeunload', () => {
            if (localAudioTrack) localAudioTrack.close();
            if (localVideoTrack) localVideoTrack.close();
        });
    </script>
</x-filament-panels::page>
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

    {{-- TokBox SDK --}}
    <script src="https://static.opentok.com/v2/js/opentok.min.js"></script>

    <script>
        // TokBox Configuration
        const apiKey = '{{ $apiKey }}';
        const sessionId = '{{ $sessionId }}';
        const token = '{{ $token }}';
        const callType = '{{ $callType }}';

        let session;
        let publisher;
        let subscriber;
        let audioEnabled = true;
        let videoEnabled = true;

        // Initialize TokBox Session
        function initializeSession() {
            session = OT.initSession(apiKey, sessionId);

            // Create Publisher (Local Video/Audio)
            const publisherOptions = {
                insertMode: 'append',
                width: '100%',
                height: '100%',
                publishAudio: true,
                publishVideo: callType === 'video',
                style: {
                    buttonDisplayMode: 'off'
                }
            };

            publisher = OT.initPublisher('publisher', publisherOptions, (error) => {
                if (error) {
                    console.error('Error initializing publisher:', error);
                    updateCallStatus('Error: ' + error.message);
                } else {
                    console.log('Publisher initialized');
                }
            });

            // Subscribe to Remote Stream
            session.on('streamCreated', (event) => {
                console.log('Remote stream created');
                updateCallStatus('Connected');

                const subscriberOptions = {
                    insertMode: 'append',
                    width: '100%',
                    height: '100%',
                    style: {
                        buttonDisplayMode: 'off'
                    }
                };

                subscriber = session.subscribe(
                    event.stream,
                    'subscriber',
                    subscriberOptions,
                    (error) => {
                        if (error) {
                            console.error('Error subscribing:', error);
                        }
                    }
                );
            });

            // Handle Stream Destroyed
            session.on('streamDestroyed', (event) => {
                console.log('Stream destroyed:', event.reason);
                updateCallStatus('Call ended');
                setTimeout(() => {
                    window.close();
                }, 2000);
            });

            // Connect to Session
            session.connect(token, (error) => {
                if (error) {
                    console.error('Error connecting:', error);
                    updateCallStatus('Connection failed');
                } else {
                    console.log('Connected to session');
                    updateCallStatus('Waiting for other participant...');

                    // Publish local stream
                    session.publish(publisher, (error) => {
                        if (error) {
                            console.error('Error publishing:', error);
                        }
                    });
                }
            });
        }

        // Update Call Status
        function updateCallStatus(status) {
            document.getElementById('callStatus').textContent = status;
        }

        // Mute/Unmute Audio
        document.getElementById('muteAudio').addEventListener('click', () => {
            audioEnabled = !audioEnabled;
            publisher.publishAudio(audioEnabled);

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
                publisher.publishVideo(videoEnabled);

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
        document.getElementById('endCall').addEventListener('click', () => {
            if (session) {
                session.disconnect();
            }
            updateCallStatus('Call ended');
            setTimeout(() => {
                window.close();
            }, 1000);
        });

        // Initialize on page load
        if (sessionId && token && apiKey) {
            initializeSession();
        } else {
            updateCallStatus('Invalid call parameters');
        }

        // Cleanup on window close
        window.addEventListener('beforeunload', () => {
            if (session) {
                session.disconnect();
            }
        });
    </script>
</x-filament-panels::page>
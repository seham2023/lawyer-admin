<div class="flex h-screen flex-col bg-gray-900">
    <!-- Video Container -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Remote Video (Main) -->
        <div class="relative flex-1 bg-black">
            <div id="subscriber" class="h-full w-full"></div>
            <div class="absolute top-4 right-4 text-white text-sm">
                @if($videoCall)
                    <p class="font-semibold">{{ $videoCall->caller_id === auth()->id() ? $videoCall->receiver->name : $videoCall->caller->name }}</p>
                @endif
            </div>
        </div>

        <!-- Local Video (Picture-in-Picture) -->
        <div class="relative h-32 w-40 bg-black">
            <div id="publisher" class="h-full w-full"></div>
        </div>
    </div>

    <!-- Control Bar -->
    <div class="flex items-center justify-center space-x-4 bg-gray-800 px-6 py-4">
        <!-- Mute Button -->
        <button 
            wire:click="toggleMute"
            class="rounded-full p-3 transition-colors {{ $isMuted ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-700 hover:bg-gray-600' }}"
            title="{{ $isMuted ? 'Unmute' : 'Mute' }}"
        >
            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                @if($isMuted)
                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 2c.5 0 .97.293 1.184.876l.888 1.776a1 1 0 11-1.796.895L9.383 3.076zM15.364 5.364a1 1 0 011.414 0l1.414 1.414a1 1 0 01-1.414 1.414l-1.414-1.414a1 1 0 010-1.414zm2.121 9.242a1 1 0 010 1.414l-1.414 1.414a1 1 0 01-1.414-1.414l1.414-1.414a1 1 0 011.414 0zM10 15a1 1 0 011-1h.01a1 1 0 110 2H11a1 1 0 01-1-1z" clip-rule="evenodd"/>
                @else
                    <path d="M8 16A8 8 0 1116 8a.5.5 0 01-1 0 7 7 0 10-14 0 .5.5 0 01-1 0 8 8 0 018-8z"/>
                @endif
            </svg>
        </button>

        <!-- Video Toggle Button -->
        @if($callType === 'video')
        <button 
            wire:click="toggleVideo"
            class="rounded-full p-3 transition-colors {{ $isVideoOff ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-700 hover:bg-gray-600' }}"
            title="{{ $isVideoOff ? 'Turn on camera' : 'Turn off camera' }}"
        >
            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                @if($isVideoOff)
                    <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm10.5-2a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                @else
                    <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm10.5-2a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                @endif
            </svg>
        </button>
        @endif

        <!-- End Call Button -->
        <button 
            wire:click="endCall"
            class="rounded-full bg-red-600 p-3 hover:bg-red-700 transition-colors"
            title="End call"
        >
            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>

    <!-- Pending Call Modal -->
    @if($videoCall && !$isCallActive && $videoCall->status === 'pending' && $videoCall->receiver_id === auth()->id())
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="rounded-lg bg-white p-8 shadow-lg dark:bg-gray-800">
            <div class="text-center">
                <div class="mb-4 flex justify-center">
                    <img class="h-16 w-16 rounded-full" src="{{ $videoCall->caller->avatar ?? 'https://via.placeholder.com/64' }}" alt="{{ $videoCall->caller->name }}">
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $videoCall->caller->name ?? 'Unknown' }}
                </h3>
                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    {{ __('is calling you') }}
                </p>
                <div class="flex justify-center space-x-4">
                    <button 
                        wire:click="answerCall"
                        class="inline-flex items-center justify-center rounded-lg bg-green-600 px-6 py-3 text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        <svg class="mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773c.058.3.102.605.102.924v1.902c0 .331.044.645.102.924l1.548.773a1 1 0 01.54 1.06l-.74 4.435a1 1 0 01-.986.836H3a1 1 0 01-1-1V3z"/>
                        </svg>
                        {{ __('Answer') }}
                    </button>
                    <button 
                        wire:click="declineCall"
                        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-6 py-3 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        <svg class="mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('Decline') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="https://static.opentok.com/v2/js/opentok.min.js"></script>
    <script>
        // OpenTok initialization will be handled here
        const apiKey = "{{ $apiKey }}";
        const sessionId = "{{ $sessionId }}";
        const token = "{{ $token }}";
        const callType = "{{ $callType }}";

        let session;
        let publisher;
        let subscriber;

        // Initialize OpenTok session
        function initializeSession() {
            session = OT.initSession(apiKey, sessionId);

            // Subscribe to streams
            session.on('streamCreated', function(event) {
                subscriber = session.subscribe(event.stream, 'subscriber', {
                    audioVolume: 100,
                    videoSource: callType === 'video' ? 'camera' : null,
                });
            });

            // Connect to session
            session.connect(token, function(error) {
                if (error) {
                    console.error('Failed to connect:', error);
                } else {
                    // Publish local stream
                    publisher = OT.initPublisher('publisher', {
                        audioSource: true,
                        videoSource: callType === 'video' ? 'camera' : null,
                        width: 160,
                        height: 120,
                    });

                    session.publish(publisher, function(error) {
                        if (error) {
                            console.error('Failed to publish:', error);
                        }
                    });
                }
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initializeSession);

        // Listen for Livewire events
        Livewire.on('muteToggled', (isMuted) => {
            if (publisher) {
                publisher.publishAudio(!isMuted);
            }
        });

        Livewire.on('videoToggled', (isOn) => {
            if (publisher) {
                publisher.publishVideo(isOn);
            }
        });

        Livewire.on('callEnded', () => {
            if (session) {
                session.disconnect();
            }
            window.location.href = '/admin/video-calls';
        });
    </script>
    @endpush
</div>


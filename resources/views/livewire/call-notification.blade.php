<div>
    {{-- Call Notification Modal --}}
    @if($showCallModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 max-w-md w-full mx-4 animate-bounce">
                {{-- Call Icon --}}
                <div class="flex justify-center mb-6">
                    @if($callType === 'video')
                        <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center">
                            <x-heroicon-o-video-camera class="w-10 h-10 text-white" />
                        </div>
                    @else
                        <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center">
                            <x-heroicon-o-phone class="w-10 h-10 text-white" />
                        </div>
                    @endif
                </div>

                {{-- Caller Info --}}
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $callerName }}
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400">
                        Incoming {{ ucfirst($callType) }} Call
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-4">
                    {{-- Reject Button --}}
                    <button wire:click="rejectCall"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-4 px-6 rounded-lg transition flex items-center justify-center gap-2">
                        <x-heroicon-o-phone-x-mark class="w-6 h-6" />
                        Decline
                    </button>

                    {{-- Accept Button --}}
                    <button wire:click="acceptCall"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-4 px-6 rounded-lg transition flex items-center justify-center gap-2">
                        <x-heroicon-o-phone class="w-6 h-6" />
                        Accept
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Ringing Sound --}}
    <audio id="ringing-sound" loop preload="auto">
        <source src="/sounds/call.mp3" type="audio/mpeg">
    </audio>

    {{-- Socket.IO Call Listener Logic --}}
    @script
    <script>
        const ringtone = document.getElementById('ringing-sound');

        $wire.on('play-ringtone', () => {
            console.log('Playing ringtone...');
            if (ringtone) {
                ringtone.currentTime = 0;
                ringtone.play().catch(e => console.log('Audio play failed:', e));
            }
        });

        $wire.on('stop-ringtone', () => {
            console.log('Stopping ringtone...');
            if (ringtone) {
                ringtone.pause();
                ringtone.currentTime = 0;
            }
        });

        // Handle accept call
        $wire.on('accept-call', (event) => {
            const payload = Array.isArray(event) ? event[0] : event;
            const callData = payload.callData ?? payload;

            $wire.dispatch('stop-ringtone');

            // Emit to Socket.IO that call was accepted
            if (window.socket && window.socket.socket) {
                window.socket.socket.emit('acceptCall', {
                    caller_id: callData.caller_id ?? callData.callerId ?? callData.userId,
                    receiver_id: {{ auth()->id() }},
                    room_id: callData.room_id,
                });
            }

            // Open video call page or modal
            const params = new URLSearchParams({
                session: callData.session_id ?? callData.sessionId ?? '',
                token: callData.token ?? '',
                apiKey: callData.api_key ?? callData.apiKey ?? '',
                callType: callData.call_type ?? callData.callType ?? 'audio'
            });

            window.open('/admin/video-call?' + params.toString(), '_blank', 'width=1200,height=800');
        });

        // Handle reject call
        $wire.on('reject-call', (event) => {
            const payload = Array.isArray(event) ? event[0] : event;
            const callData = payload.callData ?? payload;

            $wire.dispatch('stop-ringtone');

            // Emit to Socket.IO that call was rejected
            if (window.socket && window.socket.socket) {
                window.socket.socket.emit('rejectCall', {
                    caller_id: callData.caller_id ?? callData.callerId ?? callData.userId,
                    receiver_id: {{ auth()->id() }},
                    room_id: callData.room_id,
                });
            }
        });
    </script>
    @endscript
</div>

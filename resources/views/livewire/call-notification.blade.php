<div>
    {{-- Call Notification Modal --}}
    @if($showCallModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="z-index: 9999;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4"
                 style="animation: callPulse 2s ease-in-out infinite;">
                {{-- Call Icon --}}
                <div class="flex justify-center mb-6">
                    @if($callType === 'video')
                        <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center shadow-lg"
                             style="animation: ringBounce 1s ease-in-out infinite;">
                            <x-heroicon-o-video-camera class="w-10 h-10 text-white" />
                        </div>
                    @else
                        <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center shadow-lg"
                             style="animation: ringBounce 1s ease-in-out infinite;">
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
                        {{ $callType === 'video' ? 'مكالمة فيديو واردة' : 'مكالمة صوتية واردة' }}
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-4">
                    {{-- Reject Button --}}
                    <button wire:click="rejectCall"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-4 px-6 rounded-xl transition-all hover:shadow-lg flex items-center justify-center gap-2">
                        <x-heroicon-o-phone-x-mark class="w-6 h-6" />
                        رفض
                    </button>

                    {{-- Accept Button --}}
                    <button wire:click="acceptCall"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-4 px-6 rounded-xl transition-all hover:shadow-lg flex items-center justify-center gap-2">
                        <x-heroicon-o-phone class="w-6 h-6" />
                        قبول
                    </button>
                </div>
            </div>
        </div>

        <style>
            @keyframes callPulse {
                0%, 100% { transform: scale(1); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
                50% { transform: scale(1.02); box-shadow: 0 25px 50px -6px rgba(0, 0, 0, 0.35); }
            }
            @keyframes ringBounce {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
        </style>
    @endif

    {{-- Ringing Sound --}}
    <audio id="ringing-sound" loop preload="auto">
        <source src="/sounds/call.mp3" type="audio/mpeg">
    </audio>

    {{-- Socket.IO Call Event Handlers --}}
    @script
    <script>
        const ringtone = document.getElementById('ringing-sound');
        const currentUserId = {{ auth()->id() }};

        // ═══════════════════════════════════════
        // Ringtone controls
        // ═══════════════════════════════════════
        $wire.on('play-ringtone', () => {
            console.log('[CallNotification] Playing ringtone...');
            if (ringtone) {
                ringtone.currentTime = 0;
                ringtone.play().catch(e => console.log('Audio play failed:', e));
            }
        });

        $wire.on('stop-ringtone', () => {
            console.log('[CallNotification] Stopping ringtone...');
            if (ringtone) {
                ringtone.pause();
                ringtone.currentTime = 0;
            }
        });

        // ═══════════════════════════════════════
        // Handle ACCEPT call
        // ═══════════════════════════════════════
        $wire.on('accept-call', (event) => {
            const payload = Array.isArray(event) ? event[0] : event;
            const callData = payload.callData ?? payload;

            console.log('[CallNotification] Accepting call:', callData);
            $wire.dispatch('stop-ringtone');

            // Emit acceptCall to Socket.IO server (compatible with old + new socket-client.js)
            const acceptData = {
                caller_id: callData.caller_id ?? callData.callerId,
                callerId: callData.caller_id ?? callData.callerId,
                receiver_id: currentUserId,
                receiverId: currentUserId,
                room_id: callData.room_id ?? callData.roomId,
                roomId: callData.room_id ?? callData.roomId,
                token: callData.token || '',
            };

            if (window.socket) {
                if (typeof window.socket.emitAcceptCall === 'function') {
                    window.socket.emitAcceptCall(acceptData);
                } else if (window.socket.socket) {
                    window.socket.socket.emit('acceptCall', acceptData);
                }
                console.log('[CallNotification] acceptCall emitted to socket');
            }

            // Open video call page with the credentials from incomingCall payload
            const params = new URLSearchParams({
                session: callData.session_id ?? callData.sessionId ?? '',
                token: callData.token ?? '',
                apiKey: callData.api_key ?? callData.apiKey ?? '',
                callType: callData.call_type ?? callData.callType ?? 'audio'
            });

            console.log('[CallNotification] Opening call window with params:', params.toString());
            window.open('/admin/video-call?' + params.toString(), '_blank', 'width=1200,height=800');
        });

        // ═══════════════════════════════════════
        // Handle REJECT call
        // ═══════════════════════════════════════
        $wire.on('reject-call', (event) => {
            const payload = Array.isArray(event) ? event[0] : event;
            const callData = payload.callData ?? payload;

            console.log('[CallNotification] Rejecting call:', callData);
            $wire.dispatch('stop-ringtone');

            // Emit rejectCall to Socket.IO server (compatible with old + new socket-client.js)
            const rejectData = {
                caller_id: callData.caller_id ?? callData.callerId,
                callerId: callData.caller_id ?? callData.callerId,
                receiver_id: currentUserId,
                receiverId: currentUserId,
                room_id: callData.room_id ?? callData.roomId,
                roomId: callData.room_id ?? callData.roomId,
            };

            if (window.socket) {
                if (typeof window.socket.emitRejectCall === 'function') {
                    window.socket.emitRejectCall(rejectData);
                } else if (window.socket.socket) {
                    window.socket.socket.emit('rejectCall', rejectData);
                }
                console.log('[CallNotification] rejectCall emitted to socket');
            }
        });

        // ═══════════════════════════════════════
        // Handle remote call ended (auto-dismiss)
        // This fires when call is accepted on another device,
        // or caller cancels, or call times out
        // ═══════════════════════════════════════
        Livewire.on('call-ended-remote', (event) => {
            console.log('[CallNotification] Remote call ended:', event);
            $wire.dispatch('stop-ringtone');
        });
    </script>
    @endscript
</div>

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

    {{-- Socket.IO Call Listener --}}
    @script
    <script>
        // Listen for incoming calls from Socket.IO
        if (window.socket) {
            window.socket.on('call', (data) => {
                console.log('Incoming call received:', data);

                if (data.status === 'start') {
                    // Trigger Livewire component
                    @this.dispatch('incoming-call', { callData: data });
                }
            });
        }

        // Handle accept call
        $wire.on('accept-call', (event) => {
            const callData = event.callData;

            // Emit to Socket.IO that call was accepted
            if (window.socket) {
                window.socket.emit('call', {
                    status: 'answer',
                    room_id: callData.room_id,
                    userId: {{ auth()->id() }},
                    receiverId: callData.userId,
                    callType: callData.callType,
                    session_id: callData.session_id,
                    token: callData.token
                });
            }

            // Open video call page or modal
            // You can redirect to a dedicated call page or open a modal
            window.open('/admin/video-call?session=' + callData.session_id, '_blank', 'width=1200,height=800');
        });

        // Handle reject call
        $wire.on('reject-call', (event) => {
            const callData = event.callData;

            // Emit to Socket.IO that call was rejected
            if (window.socket) {
                window.socket.emit('call', {
                    status: 'end',
                    room_id: callData.room_id,
                    userId: {{ auth()->id() }},
                    receiverId: callData.userId
                });
            }
        });
    </script>
    @endscript
</div>
<div class="flex flex-col h-full">
    {{-- Chat Header --}}
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                {{-- Avatar --}}
                <div
                    class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                    {{ strtoupper(substr($receiverName ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $receiverName ?? __('Chat') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400" id="typing-indicator-{{ $roomId }}"></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Audio Call Button --}}
                <button wire:click="initiateCall('audio')"
                    class="p-2 text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition"
                    title="{{ __('Audio Call') }}">
                    <x-heroicon-o-phone class="w-5 h-5" />
                </button>

                {{-- Video Call Button --}}
                <button wire:click="initiateCall('video')"
                    class="p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition"
                    title="{{ __('Video Call') }}">
                    <x-heroicon-o-video-camera class="w-5 h-5" />
                </button>

                {{-- Refresh Button --}}
                <button wire:click="loadMessages"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    title="{{ __('Refresh') }}">
                    <x-heroicon-o-arrow-path class="w-5 h-5" />
                </button>
            </div>
        </div>
    </div>

    {{-- Messages Area --}}
    <div id="messages-container-{{ $roomId }}" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 dark:bg-gray-900"
        wire:poll.5s="loadMessages">
        @forelse($messages as $message)
            <div class="flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%]">
                    <div
                        class="rounded-2xl px-4 py-2 {{ $message->sender_id == auth()->id() ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm' }}">
                        {{-- Message Content --}}
                        @if($message->type === 'text')
                            <p class="text-sm break-words">{{ $message->content }}</p>
                        @elseif($message->type === 'image')
                            <img src="{{ $message->content }}" alt="Image" class="max-w-full rounded-lg mb-2" />
                        @elseif($message->type === 'sound')
                            <audio controls class="max-w-full">
                                <source src="{{ $message->content }}" type="audio/mpeg">
                            </audio>
                        @elseif($message->type === 'file')
                            <a href="{{ $message->content }}" target="_blank" class="flex items-center gap-2 hover:underline">
                                <x-heroicon-o-document class="w-5 h-5" />
                                <span class="text-sm">{{ __('Download File') }}</span>
                            </a>
                        @endif
                    </div>

                    {{-- Timestamp --}}
                    <p
                        class="text-xs text-gray-500 dark:text-gray-400 mt-1 {{ $message->sender_id == auth()->id() ? 'text-right' : 'text-left' }}">
                        {{ \Carbon\Carbon::parse($message->created_at)->format('g:i A') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full text-center">
                <div class="w-16 h-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mb-3">
                    <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                </div>
                <p class="text-gray-500 dark:text-gray-400">{{ __('No messages yet') }}</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">{{ __('Start the conversation') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Message Input --}}
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <form wire:submit.prevent="sendMessage" class="flex items-end gap-3">
            {{-- File Upload --}}
            <div class="flex-shrink-0">
                <label
                    class="cursor-pointer p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition inline-flex items-center justify-center">
                    <input type="file" wire:model="file" class="hidden" accept="image/*,audio/*,.pdf,.doc,.docx" />
                    <x-heroicon-o-paper-clip class="w-6 h-6" />
                </label>
            </div>

            {{-- Text Input --}}
            <div class="flex-1">
                <textarea wire:model="newMessage" wire:keydown.enter.prevent="sendMessage" wire:keydown="handleTyping"
                    placeholder="{{ __('Type a message...') }}" rows="1"
                    class="w-full px-4 py-3 rounded-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    style="max-height: 120px;"></textarea>
            </div>

            {{-- Send Button --}}
            <div class="flex-shrink-0">
                <button type="submit"
                    class="p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!$wire.newMessage && !$wire.file">
                    <x-heroicon-o-paper-airplane class="w-5 h-5" />
                </button>
            </div>
        </form>

        {{-- File Preview --}}
        @if($file)
            <div class="mt-3 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $file->getClientOriginalName() }}</span>
                </div>
                <button wire:click="$set('file', null)"
                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
        @endif
    </div>

    {{-- Socket.IO Integration --}}
    @script
    <script>
        const roomId = {{ $roomId }};
        const userId = {{ auth()->id() }};

        // Auto-scroll to bottom
        function scrollToBottom() {
            const container = document.getElementById('messages-container-' + roomId);
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        // Scroll on load
        document.addEventListener('DOMContentLoaded', scrollToBottom);

        // Scroll after Livewire updates
        Livewire.hook('morph.updated', ({ el, component }) => {
            scrollToBottom();
        });

        // Socket.IO listeners
        if (window.socket) {
            // Join room
            window.socket.socket.emit('joinRoom', { room_id: roomId, user_id: userId });

            // Listen for new messages
            window.socket.socket.on('newMessage', (data) => {
                if (data.room_id == roomId) {
                    @this.loadMessages();
                    scrollToBottom();
                }
            });

            // Listen for typing
            window.socket.socket.on('userTyping', (data) => {
                if (data.room_id == roomId && data.user_id != userId) {
                    const indicator = document.getElementById('typing-indicator-' + roomId);
                    if (indicator) {
                        indicator.textContent = 'typing...';
                    }
                }
            });

            // Listen for stopped typing
            window.socket.socket.on('userStoppedTyping', (data) => {
                if (data.room_id == roomId) {
                    const indicator = document.getElementById('typing-indicator-' + roomId);
                    if (indicator) {
                        indicator.textContent = '';
                    }
                }
            });
        }

        // Mark as read when viewing (delay to ensure component is loaded)
        setTimeout(() => {
            if (@this && typeof @this.markAsRead === 'function') {
                @this.markAsRead();
            }
        }, 100);

        // Listen for call initiation
        $wire.on('initiate-call', (event) => {
            console.log('=== CALL INITIATION DEBUG ===');
            console.log('1. Event received:', event);
            console.log('2. Event type:', typeof event);
            console.log('3. Event is array:', Array.isArray(event));

            const callData = event[0];
            console.log('4. Call data extracted:', callData);
            console.log('5. Socket exists:', !!window.socket);
            console.log('6. Socket.socket exists:', !!(window.socket && window.socket.socket));

            if (window.socket && window.socket.socket) {
                const emitData = {
                    status: 'start',
                    room_id: callData.room_id,
                    userId: callData.caller_id,
                    receiverId: callData.receiver_id,
                    callType: callData.call_type,
                    session_id: callData.session_id,
                    token: callData.receiver_token,
                    api_key: callData.api_key
                };

                console.log('7. Emitting call event to Socket.IO:', emitData);

                // Emit call event to Socket.IO
                window.socket.socket.emit('call', emitData);

                console.log('8. Call event emitted successfully');

                // Open video call page for lawyer
                const callUrl = `/admin/video-call?session=${callData.session_id}&token=${callData.caller_token}&apiKey=${callData.api_key}&callType=${callData.call_type}`;
                console.log('9. Opening call window:', callUrl);

                window.open(callUrl, '_blank', 'width=1200,height=800');

                console.log('10. Call window opened');
            } else {
                console.error('ERROR: Socket not available!');
                console.log('window.socket:', window.socket);
            }

            console.log('=== END CALL INITIATION DEBUG ===');
        });
    </script>
    @endscript
</div>
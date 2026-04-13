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
    <div id="messages-container-{{ $roomId }}" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 dark:bg-gray-900">
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
                                <source src="{{ $message->content }}">
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
                <textarea id="chat-input-{{ $roomId }}" wire:model="newMessage" wire:keydown.enter.prevent="sendMessage"
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

    {{-- Socket.IO Integration (SPA-safe via Livewire dispatch) --}}
    @script
    <script>
        const roomId = {{ $roomId }};
        const userId = {{ auth()->id() }};
        const receiverId = {{ $receiverId ?? 0 }};

        // ═══════════════════════════════════════
        // Auto-scroll
        // ═══════════════════════════════════════
        function scrollToBottom() {
            const container = document.getElementById('messages-container-' + roomId);
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        document.addEventListener('DOMContentLoaded', scrollToBottom);

        Livewire.hook('morph.updated', ({ el, component }) => {
            scrollToBottom();
            setupTypingBridge();
        });

        // ═══════════════════════════════════════
        // Helper: check if socket is connected (compatible with old + new versions)
        // ═══════════════════════════════════════
        function isSocketReady() {
            if (!window.socket) return false;
            // New version has isConnected() method
            if (typeof window.socket.isConnected === 'function') return window.socket.isConnected();
            // Old version has .connected property
            if (window.socket.connected) return true;
            // Check inner socket
            if (window.socket.socket && window.socket.socket.connected) return true;
            return false;
        }

        // ═══════════════════════════════════════
        // Join room via global socket client
        // ═══════════════════════════════════════
        const joinRoom = () => {
            if (isSocketReady()) {
                window.socket.joinRoom(roomId);
            } else {
                // Retry until socket is ready
                setTimeout(joinRoom, 500);
            }
        };
        joinRoom();

        // ═══════════════════════════════════════
        // Typing emit bridge: dashboard -> mobile
        // ═══════════════════════════════════════
        function setupTypingBridge() {
            const input = document.getElementById('chat-input-' + roomId);
            if (!input || input.dataset.typingBound === '1') {
                return;
            }

            let stopTypingTimer = null;
            let isTyping = false;

            const emitStartTyping = () => {
                if (!window.socket || !receiverId) {
                    return;
                }

                if (typeof window.socket.emitTyping === 'function') {
                    window.socket.emitTyping(roomId, receiverId);
                } else if (window.socket.socket) {
                    window.socket.socket.emit('userTyping', {
                        user_id: userId,
                        room_id: roomId,
                        receiver_id: receiverId,
                    });
                }
            };

            const emitStopTyping = () => {
                if (!isTyping || !window.socket || !receiverId) {
                    return;
                }

                isTyping = false;

                if (typeof window.socket.emitStoppedTyping === 'function') {
                    window.socket.emitStoppedTyping(roomId, receiverId);
                } else if (window.socket.socket) {
                    window.socket.socket.emit('userStoppedTyping', {
                        user_id: userId,
                        room_id: roomId,
                        receiver_id: receiverId,
                    });
                }
            };

            input.addEventListener('input', () => {
                const hasText = input.value.trim().length > 0;

                if (hasText && !isTyping) {
                    isTyping = true;
                    emitStartTyping();
                }

                clearTimeout(stopTypingTimer);
                stopTypingTimer = setTimeout(() => {
                    emitStopTyping();
                }, 1500);

                if (!hasText) {
                    emitStopTyping();
                }
            });

            input.addEventListener('blur', () => {
                clearTimeout(stopTypingTimer);
                emitStopTyping();
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    clearTimeout(stopTypingTimer);
                    emitStopTyping();
                }
            });

            const form = input.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    clearTimeout(stopTypingTimer);
                    emitStopTyping();
                });
            }

            input.dataset.typingBound = '1';
        }

        setupTypingBridge();

        // ═══════════════════════════════════════
        // Listen for real-time messages via Livewire dispatch
        // (socket-client.js dispatches 'socket-new-message' globally)
        // ═══════════════════════════════════════
        Livewire.on('socket-new-message', (event) => {
            const data = event.data || event[0]?.data || event;
            const msgRoomId = data.room_id || data.roomId;

            if (String(msgRoomId) === String(roomId)) {
                console.log('[ChatInterface] Real-time message for this room:', data);
                @this.loadMessages();
                setTimeout(scrollToBottom, 200);
            }
        });

        // ═══════════════════════════════════════
        // Typing indicator via Livewire dispatch
        // ═══════════════════════════════════════
        Livewire.on('socket-typing', (event) => {
            const data = event.data || event[0]?.data || event;
            if (String(data.room_id) === String(roomId) && String(data.user_id) !== String(userId)) {
                const indicator = document.getElementById('typing-indicator-' + roomId);
                if (indicator) {
                    indicator.textContent = data.is_typing ? 'يكتب...' : '';

                    // Auto-clear after 3 seconds
                    if (data.is_typing) {
                        clearTimeout(window.__typingTimeout);
                        window.__typingTimeout = setTimeout(() => {
                            indicator.textContent = '';
                        }, 3000);
                    }
                }
            }
        });

        // Mark as read when viewing
        setTimeout(() => {
            if (@this && typeof @this.markAsRead === 'function') {
                @this.markAsRead();
            }
        }, 100);

        // ═══════════════════════════════════════
        // Call initiation
        // ═══════════════════════════════════════
        $wire.on('initiate-call', (event) => {
            console.log('[ChatInterface] ═══ CALL INITIATION ═══');
            const callData = Array.isArray(event) ? event[0] : event;
            console.log('[ChatInterface] Call data:', callData);

            if (!window.socket) {
                console.error('[ChatInterface] Socket client is not available');
                return;
            }

            // Build call payload
            const emitData = {
                room_id: callData.room_id,
                roomId: callData.room_id,
                caller_id: callData.caller_id,
                callerId: callData.caller_id,
                receiver_id: callData.receiver_id,
                receiverId: callData.receiver_id,
                caller_name: callData.caller_name,
                callerName: callData.caller_name,
                caller_avatar: callData.caller_avatar,
                callerAvatar: callData.caller_avatar,
                call_type: callData.call_type,
                callType: callData.call_type,
                session_id: callData.session_id,
                sessionId: callData.session_id,
                caller_token: callData.caller_token,
                callerToken: callData.caller_token,
                token: callData.receiver_token,
                api_key: callData.api_key,
                apiKey: callData.api_key,
            };

            // Emit call signaling (compatible with old + new socket-client.js)
            if (typeof window.socket.emitInitiateCall === 'function') {
                window.socket.emitInitiateCall(emitData);
            } else if (window.socket.socket) {
                window.socket.socket.emit('initiateCall', emitData);
            }

            console.log('[ChatInterface] Opening call window...');

            // Open video call page for the caller (lawyer)
            const callUrl = `/admin/video-call?session=${encodeURIComponent(callData.session_id)}&token=${encodeURIComponent(callData.caller_token)}&apiKey=${encodeURIComponent(callData.api_key)}&callType=${encodeURIComponent(callData.call_type)}`;
            window.open(callUrl, '_blank', 'width=1200,height=800');

            console.log('[ChatInterface] ═══ CALL INITIATED ═══');
        });

        $wire.on('relay-message', (event) => {
            const payload = Array.isArray(event) ? event[0] : event;
            const messageData = payload[0] ?? payload;

            console.log('[ChatInterface] Relaying persisted message via socket:', messageData);

            if (!window.socket) {
                console.error('[ChatInterface] Socket client is not available for relayMessage');
                return;
            }

            if (typeof window.socket.emitRelayMessage === 'function') {
                window.socket.emitRelayMessage(messageData);
            } else if (window.socket.socket) {
                window.socket.socket.emit('relayMessage', messageData);
            }
        });

        // Clean up on component destroy
        $wire.on('hook:destroyed', () => {
            if (window.socket) {
                window.socket.leaveRoom();
            }
        });
    </script>
    @endscript
</div>

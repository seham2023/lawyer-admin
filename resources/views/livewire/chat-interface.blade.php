<div class="flex flex-col h-full">
    {{-- Chat Header --}}
    <div class="p-4 border-b dark:border-gray-700 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $receiverName ?? __('Chat') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400" id="typing-indicator-{{ $roomId }}"></p>
        </div>
        <div class="flex items-center gap-2">
            <button 
                wire:click="loadMessages" 
                class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                title="{{ __('Refresh') }}"
            >
                <x-heroicon-o-arrow-path class="w-5 h-5" />
            </button>
        </div>
    </div>

    {{-- Messages Area --}}
    <div 
        id="messages-container-{{ $roomId }}" 
        class="flex-1 overflow-y-auto p-4 space-y-4"
        wire:poll.5s="loadMessages"
    >
        @forelse($messages as $message)
            <div class="flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%]">
                    <div class="rounded-lg p-3 {{ $message->sender_id == auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' }}">
                        @if($message->type === 'text')
                            <p class="break-words">{{ $message->content }}</p>
                        @elseif($message->type === 'image')
                            <img src="{{ asset('storage/chat/' . $message->content) }}" alt="Image" class="rounded max-w-full h-auto" />
                        @elseif($message->type === 'file')
                            <a href="{{ asset('storage/chat/' . $message->content) }}" download class="flex items-center gap-2 hover:underline">
                                <x-heroicon-o-document class="w-5 h-5" />
                                {{ $message->content }}
                            </a>
                        @elseif($message->type === 'sound')
                            <audio controls class="w-full">
                                <source src="{{ asset('storage/chat/' . $message->content) }}" type="audio/mpeg">
                            </audio>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 {{ $message->sender_id == auth()->id() ? 'text-right' : 'text-left' }}">
                        {{ \Carbon\Carbon::parse($message->created_at)->format('g:i A') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                <p>{{ __('No messages yet. Start the conversation!') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Message Input --}}
    <div class="p-4 border-t dark:border-gray-700">
        <form wire:submit="sendMessage" class="flex items-end gap-2">
            {{-- File Upload --}}
            <div class="relative">
                <input 
                    type="file" 
                    wire:model="file" 
                    id="file-upload-{{ $roomId }}" 
                    class="hidden"
                    accept="image/*,audio/*,.pdf,.doc,.docx"
                />
                <label 
                    for="file-upload-{{ $roomId }}" 
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 cursor-pointer block"
                    title="{{ __('Attach file') }}"
                >
                    <x-heroicon-o-paper-clip class="w-6 h-6" />
                </label>
            </div>

            {{-- Text Input --}}
            <div class="flex-1">
                <textarea 
                    wire:model.live="newMessage"
                    rows="1"
                    placeholder="{{ __('Type a message...') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white resize-none"
                    onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); this.closest('form').requestSubmit(); }"
                ></textarea>
                
                @if($file)
                    <p class="text-xs text-gray-500 mt-1">
                        {{ __('File selected:') }} {{ $file->getClientOriginalName() }}
                        <button type="button" wire:click="$set('file', null)" class="text-red-500 hover:text-red-700">
                            {{ __('Remove') }}
                        </button>
                    </p>
                @endif
            </div>

            {{-- Send Button --}}
            <button 
                type="submit"
                class="p-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="!newMessage.trim() && !file"
            >
                <x-heroicon-o-paper-airplane class="w-5 h-5" />
            </button>
        </form>
    </div>

    {{-- Socket.IO Integration --}}
    @script
    <script>
        // Auto-scroll to bottom when messages load
        $wire.on('messages-loaded', () => {
            const container = document.getElementById('messages-container-{{ $roomId }}');
            if (container) {
                setTimeout(() => {
                    container.scrollTop = container.scrollHeight;
                }, 100);
            }
        });

        // Handle typing indicator
        let typingTimeout;
        $wire.on('user-typing', (data) => {
            clearTimeout(typingTimeout);
            
            // Emit typing event via Socket.IO (will be implemented)
            if (window.socket) {
                window.socket.emit('userTyping', {
                    user_id: {{ auth()->id() }},
                    room_id: data.room_id,
                    receiver_id: data.receiver_id
                });
            }

            typingTimeout = setTimeout(() => {
                if (window.socket) {
                    window.socket.emit('userStoppedTyping', {
                        user_id: {{ auth()->id() }},
                        room_id: data.room_id,
                        receiver_id: data.receiver_id
                    });
                }
            }, 1000);
        });

        // Handle sending message via Socket.IO
        $wire.on('send-socket-message', (data) => {
            if (window.socket) {
                window.socket.emit('sendMessage', data);
            }
        });

        // Listen for incoming messages
        if (window.socket) {
            window.socket.on('newMessage', (data) => {
                if (data.room_id == {{ $roomId }}) {
                    @this.loadMessages();
                }
            });

            // Listen for typing indicators
            window.socket.on('typingIndicator', (data) => {
                const indicator = document.getElementById('typing-indicator-{{ $roomId }}');
                if (indicator && data.room_id == {{ $roomId }}) {
                    indicator.textContent = data.is_typing ? '{{ __("Typing...") }}' : '';
                }
            });
        }
    </script>
    @endscript
</div>

<div class="flex h-full flex-col bg-gray-50 dark:bg-gray-900">
    <!-- Chat Header -->
    <div class="border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ __('Call Chat') }}
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            @if($videoCall)
                {{ $videoCall->caller_id === auth()->id() ? $videoCall->receiver->name : $videoCall->caller->name }}
            @endif
        </p>
    </div>

    <!-- Messages Container -->
    <div class="flex-1 overflow-y-auto space-y-3 p-4" id="messages-container">
        @forelse($messages as $message)
            <div class="flex {{ $message['is_own'] ? 'justify-end' : 'justify-start' }}">
                <div class="flex max-w-xs gap-2 {{ $message['is_own'] ? 'flex-row-reverse' : 'flex-row' }}">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <img 
                            class="h-8 w-8 rounded-full" 
                            src="{{ $message['sender_avatar'] ?? 'https://via.placeholder.com/32' }}" 
                            alt="{{ $message['sender_name'] }}"
                        >
                    </div>

                    <!-- Message Bubble -->
                    <div class="flex flex-col {{ $message['is_own'] ? 'items-end' : 'items-start' }}">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ $message['sender_name'] }}
                        </p>
                        <div class="mt-1 rounded-lg px-3 py-2 {{ $message['is_own'] ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-white' }}">
                            <p class="text-sm break-words">{{ $message['message'] }}</p>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $message['created_at'] }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="flex h-full items-center justify-center">
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('No messages yet. Start the conversation!') }}
                </p>
            </div>
        @endforelse

        <!-- Typing Indicator -->
        @if(count($typingUsers) > 0)
            <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <div class="flex space-x-1">
                    <div class="h-2 w-2 rounded-full bg-gray-400 animate-bounce"></div>
                    <div class="h-2 w-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay: 0.1s;"></div>
                    <div class="h-2 w-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay: 0.2s;"></div>
                </div>
                <span>{{ __('Someone is typing...') }}</span>
            </div>
        @endif
    </div>

    <!-- Input Area -->
    <div class="border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex gap-2">
            <input 
                type="text"
                wire:model="messageInput"
                wire:input="onInputChange"
                wire:keydown.enter="sendMessage"
                placeholder="{{ __('Type a message...') }}"
                class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
            >
            <button 
                wire:click="sendMessage"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                title="{{ __('Send message') }}"
            >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5.951-1.429 5.951 1.429a1 1 0 001.169-1.409l-7-14z"/>
                </svg>
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto-scroll to bottom when new messages arrive
        const messagesContainer = document.getElementById('messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Listen for new messages
        Livewire.on('messageReceived', () => {
            setTimeout(() => {
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }, 100);
        });
    </script>
    @endpush
</div>


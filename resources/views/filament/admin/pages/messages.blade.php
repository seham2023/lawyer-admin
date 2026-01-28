<x-filament-panels::page class="!p-0 !max-w-none">
    <div class="flex h-[calc(100vh-4rem)] bg-gray-50 dark:bg-gray-900">
        {{-- Conversations Sidebar --}}
        <div class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
            {{-- Header --}}
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ __('Messages') }}</h1>

                {{-- Search --}}
                <input type="text" wire:model.live="search" placeholder="{{ __('Search conversations...') }}"
                    class="w-full px-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            {{-- Conversations List --}}
            <div class="flex-1 overflow-y-auto">
                @forelse($this->filteredRooms as $room)
                    <div wire:click="selectRoom({{ $room->id }})"
                        class="p-4 border-b border-gray-100 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ $selectedRoomId === $room->id ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-l-blue-600' : '' }}">
                        <div class="flex items-start gap-3">
                            {{-- Avatar --}}
                            <div class="flex-shrink-0">
                                <div
                                    class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-lg">
                                    {{ strtoupper(substr($room->client_name ?? 'U', 0, 1)) }}
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $room->client_name ?? __('Unknown Client') }}
                                    </h3>
                                    @if($room->last_message_at)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-2">
                                            {{ \Carbon\Carbon::parse($room->last_message_at)->diffForHumans(null, true) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                        {{ $room->last_message ?? __('No messages yet') }}
                                    </p>
                                    @if($room->unread_count > 0)
                                        <span
                                            class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-600 rounded-full flex-shrink-0 ml-2">
                                            {{ $room->unread_count > 9 ? '9+' : $room->unread_count }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full p-8 text-center">
                        <x-heroicon-o-chat-bubble-left-right class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-3" />
                        <p class="text-gray-500 dark:text-gray-400 font-medium">{{ __('No conversations found') }}</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">{{ __('Start chatting with clients') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Chat Window --}}
        <div class="flex-1 bg-white dark:bg-gray-800 flex flex-col">
            @if($selectedRoomId)
                <livewire:chat-interface :roomId="$selectedRoomId" :key="$selectedRoomId" />
            @else
                <div class="flex-1 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                    <div class="text-center">
                        <div
                            class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                            <x-heroicon-o-chat-bubble-left-ellipsis class="w-12 h-12 text-white" />
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __('Select a conversation') }}
                        </h2>
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ __('Choose a conversation from the list to start messaging') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Call Notification Component --}}
    <livewire:call-notification />

    {{-- Socket.IO Connection Script --}}
    @script
    <script>
        // Socket.IO will be initialized here
        console.log('Messages page loaded');

        // Listen for Livewire events
        Livewire.on('refresh-rooms', () => {
            @this.loadRooms();
        });
    </script>
    @endscript
</x-filament-panels::page>
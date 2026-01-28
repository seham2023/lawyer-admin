<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-12rem)]">
        {{-- Conversations List --}}
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden flex flex-col">
            {{-- Search --}}
            <div class="p-4 border-b dark:border-gray-700">
                <input type="text" wire:model.live="search" placeholder="{{ __('Search conversations...') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
            </div>

            {{-- Rooms List --}}
            <div class="flex-1 overflow-y-auto">
                @forelse($this->filteredRooms as $room)
                    <div wire:click="selectRoom({{ $room->id }})"
                        class="p-4 border-b dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition {{ $selectedRoomId === $room->id ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $room->client_name ?? __('Unknown Client') }}
                                    </h3>
                                    @if($room->unread_count > 0)
                                        <span
                                            class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                            {{ $room->unread_count }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate mt-1">
                                    {{ $room->last_message ?? __('No messages yet') }}
                                </p>
                            </div>
                            @if($room->last_message_at)
                                <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">
                                    {{ \Carbon\Carbon::parse($room->last_message_at)->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p>{{ __('No conversations found') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Chat Window --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden flex flex-col">
            @if($selectedRoomId)
                <livewire:chat-interface :roomId="$selectedRoomId" :key="$selectedRoomId" />
            @else
                <div class="flex-1 flex items-center justify-center text-gray-500 dark:text-gray-400">
                    <div class="text-center">
                        <x-heroicon-o-chat-bubble-left-ellipsis class="w-16 h-16 mx-auto mb-4 opacity-50" />
                        <p class="text-lg">{{ __('Select a conversation to start messaging') }}</p>
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
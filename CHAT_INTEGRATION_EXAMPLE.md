# Chat Integration Example

This guide shows how to integrate the chat component with the video call interface.

## Option 1: Side-by-Side Layout

### Filament Page
```blade
<x-filament-panels::page>
    <div class="grid grid-cols-3 gap-4 h-screen">
        <!-- Video Call (2/3 width) -->
        <div class="col-span-2">
            <livewire:video-call-interface :callId="$callId" />
        </div>

        <!-- Chat (1/3 width) -->
        <div class="col-span-1 border border-gray-200 rounded-lg dark:border-gray-700">
            <livewire:call-chat-interface :callId="$callId" />
        </div>
    </div>
</x-filament-panels::page>
```

## Option 2: Tabbed Layout

### Filament Page with Tabs
```blade
<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <!-- Tabs -->
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <button 
                    @click="activeTab = 'video'"
                    :class="activeTab === 'video' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 dark:text-gray-400'"
                    class="px-4 py-3 font-medium transition-colors"
                >
                    {{ __('Video Call') }}
                </button>
                <button 
                    @click="activeTab = 'chat'"
                    :class="activeTab === 'chat' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 dark:text-gray-400'"
                    class="px-4 py-3 font-medium transition-colors"
                >
                    {{ __('Chat') }}
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-4">
                <div x-show="activeTab === 'video'" class="h-96">
                    <livewire:video-call-interface :callId="$callId" />
                </div>
                <div x-show="activeTab === 'chat'" class="h-96">
                    <livewire:call-chat-interface :callId="$callId" />
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('callTabs', () => ({
                activeTab: 'video'
            }));
        });
    </script>
    @endpush
</x-filament-panels::page>
```

## Option 3: Overlay Chat

### Chat Overlay on Video
```blade
<x-filament-panels::page>
    <div class="relative h-screen">
        <!-- Video Call -->
        <div class="h-full">
            <livewire:video-call-interface :callId="$callId" />
        </div>

        <!-- Chat Overlay (Bottom Right) -->
        <div class="absolute bottom-4 right-4 w-80 h-96 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
            <livewire:call-chat-interface :callId="$callId" />
        </div>
    </div>
</x-filament-panels::page>
```

## Option 4: Full-Screen with Chat Drawer

### Filament Page with Drawer
```blade
<x-filament-panels::page>
    <div class="flex h-screen gap-4">
        <!-- Video Call (Main) -->
        <div class="flex-1">
            <livewire:video-call-interface :callId="$callId" />
        </div>

        <!-- Chat Drawer (Collapsible) -->
        <div 
            x-data="{ open: true }"
            :class="open ? 'w-80' : 'w-16'"
            class="border-l border-gray-200 bg-white transition-all dark:border-gray-700 dark:bg-gray-800"
        >
            <!-- Toggle Button -->
            <button 
                @click="open = !open"
                class="w-full border-b border-gray-200 px-4 py-3 text-left dark:border-gray-700"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Chat Content -->
            <div x-show="open" class="h-full overflow-hidden">
                <livewire:call-chat-interface :callId="$callId" />
            </div>
        </div>
    </div>
</x-filament-panels::page>
```

## Option 5: Modal Chat

### Chat in Modal
```blade
<x-filament-panels::page>
    <div class="h-screen">
        <!-- Video Call -->
        <livewire:video-call-interface :callId="$callId" />

        <!-- Chat Button -->
        <button 
            @click="showChat = true"
            class="fixed bottom-4 right-4 rounded-full bg-blue-600 p-4 text-white shadow-lg hover:bg-blue-700"
        >
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z"></path>
            </svg>
        </button>

        <!-- Chat Modal -->
        <div 
            x-show="showChat"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div class="h-96 w-96 rounded-lg bg-white shadow-lg dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        {{ __('Chat') }}
                    </h3>
                    <button 
                        @click="showChat = false"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <livewire:call-chat-interface :callId="$callId" />
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('callChat', () => ({
                showChat: false
            }));
        });
    </script>
    @endpush
</x-filament-panels::page>
```

## JavaScript Integration

### Connect Socket.IO and Handle Events
```javascript
import videoCallSocket from '/resources/js/video-call-socket.js';

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const userId = document.querySelector('[data-user-id]')?.dataset.userId;
    const callId = document.querySelector('[data-call-id]')?.dataset.callId;

    if (userId && callId) {
        // Connect to Socket.IO
        videoCallSocket.connect(userId);

        // Listen for incoming messages
        videoCallSocket.on('messageReceived', (data) => {
            console.log('New message:', data);
            // Dispatch Livewire event to update chat
            Livewire.dispatch('messageReceived', data);
        });

        // Listen for typing indicators
        videoCallSocket.on('typingIndicator', (data) => {
            console.log('User typing:', data);
            Livewire.dispatch('typingIndicator', data);
        });

        // Request chat history
        videoCallSocket.requestChatHistory(callId);
    }
});
```

## Styling Tips

### Make Chat Responsive
```css
/* Desktop: Side-by-side */
@media (min-width: 1024px) {
    .call-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1rem;
    }
}

/* Tablet: Stacked */
@media (max-width: 1023px) {
    .call-container {
        display: flex;
        flex-direction: column;
    }
}

/* Mobile: Full width */
@media (max-width: 640px) {
    .chat-container {
        position: fixed;
        bottom: 0;
        right: 0;
        width: 100%;
        height: 50%;
        z-index: 50;
    }
}
```

## Complete Example

### Full Integration in Filament Page
```blade
<x-filament-panels::page>
    <div 
        x-data="{ 
            activeTab: 'video',
            showChat: true 
        }"
        class="h-screen"
    >
        <!-- Desktop: Side-by-side -->
        <div class="hidden lg:grid lg:grid-cols-3 lg:gap-4 lg:h-full">
            <div class="col-span-2">
                <livewire:video-call-interface :callId="$callId" />
            </div>
            <div class="col-span-1 border border-gray-200 rounded-lg dark:border-gray-700">
                <livewire:call-chat-interface :callId="$callId" />
            </div>
        </div>

        <!-- Mobile: Tabs -->
        <div class="lg:hidden h-full flex flex-col">
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <button 
                    @click="activeTab = 'video'"
                    :class="activeTab === 'video' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                    class="flex-1 px-4 py-3 font-medium"
                >
                    {{ __('Video') }}
                </button>
                <button 
                    @click="activeTab = 'chat'"
                    :class="activeTab === 'chat' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                    class="flex-1 px-4 py-3 font-medium"
                >
                    {{ __('Chat') }}
                </button>
            </div>

            <div class="flex-1 overflow-hidden">
                <div x-show="activeTab === 'video'" class="h-full">
                    <livewire:video-call-interface :callId="$callId" />
                </div>
                <div x-show="activeTab === 'chat'" class="h-full">
                    <livewire:call-chat-interface :callId="$callId" />
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
```

## Recommended Layout

For best user experience, we recommend **Option 1: Side-by-Side Layout** for desktop and **Option 2: Tabbed Layout** for mobile.

This provides:
- ✅ Full video visibility on desktop
- ✅ Easy access to chat without switching
- ✅ Mobile-friendly tab interface
- ✅ Responsive design
- ✅ Professional appearance


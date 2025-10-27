<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Pending Calls Section -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('Incoming Calls') }}
            </h2>
            
            <div id="pending-calls" class="space-y-3">
                @forelse($this->getPendingCalls() as $call)
                    <div class="flex items-center justify-between rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900 dark:bg-yellow-900/20">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <img class="h-10 w-10 rounded-full" src="{{ $call->caller->avatar ?? 'https://via.placeholder.com/40' }}" alt="{{ $call->caller->name }}">
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $call->caller->name ?? 'Unknown' }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ ucfirst($call->call_type) }} {{ __('call') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button 
                                @click="answerCall({{ $call->id }})"
                                class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            >
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ __('Answer') }}
                            </button>
                            <button 
                                @click="declineCall({{ $call->id }})"
                                class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            >
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                {{ __('Decline') }}
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No incoming calls') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Active Calls Section -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('Active Calls') }}
            </h2>
            
            <div id="active-calls" class="space-y-3">
                @forelse($this->getActiveCalls() as $call)
                    <div class="flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="relative">
                                    <img class="h-10 w-10 rounded-full" src="{{ ($call->caller_id === auth()->id() ? $call->receiver : $call->caller)->avatar ?? 'https://via.placeholder.com/40' }}" alt="">
                                    <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full bg-green-400 ring-2 ring-white dark:ring-gray-800"></span>
                                </div>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ ($call->caller_id === auth()->id() ? $call->receiver : $call->caller)->name ?? 'Unknown' }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('Call in progress') }}
                                </p>
                            </div>
                        </div>
                        <button 
                            @click="endCall({{ $call->id }})"
                            class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            {{ __('End Call') }}
                        </button>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No active calls') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Call History Section -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('Call History') }}
            </h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">{{ __('Contact') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">{{ __('Type') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">{{ __('Duration') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->getCallHistory() as $call)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <img class="h-8 w-8 rounded-full" src="{{ ($call->caller_id === auth()->id() ? $call->receiver : $call->caller)->avatar ?? 'https://via.placeholder.com/32' }}" alt="">
                                        <span class="text-gray-900 dark:text-white">{{ ($call->caller_id === auth()->id() ? $call->receiver : $call->caller)->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ ucfirst($call->call_type) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                                        @if($call->status === 'ended') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                        @elseif($call->status === 'declined') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                        @endif
                                    ">
                                        {{ ucfirst($call->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                    @if($call->duration)
                                        {{ gmdate('H:i:s', $call->duration) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $call->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">{{ __('No call history') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function answerCall(callId) {
            // Implementation will be added with Socket.IO integration
            console.log('Answer call:', callId);
        }

        function declineCall(callId) {
            // Implementation will be added with Socket.IO integration
            console.log('Decline call:', callId);
        }

        function endCall(callId) {
            // Implementation will be added with Socket.IO integration
            console.log('End call:', callId);
        }
    </script>
    @endpush
</x-filament-panels::page>


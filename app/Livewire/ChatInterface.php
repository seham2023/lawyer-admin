<?php

namespace App\Livewire;

use App\Services\SocketIOService;
use App\Filament\Pages\Messages;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChatInterface extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $roomId;
    
    public array $messages = [];
    public string $newMessage = '';
    public $file;
    public ?string $receiverName = null;
    
    #[Locked]
    public ?int $receiverId = null;

    public function mount(): void
    {
        $this->loadMessages();
    }

    protected function authorizeRoomAccess(): void
    {
        $room = DB::connection('qestass_app')
            ->table('rooms')
            ->where('id', $this->roomId)
            ->first();

        if (!$room || ($room->userone_id != Auth::id() && $room->usertwo_id != Auth::id())) {
            abort(403, 'Unauthorized access to chat room.');
        }

        // Determine receiver: if current user is userone, receiver is usertwo, and vice versa
        $userId = Auth::id();
        $this->receiverId = $room->userone_id == $userId ? $room->usertwo_id : $room->userone_id;
    }

    public function loadMessages(): void
    {
        $this->authorizeRoomAccess();

        $service = app(SocketIOService::class);
        $this->messages = $service->getRoomMessages($this->roomId);

        // Get receiver name
        $receiver = DB::connection('qestass_app')
            ->table('users')
            ->where('id', $this->receiverId)
            ->first();

        $this->receiverName = $receiver ? $receiver->first_name . ' ' . $receiver->last_name : 'Unknown';

        $this->dispatch('messages-loaded');
    }

    /**
     * Handle real-time message from socket (dispatched by socket-client.js)
     * This method is called via Livewire.dispatch('socket-new-message') from JS
     * No need for #[On] attribute here — the JS @script in the blade handles it
     * and calls @this.loadMessages() directly
     */

    public function sendMessage(): void
    {
        if (empty($this->newMessage) && !$this->file) {
            return;
        }

        $this->authorizeRoomAccess();

        $this->validate([
            'newMessage' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp3,wav,ogg,pdf,doc,docx|max:10240', // 10MB limit
        ]);

        $type = 'text';
        $content = $this->newMessage;

        // Handle file upload
        if ($this->file) {
            // Store with a unique, non-original name
            $path = $this->file->store('chat-files', 'public');
            $content = '/storage/' . $path;

            $extension = $this->file->getClientOriginalExtension();
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $type = 'image';
            } elseif (in_array(strtolower($extension), ['mp3', 'wav', 'ogg'])) {
                $type = 'sound';
            } else {
                $type = 'file';
            }
        }

        // Save to database
        DB::connection('qestass_app')->table('room_messages')->insert([
            'room_id' => $this->roomId,
            'sender_id' => Auth::id(),
            'receiver_id' => $this->receiverId,
            'content' => $content,
            'type' => $type,
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Relay through the already-connected dashboard browser socket.
        // This avoids server-to-server HTTP failures and keeps the DB write single-source.
        $this->dispatch('relay-message', [
            'room_id' => $this->roomId,
            'roomId' => $this->roomId,
            'sender_id' => Auth::id(),
            'senderId' => Auth::id(),
            'receiver_id' => $this->receiverId,
            'receiverId' => $this->receiverId,
            'content' => $content,
            'type' => $type,
            'duration' => null,
        ]);

        $this->newMessage = '';
        $this->file = null;
        $this->loadMessages();
        $this->dispatch('message-sent')->to(Messages::class);
    }

    public function markAsRead(): void
    {
        if (!$this->roomId) {
            return;
        }

        $this->authorizeRoomAccess();

        $service = app(SocketIOService::class);
        $service->markAsRead($this->roomId, Auth::id());
        $this->dispatch('refresh-rooms')->to(Messages::class);
    }

    public function handleTyping(): void
    {
        // Typing is emitted client-side via socket-client.js
        // The Livewire dispatch triggers JS code that calls window.socket.emitTyping()
        $this->dispatch('user-typing', [
            'roomId' => $this->roomId,
            'userId' => Auth::id()
        ]);
    }

    public function initiateCall(string $callType = 'video'): void
    {
        $this->authorizeRoomAccess();

        Log::info('ChatInterface: initiateCall', [
            'callType' => $callType,
            'roomId' => $this->roomId,
            'receiverId' => $this->receiverId
        ]);

        // ═══════════════════════════════════════════════════════
        // Get Agora token from Node.js server (SINGLE AUTHORITY)
        // This is the same endpoint Flutter uses, ensuring 
        // consistent token generation across all platforms
        // ═══════════════════════════════════════════════════════
        
        $nodeServerUrl = rtrim(config('services.opentok.node_server_url', config('socket.url', 'https://qestass.com:4888')), '/');
        
        try {
            $response = Http::withHeaders([
                'lang' => app()->getLocale(),
            ])->timeout(10)->get("{$nodeServerUrl}/api/createSessionToken", [
                'senderId' => Auth::id(),
                'receiverId' => $this->receiverId,
                'roomId' => $this->roomId,
            ]);

            $data = $response->json();

            Log::info('ChatInterface: Node.js session response', ['data' => $data]);

            if (($data['key'] ?? '') !== 'success') {
                Log::error('ChatInterface: Node.js session creation failed', ['response' => $data]);
                $this->dispatch('call-error', message: $data['msg'] ?? 'Failed to create call session');
                return;
            }

            $sessionData = $data['data'];

            $callerName = trim(
                (Auth::user()->name ?? '') ?: 
                ((Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? ''))
            );

            $callData = [
                'room_id' => $this->roomId,
                'caller_id' => Auth::id(),
                'receiver_id' => $this->receiverId,
                'call_type' => $callType,
                'session_id' => $sessionData['channelName'],
                'api_key' => $sessionData['appId'],
                'caller_token' => $sessionData['token'],   // Caller's token from Node.js
                'receiver_token' => '',                     // Receiver token generated server-side on initiateCall
                'caller_name' => $callerName,
                'caller_avatar' => Auth::user()->avatar ?? null,
            ];

            Log::info('ChatInterface: Dispatching initiate-call', $callData);

            // Emit to JavaScript handler which will:
            // 1. Send initiateCall socket event to Node.js
            // 2. Open the call window for the caller
            $this->dispatch('initiate-call', $callData);

        } catch (\Exception $e) {
            Log::error('ChatInterface: Failed to create session via Node.js', [
                'error' => $e->getMessage(),
                'url' => $nodeServerUrl,
            ]);
            $this->dispatch('call-error', message: 'Failed to connect to call server: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}

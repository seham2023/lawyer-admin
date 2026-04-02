<?php

namespace App\Livewire;

use App\Services\AgoraService;
use App\Services\SocketIOService;
use App\Filament\Pages\Messages;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;
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

        // Send via Socket.IO
        $service = app(SocketIOService::class);
        $service->sendMessage(
            $this->roomId,
            Auth::id(),
            $this->receiverId,
            $content,
            $type,
        );

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
        // Emit typing event via Socket.IO
        $this->dispatch('user-typing', [
            'roomId' => $this->roomId,
            'userId' => Auth::id()
        ]);
    }

    public function initiateCall(string $callType = 'video'): void
    {
        $this->authorizeRoomAccess();

        Log::info('ChatInterface: initiateCall called', [
            'callType' => $callType,
            'roomId' => $this->roomId,
            'receiverId' => $this->receiverId
        ]);

        $agoraService = app(AgoraService::class);

        // Create Agora session
        $sessionData = $agoraService->createCallSession(Auth::id(), $this->receiverId, $this->roomId);

        Log::info('ChatInterface: Agora session created', [
            'success' => $sessionData['success'] ?? false,
            'sessionData' => $sessionData
        ]);

        if (!$sessionData['success']) {
            Log::error('ChatInterface: Failed to create call session');
            $this->dispatch('call-error', message: 'Failed to create call session');
            return;
        }

        // Send call via Socket.IO
        $socketService = app(SocketIOService::class);

        $callData = [
            'room_id' => $this->roomId,
            'caller_id' => Auth::id(),
            'receiver_id' => $this->receiverId,
            'call_type' => $callType,
            'session_id' => $sessionData['session_id'],
            'api_key' => $sessionData['api_key'],
            'caller_token' => $sessionData['lawyer_token'],
            'receiver_token' => $sessionData['client_token'],
            'caller_name' => trim((Auth::user()->name ?? '') ?: ((Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? ''))),
            'caller_avatar' => Auth::user()->avatar ?? null,
        ];

        Log::info('ChatInterface: Dispatching initiate-call event', $callData);

        // Emit call event
        $this->dispatch('initiate-call', $callData);
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}

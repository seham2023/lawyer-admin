<?php

namespace App\Livewire;

use App\Services\TokBoxService;
use App\Services\SocketIOService;
use App\Filament\Pages\Messages;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;

class ChatInterface extends Component
{
    use WithFileUploads;

    public int $roomId;
    public array $messages = [];
    public string $newMessage = '';
    public $file;
    public ?string $receiverName = null;
    public ?int $receiverId = null;

    public function mount(): void
    {
        $this->loadMessages();
    }

    public function loadMessages(): void
    {
        $service = app(SocketIOService::class);
        $this->messages = $service->getRoomMessages($this->roomId);

        // Get receiver info from room
        $room = \DB::connection('qestass_app')
            ->table('rooms')
            ->where('id', $this->roomId)
            ->first();

        if ($room) {
            $userId = auth()->id();
            // Determine receiver: if current user is userone, receiver is usertwo, and vice versa
            $this->receiverId = $room->userone_id == $userId ? $room->usertwo_id : $room->userone_id;

            // Get receiver name
            $receiver = \DB::connection('qestass_app')
                ->table('users')
                ->where('id', $this->receiverId)
                ->first();

            $this->receiverName = $receiver ? $receiver->first_name . ' ' . $receiver->last_name : 'Unknown';
        }

        $this->dispatch('messages-loaded');
    }

    public function sendMessage(): void
    {
        if (empty($this->newMessage) && !$this->file) {
            return;
        }

        $type = 'text';
        $content = $this->newMessage;

        // Handle file upload
        if ($this->file) {
            $path = $this->file->store('chat-files', 'public');
            $content = '/storage/' . $path;

            $extension = $this->file->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $type = 'image';
            } elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) {
                $type = 'sound';
            } else {
                $type = 'file';
            }
        }

        // Save to database
        \DB::connection('qestass_app')->table('room_messages')->insert([
            'room_id' => $this->roomId,
            'sender_id' => auth()->id(),
            'receiver_id' => $this->receiverId,
            'content' => $content,
            'type' => $type,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send via Socket.IO
        $service = app(SocketIOService::class);
        $service->sendMessage(
            $this->roomId,
            auth()->id(),
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

        $service = app(SocketIOService::class);
        $service->markAsRead($this->roomId, auth()->id());
        $this->dispatch('refresh-rooms')->to(Messages::class);
    }

    public function handleTyping(): void
    {
        // Emit typing event via Socket.IO
        $this->dispatch('user-typing', [
            'roomId' => $this->roomId,
            'userId' => auth()->id()
        ]);
    }

    public function initiateCall(string $callType = 'video'): void
    {
        \Log::info('ChatInterface: initiateCall called', [
            'callType' => $callType,
            'roomId' => $this->roomId,
            'receiverId' => $this->receiverId
        ]);

        $tokboxService = app(TokBoxService::class);

        // Create TokBox session
        $sessionData = $tokboxService->createCallSession(auth()->id(), $this->receiverId);

        \Log::info('ChatInterface: TokBox session created', [
            'success' => $sessionData['success'] ?? false,
            'sessionData' => $sessionData
        ]);

        if (!$sessionData['success']) {
            \Log::error('ChatInterface: Failed to create call session');
            $this->dispatch('call-error', message: 'Failed to create call session');
            return;
        }

        // Send call via Socket.IO
        $socketService = app(SocketIOService::class);

        $callData = [
            'room_id' => $this->roomId,
            'caller_id' => auth()->id(),
            'receiver_id' => $this->receiverId,
            'call_type' => $callType,
            'session_id' => $sessionData['session_id'],
            'api_key' => $sessionData['api_key'],
            'caller_token' => $sessionData['lawyer_token'],
            'receiver_token' => $sessionData['client_token'],
        ];

        \Log::info('ChatInterface: Dispatching initiate-call event', $callData);

        // Emit call event
        $this->dispatch('initiate-call', $callData);
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}

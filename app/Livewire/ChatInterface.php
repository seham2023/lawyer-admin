<?php

namespace App\Livewire;

use App\Services\SocketIOService;
use App\Filament\Pages\Messages;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;

class ChatInterface extends Component
{
    use WithFileUploads;

    public ?int $roomId = null;
    public array $messages = [];
    public string $newMessage = '';
    public $file;
    public bool $isTyping = false;
    public ?int $receiverId = null;
    public ?string $receiverName = null;

    protected $listeners = ['room-changed' => 'loadRoom'];

    public function mount(?int $roomId = null): void
    {
        if ($roomId) {
            $this->roomId = $roomId;
            $this->loadMessages();
        }
    }

    #[On('room-changed')]
    public function loadRoom(int $roomId): void
    {
        $this->roomId = $roomId;
        $this->loadMessages();
        $this->markAsRead();
    }

    public function loadMessages(): void
    {
        if (!$this->roomId) {
            return;
        }

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
        if (empty(trim($this->newMessage)) && !$this->file) {
            return;
        }

        $content = $this->newMessage;
        $type = 'text';

        // Handle file upload
        if ($this->file) {
            $filename = time() . '_' . $this->file->getClientOriginalName();
            $this->file->storeAs('chat', $filename, 'public');
            $content = $filename;
            $type = $this->getFileType($this->file->getClientOriginalExtension());
        }

        // Save to database
        \DB::connection('qestass_app')->table('room_messages')->insert([
            'room_id' => $this->roomId,
            'sender_id' => auth()->id(),
            'receiver_id' => $this->receiverId,
            'content' => $content,
            'type' => $type,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Emit Socket.IO event (handled by JavaScript)
        $this->dispatch('send-socket-message', [
            'room_id' => $this->roomId,
            'sender_id' => auth()->id(),
            'receiver_id' => $this->receiverId,
            'content' => $content,
            'type' => $type,
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

        $service = app(SocketIOService::class);
        $service->markAsRead($this->roomId, auth()->id());
        $this->dispatch('refresh-rooms')->to(Messages::class);
    }

    public function updatedNewMessage(): void
    {
        $this->dispatch('user-typing', [
            'room_id' => $this->roomId,
            'receiver_id' => $this->receiverId,
        ]);
    }

    protected function getFileType(string $extension): string
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $audioExtensions = ['mp3', 'wav', 'ogg', 'm4a'];

        if (in_array(strtolower($extension), $imageExtensions)) {
            return 'image';
        }

        if (in_array(strtolower($extension), $audioExtensions)) {
            return 'sound';
        }

        return 'file';
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}

<?php

namespace App\Livewire;

use App\Models\VideoCall;
use App\Models\CallMessage;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CallChatInterface extends Component
{
    public $callId;
    public $videoCall;
    public $messages = [];
    public $messageInput = '';
    public $isTyping = false;
    public $typingUsers = [];

    protected $listeners = [
        'messageReceived' => 'handleMessageReceived',
        'typingIndicator' => 'handleTypingIndicator',
    ];

    public function mount($callId = null)
    {
        if ($callId) {
            $this->callId = $callId;
            $this->videoCall = VideoCall::findOrFail($callId);
            
            // Verify user is part of this call
            $user = Auth::user();
            if ($this->videoCall->caller_id !== $user->id && $this->videoCall->receiver_id !== $user->id) {
                abort(403, 'Unauthorized');
            }

            // Load existing messages
            $this->loadMessages();
        }
    }

    public function loadMessages()
    {
        $this->messages = CallMessage::where('call_id', $this->callId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'sender_avatar' => $message->sender->avatar,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('H:i'),
                    'is_own' => $message->sender_id === Auth::id(),
                ];
            })
            ->toArray();
    }

    public function sendMessage()
    {
        if (empty(trim($this->messageInput))) {
            return;
        }

        $user = Auth::user();
        
        // Save message to database
        $message = CallMessage::create([
            'call_id' => $this->callId,
            'sender_id' => $user->id,
            'message' => $this->messageInput,
        ]);

        // Add to local messages
        $this->messages[] = [
            'id' => $message->id,
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'sender_avatar' => $user->avatar,
            'message' => $this->messageInput,
            'created_at' => $message->created_at->format('H:i'),
            'is_own' => true,
        ];

        // Emit Socket.IO event
        $this->dispatch('sendMessage', [
            'callId' => $this->callId,
            'message' => $this->messageInput,
            'senderName' => $user->name,
        ]);

        // Clear input and stop typing
        $this->messageInput = '';
        $this->isTyping = false;
        $this->dispatch('stopTyping', ['callId' => $this->callId]);
    }

    public function handleMessageReceived($data)
    {
        $this->messages[] = [
            'id' => $data['id'] ?? null,
            'sender_id' => $data['senderId'],
            'sender_name' => $data['senderName'],
            'sender_avatar' => $data['senderAvatar'] ?? null,
            'message' => $data['message'],
            'created_at' => now()->format('H:i'),
            'is_own' => false,
        ];
    }

    public function handleTypingIndicator($data)
    {
        if ($data['isTyping']) {
            if (!in_array($data['userId'], $this->typingUsers)) {
                $this->typingUsers[] = $data['userId'];
            }
        } else {
            $this->typingUsers = array_filter(
                $this->typingUsers,
                fn($id) => $id !== $data['userId']
            );
        }
    }

    public function onInputChange()
    {
        if (!$this->isTyping && !empty($this->messageInput)) {
            $this->isTyping = true;
            $this->dispatch('startTyping', ['callId' => $this->callId]);
        } elseif ($this->isTyping && empty($this->messageInput)) {
            $this->isTyping = false;
            $this->dispatch('stopTyping', ['callId' => $this->callId]);
        }
    }

    public function render()
    {
        return view('livewire.call-chat-interface');
    }
}


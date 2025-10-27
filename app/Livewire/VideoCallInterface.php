<?php

namespace App\Livewire;

use App\Models\VideoCall;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class VideoCallInterface extends Component
{
    public $callId;
    public $videoCall;
    public $sessionId;
    public $token;
    public $apiKey;
    public $callType = 'video';
    public $isCallActive = false;
    public $isMuted = false;
    public $isVideoOff = false;

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

            $this->sessionId = $this->videoCall->session_id;
            $this->token = $this->videoCall->token;
            $this->apiKey = $this->videoCall->api_key;
            $this->callType = $this->videoCall->call_type;
            $this->isCallActive = $this->videoCall->status === 'active';
        }
    }

    public function answerCall()
    {
        if (!$this->videoCall) {
            return;
        }

        $user = Auth::user();
        if ($this->videoCall->receiver_id !== $user->id) {
            $this->dispatch('error', 'You are not the receiver of this call');
            return;
        }

        $this->videoCall->update([
            'status' => 'active',
            'answered_at' => now(),
            'answered_on_web' => true,
        ]);

        $this->isCallActive = true;
        $this->dispatch('callAnswered', $this->videoCall->id);
    }

    public function declineCall()
    {
        if (!$this->videoCall) {
            return;
        }

        $user = Auth::user();
        if ($this->videoCall->receiver_id !== $user->id) {
            $this->dispatch('error', 'You are not the receiver of this call');
            return;
        }

        $this->videoCall->update([
            'status' => 'declined',
            'ended_at' => now(),
        ]);

        $this->dispatch('callDeclined', $this->videoCall->id);
    }

    public function endCall()
    {
        if (!$this->videoCall) {
            return;
        }

        $user = Auth::user();
        if ($this->videoCall->caller_id !== $user->id && $this->videoCall->receiver_id !== $user->id) {
            $this->dispatch('error', 'Unauthorized');
            return;
        }

        $this->videoCall->update([
            'status' => 'ended',
            'ended_at' => now(),
            'duration' => $this->videoCall->started_at ? now()->diffInSeconds($this->videoCall->started_at) : null,
        ]);

        $this->isCallActive = false;
        $this->dispatch('callEnded', $this->videoCall->id);
    }

    public function toggleMute()
    {
        $this->isMuted = !$this->isMuted;
        $this->dispatch('muteToggled', $this->isMuted);
    }

    public function toggleVideo()
    {
        $this->isVideoOff = !$this->isVideoOff;
        $this->dispatch('videoToggled', !$this->isVideoOff);
    }

    public function render()
    {
        return view('livewire.video-call-interface');
    }
}


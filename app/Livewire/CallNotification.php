<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class CallNotification extends Component
{
    public bool $showCallModal = false;
    public ?array $incomingCall = null;
    public string $callerName = '';
    public string $callType = 'audio';

    #[On('incoming-call')]
    public function handleIncomingCall(array $callData): void
    {
        $this->incomingCall = $callData;
        $this->callType = $callData['callType'] ?? $callData['call_type'] ?? 'audio';

        // Get caller name
        $callerId = $callData['caller_id'] ?? $callData['callerId'] ?? $callData['userId'] ?? 0;
        
        $caller = \DB::connection('qestass_app')
            ->table('users')
            ->where('id', $callerId)
            ->first();

        $this->callerName = $caller
            ? $caller->first_name . ' ' . $caller->last_name
            : ($callData['caller_name'] ?? $callData['callerName'] ?? 'Unknown Caller');

        $this->showCallModal = true;
        $this->dispatch('play-ringtone');
    }

    #[On('call-ended-remote')]
    public function handleRemoteEnd(): void
    {
        $this->showCallModal = false;
        $this->incomingCall = null;
        $this->dispatch('stop-ringtone');
    }

    /**
     * Also handle when call is accepted on another device
     */
    #[On('socket-call-accepted')]
    public function handleCallAcceptedOnOtherDevice(): void
    {
        // If we're showing the call modal, dismiss it
        // (the other device has picked up)
        if ($this->showCallModal) {
            $this->showCallModal = false;
            $this->incomingCall = null;
            $this->dispatch('stop-ringtone');
        }
    }

    /**
     * Also handle when call is rejected
     */
    #[On('socket-call-rejected')]
    public function handleCallRejected(): void
    {
        if ($this->showCallModal) {
            $this->showCallModal = false;
            $this->incomingCall = null;
            $this->dispatch('stop-ringtone');
        }
    }

    public function acceptCall(): void
    {
        if (!$this->incomingCall) {
            return;
        }

        // Emit event to JavaScript to handle connection
        $this->dispatch('accept-call', callData: $this->incomingCall);
        $this->showCallModal = false;
    }

    public function rejectCall(): void
    {
        if (!$this->incomingCall) {
            return;
        }

        // Emit socket event to reject call
        $this->dispatch('reject-call', callData: $this->incomingCall);
        $this->showCallModal = false;
        $this->incomingCall = null;
    }

    public function render()
    {
        return view('livewire.call-notification');
    }
}

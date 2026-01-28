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
        $this->callType = $callData['callType'] ?? 'audio';

        // Get caller name
        $caller = \DB::connection('qestass_app')
            ->table('users')
            ->where('id', $callData['userId'] ?? 0)
            ->first();

        $this->callerName = $caller
            ? $caller->first_name . ' ' . $caller->last_name
            : 'Unknown Caller';

        $this->showCallModal = true;
    }

    public function acceptCall(): void
    {
        if (!$this->incomingCall) {
            return;
        }

        // Emit event to JavaScript to handle TokBox connection
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

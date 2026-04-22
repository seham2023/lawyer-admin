<?php

namespace App\Filament\Lawyer\Pages;

use Filament\Pages\Page;

class VideoCall extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static string $view = 'filament.admin.pages.video-call';

    // Hide from navigation - only accessible via direct link
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Video Call';

    // Full width layout
    protected ?string $maxContentWidth = 'full';

    public ?string $sessionId = null;
    public ?string $token = null;
    public ?string $apiKey = null;
    public ?string $callType = 'video';
    public ?int $uid = null;
    public ?int $roomId = null;
    public ?int $callerId = null;
    public ?int $receiverId = null;
    public ?string $role = null;

    public function mount(): void
    {
        $this->sessionId = request()->query('session');
        $this->token = request()->query('token');
        $this->apiKey = request()->query('apiKey', config('services.agora.app_id'));
        $this->callType = request()->query('callType', 'video');
        $this->uid = auth()->id() ?? 0;
        $this->roomId = request()->integer('roomId') ?: null;
        $this->callerId = request()->integer('callerId') ?: null;
        $this->receiverId = request()->integer('receiverId') ?: null;
        $this->role = request()->query('role');
    }
}

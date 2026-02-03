<?php

namespace App\Filament\Pages;

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

    public function mount(): void
    {
        $this->sessionId = request()->query('session');
        $this->token = request()->query('token');
        $this->apiKey = request()->query('apiKey', config('services.tokbox.api_key', '47723411'));
        $this->callType = request()->query('callType', 'video');
    }
}

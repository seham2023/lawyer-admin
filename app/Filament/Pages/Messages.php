<?php

namespace App\Filament\Pages;

use App\Services\SocketIOService;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class Messages extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string $view = 'filament.admin.pages.messages';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?string $title = 'Messages';

    // Remove max-width constraint for full-width layout
    protected static string $maxContentWidth = 'full';

    // Remove padding for edge-to-edge layout
    public function getContentTabLabel(): ?string
    {
        return null;
    }

    protected static ?int $navigationSort = 1;

    public ?int $selectedRoomId = null;
    public array $rooms = [];
    public int $unreadCount = 0;
    public string $search = '';

    public static function getNavigationLabel(): string
    {
        return __('Messages');
    }

    public static function getNavigationBadge(): ?string
    {
        $service = app(SocketIOService::class);
        $count = $service->getUnreadCount(auth()->id());
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function mount(): void
    {
        $this->loadRooms();
    }

    #[On('room-selected')]
    public function selectRoom(int $roomId): void
    {
        $this->selectedRoomId = $roomId;
        $this->dispatch('room-changed', roomId: $roomId);
    }

    #[On('message-sent')]
    public function onMessageSent(): void
    {
        $this->loadRooms();
    }

    #[On('refresh-rooms')]
    public function loadRooms(): void
    {
        $service = app(SocketIOService::class);
        $this->rooms = $service->getActiveRooms(auth()->id());
        $this->unreadCount = $service->getUnreadCount(auth()->id());
    }

    public function getFilteredRoomsProperty(): array
    {
        if (empty($this->search)) {
            return $this->rooms;
        }

        return array_filter($this->rooms, function ($room) {
            $searchTerm = strtolower($this->search);
            // Search in client name or last message
            return str_contains(strtolower($room->client_name ?? ''), $searchTerm) ||
                str_contains(strtolower($room->last_message ?? ''), $searchTerm);
        });
    }
}

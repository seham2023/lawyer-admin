<?php

namespace App\Filament\Lawyer\Widgets;

use App\Services\SocketIOService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UnreadMessagesWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->checkPermissionTo('view Dashboard');
    }

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $service = app(SocketIOService::class);
        $userId = auth()->id();

        $unreadCount = $service->getUnreadCount($userId);
        $rooms = $service->getActiveRooms($userId);

        $activeConversations = count($rooms);
        $todayMessages = \DB::connection('qestass_app')
            ->table('room_messages')
            ->where('receiver_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId() ?? 'admin';
        $routeName = "filament.{$panelId}.pages.messages";
        $messagesUrl = \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : null;

        return [
            Stat::make(__('Unread Messages'), $unreadCount)
                ->description(__('Messages waiting for response'))
                ->descriptionIcon('heroicon-m-envelope')
                ->color('danger')
                ->url($messagesUrl),

            Stat::make(__('Active Conversations'), $activeConversations)
                ->description(__('Total client conversations'))
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('success'),

            Stat::make(__('Today\'s Messages'), $todayMessages)
                ->description(__('Messages received today'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '10s'; // Refresh every 10 seconds
    }
}

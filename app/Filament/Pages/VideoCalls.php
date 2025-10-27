<?php

namespace App\Filament\Pages;

use App\Models\VideoCall;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;

class VideoCalls extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    protected static ?string $navigationGroup = 'Communication';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.video-calls';
    protected static string $routePath = 'video-calls';
    protected static ?string $title = 'Video Calls';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getHeading(): string
    {
        return __('Video Calls');
    }

    public function getSubHeading(): ?string
    {
        return __('Manage and view your video calls');
    }

    /**
     * Get pending calls for the current user
     */
    public function getPendingCalls()
    {
        $user = Auth::user();
        return VideoCall::where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->with(['caller', 'caseRecord'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active calls for the current user
     */
    public function getActiveCalls()
    {
        $user = Auth::user();
        return VideoCall::where(function ($query) use ($user) {
            $query->where('caller_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        })
            ->where('status', 'active')
            ->with(['caller', 'receiver', 'caseRecord'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get call history for the current user
     */
    public function getCallHistory()
    {
        $user = Auth::user();
        return VideoCall::where(function ($query) use ($user) {
            $query->where('caller_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        })
            ->whereIn('status', ['ended', 'declined', 'missed'])
            ->with(['caller', 'receiver', 'caseRecord'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }
}


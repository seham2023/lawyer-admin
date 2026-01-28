<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocketIOService
{
    protected string $socketUrl;
    protected string $socketPath;

    public function __construct()
    {
        $this->socketUrl = config('socket.url', 'https://qestass.com:4722');
        $this->socketPath = config('socket.path', '/socket.io');
    }

    /**
     * Send a message through Socket.IO server
     */
    public function sendMessage(int $roomId, int $senderId, int $receiverId, string $content, string $type = 'text', ?int $duration = null): bool
    {
        try {
            $response = Http::timeout(5)->post("{$this->socketUrl}/api/sendMessage", [
                'room_id' => $roomId,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'content' => $content,
                'type' => $type,
                'duration' => $duration,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('SocketIO sendMessage failed', [
                'error' => $e->getMessage(),
                'room_id' => $roomId,
            ]);
            return false;
        }
    }

    /**
     * Get room messages from database
     */
    public function getRoomMessages(int $roomId, int $limit = 50, int $offset = 0): array
    {
        $messages = \DB::connection('qestass_app')
            ->table('room_messages')
            ->where('room_id', $roomId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->reverse()
            ->values()
            ->toArray();

        return $messages;
    }

    /**
     * Get unread message count for a user
     */
    public function getUnreadCount(int $userId): int
    {
        return \DB::connection('qestass_app')
            ->table('room_messages')
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(int $roomId, int $userId): bool
    {
        try {
            \DB::connection('qestass_app')
                ->table('room_messages')
                ->where('room_id', $roomId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return true;
        } catch (\Exception $e) {
            Log::error('SocketIO markAsRead failed', [
                'error' => $e->getMessage(),
                'room_id' => $roomId,
                'user_id' => $userId,
            ]);
            return false;
        }
    }

    /**
     * Get active rooms (conversations) for a user
     */
    public function getActiveRooms(int $userId): array
    {
        $rooms = \DB::connection('qestass_app')
            ->table('rooms')
            ->select([
                'rooms.*',
                \DB::raw('(SELECT COUNT(*) FROM room_messages WHERE room_messages.room_id = rooms.id AND room_messages.receiver_id = ? AND room_messages.is_read = false) as unread_count'),
                \DB::raw('(SELECT content FROM room_messages WHERE room_messages.room_id = rooms.id ORDER BY created_at DESC LIMIT 1) as last_message'),
                \DB::raw('(SELECT created_at FROM room_messages WHERE room_messages.room_id = rooms.id ORDER BY created_at DESC LIMIT 1) as last_message_at'),
                \DB::raw('(SELECT CONCAT(first_name, " ", last_name) FROM users WHERE users.id = IF(rooms.userone_id = ?, rooms.usertwo_id, rooms.userone_id)) as client_name'),
            ])
            ->where(function ($query) use ($userId) {
                $query->where('userone_id', $userId)
                    ->orWhere('usertwo_id', $userId);
            })
            ->orderBy('last_message_at', 'desc')
            ->setBindings([$userId, $userId, $userId, $userId]) // Bind for subqueries and WHERE clause
            ->get()
            ->toArray();

        return $rooms;
    }

    /**
     * Update user presence status
     */
    public function updatePresence(int $userId, string $status = 'online', string $platform = 'dashboard'): bool
    {
        try {
            \DB::connection('qestass_app')
                ->table('user_presence')
                ->updateOrInsert(
                    ['user_id' => $userId],
                    [
                        'status' => $status,
                        'platform' => $platform,
                        'last_seen' => now(),
                        'updated_at' => now(),
                    ]
                );

            return true;
        } catch (\Exception $e) {
            Log::error('SocketIO updatePresence failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return false;
        }
    }

    /**
     * Get user presence status
     */
    public function getUserPresence(int $userId): ?object
    {
        return \DB::connection('qestass_app')
            ->table('user_presence')
            ->where('user_id', $userId)
            ->first();
    }
}

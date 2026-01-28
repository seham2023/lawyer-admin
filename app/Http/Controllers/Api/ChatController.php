<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SocketIOService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected SocketIOService $socketService;

    public function __construct(SocketIOService $socketService)
    {
        $this->socketService = $socketService;
    }

    /**
     * Get all rooms (conversations) for authenticated user
     * 
     * @return JsonResponse
     */
    public function getRooms(): JsonResponse
    {
        $userId = auth()->id();
        $rooms = $this->socketService->getActiveRooms($userId);

        return response()->json([
            'success' => true,
            'data' => $rooms,
        ]);
    }

    /**
     * Get messages for a specific room
     * 
     * @param int $roomId
     * @param Request $request
     * @return JsonResponse
     */
    public function getMessages(int $roomId, Request $request): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $messages = $this->socketService->getRoomMessages($roomId, $limit, $offset);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Send a message (fallback if Socket.IO is unavailable)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|integer',
            'receiver_id' => 'required|integer',
            'content' => 'required|string',
            'type' => 'required|in:text,image,sound,file,map',
            'duration' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $senderId = auth()->id();
        $sent = $this->socketService->sendMessage(
            $request->room_id,
            $senderId,
            $request->receiver_id,
            $request->content,
            $request->type,
            $request->duration
        );

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'Message sent successfully' : 'Failed to send message',
        ], $sent ? 200 : 500);
    }

    /**
     * Mark messages as read in a room
     * 
     * @param int $roomId
     * @return JsonResponse
     */
    public function markAsRead(int $roomId): JsonResponse
    {
        $userId = auth()->id();
        $marked = $this->socketService->markAsRead($roomId, $userId);

        return response()->json([
            'success' => $marked,
            'message' => $marked ? 'Messages marked as read' : 'Failed to mark messages as read',
        ]);
    }

    /**
     * Get unread message count for authenticated user
     * 
     * @return JsonResponse
     */
    public function getUnreadCount(): JsonResponse
    {
        $userId = auth()->id();
        $count = $this->socketService->getUnreadCount($userId);

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Update user presence status
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePresence(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:online,offline,away',
            'platform' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth()->id();
        $updated = $this->socketService->updatePresence(
            $userId,
            $request->status,
            $request->platform ?? 'dashboard'
        );

        return response()->json([
            'success' => $updated,
            'message' => $updated ? 'Presence updated' : 'Failed to update presence',
        ]);
    }

    /**
     * Get user presence status
     * 
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserPresence(int $userId): JsonResponse
    {
        $presence = $this->socketService->getUserPresence($userId);

        return response()->json([
            'success' => true,
            'data' => $presence,
        ]);
    }
}

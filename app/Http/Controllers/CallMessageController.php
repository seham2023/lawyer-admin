<?php

namespace App\Http\Controllers;

use App\Models\CallMessage;
use App\Models\VideoCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallMessageController extends Controller
{
    /**
     * Send a message during a call
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:video_calls,id',
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        $videoCall = VideoCall::findOrFail($validated['call_id']);

        // Verify user is part of this call
        if ($videoCall->caller_id !== $user->id && $videoCall->receiver_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create message
        $message = CallMessage::create([
            'call_id' => $validated['call_id'],
            'sender_id' => $user->id,
            'message' => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $user->name,
                'sender_avatar' => $user->avatar,
                'message' => $message->message,
                'created_at' => $message->created_at->format('H:i'),
            ],
        ]);
    }

    /**
     * Get chat history for a call
     */
    public function getChatHistory(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:video_calls,id',
            'limit' => 'integer|min:1|max:100',
        ]);

        $user = Auth::user();
        $videoCall = VideoCall::findOrFail($validated['call_id']);

        // Verify user is part of this call
        if ($videoCall->caller_id !== $user->id && $videoCall->receiver_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $limit = $validated['limit'] ?? 50;

        $messages = CallMessage::where('call_id', $validated['call_id'])
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'sender_avatar' => $message->sender->avatar,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('H:i'),
                    'is_own' => $message->sender_id === $user->id,
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'count' => $messages->count(),
        ]);
    }

    /**
     * Delete a message
     */
    public function deleteMessage(Request $request, $messageId)
    {
        $user = Auth::user();
        $message = CallMessage::findOrFail($messageId);

        // Verify user is the sender
        if ($message->sender_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted',
        ]);
    }

    /**
     * Get messages for a call with pagination
     */
    public function getMessages(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:video_calls,id',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
        ]);

        $user = Auth::user();
        $videoCall = VideoCall::findOrFail($validated['call_id']);

        // Verify user is part of this call
        if ($videoCall->caller_id !== $user->id && $videoCall->receiver_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $perPage = $validated['per_page'] ?? 20;

        $messages = CallMessage::where('call_id', $validated['call_id'])
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'last_page' => $messages->lastPage(),
            ],
        ]);
    }
}


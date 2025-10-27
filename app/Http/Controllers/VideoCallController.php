<?php

namespace App\Http\Controllers;

use App\Models\VideoCall;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class VideoCallController extends Controller
{
    /**
     * Create a new video call session
     */
    public function createSession(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'case_record_id' => 'nullable|exists:case_records,id',
            'call_type' => 'required|in:audio,video',
        ]);

        $caller = Auth::user();
        $receiver = User::findOrFail($validated['receiver_id']);

        // Call Node.js server to create OpenTok session
        try {
            $response = Http::withHeaders([
                'lang' => app()->getLocale(),
            ])->get(config('services.opentok.node_server_url') . '/api/createSessionToken', [
                'senderId' => $caller->id,
                'receiverId' => $receiver->id,
                'roomId' => $validated['case_record_id'] ?? 0,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create video session',
                ], 500);
            }

            $data = $response->json();

            if ($data['key'] !== 'success') {
                return response()->json([
                    'success' => false,
                    'message' => $data['msg'] ?? 'Failed to create session',
                ], 400);
            }

            // Create video call record
            $videoCall = VideoCall::create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'case_record_id' => $validated['case_record_id'],
                'session_id' => $data['data']['sessionId'],
                'token' => $data['data']['token'],
                'api_key' => $data['data']['apiKey'],
                'call_type' => $validated['call_type'],
                'status' => 'pending',
                'started_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'call_id' => $videoCall->id,
                    'session_id' => $data['data']['sessionId'],
                    'token' => $data['data']['token'],
                    'api_key' => $data['data']['apiKey'],
                    'call_type' => $validated['call_type'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating video session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Answer a video call
     */
    public function answerCall(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:video_calls,id',
            'answered_on_web' => 'boolean',
        ]);

        $videoCall = VideoCall::findOrFail($validated['call_id']);
        $user = Auth::user();

        // Verify the user is the receiver
        if ($videoCall->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $videoCall->update([
            'status' => 'active',
            'answered_at' => now(),
            'answered_on_web' => $validated['answered_on_web'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Call answered',
            'data' => $videoCall,
        ]);
    }

    /**
     * End a video call
     */
    public function endCall(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:video_calls,id',
        ]);

        $videoCall = VideoCall::findOrFail($validated['call_id']);
        $user = Auth::user();

        // Verify the user is either caller or receiver
        if ($videoCall->caller_id !== $user->id && $videoCall->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $videoCall->update([
            'status' => 'ended',
            'ended_at' => now(),
            'duration' => $videoCall->started_at ? now()->diffInSeconds($videoCall->started_at) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Call ended',
            'data' => $videoCall,
        ]);
    }

    /**
     * Decline a video call
     */
    public function declineCall(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:video_calls,id',
        ]);

        $videoCall = VideoCall::findOrFail($validated['call_id']);
        $user = Auth::user();

        // Verify the user is the receiver
        if ($videoCall->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $videoCall->update([
            'status' => 'declined',
            'ended_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Call declined',
        ]);
    }

    /**
     * Get pending calls for the authenticated user
     */
    public function getPendingCalls()
    {
        $user = Auth::user();

        $calls = VideoCall::where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->with(['caller', 'caseRecord'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $calls,
        ]);
    }

    /**
     * Get call history for the authenticated user
     */
    public function getCallHistory()
    {
        $user = Auth::user();

        $calls = VideoCall::where(function ($query) use ($user) {
            $query->where('caller_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        })
            ->with(['caller', 'receiver', 'caseRecord'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $calls,
        ]);
    }
}


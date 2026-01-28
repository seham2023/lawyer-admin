<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoCallController;
use App\Http\Controllers\CallMessageController;
use App\Http\Controllers\Api\ChatController;

Route::middleware('auth:sanctum')->group(function () {
    // Video call endpoints
    Route::post('/video-calls/create-session', [VideoCallController::class, 'createSession']);
    Route::post('/video-calls/answer', [VideoCallController::class, 'answerCall']);
    Route::post('/video-calls/end', [VideoCallController::class, 'endCall']);
    Route::post('/video-calls/decline', [VideoCallController::class, 'declineCall']);
    Route::get('/video-calls/pending', [VideoCallController::class, 'getPendingCalls']);
    Route::get('/video-calls/history', [VideoCallController::class, 'getCallHistory']);

    // Chat message endpoints
    Route::post('/call-messages/send', [CallMessageController::class, 'sendMessage']);
    Route::get('/call-messages/history', [CallMessageController::class, 'getChatHistory']);
    Route::get('/call-messages', [CallMessageController::class, 'getMessages']);
    Route::delete('/call-messages/{messageId}', [CallMessageController::class, 'deleteMessage']);

    // Chat/Messaging API endpoints
    Route::prefix('chat')->group(function () {
        Route::get('/rooms', [ChatController::class, 'getRooms']);
        Route::get('/rooms/{roomId}/messages', [ChatController::class, 'getMessages']);
        Route::post('/messages', [ChatController::class, 'sendMessage']);
        Route::put('/rooms/{roomId}/read', [ChatController::class, 'markAsRead']);
        Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);
        Route::post('/presence', [ChatController::class, 'updatePresence']);
        Route::get('/presence/{userId}', [ChatController::class, 'getUserPresence']);
    });
});

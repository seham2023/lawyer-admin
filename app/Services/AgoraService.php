<?php

namespace App\Services;

use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;
use Illuminate\Support\Facades\Log;

class AgoraService
{
    protected string $appId;
    protected string $appCertificate;

    public function __construct()
    {
        $this->appId = config('services.agora.app_id');
        $this->appCertificate = config('services.agora.app_certificate');
    }

    /**
     * Generate a token for a user to join a session
     */
    public function generateToken(string $channelName, int $uid, string $role = 'publisher'): ?string
    {
        try {
            $tokenRole = $role === 'moderator' ? RtcTokenBuilder::RolePublisher : RtcTokenBuilder::RoleSubscriber;
            if ($role === 'publisher') $tokenRole = RtcTokenBuilder::RolePublisher;
            
            $privilegeExpireTs = time() + (60 * 60 * 24); // 24 hours

            return RtcTokenBuilder::buildTokenWithUid(
                $this->appId,
                $this->appCertificate,
                $channelName,
                $uid,
                $tokenRole,
                $privilegeExpireTs
            );
        } catch (\Exception $e) {
            Log::error('Agora token generation failed', [
                'error' => $e->getMessage(),
                'channelName' => $channelName
            ]);
            return null;
        }
    }

    /**
     * Create session and generate tokens for both participants
     */
    public function createCallSession(int $lawyerId, int $clientId, ?int $roomId = null): array
    {
        $channelName = 'room_' . ($roomId ?? $lawyerId . '_' . $clientId . '_' . time());

        $lawyerToken = $this->generateToken($channelName, $lawyerId, 'publisher');
        $clientToken = $this->generateToken($channelName, $clientId, 'publisher'); // Both publish

        if (!$lawyerToken || !$clientToken) {
             return [
                'success' => false,
                'error' => 'Failed to generate tokens.'
            ];
        }

        return [
            'success' => true,
            'session_id' => $channelName,
            'api_key' => $this->appId,
            'lawyer_token' => $lawyerToken,
            'client_token' => $clientToken
        ];
    }

    /**
     * Get App ID
     */
    public function getApiKey(): string
    {
        return $this->appId;
    }
}

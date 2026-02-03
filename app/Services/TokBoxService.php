<?php

namespace App\Services;

use OpenTok\OpenTok;
use OpenTok\Session;
use OpenTok\Role;
use Illuminate\Support\Facades\Log;

class TokBoxService
{
    protected OpenTok $opentok;
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct()
    {
        $this->apiKey = config('services.tokbox.api_key', '47723411');
        $this->apiSecret = config('services.tokbox.api_secret', '95d722d4d34dd4d46259a1d5837a18d07bc3b9d8');

        $this->opentok = new OpenTok($this->apiKey, $this->apiSecret);
    }

    /**
     * Create a new TokBox session
     */
    public function createSession(): array
    {
        try {
            $session = $this->opentok->createSession([
                'mediaMode' => 'routed' // Use routed for better reliability
            ]);

            return [
                'success' => true,
                'session_id' => $session->getSessionId(),
                'api_key' => $this->apiKey
            ];
        } catch (\Exception $e) {
            Log::error('TokBox session creation failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate a token for a user to join a session
     */
    public function generateToken(string $sessionId, string $role = 'publisher', ?string $data = null): ?string
    {
        try {
            $tokenRole = $role === 'moderator' ? Role::MODERATOR : Role::PUBLISHER;

            $tokenOptions = [
                'role' => $tokenRole,
                'expireTime' => time() + (60 * 60 * 24) // 24 hours
            ];

            if ($data) {
                $tokenOptions['data'] = $data;
            }

            return $this->opentok->generateToken($sessionId, $tokenOptions);
        } catch (\Exception $e) {
            Log::error('TokBox token generation failed', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);

            return null;
        }
    }

    /**
     * Create session and generate tokens for both participants
     */
    public function createCallSession(int $lawyerId, int $clientId): array
    {
        $sessionData = $this->createSession();

        if (!$sessionData['success']) {
            return $sessionData;
        }

        $sessionId = $sessionData['session_id'];

        return [
            'success' => true,
            'session_id' => $sessionId,
            'api_key' => $this->apiKey,
            'lawyer_token' => $this->generateToken($sessionId, 'moderator', "user_id:{$lawyerId}"),
            'client_token' => $this->generateToken($sessionId, 'publisher', "user_id:{$clientId}")
        ];
    }

    /**
     * Get API key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}

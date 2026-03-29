<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class HardenLivewireRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only target Livewire update requests
        if ($request->is('livewire/update')) {
            // Keep rate limiting enabled, but make it realistic for Filament dashboards
            // with polling widgets, notifications, and chat screens.
            $identifier = $request->user()?->getAuthIdentifier() ?? $request->ip();
            $maxAttempts = $request->user() ? 240 : 60;
            $decaySeconds = 60;
            $rateLimitKey = 'livewire-update:' . $identifier;

            if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
                Log::warning('Rate limit exceeded for Livewire update', [
                    'ip' => $request->ip(),
                    'user_id' => $request->user()?->getAuthIdentifier(),
                    'available_in' => RateLimiter::availableIn($rateLimitKey),
                ]);

                return response()->json([
                    'message' => 'Too many requests. Please slow down.',
                ], 429);
            }

            RateLimiter::hit($rateLimitKey, $decaySeconds);

            $payload = $request->all();
            
            // 2. Inspection: Check for common injection strings
            if ($this->containsMaliciousStrings($payload)) {
                Log::error('Malicious Livewire payload blocked from IP: ' . $request->ip(), [
                    'payload' => $payload,
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json(['message' => 'Security violation detected.'], 403);
            }
        }

        return $next($request);
    }

    /**
     * Check if the array/string contains common exploit parents
     */
    protected function containsMaliciousStrings($input): bool
    {
        $maliciousPatterns = [
            'eval\(', 
            'base64_decode', 
            'system\(', 
            'shell_exec', 
            'passthru', 
            'exec\(', 
            'phpinfo',
            '/_ignition/',
            'printenv',
            'cat /etc/passwd'
        ];

        $jsonString = json_encode($input);
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match('#' . $pattern . '#i', $jsonString)) {
                return true;
            }
        }

        return false;
    }
}

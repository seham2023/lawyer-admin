<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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
            // 1. Rate Limiting: Max 30 requests per minute from the same IP
            $executed = RateLimiter::attempt(
                'livewire-update:' . $request->ip(),
                30,
                function () {
                    // Method to be called if rate limit not reached
                },
                60 // Decay minutes
            );

            if (!$executed) {
                Log::warning('Rate limit exceeded for Livewire update from IP: ' . $request->ip());
                return response()->json(['message' => 'Too many requests. Please slow down.'], 429);
            }

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

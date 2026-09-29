<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = (string) config('services.telecom.api_key', '');
        $providedKey = $request->bearerToken() ?? $request->header('X-API-KEY');

        if (empty($expectedKey) || empty($providedKey) || ! hash_equals($expectedKey, (string) $providedKey)) {
            return response()->json([
                'message' => 'Authentification requise. Jeton Bearer ou clé d\'API manquant ou invalide.',
                'error' => 'UNAUTHORIZED',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}

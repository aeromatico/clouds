<?php namespace Aero\Manager\Middleware;

use Closure;
use Config;
use Illuminate\Http\Request;

class CheckApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || !preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        $token = $matches[1];
        $validTokens = config('api_tokens.tokens', []);

        if (!in_array($token, $validTokens)) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        // Token válido, continua la petición
        return $next($request);
    }
}

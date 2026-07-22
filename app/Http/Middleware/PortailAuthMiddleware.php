<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PortailAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('portail')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }

            return redirect()->route('portail.login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::guard('portail')->user();
        if (!$user || $user->status !== 'active') {
            Auth::guard('portail')->logout();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Compte désactivé'], 403);
            }

            return redirect()->route('portail.login')
                ->with('error', 'Votre compte a été désactivé. Contactez l\'administration.');
        }

        if (!$user->client) {
            Auth::guard('portail')->logout();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Client non trouvé'], 404);
            }

            return redirect()->route('portail.login')
                ->with('error', 'Client non trouvé. Contactez l\'administration.');
        }

        return $next($request);
    }
}

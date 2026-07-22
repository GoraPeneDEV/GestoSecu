<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PortailDataFilterMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('portail')->check()) {
            return redirect()->route('portail.login');
        }

        $user = Auth::guard('portail')->user();

        $request->merge(['portail_client_id' => $user->client_id]);

        view()->share('portailClient', $user->client);
        view()->share('portailUser', $user);

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('portail.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $key = Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives de connexion. Réessayez dans {$seconds} secondes.",
            ]);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('portail')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            RateLimiter::clear($key);

            $user = Auth::guard('portail')->user();
            if ($user) {
                DB::table('portail_users')
                    ->where('id', $user->id)
                    ->update(['last_login_at' => now()]);
            }

            $this->logActivity('login', 'Connexion réussie', $request);

            return redirect()->intended(route('portail.dashboard'));
        }

        RateLimiter::hit($key);

        throw ValidationException::withMessages([
            'email' => 'Ces identifiants ne correspondent à aucun compte.',
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('portail')->user();

        if ($user) {
            $this->logActivity('logout', 'Déconnexion', $request);
        }

        Auth::guard('portail')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portail.login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }

    private function logActivity($action, $description, Request $request)
    {
        $user = Auth::guard('portail')->user();

        if ($user) {
            DB::table('portail_activity_logs')->insert([
                'portail_user_id' => $user->id,
                'action' => $action,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        }
    }
}

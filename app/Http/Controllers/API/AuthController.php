<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Connexion utilisateur pour l'app mobile
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:4',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect'
                ], 401);
            }

            $user = Auth::user();

            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre compte est désactivé. Contactez l\'administrateur.'
                ], 403);
            }

            // Révoquer les anciens tokens mobiles avant d'en créer un nouveau
            $user->tokens()->where('name', 'mobile-app')->delete();

            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->buildUserPayload($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur lors de la connexion',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Récupérer les informations de l'utilisateur connecté
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || $user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé ou désactivé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => $this->buildUserPayload($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations utilisateur',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Construit la charge utile "user" partagée entre login() et user().
     * Inclut la liste complète des permissions/rôles Spatie et un indicateur
     * is_super_admin qui réplique le Gate::before global (AuthServiceProvider)
     * afin que le menu mobile applique le même bypass que le back-office web.
     */
    private function buildUserPayload(User $user): array
    {
        $user->load(['departement', 'employe']);

        return [
            'id' => $user->id,
            'prenom' => $user->prenom,
            'nom' => $user->nom,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'status' => $user->status,
            'employe_id' => $user->id_employe,
            'departement' => [
                'id' => $user->departement?->id,
                'nom' => $user->departement?->nom,
            ],
            'employe' => $user->employe ? [
                'id' => $user->employe->id,
                'matricule' => $user->employe->matricule,
                'fonction' => $user->employe->fonction,
            ] : null,
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'roles' => $user->getRoleNames()->values(),
            'is_super_admin' => $user->hasRole('super_admin'),
        ];
    }

    /**
     * Déconnexion utilisateur
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Rafraîchir le token
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Compte désactivé'
                ], 403);
            }

            $request->user()->currentAccessToken()->delete();

            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'message' => 'Token rafraîchi avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement du token',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Mot de passe oublié
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email non trouvé dans notre système',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce compte est désactivé'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Un email de réinitialisation a été envoyé à votre adresse'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la demande de réinitialisation',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Test de connexion API (pour debug)
     */
    public function ping()
    {
        return response()->json([
            'success' => true,
            'message' => 'API GestoSecu Mobile connectée',
            'timestamp' => now(),
            'version' => '1.0.0',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
        ]);
    }
}

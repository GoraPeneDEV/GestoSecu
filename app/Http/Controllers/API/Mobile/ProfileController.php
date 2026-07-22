<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Socle commun — tout utilisateur authentifié peut consulter/modifier son
 * propre profil, sans permission Spatie additionnelle.
 *
 * Le mot de passe n'est pas mis à jour via une action Fortify qui valide
 * `current_password:web` (règle qui vérifie la session du guard "web",
 * inexistante dans un contexte API Sanctum stateless) : la vérification du
 * mot de passe actuel est refaite ici manuellement.
 */
class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'prenom' => 'sometimes|string|max:255',
            'nom' => 'sometimes|string|max:255',
            'telephone' => 'sometimes|nullable|string|max:30',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->fill($validator->validated());
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour',
            'data' => $user->only(['id', 'prenom', 'nom', 'email', 'telephone']),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe actuel est incorrect',
            ], 422);
        }

        $user->forceFill(['password' => Hash::make($request->input('password'))])->save();

        return response()->json(['success' => true, 'message' => 'Mot de passe mis à jour']);
    }

    public function updateAvatar(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier invalide',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->forceFill(['profile_photo_path' => $path])->save();

        return response()->json([
            'success' => true,
            'message' => 'Photo de profil mise à jour',
            'data' => ['profile_photo_url' => $user->profile_photo_url],
        ]);
    }
}

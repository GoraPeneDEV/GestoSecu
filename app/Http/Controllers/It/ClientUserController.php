<?php

namespace App\Http\Controllers\It;

use App\Http\Controllers\Controller;
use App\Models\PortailUser;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ClientUserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('portail-user-view');

        if ($request->ajax()) {
            $query = PortailUser::with('client');

            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addColumn('client_nom', fn($u) => $u->client->nomClient ?? '-')
                ->addColumn('nom_complet', fn($u) => $u->prenom . ' ' . $u->nom)
                ->addColumn('status_badge', fn($u) => $u->status === 'active'
                    ? '<span class="badge bg-success">Actif</span>'
                    : '<span class="badge bg-secondary">Inactif</span>')
                ->editColumn('last_login_at', fn($u) => $u->last_login_at?->format('d/m/Y H:i') ?? 'Jamais')
                ->addColumn('actions', fn($u) => '
                    <button class="btn btn-sm btn-icon btn-warning" onclick="editCompte(' . $u->id . ')" title="Modifier"><i class="ti ti-pencil"></i></button>
                    <button class="btn btn-sm btn-icon btn-info" onclick="resetPasswordCompte(' . $u->id . ')" title="Réinitialiser le mot de passe"><i class="ti ti-key"></i></button>
                    <button class="btn btn-sm btn-icon btn-danger" onclick="deleteCompte(' . $u->id . ')" title="Supprimer"><i class="ti ti-trash"></i></button>
                ')
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        $stats = [
            'total' => PortailUser::count(),
            'actifs' => PortailUser::where('status', 'active')->count(),
            'inactifs' => PortailUser::where('status', '!=', 'active')->count(),
        ];

        $clients = Client::orderBy('nomClient')->get();

        return view('it.client-users.index', compact('stats', 'clients'));
    }

    public function store(Request $request)
    {
        $this->authorize('portail-user-create');

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:portail_users,email',
            'telephone' => 'nullable|string|max:20',
            'fonction' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $motDePasseTemporaire = Str::password(12);

        $user = PortailUser::create([
            ...$validated,
            'password' => $motDePasseTemporaire,
        ]);

        $clientRole = \Spatie\Permission\Models\Role::where('name', 'client')->where('guard_name', 'portail')->first();
        if ($clientRole) {
            $user->assignRole($clientRole);
        }

        return response()->json([
            'success' => true,
            'message' => 'Compte créé avec succès.',
            'mot_de_passe_temporaire' => $motDePasseTemporaire,
        ]);
    }

    public function edit(PortailUser $clientUser)
    {
        $this->authorize('portail-user-update');

        return response()->json($clientUser);
    }

    public function update(Request $request, PortailUser $clientUser)
    {
        $this->authorize('portail-user-update');

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:portail_users,email,' . $clientUser->id,
            'telephone' => 'nullable|string|max:20',
            'fonction' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $clientUser->update($validated);

        return response()->json(['success' => true, 'message' => 'Compte mis à jour avec succès.']);
    }

    public function resetPassword(PortailUser $clientUser)
    {
        $this->authorize('portail-user-reset-password');

        $nouveauMotDePasse = Str::password(12);
        $clientUser->update(['password' => $nouveauMotDePasse]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès.',
            'mot_de_passe_temporaire' => $nouveauMotDePasse,
        ]);
    }

    public function destroy(PortailUser $clientUser)
    {
        $this->authorize('portail-user-delete');

        $clientUser->delete();

        return response()->json(['success' => true, 'message' => 'Compte supprimé avec succès.']);
    }
}

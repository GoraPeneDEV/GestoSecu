<?php

namespace App\Http\Controllers\SAV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SAV\ClientInteraction;
use App\Models\SAV\ClientContact;
use App\Models\Client;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class ClientInteractionController extends Controller
{
    private array $types = [
        'appel_entrant' => 'Appel entrant',
        'appel_sortant' => 'Appel sortant',
        'email_recu' => 'Email reçu',
        'email_envoye' => 'Email envoyé',
        'reunion' => 'Réunion',
        'visite_site' => 'Visite sur site',
        'ticket_sav' => 'Ticket SAV',
        'contrat_signe' => 'Contrat signé',
        'facture' => 'Facture',
        'relance' => 'Relance',
        'autre' => 'Autre',
    ];

    public function index(Request $request)
    {
        $this->authorize('sav-interaction-view');

        if ($request->ajax()) {
            $query = ClientInteraction::with(['client', 'contact', 'user', 'attribueA']);

            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            return DataTables::of($query)
                ->addColumn('client_nom', fn($interaction) => $interaction->client ? $interaction->client->nomClient : '-')
                ->addColumn('type_label', fn($interaction) => '<i class="ti ' . $interaction->type_icon . ' me-1"></i>' . $interaction->type_label)
                ->addColumn('statut_badge', fn($interaction) => $interaction->statut_badge)
                ->addColumn('sens_badge', function ($interaction) {
                    $badges = [
                        'entrant' => '<span class="badge bg-label-info">Entrant</span>',
                        'sortant' => '<span class="badge bg-label-warning">Sortant</span>',
                        'interne' => '<span class="badge bg-label-secondary">Interne</span>',
                    ];
                    return $badges[$interaction->sens] ?? '-';
                })
                ->addColumn('actions', fn($interaction) => '<a href="' . route('sav.interactions.show', $interaction->id) . '" class="btn btn-sm btn-info"><i class="ti ti-eye"></i></a>')
                ->rawColumns(['type_label', 'statut_badge', 'sens_badge', 'actions'])
                ->make(true);
        }

        $clients = Client::all();

        return view('sav.interactions.index', ['clients' => $clients, 'types' => $this->types]);
    }

    public function create(Request $request)
    {
        $this->authorize('sav-interaction-create');

        $clients = Client::all();
        $clientPreselected = $request->filled('client_id') ? Client::find($request->client_id) : null;
        $contactPreselected = $request->filled('contact_id') ? ClientContact::find($request->contact_id) : null;
        $users = User::where('status', 'active')->get();
        $canaux = ['telephone', 'email', 'reunion', 'portail', 'courrier', 'autre'];

        return view('sav.interactions.create', [
            'clients' => $clients,
            'clientPreselected' => $clientPreselected,
            'contactPreselected' => $contactPreselected,
            'users' => $users,
            'types' => $this->types,
            'canaux' => $canaux,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('sav-interaction-create');

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contact_client_id' => 'nullable|exists:client_contacts,id',
            'type' => 'required|in:appel_entrant,appel_sortant,email_recu,email_envoye,reunion,visite_site,ticket_sav,contrat_signe,facture,relance,autre',
            'sujet' => 'required|string|max:255',
            'contenu' => 'nullable|string',
            'canal' => 'required|in:telephone,email,reunion,portail,courrier,autre',
            'sens' => 'required|in:entrant,sortant,interne',
            'statut' => 'nullable|in:a_traiter,en_attente,traite,urgent',
            'rappel_le' => 'nullable|date',
            'rappel_attribue_a' => 'nullable|exists:users,id',
        ]);

        $interaction = ClientInteraction::create([
            'client_id' => $validated['client_id'],
            'contact_client_id' => $validated['contact_client_id'] ?? null,
            'type' => $validated['type'],
            'sujet' => $validated['sujet'],
            'contenu' => $validated['contenu'] ?? null,
            'canal' => $validated['canal'],
            'sens' => $validated['sens'],
            'statut' => $validated['statut'] ?? 'traite',
            'rappel_le' => $validated['rappel_le'] ?? null,
            'rappel_attribue_a' => $validated['rappel_attribue_a'] ?? null,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('sav.interactions.show', $interaction->id)->with('success', 'Interaction enregistrée avec succès');
    }

    public function show(ClientInteraction $interaction)
    {
        $this->authorize('sav-interaction-view');

        $interaction->load(['client.contacts', 'contact', 'user', 'attribueA', 'relatable']);

        return view('sav.interactions.show', compact('interaction'));
    }

    public function marquerTraite(ClientInteraction $interaction)
    {
        $this->authorize('sav-interaction-edit');

        $interaction->marquerTraite();

        return back()->with('success', 'Interaction marquée comme traitée');
    }

    public function programmerRappel(Request $request, ClientInteraction $interaction)
    {
        $this->authorize('sav-interaction-edit');

        $validated = $request->validate([
            'rappel_le' => 'required|date|after:now',
            'rappel_attribue_a' => 'required|exists:users,id',
        ]);

        $interaction->programmerRappel($validated['rappel_le'], $validated['rappel_attribue_a']);

        return back()->with('success', 'Rappel programmé');
    }

    public function destroy(ClientInteraction $interaction)
    {
        $this->authorize('sav-interaction-delete');

        $interaction->delete();

        return redirect()->route('sav.interactions.index')->with('success', 'Interaction supprimée');
    }
}

<?php

namespace App\Http\Controllers\SAV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SAV\ClientAsset;
use App\Models\Client;
use App\Models\Site;

class ClientAssetController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('sav-parc-view');

        $clients = Client::whereHas('sites.clientAssets')
            ->with(['sites' => function ($q) {
                $q->whereHas('clientAssets')->withCount('clientAssets');
            }])
            ->orderBy('nomClient')
            ->get();

        $allClients = Client::orderBy('nomClient')->get(['id', 'nomClient', 'numeroClient']);

        $sitesByClient = Site::orderBy('nom_site')
            ->get(['id', 'nom_site', 'client_id'])
            ->groupBy('client_id')
            ->map(fn($sites) => $sites->values());

        return view('sav.parc.index', compact('clients', 'allClients', 'sitesByClient'));
    }

    public function clientDetail(Client $client)
    {
        $this->authorize('sav-parc-view');

        $sites = $client->sites()
            ->with(['clientAssets' => fn($q) => $q->latest()])
            ->withCount('clientAssets')
            ->orderBy('nom_site')
            ->get();

        $allSites = Site::with('client')->orderBy('nom_site')->get();

        return view('sav.parc.client', compact('client', 'sites', 'allSites'));
    }

    public function store(Request $request)
    {
        $this->authorize('sav-parc-create');

        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'type' => 'required|in:incendie,securite_electronique,monetique',
            'category' => 'nullable|string',
            'label' => 'required|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'installation_date' => 'nullable|date',
        ]);

        ClientAsset::create($validated);

        return back()->with('success', 'Équipement ajouté au parc avec succès.');
    }

    public function edit(ClientAsset $asset)
    {
        $this->authorize('sav-parc-edit');

        return view('sav.parc.edit', compact('asset'));
    }

    public function update(Request $request, ClientAsset $asset)
    {
        $this->authorize('sav-parc-edit');

        $validated = $request->validate([
            'type' => 'required|in:incendie,securite_electronique,monetique',
            'category' => 'nullable|string',
            'label' => 'required|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'installation_date' => 'nullable|date',
            'status' => 'nullable|in:fonctionnel,panne,maintenance_requise,hors_service',
            'notes' => 'nullable|string',
        ]);

        $asset->update($validated);

        return redirect()->route('sav.parc.index', ['site_id' => $asset->site_id])->with('success', 'Équipement mis à jour.');
    }

    public function destroy(ClientAsset $asset)
    {
        $this->authorize('sav-parc-delete');

        $siteId = $asset->site_id;
        $asset->delete();

        return redirect()->route('sav.parc.index', ['site_id' => $siteId])->with('success', 'Équipement supprimé du parc.');
    }
}

@extends('layouts.app')

@section('title', 'Parc client')

@section('content')
    <h3 class="mb-4">Parc client</h3>

    <div class="card mb-3">
        <div class="card-header">Nouvel équipement</div>
        <div class="card-body">
            <form method="POST" action="{{ route('sav.parc.store') }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <select id="clientSelect" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        @foreach ($allClients as $client)
                            <option value="{{ $client->id }}">{{ $client->nomClient }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Site *</label>
                    <select name="site_id" id="siteSelect" class="form-select" required>
                        <option value="">-- Sélectionner un client --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type *</label>
                    <select name="type" class="form-select" required>
                        <option value="incendie">Incendie</option>
                        <option value="securite_electronique">Sécurité électronique</option>
                        <option value="monetique">Monétique</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Libellé *</label>
                    <input type="text" name="label" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Catégorie</label>
                    <input type="text" name="category" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Marque</label>
                    <input type="text" name="brand" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Modèle</label>
                    <input type="text" name="model" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° série</label>
                    <input type="text" name="serial_number" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date d'installation</label>
                    <input type="date" name="installation_date" class="form-control">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    @if ($clients->isEmpty())
        <p class="text-muted">Aucun client n'a d'équipement enregistré.</p>
    @endif

    @foreach ($clients as $client)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <a href="{{ route('sav.parc.client', $client->id) }}">{{ $client->nomClient }}</a>
                <span class="text-muted small">{{ $client->sites->sum('client_assets_count') }} équipement(s)</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Site</th><th>Équipements</th></tr></thead>
                        <tbody>
                            @foreach ($client->sites as $site)
                                <tr>
                                    <td>{{ $site->nom_site }}</td>
                                    <td>{{ $site->client_assets_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
<script>
var sitesByClient = @json($sitesByClient);

$('#clientSelect').on('change', function () {
    var clientId = $(this).val();
    var sites = sitesByClient[clientId] || [];
    var $siteSelect = $('#siteSelect');
    $siteSelect.empty().append('<option value="">-- Sélectionner --</option>');
    sites.forEach(function (site) {
        $siteSelect.append('<option value="' + site.id + '">' + site.nom_site + '</option>');
    });
});
</script>
@endpush

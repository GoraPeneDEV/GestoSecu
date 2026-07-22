@extends('layouts.app')

@section('title', 'Contrats SAV')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Contrats SAV</h3>
        <a href="{{ route('sav.contrats.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouveau contrat
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <select id="filterClient" class="form-select form-select-sm">
                        <option value="">Tous les clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->nomClient }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterType" class="form-select form-select-sm">
                        <option value="">Tous les types</option>
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterStatut" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="actif">Actif</option>
                        <option value="suspendu">Suspendu</option>
                        <option value="resilie">Résilié</option>
                        <option value="expire">Expiré</option>
                        <option value="renouvele">Renouvelé</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="contratsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Période</th>
                            <th>Jours restants</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#contratsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('sav.contrats.index') }}',
            data: function (d) {
                d.client_id = $('#filterClient').val();
                d.type = $('#filterType').val();
                d.statut = $('#filterStatut').val();
            }
        },
        columns: [
            { data: 'client_nom', name: 'client.nomClient', orderable: false },
            { data: 'type_label', name: 'type', orderable: false },
            { data: 'periode', name: 'periode', orderable: false },
            { data: 'jours_restant', name: 'jours_restant', orderable: false },
            { data: 'statut_badge', name: 'statut', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterClient, #filterType, #filterStatut').on('change', function () { table.ajax.reload(); });
});
</script>
@endpush

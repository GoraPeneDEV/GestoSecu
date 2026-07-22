@extends('layouts.app')

@section('title', 'Fiches de progrès')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Fiches de progrès</h3>
        <a href="{{ route('sav.fiches-progres.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvelle fiche
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
                    <select id="filterProcessus" class="form-select form-select-sm">
                        <option value="">Tous les processus</option>
                        @foreach ($processus as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterStatut" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="nouveau">Nouveau</option>
                        <option value="analyse_en_cours">Analyse en cours</option>
                        <option value="plan_action_etabli">Plan d'action établi</option>
                        <option value="actions_en_cours">Actions en cours</option>
                        <option value="cloture">Clôturé</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="fichesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Processus</th>
                            <th>Avancement</th>
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
    var table = $('#fichesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('sav.fiches-progres.index') }}',
            data: function (d) {
                d.client_id = $('#filterClient').val();
                d.type = $('#filterType').val();
                d.processus = $('#filterProcessus').val();
                d.statut = $('#filterStatut').val();
            }
        },
        columns: [
            { data: 'client_nom', name: 'client.nomClient', orderable: false },
            { data: 'type_label', name: 'type', orderable: false },
            { data: 'processus_label', name: 'processus_concerne', orderable: false },
            { data: 'avancement', name: 'avancement', orderable: false, searchable: false },
            { data: 'statut_badge', name: 'statut', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterClient, #filterType, #filterProcessus, #filterStatut').on('change', function () { table.ajax.reload(); });
});
</script>
@endpush

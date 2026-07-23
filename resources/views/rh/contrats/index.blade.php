@extends('layouts.contentNavbarLayout')

@section('title', 'Contrats')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Contrats</h3>
        <a href="{{ route('contrats.create') }}" class="btn btn-primary">
            <i class="ti ti-plus-lg"></i> Nouveau contrat
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0">{{ $totalCDI }}</h3><small class="text-muted">CDI</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0">{{ $totalCDD }}</h3><small class="text-muted">CDD</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0">{{ $totalStage }}</h3><small class="text-muted">Stage</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0">{{ $totalPrestationService }}</h3><small class="text-muted">Prestation de service</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-auto">
                    <select id="filterType" class="form-select form-select-sm">
                        <option value="">Tous les types</option>
                        <option value="CDI">CDI</option>
                        <option value="CDD">CDD</option>
                        <option value="Stage">Stage</option>
                        <option value="Prestation de service">Prestation de service</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterDepartement" class="form-select form-select-sm">
                        <option value="">Tous les départements</option>
                        @foreach ($departements as $dep)
                            <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterStatut" class="form-select form-select-sm">
                        <option value="actifs">Contrats actifs</option>
                        <option value="echus">Contrats échus</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="contratsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Type</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Montant</th>
                            <th>Document</th>
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
            url: '{{ route('contrats.data') }}',
            data: function (d) {
                d.type_contrat = $('#filterType').val();
                d.departement = $('#filterDepartement').val();
                d.statut = $('#filterStatut').val();
            }
        },
        columns: [
            { data: 'employe_info', name: 'employe.prenom', orderable: false },
            { data: 'type_contrat', name: 'type_contrat' },
            { data: 'date_debut', name: 'date_debut' },
            { data: 'date_fin', name: 'date_fin' },
            { data: 'montant_format', name: 'montant' },
            { data: 'document_link', name: 'document', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterType, #filterDepartement, #filterStatut').on('change', function () {
        table.ajax.reload();
    });

    $('#contratsTable').on('click', '.delete-contrat', function () {
        if (!confirm('Supprimer ce contrat ?')) return;
        var id = $(this).data('id');
        var employe = $(this).data('employe');
        $.ajax({
            url: '/contrats/' + employe + '/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () { table.ajax.reload(); },
            error: function () { alert('Erreur lors de la suppression'); }
        });
    });
});
</script>
@endpush

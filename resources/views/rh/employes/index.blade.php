@extends('layouts.contentNavbarLayout')

@section('title', 'Employés')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Employés</h3>
        <div>
            <a href="{{ route('employes.archived') }}" class="btn btn-outline-secondary">
                <i class="ti ti-archive"></i> Archivés
            </a>
            <a href="{{ route('employes.create') }}" class="btn btn-primary">
                <i class="ti ti-plus-lg"></i> Nouvel employé
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $totalEmployes }}</h3>
                    <small class="text-muted">Employés actifs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $cdiCount }}</h3>
                    <small class="text-muted">CDI</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $cddCount }}</h3>
                    <small class="text-muted">CDD</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stageCount }}</h3>
                    <small class="text-muted">Stage</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="employesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Naissance</th>
                            <th>Fonction</th>
                            <th>Département</th>
                            <th>Contrat</th>
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
    $('#employesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('employes.data') }}',
        columns: [
            { data: 'prenom', name: 'prenom' },
            { data: 'naissance_info', name: 'date_naissance', orderable: false },
            { data: 'fonction', name: 'fonction' },
            { data: 'departement.nom', name: 'departement.nom' },
            { data: 'type_contrat', name: 'type_contrat', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#employesTable').on('click', '.btn-delete-employe', function () {
        if (!confirm('Archiver cet employé ?')) return;
        var id = $(this).data('id');
        var dateArret = prompt('Date d\'arrêt (jj/mm/aaaa) :');
        if (!dateArret) return;
        var motif = prompt('Motif (Démission, Licenciement, Fin de contrat, Retraite, Décès, Autre) :', 'Démission');
        if (!motif) return;
        $.ajax({
            url: '/employes/' + id,
            type: 'DELETE',
            data: { date_arret: dateArret, motif_arret: motif, _token: '{{ csrf_token() }}' },
            success: function () { $('#employesTable').DataTable().ajax.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.message || 'Erreur'); }
        });
    });
});
</script>
@endpush

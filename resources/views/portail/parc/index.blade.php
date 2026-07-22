@extends('portail.layouts.app')

@section('title', 'Mon parc')

@section('content')
    <h3 class="mb-4">Mon parc d'équipements</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['totalEquipements'] }}</h3>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0 text-success">{{ $stats['equipementsActifs'] }}</h3>
                    <small class="text-muted">Fonctionnels</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0 text-warning">{{ $stats['equipementsMaintenance'] }}</h3>
                    <small class="text-muted">En maintenance</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0 text-danger">{{ $stats['equipementsHS'] }}</h3>
                    <small class="text-muted">Hors service</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="parcTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Équipement</th>
                            <th>Type</th>
                            <th>Site</th>
                            <th>Installation</th>
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
    $('#parcTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('portail.parc.getAssets') }}',
        columns: [
            { data: 'label', name: 'label' },
            { data: 'type', name: 'type' },
            { data: 'site_nom', name: 'site.nom_site', orderable: false },
            { data: 'installation_date', name: 'installation_date' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });
});
</script>
@endpush

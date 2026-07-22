@extends('layouts.app')

@section('title', 'Immobilisations — Biens')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Biens immobilisés</h3>
        <div>
            <a href="{{ route('immobilisations.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
            <a href="{{ route('immobilisations.biens.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nouveau bien
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total'] }}</h4><small class="text-muted">Total</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['en_stock'] }}</h4><small class="text-muted">En stock</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['affectes'] }}</h4><small class="text-muted">Affectés</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['en_reparation'] }}</h4><small class="text-muted">En réparation</small>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <select id="filterStatut" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="en_stock">En stock</option>
                        <option value="affecte">Affecté</option>
                        <option value="en_reparation">En réparation</option>
                        <option value="en_transit">En transit</option>
                        <option value="cede">Cédé</option>
                        <option value="reforme">Réformé</option>
                        <option value="perdu">Perdu</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterSite" class="form-select form-select-sm">
                        <option value="">Tous les sites</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterCategorie" class="form-select form-select-sm">
                        <option value="">Toutes les catégories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->libelle }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="biensTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Désignation</th>
                            <th>Catégorie</th>
                            <th>Site</th>
                            <th>Détenteur</th>
                            <th>Valeur</th>
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
    var table = $('#biensTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('immobilisations.biens.data') }}',
            data: function (d) {
                d.statut = $('#filterStatut').val();
                d.site_id = $('#filterSite').val();
                d.categorie_id = $('#filterCategorie').val();
            }
        },
        columns: [
            { data: 'code_interne', name: 'code_interne' },
            { data: 'designation', name: 'designation' },
            { data: 'categorie_libelle', name: 'categorie.libelle', orderable: false },
            { data: 'site_libelle', name: 'site.libelle', orderable: false },
            { data: 'detenteur', name: 'detenteur', orderable: false },
            { data: 'valeur_formattee', name: 'valeur_acquisition' },
            { data: 'statut_badge', name: 'statut', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterStatut, #filterSite, #filterCategorie').on('change', function () { table.ajax.reload(); });
});
</script>
@endpush

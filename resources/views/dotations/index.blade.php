@extends('layouts.app')

@section('title', 'Dotations')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Dotations</h3>
        <a href="{{ route('dotations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvelle dotation
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['totalDotations'] }}</h4><small class="text-muted">Dotations</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['dotationsSites'] }}</h4><small class="text-muted">Vers des sites</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['dotationsEmployes'] }}</h4><small class="text-muted">Vers des employés</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['articlesDistribues'] }}</h4><small class="text-muted">Articles distribués</small>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <select id="filterType" class="form-select form-select-sm">
                        <option value="">Tous les types</option>
                        <option value="INITIALE">Initiale</option>
                        <option value="RENOUVELLEMENT">Renouvellement</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterSite" class="form-select form-select-sm">
                        <option value="">Tous les sites</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->nom_site }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterEmploye" class="form-select form-select-sm">
                        <option value="">Tous les employés</option>
                        @foreach ($employes as $employe)
                            <option value="{{ $employe->id }}">{{ $employe->prenom }} {{ $employe->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterAnnee" class="form-select form-select-sm">
                        @foreach ($annees as $annee)
                            <option value="{{ $annee }}" @selected($annee == $anneeActuelle)>{{ $annee }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="dotationsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Bénéficiaire</th>
                            <th>Articles</th>
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
    var table = $('#dotationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('dotations.data') }}',
            data: function (d) {
                d.type = $('#filterType').val();
                d.site_id = $('#filterSite').val();
                d.employe_id = $('#filterEmploye').val();
                d.annee = $('#filterAnnee').val();
            }
        },
        columns: [
            { data: 'reference', name: 'reference' },
            { data: 'date_dotation', name: 'date_dotation' },
            { data: 'type_dotation', name: 'type_dotation' },
            { data: 'beneficiaire', name: 'beneficiaire', orderable: false },
            { data: 'articles_count', name: 'articles_count', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterType, #filterSite, #filterEmploye, #filterAnnee').on('change', function () { table.ajax.reload(); });

    $('#dotationsTable').on('click', '.delete-dotation', function () {
        if (!confirm('Supprimer cette dotation ?')) return;
        const id = $(this).data('id');
        fetch('{{ url('dotations') }}/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) { table.ajax.reload(); } else { alert(res.message); }
            });
    });
});
</script>
@endpush

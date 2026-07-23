@extends('layouts.contentNavbarLayout')

@section('title', 'Rondes')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Rondes</h3>
        <a href="{{ route('sie.rondes.create') }}" class="btn btn-primary">
            <i class="ti ti-plus-lg"></i> Nouvelle ronde
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['totalRondes'] }}</h4><small class="text-muted">Total</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['enCoursCount'] }}</h4><small class="text-muted">En cours</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['termineesCount'] }}</h4><small class="text-muted">Terminées</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['anomaliesCount'] }}</h4><small class="text-muted">Anomalies</small>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <select id="filterSite" class="form-select form-select-sm">
                        <option value="">Tous les sites</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->nom_site }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterStatut" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="en_cours">En cours</option>
                        <option value="terminee">Terminée</option>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="text" id="filterDate" class="form-control form-control-sm" placeholder="jj/mm/aaaa">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="rondesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Site</th>
                            <th>Début</th>
                            <th>Progression</th>
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
    var table = $('#rondesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('sie.rondes.data') }}',
            data: function (d) {
                d.site_id = $('#filterSite').val();
                d.statut = $('#filterStatut').val();
                d.date = $('#filterDate').val();
            }
        },
        columns: [
            { data: 'agent', name: 'agent', orderable: false },
            { data: 'site', name: 'site', orderable: false },
            { data: 'date_debut', name: 'date_debut' },
            { data: 'progression', name: 'progression', orderable: false, searchable: false },
            { data: 'statut', name: 'statut' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterSite, #filterStatut').on('change', function () { table.ajax.reload(); });
    $('#filterDate').on('change', function () { table.ajax.reload(); });

    $('#rondesTable').on('click', '.btn-terminer-ronde', function () {
        if (!confirm('Terminer cette ronde ?')) return;
        const id = $(this).data('id');
        fetch('/sie/rondes/' + id + '/terminer', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({}),
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) { table.ajax.reload(); } else { alert(res.message); }
            });
    });
});
</script>
@endpush

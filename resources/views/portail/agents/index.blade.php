@extends('portail.layouts.app')

@section('title', 'Agents')

@section('content')
    <h3 class="mb-4">Agents affectés à mes sites</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['totalAgents'] }}</h3>
                    <small class="text-muted">Agents actifs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['sitesCouverts'] }}</h3>
                    <small class="text-muted">Sites couverts</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="agentsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Département</th>
                            <th>Téléphone</th>
                            <th>Sites</th>
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
    $('#agentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('portail.agents.getAgents') }}',
        columns: [
            { data: 'agent_info', name: 'employe.prenom', orderable: false },
            { data: 'departement_nom', name: 'departements.nom' },
            { data: 'telephone', name: 'employe.telephone', orderable: false },
            { data: 'sites_assignes', name: 'sites_assignes', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });
});
</script>
@endpush

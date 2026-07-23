@extends('layouts.contentNavbarLayout')

@section('title', 'Maintenances')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Maintenances préventives</h3>
        <div>
            <a href="{{ route('sav.maintenances.export-pdf') }}" class="btn btn-outline-secondary">
                <i class="ti ti-file-pdf"></i> Export PDF
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNew">
                <i class="ti ti-plus-lg"></i> Planifier une maintenance
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($prochaines->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">Prochaines maintenances (5)</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach ($prochaines as $p)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $p->site->nom_site ?? '-' }}</span>
                            <span>{{ \Carbon\Carbon::parse($p->date_prevue)->format('d/m/Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Site</th>
                            <th>Contrat</th>
                            <th>Date prévue</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($maintenances as $m)
                            <tr>
                                <td>{{ $m->contrat->client->nomClient ?? '-' }}</td>
                                <td>{{ $m->site->nom_site ?? '-' }}</td>
                                <td>{{ $m->contrat->numero_contrat ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($m->date_prevue)->format('d/m/Y') }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $m->status)) }}</span></td>
                                <td>
                                    <a href="{{ route('sav.maintenances.show', $m->id) }}" class="btn btn-sm btn-info"><i class="ti ti-eye"></i></a>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editMaintenance({{ $m->id }}, '{{ $m->date_prevue }}', '{{ $m->status }}', '{{ addslashes($m->description) }}')"><i class="ti ti-pencil"></i></button>
                                    <a href="{{ route('sav.interventions.create', ['maintenance_id' => $m->id]) }}" class="btn btn-sm btn-success"><i class="ti ti-clipboard-check"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucune maintenance.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNew" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('sav.maintenances.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Planifier une maintenance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Contrat *</label>
                            <select name="contrat_id" id="contratSelect" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach ($contratsActifs as $contrat)
                                    <option value="{{ $contrat->id }}">{{ $contrat->numero_contrat }} — {{ $contrat->client->nomClient ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Site *</label>
                            <select name="site_id" id="siteSelect" class="form-select" required>
                                <option value="">-- Sélectionner un contrat d'abord --</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Date prévue *</label>
                            <input type="date" name="date_prevue" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Planifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier la maintenance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Date prévue *</label>
                            <input type="date" name="date_prevue" id="edit_date_prevue" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Statut *</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="planifiee">Planifiée</option>
                                <option value="en_cours">En cours</option>
                                <option value="realisee">Réalisée</option>
                                <option value="annulee">Annulée</option>
                                <option value="reportee">Reportée</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const sitesByClient = @json($sites->groupBy('client_id'));
const contratsClient = @json($contratsActifs->pluck('client_id', 'id'));

document.getElementById('contratSelect').addEventListener('change', function () {
    const clientId = contratsClient[this.value];
    const siteSelect = document.getElementById('siteSelect');
    siteSelect.innerHTML = '<option value="">-- Sélectionner --</option>';
    const sites = sitesByClient[clientId] || [];
    sites.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.nom_site;
        siteSelect.appendChild(opt);
    });
});

function editMaintenance(id, datePrevue, status, description) {
    document.getElementById('editForm').action = '{{ url('sav/maintenances') }}/' + id;
    document.getElementById('edit_date_prevue').value = datePrevue.substring(0, 10);
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_description').value = description;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
@endpush

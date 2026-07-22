@extends('layouts.app')

@section('title', 'Supervision — Visites de sites')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Supervision — Visites de sites</h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalVisite" onclick="newVisite()">
            <i class="bi bi-plus-lg"></i> Nouvelle visite
        </button>
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
                    <select id="filterSuperviseur" class="form-select form-select-sm">
                        <option value="">Tous les superviseurs</option>
                        @foreach ($superviseurs as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->prenom }} {{ $sup->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="RAS">RAS</option>
                        <option value="Normal">Normal</option>
                        <option value="Alerte">Alerte</option>
                        <option value="Incident">Incident</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="visitesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Site</th>
                            <th>Superviseur</th>
                            <th>Mode</th>
                            <th>Agents</th>
                            <th>Statut</th>
                            <th>Photo</th>
                            <th>Vidéo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalVisite" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="visiteForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalVisiteTitle">Nouvelle visite</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="visite_id">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Site *</label>
                                <select id="site_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->nom_site }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Superviseur *</label>
                                <select id="user_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach ($superviseurs as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->prenom }} {{ $sup->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Statut *</label>
                                <select id="status" class="form-select" required>
                                    <option value="RAS">RAS</option>
                                    <option value="Normal">Normal</option>
                                    <option value="Alerte">Alerte</option>
                                    <option value="Incident">Incident</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Agents attendus *</label>
                                <input type="number" id="expected_agents_count" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Agents présents *</label>
                                <input type="number" id="actual_agents_count" class="form-control" min="0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Agents manquants</label>
                                <select id="missing_agents" class="form-select" multiple></select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Détails agents manquants</label>
                                <input type="text" id="missing_agents_details" class="form-control">
                            </div>
                        </div>

                        <h6 class="mt-3">Points de contrôle</h6>
                        <div class="row g-2">
                            @foreach ([
                                'check_agent_presence' => 'Présence des agents',
                                'check_respect_planning' => 'Respect du planning',
                                'check_strict_consignes' => 'Respect strict des consignes',
                                'check_port_vestimentaire' => 'Port vestimentaire',
                                'check_proprete' => 'Propreté du site',
                                'check_talk_box' => 'Talk-box fonctionnel',
                                'check_registre_garde' => 'Registre de garde tenu',
                                'ras' => 'RAS global',
                            ] as $field => $label)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input check-field" id="{{ $field }}" checked>
                                        <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea id="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Photo</label>
                                <input type="file" id="photo" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vidéo</label>
                                <input type="file" id="video" class="form-control" accept="video/*">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de la visite</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailBody"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const checkFields = ['check_agent_presence', 'check_respect_planning', 'check_strict_consignes', 'check_port_vestimentaire', 'check_proprete', 'check_talk_box', 'check_registre_garde', 'ras'];

function newVisite() {
    document.getElementById('visiteForm').reset();
    document.getElementById('visite_id').value = '';
    document.getElementById('missing_agents').innerHTML = '';
    checkFields.forEach(f => document.getElementById(f).checked = true);
    document.getElementById('modalVisiteTitle').textContent = 'Nouvelle visite';
}

function loadAgents(siteId, selected = []) {
    const select = document.getElementById('missing_agents');
    select.innerHTML = '';
    if (!siteId) return;
    fetch('{{ url('supervision/visites/site-agents') }}/' + siteId)
        .then(r => r.json())
        .then(res => {
            res.agents.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = a.nom_complet;
                if (selected.includes(a.id)) opt.selected = true;
                select.appendChild(opt);
            });
        });
}

document.getElementById('site_id').addEventListener('change', function () { loadAgents(this.value); });

function editVisite(id) {
    fetch('{{ url('supervision/visites') }}/' + id + '/edit')
        .then(r => r.json())
        .then(v => {
            document.getElementById('visite_id').value = v.id;
            document.getElementById('site_id').value = v.site_id;
            document.getElementById('user_id').value = v.user_id;
            document.getElementById('status').value = v.status;
            document.getElementById('expected_agents_count').value = v.expected_agents_count;
            document.getElementById('actual_agents_count').value = v.actual_agents_count;
            document.getElementById('missing_agents_details').value = v.missing_agents_details || '';
            document.getElementById('notes').value = v.notes || '';
            checkFields.forEach(f => document.getElementById(f).checked = !!v[f]);
            loadAgents(v.site_id, v.missing_agents || []);
            document.getElementById('modalVisiteTitle').textContent = 'Modifier la visite';
            new bootstrap.Modal(document.getElementById('modalVisite')).show();
        });
}

function deleteVisite(id) {
    if (!confirm('Supprimer cette visite ?')) return;
    fetch('{{ url('supervision/visites') }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) { $('#visitesTable').DataTable().ajax.reload(); } else { alert(res.message); }
        });
}

function showDetail(visit, missing) {
    let html = `<dl class="row mb-0">
        <dt class="col-5">Site</dt><dd class="col-7">${visit.site ? visit.site.nom_site : '-'}</dd>
        <dt class="col-5">Superviseur</dt><dd class="col-7">${visit.supervisor ? visit.supervisor.prenom + ' ' + visit.supervisor.nom : '-'}</dd>
        <dt class="col-5">Statut</dt><dd class="col-7">${visit.status}</dd>
        <dt class="col-5">Agents</dt><dd class="col-7">${visit.actual_agents_count}/${visit.expected_agents_count}</dd>
        <dt class="col-5">Manquants</dt><dd class="col-7">${missing.map(m => m.nom_complet).join(', ') || '-'}</dd>
        <dt class="col-5">Notes</dt><dd class="col-7">${visit.notes || '-'}</dd>
    </dl>`;
    document.getElementById('detailBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('modalDetail')).show();
}

$(function () {
    var table = $('#visitesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('supervision.visites.data') }}',
            data: function (d) {
                d.site_id = $('#filterSite').val();
                d.supervisor_id = $('#filterSuperviseur').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'created_at', name: 'created_at' },
            { data: 'site_nom', name: 'site.nom_site', orderable: false },
            { data: 'superviseur', name: 'supervisor.nom', orderable: false },
            { data: 'scan_mode', name: 'scan_mode', orderable: false },
            { data: null, orderable: false, searchable: false, render: (d) => d.actual_agents_count + '/' + d.expected_agents_count },
            { data: 'status', name: 'status', orderable: false },
            { data: 'photo', name: 'photo', orderable: false, searchable: false },
            { data: 'video', name: 'video', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterSite, #filterSuperviseur, #filterStatus').on('change', function () { table.ajax.reload(); });

    $('#visitesTable').on('click', '.btn-view-details', function () {
        showDetail(JSON.parse($(this).attr('data-visit')), JSON.parse($(this).attr('data-missing')));
    });
    $('#visitesTable').on('click', '.btn-edit-visit', function () { editVisite($(this).data('id')); });
    $('#visitesTable').on('click', '.btn-delete-visit', function () { deleteVisite($(this).data('id')); });

    document.getElementById('visiteForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('visite_id').value;
        const formData = new FormData();
        formData.append('site_id', document.getElementById('site_id').value);
        formData.append('user_id', document.getElementById('user_id').value);
        formData.append('status', document.getElementById('status').value);
        formData.append('expected_agents_count', document.getElementById('expected_agents_count').value);
        formData.append('actual_agents_count', document.getElementById('actual_agents_count').value);
        formData.append('missing_agents_details', document.getElementById('missing_agents_details').value);
        formData.append('notes', document.getElementById('notes').value);
        Array.from(document.getElementById('missing_agents').selectedOptions).forEach(o => formData.append('missing_agents[]', o.value));
        checkFields.forEach(f => formData.append(f, document.getElementById(f).checked ? 1 : 0));
        const photo = document.getElementById('photo').files[0];
        if (photo) formData.append('photo', photo);
        const video = document.getElementById('video').files[0];
        if (video) formData.append('video', video);

        const url = id ? '{{ url('supervision/visites') }}/' + id : '{{ route('supervision.visites.store') }}';
        if (id) formData.append('_method', 'PUT');

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData,
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalVisite')).hide();
                    table.ajax.reload();
                } else {
                    alert(res.message || 'Erreur');
                }
            });
    });
});
</script>
@endpush

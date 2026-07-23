@extends('layouts.contentNavbarLayout')

@section('title', "Sites d'immobilisation")

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Sites d'immobilisation</h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSite" onclick="newSite()">
            <i class="ti ti-plus-lg"></i> Nouveau site
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="sitesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Libellé</th>
                            <th>Type</th>
                            <th>Biens</th>
                            <th>Valeur</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSite" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="siteForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSiteTitle">Nouveau site</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="site_id">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Code *</label>
                                <input type="text" id="code_site" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Libellé *</label>
                                <input type="text" id="libelle" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type *</label>
                                <select id="type" class="form-select" required>
                                    <option value="siege">Siège</option>
                                    <option value="annexe">Annexe</option>
                                    <option value="depot">Dépôt</option>
                                    <option value="agence">Agence</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Adresse</label>
                                <textarea id="adresse" class="form-control" rows="2"></textarea>
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
@endsection

@push('scripts')
<script>
function newSite() {
    document.getElementById('siteForm').reset();
    document.getElementById('site_id').value = '';
    document.getElementById('modalSiteTitle').textContent = 'Nouveau site';
}

function editSite(id) {
    fetch('{{ url('immobilisations/sites') }}/' + id + '/edit')
        .then(r => r.json())
        .then(s => {
            document.getElementById('site_id').value = s.id;
            document.getElementById('code_site').value = s.code_site;
            document.getElementById('libelle').value = s.libelle;
            document.getElementById('type').value = s.type;
            document.getElementById('adresse').value = s.adresse || '';
            document.getElementById('modalSiteTitle').textContent = 'Modifier le site';
            new bootstrap.Modal(document.getElementById('modalSite')).show();
        });
}

function toggleSiteStatus(id) {
    fetch('{{ url('immobilisations/sites') }}/' + id + '/toggle', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) { $('#sitesTable').DataTable().ajax.reload(); } else { alert(res.message); }
        });
}

$(function () {
    var table = $('#sitesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('immobilisations.sites.data') }}',
        columns: [
            { data: 'code_site', name: 'code_site' },
            { data: 'libelle', name: 'libelle' },
            { data: 'type_libelle', name: 'type', orderable: false },
            { data: 'nb_biens', name: 'nb_biens', orderable: false, searchable: false },
            { data: 'valeur_totale', name: 'valeur_totale', orderable: false, searchable: false },
            { data: 'statut_badge', name: 'statut', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    document.getElementById('siteForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('site_id').value;
        const payload = {
            code_site: document.getElementById('code_site').value,
            libelle: document.getElementById('libelle').value,
            type: document.getElementById('type').value,
            adresse: document.getElementById('adresse').value,
        };
        const url = id ? '{{ url('immobilisations/sites') }}/' + id + '/update' : '{{ route('immobilisations.sites.store') }}';
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload),
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalSite')).hide();
                    table.ajax.reload();
                } else {
                    alert(res.message || 'Erreur');
                }
            });
    });
});
</script>
@endpush

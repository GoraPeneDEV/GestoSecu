@extends('layouts.contentNavbarLayout')

@section('title', "Catégories d'immobilisations")

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Catégories d'immobilisations</h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCategorie" onclick="newCategorie()">
            <i class="ti ti-plus-lg"></i> Nouvelle catégorie
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="categoriesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Libellé</th>
                            <th>Type</th>
                            <th>Dotable</th>
                            <th>Amortissable</th>
                            <th>Biens</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCategorie" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="categorieForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCategorieTitle">Nouvelle catégorie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="categorie_id">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Code *</label>
                                <input type="text" id="code" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Libellé *</label>
                                <input type="text" id="libelle" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea id="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type de bien *</label>
                                <select id="type_bien" class="form-select" required>
                                    <option value="corporel">Corporel</option>
                                    <option value="incorporel">Incorporel</option>
                                    <option value="financier">Financier</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Méthode d'amortissement *</label>
                                <select id="methode_amortissement_defaut" class="form-select" required>
                                    <option value="lineaire">Linéaire</option>
                                    <option value="degressif">Dégressif</option>
                                    <option value="variable">Variable</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Durée d'amortissement (années)</label>
                                <input type="number" id="duree_amortissement_defaut" class="form-control" min="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Taux d'amortissement (%)</label>
                                <input type="number" id="taux_amortissement_defaut" class="form-control" min="0" max="100" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" id="est_dotable" class="form-check-input">
                                    <label class="form-check-label" for="est_dotable">Dotable</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" id="est_amortissable" class="form-check-input">
                                    <label class="form-check-label" for="est_amortissable">Amortissable</label>
                                </div>
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
function newCategorie() {
    document.getElementById('categorieForm').reset();
    document.getElementById('categorie_id').value = '';
    document.getElementById('modalCategorieTitle').textContent = 'Nouvelle catégorie';
}

function editCategorie(id) {
    fetch('{{ url('immobilisations/categories') }}/' + id + '/edit')
        .then(r => r.json())
        .then(c => {
            document.getElementById('categorie_id').value = c.id;
            document.getElementById('code').value = c.code;
            document.getElementById('libelle').value = c.libelle;
            document.getElementById('description').value = c.description || '';
            document.getElementById('type_bien').value = c.type_bien;
            document.getElementById('methode_amortissement_defaut').value = c.methode_amortissement_defaut;
            document.getElementById('duree_amortissement_defaut').value = c.duree_amortissement_defaut || '';
            document.getElementById('taux_amortissement_defaut').value = c.taux_amortissement_defaut || '';
            document.getElementById('est_dotable').checked = !!c.est_dotable;
            document.getElementById('est_amortissable').checked = !!c.est_amortissable;
            document.getElementById('modalCategorieTitle').textContent = 'Modifier la catégorie';
            new bootstrap.Modal(document.getElementById('modalCategorie')).show();
        });
}

function deleteCategorie(id) {
    if (!confirm('Supprimer cette catégorie ?')) return;
    fetch('{{ url('immobilisations/categories') }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) { $('#categoriesTable').DataTable().ajax.reload(); } else { alert(res.message); }
        });
}

$(function () {
    var table = $('#categoriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('immobilisations.categories.data') }}',
        columns: [
            { data: 'code', name: 'code' },
            { data: 'libelle', name: 'libelle' },
            { data: 'type_bien', name: 'type_bien' },
            { data: 'est_dotable_badge', name: 'est_dotable', orderable: false, searchable: false },
            { data: 'est_amortissable_badge', name: 'est_amortissable', orderable: false, searchable: false },
            { data: 'nb_biens', name: 'nb_biens', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    document.getElementById('categorieForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('categorie_id').value;
        const payload = {
            code: document.getElementById('code').value,
            libelle: document.getElementById('libelle').value,
            description: document.getElementById('description').value,
            type_bien: document.getElementById('type_bien').value,
            methode_amortissement_defaut: document.getElementById('methode_amortissement_defaut').value,
            duree_amortissement_defaut: document.getElementById('duree_amortissement_defaut').value || null,
            taux_amortissement_defaut: document.getElementById('taux_amortissement_defaut').value || null,
            est_dotable: document.getElementById('est_dotable').checked ? 1 : 0,
            est_amortissable: document.getElementById('est_amortissable').checked ? 1 : 0,
        };
        const url = id ? '{{ url('immobilisations/categories') }}/' + id + '/update' : '{{ route('immobilisations.categories.store') }}';
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload),
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalCategorie')).hide();
                    table.ajax.reload();
                } else {
                    alert(res.message || 'Erreur');
                }
            });
    });
});
</script>
@endpush

@extends('layouts.app')

@section('title', 'Articles')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Articles</h3>
        <div>
            <a href="{{ route('articles.inventaire') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clipboard-data"></i> Rapport d'inventaire
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalArticle" onclick="newArticle()">
                <i class="bi bi-plus-lg"></i> Nouvel article
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['totalArticles'] }}</h4><small class="text-muted">Articles</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['articlesSousStock'] }}</h4><small class="text-muted">Sous le stock minimum</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ number_format($stats['valeurTotale'], 0, ',', ' ') }} FCFA</h4><small class="text-muted">Valeur totale du stock</small>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <select id="filterDepartement" class="form-select form-select-sm">
                        <option value="">Tous les départements</option>
                        @foreach ($departements as $dep)
                            <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterStock" class="form-select form-select-sm">
                        <option value="">Tous les stocks</option>
                        <option value="in_stock">En stock</option>
                        <option value="under_min">Sous minimum</option>
                        <option value="out_of_stock">Rupture</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="articlesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Désignation</th>
                            <th>Département</th>
                            <th>Stock actuel</th>
                            <th>Prix unitaire</th>
                            <th>Valeur stock</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalArticle" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="articleForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalArticleTitle">Nouvel article</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="article_id">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Référence *</label>
                                <input type="text" id="reference" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Désignation *</label>
                                <input type="text" id="designation" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea id="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unité *</label>
                                <input type="text" id="unite" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Département *</label>
                                <select id="departement_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach ($departements as $dep)
                                        <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock actuel *</label>
                                <input type="number" id="stock_actuel" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock minimum *</label>
                                <input type="number" id="stock_minimum" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Prix unitaire *</label>
                                <input type="number" id="prix_unitaire" class="form-control" min="0" step="0.01" required>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" id="est_immobilisable" class="form-check-input" onchange="document.getElementById('categorieWrapper').classList.toggle('d-none', !this.checked)">
                                    <label class="form-check-label" for="est_immobilisable">Article immobilisable</label>
                                </div>
                            </div>
                            <div class="col-12 d-none" id="categorieWrapper">
                                <label class="form-label">Catégorie d'immobilisation</label>
                                <select id="immobilisation_categorie_id" class="form-select">
                                    <option value="">-- Aucune --</option>
                                    @foreach ($immobilisationCategories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
                                    @endforeach
                                </select>
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
function newArticle() {
    document.getElementById('articleForm').reset();
    document.getElementById('article_id').value = '';
    document.getElementById('modalArticleTitle').textContent = 'Nouvel article';
    document.getElementById('categorieWrapper').classList.add('d-none');
}

function editArticle(id) {
    fetch('{{ url('articles') }}/' + id + '/edit')
        .then(r => r.json())
        .then(a => {
            document.getElementById('article_id').value = a.id;
            document.getElementById('reference').value = a.reference;
            document.getElementById('designation').value = a.designation;
            document.getElementById('description').value = a.description || '';
            document.getElementById('unite').value = a.unite;
            document.getElementById('departement_id').value = a.departement_id;
            document.getElementById('stock_actuel').value = a.stock_actuel;
            document.getElementById('stock_minimum').value = a.stock_minimum;
            document.getElementById('prix_unitaire').value = a.prix_unitaire;
            document.getElementById('est_immobilisable').checked = !!a.est_immobilisable;
            document.getElementById('immobilisation_categorie_id').value = a.immobilisation_categorie_id || '';
            document.getElementById('categorieWrapper').classList.toggle('d-none', !a.est_immobilisable);
            document.getElementById('modalArticleTitle').textContent = 'Modifier l\'article';
            new bootstrap.Modal(document.getElementById('modalArticle')).show();
        });
}

$(function () {
    var table = $('#articlesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('articles.getArticles') }}',
            data: function (d) {
                d.departement_id = $('#filterDepartement').val();
                d.stock_status = $('#filterStock').val();
            }
        },
        columns: [
            { data: 'reference', name: 'reference' },
            { data: 'designation', name: 'designation' },
            { data: 'departement.nom', name: 'departement.nom' },
            { data: 'stock_actuel', name: 'stock_actuel' },
            { data: 'prix_unitaire', name: 'prix_unitaire' },
            { data: 'valeur_stock', name: 'valeur_stock', orderable: false, searchable: false },
            { data: 'stock_status', name: 'stock_status', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterDepartement, #filterStock').on('change', function () { table.ajax.reload(); });

    document.getElementById('articleForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('article_id').value;
        const payload = {
            reference: document.getElementById('reference').value,
            designation: document.getElementById('designation').value,
            description: document.getElementById('description').value,
            unite: document.getElementById('unite').value,
            departement_id: document.getElementById('departement_id').value,
            stock_actuel: document.getElementById('stock_actuel').value,
            stock_minimum: document.getElementById('stock_minimum').value,
            prix_unitaire: document.getElementById('prix_unitaire').value,
            est_immobilisable: document.getElementById('est_immobilisable').checked ? 1 : 0,
            immobilisation_categorie_id: document.getElementById('immobilisation_categorie_id').value || null,
        };
        const url = id ? '{{ url('articles') }}/' + id : '{{ route('articles.store') }}';
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': id ? 'PUT' : 'POST',
            },
            body: JSON.stringify(payload),
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalArticle')).hide();
                    table.ajax.reload();
                } else {
                    alert(res.message || 'Erreur');
                }
            });
    });
});
</script>
@endpush

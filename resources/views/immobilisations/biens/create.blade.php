@extends('layouts.app')

@section('title', 'Nouveau bien')

@section('content')
    <h3 class="mb-4">Nouveau bien immobilisé</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="max-width: 900px;">
        <div class="card-body">
            <form method="POST" action="{{ route('immobilisations.biens.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Catégorie *</label>
                        <select name="categorie_id" id="categorie_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('categorie_id') == $cat->id)>{{ $cat->libelle }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Prochain code : <span id="codePreview">-</span></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Désignation *</label>
                        <input type="text" name="designation" class="form-control" value="{{ old('designation') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Numéro de série</label>
                        <input type="text" name="numero_serie" class="form-control" value="{{ old('numero_serie') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Numéro de facture</label>
                        <input type="text" name="numero_facture" class="form-control" value="{{ old('numero_facture') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site *</label>
                        <select name="site_id" id="site_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}" @selected(old('site_id') == $site->id)>{{ $site->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emplacement</label>
                        <select name="emplacement_id" id="emplacement_id" class="form-select">
                            <option value="">-- Sélectionner un site d'abord --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date d'acquisition *</label>
                        <input type="date" name="date_acquisition" class="form-control" value="{{ old('date_acquisition', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Valeur d'acquisition (FCFA) *</label>
                        <input type="number" name="valeur_acquisition" class="form-control" min="0" step="0.01" value="{{ old('valeur_acquisition') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Valeur résiduelle</label>
                        <input type="number" name="valeur_residuelle" class="form-control" min="0" step="0.01" value="{{ old('valeur_residuelle', 0) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Méthode d'amortissement *</label>
                        <select name="methode_amortissement" class="form-select" required>
                            <option value="lineaire">Linéaire</option>
                            <option value="degressif">Dégressif</option>
                            <option value="variable">Variable</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Durée d'amortissement (années) *</label>
                        <input type="number" name="duree_amortissement_annees" class="form-control" min="1" value="{{ old('duree_amortissement_annees', 3) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Taux d'amortissement (%)</label>
                        <input type="number" name="taux_amortissement" class="form-control" min="0" max="100" step="0.01" value="{{ old('taux_amortissement') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de début d'amortissement</label>
                        <input type="date" name="date_debut_amortissement" class="form-control" value="{{ old('date_debut_amortissement') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quantité *</label>
                        <input type="number" name="quantite" class="form-control" min="1" max="50" value="{{ old('quantite', 1) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Affecter à un employé (optionnel)</label>
                        <select name="employe_id" class="form-select">
                            <option value="">-- Aucun (en stock) --</option>
                            @foreach ($employes as $employe)
                                <option value="{{ $employe->id }}">{{ $employe->matricule }} — {{ $employe->prenom }} {{ $employe->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date d'affectation</label>
                        <input type="date" name="date_affectation" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Action après enregistrement</label>
                        <select name="action_apres" class="form-select">
                            <option value="liste">Retourner à la liste</option>
                            <option value="nouveau">Créer un autre bien</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                <a href="{{ route('immobilisations.biens.index') }}" class="btn btn-outline-secondary mt-3">Annuler</a>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('categorie_id').addEventListener('change', function () {
    if (!this.value) { document.getElementById('codePreview').textContent = '-'; return; }
    fetch('{{ route('immobilisations.biens.preview-code') }}?categorie_id=' + this.value)
        .then(r => r.json())
        .then(res => { document.getElementById('codePreview').textContent = res.code; });
});

document.getElementById('site_id').addEventListener('change', function () {
    const emplSelect = document.getElementById('emplacement_id');
    emplSelect.innerHTML = '<option value="">-- Aucun --</option>';
    if (!this.value) return;
    fetch('{{ url('immobilisations/sites') }}/' + this.value + '/emplacements')
        .then(r => r.json())
        .then(list => {
            list.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.id;
                opt.textContent = e.libelle;
                emplSelect.appendChild(opt);
            });
        });
});
</script>
@endpush

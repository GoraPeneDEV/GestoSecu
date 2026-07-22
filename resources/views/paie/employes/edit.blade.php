@extends('layouts.app')

@section('title', 'Données de paie - ' . $employe->prenom . ' ' . $employe->nom)

@section('content')
    <a href="{{ route('employes.show', $employe->id) }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour à l'employé
    </a>

    <h3 class="mb-4">Données de paie — {{ $employe->prenom }} {{ $employe->nom }}</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('paie.employes.update', $employe->id) }}">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header">Rémunération</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Salaire de base *</label>
                    <input type="number" name="salaire_base" class="form-control" value="{{ old('salaire_base', $paieData->salaire_base) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sursalaire</label>
                    <input type="number" name="sursalaire" class="form-control" value="{{ old('sursalaire', $paieData->sursalaire) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Catégorie professionnelle</label>
                    <select name="categorie_professionnelle" class="form-select">
                        <option value="Non-cadre" @selected(old('categorie_professionnelle', $paieData->categorie_professionnelle) == 'Non-cadre')>Non-cadre</option>
                        <option value="Cadre" @selected(old('categorie_professionnelle', $paieData->categorie_professionnelle) == 'Cadre')>Cadre</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Classification</label>
                    <input type="text" name="classification" class="form-control" value="{{ old('classification', $paieData->classification) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Échelon</label>
                    <input type="number" name="echelon" class="form-control" value="{{ old('echelon', $paieData->echelon) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Coefficient</label>
                    <input type="number" step="0.01" name="coefficient" class="form-control" value="{{ old('coefficient', $paieData->coefficient) }}">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Situation familiale (parts fiscales)</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre d'épouses *</label>
                    <input type="number" name="nombre_epouses" class="form-control" value="{{ old('nombre_epouses', $paieData->nombre_epouses ?? 0) }}" min="0" max="4" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nombre d'enfants à charge *</label>
                    <input type="number" name="nombre_enfants_a_charge" class="form-control" value="{{ old('nombre_enfants_a_charge', $paieData->nombre_enfants_a_charge ?? 0) }}" min="0" max="10" required>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Numéros d'affiliation</div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label class="form-label">N° IPRES</label>
                    <input type="text" name="numero_ipres" class="form-control" value="{{ old('numero_ipres', $paieData->numero_ipres) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° CSS</label>
                    <input type="text" name="numero_css" class="form-control" value="{{ old('numero_css', $paieData->numero_css) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° IPM</label>
                    <input type="text" name="numero_ipm" class="form-control" value="{{ old('numero_ipm', $paieData->numero_ipm) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° contribuable</label>
                    <input type="text" name="numero_contribuable" class="form-control" value="{{ old('numero_contribuable', $paieData->numero_contribuable) }}">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Coordonnées bancaires</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Banque</label>
                    <input type="text" name="banque_nom" class="form-control" value="{{ old('banque_nom', $paieData->banque_nom) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Code banque</label>
                    <input type="text" name="banque_code" class="form-control" value="{{ old('banque_code', $paieData->banque_code) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Code guichet</label>
                    <input type="text" name="banque_guichet" class="form-control" value="{{ old('banque_guichet', $paieData->banque_guichet) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° de compte</label>
                    <input type="text" name="numero_compte" class="form-control" value="{{ old('numero_compte', $paieData->numero_compte) }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Clé RIB</label>
                    <input type="text" name="cle_rib" class="form-control" value="{{ old('cle_rib', $paieData->cle_rib) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">IBAN</label>
                    <input type="text" name="iban" class="form-control" value="{{ old('iban', $paieData->iban) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Domiciliation bancaire</label>
                    <input type="text" name="domiciliation_bancaire" class="form-control" value="{{ old('domiciliation_bancaire', $paieData->domiciliation_bancaire) }}">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Autres</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Date de dernière augmentation</label>
                    <input type="date" name="date_derniere_augmentation" class="form-control" value="{{ old('date_derniere_augmentation', $paieData->date_derniere_augmentation?->format('Y-m-d')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Commentaire</label>
                    <textarea name="commentaire_paie" class="form-control" rows="2">{{ old('commentaire_paie', $paieData->commentaire_paie) }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
@endsection

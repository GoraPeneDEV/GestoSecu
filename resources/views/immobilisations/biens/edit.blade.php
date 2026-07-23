@extends('layouts.contentNavbarLayout')

@section('title', 'Modifier le bien')

@section('content')
    <h3 class="mb-4">Modifier {{ $bien->code_interne }}</h3>

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
            <form method="POST" action="{{ route('immobilisations.biens.update', $bien->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Catégorie *</label>
                        <select name="categorie_id" class="form-select" required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected($bien->categorie_id == $cat->id)>{{ $cat->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Désignation *</label>
                        <input type="text" name="designation" class="form-control" value="{{ $bien->designation }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ $bien->description }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Numéro de série</label>
                        <input type="text" name="numero_serie" class="form-control" value="{{ $bien->numero_serie }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Numéro de facture</label>
                        <input type="text" name="numero_facture" class="form-control" value="{{ $bien->numero_facture }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site *</label>
                        <select name="site_id" class="form-select" required>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}" @selected($bien->site_id == $site->id)>{{ $site->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emplacement</label>
                        <select name="emplacement_id" class="form-select">
                            <option value="">-- Aucun --</option>
                            @foreach ($emplacements as $empl)
                                <option value="{{ $empl->id }}" @selected($bien->emplacement_id == $empl->id)>{{ $empl->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date d'acquisition *</label>
                        <input type="date" name="date_acquisition" class="form-control" value="{{ $bien->date_acquisition->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Valeur d'acquisition (FCFA) *</label>
                        <input type="number" name="valeur_acquisition" class="form-control" min="0" step="0.01" value="{{ $bien->valeur_acquisition }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Valeur résiduelle</label>
                        <input type="number" name="valeur_residuelle" class="form-control" min="0" step="0.01" value="{{ $bien->valeur_residuelle }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Méthode d'amortissement *</label>
                        <select name="methode_amortissement" class="form-select" required>
                            <option value="lineaire" @selected($bien->methode_amortissement == 'lineaire')>Linéaire</option>
                            <option value="degressif" @selected($bien->methode_amortissement == 'degressif')>Dégressif</option>
                            <option value="variable" @selected($bien->methode_amortissement == 'variable')>Variable</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Durée d'amortissement (années) *</label>
                        <input type="number" name="duree_amortissement_annees" class="form-control" min="1" value="{{ $bien->duree_amortissement_annees }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Taux d'amortissement (%)</label>
                        <input type="number" name="taux_amortissement" class="form-control" min="0" max="100" step="0.01" value="{{ $bien->taux_amortissement }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de début d'amortissement</label>
                        <input type="date" name="date_debut_amortissement" class="form-control" value="{{ $bien->date_debut_amortissement?->format('Y-m-d') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                <a href="{{ route('immobilisations.biens.show', $bien->id) }}" class="btn btn-outline-secondary mt-3">Annuler</a>
            </form>
        </div>
    </div>
@endsection

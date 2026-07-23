@extends('layouts.contentNavbarLayout')

@section('title', 'Modifier le contrat')

@section('content')
    <h3 class="mb-4">Modifier le contrat {{ $contrat->numero_contrat }}</h3>

    <div class="card" style="max-width: 800px;">
        <div class="card-body">
            <form method="POST" action="{{ route('sav.contrats.update', $contrat->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Statut *</label>
                        <select name="statut" class="form-select" required>
                            @foreach (['brouillon', 'en_attente_signature', 'actif', 'suspendu', 'resilie', 'expire', 'renouvele'] as $statut)
                                <option value="{{ $statut }}" @selected($contrat->statut == $statut)>{{ ucfirst(str_replace('_', ' ', $statut)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Responsable SAV</label>
                        <select name="responsable_sav_id" class="form-select">
                            <option value="">-- Aucun --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected($contrat->responsable_sav_id == $user->id)>{{ $user->nom_complet }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de signature *</label>
                        <input type="date" name="date_signature" class="form-control" value="{{ $contrat->date_signature?->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de début *</label>
                        <input type="date" name="date_debut" class="form-control" value="{{ $contrat->date_debut?->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de fin *</label>
                        <input type="date" name="date_fin" class="form-control" value="{{ $contrat->date_fin?->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Montant total (FCFA) *</label>
                        <input type="number" name="montant_total" class="form-control" value="{{ $contrat->montant_total }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fréquence de paiement *</label>
                        <select name="frequence_paiement" class="form-select" required>
                            @foreach (['mensuel', 'trimestriel', 'semestriel', 'annuel', 'unique'] as $freq)
                                <option value="{{ $freq }}" @selected($contrat->frequence_paiement == $freq)>{{ ucfirst($freq) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Délai d'intervention (heures)</label>
                        <input type="number" name="delai_intervention_heures" class="form-control" value="{{ $contrat->delai_intervention_heures }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Prestations incluses</label>
                        <textarea name="prestations_incluses" class="form-control" rows="3">{{ $contrat->prestations_incluses }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ $contrat->notes }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Fichier du contrat (PDF)</label>
                        <input type="file" name="fichier_contrat" class="form-control" accept="application/pdf">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                <a href="{{ route('sav.contrats.show', $contrat->id) }}" class="btn btn-outline-secondary mt-3">Annuler</a>
            </form>
        </div>
    </div>
@endsection

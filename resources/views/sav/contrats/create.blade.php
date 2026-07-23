@extends('layouts.contentNavbarLayout')

@section('title', 'Nouveau contrat SAV')

@section('content')
    <h3 class="mb-4">Nouveau contrat SAV</h3>

    <div class="card" style="max-width: 800px;">
        <div class="card-body">
            <form method="POST" action="{{ route('sav.contrats.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client *</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected($clientPreselected && $clientPreselected->id == $client->id)>{{ $client->nomClient }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type *</label>
                        <select name="type" class="form-select" required>
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de signature *</label>
                        <input type="date" name="date_signature" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de début *</label>
                        <input type="date" name="date_debut" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de fin *</label>
                        <input type="date" name="date_fin" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Montant total (FCFA) *</label>
                        <input type="number" name="montant_total" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fréquence de paiement *</label>
                        <select name="frequence_paiement" class="form-select" required>
                            <option value="mensuel">Mensuel</option>
                            <option value="trimestriel">Trimestriel</option>
                            <option value="semestriel">Semestriel</option>
                            <option value="annuel">Annuel</option>
                            <option value="unique">Unique</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Délai d'intervention (heures)</label>
                        <input type="number" name="delai_intervention_heures" class="form-control" value="24">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Responsable SAV</label>
                        <select name="responsable_sav_id" class="form-select">
                            <option value="">-- Aucun --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->nom_complet }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="garantie_incluse" value="1" class="form-check-input" id="garantie_incluse">
                            <label class="form-check-label" for="garantie_incluse">Garantie incluse</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Durée de garantie (mois)</label>
                        <input type="number" name="duree_garantie_mois" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Prestations incluses</label>
                        <textarea name="prestations_incluses" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Fichier du contrat (PDF)</label>
                        <input type="file" name="fichier_contrat" class="form-control" accept="application/pdf">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                <a href="{{ route('sav.contrats.index') }}" class="btn btn-outline-secondary mt-3">Annuler</a>
            </form>
        </div>
    </div>
@endsection

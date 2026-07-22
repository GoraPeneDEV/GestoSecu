@extends('layouts.app')

@section('title', 'Nouvelle garantie')

@section('content')
    <h3 class="mb-4">Nouvelle garantie</h3>

    <div class="card" style="max-width: 800px;">
        <div class="card-body">
            <form method="POST" action="{{ route('sav.garanties.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client *</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->nomClient }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contrat lié</label>
                        <select name="contrat_id" class="form-select">
                            <option value="">-- Aucun --</option>
                            @foreach ($contrats as $contrat)
                                <option value="{{ $contrat->id }}">{{ $contrat->numero_contrat }} — {{ $contrat->client->nomClient ?? '' }}</option>
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
                    <div class="col-md-6">
                        <label class="form-label">Durée (mois) *</label>
                        <input type="number" name="duree_mois" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de début *</label>
                        <input type="date" name="date_debut" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin *</label>
                        <input type="date" name="date_fin" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Conditions</label>
                        <textarea name="conditions" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Exclusions</label>
                        <textarea name="exclusions" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                <a href="{{ route('sav.garanties.index') }}" class="btn btn-outline-secondary mt-3">Annuler</a>
            </form>
        </div>
    </div>
@endsection

@extends('layouts.contentNavbarLayout')

@section('title', 'Nouvelle interaction')

@section('content')
    <h3 class="mb-4">Nouvelle interaction client</h3>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('sav.interactions.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Client *</label>
                    <select name="client_id" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected($clientPreselected && $clientPreselected->id == $client->id)>{{ $client->nomClient }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Type *</label>
                        <select name="type" class="form-select" required>
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Canal *</label>
                        <select name="canal" class="form-select" required>
                            @foreach ($canaux as $canal)
                                <option value="{{ $canal }}">{{ ucfirst($canal) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sens *</label>
                        <select name="sens" class="form-select" required>
                            <option value="entrant">Entrant</option>
                            <option value="sortant">Sortant</option>
                            <option value="interne">Interne</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="traite">Traité</option>
                            <option value="a_traiter">À traiter</option>
                            <option value="en_attente">En attente</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sujet *</label>
                    <input type="text" name="sujet" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contenu</label>
                    <textarea name="contenu" class="form-control" rows="4"></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Rappel le</label>
                        <input type="date" name="rappel_le" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Attribué à</label>
                        <select name="rappel_attribue_a" class="form-select">
                            <option value="">-- Aucun --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->nom_complet }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('sav.interactions.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection

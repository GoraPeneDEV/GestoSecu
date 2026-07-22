@extends('layouts.app')

@section('title', 'Nouvelle fiche de progrès')

@section('content')
    <h3 class="mb-4">Nouvelle fiche de progrès</h3>

    <div class="card" style="max-width: 800px;">
        <div class="card-body">
            <form method="POST" action="{{ route('sav.fiches-progres.store') }}">
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
                    <div class="col-md-6">
                        <label class="form-label">Processus concerné *</label>
                        <select name="processus_concerne" class="form-select" required>
                            @foreach ($processus as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Objet *</label>
                        <input type="text" name="objet" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Constat client *</label>
                        <textarea name="constat_client" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                <a href="{{ route('sav.fiches-progres.index') }}" class="btn btn-outline-secondary mt-3">Annuler</a>
            </form>
        </div>
    </div>
@endsection

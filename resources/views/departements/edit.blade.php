@extends('layouts.app')

@section('title', 'Modifier - ' . $departement->nom)

@section('content')
    <h3 class="mb-4">Modifier le département</h3>

    <div class="card" style="max-width: 600px;">
        <div class="card-body">
            <form method="POST" action="{{ route('departements.update', $departement->id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $departement->nom) }}" required>
                    @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Responsable (optionnel)</label>
                    <select name="responsable_id" class="form-select">
                        <option value="">-- Aucun --</option>
                        @foreach (\App\Models\Employe::where('etat', 1)->orderBy('nom')->get() as $employe)
                            <option value="{{ $employe->id }}" @selected(old('responsable_id', $departement->responsable_id) == $employe->id)>{{ $employe->prenom }} {{ $employe->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('departements.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection

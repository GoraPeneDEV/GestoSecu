@extends('layouts.app')

@section('title', 'Modifier le contrat')

@section('content')
    <h3 class="mb-4">Modifier le contrat de {{ $employe->prenom }} {{ $employe->nom }}</h3>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('contrats.update', ['employe' => $employe->id, 'contrat' => $contrat->id]) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Type de contrat *</label>
                    <select name="type_contrat" id="type_contrat" class="form-select" required>
                        @foreach (['CDI', 'CDD', 'Stage', 'Prestation de service'] as $type)
                            <option value="{{ $type }}" @selected($contrat->type_contrat == $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de début *</label>
                    <input type="date" name="date_debut" class="form-control" value="{{ $contrat->date_debut?->format('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de fin</label>
                    <input type="date" name="date_fin" class="form-control" value="{{ $contrat->date_prevu_fin?->format('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Salaire (FCFA) *</label>
                    <input type="number" name="montant" class="form-control" min="0" value="{{ $contrat->montant }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motif</label>
                    <textarea name="motif" class="form-control" rows="2">{{ $contrat->motif }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Document (PDF)</label>
                    <input type="file" name="document" class="form-control" accept="application/pdf">
                    @if ($contrat->document)
                        <div class="form-text">
                            <a href="{{ asset('storage/' . $contrat->document) }}" target="_blank">Document actuel</a>
                        </div>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('contrats.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection

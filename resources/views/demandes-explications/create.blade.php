@extends('layouts.app')

@section('title', 'Nouvelle demande d\'explication')

@section('content')
    <h3 class="mb-4">Nouvelle demande d'explication</h3>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('demandes-explications.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Employé *</label>
                    <select name="employe_id" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach ($employes as $employe)
                            <option value="{{ $employe->id }}">{{ $employe->prenom }} {{ $employe->nom }} ({{ $employe->matricule }}) - {{ $employe->departement->nom ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motif *</label>
                    <input type="text" name="motif" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de l'incident *</label>
                    <input type="date" name="date_incident" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Document</label>
                    <input type="file" name="document" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('demandes-explications.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection

@extends('layouts.contentNavbarLayout')

@section('title', 'Modifier la demande')

@section('content')
    <h3 class="mb-4">Modifier la demande d'explication</h3>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('demandes-explications.update', $demande->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Employé *</label>
                    <select name="employe_id" class="form-select" required>
                        @foreach ($employes as $employe)
                            <option value="{{ $employe->id }}" @selected($demande->employe_id == $employe->id)>{{ $employe->prenom }} {{ $employe->nom }} ({{ $employe->matricule }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motif *</label>
                    <input type="text" name="motif" class="form-control" value="{{ $demande->motif }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de l'incident *</label>
                    <input type="date" name="date_incident" class="form-control" value="{{ $demande->date_incident?->format('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" rows="4" required>{{ $demande->description }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Document</label>
                    <input type="file" name="document" class="form-control">
                    @if ($demande->document_path)
                        <div class="form-text"><a href="{{ asset('storage/' . $demande->document_path) }}" target="_blank">Document actuel</a></div>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('demandes-explications.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection

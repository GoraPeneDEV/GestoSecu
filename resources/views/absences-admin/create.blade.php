@extends('layouts.app')

@section('title', 'Nouvelle demande d\'absence')

@section('content')
    <h3 class="mb-4">Nouvelle demande d'absence</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('absences-admin.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="employe_id" value="{{ $employe->id }}">
                <p class="text-muted">Demande pour : <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong></p>

                <div class="mb-3">
                    <label class="form-label">Type de demande *</label>
                    <select name="type_demande" class="form-select" required>
                        <option value="conge_annuel">Congé annuel</option>
                        <option value="autorisation_absence">Autorisation d'absence</option>
                        <option value="conge_maladie">Congé maladie</option>
                        <option value="conge_maternite">Congé maternité</option>
                        <option value="conge_mariage">Congé mariage</option>
                        <option value="deces">Décès</option>
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Date de début *</label>
                        <input type="date" name="date_debut" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin *</label>
                        <input type="date" name="date_fin" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motif *</label>
                    <textarea name="motif" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Document justificatif</label>
                    <input type="file" name="document" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Envoyer la demande</button>
                <a href="{{ route('absences-admin.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection

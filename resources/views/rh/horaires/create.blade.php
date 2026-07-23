@extends('layouts.contentNavbarLayout')

@section('title', 'Nouvel horaire')

@section('content')
    <h3 class="mb-4">Nouvel horaire de planning</h3>

    <div class="card" style="max-width: 500px;">
        <div class="card-body">
            <form method="POST" action="{{ route('horaires.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Libellé *</label>
                    <input type="text" name="label" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Heure de début *</label>
                    <input type="time" name="heure_debut" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Heure de fin *</label>
                    <input type="time" name="heure_fin" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('horaires.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection

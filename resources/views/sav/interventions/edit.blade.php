@extends('layouts.contentNavbarLayout')

@section('title', 'Modifier l\'intervention')

@section('content')
    <h3 class="mb-4">Modifier {{ $intervention->numero_intervention }}</h3>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('sav.interventions.update', $intervention->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Date d'intervention *</label>
                        <input type="date" name="date_intervention" class="form-control" value="{{ $intervention->date_intervention?->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Recommandations générales</label>
                        <textarea name="recommandations_generales" class="form-control" rows="4">{{ $intervention->recommandations_generales }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                <a href="{{ route('sav.interventions.show', $intervention->id) }}" class="btn btn-outline-secondary mt-3">Annuler</a>
            </form>
        </div>
    </div>
@endsection

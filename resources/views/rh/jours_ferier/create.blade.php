@extends('layouts.contentNavbarLayout')

@section('title', 'Nouveau jour férié')

@section('content')
    <h3 class="mb-4">Nouveau jour férié</h3>

    <div class="card" style="max-width: 500px;">
        <div class="card-body">
            <form method="POST" action="{{ route('jours_ferier.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Date *</label>
                    <input type="date" name="date_ferier" class="form-control @error('date_ferier') is-invalid @enderror" value="{{ old('date_ferier') }}" required>
                    @error('date_ferier')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('jours_ferier.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection

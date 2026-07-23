@extends('layouts.contentNavbarLayout')

@section('title', 'Nouveau point de contrôle superviseur')

@section('content')
    <h3 class="mb-4">Nouveau point de contrôle superviseur</h3>

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
            <form method="POST" action="{{ route('superviseur.pointcontroles.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Site *</label>
                        <select name="site_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}" @selected(old('site_id') == $site->id)>{{ $site->nom_site }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emplacement</label>
                        <input type="text" name="emplacement" class="form-control" value="{{ old('emplacement') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ordre *</label>
                        <input type="number" name="ordre" class="form-control" min="1" value="{{ old('ordre', 1) }}" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="actif" value="1" class="form-check-input" id="actif" checked>
                            <label class="form-check-label" for="actif">Actif</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                <a href="{{ route('superviseur.pointcontroles.index') }}" class="btn btn-outline-secondary mt-3">Annuler</a>
            </form>
        </div>
    </div>
@endsection

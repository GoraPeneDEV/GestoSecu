@extends('layouts.contentNavbarLayout')

@section('title', 'Modifier l\'équipement')

@section('content')
    <h3 class="mb-4">Modifier {{ $asset->label }}</h3>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('sav.parc.update', $asset->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Type *</label>
                        <select name="type" class="form-select" required>
                            <option value="incendie" @selected($asset->type == 'incendie')>Incendie</option>
                            <option value="securite_electronique" @selected($asset->type == 'securite_electronique')>Sécurité électronique</option>
                            <option value="monetique" @selected($asset->type == 'monetique')>Monétique</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Catégorie</label>
                        <input type="text" name="category" class="form-control" value="{{ $asset->category }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Libellé *</label>
                        <input type="text" name="label" class="form-control" value="{{ $asset->label }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-select">
                            <option value="fonctionnel" @selected($asset->status == 'fonctionnel')>Fonctionnel</option>
                            <option value="maintenance_requise" @selected($asset->status == 'maintenance_requise')>Maintenance requise</option>
                            <option value="panne" @selected($asset->status == 'panne')>Panne</option>
                            <option value="hors_service" @selected($asset->status == 'hors_service')>Hors service</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Marque</label>
                        <input type="text" name="brand" class="form-control" value="{{ $asset->brand }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modèle</label>
                        <input type="text" name="model" class="form-control" value="{{ $asset->model }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">N° série</label>
                        <input type="text" name="serial_number" class="form-control" value="{{ $asset->serial_number }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date d'installation</label>
                        <input type="date" name="installation_date" class="form-control" value="{{ $asset->installation_date?->format('Y-m-d') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ $asset->notes }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                <a href="{{ route('sav.parc.client', $asset->site->client_id ?? '') }}" class="btn btn-outline-secondary mt-3">Annuler</a>
            </form>
        </div>
    </div>
@endsection

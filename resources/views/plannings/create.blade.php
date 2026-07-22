@extends('layouts.app')

@section('title', 'Nouveau planning')

@section('content')
    <h3 class="mb-4">Nouveau planning</h3>

    <div class="card" style="max-width: 800px;">
        <div class="card-body">
            <form method="POST" action="{{ route('plannings.store') }}">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Employé *</label>
                        <select name="employe_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($employes as $employe)
                                <option value="{{ $employe->id }}">{{ $employe->prenom }} {{ $employe->nom }} ({{ $employe->matricule }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site *</label>
                        <select name="site_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->nom_site }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de début * (jj/mm/aaaa)</label>
                        <input type="text" name="date_debut" class="form-control" placeholder="jj/mm/aaaa" pattern="\d{2}/\d{2}/\d{4}" required>
                    </div>
                </div>

                <h6>Horaires hebdomadaires (optionnel — laisser vide pour un jour de repos)</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Jour</th><th>Horaire</th></tr></thead>
                        <tbody>
                            @foreach (\App\Models\DetailPlanningHorizontal::JOURS as $jour)
                                <tr>
                                    <td class="text-capitalize">{{ $jour }}</td>
                                    <td>
                                        <select name="horaires[{{ $jour }}]" class="form-select form-select-sm">
                                            <option value="">Repos</option>
                                            @foreach ($horaires as $horaire)
                                                <option value="{{ $horaire->id }}">{{ $horaire->label }} ({{ $horaire->heure_debut }} - {{ $horaire->heure_fin }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('plannings.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection

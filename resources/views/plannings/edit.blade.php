@extends('layouts.app')

@section('title', 'Modifier le planning')

@section('content')
    <h3 class="mb-4">Modifier le planning de {{ $planning->employe->prenom }} {{ $planning->employe->nom }}</h3>

    <div class="card" style="max-width: 800px;">
        <div class="card-body">
            <form method="POST" action="{{ route('plannings.update', $planning->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Site *</label>
                        <select name="site_id" class="form-select" required>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}" @selected($planning->site_id == $site->id)>{{ $site->nom_site }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h6>Horaires hebdomadaires</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Jour</th><th>Horaire</th></tr></thead>
                        <tbody>
                            @foreach (\App\Models\DetailPlanningHorizontal::JOURS as $jour)
                                @php($current = $planning->detailsHorizontal->firstWhere('jour_semaine', $jour))
                                <tr>
                                    <td class="text-capitalize">{{ $jour }}</td>
                                    <td>
                                        <select name="horaires[{{ $jour }}]" class="form-select form-select-sm">
                                            <option value="">Repos</option>
                                            @foreach ($horaires as $horaire)
                                                <option value="{{ $horaire->id }}" @selected($current && $current->horaire_id == $horaire->id)>{{ $horaire->label }} ({{ $horaire->heure_debut }} - {{ $horaire->heure_fin }})</option>
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

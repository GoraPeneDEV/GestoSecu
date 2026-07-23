@extends('layouts.contentNavbarLayout')

@section('title', 'Planning')

@section('content')
    <a href="{{ route('plannings.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Planning de {{ $planning->employe->prenom }} {{ $planning->employe->nom }}</h3>

    <div class="card mb-3">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-3">Site</dt>
                <dd class="col-9">{{ $planning->site->nom_site ?? '-' }}</dd>
                <dt class="col-3">Département</dt>
                <dd class="col-9">{{ $planning->employe->departement->nom ?? '-' }}</dd>
                <dt class="col-3">Date de début</dt>
                <dd class="col-9">{{ $planning->date_debut?->format('d/m/Y') }}</dd>
                <dt class="col-3">Date de fin</dt>
                <dd class="col-9">{{ $planning->date_fin?->format('d/m/Y') ?? 'En cours' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Horaires hebdomadaires</div>
        <div class="card-body">
            <table class="table table-sm mb-0">
                <thead><tr><th>Jour</th><th>Horaire</th></tr></thead>
                <tbody>
                    @foreach (\App\Models\DetailPlanningHorizontal::JOURS as $jour)
                        @php($detail = $planning->detailsHorizontal->firstWhere('jour_semaine', $jour))
                        <tr>
                            <td class="text-capitalize">{{ $jour }}</td>
                            <td>
                                @if ($detail && $detail->horaire)
                                    {{ $detail->horaire->label }} ({{ $detail->horaire->heure_debut }} - {{ $detail->horaire->heure_fin }})
                                @else
                                    Repos
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

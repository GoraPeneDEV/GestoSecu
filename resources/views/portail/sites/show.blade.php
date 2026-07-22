@extends('portail.layouts.app')

@section('title', 'Site - ' . $site->nom_site)

@section('content')
    <a href="{{ route('portail.sites.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour aux sites
    </a>

    <h3 class="mb-4">{{ $site->nom_site }}</h3>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Informations</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Type</dt>
                        <dd class="col-7">{{ ucfirst($site->type_site) }}</dd>
                        <dt class="col-5">Localisation</dt>
                        <dd class="col-7">{{ $site->localisation ?? '-' }}</dd>
                        <dt class="col-5">Région</dt>
                        <dd class="col-7">{{ $site->region ?? '-' }}</dd>
                        <dt class="col-5">Zone</dt>
                        <dd class="col-7">{{ $site->zone->nom ?? '-' }}</dd>
                        <dt class="col-5">Contact</dt>
                        <dd class="col-7">{{ $site->contact_nom ?? '-' }} ({{ $site->contact_telephone ?? '-' }})</dd>
                        <dt class="col-5">Date de début</dt>
                        <dd class="col-7">{{ $site->date_debut?->format('d/m/Y') ?? '-' }}</dd>
                        @if ($site->numero_rpe)
                            <dt class="col-5">Numéro RPE</dt>
                            <dd class="col-7">{{ $site->numero_rpe }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Statistiques</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-7">Plannings actifs</dt>
                        <dd class="col-5">{{ $siteStats['planningsActifs'] }}</dd>
                        <dt class="col-7">Employés assignés</dt>
                        <dd class="col-5">{{ $siteStats['employesAssignes'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Agents assignés</div>
        <div class="card-body">
            @if ($agents->isEmpty())
                <p class="text-muted mb-0">Aucun agent actuellement assigné à ce site.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Matricule</th>
                                <th>Fonction</th>
                                <th>Département</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($agents as $agent)
                                <tr>
                                    <td><a href="{{ route('portail.agents.show', $agent->id) }}">{{ $agent->prenom }} {{ $agent->nom }}</a></td>
                                    <td>{{ $agent->matricule }}</td>
                                    <td>{{ $agent->fonction }}</td>
                                    <td>{{ $agent->departement->nom ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

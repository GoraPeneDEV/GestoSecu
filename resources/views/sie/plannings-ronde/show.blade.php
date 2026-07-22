@extends('layouts.app')

@section('title', $planningRonde->nom)

@section('content')
    <a href="{{ route('sie.plannings-ronde.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">{{ $planningRonde->nom }}</h3>
        <a href="{{ route('sie.plannings-ronde.edit', $planningRonde->id) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i> Modifier
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Détails</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Site</dt><dd class="col-7">{{ $planningRonde->site->nom_site ?? '-' }}</dd>
                        <dt class="col-5">Fréquence</dt><dd class="col-7">{{ ucfirst($planningRonde->frequence) }}</dd>
                        <dt class="col-5">Heure de début</dt><dd class="col-7">{{ \Carbon\Carbon::parse($planningRonde->heure_debut)->format('H:i') }}</dd>
                        <dt class="col-5">Durée estimée</dt><dd class="col-7">{{ $planningRonde->duree_estimee }} min</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Points de contrôle ({{ $planningRonde->pointsControle->count() }})</div>
                <div class="card-body p-0">
                    <ol class="list-group list-group-numbered list-group-flush">
                        @foreach ($planningRonde->pointsControle as $point)
                            <li class="list-group-item">{{ $point->nom }} <small class="text-muted">{{ $point->emplacement }}</small></li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

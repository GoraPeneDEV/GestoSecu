@extends('layouts.contentNavbarLayout')

@section('title', $departement->nom)

@section('content')
    <a href="{{ route('departements.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">{{ $departement->nom }}</h3>

    <div class="card" style="max-width: 600px;">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-4">Responsable</dt>
                <dd class="col-8">{{ $departement->responsable ? $departement->responsable->prenom . ' ' . $departement->responsable->nom : '-' }}</dd>
                <dt class="col-4">Effectif actif</dt>
                <dd class="col-8">{{ $departement->employes()->where('etat', 1)->count() }}</dd>
            </dl>
        </div>
    </div>
@endsection

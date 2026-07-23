@extends('layouts.contentNavbarLayout')

@section('title', 'Paie - ' . $employe->prenom . ' ' . $employe->nom)

@section('content')
    <a href="{{ route('employes.show', $employe->id) }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour à l'employé
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Données de paie — {{ $employe->prenom }} {{ $employe->nom }}</h3>
        <a href="{{ route('paie.employes.edit', $employe->id) }}" class="btn btn-warning">
            <i class="ti ti-pencil"></i> Modifier
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Rémunération</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-6">Salaire de base</dt>
                        <dd class="col-6 text-end">{{ number_format($paieData->salaire_base, 0, ',', ' ') }} FCFA</dd>
                        <dt class="col-6">Sursalaire</dt>
                        <dd class="col-6 text-end">{{ number_format($paieData->sursalaire ?? 0, 0, ',', ' ') }} FCFA</dd>
                        <dt class="col-6">Catégorie</dt>
                        <dd class="col-6 text-end">{{ $paieData->categorie_professionnelle ?? '-' }}</dd>
                        <dt class="col-6">Parts fiscales</dt>
                        <dd class="col-6 text-end">{{ $paieData->parts_fiscales }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Affiliations</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-6">N° IPRES</dt><dd class="col-6 text-end">{{ $paieData->numero_ipres ?? '-' }}</dd>
                        <dt class="col-6">N° CSS</dt><dd class="col-6 text-end">{{ $paieData->numero_css ?? '-' }}</dd>
                        <dt class="col-6">N° IPM</dt><dd class="col-6 text-end">{{ $paieData->numero_ipm ?? '-' }}</dd>
                        <dt class="col-6">Banque</dt><dd class="col-6 text-end">{{ $paieData->banque_nom ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection

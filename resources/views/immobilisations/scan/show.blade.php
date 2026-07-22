@extends('layouts.app')

@section('title', 'Scan — ' . $immobilisation->code_interne)

@section('content')
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $immobilisation->code_interne }}</h5>
            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $immobilisation->statut)) }}</span>
        </div>
        <div class="card-body">
            <h4>{{ $immobilisation->designation }}</h4>
            <dl class="row mb-0">
                <dt class="col-5">Catégorie</dt><dd class="col-7">{{ $immobilisation->categorie->libelle ?? '-' }}</dd>
                <dt class="col-5">Site</dt><dd class="col-7">{{ $immobilisation->site->libelle ?? '-' }}</dd>
                <dt class="col-5">Emplacement</dt><dd class="col-7">{{ $immobilisation->emplacement->libelle ?? '-' }}</dd>
                <dt class="col-5">Détenteur</dt><dd class="col-7">{{ $immobilisation->employe ? $immobilisation->employe->prenom . ' ' . $immobilisation->employe->nom : 'En stock' }}</dd>
                <dt class="col-5">Numéro de série</dt><dd class="col-7">{{ $immobilisation->numero_serie ?? '-' }}</dd>
                <dt class="col-5">Date d'acquisition</dt><dd class="col-7">{{ $immobilisation->date_acquisition->format('d/m/Y') }}</dd>
                <dt class="col-5">Valeur d'acquisition</dt><dd class="col-7">{{ number_format($immobilisation->valeur_acquisition, 0, ',', ' ') }} FCFA</dd>
            </dl>
            <a href="{{ route('immobilisations.biens.show', $immobilisation->id) }}" class="btn btn-primary btn-sm mt-3">
                <i class="bi bi-box-arrow-up-right"></i> Voir la fiche complète
            </a>
        </div>
    </div>
@endsection

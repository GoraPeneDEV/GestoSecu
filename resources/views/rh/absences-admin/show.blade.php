@extends('layouts.contentNavbarLayout')

@section('title', 'Demande d\'absence')

@section('content')
    <a href="{{ route('absences-admin.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Demande de {{ $demande->employe->prenom ?? '' }} {{ $demande->employe->nom ?? '' }}</h3>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Détails</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Type</dt>
                        <dd class="col-7">{{ $demande->type_conges }}</dd>
                        <dt class="col-5">Période</dt>
                        <dd class="col-7">{{ $demande->date_debut?->format('d/m/Y') }} → {{ $demande->date_fin?->format('d/m/Y') }}</dd>
                        <dt class="col-5">Jours ouvrables</dt>
                        <dd class="col-7">{{ $demande->nbr_jour }}</dd>
                        <dt class="col-5">Motif</dt>
                        <dd class="col-7">{{ $demande->motif }}</dd>
                        <dt class="col-5">Statut</dt>
                        <dd class="col-7"><span class="badge bg-secondary">{{ $demande->statut_libelle ?? $demande->statut }}</span></dd>
                        @if ($demande->document_path)
                            <dt class="col-5">Document</dt>
                            <dd class="col-7"><a href="{{ asset('storage/' . $demande->document_path) }}" target="_blank">Voir le document</a></dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Validation</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Supérieur</dt>
                        <dd class="col-7">{{ $demande->superieur ? $demande->superieur->prenom . ' ' . $demande->superieur->nom : '-' }}</dd>
                        <dt class="col-5">Commentaire sup.</dt>
                        <dd class="col-7">{{ $demande->commentaire_sup ?? '-' }}</dd>
                        <dt class="col-5">RH</dt>
                        <dd class="col-7">{{ $demande->responsableRH ? $demande->responsableRH->prenom . ' ' . $demande->responsableRH->nom : '-' }}</dd>
                        <dt class="col-5">Commentaire RH</dt>
                        <dd class="col-7">{{ $demande->commentaire_rh ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection

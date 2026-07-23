@extends('layouts.contentNavbarLayout')

@section('title', 'Demande d\'explication')

@section('content')
    <a href="{{ route('demandes-explications.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Demande — {{ $demande->employe->prenom ?? '' }} {{ $demande->employe->nom ?? '' }}</h3>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-4">Département</dt>
                <dd class="col-8">{{ $demande->employe->departement->nom ?? '-' }}</dd>
                <dt class="col-4">Motif</dt>
                <dd class="col-8">{{ $demande->motif }}</dd>
                <dt class="col-4">Date incident</dt>
                <dd class="col-8">{{ $demande->date_incident?->format('d/m/Y') }}</dd>
                <dt class="col-4">Description</dt>
                <dd class="col-8">{{ $demande->description }}</dd>
                <dt class="col-4">Statut</dt>
                <dd class="col-8">
                    <span class="badge {{ $demande->statut === 'repondue' ? 'bg-success' : 'bg-warning' }}">
                        {{ $demande->statut === 'repondue' ? 'Répondue' : 'En attente' }}
                    </span>
                </dd>
                @if ($demande->document_path)
                    <dt class="col-4">Document</dt>
                    <dd class="col-8"><a href="{{ asset('storage/' . $demande->document_path) }}" target="_blank">Voir le document</a></dd>
                @endif
                @if ($demande->reponse_document_path)
                    <dt class="col-4">Réponse</dt>
                    <dd class="col-8">
                        <a href="{{ asset('storage/' . $demande->reponse_document_path) }}" target="_blank">Voir la réponse</a>
                        ({{ $demande->date_reponse?->format('d/m/Y') }})
                    </dd>
                @endif
            </dl>
        </div>
    </div>
@endsection

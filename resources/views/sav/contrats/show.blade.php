@extends('layouts.app')

@section('title', $contrat->numero_contrat)

@section('content')
    <a href="{{ route('sav.contrats.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">Contrat {{ $contrat->numero_contrat }} — {{ $contrat->client->nomClient ?? '' }}</h3>
        <div>
            <a href="{{ route('sav.contrats.edit', $contrat->id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            @if ($contrat->fichier_contrat)
                <a href="{{ route('sav.contrats.download', $contrat->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download"></i> Télécharger
                </a>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Détails</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Type</dt><dd class="col-7">{{ $contrat->type_label }}</dd>
                        <dt class="col-5">Statut</dt><dd class="col-7">{!! $contrat->statut_badge !!}</dd>
                        <dt class="col-5">Période</dt><dd class="col-7">{{ $contrat->date_debut?->format('d/m/Y') }} → {{ $contrat->date_fin?->format('d/m/Y') }}</dd>
                        <dt class="col-5">Montant total</dt><dd class="col-7">{{ number_format($contrat->montant_total, 0, ',', ' ') }} FCFA</dd>
                        <dt class="col-5">Fréquence paiement</dt><dd class="col-7">{{ ucfirst($contrat->frequence_paiement) }}</dd>
                        <dt class="col-5">Délai intervention</dt><dd class="col-7">{{ $contrat->delai_intervention_heures }}h</dd>
                        <dt class="col-5">Responsable SAV</dt><dd class="col-7">{{ $contrat->responsableSav->nom_complet ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Prestations incluses</div>
                <div class="card-body">
                    <p>{{ $contrat->prestations_incluses ?? 'Aucune information.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Fiches de progrès liées</div>
                <div class="card-body">
                    @if ($contrat->fichesProgres->isEmpty())
                        <p class="text-muted mb-0">Aucune.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($contrat->fichesProgres as $fiche)
                                <li class="list-group-item"><a href="{{ route('sav.fiches-progres.show', $fiche->id) }}">{{ $fiche->type }} — {{ $fiche->statut }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Garanties liées</div>
                <div class="card-body">
                    @if ($contrat->garanties->isEmpty())
                        <p class="text-muted mb-0">Aucune.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($contrat->garanties as $garantie)
                                <li class="list-group-item"><a href="{{ route('sav.garanties.show', $garantie->id) }}">{{ $garantie->type ?? '-' }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

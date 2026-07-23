@extends('layouts.contentNavbarLayout')

@section('title', $pointControle->nom)

@section('content')
    <a href="{{ route('sie.pointcontroles.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">{{ $pointControle->nom }}</h3>
        <div>
            <a href="{{ route('sie.pointcontroles.edit', $pointControle->id) }}" class="btn btn-warning btn-sm">
                <i class="ti ti-pencil"></i> Modifier
            </a>
            <a href="{{ route('sie.pointcontroles.download-qr', $pointControle->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-download"></i> Télécharger le QR Code
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Détails</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Site</dt><dd class="col-7">{{ $pointControle->site->nom_site ?? '-' }}</dd>
                        <dt class="col-5">Emplacement</dt><dd class="col-7">{{ $pointControle->emplacement ?? '-' }}</dd>
                        <dt class="col-5">Ordre</dt><dd class="col-7">{{ $pointControle->ordre }}</dd>
                        <dt class="col-5">Statut</dt><dd class="col-7">{{ $pointControle->actif ? 'Actif' : 'Inactif' }}</dd>
                        <dt class="col-5">Code QR</dt><dd class="col-7"><code>{{ $pointControle->qr_code }}</code></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-center">
            <div class="card h-100">
                <div class="card-header">QR Code</div>
                <div class="card-body">
                    {!! $qrCode !!}
                </div>
            </div>
        </div>
    </div>
@endsection

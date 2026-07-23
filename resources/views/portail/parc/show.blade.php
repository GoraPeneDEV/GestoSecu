@extends('layouts.contentNavbarLayout')

@section('title', 'Équipement - ' . $asset->label)

@section('content')
    <a href="{{ route('portail.parc.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour au parc
    </a>

    <h3 class="mb-4">{{ $asset->label }}</h3>

    <div class="card mb-3">
        <div class="card-header">Informations</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-3">Type</dt>
                <dd class="col-9">{{ $asset->type }}</dd>
                <dt class="col-3">Catégorie</dt>
                <dd class="col-9">{{ $asset->category ?? '-' }}</dd>
                <dt class="col-3">Marque / Modèle</dt>
                <dd class="col-9">{{ $asset->brand ?? '-' }} {{ $asset->model ?? '' }}</dd>
                <dt class="col-3">Numéro de série</dt>
                <dd class="col-9">{{ $asset->serial_number ?? '-' }}</dd>
                <dt class="col-3">Site</dt>
                <dd class="col-9">{{ $asset->site->nom_site ?? '-' }}</dd>
                <dt class="col-3">Date d'installation</dt>
                <dd class="col-9">{{ $asset->installation_date?->format('d/m/Y') ?? '-' }}</dd>
                <dt class="col-3">Statut</dt>
                <dd class="col-9">{{ ucfirst(str_replace('_', ' ', $asset->status)) }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Historique des interventions</div>
        <div class="card-body">
            @if ($asset->interventions->isEmpty())
                <p class="text-muted mb-0">Aucune intervention enregistrée pour cet équipement.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($asset->interventions as $intervention)
                                <tr>
                                    <td>{{ $intervention->date_intervention?->format('d/m/Y') }}</td>
                                    <td>{{ $intervention->type }}</td>
                                    <td>{{ ucfirst($intervention->statut) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

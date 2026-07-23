@extends('layouts.contentNavbarLayout')

@section('title', 'Garantie — ' . ($garantie->client->nomClient ?? ''))

@section('content')
    <a href="{{ route('sav.garanties.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">Garantie — {{ $garantie->client->nomClient ?? '' }}</h3>
        <div>
            <a href="{{ route('sav.garanties.edit', $garantie->id) }}" class="btn btn-warning btn-sm">
                <i class="ti ti-pencil"></i> Modifier
            </a>
            <form method="POST" action="{{ route('sav.garanties.destroy', $garantie->id) }}" class="d-inline" onsubmit="return confirm('Supprimer cette garantie ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash"></i> Supprimer</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-3">Type</dt><dd class="col-9">{{ $garantie->type_label }}</dd>
                <dt class="col-3">Statut</dt><dd class="col-9">{!! $garantie->statut_badge !!}</dd>
                <dt class="col-3">Contrat lié</dt><dd class="col-9">{{ $garantie->contrat->numero_contrat ?? '-' }}</dd>
                <dt class="col-3">Période</dt><dd class="col-9">{{ $garantie->date_debut?->format('d/m/Y') }} → {{ $garantie->date_fin?->format('d/m/Y') }} ({{ $garantie->duree_mois }} mois)</dd>
                <dt class="col-3">Jours restants</dt><dd class="col-9">{{ $garantie->joursRestants() }}</dd>
                <dt class="col-3">Conditions</dt><dd class="col-9">{{ $garantie->conditions ?? '-' }}</dd>
                <dt class="col-3">Exclusions</dt><dd class="col-9">{{ $garantie->exclusions ?? '-' }}</dd>
            </dl>
        </div>
    </div>
@endsection

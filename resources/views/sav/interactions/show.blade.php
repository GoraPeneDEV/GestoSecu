@extends('layouts.contentNavbarLayout')

@section('title', 'Interaction — ' . $interaction->sujet)

@section('content')
    <a href="{{ route('sav.interactions.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">{{ $interaction->sujet }}</h3>
        <div>
            @if ($interaction->statut !== 'traite')
                <form method="POST" action="{{ route('sav.interactions.traite', $interaction->id) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm">Marquer comme traité</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-3">Client</dt>
                <dd class="col-9">{{ $interaction->client->nomClient ?? '-' }}</dd>
                <dt class="col-3">Type</dt>
                <dd class="col-9">{{ $interaction->type_label }}</dd>
                <dt class="col-3">Canal</dt>
                <dd class="col-9">{{ ucfirst($interaction->canal) }}</dd>
                <dt class="col-3">Sens</dt>
                <dd class="col-9">{{ ucfirst($interaction->sens) }}</dd>
                <dt class="col-3">Statut</dt>
                <dd class="col-9">{!! $interaction->statut_badge !!}</dd>
                <dt class="col-3">Contenu</dt>
                <dd class="col-9">{{ $interaction->contenu ?? '-' }}</dd>
                <dt class="col-3">Enregistré par</dt>
                <dd class="col-9">{{ $interaction->user->nom_complet ?? '-' }} le {{ $interaction->created_at->format('d/m/Y H:i') }}</dd>
                @if ($interaction->rappel_le)
                    <dt class="col-3">Rappel prévu</dt>
                    <dd class="col-9">{{ \Carbon\Carbon::parse($interaction->rappel_le)->format('d/m/Y') }} — {{ $interaction->attribueA->nom_complet ?? '-' }}</dd>
                @endif
            </dl>
        </div>
    </div>
@endsection

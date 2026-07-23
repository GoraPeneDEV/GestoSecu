@extends('layouts.contentNavbarLayout')

@section('title', $horaire->label)

@section('content')
    <a href="{{ route('horaires.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">{{ $horaire->label }}</h3>

    <div class="card" style="max-width: 500px;">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-5">Heure de début</dt>
                <dd class="col-7">{{ $horaire->heure_debut }}</dd>
                <dt class="col-5">Heure de fin</dt>
                <dd class="col-7">{{ $horaire->heure_fin }}</dd>
                <dt class="col-5">Nombre d'heures</dt>
                <dd class="col-7">{{ $horaire->nombre_heures }}h</dd>
            </dl>
        </div>
    </div>
@endsection

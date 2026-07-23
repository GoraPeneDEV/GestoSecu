@extends('layouts.contentNavbarLayout')

@section('title', 'Jour férié')

@section('content')
    <a href="{{ route('jours_ferier.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="card" style="max-width: 500px;">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-4">Date</dt>
                <dd class="col-8">{{ $jourFerier->date_ferier?->format('d/m/Y') }}</dd>
                <dt class="col-4">Description</dt>
                <dd class="col-8">{{ $jourFerier->description ?? '-' }}</dd>
            </dl>
        </div>
    </div>
@endsection

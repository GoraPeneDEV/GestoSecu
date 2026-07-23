@extends('layouts.contentNavbarLayout')

@section('title', 'QR Code non assigné')

@section('content')
    <div class="card mx-auto text-center" style="max-width: 500px;">
        <div class="card-body">
            <i class="ti ti-qr-code-scan display-4 text-muted"></i>
            <h4 class="mt-3">QR Code non reconnu</h4>
            <p class="text-muted">Ce code ({{ $token }}) n'est associé à aucune immobilisation enregistrée.</p>
            <a href="{{ route('immobilisations.biens.index') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-arrow-left"></i> Retour aux biens
            </a>
        </div>
    </div>
@endsection
